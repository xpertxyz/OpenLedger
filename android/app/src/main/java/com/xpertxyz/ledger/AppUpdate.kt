package com.xpertxyz.ledger

import android.app.Activity
import com.google.android.play.core.appupdate.AppUpdateManager
import com.google.android.play.core.appupdate.AppUpdateManagerFactory
import com.google.android.play.core.appupdate.AppUpdateOptions
import com.google.android.play.core.install.InstallStateUpdatedListener
import com.google.android.play.core.install.model.AppUpdateType
import com.google.android.play.core.install.model.InstallStatus
import com.google.android.play.core.install.model.UpdateAvailability

/**
 * In-app updates, wearing this app's own clothes.
 *
 * Play offers two flows. The *immediate* one is a full-screen Google-blue takeover that cannot
 * be themed and holds the ledger hostage until it finishes. This is the *flexible* one: Play
 * downloads in the background, and every piece of UI around that — the offer, the progress bar,
 * the restart prompt — is one native strip MainActivity draws over the WebView, in the colours
 * the page hands it. Native rather than in views.php, so that it is there on every screen the
 * WebView can show: the sign-in page, the terms, the offline page, and a website deployed
 * before the app was. The one screen Google keeps is the single confirmation dialog asking
 * permission to download, which is not ours to skip.
 *
 * Nothing here throws where there is no Play Store to ask: a debug build, a sideloaded APK and
 * a device without Play all land in [refresh]'s failure branch, leave `state` empty, and the
 * strip stays hidden.
 */
class UpdateBridge(private val activity: Activity, private val onChange: () -> Unit) {

    private val manager: AppUpdateManager by lazy { AppUpdateManagerFactory.create(activity) }

    // Written on Play's callback thread, read on the main thread.
    @Volatile var state = ""                  // "" | available | downloading | downloaded | failed
        private set
    @Volatile var bytes = 0L
        private set
    @Volatile var total = 0L
        private set
    @Volatile var error = ""
        private set
    @Volatile private var version = 0
    // Session-only on purpose. "Not now" should mean not now, not never — the offer comes back
    // the next time the app is opened, which is roughly when someone is willing to hear it.
    @Volatile private var dismissed = false

    /** What the strip should show. Empty means nothing at all. */
    val shown: String get() = if (dismissed && state == "available") "" else state

    private fun changed() = activity.runOnUiThread(onChange)

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
        changed()
    }

    // Guarded because this runs in onCreate: a device with no Play Store at all should get a
    // ledger with no update strip, not a launch crash.
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
                changed()
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
        changed()
    }

    /** Play's own download-permission dialog, then the download runs in the background. */
    fun begin() {
        val task = runCatching { manager.appUpdateInfo }.getOrNull() ?: return
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
                changed()
            }
        }
    }

    /** Restart into the downloaded version. Play replaces the app and relaunches it. */
    fun install() { manager.completeUpdate() }

    fun dismiss() { dismissed = true; changed() }
}
