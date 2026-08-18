package xyz.openledger.app

import android.content.Context
import android.content.Intent
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInClient
import com.google.android.gms.auth.api.signin.GoogleSignInOptions
import com.google.android.gms.common.api.Scope
import com.google.api.client.googleapis.extensions.android.gms.auth.GoogleAccountCredential
import com.google.api.client.http.javanet.NetHttpTransport
import com.google.api.client.json.gson.GsonFactory
import com.google.api.services.drive.Drive
import com.google.api.services.drive.DriveScopes

/**
 * ─────────────────────────────────────────────────────────────────────────────
 *  THE ONE THING YOU HAVE TO SUPPLY
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Everything else in the backup path is finished and tested. This file is the seam.
 *
 * Drive backup stays switched off until [WEB_CLIENT_ID] is filled in — the app runs, the
 * ledger works, and the backup panel says the account is not connected. Nothing here
 * fails at runtime because of the placeholder; it is checked for explicitly.
 *
 * To enable it:
 *
 *  1. Google Cloud Console → APIs & Services → **Enable** the Google Drive API.
 *  2. OAuth consent screen → External → add the scope `.../auth/drive.appdata`.
 *     Note this is a NON-sensitive scope, so it needs no CASA security assessment.
 *     Do not request `drive` or `drive.readonly` — those are restricted, and shipping
 *     either one means an annual third-party security audit at your own expense.
 *  3. Credentials → Create OAuth client ID → **Android**:
 *        Package name: xyz.openledger.app        (must match applicationId exactly)
 *        SHA-1:        keytool -list -v -keystore <your.keystore> -alias <alias>
 *     Register the debug certificate too if you want this to work from a debug build:
 *        keytool -list -v -keystore ~/.android/debug.keystore \
 *                -alias androiddebugkey -storepass android -keypass android
 *  4. Credentials → Create OAuth client ID → **Web application**. Copy THAT client id
 *     into [WEB_CLIENT_ID] below.
 *
 * The Android client id is never pasted anywhere — Google matches it by package name and
 * signing fingerprint. The *web* client id is the one the SDK wants. Getting these two the
 * wrong way round produces a silent APIException 10 (DEVELOPER_ERROR) with no explanation,
 * and is the single most common way this setup goes wrong.
 */
object DriveAuth {

    /** Replace with the **Web application** OAuth client id. See the notes above. */
    const val WEB_CLIENT_ID = "REPLACE_ME.apps.googleusercontent.com"

    val isConfigured: Boolean get() = !WEB_CLIENT_ID.startsWith("REPLACE_ME")

    private val scope = Scope(DriveScopes.DRIVE_APPDATA)

    fun signInClient(ctx: Context): GoogleSignInClient =
        GoogleSignIn.getClient(
            ctx,
            GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
                .requestIdToken(WEB_CLIENT_ID)
                .requestEmail()
                .requestScopes(scope)
                .build()
        )

    fun signInIntent(ctx: Context): Intent = signInClient(ctx).signInIntent

    /** The connected account's email, or null when nobody has connected one. */
    fun connectedAccount(ctx: Context): String? {
        if (!isConfigured) return null
        val acct = GoogleSignIn.getLastSignedInAccount(ctx) ?: return null
        if (!GoogleSignIn.hasPermissions(acct, scope)) return null
        return acct.email
    }

    fun disconnect(ctx: Context) {
        if (isConfigured) runCatching { signInClient(ctx).signOut() }
    }

    /**
     * An authorised Drive client, or null when the seam above is unfilled or no account is
     * connected. Callers treat null as "backup is not set up", never as an error.
     */
    fun drive(ctx: Context): Drive? {
        if (!isConfigured) return null
        val acct = GoogleSignIn.getLastSignedInAccount(ctx) ?: return null
        if (!GoogleSignIn.hasPermissions(acct, scope)) return null

        val credential = GoogleAccountCredential
            .usingOAuth2(ctx, listOf(DriveScopes.DRIVE_APPDATA))
            .apply { selectedAccount = acct.account }

        // NetHttpTransport, not AndroidHttp — the latter was removed from
        // google-api-client-android and only survives in tutorials.
        return Drive.Builder(NetHttpTransport(), GsonFactory.getDefaultInstance(), credential)
            .setApplicationName("Open Ledger")
            .build()
    }
}
