package xyz.openledger.app

import android.annotation.SuppressLint
import android.os.Bundle
import android.webkit.CookieManager
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.OnBackPressedCallback

/**
 * The whole UI: one WebView pointed at the PHP process running inside this app.
 *
 * There is no second copy of the ledger's logic here. Every screen, validation rule and
 * calculation is the same PHP the website serves, so the two cannot drift.
 */
class MainActivity : ComponentActivity() {

    companion object { const val RC_DRIVE_SIGN_IN = 4001 }

    private lateinit var server: PhpServer
    private lateinit var web: WebView

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        server = PhpServer(this)

        web = WebView(this).apply {
            settings.javaScriptEnabled = true          // the app's own inline JS, nothing remote
            settings.domStorageEnabled = true
            settings.allowFileAccess = false
            settings.allowContentAccess = false
            // Everything is served from 127.0.0.1; anything else is a link the user tapped and
            // belongs in a browser, not inside a window that holds their ledger.
            webViewClient = object : WebViewClient() {
                override fun shouldOverrideUrlLoading(v: WebView?, url: String?): Boolean =
                    url != null && !url.startsWith(server.origin)
            }
            // The backup panel is rendered by views.php and driven from here, so there is one
            // set of buttons in one design system instead of a native screen that almost
            // matches. Safe because the only page this WebView ever loads is our own, served
            // from loopback behind a per-process token.
            addJavascriptInterface(BackupBridge(this@MainActivity, server), "HLBackup")
        }

        setContentView(web)
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
    private fun serve() = server.startAsync {
        // The loopback socket is reachable by every other app on this device. This cookie is
        // what separates our WebView from them: index.php rejects any request that cannot
        // present it, and no other app can read this app's cookie jar or guess the value.
        // Set before the first load, so even that request carries it.
        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setCookie("${server.origin}/", "hl_local=${server.token}; Path=/; SameSite=Lax")
            flush()
        }
        if (web.url == null) web.loadUrl(server.origin + "/")
    }

    /**
     * Restarting the interpreter on every resume is also what posts overdue recurring items:
     * the first request after the app opens runs the catch-up sweep, so a phone that was off
     * for a month files that month on launch. No background job, no WorkManager, no cron.
     */
    override fun onResume() {
        super.onResume()
        serve()
    }

    /**
     * Google's account chooser coming back. Reloading is enough: the backup panel reads its
     * state from HLBackup.status() on render, so the newly connected account just appears.
     */
    @Deprecated("startActivityForResult is what GoogleSignIn still hands back")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: android.content.Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == RC_DRIVE_SIGN_IN) web.reload()
    }

    override fun onStop() {
        super.onStop()
        // Nothing should hold a listening socket open while the app is not on screen.
        server.stop()
    }

    override fun onDestroy() {
        server.stop()
        super.onDestroy()
    }
}
