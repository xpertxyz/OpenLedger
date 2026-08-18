<?php
declare(strict_types=1);
/**
 * Fill a ledger with a plausible year of household money, so every screen has something on it.
 *
 * This is a test fixture, not a product feature. It lives in tests/ — which .htaccess and
 * router.php both deny over HTTP — precisely so no demo-data code ships inside the app. The
 * phone gets its copy as a file, not as a code path:
 *
 *     # web, on the MySQL database the site is already using
 *     php tests/demo-seed.php
 *
 *     # phone: build the file here, push it, restart the app
 *     DB_DRIVER=sqlite DB_PATH=/tmp/demo.db php tests/demo-seed.php
 *     adb push /tmp/demo.db /data/local/tmp/demo.db
 *     adb shell run-as com.xpertxyz.ledger cp /data/local/tmp/demo.db files/ledger.db
 *     adb shell run-as com.xpertxyz.ledger rm -f files/ledger.db-wal files/ledger.db-shm
 *
 * Deleting the -wal and -shm is not optional. They belong to the database being replaced, and
 * SQLite will happily replay a stale write-ahead log on top of the new file — which is a
 * corrupt ledger that passes every check until the missing rows are noticed.
 *
 * Everything is deterministic (mt_srand with a fixed seed), so two runs on two drivers produce
 * the same ledger and the backup/restore round trip can be compared row for row.
 */

$config = require __DIR__ . '/../config.php';
require __DIR__ . '/../lib.php';

$db = makeDb($config);
mt_srand(20260818);

// Dates are computed here and bound, never asked of the database — same rule as the app.
$today = today();
$ym = fn(int $monthsAgo, int $day) => date('Y-m-d', strtotime("$today -$monthsAgo month"));
$on  = function (int $monthsAgo, int $day) use ($today): string {
    $base = strtotime(date('Y-m-01', strtotime("$today -$monthsAgo month")));
    $last = (int)date('t', $base);
    return date('Y-m-d', strtotime(date('Y-m-', $base) . min($day, $last)));
};

// ── The household. Reuse the one that is already there, so this can be run against a phone
// database the app has already created and the local user keeps working.
$hid = (int)($db->query("SELECT id FROM households ORDER BY id LIMIT 1")->fetchColumn() ?: 0);
if (!$hid) {
    $sub = $config['features']['google_signin'] ? 'demo-seed-user' : 'local-device-user';
    bootstrapHousehold($db, 'Me', 'me@localhost', $sub);
    $hid = (int)$db->query("SELECT id FROM households ORDER BY id LIMIT 1")->fetchColumn();
}
$uid = (int)$db->query("SELECT id FROM users WHERE household_id = $hid ORDER BY id LIMIT 1")->fetchColumn();

$already = (int)$db->query("SELECT COUNT(*) FROM expenses WHERE household_id = $hid")->fetchColumn();
if ($already > 0 && ($argv[1] ?? '') !== '--force') {
    fwrite(STDERR, "household $hid already has $already expenses; pass --force to add anyway\n");
    exit(1);
}

$db->beginTransaction();

// ── People. A second and third spender, so the who-filter on Expense/Earn/Invest and the
// yearly summary have something to filter. user_id stays NULL: they are labels in this
// ledger, not accounts, which is what an unshared household actually looks like.
$insMember = $db->prepare("INSERT INTO members (household_id, name, user_id) VALUES (?,?,NULL)");
foreach (['Priya', 'Arjun'] as $m) $insMember->execute([$hid, $m]);
$members = $db->query("SELECT id FROM members WHERE household_id = $hid ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

// ── Categories. Budgets on the parents so Add shows "left this month" and History shows the
// bars; one sub-category to exercise the tree and its rollup. A sub-category must not carry a
// budget of its own — preflight checks that, and the rollup would double-count.
$cats = [];
foreach ($db->query("SELECT id, name FROM categories WHERE household_id = $hid AND parent_id IS NULL") as $r) {
    $cats[$r['name']] = (int)$r['id'];
}
$budgets = ['Groceries' => 18000, 'Rent' => 32000, 'Utilities' => 6000, 'Dining Out' => 8000,
            'Transport' => 5000, 'Health' => 4000, 'Shopping' => 7000, 'Entertainment' => 3000];
$setBudget = $db->prepare("UPDATE categories SET budget = ? WHERE id = ? AND household_id = ?");
foreach ($budgets as $name => $amount) {
    if (isset($cats[$name])) $setBudget->execute([$amount, $cats[$name], $hid]);
}
if (isset($cats['Groceries'])) {
    $db->prepare("INSERT INTO categories (household_id, name, icon, is_custom, budget, parent_id) VALUES (?,?,?,1,0,?)")
       ->execute([$hid, 'Vegetables', 'shopping-cart', $cats['Groceries']]);
    $cats['Vegetables'] = (int)$db->lastInsertId();
}

// ── Expenses: 14 months back, so both the 12-month strip and the year boundary have data.
// Weighted so the shape looks like a household rather than a random scatter — rent once a
// month at a fixed amount, groceries often and small, the rest in between.
$pattern = [
    ['Rent',          1,  32000, 32000],
    ['Groceries',     9,    400,  2600],
    ['Vegetables',    4,    120,   700],
    ['Utilities',     2,    600,  3200],
    ['Dining Out',    5,    250,  1900],
    ['Transport',     6,     60,   900],
    ['Health',        1,    300,  4500],
    ['Shopping',      2,    500,  6000],
    ['Entertainment', 2,    200,  1200],
    ['Subscriptions', 2,    149,   799],
    ['Education',     1,   1000,  5000],
    ['EMI / Loans',   1,  14500, 14500],
    ['Credit Card Bill', 1, 4000, 22000],
];
$notes = ['', '', '', 'weekend', 'monthly', 'with family', 'urgent', 'gift', 'annual', 'top-up'];
$insExp = $db->prepare(
    "INSERT INTO expenses (household_id, amount, category_id, member_id, created_by, note, date, created_at)
     VALUES (?,?,?,?,?,?,?,?)"
);
$nExp = 0;
for ($back = 13; $back >= 0; $back--) {
    foreach ($pattern as [$cat, $perMonth, $lo, $hi]) {
        if (!isset($cats[$cat])) continue;
        for ($i = 0; $i < $perMonth; $i++) {
            $day = mt_rand(1, 28);
            $date = $on($back, $day);
            if ($date > $today) continue;                       // never file a future expense
            $amount = $lo === $hi ? $lo : mt_rand($lo, $hi) + mt_rand(0, 99) / 100;
            $insExp->execute([
                $hid, $amount, $cats[$cat], $members[mt_rand(0, count($members) - 1)], $uid,
                $notes[mt_rand(0, count($notes) - 1)], $date, $date . ' 10:00:00',
            ]);
            $nExp++;
        }
    }
}

// ── Earnings, so Earn's bars and its share-of-total pie both have a mix rather than one slice.
$ecats = [];
foreach ($db->query("SELECT id, name FROM earning_categories WHERE household_id = $hid") as $r) {
    $ecats[$r['name']] = (int)$r['id'];
}
$earnPattern = [['Salary', 1, 95000, 95000], ['Freelance', 1, 8000, 42000],
                ['Interest', 1, 400, 2600], ['Other', 1, 500, 9000]];
$insEarn = $db->prepare(
    "INSERT INTO earnings (household_id, name, amount, category_id, member_id, created_by, date, created_at)
     VALUES (?,?,?,?,?,?,?,?)"
);
$nEarn = 0;
for ($back = 13; $back >= 0; $back--) {
    foreach ($earnPattern as [$cat, $perMonth, $lo, $hi]) {
        $cid = $ecats[$cat] ?? array_values($ecats)[0] ?? null;
        if (!$cid) continue;
        if ($cat !== 'Salary' && mt_rand(0, 2) === 0) continue;   // the extras are not every month
        $date = $on($back, $cat === 'Salary' ? 1 : mt_rand(3, 27));
        if ($date > $today) continue;
        $insEarn->execute([
            $hid, $cat, $lo === $hi ? $lo : mt_rand($lo, $hi), $cid,
            $members[$cat === 'Salary' ? 0 : mt_rand(0, count($members) - 1)], $uid,
            $date, $date . ' 09:00:00',
        ]);
        $nEarn++;
    }
}

// ── Investments. Types must already exist in investment_types — preflight checks that every
// investment names a type the ledger knows, so this reads them rather than inventing any.
$types = $db->query("SELECT name FROM investment_types WHERE household_id = $hid")->fetchAll(PDO::FETCH_COLUMN);
$insInv = $db->prepare(
    "INSERT INTO investments (household_id, name, amount, type, member_id, created_by, date) VALUES (?,?,?,?,?,?,?)"
);
$nInv = 0;
$funds = ['Index Fund SIP', 'Bluechip SIP', 'Emergency Fund', 'Gold', 'PPF', 'Fixed Deposit'];
for ($back = 13; $back >= 0; $back--) {
    foreach ([0, 1] as $slot) {
        $date = $on($back, $slot === 0 ? 5 : 20);
        if ($date > $today) continue;
        $insInv->execute([
            $hid, $funds[mt_rand(0, count($funds) - 1)], mt_rand(2000, 25000),
            $types[mt_rand(0, count($types) - 1)], $members[mt_rand(0, count($members) - 1)], $uid, $date,
        ]);
        $nInv++;
    }
}

// ── Recurring, one of each kind, so the Recurring tab shows all three and the sweep has
// something to do. next_date is deliberately in the future: a past date would make the very
// next request post a catch-up run, which is correct behaviour but confusing in a fixture.
// No total_amount on any of them — a split plan carries start/end invariants that preflight
// enforces, and a fixture has no business generating half of one.
$next = fn(int $days) => date('Y-m-d', strtotime("$today +$days day"));
$insRec = $db->prepare(
    "INSERT INTO recurring (household_id, name, amount, kind, category_id, type, member_id,
                            frequency, next_date, start_date, created_by)
     VALUES (?,?,?,?,?,?,?,?,?,?,?)"
);
$recs = [
    ['Rent',            32000, 'expense',    $cats['Rent'] ?? null,          null,      'monthly',   3],
    ['Broadband',        1199, 'expense',    $cats['Utilities'] ?? null,     null,      'monthly',   8],
    ['Car insurance',   14500, 'expense',    $cats['EMI / Loans'] ?? null,   null,      'yearly',   45],
    ['Index Fund SIP',   5000, 'investment', null,                   $types[0] ?? null, 'monthly',   5],
    ['Salary',          95000, 'earning',    array_values($ecats)[0] ?? null, null,     'monthly',  12],
];
foreach ($recs as [$name, $amount, $kind, $cid, $type, $freq, $inDays]) {
    $insRec->execute([
        $hid, $name, $amount, $kind, $cid, $type, $members[0], $freq,
        $next($inDays), $today, $uid,
    ]);
}

$db->commit();

printf(
    "seeded household %d: %d expenses, %d earnings, %d investments, %d recurring, %d members, %d budgets\n",
    $hid, $nExp, $nEarn, $nInv, count($recs), count($members), count($budgets)
);
