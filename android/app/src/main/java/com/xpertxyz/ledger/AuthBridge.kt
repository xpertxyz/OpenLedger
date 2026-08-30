package com.xpertxyz.ledger

import android.webkit.JavascriptInterface

/**
 * Signing into the website from inside the app.
 *
 * Google Identity Services refuses to render inside an embedded WebView — it detects the user
 * agent and simply does not draw the button, which is why the sign-in vanished the moment the
 * app switched to online mode. That is Google's policy, not a bug in the page, and no amount
 * of CSS brings the button back.
 *
 * So the app signs in natively instead, through the Credential Manager it already uses for
 * Drive, and hands the resulting Google ID token to the page. The page posts it to
 * /signin/app exactly as the web form posts the token GIS would have produced — same
 * verification, same session, same everything after that point. The only difference is which
 * component asked Google.
 */
class AuthBridge(private val activity: MainActivity) {

    /** Whether a native sign-in is possible at all. False leaves the page's own GIS block up. */
    @JavascriptInterface
    fun available(): Boolean = DriveAuth.isConfigured

    /**
     * Opens the account sheet. Asynchronous: the token comes back to the page through
     * `window.__hlGoogleToken(token)`, because the sheet is an activity result and there is
     * nothing to return synchronously.
     */
    @JavascriptInterface
    fun signIn() = activity.startNativeSignIn()
}
