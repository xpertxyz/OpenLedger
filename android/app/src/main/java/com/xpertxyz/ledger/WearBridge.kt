package com.xpertxyz.ledger

import android.content.Context
import android.webkit.JavascriptInterface
import com.google.android.gms.wearable.CapabilityClient
import com.google.android.gms.wearable.Wearable
import com.google.android.gms.tasks.Tasks
import org.json.JSONArray
import org.json.JSONObject
import java.util.concurrent.TimeUnit

/**
 * "Which of my watches can see this ledger", for the profile drawer.
 *
 * The same shape the website's Connected devices panel has, and deliberately so — a household
 * should be able to answer "what can read my money" in one place whichever build they are
 * holding. What it lists is different, though, and the difference is the point:
 *
 *   website   devices that redeemed a pairing code, each holding a bearer token you revoke.
 *   phone     watches paired to THIS phone by Wear OS, reached over the Data Layer.
 *
 * There is nothing to revoke here, and no token to show. A watch reaches this ledger because
 * Wear OS paired it to this phone and both apps share a signing key — so the way to disconnect
 * one is to unpair the watch, which is Wear OS's own screen and not ours to reimplement.
 * Saying that plainly beats drawing a Disconnect button that lies.
 */
class WearBridge(private val ctx: Context) {

    /** Everything the panel needs, as JSON. Blocking, but called off the UI thread by the page. */
    @JavascriptInterface
    fun status(): String {
        val out = JSONObject()
        val nodes = JSONArray()
        var reachable = 0

        runCatching {
            // Only nodes advertising OUR capability. Every paired watch is a "connected node";
            // the ones that matter are the ones actually running this app, and listing the
            // rest would answer a question nobody asked.
            val caps = Tasks.await(
                Wearable.getCapabilityClient(ctx)
                    .getCapability(CAPABILITY, CapabilityClient.FILTER_ALL),
                6, TimeUnit.SECONDS,
            )
            for (n in caps.nodes) {
                if (n.isNearby) reachable++
                nodes.put(
                    JSONObject()
                        .put("name", n.displayName)
                        .put("nearby", n.isNearby)
                )
            }
            out.put("ok", true)
        }.onFailure {
            // No Play services, no Wear app installed, or the call timed out. The panel says so
            // rather than showing an empty list, which reads as "no watches" and is a different
            // and wrong answer.
            logErr("wear: could not list nodes", it)
            out.put("ok", false)
        }

        val prefs = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
        return out
            .put("nodes", nodes)
            .put("reachable", reachable)
            .put("lastSeen", prefs.getLong(KEY_LAST_SEEN, 0L))
            .put("lastPath", prefs.getString(KEY_LAST_PATH, "") ?: "")
            .toString()
    }

    companion object {
        const val CAPABILITY = "open_ledger_phone"
        private const val PREFS = "wear_link"
        private const val KEY_LAST_SEEN = "last_seen"
        private const val KEY_LAST_PATH = "last_path"

        /**
         * Called by LedgerWearService every time a watch actually asks for something.
         *
         * "Paired" and "using it" are different facts, and only this one tells you the link
         * works end to end — a watch can be paired, nearby, and still failing to reach the
         * ledger because the apps' signatures do not match.
         */
        fun noteRequest(ctx: Context, path: String) {
            ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE).edit()
                .putLong(KEY_LAST_SEEN, System.currentTimeMillis())
                .putString(KEY_LAST_PATH, path)
                .apply()
        }
    }
}
