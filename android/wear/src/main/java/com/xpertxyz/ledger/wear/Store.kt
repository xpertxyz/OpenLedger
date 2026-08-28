package com.xpertxyz.ledger.wear

import android.content.Context
import androidx.core.content.edit
import org.json.JSONArray
import org.json.JSONObject

/**
 * Everything this app remembers, which is three things: the bearer token, the last summary it
 * managed to fetch, and any expenses logged while there was no route to the server.
 *
 * Plain SharedPreferences, not EncryptedSharedPreferences. The file is in the app's private
 * directory on a device that is locked to a wrist, the token grants exactly what a paired
 * watch is allowed (read a summary, add an expense — see api.php), and androidx.security is a
 * deprecated library to take a dependency on for the privilege.
 * ponytail: if a device token ever grows the ability to edit or delete, revisit this.
 */
object Store {

    private const val PREFS   = "ledger"
    private const val TOKEN   = "token"
    private const val SUMMARY = "summary"
    private const val FETCHED = "fetched_at"
    private const val PENDING = "pending"

    private fun p(ctx: Context) = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    fun token(ctx: Context): String? = p(ctx).getString(TOKEN, null)?.takeIf { it.length == 64 }

    fun saveToken(ctx: Context, token: String) = p(ctx).edit { putString(TOKEN, token) }

    /** Called when the server says 401: the pairing is gone, so the cached ledger is a lie too. */
    fun forget(ctx: Context) = p(ctx).edit { remove(TOKEN); remove(SUMMARY); remove(FETCHED) }

    /**
     * The last good summary, so the watch draws real numbers the instant it opens instead of a
     * spinner. Out of date is not the same as wrong — [fetchedAt] is shown alongside whenever
     * it is more than a few minutes old.
     */
    fun summary(ctx: Context): JSONObject? =
        p(ctx).getString(SUMMARY, null)?.let { runCatching { JSONObject(it) }.getOrNull() }

    fun fetchedAt(ctx: Context): Long = p(ctx).getLong(FETCHED, 0L)

    fun saveSummary(ctx: Context, json: JSONObject) =
        p(ctx).edit { putString(SUMMARY, json.toString()); putLong(FETCHED, System.currentTimeMillis()) }

    // ── The offline queue ────────────────────────────────────────────────
    //
    // An expense logged out of range is not lost and is not silently dropped. It sits here and
    // PendingSync posts it the moment the watch has a route again.

    fun pending(ctx: Context): List<JSONObject> {
        val raw = p(ctx).getString(PENDING, null) ?: return emptyList()
        val arr = runCatching { JSONArray(raw) }.getOrNull() ?: return emptyList()
        return (0 until arr.length()).mapNotNull { arr.optJSONObject(it) }
    }

    fun queue(ctx: Context, item: JSONObject) =
        writePending(ctx, pending(ctx) + item)

    fun writePending(ctx: Context, items: List<JSONObject>) =
        p(ctx).edit { putString(PENDING, JSONArray().apply { items.forEach { put(it) } }.toString()) }
}
