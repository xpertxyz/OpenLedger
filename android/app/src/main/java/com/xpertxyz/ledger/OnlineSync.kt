package com.xpertxyz.ledger

import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import androidx.lifecycle.lifecycleScope
import org.json.JSONObject
import java.io.File
import java.net.HttpURLConnection
import java.net.URL
import java.net.URLDecoder

/**
 * A copy of the online ledger, kept on this phone so it can be adopted as the local one.
 *
 * Two ledgers exist in this app and they are deliberately separate: `ledger.db`, the one this
 * phone owns and the only one Drive ever backs up, and `online.db`, a read-only snapshot of
 * whichever online ledger is being viewed. This class only ever writes the second one. The
 * drawer's button is what copies it over the first, and that goes through PHP's `--restore`,
 * which validates the file and keeps the ledger it replaces at `ledger.db.pre-restore`.
 *
 * The snapshot is built by the website, not here: /export/ledger.db flattens the household to
 * a single user carrying the local device's identity, because the local build finds its user
 * by that identity and would otherwise treat an imported ledger as a first run. See
 * buildLocalLedgerExport() in lib.php.
 *
 * The download is Kotlin rather than PHP because the interpreter this app carries is built
 * without OpenSSL and has no https:// stream wrapper at all.
 */
class SyncBridge(private val activity: MainActivity, private val server: PhpServer) {

    companion object {
        private const val PREFS = "online_sync"
        private const val AT = "synced_at"          // epoch ms of the last successful fetch
        private const val ENTRIES = "entries"       // what the snapshot holds
        private const val LEDGER = "ledger"         // its name, for the confirmation
        private const val LOCAL = "local_entries"   // what replacing it would erase
        /** Long enough that a burst of saves is one fetch, short enough to feel live. */
        private const val QUIET_MS = 30_000L
        private val MAGIC = "SQLite format 3".toByteArray()

        fun mirror(ctx: android.content.Context) = File(ctx.filesDir, "online.db")
    }

    private fun prefs() = activity.getSharedPreferences(PREFS, android.content.Context.MODE_PRIVATE)

    @Volatile private var busy = false

    /**
     * Everything the drawer needs to draw itself. Reads preferences and one file stat, so it
     * is safe to answer straight back to the page's own thread.
     */
    @JavascriptInterface
    fun status(): String = JSONObject()
        .put("available", mirror(activity).length() > 0)
        .put("entries", prefs().getInt(ENTRIES, 0))
        .put("ledger", prefs().getString(LEDGER, "") ?: "")
        .put("local", prefs().getInt(LOCAL, -1))
        .put("syncedAt", prefs().getLong(AT, 0L))
        .put("busy", busy)
        .toString()

    /** The page saying something was saved. Fire and forget; the drawer reads status() later. */
    @JavascriptInterface
    fun refresh() = activity.lifecycleScope.launch(Dispatchers.IO) { fetch(force = false) }.let { }

    /**
     * Replace this phone's own ledger with the snapshot, then restart into it.
     *
     * Returns immediately. On success the app restarts, which is the only signal anyone needs;
     * on failure the page is told through window.__hlSyncFailed so the drawer can say why.
     */
    @JavascriptInterface
    fun promote() {
        // A JavascriptInterface is exposed to every page this WebView loads, and online mode
        // can navigate to Google's sign-in. Nothing but our own site may erase a ledger.
        activity.runOnUiThread {
            if (!activity.onSite()) { fail("This can only be done from the ledger itself."); return@runOnUiThread }
            activity.lifecycleScope.launch(Dispatchers.IO) {
                // Freshest copy possible, but a snapshot already on the phone is better than
                // refusing because the network chose this moment to drop.
                fetch(force = true)
                val snapshot = mirror(activity)
                if (snapshot.length() <= 0) {
                    fail("No copy of the online ledger has been downloaded yet.")
                    return@launch
                }
                runCatching { server.stop() }
                val err = server.restoreFrom(snapshot)
                if (err != null) { fail(err); return@launch }
                AppMode.set(activity, AppMode.LOCAL)
                activity.runOnUiThread { activity.restartForModeChange() }
            }
        }
    }

    private fun fail(reason: String) = activity.runOnUiThread {
        activity.tellPage("window.__hlSyncFailed", reason)
    }

    /**
     * Fetch the snapshot beside the real ledger. Returns whether the copy on disk moved.
     *
     * Never on the main thread. Writes to a temporary file and renames only once the bytes
     * look like a database, so a half-finished download or a signed-out redirect can never
     * become the thing the button would later copy over the ledger.
     */
    fun fetch(force: Boolean): Boolean {
        if (!AppMode.isOnline(activity)) return false
        if (busy) return false
        val since = System.currentTimeMillis() - prefs().getLong(AT, 0L)
        if (!force && since < QUIET_MS) return false
        val cookie = CookieManager.getInstance().getCookie(AppMode.SITE) ?: return false
        if (cookie.isBlank()) return false

        busy = true
        val tmp = File(activity.filesDir, "online.db.part")
        try {
            val conn = (URL(AppMode.SITE + "/export/ledger.db").openConnection() as HttpURLConnection).apply {
                // A 302 here is the session having expired: it must read as "no snapshot
                // today", never as a login page saved over the copy we already hold.
                instanceFollowRedirects = false
                connectTimeout = 15_000
                readTimeout = 60_000
                setRequestProperty("Cookie", cookie)
                setRequestProperty("Accept", "application/octet-stream")
            }
            try {
                if (conn.responseCode != 200) {
                    log("sync: server answered ${conn.responseCode}")
                    return false
                }
                tmp.delete()
                conn.inputStream.use { input -> tmp.outputStream().use { input.copyTo(it) } }
                if (!looksLikeSqlite(tmp)) { log("sync: reply was not a database"); return false }

                val dest = mirror(activity)
                // The -wal and -shm would belong to the file being replaced. PHP writes this
                // snapshot with journal_mode=DELETE so there should be none, and any left by
                // an older build must not survive to be replayed over the new one.
                for (sfx in listOf("", "-wal", "-shm")) File(dest.path + sfx).delete()
                if (!tmp.renameTo(dest)) { log("sync: could not put the snapshot in place"); return false }

                // What promoting would erase. Counted here rather than in status() because it
                // costs an interpreter launch, which has no business happening while the drawer
                // is opening — and guarded, because the snapshot is already on disk and a
                // ledger that cannot be counted must not leave it sitting there undescribed.
                val local = runCatching { server.entryCount() }.getOrDefault(-1)
                prefs().edit()
                    .putLong(AT, System.currentTimeMillis())
                    .putInt(ENTRIES, conn.getHeaderField("X-Ledger-Entries")?.toIntOrNull() ?: 0)
                    .putString(LEDGER, decodeName(conn.getHeaderField("X-Ledger-Name")))
                    .putInt(LOCAL, local)
                    .apply()
                log("sync: snapshot updated (${dest.length()} bytes)")
                return true
            } finally {
                conn.disconnect()
            }
        } catch (e: Exception) {
            // Offline, a dropped connection, a server having a bad day. The copy already on
            // the phone stays exactly as it was, which is the entire point of keeping one.
            log("sync: ${e.message ?: e.javaClass.simpleName}")
            return false
        } finally {
            tmp.delete()
            busy = false
        }
    }

    private fun looksLikeSqlite(f: File): Boolean {
        if (f.length() < 512) return false
        return runCatching {
            f.inputStream().use { input ->
                val head = ByteArray(MAGIC.size)
                input.read(head) == MAGIC.size && head.contentEquals(MAGIC)
            }
        }.getOrDefault(false)
    }

    private fun decodeName(raw: String?): String =
        runCatching { URLDecoder.decode(raw ?: "", "UTF-8") }.getOrDefault("")
}
