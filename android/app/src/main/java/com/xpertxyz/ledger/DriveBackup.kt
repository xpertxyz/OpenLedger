package com.xpertxyz.ledger

import android.content.Context
import androidx.work.*
import kotlinx.coroutines.sync.withLock
import com.google.api.client.http.FileContent
import com.google.api.services.drive.Drive
import com.google.api.services.drive.model.File as DriveFile
import java.io.File
import java.util.concurrent.TimeUnit
import java.util.zip.GZIPInputStream
import java.util.zip.GZIPOutputStream

/**
 * Back the ledger up to the user's own Google Drive, and put it back.
 *
 * Two decisions here matter more than the code:
 *
 * 1. **appDataFolder scope, never full Drive.** `DriveScopes.DRIVE` and `DRIVE_READONLY` are
 *    Google *restricted* scopes: shipping either means an annual CASA security assessment —
 *    real money, weeks of process. `DRIVE_APPDATA` is not restricted, is invisible in the
 *    user's Drive UI, and can only see files this app created. Strictly less power, strictly
 *    less paperwork.
 *
 * 2. **VACUUM INTO, never a file copy.** Copying a live SQLite database captures a torn file
 *    plus a hot -wal and restores as corruption. The snapshot comes from `index.php --backup`,
 *    which is one statement and always consistent.
 *
 * The upload is the only time ledger data leaves the device, it goes to the user's own
 * account, and it happens because they asked for it.
 */
object BackupNames {
    const val REMOTE = "openledger-backup.db.gz"
    const val WORK_PERIODIC = "openledger-backup-periodic"
    const val WORK_ONCE = "openledger-backup-now"
    const val PREFS = "backup"
    const val KEY_FREQUENCY = "frequency"     // off | daily | weekly
    const val KEY_LAST_OK = "last_ok"
    const val KEY_LAST_ERROR = "last_error"
    // A run that declined to upload. Kept apart from KEY_LAST_ERROR so the panel can say what
    // happened without painting an intact backup red.
    const val KEY_LAST_NOTE = "last_note"
}

/**
 * One tag for the whole backup path, so the entire round trip reads back with
 *
 *     adb logcat -s HLBackup
 *
 * Every step that can fail logs what it saw, not just that it failed. The bugs in this path so
 * far — two workers deleting each other's temp files, a restore that put back an empty snapshot
 * — were both invisible from the outside and both obvious from one line of "how many bytes".
 * Sizes and counts are the useful facts here; no entry text or account data is ever logged.
 */
internal const val TAG = "HLBackup"

internal fun log(msg: String) { android.util.Log.i(TAG, msg) }
internal fun logErr(msg: String, e: Throwable? = null) { android.util.Log.e(TAG, msg, e) }

class DriveBackupWorker(ctx: Context, params: WorkerParameters) : CoroutineWorker(ctx, params) {

    companion object {
        /**
         * "Back up now" and the daily run are separate unique work names, so WorkManager is
         * free to run both at once — and connecting Drive enqueues one of each. They share the
         * cache filenames below, and the first to finish deletes the other's file mid-flight:
         * "ledger-backup.db.gz: open failed: ENOENT". Both run in this process, so one lock
         * settles it, and the second run finds a fresh snapshot to take anyway.
         */
        private val lock = kotlinx.coroutines.sync.Mutex()
    }

    override suspend fun doWork(): Result = lock.withLock { backup() }

    private suspend fun backup(): Result {
        val ctx = applicationContext
        val prefs = ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
        val snapshot = File(ctx.cacheDir, "ledger-backup.db")
        val gz = File(ctx.cacheDir, "ledger-backup.db.gz")
        val sealed = File(ctx.cacheDir, "ledger-backup.sealed")
        log("backup: start (attempt ${runAttemptCount + 1})")
        try {
            val drive = DriveAuth.drive(ctx) ?: return fail(prefs, "No Google account connected")

            val php = PhpServer(ctx)
            php.backupTo(snapshot)?.let { return fail(prefs, it) }
            val entries = php.entryCount()
            log("backup: snapshot ${snapshot.length()} bytes, $entries entries")

            // The one upload this app must never perform. An empty ledger over a backup that
            // holds something is not a backup, it is the deletion of the only copy — and it is
            // the *likely* case, because a ledger is empty exactly when the user has just set
            // the app up again and is about to restore. Cheap to check, and there is no version
            // of "the user meant this" that is worth the alternative.
            val existing = remoteId(drive)
            if (entries == 0 && existing != null) {
                return skip(prefs, "This ledger is empty — the backup in Drive was left alone. "
                    + "Restore it first, or add an entry and back up again.")
            }

            // A ledger is repetitive text and compresses to a fraction of its size, which
            // matters on mobile data. Compress BEFORE encrypting — ciphertext does not
            // compress, so the other order would upload the full-size database.
            snapshot.inputStream().use { input ->
                GZIPOutputStream(gz.outputStream()).use { input.copyTo(it) }
            }

            val payload = if (BackupCrypto.isEnabled()) {
                BackupCrypto.encrypt(ctx, gz, sealed); sealed
            } else gz
            log("backup: uploading ${payload.name}, ${payload.length()} bytes"
                + if (BackupCrypto.isEnabled()) " (encrypted)" else "")

            upload(drive, payload, existing)
            prefs.edit()
                .putLong(BackupNames.KEY_LAST_OK, System.currentTimeMillis())
                .remove(BackupNames.KEY_LAST_ERROR)
                .remove(BackupNames.KEY_LAST_NOTE)
                .apply()
            log("backup: done")
            return Result.success()
        } catch (e: Exception) {
            // Retry covers the ordinary case: no signal, or Drive briefly unavailable.
            logErr("backup: failed", e)
            prefs.edit().putString(BackupNames.KEY_LAST_ERROR, e.message ?: e.javaClass.simpleName)
                .remove(BackupNames.KEY_LAST_NOTE).apply()
            return if (runAttemptCount < 3) Result.retry() else Result.failure()
        } finally {
            snapshot.delete()
            gz.delete()
            sealed.delete()
        }
    }

    private fun fail(prefs: android.content.SharedPreferences, why: String): Result {
        logErr("backup: $why")
        prefs.edit().putString(BackupNames.KEY_LAST_ERROR, why)
            .remove(BackupNames.KEY_LAST_NOTE).apply()
        return Result.failure()      // not retry: neither cause fixes itself
    }

    /**
     * Declined, not failed — so no retry, and last-backed-up is left alone rather than claiming
     * a copy that was never made. The reason still goes in the panel: someone whose ledger is
     * empty is the one person who most needs to be told the backup is still there.
     */
    private fun skip(prefs: android.content.SharedPreferences, why: String): Result {
        log("backup: skipped — $why")
        prefs.edit().putString(BackupNames.KEY_LAST_NOTE, why)
            .remove(BackupNames.KEY_LAST_ERROR).apply()
        return Result.success()
    }

    /**
     * One rolling file in appDataFolder, replaced each run.
     *
     * Deliberately not a dated history: keeping N copies of a household's financial database
     * in someone else's cloud is a bigger promise than this app wants to make, and Drive keeps
     * its own revisions of a replaced file anyway.
     */
    private fun upload(drive: Drive, payload: File, existingId: String?) {
        val content = FileContent("application/gzip", payload)
        if (existingId == null) {
            val meta = DriveFile().setName(BackupNames.REMOTE).setParents(listOf("appDataFolder"))
            val made = drive.files().create(meta, content).setFields("id,size").execute()
            log("upload: created ${made.id}")
        } else {
            val put = drive.files().update(existingId, DriveFile(), content).setFields("id,size").execute()
            log("upload: updated ${put.id}, ${put.getSize()} bytes")
        }
    }

    /**
     * The id of the backup already in Drive, or null if there is none.
     *
     * Looked up before the upload rather than inside it, because whether a copy exists decides
     * whether this run is allowed to happen at all.
     */
    private fun remoteId(drive: Drive): String? {
        val files = drive.files().list()
            .setSpaces("appDataFolder")
            .setQ("name = '${BackupNames.REMOTE}'")
            .setFields("files(id,size,modifiedTime)")
            .setOrderBy("modifiedTime desc")
            .execute().files
        if (files.isNullOrEmpty()) return null
        // More than one means an earlier race created a duplicate. Say so — both sides take the
        // newest, and a silent second copy is how a restore puts back a snapshot nobody
        // recognises.
        if (files.size > 1) logErr("upload: ${files.size} remote copies exist, replacing the newest")
        log("upload: replacing ${files[0].id}, ${files[0].getSize()} bytes from ${files[0].modifiedTime}")
        return files[0].id
    }
}

/**
 * Pull the newest snapshot back down and hand it to `index.php --restore`.
 *
 * Runs with the interpreter stopped, because restore replaces the very file the running PHP
 * process has open. Decompression happens here rather than in PHP: the Android PHP build is
 * compiled with --disable-all and has no zlib.
 */
object DriveRestore {

    /**
     * Returns null on success, or a message describing what stopped it.
     *
     * [passphrase] is only consulted when the downloaded blob turns out to be encrypted, which
     * is detected from the file itself rather than from local settings — a phone restoring
     * someone's backup for the first time has no settings to consult.
     */
    fun run(ctx: Context, server: PhpServer, passphrase: String = ""): String? {
        log("restore: start")
        if (!DriveAuth.isConfigured) return fail("Drive backup is not configured in this build")
        val drive = DriveAuth.drive(ctx) ?: return fail("No Google account connected")

        val blob = File(ctx.cacheDir, "restore.blob")
        val gz = File(ctx.cacheDir, "restore.db.gz")
        val db = File(ctx.cacheDir, "restore.db")
        try {
            val found = drive.files().list()
                .setSpaces("appDataFolder")
                .setQ("name = '${BackupNames.REMOTE}'")
                .setFields("files(id,size,modifiedTime)")
                // Newest first. Without this the order is Drive's own, so a duplicate left by
                // an earlier failed run could be restored in preference to the current backup —
                // which looks exactly like "restore put back an empty ledger".
                .setOrderBy("modifiedTime desc")
                .execute().files
            if (found.isNullOrEmpty()) return fail("No backup found in Drive")
            if (found.size > 1) logErr("restore: ${found.size} remote copies, taking the newest")
            val pick = found[0]
            log("restore: ${pick.id}, ${pick.getSize()} bytes, modified ${pick.modifiedTime}")

            blob.outputStream().use { drive.files().get(pick.id).executeMediaAndDownloadTo(it) }
            log("restore: downloaded ${blob.length()} bytes")

            if (BackupCrypto.isEncrypted(blob)) {
                if (passphrase.isEmpty()) { log("restore: blob is encrypted, asking for the passphrase"); return "PASSPHRASE_REQUIRED" }
                if (!BackupCrypto.decrypt(blob, gz, passphrase)) return fail("That passphrase does not open this backup")
            } else {
                blob.copyTo(gz, overwrite = true)
            }
            GZIPInputStream(gz.inputStream()).use { input -> db.outputStream().use { input.copyTo(it) } }
            log("restore: decompressed to ${db.length()} bytes")

            // Stop the server first: --restore swaps the database file underneath it, and the
            // PHP side deletes the -wal/-shm that the live process still believes it owns.
            server.stop()
            log("restore: ledger held ${server.entryCount()} entries before this")
            val result = server.restoreFrom(db)
            if (result != null) { server.start(); return fail("restore: $result") }
            log("restore: ledger now holds ${server.entryCount()} entries")
            // "This ledger is empty" was true when it was written and is not any more — a
            // restore is the exact event that makes it false. Left in place it sits in the
            // panel telling someone their freshly restored ledger is empty.
            ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
                .edit().remove(BackupNames.KEY_LAST_NOTE).apply()
            server.start()
            log("restore: done, server back up on ${server.origin}")
            return null
        } catch (e: Exception) {
            logErr("restore: failed", e)
            runCatching { server.start() }        // never leave the app without a server
            return e.message ?: e.javaClass.simpleName
        } finally {
            // Plaintext copies of the whole ledger. Do not leave them in the cache dir.
            blob.delete()
            gz.delete()
            db.delete()
        }
    }

    private fun fail(why: String): String { logErr(why); return why }
}

/** Turns the user's chosen frequency into WorkManager state. */
object BackupScheduler {

    fun apply(ctx: Context, frequency: String) {
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
            .edit().putString(BackupNames.KEY_FREQUENCY, frequency).apply()

        val wm = WorkManager.getInstance(ctx)
        if (frequency == "off") {
            wm.cancelUniqueWork(BackupNames.WORK_PERIODIC)
            return
        }
        val days = if (frequency == "weekly") 7L else 1L
        val request = PeriodicWorkRequestBuilder<DriveBackupWorker>(days, TimeUnit.DAYS)
            // WorkManager runs periodic work as soon as the constraints allow, which means the
            // moment the schedule is switched on. That is the wrong moment: the schedule gets
            // switched on when an account is connected, and an account is connected either on a
            // new phone or after clearing app data — both times with an empty ledger, and the
            // copy in Drive is the one thing standing between the user and losing everything.
            // First automatic run is one period out. "Back up now" is unaffected.
            .setInitialDelay(days, TimeUnit.DAYS)
            .setConstraints(
                Constraints.Builder()
                    // CONNECTED, not UNMETERED. A gzipped ledger is tens of kilobytes — a
                    // rounding error against one photo — and waiting for Wi-Fi on a phone that
                    // rarely sees any turns "daily" into "never", silently, which is the worst
                    // possible way for a backup to fail. Battery-not-low still applies.
                    .setRequiredNetworkType(NetworkType.CONNECTED)
                    .setRequiresBatteryNotLow(true)
                    .build()
            )
            .setBackoffCriteria(BackoffPolicy.EXPONENTIAL, 30, TimeUnit.MINUTES)
            .build()

        // KEEP, not REPLACE: re-applying the same frequency on every app launch would
        // otherwise reset the period and a daily backup would never come due.
        wm.enqueueUniquePeriodicWork(BackupNames.WORK_PERIODIC, ExistingPeriodicWorkPolicy.KEEP, request)
    }

    /** "Back up now" — ignores the unmetered/battery constraints, because the user asked. */
    fun runNow(ctx: Context) {
        WorkManager.getInstance(ctx).enqueueUniqueWork(
            BackupNames.WORK_ONCE,
            ExistingWorkPolicy.REPLACE,
            OneTimeWorkRequestBuilder<DriveBackupWorker>()
                .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
                .build()
        )
    }

    fun frequency(ctx: Context): String =
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
            .getString(BackupNames.KEY_FREQUENCY, "off") ?: "off"
}
