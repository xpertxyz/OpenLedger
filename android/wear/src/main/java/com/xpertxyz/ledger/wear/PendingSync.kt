package com.xpertxyz.ledger.wear

import android.content.Context
import androidx.work.Constraints
import androidx.work.CoroutineWorker
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters

/**
 * Posts the expenses that were logged with no route to the server.
 *
 * A watch spends a good part of its day out of range of the phone it proxies through, and an
 * app that refuses to record a spend until it can reach a server is an app nobody trusts at a
 * till. So an add that cannot go out is kept, and this drains the queue the next time Android
 * says there is a network.
 */
class PendingSync(ctx: Context, params: WorkerParameters) : CoroutineWorker(ctx, params) {

    override suspend fun doWork(): Result {
        val ctx = applicationContext
        // Only the online ledger needs a token; the phone is reached over the Data Layer.
        if (Store.mode(ctx) == Store.ONLINE && Store.token(ctx) == null) {
            // Unpaired while things were queued. Nothing bound for the website can ever be
            // delivered now, and holding it would resurrect old spends against whatever
            // account is paired next. Entries for the phone are untouched.
            Store.writePending(ctx, Store.pending(ctx).filter { Store.modeOf(it) != Store.ONLINE })
            return Result.success()
        }

        // Oldest first: the queue is a ledger of what happened, and it should land in the
        // order it happened.
        val here = Store.mode(ctx)
        val left = mutableListOf<org.json.JSONObject>()
        var offline = false
        for (item in Store.pending(ctx)) {
            // Bound for the other ledger. Held, not posted and not dropped: it will go out the
            // next time that ledger is the one selected. Posting it now would file a spend
            // into a household that never made it.
            if (Store.modeOf(item) != here) { left += item; continue }
            if (offline) { left += item; continue }   // no point trying the rest
            val r = Api.addExpense(
                ctx,
                item.optString("amount"),
                item.optInt("category_id"),
                item.optString("note"),
                // Always a retry by definition. This is what stops a spend whose reply was
                // lost from being filed twice — see the dedupe in api.php.
                retry = true,
            )
            when (r) {
                is Api.Result.Ok -> Store.saveSummary(ctx, r.body)
                // The ledger said no — a category deleted on the website, an amount over the
                // cap. Retrying forever would never fix it, so the entry is dropped rather
                // than left to block everything queued behind it.
                is Api.Result.Rejected -> Unit
                Api.Result.Unpaired -> {
                    Store.forget(ctx)
                    Store.writePending(ctx, Store.pending(ctx).filter { Store.modeOf(it) != Store.ONLINE })
                    return Result.success()
                }
                // Both mean "not now, keep it". The phone app may be installed later.
                Api.Result.NoPhone, Api.Result.Offline -> { left += item; offline = true }
            }
        }
        Store.writePending(ctx, left)
        LedgerTileService.refresh(ctx); LedgerComplication.refresh(ctx)
        return if (left.isEmpty()) Result.success() else Result.retry()
    }

    companion object {
        private const val NAME = "pending-expenses"

        fun schedule(ctx: Context) {
            WorkManager.getInstance(ctx).enqueueUniqueWork(
                NAME,
                // KEEP, not REPLACE: three adds in a row out of range should not each reset
                // the backoff of the job that is already waiting for a network.
                ExistingWorkPolicy.KEEP,
                OneTimeWorkRequestBuilder<PendingSync>()
                    .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
                    .build(),
            )
        }
    }
}
