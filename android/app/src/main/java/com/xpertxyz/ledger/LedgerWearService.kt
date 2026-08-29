package com.xpertxyz.ledger

import com.google.android.gms.tasks.Task
import com.google.android.gms.tasks.Tasks
import com.google.android.gms.wearable.WearableListenerService
import java.util.concurrent.Executors

/**
 * The phone's half of local watch sync.
 *
 * The watch cannot reach this app over the network: the ledger is a SQLite file served to this
 * app's own WebView on 127.0.0.1, and loopback does not leave the device. So the watch asks
 * over the Wearable Data Layer instead — Bluetooth, no internet, nothing leaving the pair —
 * and this answers with exactly the JSON api.php would have returned over HTTPS, from exactly
 * the same PHP functions. See index.php --wear-summary / --wear-add.
 *
 * There is no token and no pairing here, and none is needed. The Data Layer only delivers
 * between apps sharing a package name AND a signing key, so the only thing that can reach this
 * is our own watch app on the phone's own paired watch. A bearer token would be a credential
 * guarding a channel that is already closed.
 */
class LedgerWearService : WearableListenerService() {

    // One thread, because each answer runs a PHP process against a SQLite file. Two concurrent
    // requests would be two interpreters writing the same database — survivable, thanks to WAL
    // and busy_timeout, but pointless when a watch asks one question at a time.
    private val worker = Executors.newSingleThreadExecutor()

    override fun onRequest(nodeId: String, path: String, data: ByteArray): Task<ByteArray>? {
        val args: Array<String> = when (path) {
            PATH_SUMMARY -> arrayOf("--wear-summary")
            PATH_ADD -> {
                // amount, category_id, note. Split on a unit separator rather than a comma or
                // a pipe: a note is free text and will eventually contain both of those.
                val parts = String(data, Charsets.UTF_8).split(SEP)
                if (parts.size < 2) return null
                arrayOf("--wear-add", parts[0], parts[1], parts.getOrElse(2) { "" })
            }
            // Null tells the Data Layer we do not handle this path, which is the truth and
            // leaves room for a later version to add one without this one lying about it.
            else -> return null
        }

        // Recorded before the work, not after: the drawer's question is "is a watch reaching
        // this ledger", and a request that arrived and then failed still answers it yes.
        WearBridge.noteRequest(applicationContext, path)

        return Tasks.call(worker) {
            val json = PhpServer(applicationContext).wear(*args)
                ?: """{"error":"The ledger could not be read on the phone."}"""
            json.toByteArray(Charsets.UTF_8)
        }
    }

    override fun onDestroy() {
        worker.shutdown()
        super.onDestroy()
    }

    companion object {
        const val PATH_SUMMARY = "/ledger/summary"
        const val PATH_ADD     = "/ledger/add"
        /** ASCII unit separator — cannot occur in an amount, an id, or a typed note. */
        const val SEP = "\u001F"
    }
}
