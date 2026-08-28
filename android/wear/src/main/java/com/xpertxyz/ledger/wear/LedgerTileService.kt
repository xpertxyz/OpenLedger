package com.xpertxyz.ledger.wear

import android.content.Context
import androidx.wear.protolayout.ActionBuilders
import androidx.wear.protolayout.ColorBuilders.argb
import androidx.wear.protolayout.DimensionBuilders.dp
import androidx.wear.protolayout.DimensionBuilders.expand
import androidx.wear.protolayout.LayoutElementBuilders
import androidx.wear.protolayout.ModifiersBuilders
import androidx.wear.protolayout.ResourceBuilders
import androidx.wear.protolayout.TimelineBuilders
import androidx.wear.tiles.RequestBuilders
import androidx.wear.tiles.TileBuilders
import androidx.wear.tiles.TileService
import androidx.compose.ui.graphics.toArgb
import androidx.concurrent.futures.ResolvableFuture
import com.google.common.util.concurrent.ListenableFuture

/**
 * The tile: the card one swipe right of the watch face.
 *
 * This is the whole reason the app is worth building. Opening an app on a watch means raising
 * the wrist, finding the launcher, scrolling and tapping — by which time the card machine has
 * timed out. A tile is one swipe, and the Add button on it goes straight to the amount screen.
 *
 * Drawn with ProtoLayout rather than Compose because a tile is rendered by the system launcher
 * in a different process: what is handed over is a serialised layout tree, not a running
 * composition. Nothing here can react to anything — it paints [Store]'s cached summary, and
 * [refresh] is what asks the system to come and collect a new one.
 */
class LedgerTileService : TileService() {

    override fun onTileRequest(requestParams: RequestBuilders.TileRequest): ListenableFuture<TileBuilders.Tile> {
        // Never fetches. A tile request runs on the launcher's schedule with a hard deadline,
        // and a Bluetooth-proxied HTTP call would blow through it and render nothing. The app
        // and the sync worker keep the cache warm; this only ever draws it.
        val s = Store.summary(this)
        val currency = s?.optString("currency", "₹") ?: "₹"
        val indian = (s?.optString("numfmt", "indian") ?: "indian") == "indian"

        val root = if (Store.token(this) == null) {
            column(
                text("Open Ledger", 16, Ledger.accent.toArgb(), bold = true),
                text("Tap to connect", 12, Ledger.muted.toArgb()),
            )
        } else {
            column(
                text("Today", 12, Ledger.muted.toArgb()),
                text(money(s?.optDouble("today", 0.0) ?: 0.0, currency, indian), 30, Ledger.text.toArgb(), bold = true),
                text(
                    (s?.optString("month_label") ?: "This month") + "  " +
                        money(s?.optDouble("month", 0.0) ?: 0.0, currency, indian),
                    12,
                    Ledger.muted.toArgb(),
                ),
                spacer(8),
                addButton(),
            )
        }

        // The whole tile is tappable and opens the app; the Add button inside it skips the
        // home screen entirely. Two targets, because "check the total" and "log a spend" are
        // both one gesture from the watch face and neither should cost the other a tap.
        val clickable = ModifiersBuilders.Clickable.Builder()
            .setId("open")
            .setOnClick(launch(MainActivity::class.java.name))
            .build()

        val tile = TileBuilders.Tile.Builder()
            .setResourcesVersion(RESOURCES)
            // Not a refresh interval: the tile is pushed by refresh() whenever the numbers
            // actually change. A timed poll would wake the app to redraw an identical card.
            .setTileTimeline(
                TimelineBuilders.Timeline.Builder()
                    .addTimelineEntry(
                        TimelineBuilders.TimelineEntry.Builder()
                            .setLayout(
                                LayoutElementBuilders.Layout.Builder()
                                    .setRoot(
                                        LayoutElementBuilders.Box.Builder()
                                            .setWidth(expand())
                                            .setHeight(expand())
                                            .setModifiers(
                                                ModifiersBuilders.Modifiers.Builder()
                                                    .setClickable(clickable)
                                                    .build()
                                            )
                                            .addContent(root)
                                            .build()
                                    )
                                    .build()
                            )
                            .build()
                    )
                    .build()
            )
            .build()
        return done(tile)
    }

    override fun onTileResourcesRequest(
        requestParams: RequestBuilders.ResourcesRequest,
    ): ListenableFuture<ResourceBuilders.Resources> =
        // No images: every pixel of this tile is text and colour, which is also why it costs
        // nothing to redraw after every expense.
        done(ResourceBuilders.Resources.Builder().setVersion(RESOURCES).build())

    /**
     * A ListenableFuture that is already finished.
     *
     * androidx's ResolvableFuture rather than Guava's Futures.immediateFuture: the tiles
     * library only puts the ListenableFuture *interface* on the classpath, and pulling all of
     * Guava onto a watch to call one static factory is not a trade worth making.
     */
    private fun <T> done(value: T): ListenableFuture<T> =
        ResolvableFuture.create<T>().apply { set(value) }

    // ── layout helpers ───────────────────────────────────────────────────

    private fun column(vararg children: LayoutElementBuilders.LayoutElement) =
        LayoutElementBuilders.Column.Builder()
            .setWidth(expand())
            .setHorizontalAlignment(LayoutElementBuilders.HORIZONTAL_ALIGN_CENTER)
            .apply { children.forEach { addContent(it) } }
            .build()

    private fun text(value: String, size: Int, color: Int, bold: Boolean = false) =
        LayoutElementBuilders.Text.Builder()
            .setText(value)
            .setFontStyle(
                LayoutElementBuilders.FontStyle.Builder()
                    .setSize(androidx.wear.protolayout.DimensionBuilders.sp(size.toFloat()))
                    .setColor(argb(color))
                    .setWeight(
                        LayoutElementBuilders.FontWeightProp.Builder()
                            .setValue(if (bold) LayoutElementBuilders.FONT_WEIGHT_BOLD else LayoutElementBuilders.FONT_WEIGHT_NORMAL)
                            .build()
                    )
                    .build()
            )
            .setMaxLines(1)
            .build()

    private fun spacer(height: Int) =
        LayoutElementBuilders.Spacer.Builder().setHeight(dp(height.toFloat())).build()

    private fun addButton(): LayoutElementBuilders.LayoutElement =
        LayoutElementBuilders.Box.Builder()
            .setWidth(dp(112f))
            .setHeight(dp(40f))
            .setModifiers(
                ModifiersBuilders.Modifiers.Builder()
                    .setBackground(
                        ModifiersBuilders.Background.Builder()
                            .setColor(argb(Ledger.accent.toArgb()))
                            .setCorner(ModifiersBuilders.Corner.Builder().setRadius(dp(20f)).build())
                            .build()
                    )
                    .setClickable(
                        ModifiersBuilders.Clickable.Builder()
                            .setId("add")
                            .setOnClick(launch(AddActivity::class.java.name))
                            .build()
                    )
                    .build()
            )
            .addContent(text("+ Add", 15, Ledger.surface.toArgb(), bold = true))
            .build()

    private fun launch(activity: String) =
        ActionBuilders.LaunchAction.Builder()
            .setAndroidActivity(
                ActionBuilders.AndroidActivity.Builder()
                    .setPackageName(packageName)
                    .setClassName(activity)
                    .build()
            )
            .build()

    companion object {
        private const val RESOURCES = "1"

        /**
         * Tell the system this tile's numbers moved. Cheap and safe to call after every write —
         * if the tile is not on the watch face, or the service is not bound, this is a no-op.
         */
        fun refresh(ctx: Context) {
            runCatching { getUpdater(ctx).requestUpdate(LedgerTileService::class.java) }
        }
    }
}
