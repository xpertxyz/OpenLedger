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
    private const val MODE    = "mode"
    private const val TOKEN   = "token"
    private const val SUMMARY = "summary"
    private const val FETCHED = "fetched_at"
    private const val PENDING = "pending"
    private const val THEME   = "theme"

    private fun p(ctx: Context) = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    /**
     * Which ledger this watch is reading.
     *
     * ONLINE is the website over HTTPS; PHONE is the Android app's own SQLite file, reached
     * over the Data Layer. They are different ledgers, not two views of one — the phone app
     * has never synced anywhere — so switching modes changes the numbers, and the settings
     * screen says so before it switches.
     */
    const val ONLINE = "online"
    const val PHONE  = "phone"

    fun mode(ctx: Context): String = p(ctx).getString(MODE, ONLINE) ?: ONLINE

    /**
     * Switch which ledger this watch reads, and forget everything that described the old one.
     *
     * The summary, when it was fetched and the palette all belong to the ledger being left.
     * Leaving any of them would show one ledger's totals, or one account's colours, under the
     * other one's name for however long the first refresh takes.
     *
     * The queue is deliberately NOT cleared — see [pending]. Those are expenses somebody
     * actually recorded, and they are tagged with the ledger they were meant for.
     */
    fun setMode(ctx: Context, mode: String) =
        p(ctx).edit { putString(MODE, mode); remove(SUMMARY); remove(FETCHED); remove(THEME) }

    fun token(ctx: Context): String? = p(ctx).getString(TOKEN, null)?.takeIf { it.length == 64 }

    /**
     * Is this watch ready to show a ledger? Online needs a paired token; the phone needs
     * nothing — the Data Layer pairing IS the credential.
     */
    fun ready(ctx: Context): Boolean = mode(ctx) == PHONE || token(ctx) != null

    /** Sign out. Keeps the mode, because that is a preference and not a credential. */
    fun signOut(ctx: Context) =
        p(ctx).edit { remove(TOKEN); remove(SUMMARY); remove(FETCHED); remove(PENDING); remove(THEME) }

    fun saveToken(ctx: Context, token: String) = p(ctx).edit { putString(TOKEN, token) }

    /** Called when the server says 401: the pairing is gone, so the cached ledger is a lie too. */
    fun forget(ctx: Context) = p(ctx).edit { remove(TOKEN); remove(SUMMARY); remove(FETCHED); remove(THEME) }

    /**
     * The last good summary, so the watch draws real numbers the instant it opens instead of a
     * spinner. Out of date is not the same as wrong — [fetchedAt] is shown alongside whenever
     * it is more than a few minutes old.
     */
    fun summary(ctx: Context): JSONObject? =
        p(ctx).getString(SUMMARY, null)?.let { runCatching { JSONObject(it) }.getOrNull() }

    fun fetchedAt(ctx: Context): Long = p(ctx).getLong(FETCHED, 0L)

    fun saveSummary(ctx: Context, json: JSONObject) =
        p(ctx).edit {
            putString(SUMMARY, json.toString())
            putLong(FETCHED, System.currentTimeMillis())
            // Stored separately from the summary because the tile and the complications draw
            // before anything is fetched, and because clearing the cache on a mode switch must
            // not leave them with no colours at all.
            json.optJSONObject("theme")?.let { putString(THEME, it.toString()) }
        }

    /** The palette the server last sent, or null to use the shipped one. */
    fun theme(ctx: Context): JSONObject? =
        p(ctx).getString(THEME, null)?.let { runCatching { JSONObject(it) }.getOrNull() }

    // ── The offline queue ────────────────────────────────────────────────
    //
    // An expense logged out of range is not lost and is not silently dropped. It sits here and
    // PendingSync posts it the moment the watch has a route again.
    //
    // Every entry carries the mode it was recorded in, and that is not bookkeeping — it is
    // what stops an expense typed against the website from being filed into the phone's ledger
    // because somebody changed a setting in between. The two are different ledgers; a queued
    // rupee belongs to exactly one of them.

    /** Every queued expense, whichever ledger it is bound for. */
    fun pending(ctx: Context): List<JSONObject> {
        val raw = p(ctx).getString(PENDING, null) ?: return emptyList()
        val arr = runCatching { JSONArray(raw) }.getOrNull() ?: return emptyList()
        return (0 until arr.length()).mapNotNull { arr.optJSONObject(it) }
    }

    /**
     * The mode an entry is bound for.
     *
     * Entries written before the queue was tagged have none. Those can only be ONLINE — it was
     * the only ledger the watch could reach — so that is what they are read as, rather than
     * being adopted by whichever mode happens to be current and posted somewhere they were
     * never meant to go.
     */
    fun modeOf(item: JSONObject): String = item.optString("mode", ONLINE).ifBlank { ONLINE }

    /** Queued for the ledger currently selected — what "N waiting to sync" should count. */
    fun pendingHere(ctx: Context): List<JSONObject> {
        val here = mode(ctx)
        return pending(ctx).filter { modeOf(it) == here }
    }

    /** Stamped with the current mode as it goes in. */
    fun queue(ctx: Context, item: JSONObject) =
        writePending(ctx, pending(ctx) + item.put("mode", mode(ctx)))

    fun writePending(ctx: Context, items: List<JSONObject>) =
        p(ctx).edit { putString(PENDING, JSONArray().apply { items.forEach { put(it) } }.toString()) }
}
