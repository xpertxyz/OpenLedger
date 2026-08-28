package com.xpertxyz.ledger.wear

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
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
    /** Drawn where the decimal point would otherwise sit. Used for the mic. */
    extraKey: (@Composable () -> Unit)? = null,
) {
    Column(
        modifier = modifier.fillMaxWidth().verticalScroll(rememberScrollState()),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(4.dp),
    ) {
        KeyRow("123", onDigit)
        KeyRow("456", onDigit)
        KeyRow("789", onDigit)
        Row(horizontalArrangement = Arrangement.spacedBy(4.dp)) {
            // The bottom-left slot: a decimal point on the amount screen, a mic where one is
            // offered, and an empty gap otherwise — never a third thing crowding the row.
            when {
                decimal -> Key(".") { onDigit('.') }
                extraKey != null -> extraKey()
                else -> Spacer(Modifier.size(KEY))
            }
            Key("0") { onDigit('0') }
            Key("⌫", tint = Ledger.muted, onClick = onDelete)
        }
    }
}

@Composable
private fun KeyRow(digits: String, onDigit: (Char) -> Unit) {
    Row(horizontalArrangement = Arrangement.spacedBy(4.dp)) {
        digits.forEach { d -> Key(d.toString()) { onDigit(d) } }
    }
}

@Composable
fun Key(
    label: String,
    tint: Color = Ledger.text,
    bg: Color = Ledger.surface,
    onClick: () -> Unit,
) {
    Box(
        modifier = Modifier
            .size(KEY)
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

/**
 * Three of these plus the gaps is 158dp, which fits across the flat width of every round Wear
 * screen from the smallest 1.2" upwards. Larger keys would be nicer and would not fit.
 */
private val KEY = 50.dp

/** The digits entered so far, in the accent, with the empty places shown as dashes. */
@Composable
fun CodeDisplay(entered: String, length: Int = 6) {
    val shown = entered.padEnd(length, '·')
    androidx.wear.compose.material3.Text(
        // Grouped 3-and-3, the same way the website prints it, so the two read as one number.
        shown.take(3) + "  " + shown.drop(3),
        fontSize = 26.sp,
        fontWeight = FontWeight.Bold,
        color = if (entered.isEmpty()) Ledger.muted else Ledger.accent,
        modifier = Modifier.padding(bottom = 2.dp),
    )
}
