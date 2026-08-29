package com.xpertxyz.ledger.wear

import android.app.RemoteInput
import android.content.Intent
import android.os.Bundle
import android.os.VibrationEffect
import android.os.Vibrator
import androidx.activity.ComponentActivity
import androidx.activity.addCallback
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
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
import androidx.wear.compose.material3.FilledTonalButton
import androidx.wear.compose.material3.MaterialTheme
import androidx.wear.compose.material3.ScreenScaffold
import androidx.wear.compose.material3.Text
import androidx.wear.compose.material3.TimeText
import kotlinx.coroutines.launch
import org.json.JSONArray
import org.json.JSONObject

/**
 * Add an expense in two taps and a word.
 *
 * Amount first, then category, because that is the order the information arrives in: you know
 * what it cost before you have decided what to call it. The category list comes from the last
 * summary — already sorted by this month's spend, so the ones actually used sit under the
 * thumb — which also means this screen opens instantly and works with no signal.
 */
class AddActivity : ComponentActivity() {

    private var amount by mutableStateOf<String?>(null)
    private var busy by mutableStateOf(false)
    private var error by mutableStateOf("")
    private var done by mutableStateOf<String?>(null)
    /** What is on the keypad right now, before it is committed as [amount]. */
    private var typing by mutableStateOf("")

    private val cached: JSONObject? get() = Store.summary(this)
    private val currency: String get() = cached?.optString("currency", "₹") ?: "₹"
    private val indian: Boolean get() = (cached?.optString("numfmt", "indian") ?: "indian") == "indian"

    /** Voice, for when saying it beats tapping it. The keypad is the main path. */
    private val getAmount = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { res ->
        val typed = RemoteInput.getResultsFromIntent(res.data)?.getCharSequence(KEY_AMOUNT)?.toString()
        val parsed = parseSpokenAmount(typed)
        // Backing out of the voice screen is a decision, not a mistake — it just returns to
        // the keypad with whatever was already typed still there.
        if (parsed == null) {
            if (!typed.isNullOrBlank()) error = "That did not read as an amount."
        } else {
            error = ""
            typing = parsed
            amount = parsed
        }
    }

        /**
     * Held as state, not read once: a summary can arrive carrying a palette the account
     * changed on another device, and the screen it lands on should become that colour rather
     * than waiting for the next launch.
     */
    private var palette by mutableStateOf(Palette.Default)

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        palette = Palette.of(this)

        // The tile's + can be tapped before the watch has ever been paired, or after the
        // website disconnected it. Either way this screen has no ledger to file into and no
        // category list to offer, so hand over to the screen that can fix that rather than
        // showing an amount prompt that leads nowhere.
        if (Store.token(this) == null) {
            startActivity(Intent(this, MainActivity::class.java))
            finish()
            return
        }

        // One back press leaves the app, not one per screen.
        //
        // Adding an expense is a two-screen errand reached from a one-screen app, so the
        // default stack behaviour meant: back closes the amount screen, the ledger screen
        // underneath re-fetches and repaints — which reads as the app reopening — and a second
        // back is needed to actually get out. On a wrist that is one press too many and one
        // repaint too many.
        onBackPressedDispatcher.addCallback(this) { finishAffinity() }

        setContent {
            LedgerTheme(palette) {
                // Same reasoning as the pairing screen, and this flow is five seconds long.
                AppScaffold(timeText = {}) {
                    when {
                        done != null -> DoneScreen(done!!)
                        amount == null -> AmountPrompt()
                        else -> CategoryPicker()
                    }
                }
            }
        }
    }

    private fun askAmountByVoice() = getAmount.launch(remoteInputIntent(KEY_AMOUNT, "Amount", currency))

    @Composable
    private fun AmountPrompt() {
        ScreenScaffold {
            // No horizontal padding. The flanking keys are meant to sit at the widest part of
            // the circle, and 10dp a side was enough to push the confirm key under the bezel —
            // it rendered as a squashed pill with its right edge off the screen. A round
            // display already provides its own margin; adding one takes it from where it is
            // needed most.
            Column(
                Modifier.fillMaxSize(),
                Arrangement.Center,
                Alignment.CenterHorizontally,
            ) {
                Text(
                    if (typing.isEmpty()) currency else currency + typing,
                    fontSize = 22.sp,
                    maxLines = 1,
                    color = if (typing.isEmpty()) LocalPalette.current.muted else LocalPalette.current.accent,
                )
                if (error.isNotEmpty()) {
                    Text(error, style = MaterialTheme.typography.bodySmall, color = LocalPalette.current.over, textAlign = TextAlign.Center, maxLines = 1)
                }
                // Mic left, confirm right, both level with the middle of the pad.
                //
                // They started in the top corners, which on a round screen is exactly where
                // there is least room and least reach — the bezel clips them and the thumb has
                // furthest to travel. The sides at mid-height are the widest part of the
                // circle. Before that they were underneath the pad, off the bottom of the
                // screen entirely: a number pad with no way to accept the number.
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Key("🎤", tint = LocalPalette.current.sage, size = ACTION, onClick = ::askAmountByVoice)
                    Spacer(Modifier.width(FLANK_GAP))
                    NumberPad(
                        reserve = 32,
                        reserveWidth = ((ACTION + FLANK_GAP) * 2).value.toInt(),
                        onDigit = { d ->
                            error = ""
                            // One decimal point, two places after it, ten digits before — the
                            // same shape parseAmount() will accept, enforced here so a bad key
                            // is ignored rather than rejected after a round trip.
                            val next = typing + d
                            val ok = when {
                                d == '.' -> !typing.contains('.') && typing.isNotEmpty()
                                typing.contains('.') -> typing.substringAfter('.').length < 2
                                else -> typing.length < 10
                            }
                            if (ok) typing = next
                        },
                        onDelete = {
                            error = ""
                            if (typing.isNotEmpty()) typing = typing.dropLast(1)
                        },
                        decimal = true,
                    )
                    Spacer(Modifier.width(FLANK_GAP))
                    // The one filled key on the screen: it is the only one that leaves it.
                    Key("→", tint = LocalPalette.current.surface, bg = LocalPalette.current.accent, size = ACTION, onClick = ::commitAmount)
                }
            }
        }
    }

    /** Move to the category list, but only for something the ledger would actually take. */
    private fun commitAmount() {
        val v = parseSpokenAmount(typing)
        if (v == null) { error = "Enter an amount first."; return }
        error = ""
        amount = v
    }

    @Composable
    private fun CategoryPicker() {
        val cats = cached?.optJSONArray("categories") ?: JSONArray()
        val listState = rememberScalingLazyListState()
        ScreenScaffold(scrollState = listState) { contentPadding ->
            ScalingLazyColumn(
                state = listState,
                modifier = Modifier.fillMaxSize(),
                contentPadding = contentPadding,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                item {
                    // The amount stays on screen through the whole category list. It is the
                    // one thing that must be right, and it was typed on a screen that is now
                    // gone — tapping the wrong row is recoverable, filing the wrong figure is
                    // not noticed until the month is wrong.
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(
                            money(amount!!.toDoubleOrNull() ?: 0.0, currency, indian, decimals = 2),
                            fontSize = 30.sp,
                            color = LocalPalette.current.accent,
                        )
                        Text("on what?", style = MaterialTheme.typography.labelSmall, color = LocalPalette.current.muted)
                    }
                }
                if (busy) {
                    item { Box(Modifier.fillMaxWidth(), Alignment.Center) { CircularProgressIndicator() } }
                    return@ScalingLazyColumn
                }
                if (error.isNotEmpty()) {
                    item { Text(error, style = MaterialTheme.typography.bodySmall, color = LocalPalette.current.over, textAlign = TextAlign.Center) }
                }
                if (cats.length() == 0) {
                    // Paired, but the first summary has never arrived — a watch that was
                    // connected indoors and first used out of range. There is nothing to pick
                    // from, so say why instead of showing an empty list.
                    item {
                        Text(
                            "Open the app once with a connection to load your categories.",
                            style = MaterialTheme.typography.bodySmall,
                            color = LocalPalette.current.muted,
                            textAlign = TextAlign.Center,
                        )
                    }
                }
                for (i in 0 until cats.length()) {
                    val c = cats.optJSONObject(i) ?: continue
                    val id = c.optInt("id")
                    val name = c.optString("name")
                    item {
                        FilledTonalButton(
                            onClick = { post(id, name, "") },
                            // No note field. The category is the label, and a second input
                            // screen on every add for the sake of the one in ten spends worth
                            // naming is the wrong trade on a wrist. Notes get typed later, on
                            // the website, where typing is a thing people do.
                            modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
                            label = { Text(name, maxLines = 1) },
                        )
                    }
                }
                item {
                    Button(
                        onClick = { amount = null },
                        modifier = Modifier.fillMaxWidth().padding(top = 4.dp),
                        colors = ButtonDefaults.filledTonalButtonColors(),
                    ) { Text("Change amount") }
                }
            }
        }
    }

    @Composable
    private fun DoneScreen(text: String) {
        ScreenScaffold {
            Column(
                Modifier.fillMaxSize().padding(horizontal = 18.dp),
                Arrangement.Center,
                Alignment.CenterHorizontally,
            ) {
                Text(text, style = MaterialTheme.typography.titleSmall, color = LocalPalette.current.sage, textAlign = TextAlign.Center)
            }
        }
    }

    private fun post(categoryId: Int, categoryName: String, note: String) {
        val amt = amount ?: return
        busy = true
        error = ""
        lifecycleScope.launch {
            when (val r = Api.addExpense(this@AddActivity, amt, categoryId, note)) {
                is Api.Result.Ok -> {
                    Store.saveSummary(this@AddActivity, r.body)
                    palette = Palette.from(r.body.optJSONObject("theme"))
                    LedgerTileService.refresh(this@AddActivity); LedgerComplication.refresh(this@AddActivity)
                    finishWith("Added\n${money(amt.toDouble(), currency, indian, 2)} · $categoryName")
                }
                is Api.Result.Rejected -> { error = r.message; busy = false }
                Api.Result.Unpaired -> {
                    Store.forget(this@AddActivity)
                    error = "This watch is no longer connected."
                    busy = false
                }
                // Queued, not lost, for both: a phone that has no ledger app today may have
                // one tomorrow, and the wearer at a till needs the number recorded either way.
                Api.Result.NoPhone, Api.Result.Offline -> {
                    // Kept, not lost, and said so plainly. The wearer is at a till and needs
                    // to know the number is recorded, not that a request failed.
                    Store.queue(
                        this@AddActivity,
                        JSONObject().put("amount", amt).put("category_id", categoryId).put("note", note),
                    )
                    PendingSync.schedule(this@AddActivity)
                    finishWith(
                        if (r == Api.Result.NoPhone) "Saved\nwaiting for your phone"
                        else "Saved\nwill sync when connected"
                    )
                }
            }
        }
    }

    private fun finishWith(text: String) {
        busy = false
        done = text
        buzz()
        // Long enough to read two lines, short enough that nobody waits for it. The wrist
        // drops before this fires most of the time, which is the intended shape.
        //
        // finishAffinity, not finish: see the back handler below. After filing an expense the
        // watch should be back on the watch face, not on a ledger screen re-fetching a total
        // nobody asked to see again.
        window.decorView.postDelayed({ finishAffinity() }, 1400)
    }

    private fun buzz() {
        val v = getSystemService(Vibrator::class.java) ?: return
        runCatching { v.vibrate(VibrationEffect.createOneShot(40, VibrationEffect.DEFAULT_AMPLITUDE)) }
    }

    companion object {
        /** Small enough that the value between them keeps room to be read. */
        private val ACTION = KeyAction
        private val FLANK_GAP = KeyFlankGap
        const val KEY_AMOUNT = "amount"
    }
}
