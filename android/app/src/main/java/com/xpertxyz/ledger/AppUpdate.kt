package com.xpertxyz.ledger

import android.app.Activity
import android.webkit.JavascriptInterface
import com.google.android.play.core.appupdate.AppUpdateManager
import com.google.android.play.core.appupdate.AppUpdateManagerFactory
import com.google.android.play.core.appupdate.AppUpdateOptions
import com.google.android.play.core.install.InstallStateUpdatedListener
import com.google.android.play.core.install.model.AppUpdateType
import com.google.android.play.core.install.model.InstallStatus
import com.google.android.play.core.install.model.UpdateAvailability
import org.json.JSONObject

/**
 * In-app updates, wearing this app's own clothes.
 *
 * Play offers two flows. The *immediate* one is a full-screen Google-blue takeover that cannot
 * be themed and holds the ledger hostage until it finishes. This is the *flexible* one: Play
 * downloads in the background, and every piece of UI around that — the offer, the progress bar,
 * the restart prompt — is drawn by views.php out of the same tokens as the rest of the app, the
 * same way the backup panel is. The one screen Google keeps is the single confirmation dialog
 * asking permission to download, which is not ours to skip.
 *
 * Nothing here throws where there is no Play Store to ask: a debug build, a sideloaded APK and
 * a device without Play all land in [refresh]'s failure branch, leave `state` empty, and the
 * page draws nothing at all.
 */
class UpdateBridge(private val activity: Activity) {

    private val manager: AppUpdateManager by lazy { AppUpdateManagerFactory.create(activity) }

    // Written on Play's callback thread, read on the WebView's JS thread.
    @Volatile private var state = ""          // "" | available | downloading | downloaded | failed
    @Volatile private var version = 0
    @Volatile private var bytes = 0L
    @Volatile private var total = 0L
    @Volatile private var error = ""
    // Session-only on purpose. "Not now" should mean not now, not never — the offer comes back
    // the next time the app is opened, which is roughly when someone is willing to hear it.
    @Volatile private var dismissed = false

    private val listener = InstallStateUpdatedListener { s ->
        when (s.installStatus()) {
            InstallStatus.DOWNLOADING -> {
                state = "downloading"
                bytes = s.bytesDownloaded()
                total = s.totalBytesToDownload()
            }
            // Play reports 100% a moment before it reports DOWNLOADED; without this the bar
            // sticks at 99% for the last beat.
            InstallStatus.DOWNLOADED -> { state = "downloaded"; if (total > 0) bytes = total }
            InstallStatus.FAILED -> {
                state = "failed"
                error = "Download failed (" + s.installErrorCode() + ")"
            }
            InstallStatus.CANCELED -> { state = if (version > 0) "available" else ""; bytes = 0 }
            else -> {}
        }
    }

    // Guarded because this runs in onCreate: a device with no Play Store at all should get a
    // ledger with no update bar, not a launch crash.
    fun start() { runCatching { manager.registerListener(listener) } }
    fun stop()  { runCatching { manager.unregisterListener(listener) } }

    /**
     * Ask Play what it is holding. Called on every resume, because a download that finished
     * while the app was in the background leaves no callback behind — only this to notice it.
     */
    fun refresh() {
        val task = runCatching { manager.appUpdateInfo }.getOrNull() ?: return
        task
            .addOnSuccessListener { info ->
                version = info.availableVersionCode()
                bytes = info.bytesDownloaded()
                total = info.totalBytesToDownload()
                state = when {
                    info.installStatus() == InstallStatus.DOWNLOADED  -> "downloaded"
                    info.installStatus() == InstallStatus.DOWNLOADING -> "downloading"
                    info.updateAvailability() == UpdateAvailability.UPDATE_AVAILABLE
                        && info.isUpdateTypeAllowed(AppUpdateType.FLEXIBLE) -> "available"
                    // A failure the user has already been shown outlives one poll: replacing it
                    // with "" here would blank the message before anyone could read it.
                    state == "failed" -> "failed"
                    else -> ""
                }
            }
            .addOnFailureListener { e ->
                // Not worth showing. A debug build, a sideload, or a device with no Play Store
                // has nothing to compare against and answers this way every single time.
                log("update check unavailable: " + (e.message ?: e.javaClass.simpleName))
            }
    }

    /** The chooser coming back. Declining is an answer, not a failure. */
    fun onFlowResult(resultCode: Int) {
        if (resultCode != Activity.RESULT_OK && state == "available") dismissed = true
    }

    /** Everything the page needs to draw itself, as JSON. */
    @JavascriptInterface
    fun status(): String = JSONObject().apply {
        put("state", if (dismissed && state == "available") "" else state)
        put("version", version)
        put("bytes", bytes)
        put("total", total)
        put("error", error)
    }.toString()

    /** Play's own download-permission dialog, then the download runs in the background. */
    @JavascriptInterface
    fun begin() {
        activity.runOnUiThread {
            val task = runCatching { manager.appUpdateInfo }.getOrNull() ?: return@runOnUiThread
            task.addOnSuccessListener { info ->
                if (info.updateAvailability() != UpdateAvailability.UPDATE_AVAILABLE) return@addOnSuccessListener
                if (!info.isUpdateTypeAllowed(AppUpdateType.FLEXIBLE)) return@addOnSuccessListener
                runCatching {
                    manager.startUpdateFlowForResult(
                        info,
                        activity,
                        AppUpdateOptions.newBuilder(AppUpdateType.FLEXIBLE).build(),
                        MainActivity.RC_APP_UPDATE
                    )
                }.onFailure {
                    logErr("could not start the update flow", it)
                    error = it.message ?: "Could not start the update"
                    state = "failed"
                }
            }
        }
    }

    /** Restart into the downloaded version. Play replaces the app and relaunches it. */
    @JavascriptInterface
    fun install() { activity.runOnUiThread { manager.completeUpdate() } }

    @JavascriptInterface
    fun dismiss() { dismissed = true }
}
