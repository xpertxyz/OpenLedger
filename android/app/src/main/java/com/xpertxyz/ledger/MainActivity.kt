package com.xpertxyz.ledger

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.graphics.Color
import android.graphics.drawable.ClipDrawable
import android.graphics.drawable.ColorDrawable
import android.graphics.drawable.GradientDrawable
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.view.Gravity
import android.view.View
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Button
import android.widget.FrameLayout
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
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
import androidx.lifecycle.lifecycleScope
import kotlinx.coroutines.launch
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
        private const val ACCENT_COLOR = "accent_color"
    }

    private lateinit var server: PhpServer
    private lateinit var updates: UpdateBridge
    private lateinit var web: WebView
    private lateinit var shell: FrameLayout
    private lateinit var pull: SwipeRefreshLayout
    // The two things drawn over the page rather than by it: the page-load bar, and the in-app
    // update strip. Native so they exist on every screen the WebView can show.
    private lateinit var progress: ProgressBar
    private lateinit var upd: LinearLayout
    private lateinit var updText: TextView
    private lateinit var updBar: ProgressBar
    private lateinit var updLater: Button
    private lateinit var updGo: Button
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

        /** The palette's accent, for the native strips that have no CSS to read it from. */
        @JavascriptInterface
        fun accent(hex: String) {
            val color = try { Color.parseColor(hex) } catch (e: IllegalArgumentException) { return }
            prefs().edit().putInt(ACCENT_COLOR, color).apply()
            runOnUiThread { applyAccent(color) }
        }
    }

    /**
     * The page telling the app when a pull-down must not mean "refresh".
     *
     * The drawer is a fixed overlay with its own scroll, so the document underneath stays at
     * scrollY 0 while it is open — and the native gesture, which has no idea the overlay
     * exists, would fire a refresh in the middle of scrolling it. The page knows; this is how
     * it says so.
     */
    inner class UiBridge {
        @JavascriptInterface
        fun pull(enabled: Boolean) = runOnUiThread {
            // Qualified: this method's own name shadows the property inside the inner class.
            if (this@MainActivity::pull.isInitialized) this@MainActivity.pull.isEnabled = enabled
        }
    }

    /** The strips behind the system bars, and whether their icons are drawn dark or light. */
    private fun applyBarColor(color: Int) {
        // The refresh spinner is native, so it does not inherit the page's palette. paintStatusBar()
        // in views.php already hands over the one colour that tracks the theme; the arrow is
        // then whichever of dark/light actually reads on it.
        val fg = if (ColorUtils.calculateLuminance(color) > 0.5) 0xFF201E1D.toInt() else 0xFFF3E9D8.toInt()
        if (::pull.isInitialized) {
            pull.setProgressBackgroundColorSchemeColor(color)
            pull.setColorSchemeColors(fg)
        }
        if (::upd.isInitialized) {
            updText.setTextColor(fg)
            // Near enough the page's surface tone; the strip has no stylesheet to take it from.
            upd.background = GradientDrawable().apply {
                cornerRadius = dp(12).toFloat()
                setColor(ColorUtils.blendARGB(color, fg, 0.07f))
            }
            updBar.background = ColorDrawable(ColorUtils.blendARGB(color, fg, 0.18f))
        }
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

    /** The accent goes on the two bars and the strip's buttons — everything that must read as "ours". */
    private fun applyAccent(accent: Int) {
        if (!::upd.isInitialized) return
        progress.progressDrawable = ClipDrawable(ColorDrawable(accent), Gravity.START, ClipDrawable.HORIZONTAL)
        updBar.progressDrawable = ClipDrawable(ColorDrawable(accent), Gravity.START, ClipDrawable.HORIZONTAL)
        updLater.setTextColor(accent)
        updGo.setTextColor(accent)
    }

    private fun dp(v: Int) = (v * resources.displayMetrics.density).toInt()

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        server = PhpServer(this)
        updates = UpdateBridge(this, ::renderUpdate).also { it.start() }

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
                // Not on onPageStarted's timer or a delay: the spinner belongs on screen for
                // exactly as long as the load takes, which only the WebView knows.
                override fun onPageFinished(view: WebView?, url: String?) {
                    pull.isRefreshing = false
                }

                override fun onReceivedError(
                    view: WebView?,
                    request: android.webkit.WebResourceRequest?,
                    error: android.webkit.WebResourceError?,
                ) {
                    if (request?.isForMainFrame != true) return
                    // A failed refresh must still end. Otherwise the one time the network is
                    // down is the one time the spinner never stops.
                    pull.isRefreshing = false
                    // Chrome's grey "Webpage not available" is the one screen in the app that
                    // wears no theme. Once the service worker is installed the site answers
                    // this itself (sw.js serves /offline); this is for the launches before
                    // that — the first, and any before the site has ever been reached. Local
                    // mode is left to its own errors: nothing there is "offline".
                    val url = request.url.toString()
                    if (AppMode.isOnline(this@MainActivity) && url.startsWith(AppMode.SITE)) {
                        view?.loadDataWithBaseURL(url, offlineHtml(url), "text/html", "utf-8", url)
                    }
                }

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
                // The thin bar under the status bar a browser draws for a page on its way. A
                // WebView has no chrome and drew nothing, so the seconds a slow page takes read
                // as a tap that did nothing.
                override fun onProgressChanged(view: WebView, p: Int) {
                    // Qualified: inside apply{} on the WebView, `progress` is its own getProgress().
                    this@MainActivity.progress.progress = p
                    this@MainActivity.progress.visibility = if (p < 100) View.VISIBLE else View.GONE
                }

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
            // Google will not render its own button inside a WebView, so the app asks Google
            // natively and hands the page the token. See AuthBridge.
            addJavascriptInterface(AuthBridge(this@MainActivity), "HLAuth")
            addJavascriptInterface(WearBridge(applicationContext), "HLWear")
            addJavascriptInterface(ThemeBridge(), "HLTheme")
            addJavascriptInterface(UiBridge(), "HLUi")
        }

        // The insets go on a container, not on the WebView. A WebView accepts setPadding and
        // then lays the page out as if it were not there — the bars still sat on top of the
        // header. A plain FrameLayout honours it, and its background is what paints the strips
        // the padding leaves behind, so they carry whatever palette is on.
        // Pull-to-refresh, which the page cannot provide for itself: a browser's pull gesture
        // is chrome, and a WebView has none.
        pull = SwipeRefreshLayout(this).apply {
            addView(web)
            // The document scrolls, not an inner container, so scrollY answers "is there
            // anything above this" — which is the only condition under which a downward drag
            // should mean refresh rather than scroll.
            setOnChildScrollUpCallback { _, _ -> web.scrollY > 0 }
            setOnRefreshListener { web.reload() }
        }
        progress = ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal).apply {
            max = 100
            minimumHeight = 0
            visibility = View.GONE
            layoutParams = FrameLayout.LayoutParams(FrameLayout.LayoutParams.MATCH_PARENT, dp(3), Gravity.TOP)
        }
        // The in-app update strip. Over the page rather than in it, so it is there on the
        // sign-in page, the terms, the offline page, and in online mode before the website has
        // been deployed with the same views.php as the app. See UpdateBridge.
        updText = TextView(this).apply { textSize = 13f }
        updBar = ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal).apply {
            max = 100
            minimumHeight = 0
            visibility = View.GONE
            layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, dp(4))
                .apply { topMargin = dp(8) }
        }
        updLater = Button(this, null, android.R.attr.borderlessButtonStyle).apply {
            setOnClickListener { updates.dismiss() }
        }
        updGo = Button(this, null, android.R.attr.borderlessButtonStyle).apply {
            setOnClickListener { if (updates.state == "downloaded") updates.install() else updates.begin() }
        }
        upd = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            visibility = View.GONE
            elevation = dp(6).toFloat()
            setPadding(dp(16), dp(12), dp(8), dp(2))
            layoutParams = FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.WRAP_CONTENT, Gravity.TOP
            ).apply { setMargins(dp(12), dp(12), dp(12), 0) }
            addView(updText)
            addView(updBar)
            addView(LinearLayout(context).apply { gravity = Gravity.END; addView(updLater); addView(updGo) })
        }
        shell = FrameLayout(this).apply { addView(pull); addView(progress); addView(upd) }
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
        applyAccent(prefs().getInt(ACCENT_COLOR, Color.parseColor("#c67139")))

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
    /**
     * Ask Google for an ID token and give it to the page.
     *
     * The page turns it into a session by posting to /signin/app, which verifies it exactly as
     * it verifies the token GIS produces on the web. Nothing about the session differs
     * afterwards — this only replaces the button Google refuses to draw here.
     */
    fun startNativeSignIn() {
        lifecycleScope.launch {
            val token = DriveAuth.idToken(this@MainActivity)
            // A cancelled sheet is an answer. The page is told, so it can stop showing a
            // spinner, and is told nothing else — there is no error worth spelling out.
            val js = if (token.isNullOrBlank()) {
                "window.__hlGoogleToken && window.__hlGoogleToken('')"
            } else {
                "window.__hlGoogleToken && window.__hlGoogleToken(" +
                    org.json.JSONObject.quote(token) + ")"
            }
            web.evaluateJavascript(js, null)
        }
    }

    /** Draw whatever Play is holding, or nothing. UpdateBridge calls this on every change. */
    private fun renderUpdate() {
        if (!::upd.isInitialized) return
        val s = updates.shown
        upd.visibility = if (s.isEmpty()) View.GONE else View.VISIBLE
        if (s.isEmpty()) return
        val pct  = if (updates.total > 0) (updates.bytes * 100 / updates.total).toInt().coerceIn(0, 100) else 0
        val size = if (updates.total > 0) " · %.1f MB".format(updates.total / 1048576.0) else ""
        updBar.visibility = if (s == "downloading") View.VISIBLE else View.GONE
        updBar.progress = pct
        val (text, later, go) = when (s) {
            "available"   -> Triple("Update available$size", "Later", "Update")
            "downloading" -> Triple("Downloading update · $pct% — carry on, this runs in the background", null, null)
            "downloaded"  -> Triple("Update ready · restart to finish installing it", "Later", "Restart")
            else          -> Triple(updates.error.ifEmpty { "Update failed" }, "Dismiss", "Try again")
        }
        updText.text = text
        updLater.text = later
        updLater.visibility = if (later == null) View.GONE else View.VISIBLE
        updGo.text = go
        updGo.visibility = if (go == null) View.GONE else View.VISIBLE
    }

    /**
     * The offline page for before a service worker exists, in the last colours the page handed
     * over. Loaded with the failed URL as both base and history entry, so "Try again" and the
     * online listener are plain navigations back to it, and onResume sees a site URL and leaves
     * the WebView alone.
     */
    private fun offlineHtml(url: String): String {
        val bg     = prefs().getInt(BAR_COLOR, Color.parseColor("#f5ead8"))
        val accent = prefs().getInt(ACCENT_COLOR, Color.parseColor("#c67139"))
        val fg     = if (ColorUtils.calculateLuminance(bg) > 0.5) "#201e1d" else "#f3e9d8"
        val hex    = { c: Int -> String.format("#%06x", c and 0xFFFFFF) }
        val href   = org.json.JSONObject.quote(url)
        return """<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title>
<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;font-family:system-ui,sans-serif;background:${hex(bg)};color:$fg}
.c{max-width:320px;padding:24px}.c svg{width:56px;height:56px;margin-bottom:16px;color:${hex(accent)}}
h1{font-size:22px;margin:0 0 8px}p{opacity:.72;font-size:14px;line-height:1.5;margin:0 0 20px}
a{display:inline-block;background:${hex(accent)};color:${hex(bg)};padding:10px 22px;border-radius:999px;text-decoration:none;font-weight:600}</style></head>
<body><div class="c"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.56 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
<h1>You're offline</h1><p>Open Ledger can't be reached right now. It will come back on its own when the connection does.</p><a href=$href>Try again</a></div>
<script>addEventListener('online',function(){location.href=$href})</script></body></html>"""
    }

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
    // asked for once per launch: when the activity is created, and not again on every return
    // from Recents — that was a prompt on every glance at another app, and it made the lock
    // feel like the app arguing with its owner. Backgrounded, the phone's own lock screen is
    // what stands guard; a fresh process starts locked again.
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
        // The lock is not re-armed here: once unlocked, this process stays unlocked. (It used
        // to be, and confirming a PIN on API 30+ is a separate system activity — so this fired
        // mid-authentication too, which is why `prompting` is still checked in onStart.)
        // Nothing should hold a listening socket open while the app is not on screen.
        server.stop()
    }

    override fun onDestroy() {
        updates.stop()
        server.stop()
        super.onDestroy()
    }
}
