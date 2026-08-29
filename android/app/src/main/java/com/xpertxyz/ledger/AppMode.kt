package com.xpertxyz.ledger

import android.content.Context
import androidx.core.content.edit

/**
 * Which ledger this phone is showing: its own SQLite file, or the website.
 *
 * These are two different ledgers and nothing moves between them — the same split the watch
 * has. Local is the default and the one the app was built around: no account, no network, the
 * household's money on one phone. Online exists because sharing does. Invites, a second
 * person, a Google account and every screen that depends on them are the website's, and the
 * honest way to offer them is to show the website rather than to reimplement them offline.
 *
 * Switching is reversible and destroys nothing: the SQLite file sits where it was, and coming
 * back to Local finds it exactly as it was left.
 */
object AppMode {

    const val LOCAL  = "local"
    const val ONLINE = "online"

    /** Where Online points. Compiled in rather than typed by anyone — see WEAR.md. */
    const val SITE = "https://ledger.xpertxyz.com"

    private const val PREFS = "app_mode"
    private const val KEY_MODE = "mode"
    private const val KEY_TERMS = "terms_accepted_at"

    private fun p(ctx: Context) = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    fun current(ctx: Context): String =
        if (p(ctx).getString(KEY_MODE, LOCAL) == ONLINE) ONLINE else LOCAL

    fun isOnline(ctx: Context): Boolean = current(ctx) == ONLINE

    /**
     * Going online is the first time this app sends the household's money anywhere, so it is
     * gated on the terms being read once. Kept as a timestamp rather than a flag: "when did
     * they agree" is the question that actually gets asked later, and a boolean cannot answer
     * it.
     */
    fun termsAcceptedAt(ctx: Context): Long = p(ctx).getLong(KEY_TERMS, 0L)

    fun acceptTerms(ctx: Context) = p(ctx).edit { putLong(KEY_TERMS, System.currentTimeMillis()) }

    /** Refuses to go online until the terms have been accepted. Returns whether it switched. */
    fun set(ctx: Context, mode: String): Boolean {
        if (mode == ONLINE && termsAcceptedAt(ctx) == 0L) return false
        p(ctx).edit { putString(KEY_MODE, if (mode == ONLINE) ONLINE else LOCAL) }
        return true
    }
}
