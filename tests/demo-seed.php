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
require __DIR__ . '/../goals.php';   // the projection engine, so seeded snapshots sit on the path

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
    // A name and a ledger title that read like somebody's, because these end up in screenshots
    // and "Me / Me" reads like a placeholder nobody bothered to fill in.
    $sub = $config['features']['google_signin'] ? 'demo-seed-user' : 'local-device-user';
    bootstrapHousehold($db, 'Aarav Sharma', '', $sub, 'Home');
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
// Notes people would actually type, per category. Generic filler ("weekend", "monthly") makes
// a screenshot look like a fixture; a row reading "sabzi mandi" or "car EMI" reads like a
// ledger somebody keeps. Blanks are in the mix on purpose — most real entries have no note.
$notesFor = [
    'Groceries'        => ['', '', 'BigBasket', 'weekly veg', 'milk and eggs', 'DMart run', 'rice and dal'],
    'Vegetables'       => ['', 'sabzi mandi', 'weekly veg', 'fruits'],
    'Rent'             => ['monthly rent'],
    'Utilities'        => ['', 'electricity', 'water bill', 'gas cylinder', 'broadband'],
    'Dining Out'       => ['', 'Sunday brunch', 'Swiggy', 'team lunch', 'birthday dinner', 'chai and samosa'],
    'Transport'        => ['', '', 'Uber', 'petrol', 'metro card', 'auto'],
    'Health'           => ['', 'pharmacy', 'dentist', 'lab test', 'checkup'],
    'Shopping'         => ['', 'Amazon', 'shoes', 'kurta', 'gift for Amma'],
    'Entertainment'    => ['', 'movie night', 'concert tickets', 'board game'],
    'Subscriptions'    => ['Spotify', 'Netflix', 'iCloud storage', 'newspaper'],
    'Education'        => ['school fees', 'books', 'online course'],
    'EMI / Loans'      => ['car EMI'],
    'Credit Card Bill' => ['HDFC card', 'Amex bill'],
];
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
            $pool   = $notesFor[$cat] ?? [''];
            $insExp->execute([
                $hid, $amount, $cats[$cat], $members[mt_rand(0, count($members) - 1)], $uid,
                $pool[mt_rand(0, count($pool) - 1)], $date, $date . ' 10:00:00',
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
// The name is what shows in the list, so it says what the money was rather than which bucket
// it landed in — the category already says that.
$earnPattern = [
    ['Salary',    1, 95000, 95000, ['Salary']],
    ['Freelance', 1,  8000, 42000, ['Design retainer', 'Logo project', 'Website build']],
    ['Interest',  1,   400,  2600, ['FD interest', 'Savings interest']],
    ['Other',     1,   500,  9000, ['Cashback', 'Sold old phone', 'Festival bonus']],
];
$insEarn = $db->prepare(
    "INSERT INTO earnings (household_id, name, amount, category_id, member_id, created_by, date, created_at)
     VALUES (?,?,?,?,?,?,?,?)"
);
$nEarn = 0;
for ($back = 13; $back >= 0; $back--) {
    foreach ($earnPattern as [$cat, $perMonth, $lo, $hi, $names]) {
        $cid = $ecats[$cat] ?? array_values($ecats)[0] ?? null;
        if (!$cid) continue;
        if ($cat !== 'Salary' && mt_rand(0, 2) === 0) continue;   // the extras are not every month
        $date = $on($back, $cat === 'Salary' ? 1 : mt_rand(3, 27));
        if ($date > $today) continue;
        $insEarn->execute([
            $hid, $names[mt_rand(0, count($names) - 1)], $lo === $hi ? $lo : mt_rand($lo, $hi), $cid,
            $members[$cat === 'Salary' ? 0 : mt_rand(0, count($members) - 1)], $uid,
            $date, $date . ' 09:00:00',
        ]);
        $nEarn++;
    }
}

// ── Investment types, nested one level, the way a household actually files them: the asset
// class is the parent and the instrument is the child. This is what the Invest tab's rollup
// and a goal's type filter are for — a goal tracking "Equity" has to pick up the SIP and the
// Stocks filed under it — and a flat list of six defaults exercises neither.
$typeId = [];
foreach ($db->query("SELECT id, name FROM investment_types WHERE household_id = $hid") as $r) {
    $typeId[$r['name']] = (int)$r['id'];
}
$mkType = $db->prepare("INSERT INTO investment_types (household_id, name, archived, target, parent_id) VALUES (?,?,0,?,?)");
$setParent = $db->prepare("UPDATE investment_types SET parent_id = ?, target = ? WHERE id = ? AND household_id = ?");
// Targets sit on the parents only. A child carrying one would be counted twice by the rollup,
// exactly as a sub-category's budget would.
foreach (['Equity' => 40000, 'Debt' => 12000] as $parent => $target) {
    if (!isset($typeId[$parent])) {
        $mkType->execute([$hid, $parent, $target, null]);
        $typeId[$parent] = (int)$db->lastInsertId();
    }
}
foreach (['SIP' => 'Equity', 'Stocks' => 'Equity', 'PPF-EPF' => 'Debt', 'FD-RD' => 'Debt'] as $child => $parent) {
    if (isset($typeId[$child], $typeId[$parent])) $setParent->execute([$typeId[$parent], 0, $typeId[$child], $hid]);
}
if (!isset($typeId['SGB'])) {                        // sovereign gold bonds, under Gold
    $mkType->execute([$hid, 'SGB', 0, $typeId['Gold'] ?? null]);
    $typeId['SGB'] = (int)$db->lastInsertId();
}

// ── Investments. A plan somebody is actually running, not a scatter: two monthly SIPs on the
// 5th that step up every January, stocks a few times a year, PPF before the March deadline,
// gold at festivals, an FD when a bonus lands. Thirty months, so a goal has real history and
// the year-by-year table has a completed year above the one in progress.
$insInv = $db->prepare(
    "INSERT INTO investments (household_id, name, amount, type, member_id, created_by, date) VALUES (?,?,?,?,?,?,?)"
);
$nInv = 0;
$startYear = (int)date('Y', strtotime("$today -29 month"));
// Aarav's own SIPs and Priya's, kept apart so the goal filter "count only entries by" has
// something to separate.
$sips = [
    ['Index Fund SIP', 15000, $members[0]],
    ['Bluechip SIP',   10000, $members[0]],
    ['Flexi Cap SIP',   8000, $members[1]],
];
for ($back = 29; $back >= 0; $back--) {
    $date = $on($back, 5);
    if ($date > $today) continue;
    // 10% more each January, the way a step-up SIP is actually mandated.
    $steps = (int)date('Y', strtotime($date)) - $startYear;
    foreach ($sips as [$name, $base, $who]) {
        $insInv->execute([$hid, $name, round($base * (1.10 ** $steps)), 'SIP', $who, $uid, $date]);
        $nInv++;
    }
    // Stocks in the months a quarter ends, out of whatever was left over.
    if (in_array((int)date('n', strtotime($date)), [3, 6, 9, 12], true)) {
        $insInv->execute([$hid, ['HDFC Bank', 'Infosys', 'ITC', 'Reliance'][mt_rand(0, 3)],
                          mt_rand(12, 40) * 1000, 'Stocks', $members[0], $uid, $on($back, 18)]);
        $nInv++;
    }
    // PPF before the 31 March cutoff, EPF is payroll so it is not logged by hand.
    if ((int)date('n', strtotime($date)) === 3) {
        $insInv->execute([$hid, 'PPF top-up', 150000, 'PPF-EPF', $members[0], $uid, $on($back, 28)]);
        $nInv++;
    }
    // Gold at Dhanteras-ish, and an FD when the March bonus lands.
    if ((int)date('n', strtotime($date)) === 10) {
        $insInv->execute([$hid, 'Sovereign Gold Bond', mt_rand(25, 60) * 1000, 'SGB', $members[1], $uid, $on($back, 22)]);
        $nInv++;
    }
    if ((int)date('n', strtotime($date)) === 4) {
        $insInv->execute([$hid, 'Bank FD', mt_rand(50, 120) * 1000, 'FD-RD', $members[0], $uid, $on($back, 12)]);
        $nInv++;
    }
}
$types = $db->query("SELECT name FROM investment_types WHERE household_id = $hid")->fetchAll(PDO::FETCH_COLUMN);

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

// ── Goals. Two, because the interesting cases are the pair: one tracking a parent type for
// one person, one tracking the same class for somebody else, so "count only entries by" and
// the sub-type rollup both have something to prove.
$trackFrom = date('Y-m-01', strtotime("$today -29 month"));
$insGoal = $db->prepare(
    "INSERT INTO goals (household_id, member_id, created_by, name, target_amount, starting_corpus,
                        tracking_start, plan_start, monthly_sip, stepup_pct, stepup_month,
                        return_low, return_base, return_high, band_low, horizon_years,
                        type_filter, filter_member_id)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);
$insSnap = $db->prepare(
    "INSERT INTO goal_snapshots (goal_id, household_id, as_of, current_value, note, created_by, created_at)
     VALUES (?,?,?,?,?,?,?)"
);
// [name, whose, target, corpus already held, monthly SIP, types counted, whose entries count]
$goalPlans = [
    ['Retirement',      $members[0], 100000000, 800000, 25000, 'Equity', $members[0]],
    ['First crore',     $members[1], 10000000,        0,  8000, 'Equity', $members[1]],
];
$nGoal = $nSnap = 0;
foreach ($goalPlans as [$gname, $whose, $target, $corpus, $sip, $filter, $onlyBy]) {
    $insGoal->execute([
        $hid, $whose, $uid, $gname, $target, $corpus, $trackFrom, $trackFrom, $sip,
        10.00, 1, 10.00, 12.00, 14.00, 0.90, 20, $filter, $onlyBy,
    ]);
    $gid = (int)$db->lastInsertId();
    $nGoal++;
    // Snapshots every quarter for the last year and a half, read off the base projection and
    // nudged, so the statuses read like a portfolio rather than a straight line. Anchoring on
    // the engine is what keeps a seeded ledger from showing "behind" on every row.
    $proj = array_column(goalProject([
        'starting_corpus' => $corpus, 'tracking_start' => $trackFrom, 'plan_start' => $trackFrom,
        'monthly_sip' => $sip, 'stepup_pct' => 10, 'stepup_month' => 1,
        'return_low' => 10, 'return_base' => 12, 'return_high' => 14, 'horizon_years' => 20,
    ]), null, 'ym');
    $notes = ['Groww + Zerodha', 'quarter end', '', 'after the market dip', 'Groww'];
    foreach ([18, 15, 12, 9, 6, 3, 0] as $i => $back) {
        if ($gname !== 'Retirement' && $back > 6) continue;    // Priya started tracking later
        $date = $on($back, 1);
        if ($date > $today) continue;
        $row = $proj[substr($date, 0, 7)] ?? null;
        if (!$row) continue;
        $insSnap->execute([
            $gid, $hid, $date, round($row['base'] * mt_rand(94, 109) / 100, 2),
            $notes[$i % count($notes)], $uid, $date . ' 20:00:00',
        ]);
        $nSnap++;
    }
}

$db->commit();

printf(
    "seeded household %d: %d expenses, %d earnings, %d investments, %d recurring, %d members, %d budgets, %d goals, %d snapshots\n",
    $hid, $nExp, $nEarn, $nInv, count($recs), count($members), count($budgets), $nGoal, $nSnap
);
