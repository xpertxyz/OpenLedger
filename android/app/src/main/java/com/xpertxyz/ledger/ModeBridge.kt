package com.xpertxyz.ledger

import android.webkit.JavascriptInterface
import org.json.JSONObject

/**
 * The drawer's "which ledger am I on" control, for both sides of the switch.
 *
 * Reachable from the local build's own PHP *and* from the website when it is being shown
 * inside this app — otherwise going online would be a one-way door, since the website has no
 * idea the local ledger exists. views.php renders the item; this performs it.
 */
class ModeBridge(private val activity: MainActivity) {

    @JavascriptInterface
    fun status(): String = JSONObject()
        .put("mode", AppMode.current(activity))
        .put("termsAccepted", AppMode.termsAcceptedAt(activity) > 0L)
        .put("site", AppMode.SITE)
        .toString()

    /** Records that the terms were read. Separate call, so the page can gate the switch on it. */
    @JavascriptInterface
    fun acceptTerms() = AppMode.acceptTerms(activity)

    /**
     * Switch and restart.
     *
     * A restart rather than a reload: the two modes differ in whether a PHP interpreter is
     * running, which cookie the WebView carries and which origins it may navigate to. Rebuilding
     * that in place would be three subtle teardowns to get right for something that happens
     * about twice in the life of an install.
     */
    @JavascriptInterface
    fun switchTo(mode: String) {
        if (!AppMode.set(activity, mode)) return   // online without terms — the page must ask first
        activity.runOnUiThread { activity.restartForModeChange() }
    }
}
