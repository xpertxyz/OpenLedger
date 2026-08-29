package com.xpertxyz.ledger.wear

import androidx.compose.ui.graphics.Color
import org.json.JSONObject
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

    // ── The palette the server sends ─────────────────────────────────────
    //
    // Every colour on the watch comes through this. A wrong parse is not a crash, it is a
    // screen quietly drawn in the wrong palette, which is exactly the sort of thing that ships.

    @Test
    fun `palette parses the hex the server sends`() {
        val p = Palette.from(
            JSONObject(
                """{"name":"harbor","bg":"#191f26","surface":"#212933","text":"#eef1f5",
                    "accent":"#6da5f8","accentSoft":"#8fb9fa","accent2":"#57bebe",
                    "muted":"#8894a0","track":"#42484f","over":"#e06c5a"}"""
            )
        )
        assertEquals("harbor", p.name)
        assertEquals(Color(0xFF191F26), p.bg)
        assertEquals(Color(0xFF6DA5F8), p.accent)
        // `accent2` on the wire, `sage` in the app — the name it was given when there was only
        // one palette and it really was sage.
        assertEquals(Color(0xFF57BEBE), p.sage)
        assertEquals(Color(0xFF42484F), p.track)
    }

    @Test
    fun `an absent palette is the shipped one`() {
        // What a watch has before its first fetch, and what an older server sends.
        assertEquals(Palette.Default, Palette.from(null))
    }

    @Test
    fun `a broken colour falls back per key rather than wholesale`() {
        // A server sending one bad value should cost one colour, not the whole screen.
        val p = Palette.from(
            JSONObject("""{"name":"plum","bg":"#231a22","accent":"nonsense","track":"#4d454b"}""")
        )
        assertEquals(Color(0xFF231A22), p.bg)
        assertEquals(Palette.Default.accent, p.accent)
        assertEquals(Color(0xFF4D454B), p.track)
        // Keys that were never sent keep the default too.
        assertEquals(Palette.Default.muted, p.muted)
    }

    @Test
    fun `colours are opaque whatever the server sends`() {
        // The hex is six digits, so alpha is ours to set. A palette that came back transparent
        // would render as an invisible app.
        val p = Palette.from(JSONObject("""{"bg":"#000000","accent":"#ffffff"}"""))
        assertEquals(1f, p.bg.alpha, 0.0001f)
        assertEquals(1f, p.accent.alpha, 0.0001f)
        assertEquals(Color(0xFF000000), p.bg)
        assertEquals(Color(0xFFFFFFFF), p.accent)
    }

    // ── Which ledger a queued expense belongs to ─────────────────────────
    //
    // Store.modeOf is the only thing standing between an expense recorded against the website
    // and that expense landing in the phone's ledger because a setting changed while it sat in
    // the queue. It is three lines and it is the most consequential three lines in the app.

    @Test
    fun `a queued expense remembers which ledger it was meant for`() {
        assertEquals(Store.ONLINE, Store.modeOf(JSONObject("""{"amount":"10","mode":"online"}""")))
        assertEquals(Store.PHONE, Store.modeOf(JSONObject("""{"amount":"10","mode":"phone"}""")))
    }

    @Test
    fun `an untagged entry is treated as online, never as whatever is current`() {
        // Written before the queue was tagged. Online was the only ledger the watch could
        // reach then, so that is where it goes — adopting it into the current mode would file
        // somebody's spend into a household that never made it.
        assertEquals(Store.ONLINE, Store.modeOf(JSONObject("""{"amount":"10"}""")))
        assertEquals(Store.ONLINE, Store.modeOf(JSONObject("""{"amount":"10","mode":""}""")))
    }

    @Test
    fun `entries split cleanly by ledger`() {
        // What PendingSync does: post the ones for the ledger in front of you, hold the rest.
        val queue = listOf(
            JSONObject("""{"amount":"10","mode":"online"}"""),
            JSONObject("""{"amount":"20","mode":"phone"}"""),
            JSONObject("""{"amount":"30"}"""),
        )
        val forOnline = queue.filter { Store.modeOf(it) == Store.ONLINE }
        val forPhone = queue.filter { Store.modeOf(it) == Store.PHONE }
        assertEquals(2, forOnline.size)
        assertEquals(1, forPhone.size)
        // Nothing is dropped and nothing is counted twice.
        assertEquals(queue.size, forOnline.size + forPhone.size)
    }
}
