package com.xpertxyz.ledger

import android.content.Context
import android.security.keystore.KeyProperties
import android.security.keystore.KeyProtection
import java.io.File
import java.security.KeyStore
import java.security.SecureRandom
import javax.crypto.Cipher
import javax.crypto.SecretKey
import javax.crypto.SecretKeyFactory
import javax.crypto.spec.GCMParameterSpec
import javax.crypto.spec.PBEKeySpec
import javax.crypto.spec.SecretKeySpec

/**
 * Optional passphrase encryption for the Drive backup.
 *
 * Without this, "we never see your data" is true of us and false of Google — the blob sitting
 * in Drive is a readable SQLite database. With a passphrase set, what leaves the phone is
 * AES-GCM ciphertext and the passphrase never does.
 *
 * ── The file format ──────────────────────────────────────────────────────────
 *
 *     "OLB1" | salt (16) | iv (12) | ciphertext+tag
 *
 * The salt travels **inside the blob**, not in local settings. That is the whole point: a new
 * phone has no settings, and the entire reason to encrypt a backup is being able to restore it
 * onto a device that has lost everything else. Given the passphrase and the file, the key can
 * be re-derived from nothing else.
 *
 * ── Where the key lives ──────────────────────────────────────────────────────
 *
 * A scheduled backup runs with nobody watching, so it cannot ask for the passphrase. The
 * derived key is therefore imported into the Android Keystore, which keeps it out of the app's
 * own storage and, on most devices, inside hardware. The passphrase itself is never written
 * down anywhere — losing it means the Drive copy is unreadable, by design, and the UI says so
 * before it lets anyone turn this on.
 *
 * ponytail: PBKDF2, not Argon2. Argon2 is the better KDF and needs a native library; PBKDF2
 * with a high iteration count ships in the platform. Revisit if the threat model grows past
 * "someone got hold of the Drive file".
 */
object BackupCrypto {

    private const val MAGIC = "OLB1"
    private const val KEYSTORE = "AndroidKeyStore"
    private const val ALIAS = "openledger-backup-key"
    private const val SALT_LEN = 16
    private const val IV_LEN = 12
    private const val TAG_BITS = 128
    // OWASP's floor for PBKDF2-HMAC-SHA256. Measured at roughly a third of a second on the
    // emulator, which is paid once when the passphrase is set and once per restore.
    private const val ITERATIONS = 210_000

    private val rng = SecureRandom()

    // ── Key management ───────────────────────────────────────────────────────

    /** True once a passphrase has been set on this device. */
    fun isEnabled(): Boolean = keystore().containsAlias(ALIAS)

    /**
     * Derive a key from [passphrase] and keep it for unattended backups.
     *
     * The salt is generated here and stored alongside the key, because every future backup
     * this device writes must carry the same salt for the passphrase to reproduce the key.
     */
    fun enable(ctx: Context, passphrase: String) {
        val salt = ByteArray(SALT_LEN).also { rng.nextBytes(it) }
        val key = derive(passphrase, salt)
        keystore().setEntry(
            ALIAS,
            KeyStore.SecretKeyEntry(key),
            KeyProtection.Builder(KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT)
                .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
                // GCM must never reuse an IV under one key, so we always supply our own.
                .setRandomizedEncryptionRequired(false)
                .build()
        )
        prefs(ctx).edit().putString("salt", salt.toHex()).apply()
    }

    fun disable(ctx: Context) {
        runCatching { keystore().deleteEntry(ALIAS) }
        prefs(ctx).edit().remove("salt").apply()
    }

    // ── Encrypt / decrypt ────────────────────────────────────────────────────

    /** Wrap [plain] into [dest] using the stored key. */
    fun encrypt(ctx: Context, plain: File, dest: File) {
        val key = keystore().getKey(ALIAS, null) as SecretKey
        val salt = prefs(ctx).getString("salt", null)?.fromHex()
            ?: error("backup key present but its salt is missing")
        val iv = ByteArray(IV_LEN).also { rng.nextBytes(it) }

        val cipher = Cipher.getInstance("AES/GCM/NoPadding")
        cipher.init(Cipher.ENCRYPT_MODE, key, GCMParameterSpec(TAG_BITS, iv))

        dest.outputStream().use { out ->
            out.write(MAGIC.toByteArray(Charsets.US_ASCII))
            out.write(salt)
            out.write(iv)
            javax.crypto.CipherOutputStream(out, cipher).use { enc ->
                plain.inputStream().use { it.copyTo(enc) }
            }
        }
    }

    /** True if [file] is one of ours. Lets restore accept old unencrypted backups unchanged. */
    fun isEncrypted(file: File): Boolean {
        if (file.length() < MAGIC.length + SALT_LEN + IV_LEN) return false
        val head = ByteArray(MAGIC.length)
        file.inputStream().use { it.read(head) }
        return String(head, Charsets.US_ASCII) == MAGIC
    }

    /**
     * Unwrap [src] into [dest] using [passphrase].
     *
     * Takes the passphrase rather than the stored key on purpose: this has to work on a phone
     * that has never seen this backup before, which is the only situation that really matters.
     * Returns false when the passphrase is wrong — GCM's tag check catches it, so a wrong
     * passphrase can never yield a plausible-looking but wrong database.
     */
    fun decrypt(src: File, dest: File, passphrase: String): Boolean {
        src.inputStream().use { input ->
            val head = ByteArray(MAGIC.length); input.read(head)
            if (String(head, Charsets.US_ASCII) != MAGIC) return false
            val salt = ByteArray(SALT_LEN).also { input.read(it) }
            val iv = ByteArray(IV_LEN).also { input.read(it) }

            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.DECRYPT_MODE, derive(passphrase, salt), GCMParameterSpec(TAG_BITS, iv))
            return try {
                dest.outputStream().use { out ->
                    javax.crypto.CipherInputStream(input, cipher).use { it.copyTo(out) }
                }
                true
            } catch (e: Exception) {
                // AEADBadTagException, wrapped by CipherInputStream. Delete the half-written
                // plaintext so nothing downstream mistakes it for a usable database.
                dest.delete()
                false
            }
        }
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private fun derive(passphrase: String, salt: ByteArray): SecretKey {
        val spec = PBEKeySpec(passphrase.toCharArray(), salt, ITERATIONS, 256)
        val bits = SecretKeyFactory.getInstance("PBKDF2WithHmacSHA256").generateSecret(spec).encoded
        return SecretKeySpec(bits, "AES")
    }

    private fun keystore() = KeyStore.getInstance(KEYSTORE).apply { load(null) }

    private fun prefs(ctx: Context) =
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)

    private fun ByteArray.toHex() = joinToString("") { "%02x".format(it) }
    private fun String.fromHex() = chunked(2).map { it.toInt(16).toByte() }.toByteArray()
}
