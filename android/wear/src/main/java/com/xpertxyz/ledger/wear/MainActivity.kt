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


    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        paired = Store.token(this) != null
        summary = Store.summary(this)

        setContent {
            LedgerTheme {
                AppScaffold(timeText = { TimeText() }) {
                    if (!paired) PairScreen() else HomeScreen()
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        pendingCount = Store.pending(this).size
        if (pendingCount > 0) PendingSync.schedule(this)
        if (paired) refresh()
    }

    // ── Pairing ──────────────────────────────────────────────────────────

    @Composable
    private fun PairScreen() {
        ScreenScaffold {
            Column(
                modifier = Modifier.fillMaxSize().padding(horizontal = 10.dp),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                Text(
                    // Short, because the keypad below needs the room. The long version of this
                    // instruction lives on the website, next to the button being described.
                    if (entered.isEmpty()) "Code from the website" else " ",
                    style = MaterialTheme.typography.labelSmall,
                    color = Ledger.muted,
                    textAlign = TextAlign.Center,
                )
                Spacer(Modifier.height(8.dp))
                if (busy) {
                    CircularProgressIndicator(modifier = Modifier.height(28.dp))
                } else {
                    CodeDisplay(entered)
                    if (message.isNotEmpty()) {
                        Text(message, style = MaterialTheme.typography.bodySmall, color = Ledger.over, textAlign = TextAlign.Center)
                    }
                    Spacer(Modifier.height(4.dp))
                    NumberPad(
                        onDigit = { d ->
                            if (entered.length < 6) {
                                message = ""
                                entered += d
                                // Submits itself on the sixth digit. There is no ambiguity
                                // about when a six-digit code is finished, so a confirm button
                                // would be one tap that never carries a decision.
                                if (entered.length == 6) submitCode(entered)
                            }
                        },
                        onDelete = { if (entered.isNotEmpty()) { entered = entered.dropLast(1); message = "" } },
                    )
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
                    paired = true
                    message = ""
                    LedgerTileService.refresh(this@MainActivity)
                }
                // Every failure clears the field. Six digits are quicker to retype than to
                // audit, and a half-corrected wrong code is the thing that gets typed twice.
                is Api.Result.Rejected -> { message = r.message; entered = "" }
                Api.Result.Unpaired -> { message = "That code did not work."; entered = "" }
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
                    stale = false
                    message = ""
                    LedgerTileService.refresh(this@MainActivity)
                }
                Api.Result.Unpaired -> {
                    // The website disconnected this watch. Drop everything and start over
                    // rather than showing numbers from a ledger we can no longer read.
                    Store.forget(this@MainActivity)
                    summary = null
                    paired = false
                }
                // Both leave whatever was last fetched on screen, marked as old. A blank
                // watch face is worse than a slightly stale one.
                is Api.Result.Rejected -> stale = true
                Api.Result.Offline -> stale = true
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
                if (s == null) {
                    item { Box(Modifier.fillMaxWidth(), Alignment.Center) { CircularProgressIndicator() } }
                    return@ScalingLazyColumn
                }

                val currency = s.optString("currency", "₹")
                val indian = s.optString("numfmt", "indian") == "indian"

                // The number this app exists for. Everything else on the screen is context.
                item {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text("Today", style = MaterialTheme.typography.labelSmall, color = Ledger.muted)
                        Text(
                            money(s.optDouble("today", 0.0), currency, indian),
                            fontSize = 34.sp,
                            color = Ledger.text,
                        )
                    }
                }

                item { MonthCard(s, currency, indian) }

                item {
                    Button(
                        onClick = { startActivity(Intent(this@MainActivity, AddActivity::class.java)) },
                        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = Ledger.accent, contentColor = Ledger.surface),
                    ) { Text("Add expense") }
                }

                if (pendingCount > 0) {
                    item {
                        Text(
                            if (pendingCount == 1) "1 waiting to sync" else "$pendingCount waiting to sync",
                            style = MaterialTheme.typography.bodySmall,
                            color = Ledger.sage,
                        )
                    }
                }
                if (stale) {
                    item {
                        Text(
                            "Showing the last figures — no connection.",
                            style = MaterialTheme.typography.bodySmall,
                            color = Ledger.muted,
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
            }
        }
    }


}
