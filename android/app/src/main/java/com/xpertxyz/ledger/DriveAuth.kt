package com.xpertxyz.ledger

import android.content.Context
import androidx.credentials.ClearCredentialStateRequest
import androidx.credentials.CredentialManager
import androidx.credentials.GetCredentialRequest
import androidx.credentials.GetCredentialResponse
import androidx.credentials.exceptions.GetCredentialException
import com.google.android.gms.auth.api.identity.AuthorizationRequest
import com.google.android.gms.auth.api.identity.AuthorizationResult
import com.google.android.gms.auth.api.identity.Identity
import com.google.android.gms.common.api.ApiException
import com.google.android.gms.common.api.CommonStatusCodes
import com.google.android.gms.common.api.Scope
import com.google.android.libraries.identity.googleid.GetGoogleIdOption
import com.google.android.libraries.identity.googleid.GoogleIdTokenCredential
import com.google.api.client.googleapis.extensions.android.gms.auth.GoogleAccountCredential
import com.google.api.client.http.javanet.NetHttpTransport
import com.google.api.client.json.gson.GsonFactory
import com.google.api.services.drive.Drive
import com.google.api.services.drive.DriveScopes
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import androidx.core.content.edit

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
 *        Package name: com.xpertxyz.ledger        (must match applicationId exactly)
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
    const val WEB_CLIENT_ID = "476911098690-vpmm92mij1k48f50pmc6u2utjdms5lnu.apps.googleusercontent.com"

    val isConfigured: Boolean get() = !WEB_CLIENT_ID.startsWith("REPLACE_ME")

    private val driveScope = Scope(DriveScopes.DRIVE_APPDATA)
    private const val PREFS_AUTH = "auth_state"
    private const val KEY_ACCOUNT = "account_email"

    /** The connected account's email, or null when nobody has connected one. */
    fun connectedAccount(ctx: Context): String? {
        if (!isConfigured) return null
        return ctx.getSharedPreferences(PREFS_AUTH, Context.MODE_PRIVATE).getString(KEY_ACCOUNT, null)
    }

    suspend fun signIn(ctx: Context): String? {
        val credentialManager = CredentialManager.create(ctx)
        val googleIdOption = GetGoogleIdOption.Builder()
            .setFilterByAuthorizedAccounts(false)
            .setServerClientId(WEB_CLIENT_ID)
            .build()

        val request = GetCredentialRequest.Builder()
            .addCredentialOption(googleIdOption)
            .build()

        return try {
            val result = credentialManager.getCredential(ctx, request)
            val email = handleSignInResponse(ctx, result)
            if (email != null) {
                // Store the email temporarily so authorize() can use it if needed,
                // or just wait for authorization to complete.
                ctx.getSharedPreferences(PREFS_AUTH, Context.MODE_PRIVATE)
                    .edit { putString(KEY_ACCOUNT, email) }
            }
            email
        } catch (e: GetCredentialException) {
            logErr("signIn failed", e)
            noteSignInError(ctx, e)
            null
        }
    }

    private fun handleSignInResponse(ctx: Context, response: GetCredentialResponse): String? {
        val credential = response.credential
        if (credential is GoogleIdTokenCredential) {
            val email = credential.id
            log("signIn: success, email=$email")
            return email
        }
        return null
    }

    fun authorize(ctx: Context, activity: android.app.Activity) {
        val request = AuthorizationRequest.builder()
            .setRequestedScopes(listOf(driveScope))
            .build()

        Identity.getAuthorizationClient(activity)
            .authorize(request)
            .addOnSuccessListener { result ->
                if (result.hasResolution()) {
                    (activity as? MainActivity)?.launchAuthorization(result)
                } else {
                    noteAuthorizationResult(ctx, result)
                }
            }
            .addOnFailureListener { e ->
                logErr("authorize failed", e)
                noteSignInError(ctx, e as Exception)
            }
    }

    fun noteAuthorizationResult(ctx: Context, result: AuthorizationResult) {
        // If we reach here, the user has granted permissions.
        // The account email should already be in PREFS_AUTH from signIn().
        if (connectedAccount(ctx) != null) {
            forgetSignInError(ctx)
            log("authorize: success")
            // Notify MainActivity to reload
            (ctx as? MainActivity)?.onDriveAuthSuccess()
        } else {
             noteSignInError(ctx, Exception("Signed in, but account information is missing."))
        }
    }

    fun disconnect(ctx: Context) {
        ctx.getSharedPreferences(PREFS_AUTH, Context.MODE_PRIVATE).edit { clear() }
        val credentialManager = CredentialManager.create(ctx)
        CoroutineScope(Dispatchers.Main).launch {
            runCatching { credentialManager.clearCredentialState(ClearCredentialStateRequest()) }
        }
        forgetSignInError(ctx)
    }

    private fun forgetSignInError(ctx: Context) {
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
            .edit { remove(BackupNames.KEY_LAST_ERROR) }
    }

    private fun noteSignInError(ctx: Context, e: Exception) {
        val why = when (e) {
            is ApiException -> when (e.statusCode) {
                CommonStatusCodes.DEVELOPER_ERROR ->
                    "Google does not recognise this build (error 10). Its signing certificate's " +
                    "SHA-1 must be on an Android OAuth client for com.xpertxyz.ledger."
                CommonStatusCodes.NETWORK_ERROR ->
                    "No connection to Google. Try again."
                CommonStatusCodes.SIGN_IN_REQUIRED ->
                    "Signed in, but Drive access was not granted. Tap Connect and allow it."
                else ->
                    "Google error: " + (e.message ?: e.javaClass.simpleName) + " (" + e.statusCode + ")"
            }
            else -> e.message ?: e.javaClass.simpleName
        }
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
            .edit { putString(BackupNames.KEY_LAST_ERROR, why) }
    }

    /**
     * An authorised Drive client, or null when the seam above is unfilled or no account is
     * connected. Callers treat null as "backup is not set up", never as an error.
     */
    fun drive(ctx: Context): Drive? {
        if (!isConfigured) return null
        val email = connectedAccount(ctx) ?: return null

        val credential = GoogleAccountCredential
            .usingOAuth2(ctx, listOf(DriveScopes.DRIVE_APPDATA))
            .apply { selectedAccountName = email }

        return Drive.Builder(NetHttpTransport(), GsonFactory.getDefaultInstance(), credential)
            .setApplicationName("Open Ledger")
            .build()
    }
}
