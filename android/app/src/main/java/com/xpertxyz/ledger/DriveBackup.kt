package com.xpertxyz.ledger

import android.content.Context
import androidx.work.*
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
}

class DriveBackupWorker(ctx: Context, params: WorkerParameters) : CoroutineWorker(ctx, params) {

    override suspend fun doWork(): Result {
        val ctx = applicationContext
        val prefs = ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
        val snapshot = File(ctx.cacheDir, "ledger-backup.db")
        val gz = File(ctx.cacheDir, "ledger-backup.db.gz")
        val sealed = File(ctx.cacheDir, "ledger-backup.sealed")
        try {
            val drive = DriveAuth.drive(ctx) ?: return fail(prefs, "No Google account connected")

            PhpServer(ctx).backupTo(snapshot)?.let { return fail(prefs, it) }

            // A ledger is repetitive text and compresses to a fraction of its size, which
            // matters on mobile data. Compress BEFORE encrypting — ciphertext does not
            // compress, so the other order would upload the full-size database.
            snapshot.inputStream().use { input ->
                GZIPOutputStream(gz.outputStream()).use { input.copyTo(it) }
            }

            val payload = if (BackupCrypto.isEnabled()) {
                BackupCrypto.encrypt(ctx, gz, sealed); sealed
            } else gz

            upload(drive, payload)
            prefs.edit()
                .putLong(BackupNames.KEY_LAST_OK, System.currentTimeMillis())
                .remove(BackupNames.KEY_LAST_ERROR)
                .apply()
            return Result.success()
        } catch (e: Exception) {
            // Retry covers the ordinary case: no signal, or Drive briefly unavailable.
            prefs.edit().putString(BackupNames.KEY_LAST_ERROR, e.message ?: e.javaClass.simpleName).apply()
            return if (runAttemptCount < 3) Result.retry() else Result.failure()
        } finally {
            snapshot.delete()
            gz.delete()
            sealed.delete()
        }
    }

    private fun fail(prefs: android.content.SharedPreferences, why: String): Result {
        prefs.edit().putString(BackupNames.KEY_LAST_ERROR, why).apply()
        return Result.failure()      // not retry: neither cause fixes itself
    }

    /**
     * One rolling file in appDataFolder, replaced each run.
     *
     * Deliberately not a dated history: keeping N copies of a household's financial database
     * in someone else's cloud is a bigger promise than this app wants to make, and Drive keeps
     * its own revisions of a replaced file anyway.
     */
    private fun upload(drive: Drive, payload: File) {
        val content = FileContent("application/gzip", payload)
        val existing = drive.files().list()
            .setSpaces("appDataFolder")
            .setQ("name = '${BackupNames.REMOTE}'")
            .setFields("files(id)")
            .execute().files

        if (existing.isNullOrEmpty()) {
            val meta = DriveFile().setName(BackupNames.REMOTE).setParents(listOf("appDataFolder"))
            drive.files().create(meta, content).setFields("id").execute()
        } else {
            drive.files().update(existing[0].id, DriveFile(), content).execute()
        }
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
        if (!DriveAuth.isConfigured) return "Drive backup is not configured in this build"
        val drive = DriveAuth.drive(ctx) ?: return "No Google account connected"

        val blob = File(ctx.cacheDir, "restore.blob")
        val gz = File(ctx.cacheDir, "restore.db.gz")
        val db = File(ctx.cacheDir, "restore.db")
        try {
            val found = drive.files().list()
                .setSpaces("appDataFolder")
                .setQ("name = '${BackupNames.REMOTE}'")
                .setFields("files(id,size,modifiedTime)")
                .execute().files
            if (found.isNullOrEmpty()) return "No backup found in Drive"

            blob.outputStream().use { drive.files().get(found[0].id).executeMediaAndDownloadTo(it) }

            if (BackupCrypto.isEncrypted(blob)) {
                if (passphrase.isEmpty()) return "PASSPHRASE_REQUIRED"
                if (!BackupCrypto.decrypt(blob, gz, passphrase)) return "That passphrase does not open this backup"
            } else {
                blob.copyTo(gz, overwrite = true)
            }
            GZIPInputStream(gz.inputStream()).use { input -> db.outputStream().use { input.copyTo(it) } }

            // Stop the server first: --restore swaps the database file underneath it, and the
            // PHP side deletes the -wal/-shm that the live process still believes it owns.
            server.stop()
            val result = server.restoreFrom(db)
            server.start()
            return result
        } catch (e: Exception) {
            runCatching { server.start() }        // never leave the app without a server
            return e.message ?: e.javaClass.simpleName
        } finally {
            // Plaintext copies of the whole ledger. Do not leave them in the cache dir.
            blob.delete()
            gz.delete()
            db.delete()
        }
    }
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
