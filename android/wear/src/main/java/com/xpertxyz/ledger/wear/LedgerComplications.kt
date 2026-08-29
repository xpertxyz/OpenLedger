package com.xpertxyz.ledger.wear

import android.app.PendingIntent
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import androidx.wear.watchface.complications.data.ComplicationData
import androidx.wear.watchface.complications.data.ComplicationType
import androidx.wear.watchface.complications.data.PlainComplicationText
import androidx.wear.watchface.complications.data.RangedValueComplicationData
import androidx.wear.watchface.complications.data.ShortTextComplicationData
import androidx.wear.watchface.complications.datasource.ComplicationDataSourceUpdateRequester
import androidx.wear.watchface.complications.datasource.ComplicationRequest
import androidx.wear.watchface.complications.datasource.SuspendingComplicationDataSourceService

/**
 * Two numbers for the watch face itself: what has been spent today, and how much of this
 * month's investment target is in.
 *
 * Complications rather than a watch face of our own, deliberately. Steps and battery already
 * exist as complications on every face Samsung ships; writing a face to redraw them would mean
 * reimplementing two things the system does better, and would force one design on someone who
 * already chose theirs. This way the ledger sits on *their* face, beside the step count.
 *
 * Both read the cached summary and never fetch. A complication is refreshed by the system on
 * its own schedule with a hard deadline, and a Bluetooth-proxied request would miss it; the
 * app, the tile refresh and the sync worker keep the cache warm, and [refresh] pushes a new
 * value the moment one lands.
 */
abstract class LedgerComplication : SuspendingComplicationDataSourceService() {

    /** Tapping any of these opens the ledger — the number is the question, the app is the answer. */
    protected fun openApp(): PendingIntent = PendingIntent.getActivity(
        this,
        0,
        Intent(this, MainActivity::class.java),
        PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT,
    )

    companion object {
        /**
         * Tell the system both complications moved. Safe to call when neither is on a watch
         * face — it is a no-op then, which is the common case.
         */
        fun refresh(ctx: Context) {
            val requester = ComplicationDataSourceUpdateRequester.create(
                ctx,
                ComponentName(ctx, SpendTodayComplicationService::class.java),
            )
            runCatching { requester.requestUpdateAll() }
            runCatching {
                ComplicationDataSourceUpdateRequester
                    .create(ctx, ComponentName(ctx, InvestProgressComplicationService::class.java))
                    .requestUpdateAll()
            }
        }
    }
}

/** Today's spend, as short text: "₹460". */
class SpendTodayComplicationService : LedgerComplication() {

    override fun getPreviewData(type: ComplicationType): ComplicationData? =
        if (type != ComplicationType.SHORT_TEXT) null
        else short("₹460", "Spent today")

    override suspend fun onComplicationRequest(request: ComplicationRequest): ComplicationData? {
        if (request.complicationType != ComplicationType.SHORT_TEXT) return null
        val s = Store.summary(this)
        val currency = s?.optString("currency", "₹") ?: "₹"
        val indian = (s?.optString("numfmt", "indian") ?: "indian") == "indian"
        // No cache yet — unpaired, or never opened with a connection. An em dash says "nothing
        // to show" without pretending the household spent zero today.
        val text = if (s == null) "—" else money(s.optDouble("today", 0.0), currency, indian)
        return short(text, "Spent today")
    }

    private fun short(text: String, description: String) =
        ShortTextComplicationData.Builder(
            text = PlainComplicationText.Builder(text).build(),
            contentDescription = PlainComplicationText.Builder(description).build(),
        ).setTapAction(openApp()).build()
}

/**
 * How much of this month's investment target is in, as a ring plus "62%".
 *
 * RANGED_VALUE so a face can draw it as an arc, which is the only form in which a percentage
 * is worth a glance. The value is clamped to the range even though the percentage is not —
 * going past the target is worth seeing as "104%", but an arc past full is just a full arc.
 */
class InvestProgressComplicationService : LedgerComplication() {

    override fun getPreviewData(type: ComplicationType): ComplicationData? =
        if (type != ComplicationType.RANGED_VALUE) null else ranged(62)

    override suspend fun onComplicationRequest(request: ComplicationRequest): ComplicationData? {
        if (request.complicationType != ComplicationType.RANGED_VALUE) return null
        val s = Store.summary(this) ?: return ranged(0, "—")
        // No target set means there is no progress to be at, not that they are at zero.
        if (s.optDouble("invest_target", 0.0) <= 0.0) return ranged(0, "—")
        return ranged(s.optInt("invest_pct", 0))
    }

    private fun ranged(pct: Int, label: String = "$pct%") =
        RangedValueComplicationData.Builder(
            value = pct.coerceIn(0, 100).toFloat(),
            min = 0f,
            max = 100f,
            contentDescription = PlainComplicationText.Builder("Investment target progress").build(),
        )
            .setText(PlainComplicationText.Builder(label).build())
            .setTapAction(openApp())
            .build()
}
