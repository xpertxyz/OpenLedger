package com.xpertxyz.ledger.wear

import android.content.Context
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.runtime.staticCompositionLocalOf
import androidx.compose.ui.graphics.Color
import androidx.wear.compose.material3.ColorScheme
import androidx.wear.compose.material3.MaterialTheme
import org.json.JSONObject

/**
 * The account's palette, resolved by the server.
 *
 * Not hardcoded. The website authors its palettes in OKLCH and there are three of them; a copy
 * transcribed into Kotlin is a copy that stops matching the first time a hue is retuned. So the
 * server resolves whichever palette the user is on down to plain hex and sends it with every
 * summary — see watchPalette() in lib.php — which also means a fourth palette added there
 * reaches the watch with no app update.
 *
 * Always the dark variant, whatever the account's light/dark choice is. A watch panel is OLED,
 * inches from the eye and frequently always-on: a light one costs visible battery all day. The
 * palette is honoured; the mode deliberately is not.
 */
data class Palette(
    val name: String,
    val bg: Color,
    val surface: Color,
    val text: Color,
    val accent: Color,
    val accentSoft: Color,
    val sage: Color,
    val muted: Color,
    val track: Color,
    val over: Color,
) {
    companion object {
        /**
         * Organic dark, the palette the design system ships. Used before the first summary
         * arrives and whenever the server sends something unparseable — a watch that has never
         * been online still has to draw itself.
         */
        val Default = Palette(
            name = "organic",
            bg = Color(0xFF201E1D),
            surface = Color(0xFF2B2721),
            text = Color(0xFFF3E9D8),
            accent = Color(0xFFE0864C),
            accentSoft = Color(0xFFE79968),
            sage = Color(0xFF93A86E),
            muted = Color(0xFF9C8F76),
            track = Color(0xFF453D31),
            over = Color(0xFFE06C5A),
        )

        /** Parse the `theme` object out of a summary. Any missing key keeps the default's. */
        fun from(json: JSONObject?): Palette {
            if (json == null) return Default
            fun c(key: String, fallback: Color): Color =
                runCatching {
                    val hex = json.optString(key).removePrefix("#")
                    if (hex.length != 6) fallback
                    else Color(hex.toLong(16) or 0xFF000000L)
                }.getOrDefault(fallback)

            return Palette(
                name = json.optString("name", "organic"),
                bg = c("bg", Default.bg),
                surface = c("surface", Default.surface),
                text = c("text", Default.text),
                accent = c("accent", Default.accent),
                accentSoft = c("accentSoft", Default.accentSoft),
                sage = c("accent2", Default.sage),
                muted = c("muted", Default.muted),
                track = c("track", Default.track),
                over = c("over", Default.over),
            )
        }

        /**
         * The palette to draw with right now, from whatever was last stored.
         *
         * For the tile and the complications, which are not compositions and cannot read a
         * CompositionLocal — they are rendered in the system launcher's process.
         */
        fun of(ctx: Context): Palette = from(Store.theme(ctx))
    }
}

/** So screens can read the palette without every composable taking it as a parameter. */
val LocalPalette = staticCompositionLocalOf { Palette.Default }

@Composable
fun LedgerTheme(palette: Palette = Palette.Default, content: @Composable () -> Unit) {
    CompositionLocalProvider(LocalPalette provides palette) {
        MaterialTheme(
            colorScheme = ColorScheme(
                primary = palette.accent,
                onPrimary = palette.bg,
                primaryContainer = palette.accentSoft,
                onPrimaryContainer = palette.bg,
                secondary = palette.sage,
                onSecondary = palette.bg,
                background = palette.bg,
                onBackground = palette.text,
                surfaceContainer = palette.surface,
                surfaceContainerLow = palette.bg,
                surfaceContainerHigh = palette.surface,
                onSurface = palette.text,
                onSurfaceVariant = palette.muted,
                outline = palette.track,
            ),
            content = content,
        )
    }
}
