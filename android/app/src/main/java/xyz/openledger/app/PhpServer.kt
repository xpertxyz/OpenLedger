package xyz.openledger.app

import android.content.Context
import java.io.File
import java.net.ServerSocket
import java.security.SecureRandom

/**
 * The PHP process that serves the ledger to the WebView.
 *
 * The same PHP that runs on the website runs here, unmodified — it is copied out of the APK
 * assets and served by PHP's own built-in server over loopback. Nothing is bundled that the
 * web app does not also use; the only difference is the environment this class hands it.
 */
class PhpServer(private val ctx: Context) {

    /** Random per launch, handed to the WebView as a cookie and enforced by index.php. */
    val token: String = SecureRandom().let { rng ->
        ByteArray(24).also { rng.nextBytes(it) }.joinToString("") { "%02x".format(it) }
    }

    var port: Int = 0; private set
    val origin: String get() = "http://127.0.0.1:$port"

    private var process: Process? = null
    private val docRoot: File get() = File(ctx.filesDir, "app")
    private val dbPath: File get() = File(ctx.filesDir, "ledger.db")
    private val logFile: File get() = File(ctx.filesDir, "php.log")

    /**
     * Start the interpreter and call [onReady] on the main thread once it is serving.
     *
     * Deliberately not synchronous. The readiness probe opens a socket, and any socket opened
     * on the main thread throws NetworkOnMainThreadException — which, caught by a `runCatching`
     * around the probe, looks exactly like "the server is not up yet" and never stops looking.
     */
    fun startAsync(onReady: () -> Unit) {
        Thread {
            runCatching { start() }
                .onFailure { e -> android.util.Log.e("PhpServer", "start failed", e) }
                .onSuccess { android.os.Handler(android.os.Looper.getMainLooper()).post(onReady) }
        }.start()
    }

    /** Blocking. Call from a background thread — see [startAsync]. */
    fun start() {
        if (process?.isAlive == true) return
        syncAppCode()
        port = freePort()

        // The interpreter is shipped as libphp.so so it lands in nativeLibraryDir, which is the
        // only place Android 10+ still permits executing a binary from. See build-php.sh.
        val php = File(ctx.applicationInfo.nativeLibraryDir, "libphp.so")
        check(php.canExecute()) { "libphp.so missing or not executable — run android/build-php.sh" }

        val sessions = File(ctx.filesDir, "sessions").apply { mkdirs() }

        val pb = ProcessBuilder(
            php.absolutePath,
            // PHP needs a writable session dir, and the default (/tmp) does not exist here.
            "-d", "session.save_path=${sessions.absolutePath}",
            "-d", "display_errors=0",
            "-S", "127.0.0.1:$port",
            "-t", docRoot.absolutePath,
            File(docRoot, "router.php").absolutePath
        ).directory(docRoot)

        // Configuration goes through the process environment, not a written .env file.
        // config.php already prefers real env vars over .env, so this needs no PHP change:
        // the app is configured exactly the way a production host configures it.
        pb.environment().apply {
            put("DB_DRIVER", "sqlite")
            put("DB_PATH", dbPath.absolutePath)
            put("HL_LOCAL_TOKEN", token)
            put("APP_TZ", java.util.TimeZone.getDefault().id)
            // Off for the local build: there is no second device to share a ledger with and no
            // server to hold a Google account. Set at build time via BuildConfig, never at
            // runtime — an app that changes behaviour after review is what gets it pulled.
            put("HL_SHARING", if (BuildConfig.FEATURE_SHARING) "1" else "0")
            put("HL_GOOGLE_SIGNIN", if (BuildConfig.FEATURE_SIGNIN) "1" else "0")
            // Draws the backup panel in the profile drawer, which drives BackupBridge.
            put("HL_BACKUP", "1")
            put("APP_DEBUG", "0")
        }
        // Both streams go to a file rather than a pipe. A pipe nobody drains fills its buffer
        // and blocks the interpreter, and on failure there would be nothing to read back —
        // "php did not start listening" with no reason attached is not a diagnosable error.
        pb.redirectErrorStream(true)
        pb.redirectOutput(ProcessBuilder.Redirect.to(logFile))
        process = pb.start()

        // PHP's built-in server binds before it prints anything; a short poll beats a fixed
        // sleep, which is either too slow on a fast device or too short on a cold start.
        waitUntilListening()
    }

    fun stop() {
        process?.destroy()
        process = null
    }

    /** A consistent snapshot for Drive backup. See index.php --backup for why VACUUM INTO. */
    fun backupTo(dest: File): Boolean {
        dest.delete()
        return cli("--backup", dest.absolutePath).first == 0 && dest.exists()
    }

    /**
     * Replace the ledger with a snapshot. Returns null on success, or PHP's own reason.
     *
     * The PHP side validates before it touches anything — integrity_check, then that the file
     * actually is an Open Ledger database — so a corrupt or unrelated file is refused while
     * the real ledger is still intact. Stop the server before calling this.
     */
    fun restoreFrom(src: File): String? {
        val (code, output) = cli("--restore", src.absolutePath)
        return if (code == 0) null else output.trim().ifEmpty { "restore failed (exit $code)" }
    }

    /** Run one of index.php's CLI modes against this device's ledger. */
    private fun cli(vararg args: String): Pair<Int, String> {
        val php = File(ctx.applicationInfo.nativeLibraryDir, "libphp.so")
        val p = ProcessBuilder(
            listOf(php.absolutePath, File(docRoot, "index.php").absolutePath) + args
        ).apply {
            environment()["DB_DRIVER"] = "sqlite"
            environment()["DB_PATH"] = dbPath.absolutePath
            redirectErrorStream(true)
        }.start()
        val out = p.inputStream.bufferedReader().readText()
        return p.waitFor() to out
    }

    /**
     * Copy the PHP app out of assets on first run and after every app update.
     *
     * The code ships inside the APK and updates through the Play Store. It is deliberately not
     * fetched from git at runtime: that would be a channel for running new code on a device
     * holding the household's finances, which is the opposite of what this app promises.
     */
    private fun syncAppCode() {
        val stamp = File(ctx.filesDir, "code-version")
        // Keyed on when the package was last installed, NOT on versionCode. versionCode does
        // not move between debug builds, so the device happily kept serving PHP from a
        // previous APK — new CLI modes were simply missing. lastUpdateTime changes on every
        // install and every update, which is exactly the question being asked.
        val current = runCatching {
            ctx.packageManager.getPackageInfo(ctx.packageName, 0).lastUpdateTime.toString()
        }.getOrDefault(BuildConfig.VERSION_CODE.toString())
        if (docRoot.isDirectory && stamp.takeIf { it.exists() }?.readText() == current) return

        docRoot.deleteRecursively()
        docRoot.mkdirs()
        copyAsset("app", docRoot)
        stamp.writeText(current)
    }

    private fun copyAsset(path: String, dest: File) {
        val children = ctx.assets.list(path) ?: emptyArray()
        if (children.isEmpty()) {                       // a file, not a directory
            dest.parentFile?.mkdirs()
            ctx.assets.open(path).use { input -> dest.outputStream().use { input.copyTo(it) } }
            return
        }
        dest.mkdirs()
        children.forEach { copyAsset("$path/$it", File(dest, it)) }
    }

    private fun freePort(): Int = ServerSocket(0).use { it.localPort }

    private fun waitUntilListening(timeoutMs: Long = 10_000) {
        val deadline = System.currentTimeMillis() + timeoutMs
        while (System.currentTimeMillis() < deadline) {
            runCatching { java.net.Socket("127.0.0.1", port).close(); return }
            if (process?.isAlive != true) {
                error("php exited (${process?.exitValue()}): ${logFile.readTextOrEmpty()}")
            }
            Thread.sleep(25)
        }
        error("php did not start listening on $port: ${logFile.readTextOrEmpty()}")
    }

    private fun File.readTextOrEmpty(): String = runCatching { readText().takeLast(2000) }.getOrDefault("")
}
