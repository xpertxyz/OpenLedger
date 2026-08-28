package com.xpertxyz.ledger.wear

import kotlin.math.abs
import kotlin.math.roundToLong

/**
 * 10,00,000 — not 1,000,000.
 *
 * The Kotlin twin of groupIndian() in lib.php, and it exists for the same reason that one
 * does: java.text.NumberFormat groups in thousands everywhere, so a ledger kept in rupees
 * would read its own totals wrong on the one screen glanced at most often. The household
 * chooses which grouping it wants; the server sends that choice as `numfmt`.
 *
 * Whole rupees by default. Paise on a 1.5" screen cost two characters and tell nobody
 * anything — the amount entry keeps them, the summary does not.
 */
fun groupAmount(value: Double, indian: Boolean, decimals: Int = 0): String {
    val neg = value < 0
    val n = abs(value)
    val whole = if (decimals == 0) n.roundToLong() else n.toLong()
    val frac = if (decimals == 0) "" else {
        // Round the fraction from the ORIGINAL value, not from what is left after truncating
        // to a Long — 99.995 has to reach ".99", not ".00" with a lost rupee.
        val cents = ((n - whole) * 100).roundToLong().coerceIn(0, 99)
        ".%02d".format(cents)
    }

    val s = whole.toString()
    val grouped = if (!indian || s.length <= 3) {
        // Western grouping, and also the Indian path for anything under a thousand.
        s.reversed().chunked(3).joinToString(",").reversed()
    } else {
        // The last three digits, then pairs: 1,23,45,678.
        val head = s.dropLast(3)
        val tail = s.takeLast(3)
        head.reversed().chunked(2).joinToString(",").reversed() + "," + tail
    }
    return (if (neg) "-" else "") + grouped + frac
}

fun money(value: Double, currency: String, indian: Boolean, decimals: Int = 0): String =
    currency + groupAmount(value, indian, decimals)
