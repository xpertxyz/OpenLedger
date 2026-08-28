<?php
declare(strict_types=1);

/**
 * The JSON API the Wear OS app talks to.
 *
 * Included from index.php after the crawler files and BEFORE session_start(), because none of
 * this wants a session: a device authenticates with a bearer token on every request, so a
 * session file per watch poll would be pure litter. It opens its own connection for the same
 * reason — it runs before the one index.php makes.
 *
 * Three endpoints and no more. A watch can pair, read a summary, and file an expense; it
 * cannot edit, delete, invite, or change a setting. That is not an oversight — a token sitting
 * on a wrist should be able to do the one thing a wrist is good for and nothing else.
 *
 * Everything it does file goes through createExpense(), the same function the web form posts
 * to, so the daily cap, the amount rules and the member attribution cannot drift between the
 * two front doors.
 *
 * Expects $config (from index.php), lib.php loaded, and $path / $method set.
 */

// One shape for every answer, including the failures. A watch on a flaky Bluetooth proxy will
// meet all of them, and a client that has to guess whether the body is JSON is a client that
// crashes on the captive-portal login page some hotel wifi returns instead.
$reply = function (int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    // No CORS header on purpose. This is for a native client, and a browser that could call
    // it cross-origin would be carrying the user's cookies to an endpoint that does not want
    // them.
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

$fail = fn(int $status, string $msg): never => $reply($status, ['error' => $msg]);

try {
    $apiDb = makeDb($config);
} catch (PDOException $e) {
    // The reason stays in the log. A device is not the place to explain a database outage,
    // and the connection string is in the exception message.
    error_log('[api] db: ' . $e->getMessage());
    $fail(503, 'The ledger is unavailable. Try again shortly.');
}

// Bodies are JSON, not form-encoded: the client is Kotlin, not a <form>, and a watch sending
// an amount with a decimal point through urlencode for no reason is a bug waiting to happen.
$body = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input') ?: '';
    if (strlen($raw) > 4096) $fail(413, 'Request too large.');
    $body = json_decode($raw, true);
    if (!is_array($body)) $fail(400, 'Expected a JSON object.');
}

// ── Pairing ──────────────────────────────────────────────────────────
// Unauthenticated by definition — this is how a device gets its credential. So it is the one
// endpoint an attacker can reach, and the only thing standing between them and a token is a
// six-digit code that lives for ten minutes. The rate limit is what makes that enough: 10
// tries an hour against a 1-in-a-million code inside a 10-minute window is not an attack.
if ($path === '/api/pair' && $method === 'POST') {
    rateLimit($apiDb, $config, 'api-pair', 10, 3600);
    // DEVICE_SCOPE_API explicitly: a watch takes a watch code and nothing else. Handing it a
    // browser's full-access code because the digits happened to match would give a device on
    // a wrist the ability to delete a year of entries.
    $paired = redeemDevicePairing(
        $apiDb,
        (string)($body['code'] ?? ''),
        (string)($body['label'] ?? 'Watch'),
        DEVICE_SCOPE_API
    );
    if (!$paired) $fail(404, 'That code is wrong or has expired. Generate a new one.');
    $reply(200, ['token' => $paired['token']] + apiLedgerInfo($apiDb, $config, $paired['household_id']));
}

// ── Everything below needs a token ───────────────────────────────────
// Two spellings of the same credential. Bearer is the standard one and what the watch sends
// first; X-Ledger-Token exists because a fair number of shared hosts run PHP through CGI or
// LiteSpeed, and those strip Authorization before PHP ever sees it. Without the fallback that
// host produces a watch stuck on "Missing token" with a token it is definitely sending —
// which is a miserable thing to diagnose from a wrist. See also the mod_rewrite passthrough
// in .htaccess, which fixes it on the hosts where it can be fixed.
$auth = (string)($_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? $_SERVER['HTTP_X_LEDGER_TOKEN']
    ?? '');
if (!preg_match('/^(?:Bearer\s+)?([0-9a-f]{64})$/i', trim($auth), $m)) {
    $fail(401, 'Missing token.');
}
$device = deviceFromToken($apiDb, strtolower($m[1]));
// 401, deliberately, not 403: it tells the watch to forget what it is holding and show the
// pairing screen again, which is exactly right for a token whose ledger access was revoked.
if (!$device) $fail(401, 'This device is no longer paired.');

$hid  = $device['household_id'];
$uid  = $device['user_id'];
$role = $device['role'];

if ($path === '/api/summary' && $method === 'GET') {
    rateLimit($apiDb, $config, 'api-read', 300, 3600);
    $reply(200, watchSummary($apiDb, $hid) + apiLedgerInfo($apiDb, $config, $hid));
}

if ($path === '/api/expense' && $method === 'POST') {
    rateLimit($apiDb, $config, 'api-write', 120, 3600);

    // A retry of an expense that may already be in. The watch queues an add when it has no
    // route to the server, and "the request was sent, the reply was lost" is indistinguishable
    // from "the request never left" — so a flush of that queue could file the same coffee
    // twice. `retry` is set only by PendingSync, never on a first attempt, which is what keeps
    // two genuine coffees ten seconds apart from collapsing into one.
    if (!empty($body['retry'])) {
        $dupe = $apiDb->prepare(
            "SELECT id FROM expenses
             WHERE household_id = ? AND `date` = ? AND amount = ? AND note = ?
               AND created_by = ? AND created_at >= ?
             LIMIT 1"
        );
        $dupe->execute([
            $hid, today(), (float)($body['amount'] ?? 0), (string)($body['note'] ?? ''), $uid,
            date('Y-m-d H:i:s', time() - 900),
        ]);
        if ($dupe->fetchColumn()) {
            $reply(200, ['ok' => true, 'duplicate' => true] + watchSummary($apiDb, $hid) + apiLedgerInfo($apiDb, $config, $hid));
        }
    }

    try {
        // No `date` from the client. A watch files what is being spent now, and its clock can
        // be days out after a flat battery — the server's own today() is the honest answer.
        createExpense($apiDb, $config, $hid, $uid, $role, [
            'amount'      => (string)($body['amount'] ?? ''),
            'category_id' => (int)($body['category_id'] ?? 0),
            'note'        => (string)($body['note'] ?? ''),
        ]);
    } catch (UserErr $e) {
        // 422, not 400: the JSON parsed fine, the ledger rejected what it said. The watch
        // shows this message verbatim, so UserErr's wording is the user-facing wording.
        $fail(422, $e->getMessage());
    }
    // The fresh totals ride back on the write. Without this every add costs a second round
    // trip over a Bluetooth proxy to answer "so what is my total now" — which is the only
    // question anyone asks after adding an expense.
    $reply(200, ['ok' => true] + watchSummary($apiDb, $hid) + apiLedgerInfo($apiDb, $config, $hid));
}

$fail(404, 'No such endpoint.');

/**
 * The parts of a ledger a device caches rather than fetches: its name, its currency symbol,
 * and the categories to choose from. Sent with every reply, not just at pairing — a category
 * renamed on the website should reach the wrist on the next refresh, not on the next re-pair.
 */
function apiLedgerInfo(PDO $db, array $config, int $hid): array {
    // Currency and grouping live on the household, not the user — index.php copies them into
    // the session from exactly here. The watch formats its own numbers, so it needs both.
    $h = $db->prepare("SELECT name, currency, number_format FROM households WHERE id = ?");
    $h->execute([$hid]);
    $house = $h->fetch() ?: [];

    // Ordered by this month's spend so the categories a household actually uses sit at the top
    // of a list being scrolled with a bezel. Ties and unused categories keep the app's own
    // order behind them, so the list is stable rather than reshuffling day to day.
    $monthStart = date('Y-m-01');
    $monthEnd   = date('Y-m-d', strtotime($monthStart . ' +1 month'));
    $c = $db->prepare(
        "SELECT c.id, c.name, c.icon, COALESCE(SUM(e.amount), 0) AS used
         FROM categories c
         LEFT JOIN expenses e ON e.category_id = c.id AND e.household_id = c.household_id
                             AND e.`date` >= ? AND e.`date` < ?
         WHERE c.household_id = ?
         GROUP BY c.id, c.name, c.icon, c.is_custom
         ORDER BY used DESC, c.is_custom, c.id"
    );
    $c->execute([$monthStart, $monthEnd, $hid]);

    return [
        'ledger'     => (string)($house['name'] ?? 'Ledger'),
        'currency'   => (string)($house['currency'] ?? $config['currency'] ?? '₹'),
        'numfmt'     => (string)($house['number_format'] ?? 'indian'),
        'categories' => array_map(fn(array $r) => [
            'id'   => (int)$r['id'],
            'name' => (string)$r['name'],
            'icon' => (string)$r['icon'],
        ], $c->fetchAll()),
    ];
}
