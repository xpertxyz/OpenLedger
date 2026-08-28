package com.xpertxyz.ledger

import android.app.Activity
import android.content.Context
import android.webkit.JavascriptInterface
import androidx.fragment.app.FragmentActivity
import androidx.lifecycle.lifecycleScope
import kotlinx.coroutines.launch
import org.json.JSONObject

/**
 * The backup panel's UI lives in the shared PHP, not in a native screen.
 *
 * That is the whole point of this app: one codebase, one design system. A native settings
 * screen would mean maintaining a second set of buttons that look almost — but never quite —
 * like the ledger's own. So `views.php` renders the panel behind the `FEATURE_BACKUP` flag,
 * and it calls the methods below through this bridge.
 *
 * Exposed only in the local build, and only over 127.0.0.1 behind the loopback token. Every
 * method here is callable by any JavaScript in the WebView, so each one is deliberately
 * narrow: no method takes a path, a URL, or anything else the page could point somewhere
 * unexpected. `restore()` cannot be aimed at an arbitrary file — it always means "the one
 * backup in this app's Drive appDataFolder".
 */
class BackupBridge(private val activity: Activity, private val server: PhpServer) {

    private val ctx: Context get() = activity.applicationContext

    /** Everything the panel needs to render itself, as JSON. */
    @JavascriptInterface
    fun status(): String {
        val prefs = ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
        return JSONObject().apply {
            put("configured", DriveAuth.isConfigured)
            put("account", DriveAuth.connectedAccount(ctx) ?: "")
            put("frequency", BackupScheduler.frequency(ctx))
            put("lastOk", prefs.getLong(BackupNames.KEY_LAST_OK, 0L))
            put("lastError", prefs.getString(BackupNames.KEY_LAST_ERROR, "") ?: "")
            put("lastNote", prefs.getString(BackupNames.KEY_LAST_NOTE, "") ?: "")
            put("encrypted", BackupCrypto.isEnabled())
        }.toString()
    }

    /**
     * Turn on passphrase encryption. The passphrase is never stored — only the key it derives,
     * inside the Android Keystore — so this is the last time the app can see it.
     */
    @JavascriptInterface
    fun setPassphrase(passphrase: String): String {
        if (passphrase.length < 8) return "Use at least 8 characters"
        return try { BackupCrypto.enable(ctx, passphrase); "" }
        catch (e: Exception) { e.message ?: "Could not set the passphrase" }
    }

    /**
     * Stop encrypting future backups. Deliberately does not touch what is already in Drive:
     * that blob is still sealed with the old key, and silently orphaning it would leave the
     * user believing they have a restorable backup when the key is gone.
     */
    @JavascriptInterface
    fun clearPassphrase() = BackupCrypto.disable(ctx)

    /** Opens Google's account chooser. */
    @JavascriptInterface
    fun connect() {
        if (!DriveAuth.isConfigured) return
        (activity as? FragmentActivity)?.let { fa ->
            fa.lifecycleScope.launch {
                val email = DriveAuth.signIn(ctx)
                if (email != null) {
                    DriveAuth.authorize(ctx, fa)
                }
            }
        }
    }

    @JavascriptInterface
    fun disconnect() {
        DriveAuth.disconnect(ctx)
        BackupScheduler.apply(ctx, "off")
    }

    /** "off" | "daily" | "weekly" — anything else is ignored rather than trusted. */
    @JavascriptInterface
    fun setFrequency(frequency: String) {
        if (frequency !in setOf("off", "daily", "weekly")) return
        BackupScheduler.apply(ctx, frequency)
    }

    @JavascriptInterface
    fun backupNow() { log("bridge: backupNow"); BackupScheduler.runNow(ctx) }

    /**
     * Fetch the Drive backup and replace the ledger with it.
     *
     * Blocking on purpose: it is called from a WebView JS thread, never the main thread, and
     * the page shows a confirm dialog first. Returns "" on success or the reason it stopped —
     * including the sentinel "PASSPHRASE_REQUIRED", which tells the page to ask and try again.
     */
    @JavascriptInterface
    fun restore(passphrase: String): String {
        log("bridge: restore")
        return DriveRestore.run(ctx, server, passphrase) ?: ""
    }
}
