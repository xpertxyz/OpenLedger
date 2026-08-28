package com.xpertxyz.ledger.wear

import android.app.RemoteInput
import android.content.Intent
import androidx.wear.input.RemoteInputIntentHelper

/**
 * Ask the wearer for a piece of text, using whatever they normally use.
 *
 * This launches the platform's own input screen rather than anything drawn here, which is the
 * point: on a Galaxy Watch that is voice, the QWERTY strip and handwriting, already configured
 * the way its owner likes. A keypad of our own would be one more thing to learn and worse at
 * every one of those three.
 */
fun remoteInputIntent(key: String, title: String, label: String): Intent {
    val input = RemoteInput.Builder(key)
        .setLabel(label)
        .setAllowFreeFormInput(true)
        .build()
    return RemoteInputIntentHelper.putTitleExtra(
        RemoteInputIntentHelper.putRemoteInputsExtra(
            RemoteInputIntentHelper.createActionRemoteInputIntent(),
            listOf(input),
        ),
        title,
    )
}

/**
 * Turn whatever came back into an amount the ledger will accept, or null.
 *
 * Dictation is the reason this is not `toDoubleOrNull()`. "Two fifty" comes back as any of
 * "250", "₹250", "250 rupees" or "250.00" depending on the recogniser and the phrasing, and
 * the handwriting recogniser likes to add a stray space. Keeping the digits and at most one
 * decimal point is the rule that survives all of them.
 *
 * parseAmount() in lib.php is still the authority — it rejects zero, negatives and anything
 * over the cap, and its message is what the wearer is shown. This only gets the string into a
 * shape worth sending.
 */
fun parseSpokenAmount(raw: String?): String? {
    if (raw == null) return null
    val sb = StringBuilder()
    var dot = false
    for (ch in raw) {
        when {
            ch.isDigit() -> sb.append(ch)
            (ch == '.' || ch == ',') && !dot && sb.isNotEmpty() -> {
                // A comma this late is a decimal separator in some locales and a thousands
                // separator in others. Treating the FIRST one after digits as the decimal
                // point is wrong for "1,234" — so only accept it when what follows is short.
                if (ch == ',' && raw.substringAfter(ch).count { it.isDigit() } > 2) continue
                sb.append('.'); dot = true
            }
        }
    }
    val s = sb.toString().trimEnd('.')
    if (s.isEmpty() || s == "0") return null
    // Two decimal places is what the server's own regex allows.
    val parts = s.split('.')
    return if (parts.size == 2) parts[0] + "." + parts[1].take(2) else s
}
