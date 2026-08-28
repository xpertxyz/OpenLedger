package com.xpertxyz.ledger.wear

import android.app.RemoteInput
import android.content.Intent
import android.os.Bundle
import android.os.VibrationEffect
import android.os.Vibrator
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
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

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // The tile's + can be tapped before the watch has ever been paired, or after the
        // website disconnected it. Either way this screen has no ledger to file into and no
        // category list to offer, so hand over to the screen that can fix that rather than
        // showing an amount prompt that leads nowhere.
        if (Store.token(this) == null) {
            startActivity(Intent(this, MainActivity::class.java))
            finish()
            return
        }

        setContent {
            LedgerTheme {
                AppScaffold(timeText = { TimeText() }) {
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
            Column(
                Modifier.fillMaxSize().padding(horizontal = 10.dp),
                Arrangement.Center,
                Alignment.CenterHorizontally,
            ) {
                Text(
                    if (typing.isEmpty()) currency else currency + typing,
                    fontSize = 26.sp,
                    color = if (typing.isEmpty()) Ledger.muted else Ledger.accent,
                )
                if (error.isNotEmpty()) {
                    Text(error, style = MaterialTheme.typography.bodySmall, color = Ledger.over, textAlign = TextAlign.Center)
                }
                Spacer(Modifier.height(4.dp))
                NumberPad(
                    onDigit = { d ->
                        error = ""
                        // One decimal point, two places after it, ten digits before — the same
                        // shape parseAmount() will accept, enforced here so a bad key is
                        // simply ignored rather than rejected after a round trip.
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
                Spacer(Modifier.height(6.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Key("🎤", tint = Ledger.sage, onClick = ::askAmountByVoice)
                    // The one filled key on the screen: it is the only one that leaves it.
                    Key("→", tint = Ledger.surface, bg = Ledger.accent, onClick = ::commitAmount)
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
                            color = Ledger.accent,
                        )
                        Text("on what?", style = MaterialTheme.typography.labelSmall, color = Ledger.muted)
                    }
                }
                if (busy) {
                    item { Box(Modifier.fillMaxWidth(), Alignment.Center) { CircularProgressIndicator() } }
                    return@ScalingLazyColumn
                }
                if (error.isNotEmpty()) {
                    item { Text(error, style = MaterialTheme.typography.bodySmall, color = Ledger.over, textAlign = TextAlign.Center) }
                }
                if (cats.length() == 0) {
                    // Paired, but the first summary has never arrived — a watch that was
                    // connected indoors and first used out of range. There is nothing to pick
                    // from, so say why instead of showing an empty list.
                    item {
                        Text(
                            "Open the app once with a connection to load your categories.",
                            style = MaterialTheme.typography.bodySmall,
                            color = Ledger.muted,
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
                Text(text, style = MaterialTheme.typography.titleSmall, color = Ledger.sage, textAlign = TextAlign.Center)
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
                    LedgerTileService.refresh(this@AddActivity)
                    finishWith("Added\n${money(amt.toDouble(), currency, indian, 2)} · $categoryName")
                }
                is Api.Result.Rejected -> { error = r.message; busy = false }
                Api.Result.Unpaired -> {
                    Store.forget(this@AddActivity)
                    error = "This watch is no longer connected."
                    busy = false
                }
                Api.Result.Offline -> {
                    // Kept, not lost, and said so plainly. The wearer is at a till and needs
                    // to know the number is recorded, not that a request failed.
                    Store.queue(
                        this@AddActivity,
                        JSONObject().put("amount", amt).put("category_id", categoryId).put("note", note),
                    )
                    PendingSync.schedule(this@AddActivity)
                    finishWith("Saved\nwill sync when connected")
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
        window.decorView.postDelayed({ finish() }, 1400)
    }

    private fun buzz() {
        val v = getSystemService(Vibrator::class.java) ?: return
        runCatching { v.vibrate(VibrationEffect.createOneShot(40, VibrationEffect.DEFAULT_AMPLITUDE)) }
    }

    companion object {
        const val KEY_AMOUNT = "amount"
    }
}
