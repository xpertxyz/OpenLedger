package com.xpertxyz.ledger.wear

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test

/**
 * The two pure functions in this app, and the only two with enough branching to be wrong
 * quietly. Everything else is a network call or a layout.
 *
 * Run with:  ./gradlew :wear:testDebugUnitTest
 */
class FormattingTest {

    @Test
    fun `indian grouping matches groupIndian in lib php`() {
        // The lakh/crore break is the whole point: 1000000 must read as 10,00,000.
        assertEquals("0", groupAmount(0.0, indian = true))
        assertEquals("999", groupAmount(999.0, indian = true))
        assertEquals("1,000", groupAmount(1000.0, indian = true))
        assertEquals("10,000", groupAmount(10000.0, indian = true))
        assertEquals("1,00,000", groupAmount(100000.0, indian = true))
        assertEquals("10,00,000", groupAmount(1000000.0, indian = true))
        assertEquals("1,23,45,678", groupAmount(12345678.0, indian = true))
        assertEquals("-1,00,000", groupAmount(-100000.0, indian = true))
    }

    @Test
    fun `world grouping stays in thousands`() {
        assertEquals("1,000,000", groupAmount(1000000.0, indian = false))
        assertEquals("12,345,678", groupAmount(12345678.0, indian = false))
    }

    @Test
    fun `whole rupees round rather than truncate`() {
        // 249.6 shown as 249 would make the watch and the website disagree by a rupee, which
        // is exactly the kind of thing that gets reported as "the totals are wrong".
        assertEquals("250", groupAmount(249.6, indian = true))
        assertEquals("249", groupAmount(249.4, indian = true))
    }

    @Test
    fun `paise survive when asked for`() {
        assertEquals("250.50", groupAmount(250.50, indian = true, decimals = 2))
        assertEquals("1,00,000.05", groupAmount(100000.05, indian = true, decimals = 2))
    }

    @Test
    fun `dictated amounts become something parseAmount will accept`() {
        assertEquals("250", parseSpokenAmount("250"))
        assertEquals("250", parseSpokenAmount("₹250"))
        assertEquals("250", parseSpokenAmount("250 rupees"))
        assertEquals("250", parseSpokenAmount(" 2 5 0 "))          // digits dictated singly
        assertEquals("250.50", parseSpokenAmount("250.50"))
        assertEquals("250.5", parseSpokenAmount("250,5"))          // comma as a decimal point
        assertEquals("1234", parseSpokenAmount("1,234"))           // comma as a thousands mark
        assertEquals("250.99", parseSpokenAmount("250.9999"))      // the server allows two places
        assertEquals("250", parseSpokenAmount("250."))             // trailing point dropped
    }

    @Test
    fun `nonsense is refused rather than sent`() {
        assertNull(parseSpokenAmount(null))
        assertNull(parseSpokenAmount(""))
        assertNull(parseSpokenAmount("groceries"))
        // Zero is not an expense, and the server would reject it anyway — better to say so
        // on the watch than to make a round trip to be told.
        assertNull(parseSpokenAmount("0"))
    }
}
