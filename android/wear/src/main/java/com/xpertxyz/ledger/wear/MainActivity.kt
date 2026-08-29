package com.xpertxyz.ledger.wear

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.lifecycleScope
import androidx.wear.compose.foundation.lazy.ScalingLazyColumn
import androidx.wear.compose.foundation.lazy.rememberScalingLazyListState
import androidx.wear.compose.material3.AppScaffold
import androidx.wear.compose.material3.Button
import androidx.wear.compose.material3.ButtonDefaults
import androidx.wear.compose.material3.CircularProgressIndicator
import androidx.wear.compose.material3.MaterialTheme
import androidx.wear.compose.material3.ScreenScaffold
import androidx.wear.compose.material3.Text
import androidx.wear.compose.material3.TimeText
import kotlinx.coroutines.launch
import org.json.JSONObject

/**
 * The whole watch app: a pairing screen until there is a token, and the ledger after that.
 *
 * State lives in plain `mutableStateOf` on the activity. There is one screen with four values
 * on it, refreshed by one call — a ViewModel, a repository and a state holder would be three
 * files standing between this and a JSONObject.
 */
class MainActivity : ComponentActivity() {

    private var summary by mutableStateOf<JSONObject?>(null)
    private var paired by mutableStateOf(false)
    private var busy by mutableStateOf(false)
    private var message by mutableStateOf("")
    private var pendingCount by mutableStateOf(0)
    private var stale by mutableStateOf(false)
    /** Digits typed on the pairing keypad so far. */
    private var entered by mutableStateOf("")
    /**
     * Why there is nothing to show, once an attempt has actually been made.
     *
     * Null means "still trying", and only then is a spinner honest. Without this the screen
     * spun forever whenever the first fetch failed with no cache behind it — switching to a
     * phone that does not have the app, or pairing indoors and opening it out of range. A
     * spinner that will never resolve is the worst of both: it says wait, and waiting cannot
     * help.
     */
    private var loadError by mutableStateOf<String?>(null)


        /**
     * Held as state, not read once: a summary can arrive carrying a palette the account
     * changed on another device, and the screen it lands on should become that colour rather
     * than waiting for the next launch.
     */
    private var palette by mutableStateOf(Palette.Default)

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        palette = Palette.of(this)
        paired = Store.ready(this)
        summary = Store.summary(this)

        setContent {
            LedgerTheme(palette) {
                // Stable for the whole screen, and deliberately NOT dependent on whether data
                // has loaded.
                //
                // It briefly read `paired && summary != null`, which looks harmless and is
                // not: ScreenScaffold sizes its top inset from whether a clock is there, so
                // the list laid out with no clock and then the clock appeared on top of the
                // day's total. A layout that changes shape when a network call returns is a
                // layout that will always be wrong for the first second.
                //
                // The pairing keypad still gets none — there it costs more height than a whole
                // row of keys, and that decision never changes while the screen is up.
                AppScaffold(timeText = { if (paired) TimeText() }) {
                    if (!paired) PairScreen() else HomeScreen()
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        paired = Store.ready(this)
        loadError = null
        pendingCount = Store.pendingHere(this).size
        if (pendingCount > 0) PendingSync.schedule(this)
        if (paired) refresh()
    }

    // ── Pairing ──────────────────────────────────────────────────────────

    @Composable
    private fun PairScreen() {
        ScreenScaffold {
            // No horizontal padding, for the same reason as the amount screen: the flanking
            // key has to clear the bezel, and a round display supplies its own margin.
            Column(
                modifier = Modifier.fillMaxSize(),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                Text(
                    // Short, because the keypad below needs the room. The long version of this
                    // instruction lives on the website, next to the button being described.
                    if (entered.isEmpty()) "Code from the website" else " ",
                    style = MaterialTheme.typography.labelSmall,
                    color = LocalPalette.current.muted,
                    textAlign = TextAlign.Center,
                )
                Spacer(Modifier.height(8.dp))
                if (busy) {
                    CircularProgressIndicator(modifier = Modifier.height(28.dp))
                } else {
                    CodeDisplay(entered)
                    if (message.isNotEmpty()) {
                        Text(message, style = MaterialTheme.typography.bodySmall, color = LocalPalette.current.over, textAlign = TextAlign.Center)
                    }
                    Spacer(Modifier.height(2.dp))
                    // The gear sits BESIDE the pad, not under it.
                    //
                    // It was a full-width button below the keypad, which on a 206dp screen is
                    // simply off the bottom of a column that does not scroll — so a watch that
                    // had never been paired online had no way at all to reach the setting that
                    // would let it read the phone instead. The one escape from this screen was
                    // unreachable, which is worse than not having built it.
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Key(
                            "⚙",
                            tint = LocalPalette.current.sage,
                            size = KeyAction,
                            onClick = { startActivity(Intent(this@MainActivity, SettingsActivity::class.java)) },
                        )
                        Spacer(Modifier.width(KeyFlankGap))
                        NumberPad(
                            reserve = 44,
                            reserveWidth = ((KeyAction + KeyFlankGap) * 2).value.toInt(),
                            onDigit = { d ->
                                if (entered.length < 6) {
                                    message = ""
                                    entered += d
                                    // Submits itself on the sixth digit. There is no ambiguity
                                    // about when a six-digit code is finished, so a confirm
                                    // button would be a tap that never carries a decision.
                                    if (entered.length == 6) submitCode(entered)
                                }
                            },
                            onDelete = { if (entered.isNotEmpty()) { entered = entered.dropLast(1); message = "" } },
                        )
                        // Keeps the pad centred; the gear has no counterpart on this screen.
                        Spacer(Modifier.width(KeyFlankGap))
                        Spacer(Modifier.size(KeyAction))
                    }
                }
            }
        }
    }

    private fun submitCode(code: String) {
        busy = true
        lifecycleScope.launch {
            when (val r = Api.pair(code, android.os.Build.MODEL ?: "Watch")) {
                is Api.Result.Ok -> {
                    Store.saveToken(this@MainActivity, r.body.optString("token"))
                    Store.saveSummary(this@MainActivity, r.body)
                    summary = r.body
                    palette = Palette.from(r.body.optJSONObject("theme"))
                    paired = true
                    message = ""
                    LedgerTileService.refresh(this@MainActivity); LedgerComplication.refresh(this@MainActivity)
                }
                // Every failure clears the field. Six digits are quicker to retype than to
                // audit, and a half-corrected wrong code is the thing that gets typed twice.
                is Api.Result.Rejected -> { message = r.message; entered = "" }
                Api.Result.Unpaired -> { message = "That code did not work."; entered = "" }
                // Unreachable in practice — pairing only happens in Online mode — but named
                // rather than swallowed by an else, so adding a result later is a compile
                // error here instead of a silent no-op on the one screen that grants access.
                Api.Result.NoPhone,
                Api.Result.Offline -> { message = "No connection to the ledger."; entered = "" }
            }
            busy = false
        }
    }

    // ── The ledger ───────────────────────────────────────────────────────

    private fun refresh() {
        lifecycleScope.launch {
            when (val r = Api.summary(this@MainActivity)) {
                is Api.Result.Ok -> {
                    Store.saveSummary(this@MainActivity, r.body)
                    summary = r.body
                    // Adopted from the reply, so changing palette on the website shows up on
                    // the wrist at the next refresh rather than the next install.
                    palette = Palette.from(r.body.optJSONObject("theme"))
                    stale = false
                    message = ""
                    loadError = null
                    LedgerTileService.refresh(this@MainActivity); LedgerComplication.refresh(this@MainActivity)
                }
                Api.Result.Unpaired -> {
                    // The website disconnected this watch. Drop everything and start over
                    // rather than showing numbers from a ledger we can no longer read.
                    Store.forget(this@MainActivity)
                    summary = null
                    paired = false
                }
                // These leave whatever was last fetched on screen, marked as old — a stale
                // number beats a blank screen. With no cache at all there is nothing to mark,
                // so loadError is what the screen shows instead of spinning.
                is Api.Result.Rejected -> { stale = true; loadError = r.message }
                Api.Result.Offline -> {
                    stale = true
                    loadError = if (Store.mode(this@MainActivity) == Store.PHONE)
                        "Your phone did not answer. Is it nearby and unlocked?"
                    else
                        "No connection to the ledger."
                }
                Api.Result.NoPhone -> {
                    stale = true
                    // Names the actual fix. "Offline" would send someone to check their wifi
                    // for a problem that is a missing app version.
                    loadError = "No paired phone is running Open Ledger. " +
                        "Install or update the phone app, or switch back to Online."
                }
            }
        }
    }

    @Composable
    private fun HomeScreen() {
        val s = summary
        val listState = rememberScalingLazyListState()
        ScreenScaffold(scrollState = listState) { contentPadding ->
            ScalingLazyColumn(
                state = listState,
                modifier = Modifier.fillMaxSize(),
                contentPadding = contentPadding,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                // ScalingLazyColumn scales items toward the centre, so a tall first item can
                // still reach above the scaffold's inset and meet the clock. A few dp of
                // nothing at the top costs one scroll position and fixes it everywhere.
                item { Spacer(Modifier.height(6.dp)) }
                if (s == null) {
                    val err = loadError
                    if (err == null) {
                        item { Box(Modifier.fillMaxWidth(), Alignment.Center) { CircularProgressIndicator() } }
                    } else {
                        item {
                            Text(
                                err,
                                style = MaterialTheme.typography.bodySmall,
                                color = LocalPalette.current.muted,
                                textAlign = TextAlign.Center,
                                modifier = Modifier.padding(horizontal = 8.dp),
                            )
                        }
                        item {
                            Button(
                                onClick = { loadError = null; refresh() },
                                modifier = Modifier.fillMaxWidth().padding(top = 6.dp),
                                colors = ButtonDefaults.filledTonalButtonColors(),
                            ) { Text("Try again") }
                        }
                        // Always reachable from the failure. Otherwise picking a ledger the
                        // watch cannot read is a dead end with no way back to the one it can.
                        item {
                            Button(
                                onClick = { startActivity(Intent(this@MainActivity, SettingsActivity::class.java)) },
                                modifier = Modifier.fillMaxWidth().padding(top = 4.dp),
                                colors = ButtonDefaults.filledTonalButtonColors(),
                            ) { Text("Settings", style = MaterialTheme.typography.bodySmall) }
                        }
                    }
                    return@ScalingLazyColumn
                }

                val currency = s.optString("currency", "₹")
                val indian = s.optString("numfmt", "indian") == "indian"

                // The number this app exists for. Everything else on the screen is context.
                item {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text("Today", style = MaterialTheme.typography.labelSmall, color = LocalPalette.current.muted)
                        Text(
                            money(s.optDouble("today", 0.0), currency, indian),
                            fontSize = 34.sp,
                            color = LocalPalette.current.text,
                        )
                    }
                }

                item { MonthCard(s, currency, indian) }

                item {
                    Button(
                        onClick = { startActivity(Intent(this@MainActivity, AddActivity::class.java)) },
                        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = LocalPalette.current.accent, contentColor = LocalPalette.current.surface),
                    ) { Text("Add expense") }
                }

                if (pendingCount > 0) {
                    item {
                        Text(
                            if (pendingCount == 1) "1 waiting to sync" else "$pendingCount waiting to sync",
                            style = MaterialTheme.typography.bodySmall,
                            color = LocalPalette.current.sage,
                        )
                    }
                }
                if (stale) {
                    item {
                        Text(
                            "Showing the last figures — no connection.",
                            style = MaterialTheme.typography.bodySmall,
                            color = LocalPalette.current.muted,
                            textAlign = TextAlign.Center,
                        )
                    }
                }

                val top = s.optJSONArray("top")
                if (top != null && top.length() > 0) {
                    item { SectionLabel("Top categories") }
                    for (i in 0 until top.length()) {
                        val c = top.optJSONObject(i) ?: continue
                        item { CategoryRow(c, currency, indian) }
                    }
                }

                val recent = s.optJSONArray("recent")
                if (recent != null && recent.length() > 0) {
                    item { SectionLabel("Recent") }
                    for (i in 0 until recent.length()) {
                        val e = recent.optJSONObject(i) ?: continue
                        item { RecentRow(e, currency, indian) }
                    }
                }

                // Last, and labelled with what it currently does rather than "Settings" — on a
                // screen you scroll to the end of, "Reading online" answers the question you
                // came down here to ask.
                item {
                    Button(
                        onClick = { startActivity(Intent(this@MainActivity, SettingsActivity::class.java)) },
                        modifier = Modifier.fillMaxWidth().padding(top = 4.dp),
                        colors = ButtonDefaults.filledTonalButtonColors(),
                    ) {
                        Text(
                            if (Store.mode(this@MainActivity) == Store.PHONE) "Reading this phone" else "Reading online",
                            style = MaterialTheme.typography.bodySmall,
                        )
                    }
                }

                item { VersionLine() }

            }
        }
    }


}
