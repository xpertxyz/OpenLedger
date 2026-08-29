package com.xpertxyz.ledger.wear

import android.content.Context
import com.google.android.gms.wearable.CapabilityClient
import com.google.android.gms.wearable.Wearable
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.tasks.await
import kotlinx.coroutines.withContext
import org.json.JSONObject

/**
 * The other ledger: the Android app's own SQLite file on the paired phone.
 *
 * There is no HTTP here and no server to be up. The phone app serves its ledger to its own
 * WebView on 127.0.0.1, which does not leave the device, so the watch asks over the Wearable
 * Data Layer and LedgerWearService answers by running the same PHP through its CLI. Bluetooth
 * only — this works with no internet on either device, and nothing leaves the pair.
 *
 * The answers are the same shape api.php returns over the network, from the same functions, so
 * everything above this — the screens, the tile, the complications — cannot tell which backend
 * it is talking to.
 */
object PhoneBackend {

    suspend fun summary(ctx: Context): Api.Result = call(ctx, PATH_SUMMARY, ByteArray(0))

    suspend fun addExpense(ctx: Context, amount: String, categoryId: Int, note: String): Api.Result =
        call(ctx, PATH_ADD, listOf(amount, categoryId.toString(), note).joinToString(SEP).toByteArray())

    private suspend fun call(ctx: Context, path: String, data: ByteArray): Api.Result =
        withContext(Dispatchers.IO) {
            try {
                // A capability rather than a hardcoded node id: a watch can be paired to more
                // than one phone over its life, and only the one actually running the ledger
                // app advertises this.
                val nodes = Wearable.getCapabilityClient(ctx)
                    .getCapability(CAPABILITY, CapabilityClient.FILTER_REACHABLE)
                    .await()
                    .nodes
                val node = nodes.firstOrNull { it.isNearby }
                    ?: nodes.firstOrNull()
                    // Nothing advertises the capability: the phone does not have the ledger
                    // app, or has a version predating LedgerWearService. Its own result,
                    // because "out of range" resolves itself and this never will.
                    ?: return@withContext Api.Result.NoPhone

                val reply = Wearable.getMessageClient(ctx).sendRequest(node.id, path, data).await()
                val body = JSONObject(String(reply, Charsets.UTF_8))
                // The phone shapes a rejected expense as {"error": ...}, exactly as api.php
                // does for a 422, so one branch here covers both backends.
                body.optString("error").takeIf { it.isNotBlank() }
                    ?.let { Api.Result.Rejected(it) }
                    ?: Api.Result.Ok(body)
            } catch (e: Exception) {
                // A dropped Bluetooth link, a phone asleep mid-request, a reply that was not
                // JSON. All mean "ask again later", which is Offline — and which is what puts
                // an add on the queue rather than losing it.
                Api.Result.Offline
            }
        }

    /**
     * Declared by the phone app in wear.xml. Nothing else about that app is discoverable from
     * here, so this is also how the settings screen knows whether Phone mode is worth offering.
     */
    const val CAPABILITY = "open_ledger_phone"

    private const val PATH_SUMMARY = "/ledger/summary"
    private const val PATH_ADD     = "/ledger/add"
    /** ASCII unit separator, matching LedgerWearService. */
    private const val SEP          = "\u001F"
}
