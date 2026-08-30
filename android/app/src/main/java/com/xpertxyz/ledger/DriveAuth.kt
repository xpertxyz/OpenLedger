package com.xpertxyz.ledger

import android.accounts.Account
import android.app.Activity
import android.content.Context
import androidx.credentials.ClearCredentialStateRequest
import androidx.credentials.CustomCredential
import androidx.credentials.CredentialManager
import androidx.credentials.GetCredentialRequest
import androidx.credentials.exceptions.GetCredentialCancellationException
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
    private const val PREFS_AUTH  = "auth_state"
    private const val KEY_ACCOUNT = "account_email"
    private const val KEY_GRANTED = "drive_granted"

    private fun authPrefs(ctx: Context) =
        ctx.getSharedPreferences(PREFS_AUTH, Context.MODE_PRIVATE)

    /**
     * The connected account's email, or null when nobody has connected one.
     *
     * Signing in and granting Drive are two separate answers, and only the second one gives
     * this app anything. An email on its own means somebody got halfway — so it is not enough
     * to report an account, or every backup would fail against a panel claiming to be set up.
     */
    fun connectedAccount(ctx: Context): String? {
        if (!isConfigured) return null
        val p = authPrefs(ctx)
        if (!p.getBoolean(KEY_GRANTED, false)) return null
        return p.getString(KEY_ACCOUNT, null)
    }

    /**
     * Who are you, then may we keep a backup for you. Two questions, in that order, and only
     * the second one grants this app anything — see [connectedAccount].
     *
     * Needs the Activity, not the application context: both halves put a sheet on the screen,
     * and Credential Manager has nowhere to put one without a window.
     */
    suspend fun connect(activity: Activity) {
        if (!isConfigured) return
        val email = signIn(activity)
        // Every exit repaints. The reason a connect stopped is written into the panel's own
        // error line, and a page nobody reloads is a page that still says "not connected"
        // with no word of why — which is the whole failure this arrangement is here to end.
        if (email == null) { (activity as? MainActivity)?.onDriveAuthDone(); return }
        authPrefs(activity).edit { putString(KEY_ACCOUNT, email) }
        authorize(activity, email)
    }

    /**
     * The Google ID token itself, for signing into the website.
     *
     * Distinct from [signIn], which returns the email because that is all the Drive flow needs.
     * A server cannot verify an email — it verifies the signed token, whose audience must be
     * this app's client id. Same sheet, same Credential Manager, different field.
     */
    suspend fun idToken(activity: Activity): String? {
        if (!isConfigured) return null
        val request = GetCredentialRequest.Builder()
            .addCredentialOption(
                GetGoogleIdOption.Builder()
                    .setFilterByAuthorizedAccounts(false)
                    .setServerClientId(WEB_CLIENT_ID)
                    .build()
            )
            .build()
        return try {
            val credential = CredentialManager.create(activity)
                .getCredential(activity, request).credential
            if (credential is CustomCredential &&
                credential.type == GoogleIdTokenCredential.TYPE_GOOGLE_ID_TOKEN_CREDENTIAL) {
                GoogleIdTokenCredential.createFrom(credential.data).idToken
            } else {
                logErr("idToken: unexpected credential type " + credential.type)
                null
            }
        } catch (e: GetCredentialCancellationException) {
            null
        } catch (e: GetCredentialException) {
            logErr("idToken failed", e)
            null
        }
    }

    private suspend fun signIn(activity: Activity): String? {
        val request = GetCredentialRequest.Builder()
            .addCredentialOption(
                GetGoogleIdOption.Builder()
                    .setFilterByAuthorizedAccounts(false)
                    .setServerClientId(WEB_CLIENT_ID)
                    .build()
            )
            .build()

        return try {
            val credential = CredentialManager.create(activity)
                .getCredential(activity, request).credential
            // Credential Manager rebuilds every credential from a Bundle, so what arrives is a
            // plain CustomCredential carrying the Google type string — never the
            // GoogleIdTokenCredential subclass, however much it looks like one. `is
            // GoogleIdTokenCredential` is therefore always false, which is why a sign-in that
            // visibly succeeded wrote nothing down and the panel kept forgetting the account.
            if (credential is CustomCredential &&
                credential.type == GoogleIdTokenCredential.TYPE_GOOGLE_ID_TOKEN_CREDENTIAL) {
                GoogleIdTokenCredential.createFrom(credential.data).id
            } else {
                logErr("signIn: unexpected credential type " + credential.type)
                noteError(activity, "Google returned a credential this app cannot read.")
                null
            }
        } catch (e: GetCredentialCancellationException) {
            null                       // backing out of the sheet is an answer, not a fault
        } catch (e: GetCredentialException) {
            logErr("signIn failed", e)
            noteSignInError(activity, e)
            null
        }
    }

    private fun authorize(activity: Activity, email: String) {
        val request = AuthorizationRequest.builder()
            .setRequestedScopes(listOf(driveScope))
            // Pinned to the account that just signed in. Left open, the consent sheet offers a
            // picker of its own, and choosing a different account there authorises one address
            // while drive() goes on asking for a token for the other.
            .setAccount(Account(email, "com.google"))
            .build()

        Identity.getAuthorizationClient(activity)
            .authorize(request)
            .addOnSuccessListener { result ->
                if (result.hasResolution()) {
                    (activity as? MainActivity)?.launchAuthorization(result)
                } else {
                    // Already granted on an earlier run: no sheet, nothing comes back through
                    // the launcher, and only this branch is left to record it and repaint.
                    noteAuthorizationResult(activity, result)
                    (activity as? MainActivity)?.onDriveAuthDone()
                }
            }
            .addOnFailureListener { e ->
                logErr("authorize failed", e)
                noteSignInError(activity, e)
                (activity as? MainActivity)?.onDriveAuthDone()
            }
    }

    /** The grant itself, from either path. Nothing else may set [KEY_GRANTED]. */
    fun noteAuthorizationResult(ctx: Context, result: AuthorizationResult) {
        val granted = result.grantedScopes.contains(DriveScopes.DRIVE_APPDATA)
        authPrefs(ctx).edit { putBoolean(KEY_GRANTED, granted) }
        if (granted) {
            log("authorize: drive.appdata granted")
            forgetSignInError(ctx)
        } else {
            noteError(ctx, "Drive access was not granted. Tap Connect and allow it.")
        }
    }

    /** The consent sheet closed without granting anything, or came back unreadable. */
    fun noteAuthorizationDeclined(
        ctx: Context,
        why: String = "Drive access was not granted. Tap Connect and allow it."
    ) {
        authPrefs(ctx).edit { putBoolean(KEY_GRANTED, false) }
        noteError(ctx, why)
    }

    fun disconnect(ctx: Context) {
        authPrefs(ctx).edit { clear() }
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

    private fun noteError(ctx: Context, why: String) {
        ctx.getSharedPreferences(BackupNames.PREFS, Context.MODE_PRIVATE)
            .edit { putString(BackupNames.KEY_LAST_ERROR, why) }
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
        noteError(ctx, why)
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
