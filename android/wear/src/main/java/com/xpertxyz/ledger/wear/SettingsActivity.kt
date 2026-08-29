package com.xpertxyz.ledger.wear

import android.content.Intent
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.wear.compose.foundation.lazy.ScalingLazyColumn
import androidx.wear.compose.foundation.lazy.rememberScalingLazyListState
import androidx.wear.compose.material3.AppScaffold
import androidx.wear.compose.material3.Button
import androidx.wear.compose.material3.ButtonDefaults
import androidx.wear.compose.material3.FilledTonalButton
import androidx.wear.compose.material3.MaterialTheme
import androidx.wear.compose.material3.ScreenScaffold
import androidx.wear.compose.material3.Text
import androidx.wear.compose.material3.TimeText

/**
 * Which ledger to read, and how to stop reading it.
 *
 * Two ledgers exist and they are not two views of one thing: the website has its own database
 * and the phone app has a SQLite file that has never synced anywhere. Switching modes changes
 * the numbers, so this screen says which is which rather than offering a bare toggle.
 */
class SettingsActivity : ComponentActivity() {

    private var mode by mutableStateOf(Store.ONLINE)
    /** Queued for the ledger currently selected, so a switch can say what it parks. */
    private var waiting by mutableStateOf(0)

        /**
     * Held as state, not read once: a summary can arrive carrying a palette the account
     * changed on another device, and the screen it lands on should become that colour rather
     * than waiting for the next launch.
     */
    private var palette by mutableStateOf(Palette.Default)

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        palette = Palette.of(this)
        mode = Store.mode(this)
        waiting = Store.pendingHere(this).size
        setContent {
            LedgerTheme(palette) {
                AppScaffold(timeText = { TimeText() }) { Screen() }
            }
        }
    }

    @Composable
    private fun Screen() {
        val listState = rememberScalingLazyListState()
        ScreenScaffold(scrollState = listState) { contentPadding ->
            ScalingLazyColumn(
                state = listState,
                modifier = Modifier.fillMaxSize(),
                contentPadding = contentPadding,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                item { Text("Ledger", style = MaterialTheme.typography.titleSmall, color = LocalPalette.current.accent) }

                item {
                    Choice(
                        title = "Online",
                        sub = "ledger.xpertxyz.com",
                        selected = mode == Store.ONLINE,
                        onClick = { switchTo(Store.ONLINE) },
                    )
                }
                item {
                    Choice(
                        title = "This phone",
                        sub = "over Bluetooth",
                        selected = mode == Store.PHONE,
                        onClick = { switchTo(Store.PHONE) },
                    )
                }
                item {
                    Text(
                        // Said plainly, because the first thing anyone will do after switching
                        // is wonder where their money went.
                        "These are separate ledgers. Switching changes which one you see, and " +
                            "moves nothing between them.",
                        style = MaterialTheme.typography.bodySmall,
                        color = LocalPalette.current.muted,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.padding(vertical = 6.dp),
                    )
                }
                // Only when it is actually true. An expense recorded out of range is held for
                // the ledger it was meant for, so switching parks it rather than losing it —
                // but somebody watching a count go from 2 to 0 deserves to know why.
                if (waiting > 0) {
                    item {
                        Text(
                            if (waiting == 1) "1 expense is still waiting to sync here. It will go out when you switch back."
                            else "$waiting expenses are still waiting to sync here. They will go out when you switch back.",
                            style = MaterialTheme.typography.bodySmall,
                            color = LocalPalette.current.sage,
                            textAlign = TextAlign.Center,
                            modifier = Modifier.padding(bottom = 6.dp),
                        )
                    }
                }

                if (mode == Store.ONLINE && Store.token(this@SettingsActivity) != null) {
                    item {
                        Button(
                            onClick = ::signOut,
                            modifier = Modifier.fillMaxWidth(),
                            colors = ButtonDefaults.buttonColors(
                                containerColor = LocalPalette.current.over,
                                contentColor = LocalPalette.current.surface,
                            ),
                        ) { Text("Sign out") }
                    }
                    item {
                        Text(
                            "Anything still waiting to sync is dropped.",
                            style = MaterialTheme.typography.bodySmall,
                            color = LocalPalette.current.muted,
                            textAlign = TextAlign.Center,
                        )
                    }
                }

                item { VersionLine() }
            }
        }
    }

    @Composable
    private fun Choice(title: String, sub: String, selected: Boolean, onClick: () -> Unit) {
        FilledTonalButton(
            onClick = onClick,
            modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
            colors = if (selected) {
                ButtonDefaults.filledTonalButtonColors(
                    containerColor = LocalPalette.current.accent,
                    contentColor = LocalPalette.current.surface,
                    // Named explicitly. The default secondary content colour is a muted grey
                    // meant for the dark tonal background, and on the accent fill it came out
                    // grey-on-orange — the subtitle of the SELECTED row, the one row whose
                    // subtitle you most want to read, was the only unreadable thing on screen.
                    secondaryContentColor = LocalPalette.current.surface,
                )
            } else {
                ButtonDefaults.filledTonalButtonColors()
            },
            label = { Text(title, maxLines = 1) },
            secondaryLabel = { Text(sub, maxLines = 1) },
        )
    }

    private fun switchTo(next: String) {
        if (next == mode) return
        // Clears the cached summary with it — the totals on screen belong to the ledger being
        // left, and showing them under the other one's name for a beat is a lie.
        Store.setMode(this, next)
        mode = next
        LedgerTileService.refresh(this); LedgerComplication.refresh(this)
        restart()
    }

    private fun signOut() {
        Store.signOut(this)
        LedgerTileService.refresh(this); LedgerComplication.refresh(this)
        restart()
    }

    /** Back to the top, with the task cleared, so there is no stale screen behind this one. */
    private fun restart() {
        startActivity(
            Intent(this, MainActivity::class.java)
                .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
        )
        finish()
    }
}
