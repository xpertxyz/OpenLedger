package xyz.openledger.app

import androidx.test.ext.junit.runners.AndroidJUnit4
import androidx.test.platform.app.InstrumentationRegistry
import org.junit.After
import org.junit.Assert.assertArrayEquals
import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test
import org.junit.runner.RunWith
import java.io.File

/**
 * The backup encryption has to be tested on a device: the key lives in the Android Keystore,
 * which has no JVM equivalent. Encryption nobody has watched round-trip is not encryption,
 * it is a way to lose a household's records.
 *
 *   cd android && gradle connectedDebugAndroidTest
 */
@RunWith(AndroidJUnit4::class)
class BackupCryptoTest {

    private val ctx = InstrumentationRegistry.getInstrumentation().targetContext
    private fun tmp(name: String) = File(ctx.cacheDir, name).also { it.delete() }

    @After fun tearDown() = BackupCrypto.disable(ctx)

    @Test fun roundTripsAnArbitraryPayload() {
        val plain = tmp("p.bin").apply { writeBytes(ByteArray(200_000) { (it * 31 % 251).toByte() }) }
        val sealed = tmp("s.bin")
        val out = tmp("o.bin")

        BackupCrypto.enable(ctx, "correct horse battery")
        BackupCrypto.encrypt(ctx, plain, sealed)

        assertTrue("blob must be recognisable as encrypted", BackupCrypto.isEncrypted(sealed))
        assertFalse("ciphertext must not equal plaintext", sealed.readBytes().contentEquals(plain.readBytes()))
        assertTrue(BackupCrypto.decrypt(sealed, out, "correct horse battery"))
        assertArrayEquals(plain.readBytes(), out.readBytes())
    }

    @Test fun wrongPassphraseIsRejectedAndLeavesNoPlaintext() {
        val plain = tmp("p2.bin").apply { writeText("SQLite format 3 pretend ledger") }
        val sealed = tmp("s2.bin")
        val out = tmp("o2.bin")

        BackupCrypto.enable(ctx, "the-real-one")
        BackupCrypto.encrypt(ctx, plain, sealed)

        // GCM authenticates, so a wrong key cannot yield plausible-but-wrong output.
        assertFalse(BackupCrypto.decrypt(sealed, out, "not-the-real-one"))
        assertFalse("a failed decrypt must not leave half a ledger behind", out.exists())
    }

    @Test fun plainBackupsAreStillRecognised() {
        // Backups taken before encryption existed must keep restoring.
        val gz = tmp("old.gz").apply { writeBytes(byteArrayOf(0x1f, 0x8b.toByte(), 8, 0, 0, 0, 0, 0)) }
        assertFalse(BackupCrypto.isEncrypted(gz))
    }

    @Test fun saltTravelsInTheBlobSoAnotherDeviceCanOpenIt() {
        val plain = tmp("p3.bin").apply { writeText("ledger bytes") }
        val sealed = tmp("s3.bin")
        val out = tmp("o3.bin")

        BackupCrypto.enable(ctx, "shared-secret")
        BackupCrypto.encrypt(ctx, plain, sealed)

        // Simulate a new phone: no keystore entry, no saved salt — only the file and the
        // passphrase. This is the only scenario that makes an off-device backup worth having.
        BackupCrypto.disable(ctx)
        assertFalse(BackupCrypto.isEnabled())

        assertTrue(BackupCrypto.decrypt(sealed, out, "shared-secret"))
        assertEquals("ledger bytes", out.readText())
    }
}
