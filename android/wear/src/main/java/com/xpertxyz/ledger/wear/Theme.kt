package com.xpertxyz.ledger.wear

import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.wear.compose.material3.ColorScheme
import androidx.wear.compose.material3.MaterialTheme

/**
 * The Organic palette, dark mode, lifted from the values the website already ships.
 *
 * Deliberately hardcoded rather than parsed from design-tokens/styles.css: a watch cannot read
 * the stylesheet, and six hex values are a smaller thing to keep in step than a CSS parser.
 * They come from THEME_DARK_VARS in views.php — if the ramp is retuned there, retune it here.
 *
 * Dark only, and no palette switcher. An always-on OLED watch face is dark because a light one
 * costs battery every minute of the day, and three palettes on a 1.5" screen is a setting
 * nobody would visit twice.
 */
private val Bg        = Color(0xFF201E1D)   // --color-bg
private val Surface   = Color(0xFF2B2721)   // --color-surface
private val Text      = Color(0xFFF3E9D8)   // --color-text  / --color-neutral-900
private val Accent    = Color(0xFFE0864C)   // --color-accent
private val AccentSub = Color(0xFFE79968)   // --color-accent-700
private val Sage      = Color(0xFF93A86E)   // --color-accent-2
private val Muted     = Color(0xFF9C8F76)   // --color-neutral-600
private val Track     = Color(0xFF453D31)   // --color-neutral-300

object Ledger {
    val accent = Accent
    val accentSoft = AccentSub
    val sage = Sage
    val muted = Muted
    val track = Track
    val surface = Surface
    val text = Text
    /** A budget bar past 100%. The app's own danger red, not Material's. */
    val over = Color(0xFFE06C5A)
}

@Composable
fun LedgerTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = ColorScheme(
            primary = Accent,
            onPrimary = Bg,
            primaryContainer = Color(0xFF95592C),      // --color-accent-400
            onPrimaryContainer = Color(0xFFF8D7B8),    // --color-accent-900
            secondary = Sage,
            onSecondary = Bg,
            background = Bg,
            onBackground = Text,
            surfaceContainer = Surface,
            surfaceContainerLow = Color(0xFF2C2822),   // --color-neutral-100 (dark)
            surfaceContainerHigh = Color(0xFF363028),  // --color-neutral-200 (dark)
            onSurface = Text,
            onSurfaceVariant = Muted,
            outline = Track,
        ),
        content = content,
    )
}
