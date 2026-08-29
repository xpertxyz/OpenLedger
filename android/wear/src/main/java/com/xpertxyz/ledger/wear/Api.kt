package com.xpertxyz.ledger.wear

import android.content.Context
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.io.IOException
import java.net.HttpURLConnection
import java.net.URL

/**
 * The three calls this app makes, over HttpURLConnection and nothing else.
 *
 * No Retrofit, no OkHttp, no JSON mapper: three endpoints and four fields do not earn a
 * dependency, and every kilobyte matters more on a watch than it does on a phone.
 *
 * On a Bluetooth-only watch there is no direct route to the internet — Wear proxies TCP
 * through the paired phone, which is slower and drops more often than wifi. Hence the
 * generous timeouts, and hence [Result.Offline] being a first-class answer rather than an
 * error: out of range is the normal state of a watch, not a fault.
 */
object Api {

    private const val BASE = BuildConfig.LEDGER_URL

    sealed interface Result {
        data class Ok(val body: JSONObject) : Result
        /** The server answered and said no. [message] is written for a human — show it verbatim. */
        data class Rejected(val message: String) : Result
        /** The token is dead. Forget it and go back to the pairing screen. */
        data object Unpaired : Result
        /** No route to the server. Nothing is wrong; the watch is out of range. */
        data object Offline : Result
        /**
         * Phone mode, and no paired phone is advertising the ledger app.
         *
         * Distinct from [Offline] because the fix is completely different and the wearer
         * cannot guess it: out of range resolves itself, a phone without the app installed
         * never will. Treated like Offline for queueing — the app may yet be installed — but
         * it gets its own message.
         */
        data object NoPhone : Result
    }

    suspend fun pair(code: String, label: String): Result =
        call("/api/pair", "POST", null, JSONObject().put("code", code).put("label", label))

    /**
     * The ledger this watch is set to read, whichever that is.
     *
     * The mode switch lives here and nowhere else. Screens, the tile and the complications all
     * call this and never learn whether the answer arrived over HTTPS from the website or over
     * Bluetooth from the phone — which is what lets one set of UI serve both.
     */
    suspend fun summary(ctx: Context): Result =
        if (Store.mode(ctx) == Store.PHONE) PhoneBackend.summary(ctx)
        else call("/api/summary", "GET", Store.token(ctx), null)

    /**
     * @param retry true only when this is a second attempt at an expense that may already have
     *   landed — see PendingSync. The server dedupes on it; a first attempt never sets it, so
     *   two coffees ten seconds apart still make two rows.
     */
    suspend fun addExpense(ctx: Context, amount: String, categoryId: Int, note: String, retry: Boolean = false): Result {
        // The phone path needs no dedupe key. `retry` guards against a lost HTTP reply; a Data
        // Layer request either returns the phone's answer or throws, and a throw means the PHP
        // never ran.
        if (Store.mode(ctx) == Store.PHONE) return PhoneBackend.addExpense(ctx, amount, categoryId, note)
        return call(
            "/api/expense", "POST", Store.token(ctx),
            JSONObject()
                .put("amount", amount)
                .put("category_id", categoryId)
                .put("note", note)
                .put("retry", retry)
        )
    }

    private suspend fun call(path: String, method: String, token: String?, body: JSONObject?): Result =
        withContext(Dispatchers.IO) {
            if (method != "POST" && token == null) return@withContext Result.Unpaired
            val conn = (URL(BASE + path).openConnection() as HttpURLConnection).apply {
                requestMethod = method
                // Long, because the first request after the watch wakes has to bring up the
                // Bluetooth proxy before a single byte moves. Anything under ten seconds
                // reports "offline" on a watch that is merely slow.
                connectTimeout = 15_000
                readTimeout = 20_000
                setRequestProperty("Accept", "application/json")
                if (token != null) {
                    setRequestProperty("Authorization", "Bearer $token")
                    // Belt and braces for hosts whose CGI layer eats Authorization. api.php
                    // reads either one.
                    setRequestProperty("X-Ledger-Token", token)
                }
                if (body != null) {
                    doOutput = true
                    setRequestProperty("Content-Type", "application/json; charset=utf-8")
                }
            }
            try {
                if (body != null) conn.outputStream.use { it.write(body.toString().toByteArray()) }
                val code = conn.responseCode
                // errorStream, not inputStream, once the status is 4xx — reading the wrong one
                // throws and the real message (the one written for the user) is lost.
                val text = (if (code in 200..299) conn.inputStream else conn.errorStream)
                    ?.bufferedReader()?.use { it.readText() }.orEmpty()

                when {
                    code == 401 -> Result.Unpaired
                    code in 200..299 -> Result.Ok(JSONObject(text))
                    // A captive portal, a 502 from the host, a maintenance page: anything that
                    // is not our JSON is not a message worth showing, so it reads as offline.
                    else -> Result.Rejected(
                        runCatching { JSONObject(text).optString("error") }.getOrNull()
                            ?.takeIf { it.isNotBlank() } ?: "The ledger could not be reached."
                    )
                }
            } catch (e: IOException) {
                Result.Offline
            } catch (e: Exception) {
                // Malformed JSON from something that is not the ledger. Same user-visible
                // outcome as being out of range, and the same recovery: try again later.
                Result.Offline
            } finally {
                conn.disconnect()
            }
        }
}
