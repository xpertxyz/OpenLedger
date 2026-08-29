<?php
declare(strict_types=1);
/**
 * Proof that one codebase gives the same answers on MySQL and SQLite.
 *
 * The script prints a deterministic report and writes nothing permanent: everything runs
 * inside a transaction that is always rolled back, so it is safe against a real database.
 * The check is not "did it run" but "are the two reports byte-identical":
 *
 *     DB_DRIVER=sqlite DB_PATH=/tmp/hl-dual.db php tests/dual-driver.php > /tmp/a.txt
 *     php tests/dual-driver.php > /tmp/b.txt
 *     diff /tmp/a.txt /tmp/b.txt && echo SAME
 *
 * Covers what the dialect split actually touches: the date-extraction helpers, the
 * rate-limit upsert, DECIMAL vs NUMERIC money, invite expiry now that PHP owns the clock,
 * and the recurring catch-up sweep including that a second sweep posts nothing.
 */

$config = require __DIR__ . '/../config.php';
require __DIR__ . '/../lib.php';

// Fixed instant, so the report cannot differ between the two runs just because time passed.
// Everything date-shaped below is derived from it rather than from today().
const T0 = '2026-06-15';

$db = makeDb($config);
$sqlite = isSqlite($db);
printf("driver: %s\n", $sqlite ? 'sqlite' : 'mysql');

$db->beginTransaction();
try {
    // A household id far above anything real, so even a bug that escaped the rollback could
    // not land on top of a live ledger.
    $hid = 900001;
    $db->prepare("INSERT INTO households (id, name, currency, number_format) VALUES (?,?,?,?)")
       ->execute([$hid, 'Dual Driver Test', '₹', 'indian']);
    $db->prepare("INSERT INTO users (id, household_id, google_sub, email, name) VALUES (?,?,?,?,?)")
       ->execute([$hid, $hid, 'dual-test-sub', 'dual@test.local', 'Dual Tester']);
    $db->prepare("INSERT INTO household_users (household_id, user_id, role) VALUES (?,?,?)")
       ->execute([$hid, $hid, ROLE_OWNER]);
    $db->prepare("INSERT INTO members (id, household_id, name, user_id) VALUES (?,?,?,?)")
       ->execute([$hid, $hid, 'Tester', $hid]);
    $db->prepare("INSERT INTO categories (id, household_id, name, icon, is_custom) VALUES (?,?,?,?,0)")
       ->execute([$hid, $hid, 'Groceries', 'shopping-cart']);
    $db->prepare("INSERT INTO earning_categories (id, household_id, name) VALUES (?,?,?)")
       ->execute([$hid, $hid, 'Salary']);

    // ── Money. Amounts chosen so the total needs both decimal places and so a naive float
    // sum would drift: 0.1 + 0.2 is the classic, scaled up to rupees.
    echo "\n[money]\n";
    $amounts = [1234.56, 0.10, 0.20, 99999.99, 0.01, 7.07];
    $insE = $db->prepare(
        "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date) VALUES (?,?,?,?,?,?)"
    );
    foreach ($amounts as $i => $a) {
        $insE->execute([$hid, $a, $hid, $hid, "row $i", date('Y-m-d', strtotime(T0 . " +$i day"))]);
    }
    $sum = (float)$db->query("SELECT SUM(amount) FROM expenses WHERE household_id = $hid")->fetchColumn();
    printf("  rows=%d sum=%s expected=%s match=%s\n",
        count($amounts), number_format(roundMoney($sum), 2, '.', ''),
        number_format(array_sum($amounts), 2, '.', ''),
        roundMoney($sum) === roundMoney(array_sum($amounts)) ? 'yes' : 'NO');

    // ── Date extraction helpers, the ones that had to be written twice.
    echo "\n[date helpers]\n";
    $q = $db->query(
        "SELECT " . sqlYm($db, '`date`') . " AS ym, " . sqlYear($db, '`date`') . " AS y, "
        . sqlMonth($db, '`date`') . " AS m, " . sqlDay($db, '`date`') . " AS d
         FROM expenses WHERE household_id = $hid ORDER BY `date` LIMIT 3"
    );
    foreach ($q->fetchAll() as $r) {
        printf("  ym=%s y=%d m=%d d=%d\n", $r['ym'], (int)$r['y'], (int)$r['m'], (int)$r['d']);
    }
    $grouped = $db->query(
        "SELECT " . sqlYm($db, '`date`') . " AS ym, COUNT(*) n, SUM(amount) amt
         FROM expenses WHERE household_id = $hid GROUP BY ym ORDER BY ym"
    )->fetchAll();
    foreach ($grouped as $r) {
        printf("  group ym=%s n=%d amt=%s\n", $r['ym'], (int)$r['n'], number_format(roundMoney((float)$r['amt']), 2, '.', ''));
    }
    printf("  charlen(currency)=%d\n", (int)$db->query(
        "SELECT " . sqlCharLen($db, 'currency') . " FROM households WHERE id = $hid")->fetchColumn());

    // ── Rate-limit upsert: the increment branch, then the expired-window reset branch.
    echo "\n[rate limit upsert]\n";
    $db->prepare("DELETE FROM rate_limits")->execute();
    for ($i = 0; $i < 3; $i++) rateLimit($db, $config, 'dualtest', 99, 60);
    $row = $db->query("SELECT hits FROM rate_limits WHERE bucket LIKE '%dualtest'")->fetch();
    printf("  after 3 calls hits=%d (expect 3)\n", (int)$row['hits']);
    // Three calls a second apart must still count to 3. The window used to be compared
    // against the incoming window_end rather than against now, so every tick of the clock
    // reset the counter — this is the assertion that would have caught it.
    sleep(1);
    rateLimit($db, $config, 'dualtest', 99, 60);
    $row = $db->query("SELECT hits FROM rate_limits WHERE bucket LIKE '%dualtest'")->fetch();
    printf("  after a 4th a second later hits=%d (expect 4)\n", (int)$row['hits']);

    // Force the window into the past; the next call must reset to 1 rather than increment.
    $db->prepare("UPDATE rate_limits SET window_end = ? WHERE bucket LIKE '%dualtest'")->execute([time() - 1]);
    rateLimit($db, $config, 'dualtest', 99, 60);
    $row = $db->query("SELECT hits FROM rate_limits WHERE bucket LIKE '%dualtest'")->fetch();
    printf("  after window expiry hits=%d (expect 1)\n", (int)$row['hits']);

    // ── Invites. PHP writes expires_at and PHP reads it back; this is the pair that used to
    // sit in two different timezones and report 360 minutes for a 30-minute link.
    echo "\n[invite clock]\n";
    $token = mintInvite($db, $hid, $hid);
    $live  = liveInvite($db, $token);
    $secs  = $live ? strtotime((string)$live['expires_at']) - time() : -1;
    printf("  minted=%s live=%s minutes_left=%d (expect %d)\n",
        strlen($token) === 32 ? 'yes' : 'NO', $live ? 'yes' : 'NO',
        (int)round($secs / 60), INVITE_TTL_MINUTES);

    // ── Recurring catch-up. next_date four months back: the sweep must post every missed
    // period, and a second sweep immediately after must post nothing.
    echo "\n[recurring catch-up]\n";
    $start = date('Y-m-d', strtotime(today() . ' -4 month'));
    $db->prepare(
        "INSERT INTO recurring (id, household_id, name, amount, kind, category_id, member_id, frequency, next_date, created_by)
         VALUES (?,?,?,?,?,?,?,?,?,?)"
    )->execute([$hid, $hid, 'Rent', 1000.00, 'expense', $hid, $hid, 'monthly', $start, $hid]);
    $before = (int)$db->query("SELECT COUNT(*) FROM expenses WHERE recurring_id = $hid")->fetchColumn();
    sweepRecurring($db, $hid);
    $after1 = (int)$db->query("SELECT COUNT(*) FROM expenses WHERE recurring_id = $hid")->fetchColumn();
    sweepRecurring($db, $hid);
    $after2 = (int)$db->query("SELECT COUNT(*) FROM expenses WHERE recurring_id = $hid")->fetchColumn();
    $nd = (string)$db->query("SELECT next_date FROM recurring WHERE id = $hid")->fetchColumn();
    printf("  posted=%d (from %d), second sweep added=%d (expect 0), next_date>today=%s\n",
        $after1 - $before, $before, $after2 - $after1, $nd > today() ? 'yes' : 'NO');

    // ── The watch: pairing, the bearer token, and the one summary query it lives on.
    //
    // Deltas rather than absolutes, because the sections above have already put expenses on
    // today's date and the totals would then depend on when this is run. A delta is the same
    // number on both drivers and on any day.
    echo "\n[watch]\n";
    $code = mintDevicePairing($db, $hid, $hid);
    $livePair = liveDevicePairing($db, $hid);
    printf("  code digits=%s live=%s wrong_code_redeems=%s\n",
        preg_match('/^\d{6}$/', $code) ? 'yes' : 'NO',
        ($livePair && $livePair['pair_code'] === $code) ? 'yes' : 'NO',
        redeemDevicePairing($db, '000000' === $code ? '111111' : '000000', 'Nope', DEVICE_SCOPE_API) ? 'YES' : 'no');

    // The default scope, unstated, must be the narrow one.
    printf("  default scope=%s\n", $livePair['scope'] ?? 'MISSING');

    $claim = redeemDevicePairing($db, $code, 'Galaxy Watch', DEVICE_SCOPE_API);
    // The second redeem is the whole reason claimed_at and pair_code are both cleared. A code
    // that still worked after being spent would mint a second token for anyone who saw it.
    $replay = redeemDevicePairing($db, $code, 'Replay', DEVICE_SCOPE_API);
    printf("  token len=%d replay_redeems=%s garbage_token=%s\n",
        $claim ? strlen($claim['token']) : 0,
        $replay ? 'YES' : 'no',
        deviceFromToken($db, str_repeat('f', 64)) ? 'YES' : 'no');

    $dev = $claim ? deviceFromToken($db, $claim['token']) : null;
    printf("  resolves hid=%s uid=%s role=%s\n",
        $dev ? ($dev['household_id'] === $hid ? 'ok' : 'WRONG') : 'NULL',
        $dev ? ($dev['user_id'] === $hid ? 'ok' : 'WRONG') : 'NULL',
        $dev['role'] ?? 'NULL');

    $before = watchSummary($db, $hid);
    // Through createExpense(), not a raw INSERT — this is the path the watch actually takes,
    // and a driver difference in the daily-cap COUNT would surface right here.
    createExpense($db, $config, $hid, $hid, ROLE_OWNER, ['amount' => '250.50', 'category_id' => $hid, 'note' => 'from the wrist']);
    createExpense($db, $config, $hid, $hid, ROLE_OWNER, ['amount' => '99.50',  'category_id' => $hid, 'note' => '']);
    $after = watchSummary($db, $hid);
    // The investing half of the summary, which a complication divides by — so a driver that
    // returned the target as a string would surface as a percentage of NaN on a watch face.
    printf("  invested=%s target=%s pct=%d\n",
        number_format($after['invested'], 2, '.', ''),
        number_format($after['invest_target'], 2, '.', ''),
        $after['invest_pct']);
    printf("  d_today=%s d_month=%s d_count=%d top_n=%d recent_top=%s|%s\n",
        number_format($after['today'] - $before['today'], 2, '.', ''),
        number_format($after['month'] - $before['month'], 2, '.', ''),
        $after['month_count'] - $before['month_count'],
        count($after['top']),
        $after['recent'][0]['category'] ?? '-',
        number_format((float)($after['recent'][0]['amount'] ?? 0), 2, '.', ''));

    // A full-scope pairing — what a browser redeems at /pair. The scope has to survive the
    // round trip intact, because /pair is what turns it into a session with delete in it.
    $fullCode  = mintDevicePairing($db, $hid, $hid, DEVICE_SCOPE_FULL);
    $fullClaim = redeemDevicePairing($db, $fullCode, deviceLabelFromAgent(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36'
    ), DEVICE_SCOPE_FULL);
    // A full code offered to the watch endpoint must not be redeemable there either, and must
    // survive the attempt unspent.
    $crossCode = mintDevicePairing($db, $hid, $hid, DEVICE_SCOPE_FULL);
    $cross = redeemDevicePairing($db, $crossCode, 'Watch', DEVICE_SCOPE_API) ? 'REDEEMED' : 'refused';
    $stillLive = liveDevicePairing($db, $hid);
    printf("  cross-scope redeem -> %s ; code survives = %s\n",
        $cross, ($stillLive && $stillLive['pair_code'] === $crossCode) ? 'yes' : 'NO');
    $fullDev = $fullClaim ? deviceFromToken($db, $fullClaim['token']) : null;
    printf("  full: minted=%s claimed_scope=%s resolved_scope=%s label=%s\n",
        preg_match('/^\d{6}$/', $fullCode) ? 'yes' : 'NO',
        $fullClaim['scope'] ?? 'NULL',
        $fullDev['scope'] ?? 'NULL',
        $fullClaim ? 'ok' : 'NULL');

    // A scope nobody defined must be refused, not coerced. This is the assertion that stops a
    // typo in a form field from quietly becoming full access.
    try { mintDevicePairing($db, $hid, $hid, 'admin'); $bogus = 'ACCEPTED'; }
    catch (UserErr $e) { $bogus = 'refused'; }
    printf("  unknown scope -> %s\n", $bogus);

    // Session revocation: the row is what keeps a paired browser signed in.
    $_SESSION = ['device_id' => $fullClaim['id'] ?? 0];
    $before = deviceSessionValid($db) ? 'valid' : 'invalid';
    $db->prepare("DELETE FROM device_tokens WHERE id = ?")->execute([(int)($fullClaim['id'] ?? 0)]);
    $after = deviceSessionValid($db) ? 'STILL VALID' : 'ended';
    // A Google session carries no device_id and must never be touched by any of this.
    $_SESSION = [];
    printf("  paired session: %s -> after disconnect %s ; google session %s\n",
        $before, $after, deviceSessionValid($db) ? 'unaffected' : 'BROKEN');

    // An amount the ledger must refuse. The watch shows this message verbatim, so it has to
    // be a UserErr and not a PDOException about a NOT NULL column.
    try { createExpense($db, $config, $hid, $hid, ROLE_OWNER, ['amount' => '0', 'category_id' => $hid]); $bad = 'ACCEPTED'; }
    catch (UserErr $e) { $bad = $e->getMessage(); }
    printf("  zero amount -> %s\n", $bad);

    // ── Author/permission columns survive the round trip as integers, not strings.
    echo "\n[types]\n";
    $r = $db->query("SELECT created_by, member_id, amount FROM expenses WHERE recurring_id = $hid LIMIT 1")->fetch();
    printf("  created_by=%d member_id=%d amount=%s\n",
        (int)$r['created_by'], (int)$r['member_id'], number_format((float)$r['amount'], 2, '.', ''));

    echo "\ndone\n";
} finally {
    // Always. A failed assertion must not leave the test's household behind.
    $db->rollBack();
}
