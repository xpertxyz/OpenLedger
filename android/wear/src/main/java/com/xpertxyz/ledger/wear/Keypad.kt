package com.xpertxyz.ledger.wear

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalConfiguration
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

/**
 * A number pad, because the system one is a QWERTY keyboard.
 *
 * Wear's RemoteInput picker hands the wearer whatever input method they have set, and there is
 * no supported way to ask it for digits — androidx.wear.input exposes a title, labels and the
 * inputs themselves, and nothing about the keyboard. On a 1.5" screen a full QWERTY for a
 * six-digit code is genuinely unusable, so this app draws its own.
 *
 * Ten targets instead of thirty also means bigger ones, which is the actual win: every key
 * here is comfortably past the 48dp Wear asks for, on a screen where a mistyped digit costs a
 * whole re-entry.
 *
 * Voice is still offered alongside it — see the mic on the amount screen. Saying "two fifty"
 * beats three taps, right up until the recogniser mishears, which is why this exists as the
 * thing you fall back to rather than the thing you are stuck with.
 */
@Composable
fun NumberPad(
    onDigit: (Char) -> Unit,
    onDelete: () -> Unit,
    modifier: Modifier = Modifier,
    /** The decimal point. Off for a pairing code, on for an amount. */
    decimal: Boolean = false,
    /**
     * Height in dp the caller is using above the pad — the row showing what has been typed.
     */
    reserve: Int = VALUE_ROW,
    /**
     * Width in dp the caller is using either side of the pad, for keys that flank it.
     *
     * The amount screen puts its mic and confirm on the left and right edges at the vertical
     * centre. That is the widest part of a round screen and the easiest place on it to hit;
     * the corners, where they sat first, are the narrowest and the worst.
     */
    reserveWidth: Int = 0,
) {
    // Sized from the screen, not hardcoded. The first version used a fixed 50dp and the bottom
    // row — 0 and backspace — fell off a 206dp watch entirely: it scrolled, so it worked, but
    // having to scroll to reach zero on a number pad is not a keypad.
    //
    // Four rows plus the value above them have to fit inside the height, and on a ROUND screen
    // the corner keys have to stay inside the circle too, which is the tighter of the two
    // constraints. The cap keeps a large watch from drawing comically big keys.
    // Whichever runs out first. Four rows have to fit the height; three columns plus anything
    // flanking them have to fit the width. Sizing to only one of the two is how a pad ends up
    // reaching off the bottom of a small watch or off the sides of a narrow one.
    val cfg = LocalConfiguration.current
    val byHeight = ((cfg.screenHeightDp - reserve) / 4f) - GAP.value
    val byWidth  = ((cfg.screenWidthDp - reserveWidth) / 3f) - GAP.value
    val key = minOf(byHeight, byWidth).dp.coerceIn(30.dp, 52.dp)

    Column(
        modifier = modifier,
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(GAP),
    ) {
        KeyRow("123", key, onDigit)
        KeyRow("456", key, onDigit)
        KeyRow("789", key, onDigit)
        Row(horizontalArrangement = Arrangement.spacedBy(GAP)) {
            // The bottom-left slot: a decimal point on the amount screen, a mic where one is
            // offered, and an empty gap otherwise — never a third thing crowding the row.
            if (decimal) Key(".", size = key) { onDigit('.') } else Spacer(Modifier.size(key))
            Key("0", size = key) { onDigit('0') }
            Key("⌫", tint = LocalPalette.current.muted, size = key, onClick = onDelete)
        }
    }
}

@Composable
private fun KeyRow(digits: String, size: androidx.compose.ui.unit.Dp, onDigit: (Char) -> Unit) {
    Row(horizontalArrangement = Arrangement.spacedBy(GAP)) {
        digits.forEach { d -> Key(d.toString(), size = size) { onDigit(d) } }
    }
}

@Composable
fun Key(
    label: String,
    tint: Color = LocalPalette.current.text,
    bg: Color = LocalPalette.current.surface,
    size: androidx.compose.ui.unit.Dp = 44.dp,
    onClick: () -> Unit,
) {
    Box(
        modifier = Modifier
            .size(size)
            .clip(CircleShape)
            .background(bg)
            .clickable(onClick = onClick),
        contentAlignment = Alignment.Center,
    ) {
        androidx.wear.compose.material3.Text(
            label,
            fontSize = 19.sp,
            fontWeight = FontWeight.SemiBold,
            color = tint,
        )
    }
}

private val GAP = 3.dp

/** A key that sits beside the pad rather than in it — the mic, the confirm, the settings gear. */
val KeyAction = 34.dp
val KeyFlankGap = 3.dp

/** Height to leave above the pad for the value being typed. */
private const val VALUE_ROW = 34

/** The digits entered so far, in the accent, with the empty places shown as dashes. */
@Composable
fun CodeDisplay(entered: String, length: Int = 6) {
    val shown = entered.padEnd(length, '·')
    androidx.wear.compose.material3.Text(
        // Grouped 3-and-3, the same way the website prints it, so the two read as one number.
        shown.take(3) + "  " + shown.drop(3),
        fontSize = 22.sp,
        fontWeight = FontWeight.Bold,
        color = if (entered.isEmpty()) LocalPalette.current.muted else LocalPalette.current.accent,
    )
}
