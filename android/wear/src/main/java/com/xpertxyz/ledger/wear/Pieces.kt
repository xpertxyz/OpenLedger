package com.xpertxyz.ledger.wear

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.wear.compose.material3.MaterialTheme
import androidx.wear.compose.material3.Text
import org.json.JSONObject

/** A quiet divider-with-a-name, so the list reads as sections rather than one long column. */
@Composable
fun SectionLabel(text: String) {
    Text(
        text,
        style = MaterialTheme.typography.labelSmall,
        color = Ledger.muted,
        modifier = Modifier.padding(top = 8.dp, bottom = 2.dp),
    )
}

/** Month to date, with the household budget bar underneath when there is one to draw. */
@Composable
fun MonthCard(s: JSONObject, currency: String, indian: Boolean) {
    val spent = s.optDouble("month", 0.0)
    val budget = s.optDouble("budget", 0.0)
    val count = s.optInt("month_count", 0)
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(18.dp))
            .background(Ledger.surface)
            .padding(horizontal = 14.dp, vertical = 10.dp),
    ) {
        Row(Modifier.fillMaxWidth(), Arrangement.SpaceBetween, Alignment.CenterVertically) {
            Text(s.optString("month_label", "This month"), style = MaterialTheme.typography.labelSmall, color = Ledger.muted)
            Text(
                if (count == 1) "1 entry" else "$count entries",
                style = MaterialTheme.typography.labelSmall,
                color = Ledger.muted,
            )
        }
        Text(money(spent, currency, indian), style = MaterialTheme.typography.titleMedium, color = Ledger.text)
        if (budget > 0) {
            Spacer(Modifier.height(6.dp))
            Bar(spent, budget)
            Spacer(Modifier.height(4.dp))
            Text(
                // The number people actually want is what is LEFT, so say that, and only
                // switch to "over by" once left would be negative and read as nonsense.
                if (spent <= budget) money(budget - spent, currency, indian) + " left"
                else money(spent - budget, currency, indian) + " over",
                style = MaterialTheme.typography.labelSmall,
                color = if (spent <= budget) Ledger.sage else Ledger.over,
            )
        }
    }
}

@Composable
fun CategoryRow(c: JSONObject, currency: String, indian: Boolean) {
    val amt = c.optDouble("amt", 0.0)
    val budget = c.optDouble("budget", 0.0)
    Column(Modifier.fillMaxWidth().padding(vertical = 3.dp)) {
        Row(Modifier.fillMaxWidth(), Arrangement.SpaceBetween, Alignment.CenterVertically) {
            Text(
                c.optString("name"),
                style = MaterialTheme.typography.bodySmall,
                color = Ledger.text,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.weight(1f, fill = false),
            )
            Spacer(Modifier.height(0.dp))
            Text(money(amt, currency, indian), style = MaterialTheme.typography.bodySmall, color = Ledger.accentSoft)
        }
        if (budget > 0) {
            Spacer(Modifier.height(3.dp))
            Bar(amt, budget)
        }
    }
}

@Composable
fun RecentRow(e: JSONObject, currency: String, indian: Boolean) {
    // The note if there is one, the category if there is not. On this screen "Groceries" and
    // "milk and bread" are equally useful and there is only room for one of them.
    val note = e.optString("note").trim()
    Row(
        Modifier.fillMaxWidth().padding(vertical = 3.dp),
        Arrangement.SpaceBetween,
        Alignment.CenterVertically,
    ) {
        Text(
            note.ifEmpty { e.optString("category", "Uncategorised") },
            style = MaterialTheme.typography.bodySmall,
            color = Ledger.muted,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.weight(1f, fill = false),
        )
        Text(money(e.optDouble("amount", 0.0), currency, indian), style = MaterialTheme.typography.bodySmall, color = Ledger.text)
    }
}

/**
 * The budget bar. Clamped at full width, and coloured by whether it is over — a bar that grew
 * past its track would just render as a full one and say nothing about by how much.
 */
@Composable
private fun Bar(value: Double, of: Double) {
    val ratio = if (of <= 0) 0f else (value / of).coerceIn(0.0, 1.0).toFloat()
    Box(
        Modifier.fillMaxWidth().height(4.dp).clip(RoundedCornerShape(2.dp)).background(Ledger.track),
    ) {
        Box(
            Modifier
                .fillMaxWidth(ratio)
                .height(4.dp)
                .clip(RoundedCornerShape(2.dp))
                .background(if (value > of) Ledger.over else Ledger.sage),
        )
    }
}
