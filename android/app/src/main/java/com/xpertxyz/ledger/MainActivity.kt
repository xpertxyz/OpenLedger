package com.xpertxyz.ledger

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.graphics.Color
import android.graphics.drawable.ColorDrawable
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.FrameLayout
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.ActivityResultLauncher
import androidx.activity.result.IntentSenderRequest
import androidx.activity.result.contract.ActivityResultContracts
import androidx.biometric.BiometricManager
import androidx.biometric.BiometricManager.Authenticators.BIOMETRIC_STRONG
import androidx.biometric.BiometricManager.Authenticators.DEVICE_CREDENTIAL
import androidx.biometric.BiometricPrompt
import androidx.core.content.ContextCompat
import androidx.core.graphics.ColorUtils
import androidx.core.view.ViewCompat
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.WindowInsetsControllerCompat
import androidx.fragment.app.FragmentActivity
import com.google.android.gms.auth.api.identity.AuthorizationResult
import com.google.android.gms.auth.api.identity.Identity

/**
 * The whole UI: one WebView pointed at the PHP process running inside this app.
 *
 * There is no second copy of the ledger's logic here. Every screen, validation rule and
 * calculation is the same PHP the website serves, so the two cannot drift.
 */
class MainActivity : FragmentActivity() {

    companion object {
        const val RC_DRIVE_SIGN_IN = 4001
        const val RC_APP_UPDATE    = 4002
        private const val PREFS = "ui"
        private const val BAR_COLOR = "bar_color"
    }

    private lateinit var server: PhpServer
    private lateinit var updates: UpdateBridge
    private lateinit var web: WebView
    private lateinit var shell: FrameLayout
    private lateinit var authorizeLauncher: ActivityResultLauncher<IntentSenderRequest>

    private fun prefs() = getSharedPreferences(PREFS, MODE_PRIVATE)

    /**
     * The page is the only thing that knows which of the six palettes is on, and it can change
     * without a reload. A WebView ignores <meta name="theme-color">, so paintStatusBar() in
     * views.php hands the same colour here and this paints what CSS cannot reach.
     */
    inner class ThemeBridge {
        @JavascriptInterface
        fun paint(hex: String) {
            val color = try { Color.parseColor(hex) } catch (e: IllegalArgumentException) { return }
            prefs().edit().putInt(BAR_COLOR, color).apply()
            runOnUiThread { applyBarColor(color) }
        }
    }

    /** The strips behind the system bars, and whether their icons are drawn dark or light. */
    private fun applyBarColor(color: Int) {
        shell.setBackgroundColor(color)
        web.setBackgroundColor(color)
        window.setBackgroundDrawable(ColorDrawable(color))
        // Dark icons on a light bar and light on a dark one. Every palette's --theme-color is
        // far enough from the 0.5 split that nothing sits on the fence.
        val light = ColorUtils.calculateLuminance(color) > 0.5
        WindowInsetsControllerCompat(window, web).apply {
            isAppearanceLightStatusBars = light
            isAppearanceLightNavigationBars = light
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        server = PhpServer(this)
        updates = UpdateBridge(this).also { it.start() }

        // Google's Drive consent coming back. Every branch has to write something down and
        // repaint: a sheet that was dismissed used to leave the panel exactly as it was, which
        // is indistinguishable from a tap that did nothing.
        authorizeLauncher = registerForActivityResult(ActivityResultContracts.StartIntentSenderForResult()) { result ->
            if (result.resultCode != Activity.RESULT_OK) {
                DriveAuth.noteAuthorizationDeclined(this)
            } else {
                try {
                    DriveAuth.noteAuthorizationResult(
                        this,
                        Identity.getAuthorizationClient(this).getAuthorizationResultFromIntent(result.data)
                    )
                } catch (e: Exception) {
                    logErr("Authorization result extraction failed", e)
                    DriveAuth.noteAuthorizationDeclined(this, "Google's reply could not be read. Try again.")
                }
            }
            onDriveAuthDone()
        }

        web = WebView(this).apply {
            settings.javaScriptEnabled = true          // the app's own inline JS, nothing remote
            settings.domStorageEnabled = true
            settings.allowFileAccess = false
            settings.allowContentAccess = false
            // Everything is served from 127.0.0.1; anything else is a link the user tapped and
            // belongs in a browser, not inside a window that holds their ledger.
            webViewClient = object : WebViewClient() {
                override fun shouldOverrideUrlLoading(v: WebView?, url: String?): Boolean {
                    if (url == null) return true
                    // Online mode shows the website, so the website's own pages — and Google's
                    // sign-in, which it redirects to — have to stay inside the WebView. Only
                    // those two hosts: a link to anywhere else is still a link the user tapped
                    // and still belongs in a browser.
                    if (AppMode.isOnline(this@MainActivity)) {
                        if (url.startsWith(AppMode.SITE) ||
                            url.startsWith("https://accounts.google.com/")
                        ) return false
                    } else if (url.startsWith(server.origin)) return false
                    // Hand it to the browser. Returning true on its own claimed the navigation
                    // and then did nothing with it, so every outbound link in the app — the
                    // terms page's GitHub links, the XpertXYZ credit — was a tap that did
                    // nothing at all. http/https only: the WebView should never be able to
                    // fire an arbitrary intent, whatever a page asks for.
                    if (url.startsWith("https://") || url.startsWith("http://")) {
                        runCatching { startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url))) }
                    }
                    return true
                }
            }
            // Without this the WebView swallows every JavaScript error in silence. The backup
            // panel called a toast() that did not exist, so each failure — including the reason
            // a restore stopped — died as an unseen ReferenceError and the buttons looked dead.
            // One line here turns that into `adb logcat -s HLWeb`.
            webChromeClient = object : android.webkit.WebChromeClient() {
                override fun onConsoleMessage(m: android.webkit.ConsoleMessage): Boolean {
                    val where = "${m.sourceId()}:${m.lineNumber()}"
                    if (m.messageLevel() == android.webkit.ConsoleMessage.MessageLevel.ERROR) {
                        android.util.Log.e("HLWeb", "${m.message()} ($where)")
                    } else {
                        android.util.Log.i("HLWeb", "${m.message()} ($where)")
                    }
                    return true
                }
            }
            // The backup panel is rendered by views.php and driven from here, so there is one
            // set of buttons in one design system instead of a native screen that almost
            // matches. Safe because the only page this WebView ever loads is our own, served
            // from loopback behind a per-process token.
            addJavascriptInterface(BackupBridge(this@MainActivity, server), "HLBackup")
            // "Which watches can see this ledger" for the profile drawer. Read-only: it
            // lists nodes and when one last asked for data, and exposes nothing that writes.
            // Both ledgers' drawers use this — the local PHP's and the website's — so that
            // going online is not a one-way door.
            addJavascriptInterface(ModeBridge(this@MainActivity), "HLMode")
            addJavascriptInterface(WearBridge(applicationContext), "HLWear")
            addJavascriptInterface(ThemeBridge(), "HLTheme")
            // Same arrangement for the update bar: Play does the downloading, views.php draws
            // every part of it the user actually sees.
            addJavascriptInterface(updates, "HLUpdate")
        }

        // The insets go on a container, not on the WebView. A WebView accepts setPadding and
        // then lays the page out as if it were not there — the bars still sat on top of the
        // header. A plain FrameLayout honours it, and its background is what paints the strips
        // the padding leaves behind, so they carry whatever palette is on.
        shell = FrameLayout(this).apply { addView(web) }
        setContentView(shell)

        // Edge to edge, then inset the page back out from under the bars. ime() is in the list
        // because a window this size no longer resizes for the keyboard on its own.
        WindowCompat.setDecorFitsSystemWindows(window, false)
        ViewCompat.setOnApplyWindowInsetsListener(shell) { v, insets ->
            val bars = insets.getInsets(
                WindowInsetsCompat.Type.systemBars() or WindowInsetsCompat.Type.ime()
            )
            v.setPadding(bars.left, bars.top, bars.right, bars.bottom)
            WindowInsetsCompat.CONSUMED
        }
        // Last launch's colour, so the bars are already right for the palette the user chose
        // before the page has loaded enough to say so. Organic light is the app's default.
        applyBarColor(prefs().getInt(BAR_COLOR, Color.parseColor("#f5ead8")))

        serve()

        // Back should walk the ledger's own history before it leaves the app.
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (web.canGoBack()) web.goBack() else finish()
            }
        })
    }

    /**
     * Start the interpreter, then point the WebView at it.
     *
     * The port is chosen per launch and the token is minted per process, so both the cookie
     * and the URL have to wait until the server is actually up.
     */
    private fun serve() {
        // Online mode has no local ledger to serve. Starting the interpreter anyway would keep
        // a PHP process and a loopback socket alive for a WebView that never talks to them.
        if (AppMode.isOnline(this)) {
            if (web.url == null || web.url?.startsWith(AppMode.SITE) != true) {
                web.loadUrl(AppMode.SITE + "/")
            }
            return
        }
        serveLocal()
    }

    /**
     * Tear down and come back on the other ledger.
     *
     * recreate() is not enough: the interpreter, the cookie and the WebView's allowed origins
     * all differ between the two, and a restarted process is the one way to be sure none of
     * the previous mode is left running.
     */
    fun restartForModeChange() {
        runCatching { server.stop() }
        val i = Intent(this, MainActivity::class.java)
            .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_NEW_TASK)
        finishAffinity()
        startActivity(i)
    }

    private fun serveLocal() = server.startAsync {
        // The loopback socket is reachable by every other app on this device. This cookie is
        // what separates our WebView from them: index.php rejects any request that cannot
        // present it, and no other app can read this app's cookie jar or guess the value.
        // Set before the first load, so even that request carries it.
        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setCookie("${server.origin}/", "hl_local=${server.token}; Path=/; SameSite=Lax")
            flush()
        }
        // Normally the origin is unchanged — the port is held for the life of the server — so
        // this loads once and never disturbs the page the user was on. The mismatch branch is
        // the safety net for the one case that can move it: the held port being taken by
        // something else while the app was in the background.
        if (web.url == null || web.url?.startsWith(server.origin) != true) {
            web.loadUrl(server.origin + "/")
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // App lock
    //
    // The ledger is one file on this phone and there is no account behind it, so the phone's
    // own lock is the only thing standing between it and whoever is holding the device. It is
    // asked for on every launch and again on every return from the background, the way a
    // payments app does — not once at install.
    //
    // Nothing cryptographic hangs off this: it gates the view, it does not hold the database
    // key. ponytail: the file is protected by app-private storage, which is the same thing
    // that protects it from other apps. If a stolen-and-rooted phone is in the threat model,
    // the answer is a passphrase-derived key on the database itself, not a stronger prompt.
    // ────────────────────────────────────────────────────────────────────

    private var locked = true
    private var prompting = false

    override fun onStart() {
        super.onStart()
        if (locked && !prompting) promptUnlock()
    }

    private fun promptUnlock() {
        // A phone with no fingerprint and no screen lock has nothing to check against.
        // Refusing to open would leave the ledger unreachable rather than protected, so the
        // app opens and the terms page is honest about what that means. Asked here rather
        // than inferred from an error code: authenticate() reports "nothing enrolled" as one
        // of several codes that also cover ordinary failures, and treating those alike either
        // locks the user out or lets a failed attempt through.
        if (BiometricManager.from(this).canAuthenticate(authenticators()) !=
            BiometricManager.BIOMETRIC_SUCCESS) {
            locked = false
            return
        }

        prompting = true
        web.visibility = View.INVISIBLE      // the shell's background is all that shows

        val prompt = BiometricPrompt(this, ContextCompat.getMainExecutor(this),
            object : BiometricPrompt.AuthenticationCallback() {
                override fun onAuthenticationSucceeded(result: BiometricPrompt.AuthenticationResult) {
                    prompting = false
                    locked = false
                    web.visibility = View.VISIBLE
                }

                override fun onAuthenticationError(code: Int, msg: CharSequence) {
                    prompting = false
                    // Cancelled, backed out, or too many failed tries: close, do not sit on a
                    // half-open screen. Reopening asks again.
                    finish()
                }
            })

        prompt.authenticate(
            BiometricPrompt.PromptInfo.Builder()
                .setTitle(getString(R.string.unlock_title))
                .setSubtitle(getString(R.string.unlock_subtitle))
                .apply {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
                        setAllowedAuthenticators(authenticators())
                    } else {
                        // Before API 30 the combined form throws; this deprecated call is the
                        // only way to offer the screen lock as the fallback at all.
                        @Suppress("DEPRECATION")
                        setDeviceCredentialAllowed(true)
                    }
                }
                .build()
        )
    }

    /** Fingerprint or face, with the phone's PIN/pattern/password as the fallback. */
    private fun authenticators() = BIOMETRIC_STRONG or DEVICE_CREDENTIAL

    /**
     * Restarting the interpreter on every resume is also what posts overdue recurring items:
     * the first request after the app opens runs the catch-up sweep, so a phone that was off
     * for a month files that month on launch. No background job, no WorkManager, no cron.
     */
    override fun onResume() {
        super.onResume()
        serve()
        // Also the only way to notice a download that finished while the app was away: Play
        // sends no callback to a process that was not listening at the time.
        updates.refresh()
    }

    /**
     * Google's account chooser coming back.
     */
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: android.content.Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == RC_APP_UPDATE) { updates.onFlowResult(resultCode); return }
    }

    fun launchAuthorization(result: AuthorizationResult) {
        val pendingIntent = result.pendingIntent ?: return
        authorizeLauncher.launch(IntentSenderRequest.Builder(pendingIntent.intentSender).build())
    }

    /** Connected or not, the page has to be repainted — that is where the reason is shown. */
    fun onDriveAuthDone() {
        // Connecting an account is the user saying "keep a copy".
        if (DriveAuth.connectedAccount(this) != null && BackupScheduler.frequency(this) == "off") {
            BackupScheduler.apply(this, "daily")
        }
        web.reload()
    }

    override fun onStop() {
        super.onStop()
        // Re-arm the lock, but not while the prompt itself is up: confirming a PIN on API 30+
        // is a separate system activity, so this fires mid-authentication and would queue a
        // second prompt behind the one the user is already answering.
        if (!prompting) locked = true
        // Nothing should hold a listening socket open while the app is not on screen.
        server.stop()
    }

    override fun onDestroy() {
        updates.stop()
        server.stop()
        super.onDestroy()
    }
}
