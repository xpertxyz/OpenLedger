<?php
declare(strict_types=1);

// ────────────────────────────────────────────────────────────────────
// Investment goals — projections, milestones, achieved-value snapshots and charts.
//
// Everything about a goal lives in this file: the engine, the queries, the POST handlers, the
// pages and the SVG. index.php only routes here; lib.php only holds the tables. The engine is
// pure functions checked by goalsSelfcheck() against the reference workbook, so the maths
// cannot drift on a tidy-up.
//
// Two facts, two sources. What was *invested* comes from the investments table — the ledger
// already records every rupee, and the goal never asks twice. What it is *worth* is a
// snapshot someone types in from the broker app, because no ledger can know that.
// ────────────────────────────────────────────────────────────────────

const GOAL_LADDER    = [25e5, 50e5, 1e7, 2e7, 3e7, 5e7, 7.5e7, 1e8];
const GOALS_MAX      = 10;    // per household
const GOAL_SNAPS_MAX = 500;   // per goal
const GOAL_MS_MAX    = 40;    // per goal
const GOAL_STALE_DAYS = 45;

// ─── Engine ─────────────────────────────────────────────────────────

// Indian financial year label for a month: April starts the year. The yearly-summary page
// carries the same rule inline; duplicated here rather than refactored out of it.
function goalFyLabel(string $ym): string {
    $y = (int)substr($ym, 0, 4);
    $m = (int)substr($ym, 5, 2);
    $start = $m >= 4 ? $y : $y - 1;
    return 'FY' . $start . '-' . substr((string)($start + 1), -2);
}

// The year a month belongs to when years roll over in $stepupMonth (1 = calendar, 4 = FY).
function goalStepYear(string $ym, int $stepupMonth): int {
    $y = (int)substr($ym, 0, 4);
    return (int)substr($ym, 5, 2) >= $stepupMonth ? $y : $y - 1;
}

// One row per month for the whole horizon. Row i (1-based) is tracking_start + (i-1) months.
// Month 0 — the corpus the day before tracking began — is not a row; the chart adds it.
//   sip_i   = 0 before plan_start, else monthly_sip × (1+stepup)^k with k the number of
//             step-up boundaries passed since plan_start
//   value_i = value_{i-1} × (1 + r/12) + sip_i        for each of low / base / high
function goalProject(array $g): array {
    $start   = substr((string)$g['tracking_start'], 0, 7);
    $planYm  = substr((string)$g['plan_start'], 0, 7);
    $n       = (int)$g['horizon_years'] * 12;
    $sip     = (float)$g['monthly_sip'];
    $step    = (float)$g['stepup_pct'] / 100;
    $stepM   = (int)$g['stepup_month'];
    $rates   = ['low' => (float)$g['return_low'] / 1200, 'base' => (float)$g['return_base'] / 1200, 'high' => (float)$g['return_high'] / 1200];
    $v       = ['low' => (float)$g['starting_corpus'], 'base' => (float)$g['starting_corpus'], 'high' => (float)$g['starting_corpus']];
    $planned = (float)$g['starting_corpus'];
    $planYear = goalStepYear($planYm, $stepM);
    $rows = [];
    for ($i = 1; $i <= $n; $i++) {
        $date = addMonths($start . '-01', $i - 1);
        $ym   = substr($date, 0, 7);
        $s    = 0.0;
        if ($ym >= $planYm) {
            $k = goalStepYear($ym, $stepM) - $planYear;
            $s = $sip * (1 + $step) ** $k;
        }
        $planned += $s;
        foreach ($rates as $x => $r) $v[$x] = $v[$x] * (1 + $r) + $s;
        $rows[] = [
            'i' => $i, 'date' => $date, 'ym' => $ym, 'cy' => (int)substr($ym, 0, 4), 'fy' => goalFyLabel($ym),
            'sip' => $s, 'planned_cum' => $planned, 'low' => $v['low'], 'base' => $v['base'], 'high' => $v['high'],
        ];
    }
    return $rows;
}

// ahead / ontrack / behind / none, judging an achieved value against its month's projection.
function goalStatus(?array $row, ?float $achieved, float $bandLow): string {
    if ($achieved === null || $row === null) return 'none';
    if ($achieved < $row['low'] * $bandLow) return 'behind';
    if ($achieved > $row['high'])           return 'ahead';
    return 'ontrack';
}

// First month in which path $x reaches $amount, or null for "after the horizon".
function goalFirstReach(array $proj, string $x, float $amount): ?array {
    foreach ($proj as $r) if ($r[$x] >= $amount) return $r;
    return null;
}

// Months from $a to $b (YYYY-MM prefixes), positive when $b is later.
function goalMonthsBetween(string $a, string $b): int {
    return ((int)substr($b, 0, 4) - (int)substr($a, 0, 4)) * 12 + ((int)substr($b, 5, 2) - (int)substr($a, 5, 2));
}

// The ladder: defaults up to 1.5× target, merged with custom rows, each with when the three
// paths reach it and when it was actually crossed (override, else first snapshot at or above).
function goalMilestones(array $g, array $proj, array $snaps, array $msRows): array {
    $target  = (float)$g['target_amount'];
    $byAmt   = [];
    foreach (GOAL_LADDER as $a) if ($a <= $target * 1.5) $byAmt[(string)$a] = ['amount' => $a, 'id' => 0, 'is_custom' => 0, 'override' => null];
    foreach ($msRows as $m) {
        $a = (float)$m['amount'];
        $byAmt[(string)$a] = ['amount' => $a, 'id' => (int)$m['id'], 'is_custom' => (int)$m['is_custom'], 'override' => $m['achieved_on']];
    }
    ksort($byAmt, SORT_NUMERIC);
    $out = [];
    foreach ($byAmt as $m) {
        $base = goalFirstReach($proj, 'base', $m['amount']);
        $ach  = $m['override'];
        if ($ach === null) {
            foreach ($snaps as $s) if ((float)$s['current_value'] >= $m['amount']) { $ach = $s['as_of']; break; }
        }
        $m['proj_low']  = goalFirstReach($proj, 'low',  $m['amount']);
        $m['proj_base'] = $base;
        $m['proj_high'] = goalFirstReach($proj, 'high', $m['amount']);
        $m['achieved_on'] = $ach;
        $m['delta_months'] = ($ach !== null && $base !== null) ? goalMonthsBetween(substr($ach, 0, 7), $base['ym']) : null;
        $out[] = $m;
    }
    return $out;
}

// The snapshot that best represents a month: the latest inside it, else the latest within
// ±45 days flagged approximate, else none.
function goalSnapshotForMonth(array $snaps, string $ym): ?array {
    $best = null;
    foreach ($snaps as $s) if (substr($s['as_of'], 0, 7) === $ym) $best = $s;
    if ($best) return $best + ['approx' => false];
    $mid = new DateTimeImmutable($ym . '-15');
    $bestGap = GOAL_STALE_DAYS + 1;
    foreach ($snaps as $s) {
        $gap = abs((int)$mid->diff(new DateTimeImmutable($s['as_of']))->format('%a'));
        if ($gap <= GOAL_STALE_DAYS && $gap <= $bestGap) { $best = $s; $bestGap = $gap; }
    }
    return $best ? $best + ['approx' => true] : null;
}

// One row per year: the December (cy) or March (fy) projection row, what was invested by then,
// and the snapshot nearest that month with its status.
function goalYearRows(array $g, array $proj, array $actuals, array $snaps, string $mode): array {
    $endMonth = $mode === 'fy' ? '03' : '12';
    $band = (float)$g['band_low'];
    $out = [];
    foreach ($proj as $r) {
        if (substr($r['ym'], 5, 2) !== $endMonth) continue;
        $snap = goalSnapshotForMonth($snaps, $r['ym']);
        $val  = $snap ? (float)$snap['current_value'] : null;
        $out[] = $r + [
            'label'      => $mode === 'fy' ? $r['fy'] : (string)$r['cy'],
            'actual_cum' => goalActualCumAt($actuals, $r['ym']),
            'band_value' => $r['low'] * $band,
            'snapshot'   => $snap,
            'status'     => goalStatus($r, $val, $band),
        ];
    }
    return $out;
}

// Cumulative actual investment up to and including month $ym; null when nothing was logged yet.
function goalActualCumAt(array $actuals, string $ym): ?float {
    $cum = null;
    foreach ($actuals as $m => $a) { if ($m > $ym) break; $cum = $a['cum']; }
    return $cum;
}

// ─── Queries ────────────────────────────────────────────────────────

// Monthly and cumulative invested since tracking_start, honouring the goal's type and member
// filters. Cumulative starts at zero, not at starting_corpus — the corpus predates tracking,
// so "actual vs planned" shows a gap until the early entries are logged. The page says so.
function goalActuals(PDO $db, array $g): array {
    $sql  = "SELECT " . sqlYm($db, '`date`') . " AS m, SUM(amount) AS amt FROM investments WHERE household_id = ? AND `date` >= ?";
    $bind = [(int)$g['household_id'], substr((string)$g['tracking_start'], 0, 7) . '-01'];
    $types = goalTypeList((string)$g['type_filter']);
    if ($types) {
        $sql .= ' AND type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
        array_push($bind, ...$types);
    }
    if ($g['filter_member_id'] !== null) { $sql .= ' AND member_id = ?'; $bind[] = (int)$g['filter_member_id']; }
    $sql .= ' GROUP BY m ORDER BY m';
    $s = $db->prepare($sql);
    $s->execute($bind);
    $out = []; $cum = 0.0;
    foreach ($s->fetchAll() as $r) {
        $cum += (float)$r['amt'];
        $out[(string)$r['m']] = ['month' => roundMoney((float)$r['amt']), 'cum' => roundMoney($cum)];
    }
    return $out;
}

function goalTypeList(string $csv): array {
    return array_values(array_filter(array_map('trim', explode(',', $csv)), fn($t) => $t !== ''));
}

function goalSnapshots(PDO $db, int $goalId): array {
    $s = $db->prepare("SELECT * FROM goal_snapshots WHERE goal_id = ? ORDER BY as_of, id");
    $s->execute([$goalId]);
    return $s->fetchAll();
}

function goalMilestoneRows(PDO $db, int $goalId): array {
    $s = $db->prepare("SELECT * FROM goal_milestones WHERE goal_id = ? ORDER BY amount");
    $s->execute([$goalId]);
    return $s->fetchAll();
}

function goalLoad(PDO $db, int $hid, int $id): array {
    $s = $db->prepare("SELECT * FROM goals WHERE id = ? AND household_id = ?");
    $s->execute([$id, $hid]);
    $g = $s->fetch();
    if (!$g) throw new UserErr('That goal no longer exists.');
    return $g;
}

// ─── Formatting ─────────────────────────────────────────────────────

// ₹25 L, ₹1.02 Cr — for axes, KPIs and milestone labels where the full figure would not fit.
// Follows the ledger's grouping choice: "world" style reads K / M instead.
function goalFmtCompact(float $n): string {
    $cur = $_SESSION['currency'] ?? '₹';
    $abs = abs($n);
    $trim = fn(float $v, int $d): string => rtrim(rtrim(number_format($v, $d, '.', ''), '0'), '.');
    if (($_SESSION['numfmt'] ?? 'indian') === 'world') {
        if ($abs >= 1e6) return $cur . $trim($n / 1e6, 2) . 'M';
        if ($abs >= 1e3) return $cur . $trim($n / 1e3, 1) . 'K';
        return fmtShort($n);
    }
    if ($abs >= 1e7) return $cur . $trim($n / 1e7, 2) . ' Cr';
    if ($abs >= 1e5) return $cur . $trim($n / 1e5, 2) . ' L';
    return fmtShort($n);
}

function goalMonthLabel(string $ym): string {
    return (new DateTimeImmutable($ym . '-01'))->format('M Y');
}

function goalDateLabel(string $ymd): string {
    return (new DateTimeImmutable($ymd))->format('j M Y');
}

// ─── Self-check ─────────────────────────────────────────────────────
// Fixture and expected values come from Praveen_Wealth_Milestones_Tracker.xlsx. If the two
// ever disagree, the sheet is the spec.
function goalsSelfcheck(): void {
    $near = fn(float $have, float $want): bool => abs($have - $want) <= $want * 0.001;
    $g = [
        'starting_corpus' => 100000, 'tracking_start' => '2026-07-01', 'plan_start' => '2027-01-01',
        'monthly_sip' => 60000, 'stepup_pct' => 10, 'stepup_month' => 1,
        'return_low' => 10, 'return_base' => 12, 'return_high' => 14, 'horizon_years' => 20,
        'target_amount' => 1e8, 'band_low' => 0.9,
    ];
    $p  = goalProject($g);
    $by = array_column($p, null, 'ym');
    assert(count($p) === 240, 'goal: 240 rows');
    assert(end($p)['date'] === '2046-06-01', 'goal: last row 2046-06');
    assert($by['2026-12']['sip'] == 0, 'goal: no sip before plan_start');
    assert($by['2027-01']['sip'] == 60000, 'goal: first sip');
    assert($near($by['2028-03']['sip'], 66000), 'goal: sip stepped up in Jan');
    assert($near($by['2027-12']['planned_cum'], 820000), 'goal: planned cum Dec 2027');
    assert($near($by['2027-12']['base'], 880565), 'goal: base Dec 2027 = ' . round($by['2027-12']['base']));
    assert($near($by['2028-03']['base'], 1107234), 'goal: base Mar 2028 = ' . round($by['2028-03']['base']));
    assert($near($by['2036-12']['base'], 20395845), 'goal: base Dec 2036 = ' . round($by['2036-12']['base']));
    foreach ([25e5 => '2029-08', 50e5 => '2031-05', 1e7 => '2033-11', 2e7 => '2036-11', 5e7 => '2041-09', 1e8 => '2045-11'] as $amt => $ym) {
        $r = goalFirstReach($p, 'base', (float)$amt);
        assert($r !== null && $r['ym'] === $ym, "goal: base reaches $amt in $ym, got " . ($r['ym'] ?? 'never'));
    }
    $row = ['low' => 870000.0, 'high' => 890000.0];
    assert(goalStatus($row, 800000.0, 0.9) === 'ontrack');
    assert(goalStatus($row, 750000.0, 0.9) === 'behind');
    assert(goalStatus($row, 950000.0, 0.9) === 'ahead');
    assert(goalStatus($row, null, 0.9)     === 'none');
    assert(goalFyLabel('2027-03') === 'FY2026-27');
    assert(goalFyLabel('2027-04') === 'FY2027-28');
    $apr = array_column(goalProject(array_replace($g, ['stepup_month' => 4])), null, 'ym');
    assert($apr['2027-03']['sip'] == 60000, 'goal: April mode holds through March');
    assert($near($apr['2027-04']['sip'], 66000), 'goal: April mode steps in April');
    // Wife fixture.
    $w  = goalProject(array_replace($g, ['starting_corpus' => 0, 'monthly_sip' => 25000]));
    $wb = array_column($w, null, 'ym');
    assert($near($wb['2027-12']['base'], 317063), 'goal: wife base Dec 2027 = ' . round($wb['2027-12']['base']));
    foreach ([25e5 => '2032-01', 1e7 => '2037-11', 2e7 => '2041-08'] as $amt => $ym) {
        $r = goalFirstReach($w, 'base', (float)$amt);
        assert($r !== null && $r['ym'] === $ym, "goal: wife reaches $amt in $ym, got " . ($r['ym'] ?? 'never'));
    }
    // Milestone ladder for a ₹2 Cr goal: 25L, 50L, 1Cr, 2Cr, 3Cr.
    $ms = goalMilestones(array_replace($g, ['target_amount' => 2e7]), $p, [['as_of' => '2029-09-05', 'current_value' => 2600000]], []);
    assert(array_column($ms, 'amount') == [25e5, 50e5, 1e7, 2e7, 3e7], 'goal: default ladder');
    assert($ms[0]['achieved_on'] === '2029-09-05' && $ms[0]['delta_months'] === -1, 'goal: first snapshot ≥ amount marks it, a month late');
    assert($ms[1]['achieved_on'] === null);
    // Year rows: December in CY mode, March in FY mode.
    $cy = goalYearRows($g, $p, [], [], 'cy');
    $fy = goalYearRows($g, $p, [], [], 'fy');
    assert($cy[0]['label'] === '2026' && $cy[0]['ym'] === '2026-12', 'goal: cy rows end in December');
    assert($fy[0]['label'] === 'FY2026-27' && $fy[0]['ym'] === '2027-03', 'goal: fy rows end in March');
    assert(goalMonthsBetween('2029-09', '2029-08') === -1);
    assert(goalFmtCompact(2500000.0) === '₹25 L' && goalFmtCompact(10200000.0) === '₹1.02 Cr' && goalFmtCompact(60000.0) === '₹60,000');
}

// ─── Validation ─────────────────────────────────────────────────────

// A number typed into a goal form, held to a range. parseAmount() is for ledger entries and
// stops at the per-entry cap; a target of ₹100 Cr is well past that and still a sane goal.
function goalNum(string $raw, float $min, float $max, string $label): float {
    $raw = trim($raw);
    if ($raw === '' || !preg_match('/^\d{1,12}(\.\d{1,2})?$/', $raw)) throw new UserErr("$label must be a number.");
    $n = (float)$raw;
    if ($n < $min || $n > $max) {
        throw new UserErr("$label must be between " . goalFmtCompact($min) . ' and ' . goalFmtCompact($max) . '.');
    }
    return round($n, 2);
}

function goalPct(string $raw, float $min, float $max, string $label): float {
    $raw = trim($raw);
    if ($raw === '' || !is_numeric($raw)) throw new UserErr("$label must be a number.");
    $n = (float)$raw;
    if ($n < $min || $n > $max) throw new UserErr("$label must be between $min and $max.");
    return round($n, 2);
}

// Everyone in the ledger may look and may log a snapshot; assumptions and milestones are the
// creator's (or the owner's) to change. Same rule as every other entry, through the same gate.
function goalRequireEditable(PDO $db, int $hid, int $id, int $uid, string $role): array {
    return requireEditable($db, 'goals', $hid, $id, $uid, $role);
}

// ─── POST handlers — each returns where to redirect ─────────────────

function goalSave(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $id = (int)($_POST['id'] ?? 0);
    $prev = $id > 0 ? goalRequireEditable($db, $hid, $id, $uid, $role) : null;

    $name   = requireStr((string)($_POST['name'] ?? ''), 80, 'Goal name');
    $target = goalNum((string)($_POST['target_amount'] ?? ''), 1e5, 1e9, 'Target');
    $corpus = goalNum((string)($_POST['starting_corpus'] ?? '0'), 0, $target, 'Starting corpus');
    $sip    = goalNum((string)($_POST['monthly_sip'] ?? ''), 0, 1e7, 'Monthly SIP');
    $step   = goalPct((string)($_POST['stepup_pct'] ?? '10'), 0, 50, 'Step-up');
    $stepM  = (int)($_POST['stepup_month'] ?? 1) === 4 ? 4 : 1;
    $low    = goalPct((string)($_POST['return_low'] ?? '10'),  0, 30, 'Low return');
    $base   = goalPct((string)($_POST['return_base'] ?? '12'), 0, 30, 'Base return');
    $high   = goalPct((string)($_POST['return_high'] ?? '14'), 0, 30, 'High return');
    if (!($low <= $base && $base <= $high)) throw new UserErr('Returns must read low ≤ base ≤ high.');
    $band   = goalPct((string)($_POST['band_low'] ?? '0.9'), 0.5, 1.0, '"Behind" threshold');
    $years  = (int)($_POST['horizon_years'] ?? 20);
    if ($years < 5 || $years > 40) throw new UserErr('Horizon must be between 5 and 40 years.');
    $track  = substr(requireDate((string)($_POST['tracking_start'] ?? ''), 'Tracking start'), 0, 7) . '-01';
    $plan   = substr(requireDate((string)($_POST['plan_start'] ?? ''), 'Plan start'), 0, 7) . '-01';
    if ($track > $plan) throw new UserErr('Tracking must start on or before the plan does.');

    // Type names re-checked against this household's list; anything else is dropped.
    $known = $db->prepare("SELECT name FROM investment_types WHERE household_id = ?");
    $known->execute([$hid]);
    $known = array_flip($known->fetchAll(PDO::FETCH_COLUMN));
    $types = array_values(array_filter(array_map('strval', (array)($_POST['types'] ?? [])), fn($t) => isset($known[$t])));
    $typeCsv = implode(',', $types);
    if (mb_strlen($typeCsv) > 500) throw new UserErr('Too many types selected.');

    // Whose goal: the same rule as whose expense — owners may name anyone, members are themselves.
    $member  = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                  $prev && $prev['member_id'] !== null ? (int)$prev['member_id'] : null);
    $fMember = $role === ROLE_OWNER ? ownedId($db, 'members', $hid, (int)($_POST['filter_member_id'] ?? 0))
                                    : ($prev ? ($prev['filter_member_id'] === null ? null : (int)$prev['filter_member_id']) : null);

    $vals = [$name, $target, $corpus, $track, $plan, $sip, $step, $stepM, $low, $base, $high, $band, $years, $typeCsv, $member, $fMember];
    if ($prev) {
        $db->prepare(
            "UPDATE goals SET name = ?, target_amount = ?, starting_corpus = ?, tracking_start = ?, plan_start = ?,
                monthly_sip = ?, stepup_pct = ?, stepup_month = ?, return_low = ?, return_base = ?, return_high = ?,
                band_low = ?, horizon_years = ?, type_filter = ?, member_id = ?, filter_member_id = ?
             WHERE id = ? AND household_id = ?"
        )->execute([...$vals, $id, $hid]);
        flash('success', 'Goal updated');
    } else {
        assertUnderLimit($db, "SELECT COUNT(*) FROM goals WHERE household_id = ?", [$hid], GOALS_MAX, 'Goals');
        $db->prepare(
            "INSERT INTO goals (name, target_amount, starting_corpus, tracking_start, plan_start, monthly_sip, stepup_pct,
                stepup_month, return_low, return_base, return_high, band_low, horizon_years, type_filter, member_id,
                filter_member_id, household_id, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([...$vals, $hid, $uid]);
        $id = (int)$db->lastInsertId();
        flash('success', 'Goal created');
    }
    return '/goals/' . $id;
}

function goalArchive(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $id = (int)($_POST['id'] ?? 0);
    $g  = goalRequireEditable($db, $hid, $id, $uid, $role);
    $to = (int)$g['archived'] ? 0 : 1;
    $db->prepare("UPDATE goals SET archived = ? WHERE id = ? AND household_id = ?")->execute([$to, $id, $hid]);
    flash('success', $to ? 'Goal archived' : 'Goal restored');
    return '/goals' . ($to ? '' : '?archived=1');
}

function goalDelete(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $id = (int)($_POST['id'] ?? 0);
    goalRequireEditable($db, $hid, $id, $uid, $role);
    $db->beginTransaction();
    try {
        $db->prepare("DELETE FROM goal_snapshots  WHERE goal_id = ? AND household_id = ?")->execute([$id, $hid]);
        $db->prepare("DELETE FROM goal_milestones WHERE goal_id = ? AND household_id = ?")->execute([$id, $hid]);
        $db->prepare("DELETE FROM goals WHERE id = ? AND household_id = ?")->execute([$id, $hid]);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
    flash('success', 'Goal deleted');
    return '/goals';
}

function goalSnapshotSave(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $gid  = (int)($_POST['goal_id'] ?? 0);
    $g    = goalLoad($db, $hid, $gid);
    $id   = (int)($_POST['id'] ?? 0);
    $date = requireDate((string)($_POST['as_of'] ?? today()), 'Date');
    if ($date > today()) throw new UserErr('A snapshot cannot be dated in the future.');
    $val  = goalNum((string)($_POST['current_value'] ?? ''), 0, 1e10, 'Current value');
    $note = optionalStr($_POST['note'] ?? '', 200, 'Note');
    if ($id > 0) {
        $row = requireEditable($db, 'goal_snapshots', $hid, $id, $uid, $role);
        if ((int)$row['goal_id'] !== $gid) throw new UserErr('That snapshot belongs to another goal.');
        $db->prepare("UPDATE goal_snapshots SET as_of = ?, current_value = ?, note = ? WHERE id = ? AND household_id = ?")
           ->execute([$date, $val, $note, $id, $hid]);
        flash('success', 'Snapshot updated');
    } else {
        assertUnderLimit($db, "SELECT COUNT(*) FROM goal_snapshots WHERE goal_id = ?", [$gid], GOAL_SNAPS_MAX, 'Snapshots');
        $db->prepare("INSERT INTO goal_snapshots (goal_id, household_id, as_of, current_value, note, created_by) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$gid, $hid, $date, $val, $note, $uid]);
        flash('success', 'Snapshot saved');
    }
    return safeRedirectTarget((string)($_POST['back'] ?? '/goals/' . $gid));
}

function goalSnapshotDelete(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $id  = (int)($_POST['id'] ?? 0);
    $row = requireEditable($db, 'goal_snapshots', $hid, $id, $uid, $role);
    $db->prepare("DELETE FROM goal_snapshots WHERE id = ? AND household_id = ?")->execute([$id, $hid]);
    flash('success', 'Snapshot deleted');
    return safeRedirectTarget((string)($_POST['back'] ?? '/goals/' . (int)$row['goal_id']));
}

// Two jobs, one route: add a custom rung, or set / clear the achieved date on any rung. A
// default rung gets a row only while it carries an override, and loses it again on clear.
function goalMilestoneSave(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $gid = (int)($_POST['goal_id'] ?? 0);
    goalRequireEditable($db, $hid, $gid, $uid, $role);
    $amount = goalNum((string)($_POST['amount'] ?? ''), 1, 1e10, 'Milestone amount');
    $find = $db->prepare("SELECT * FROM goal_milestones WHERE goal_id = ? AND household_id = ? AND amount = ?");
    $find->execute([$gid, $hid, $amount]);
    $row = $find->fetch();
    if (($_POST['mode'] ?? '') === 'achieved') {
        $on = trim((string)($_POST['achieved_on'] ?? ''));
        $on = $on === '' ? null : requireDate($on, 'Achieved on');
        $isDefault = in_array($amount, GOAL_LADDER, true) || in_array((float)$amount, array_map('floatval', GOAL_LADDER), true);
        if ($row) {
            if ($on === null && !(int)$row['is_custom']) {
                $db->prepare("DELETE FROM goal_milestones WHERE id = ? AND household_id = ?")->execute([(int)$row['id'], $hid]);
            } else {
                $db->prepare("UPDATE goal_milestones SET achieved_on = ? WHERE id = ? AND household_id = ?")->execute([$on, (int)$row['id'], $hid]);
            }
        } elseif ($on !== null) {
            if (!$isDefault) throw new UserErr('That milestone is not on this goal.');
            assertUnderLimit($db, "SELECT COUNT(*) FROM goal_milestones WHERE goal_id = ?", [$gid], GOAL_MS_MAX, 'Milestones');
            $db->prepare("INSERT INTO goal_milestones (goal_id, household_id, amount, achieved_on, is_custom) VALUES (?, ?, ?, ?, 0)")
               ->execute([$gid, $hid, $amount, $on]);
        }
        flash('success', $on === null ? 'Achieved date cleared' : 'Marked achieved');
    } else {
        if ($row || in_array((float)$amount, array_map('floatval', GOAL_LADDER), true)) throw new UserErr('That milestone is already on the ladder.');
        assertUnderLimit($db, "SELECT COUNT(*) FROM goal_milestones WHERE goal_id = ?", [$gid], GOAL_MS_MAX, 'Milestones');
        $db->prepare("INSERT INTO goal_milestones (goal_id, household_id, amount, achieved_on, is_custom) VALUES (?, ?, ?, NULL, 1)")
           ->execute([$gid, $hid, $amount]);
        flash('success', 'Milestone added');
    }
    return safeRedirectTarget((string)($_POST['back'] ?? '/goals/' . $gid));
}

function goalMilestoneDelete(PDO $db, array $config, int $hid, int $uid, string $role): string {
    $id = (int)($_POST['id'] ?? 0);
    $s  = $db->prepare("SELECT * FROM goal_milestones WHERE id = ? AND household_id = ?");
    $s->execute([$id, $hid]);
    $row = $s->fetch();
    if (!$row) throw new UserErr('That milestone no longer exists.');
    goalRequireEditable($db, $hid, (int)$row['goal_id'], $uid, $role);
    if (!(int)$row['is_custom']) throw new UserErr('Default milestones cannot be deleted — lower the target to hide them.');
    $db->prepare("DELETE FROM goal_milestones WHERE id = ? AND household_id = ?")->execute([$id, $hid]);
    flash('success', 'Milestone removed');
    return safeRedirectTarget((string)($_POST['back'] ?? '/goals/' . (int)$row['goal_id']));
}

// ─── Derived figures shared by the list and the dashboard ───────────

// Everything a card or a KPI strip needs, computed once per goal.
function goalSummary(PDO $db, array $g, string $today): array {
    $proj    = goalProject($g);
    $by      = array_column($proj, null, 'ym');
    $snaps   = goalSnapshots($db, (int)$g['id']);
    $actuals = goalActuals($db, $g);
    $ms      = goalMilestones($g, $proj, $snaps, goalMilestoneRows($db, (int)$g['id']));
    $todayYm = substr($today, 0, 7);
    $last    = $snaps ? end($snaps) : null;
    $nowRow  = $by[$todayYm] ?? ($todayYm < $proj[0]['ym'] ? null : end($proj));
    $planNow = $nowRow ? $nowRow['base'] : (float)$g['starting_corpus'];
    $current = $last ? (float)$last['current_value'] : null;
    $status  = $last ? goalStatus($by[substr($last['as_of'], 0, 7)] ?? null, $current, (float)$g['band_low']) : 'none';
    $stale   = $last && (new DateTimeImmutable($last['as_of']))->diff(new DateTimeImmutable($today))->days > GOAL_STALE_DAYS;
    $next    = null;
    foreach ($ms as $m) if ($m['achieved_on'] === null && $m['amount'] > ($current ?? 0)) { $next = $m; break; }
    // This financial year: what the plan asked for against what was logged.
    $fy = goalFyLabel($todayYm);
    $fyPlan = $fyActual = 0.0;
    foreach ($proj as $r) if ($r['fy'] === $fy && $r['ym'] <= $todayYm) $fyPlan += $r['sip'];
    foreach ($actuals as $m => $a) if (goalFyLabel($m) === $fy) $fyActual += $a['month'];
    $actualCum = $actuals ? end($actuals)['cum'] : 0.0;
    return [
        'proj' => $proj, 'by' => $by, 'snaps' => $snaps, 'actuals' => $actuals, 'ms' => $ms,
        'last' => $last, 'current' => $current, 'status' => $status, 'stale' => $stale,
        'planned_now' => $planNow, 'planned_cum' => $nowRow ? $nowRow['planned_cum'] : (float)$g['starting_corpus'],
        'actual_cum' => $actualCum, 'next' => $next, 'fy' => $fy, 'fy_plan' => $fyPlan, 'fy_actual' => $fyActual,
        'today_ym' => $todayYm,
    ];
}

function goalPill(string $status, bool $approx = false): string {
    $txt = ['ahead' => 'Ahead', 'ontrack' => 'On track', 'behind' => 'Behind', 'none' => '—'][$status] ?? '—';
    if ($status === 'none') return '<span class="muted">—</span>';
    return '<span class="tag pill-' . $status . '">' . $txt . ($approx ? ' ~' : '') . '</span>';
}

// ─── Pages ──────────────────────────────────────────────────────────

// The shell is 480px wide for phones; these pages are tables and charts, so they take the room
// a desktop has. One rule, scoped to this page, rather than a second layout().
function goalWideStyle(bool $print): string {
    $css = '<style>.col{max-width:1200px;}</style>';
    if ($print) $css .= '<style>.hdr,.tabnav,.drawer,.drawer-backdrop,.goal-actions,.goal-toggles,.icon-btn,.net-pill{display:none!important;} .col{padding-bottom:0;}</style>';
    return $css;
}

function renderGoalsIndex(PDO $db, array $user, bool $archived): void {
    $hid  = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = array_column(membersFor($db, $hid, $uid), 'label', 'id');
    $s = $db->prepare("SELECT * FROM goals WHERE household_id = ? AND archived = ? ORDER BY id");
    $s->execute([$hid, $archived ? 1 : 0]);
    $goals = $s->fetchAll();
    $today = today();
    $back  = '/goals' . ($archived ? '?archived=1' : '');
    ob_start();
    echo goalWideStyle(false);
    ?>
    <div class="goal-head goal-actions">
      <h3 style="margin:0;">Investment goals</h3>
      <div class="pill-row">
        <a class="pill-btn<?= $archived ? '' : ' on' ?>" href="/goals">Active</a>
        <a class="pill-btn<?= $archived ? ' on' : '' ?>" href="/goals?archived=1">Archived</a>
        <button class="pill-btn act" type="button" onclick="document.getElementById('goal-dlg').showModal()"><?= icon('plus', 14) ?> Add goal</button>
      </div>
    </div>
    <?php if (!$goals): ?>
      <div class="empty"><?= $archived ? 'No archived goals.' : 'No goals yet. Add one to see where your SIPs are heading.' ?></div>
    <?php endif; ?>
    <div class="goal-cards">
    <?php foreach ($goals as $g): $S = goalSummary($db, $g, $today); $target = (float)$g['target_amount'];
          $pct = $S['current'] !== null ? min(100, $S['current'] / $target * 100) : 0; ?>
      <a class="card elev-sm goal-card" href="<?= '/goals/' . (int)$g['id'] ?>">
        <div class="goal-card-top">
          <span class="card-title"><?= h($g['name']) ?><?= $g['member_id'] !== null && isset($mems[(int)$g['member_id']]) ? ' <span class="muted">· ' . h($mems[(int)$g['member_id']]) . '</span>' : '' ?></span>
          <span><?= goalPill($S['status']) ?><?= $S['stale'] ? ' <span class="tag pill-stale">Snapshot stale</span>' : '' ?></span>
        </div>
        <div class="goal-big"><?= h(goalFmtCompact($target)) ?> <span class="muted">target</span></div>
        <div class="goal-line"><b><?= $S['current'] !== null ? h(goalFmtCompact($S['current'])) : '—' ?></b> now<?= $S['last'] ? ' <span class="muted">(snapshot ' . h(goalDateLabel($S['last']['as_of'])) . ')</span>' : ' <span class="muted">(no snapshot yet)</span>' ?></div>
        <div class="goal-line"><b><?= h(goalFmtCompact($S['planned_now'])) ?></b> planned today</div>
        <div class="bar sage"><i style="width:<?= number_format($pct, 1) ?>%"></i></div>
        <div class="muted"><?= number_format($pct, 1) ?>% of target</div>
        <?php if ($S['next']): ?>
          <div class="goal-line">Next: <b><?= h(goalFmtCompact($S['next']['amount'])) ?></b> · <?= $S['next']['proj_base'] ? 'projected ' . h(goalMonthLabel($S['next']['proj_base']['ym'])) : 'after the horizon' ?></div>
        <?php endif; ?>
        <div class="goal-line muted">Invested this <?= h($S['fy']) ?>: <?= h(goalFmtCompact($S['fy_actual'])) ?> of <?= h(goalFmtCompact($S['fy_plan'])) ?> planned</div>
      </a>
    <?php endforeach; ?>
    </div>
    <p class="muted">"Invested" is read from the Invest tab — every entry since the goal's tracking start, filtered by the goal's types and person. "Now" is the value you last typed in as a snapshot.</p>
    <?= goalFormDialog($db, $user, null, $back) ?>
    <?php
    layout($db, $user, '', ob_get_clean(), $back);
}

function renderGoalDashboard(PDO $db, array $user, int $id, bool $print): void {
    $hid = (int)$user['household_id'];
    $uid = (int)$user['id'];
    try { $g = goalLoad($db, $hid, $id); } catch (UserErr $e) { flash('error', $e->getMessage()); redirect('/goals'); }
    $scale  = ($_GET['scale'] ?? 'lin') === 'log' ? 'log' : 'lin';
    $mode   = ($_GET['mode'] ?? 'cy') === 'fy' ? 'fy' : 'cy';
    $range  = ($_GET['range'] ?? '24') === 'all' ? 'all' : '24';
    $months = isset($_GET['months']);
    $qs = function (array $o = []) use ($id, $scale, $mode, $range, $months): string {
        $q = ['scale' => $scale, 'mode' => $mode, 'range' => $range] + ($months ? ['months' => 1] : []);
        $q = array_filter(array_replace($q, $o), fn($v) => $v !== null);
        return '/goals/' . $id . ($q ? '?' . http_build_query($q) : '');
    };
    $back  = $qs();
    $today = today();
    $S     = goalSummary($db, $g, $today);
    $mems  = array_column(membersFor($db, $hid, $uid), 'label', 'id');
    $years = goalYearRows($g, $S['proj'], $S['actuals'], $S['snaps'], $mode);
    $canEdit = mayEdit($g, $user);
    $target  = (float)$g['target_amount'];
    $delta   = $S['actual_cum'] - ($S['planned_cum'] - (float)$g['starting_corpus']);
    $whoLbl  = $g['member_id'] !== null && isset($mems[(int)$g['member_id']]) ? $mems[(int)$g['member_id']] : 'Household';
    $snapCount = count($S['snaps']);
    $msCount   = count(array_filter($S['ms'], fn($m) => $m['id'] > 0));
    ob_start();
    echo goalWideStyle($print);
    ?>
    <div class="goal-head">
      <div style="display:flex; align-items:center; gap:10px; min-width:0;">
        <a class="btn btn-icon btn-back goal-actions" href="/goals" aria-label="All goals"><?= icon('chevron-left', 20) ?></a>
        <div style="min-width:0;">
          <h3 style="margin:0;"><?= h($g['name']) ?></h3>
          <div class="muted"><?= h($whoLbl) ?> · <?= h(goalFmtCompact($target)) ?> by <?= h(goalMonthLabel(end($S['proj'])['ym'])) ?> horizon<?= (int)$g['archived'] ? ' · archived' : '' ?></div>
        </div>
      </div>
      <div class="pill-row goal-actions">
        <button class="pill-btn act" type="button" onclick="document.getElementById('snap-dlg').showModal()"><?= icon('plus', 14) ?> Snapshot</button>
        <?php if ($canEdit): ?>
          <button class="pill-btn" type="button" onclick="document.getElementById('goal-dlg').showModal()"><?= icon('edit', 14) ?> Edit</button>
          <button class="pill-btn" type="button" onclick='askConfirm(<?= h(json_encode([
              'action' => '/goals/archive', 'id' => $id, 'back' => $back, 'danger' => false,
              'title'  => (int)$g['archived'] ? 'Restore goal?' : 'Archive goal?',
              'body'   => (int)$g['archived'] ? 'It returns to the active list.' : 'It moves to the archived list. Nothing is deleted.',
              'ok'     => (int)$g['archived'] ? 'Restore' : 'Archive'])) ?>)'><?= icon((int)$g['archived'] ? 'archive-restore' : 'archive', 14) ?> <?= (int)$g['archived'] ? 'Restore' : 'Archive' ?></button>
          <button class="pill-btn" type="button" onclick='askConfirm(<?= h(json_encode([
              'action' => '/goals/delete', 'id' => $id, 'back' => '/goals',
              'title'  => 'Delete goal?',
              'body'   => "Removes \"{$g['name']}\" with its $snapCount snapshot" . ($snapCount === 1 ? '' : 's') . " and $msCount milestone override" . ($msCount === 1 ? '' : 's') . '. Investments stay in the ledger.',
              'ok'     => 'Delete'])) ?>)'><?= icon('trash-2', 14) ?> Delete</button>
        <?php endif; ?>
        <a class="pill-btn" href="<?= h('/goals/' . $id . '/print?' . http_build_query(['scale' => $scale, 'mode' => $mode, 'range' => $range])) ?>" target="_blank">Print</a>
      </div>
    </div>

    <div class="goal-kpis">
      <div class="card elev-sm goal-kpi"><div class="k">Target</div><div class="v"><?= h(goalFmtCompact($target)) ?></div><div class="s"><?= h(fmtShort($target)) ?></div></div>
      <div class="card elev-sm goal-kpi"><div class="k">Current</div><div class="v"><?= $S['current'] !== null ? h(goalFmtCompact($S['current'])) : '—' ?></div>
        <div class="s"><?= $S['last'] ? h(goalDateLabel($S['last']['as_of'])) . ' ' . goalPill($S['status']) : 'no snapshot yet' ?><?= $S['stale'] ? ' <span class="tag pill-stale">stale</span>' : '' ?></div></div>
      <div class="card elev-sm goal-kpi"><div class="k">Planned today</div><div class="v"><?= h(goalFmtCompact($S['planned_now'])) ?></div><div class="s">base path, <?= h(goalMonthLabel($S['today_ym'])) ?></div></div>
      <div class="card elev-sm goal-kpi"><div class="k">Invested</div><div class="v"><?= h(goalFmtCompact($S['actual_cum'])) ?></div>
        <div class="s">plan <?= h(goalFmtCompact($S['planned_cum'] - (float)$g['starting_corpus'])) ?> · <?= $delta >= 0 ? '+' : '−' ?><?= h(goalFmtCompact(abs($delta))) ?></div></div>
      <div class="card elev-sm goal-kpi"><div class="k">Next milestone</div><div class="v"><?= $S['next'] ? h(goalFmtCompact($S['next']['amount'])) : 'Done' ?></div>
        <div class="s"><?= $S['next'] ? ($S['next']['proj_base'] ? h(goalMonthLabel($S['next']['proj_base']['ym'])) . ' at base' : 'after the horizon') : 'every rung crossed' ?></div></div>
    </div>

    <div class="card elev-sm">
      <div class="goal-card-top">
        <span class="card-title">Projection</span>
        <div class="seg goal-toggles">
          <a class="seg-opt<?= $scale === 'lin' ? ' on' : '' ?>" href="<?= h($qs(['scale' => 'lin'])) ?>">Linear</a>
          <a class="seg-opt<?= $scale === 'log' ? ' on' : '' ?>" href="<?= h($qs(['scale' => 'log'])) ?>">Log</a>
        </div>
      </div>
      <div class="ylegend goal-legend"><span><i class="sw" style="background:var(--color-accent);opacity:.2"></i>Low–high fan</span><span><i class="sw" style="background:var(--color-accent)"></i>Base</span><span><i class="sw" style="background:var(--color-neutral-700)"></i>Planned invested</span><span><i class="sw" style="background:var(--color-accent-2)"></i>Actually invested</span><span><i class="sw" style="border-radius:50%;background:var(--color-accent-700)"></i>Snapshots</span></div>
      <?= goalSvgProjection($g, $S, $scale) ?>
    </div>

    <div class="card elev-sm">
      <div class="goal-card-top">
        <span class="card-title">Invested: planned vs actual, by month</span>
        <div class="seg goal-toggles">
          <a class="seg-opt<?= $range === '24' ? ' on' : '' ?>" href="<?= h($qs(['range' => '24'])) ?>">24 months</a>
          <a class="seg-opt<?= $range === 'all' ? ' on' : '' ?>" href="<?= h($qs(['range' => 'all'])) ?>">All</a>
        </div>
      </div>
      <div class="ylegend goal-legend"><span><i class="sw" style="border:1.5px solid var(--color-neutral-700);background:none"></i>Planned SIP</span><span><i class="sw" style="background:var(--color-accent-2)"></i>Logged in the ledger</span></div>
      <?= goalSvgInvested($S, $range) ?>
    </div>

    <div class="goal-grid-2">
      <div class="card elev-sm">
        <div class="goal-card-top">
          <span class="card-title">Milestones</span>
          <?php if ($canEdit && !$print): ?><button class="pill-btn act goal-actions" type="button" onclick="document.getElementById('ms-dlg').showModal()"><?= icon('plus', 14) ?> Custom</button><?php endif; ?>
        </div>
        <div class="goal-table-wrap"><table class="table goal-table">
          <thead><tr><th>Amount</th><th>Low</th><th>Base</th><th>High</th><th>FY (base)</th><th>Achieved</th><th>Δ</th><?php if ($canEdit && !$print): ?><th></th><?php endif; ?></tr></thead>
          <tbody>
          <?php foreach ($S['ms'] as $m): $d = $m['delta_months']; ?>
            <tr>
              <td><b><?= h(goalFmtCompact($m['amount'])) ?></b><?= $m['is_custom'] ? ' <span class="tag-archived">custom</span>' : '' ?></td>
              <td><?= $m['proj_low']  ? h(goalMonthLabel($m['proj_low']['ym']))  : '<span class="muted">after horizon</span>' ?></td>
              <td><?= $m['proj_base'] ? h(goalMonthLabel($m['proj_base']['ym'])) : '<span class="muted">after horizon</span>' ?></td>
              <td><?= $m['proj_high'] ? h(goalMonthLabel($m['proj_high']['ym'])) : '<span class="muted">after horizon</span>' ?></td>
              <td><?= $m['proj_base'] ? h($m['proj_base']['fy']) : '—' ?></td>
              <td><?= $m['achieved_on'] ? h(goalDateLabel($m['achieved_on'])) . ($m['override'] ? ' <span class="muted" title="Set by hand">✎</span>' : '') : '—' ?></td>
              <td><?= $d === null ? '—' : '<span class="' . ($d >= 0 ? 'goal-pos' : 'goal-neg') . '">' . ($d >= 0 ? '+' : '') . $d . ' mo</span>' ?></td>
              <?php if ($canEdit && !$print): ?>
              <td class="goal-actions" style="white-space:nowrap;">
                <button class="icon-btn" type="button" title="<?= $m['override'] ? 'Change achieved date' : 'Mark achieved' ?>" onclick='openMarkAchieved(<?= h(json_encode(['amount' => $m['amount'], 'on' => $m['achieved_on'] ?? today(), 'label' => goalFmtCompact($m['amount'])])) ?>)'><?= icon('check', 15) ?></button>
                <?php if ($m['is_custom']): ?>
                  <button class="icon-btn" type="button" aria-label="Remove" onclick='askConfirm(<?= h(json_encode(['action' => '/goals/milestone/delete', 'id' => $m['id'], 'back' => $back, 'title' => 'Remove milestone?', 'body' => goalFmtCompact($m['amount']) . ' — a custom rung on this ladder.', 'ok' => 'Remove'])) ?>)'><?= icon('trash-2', 15) ?></button>
                <?php endif; ?>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <div class="muted">Achieved = the first snapshot at or above the amount, unless set by hand. Δ counts months ahead (+) or behind (−) the base projection.</div>
      </div>

      <div class="card elev-sm">
        <div class="goal-card-top">
          <span class="card-title">Year by year</span>
          <div class="seg goal-toggles">
            <a class="seg-opt<?= $mode === 'cy' ? ' on' : '' ?>" href="<?= h($qs(['mode' => 'cy'])) ?>">Calendar</a>
            <a class="seg-opt<?= $mode === 'fy' ? ' on' : '' ?>" href="<?= h($qs(['mode' => 'fy'])) ?>">Financial</a>
          </div>
        </div>
        <div class="goal-table-wrap"><table class="table goal-table">
          <thead><tr><th>Year</th><th>As at</th><th>SIP/mo</th><th>Planned in</th><th>Actual in</th><th>Low</th><th>Base</th><th>High</th><th>Behind below</th><th>Snapshot</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($years as $y): $sn = $y['snapshot']; ?>
            <tr<?= $y['ym'] === substr(addMonths($S['today_ym'] . '-01', 0), 0, 7) ? ' class="goal-now"' : '' ?>>
              <td><b><?= h($y['label']) ?></b></td>
              <td><?= h(goalMonthLabel($y['ym'])) ?></td>
              <td><?= h(fmtShort($y['sip'])) ?></td>
              <td><?= h(goalFmtCompact($y['planned_cum'])) ?></td>
              <td><?= $y['actual_cum'] !== null && $y['ym'] <= $S['today_ym'] ? h(goalFmtCompact($y['actual_cum'])) : '—' ?></td>
              <td><?= h(goalFmtCompact($y['low'])) ?></td>
              <td><b><?= h(goalFmtCompact($y['base'])) ?></b></td>
              <td><?= h(goalFmtCompact($y['high'])) ?></td>
              <td class="muted"><?= h(goalFmtCompact($y['band_value'])) ?></td>
              <td><?= $sn ? h(goalFmtCompact((float)$sn['current_value'])) . ($sn['approx'] ? ' <span class="muted" title="Nearest snapshot, ' . h(goalDateLabel($sn['as_of'])) . '">~</span>' : '') : '—' ?></td>
              <td><?= goalPill($y['status'], (bool)($sn['approx'] ?? false)) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <div class="muted">"Planned in" includes the starting corpus of <?= h(goalFmtCompact((float)$g['starting_corpus'])) ?>; "Actual in" counts only ledger entries since <?= h(goalMonthLabel(substr($g['tracking_start'], 0, 7))) ?>.</div>
      </div>
    </div>

    <div class="goal-grid-2">
      <div class="card elev-sm">
        <div class="goal-card-top"><span class="card-title">Snapshots</span><span class="muted"><?= $snapCount ?> of <?= GOAL_SNAPS_MAX ?></span></div>
        <?php if (!$S['snaps']): ?>
          <div class="muted">None yet. Open your broker app, read the current value, and log it here — that is what every status on this page is judged against.</div>
        <?php else: ?>
          <div class="stack">
          <?php foreach (array_reverse($S['snaps']) as $sn): $row = $S['by'][substr($sn['as_of'], 0, 7)] ?? null; $st = goalStatus($row, (float)$sn['current_value'], (float)$g['band_low']); ?>
            <div class="card elev-sm row">
              <div class="row-icon sage"><?= icon('calendar', 16) ?></div>
              <div class="row-main">
                <div class="title"><?= h(fmt((float)$sn['current_value'])) ?> <?= goalPill($st) ?></div>
                <div class="sub"><?= h(goalDateLabel($sn['as_of'])) ?><?= $row ? ' · planned ' . h(goalFmtCompact($row['base'])) : '' ?><?= $sn['note'] !== '' ? ' · ' . h($sn['note']) : '' ?></div>
              </div>
              <?php if (mayEdit($sn, $user) && !$print): ?>
                <button class="icon-btn goal-actions" type="button" aria-label="Edit" onclick='openSnapshot(<?= h(json_encode(['id' => (int)$sn['id'], 'as_of' => $sn['as_of'], 'value' => (string)$sn['current_value'], 'note' => $sn['note']])) ?>)'><?= icon('edit', 15) ?></button>
                <button class="icon-btn goal-actions" type="button" aria-label="Delete" onclick='askConfirm(<?= h(json_encode(['action' => '/goals/snapshot/delete', 'id' => (int)$sn['id'], 'back' => $back, 'title' => 'Delete snapshot?', 'body' => fmt((float)$sn['current_value']) . ' on ' . goalDateLabel($sn['as_of']), 'ok' => 'Delete'])) ?>)'><?= icon('trash-2', 15) ?></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card elev-sm">
        <div class="goal-card-top"><span class="card-title">Assumptions</span>
          <?php if ($canEdit && !$print): ?><button class="pill-btn goal-actions" type="button" onclick="document.getElementById('goal-dlg').showModal()"><?= icon('edit', 14) ?> Edit</button><?php endif; ?></div>
        <dl class="goal-dl">
          <dt>Starting corpus</dt><dd><?= h(fmt((float)$g['starting_corpus'])) ?> before <?= h(goalMonthLabel(substr($g['tracking_start'], 0, 7))) ?></dd>
          <dt>Monthly SIP</dt><dd><?= h(fmt((float)$g['monthly_sip'])) ?> from <?= h(goalMonthLabel(substr($g['plan_start'], 0, 7))) ?></dd>
          <dt>Step-up</dt><dd><?= h((string)(float)$g['stepup_pct']) ?>% every <?= (int)$g['stepup_month'] === 4 ? 'April' : 'January' ?></dd>
          <dt>Returns</dt><dd><?= h((string)(float)$g['return_low']) ?>% low · <?= h((string)(float)$g['return_base']) ?>% base · <?= h((string)(float)$g['return_high']) ?>% high, a year</dd>
          <dt>Behind when</dt><dd>below <?= h((string)round((float)$g['band_low'] * 100)) ?>% of the low path</dd>
          <dt>Horizon</dt><dd><?= (int)$g['horizon_years'] ?> years</dd>
          <dt>Counts</dt><dd><?= $g['type_filter'] !== '' ? h(str_replace(',', ', ', $g['type_filter'])) : 'every investment type' ?><?= $g['filter_member_id'] !== null && isset($mems[(int)$g['filter_member_id']]) ? ', by ' . h($mems[(int)$g['filter_member_id']]) : '' ?></dd>
        </dl>
      </div>
    </div>

    <?php if ($months): ?>
      <div class="card elev-sm">
        <div class="goal-card-top"><span class="card-title">Month by month</span><a class="pill-btn goal-toggles" href="<?= h($qs(['months' => null])) ?>">Hide</a></div>
        <div class="goal-table-wrap" style="max-height:60vh;"><table class="table goal-table">
          <thead><tr><th>Month</th><th>FY</th><th>SIP</th><th>Planned in</th><th>Actual, month</th><th>Actual in</th><th>Low</th><th>Base</th><th>High</th><th>Snapshot</th></tr></thead>
          <tbody>
          <?php foreach ($S['proj'] as $r): $a = $S['actuals'][$r['ym']] ?? null; $sn = goalSnapshotForMonth($S['snaps'], $r['ym']); ?>
            <tr<?= $r['ym'] === $S['today_ym'] ? ' class="goal-now"' : '' ?>>
              <td><?= h(goalMonthLabel($r['ym'])) ?></td><td class="muted"><?= h($r['fy']) ?></td>
              <td><?= h(fmtShort($r['sip'])) ?></td><td><?= h(goalFmtCompact($r['planned_cum'])) ?></td>
              <td><?= $a ? h(fmtShort($a['month'])) : '—' ?></td><td><?= $r['ym'] <= $S['today_ym'] && ($ac = goalActualCumAt($S['actuals'], $r['ym'])) !== null ? h(goalFmtCompact($ac)) : '—' ?></td>
              <td><?= h(goalFmtCompact($r['low'])) ?></td><td><b><?= h(goalFmtCompact($r['base'])) ?></b></td><td><?= h(goalFmtCompact($r['high'])) ?></td>
              <td><?= $sn && !$sn['approx'] ? h(goalFmtCompact((float)$sn['current_value'])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
    <?php elseif (!$print): ?>
      <a class="pill-btn goal-toggles" style="align-self:flex-start;" href="<?= h($qs(['months' => 1])) ?>">Show month-by-month detail</a>
    <?php endif; ?>

    <?php if (!$print): ?>
      <?= goalFormDialog($db, $user, $g, $back) ?>
      <dialog id="snap-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/goals/snapshot">
          <?= csrfInput() ?>
          <input type="hidden" name="goal_id" value="<?= $id ?>">
          <input type="hidden" name="id" id="sn-id" value="">
          <input type="hidden" name="back" value="<?= h($back) ?>">
          <div class="dlg-title" id="sn-title">Log a snapshot</div>
          <div class="dlg-body">The portfolio's current value, as your broker shows it.</div>
          <div class="field-row">
            <input class="input" name="as_of" id="sn-date" type="date" value="<?= h($today) ?>" max="<?= h($today) ?>" required>
            <input class="input" name="current_value" id="sn-value" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="15" placeholder="Current value" required
                   oninput="document.getElementById('sn-save').disabled = !(parseFloat(this.value) >= 0)">
          </div>
          <input class="input" name="note" id="sn-note" maxlength="200" placeholder="Note (optional)">
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('snap-dlg').close()">Cancel</button>
            <button class="btn btn-primary" type="submit" id="sn-save" disabled>Save</button>
          </div>
        </form>
      </dialog>
      <?php if ($canEdit): ?>
      <dialog id="ms-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/goals/milestone">
          <?= csrfInput() ?>
          <input type="hidden" name="goal_id" value="<?= $id ?>">
          <input type="hidden" name="back" value="<?= h($back) ?>">
          <div class="dlg-title">Add a custom milestone</div>
          <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="15" placeholder="Amount, e.g. 1500000" required
                 oninput="document.getElementById('ms-save').disabled = !(parseFloat(this.value) > 0)">
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('ms-dlg').close()">Cancel</button>
            <button class="btn btn-primary" type="submit" id="ms-save" disabled>Add</button>
          </div>
        </form>
      </dialog>
      <dialog id="mark-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/goals/milestone">
          <?= csrfInput() ?>
          <input type="hidden" name="goal_id" value="<?= $id ?>">
          <input type="hidden" name="mode" value="achieved">
          <input type="hidden" name="amount" id="mk-amount">
          <input type="hidden" name="back" value="<?= h($back) ?>">
          <div class="dlg-title" id="mk-title">Mark achieved</div>
          <div class="dlg-body">Overrides the date derived from snapshots. Leave empty to go back to deriving it.</div>
          <input class="input" name="achieved_on" id="mk-date" type="date" max="<?= h($today) ?>">
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('mark-dlg').close()">Cancel</button>
            <button class="btn btn-primary" type="submit">Save</button>
          </div>
        </form>
      </dialog>
      <?php endif; ?>
      <script>
      function openSnapshot(d) {
        document.getElementById('sn-id').value = d.id; document.getElementById('sn-date').value = d.as_of;
        document.getElementById('sn-value').value = d.value; document.getElementById('sn-note').value = d.note;
        document.getElementById('sn-title').textContent = 'Edit snapshot'; document.getElementById('sn-save').disabled = false;
        document.getElementById('snap-dlg').showModal();
      }
      function openMarkAchieved(d) {
        document.getElementById('mk-amount').value = d.amount; document.getElementById('mk-date').value = d.on;
        document.getElementById('mk-title').textContent = 'Achieved ' + d.label + ' on…';
        document.getElementById('mark-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    layout($db, $user, '', ob_get_clean(), $back);
}

// Add and edit share one form. On the dashboard it is pre-filled with the goal; on the list it
// opens with the defaults from the reference plan.
function goalFormDialog(PDO $db, array $user, ?array $g, string $back): string {
    $hid  = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = membersFor($db, $hid, $uid);
    $types = $db->prepare("SELECT name, archived FROM investment_types WHERE household_id = ? ORDER BY archived, name");
    $types->execute([$hid]);
    $types = $types->fetchAll();
    $sel = array_flip(goalTypeList((string)($g['type_filter'] ?? '')));
    $v = fn(string $k, string $d = '') => h((string)($g[$k] ?? $d));
    $num = fn(string $k, string $d) => h(isset($g[$k]) ? rtrim(rtrim(number_format((float)$g[$k], 2, '.', ''), '0'), '.') : $d);
    $firstOfMonth = substr(today(), 0, 7) . '-01';
    $role = (string)($user['role'] ?? ROLE_MEMBER);
    ob_start();
    ?>
    <dialog id="goal-dlg" class="confirm goal-dialog" style="max-width:560px;">
      <form method="post" action="/goals/save">
        <?= csrfInput() ?>
        <input type="hidden" name="id" value="<?= (int)($g['id'] ?? 0) ?>">
        <input type="hidden" name="back" value="<?= h($back) ?>">
        <div class="dlg-title"><?= $g ? 'Edit goal' : 'New goal' ?></div>
        <div class="field-row">
          <input class="input" name="name" placeholder="Goal name, e.g. Retirement" required maxlength="80" value="<?= $v('name') ?>">
          <?php /* Whose goal. The shared picker: owners choose anyone, members are themselves. */ ?>
          <?= memberSelect($mems, $uid, $role, '', isset($g['member_id']) ? (int)$g['member_id'] : null) ?>
        </div>
        <div class="field-row">
          <label class="field"><span>Target</span><input class="input" name="target_amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" required value="<?= $num('target_amount', '100000000') ?>"></label>
          <label class="field"><span>Starting corpus</span><input class="input" name="starting_corpus" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" value="<?= $num('starting_corpus', '0') ?>"></label>
          <label class="field"><span>Monthly SIP</span><input class="input" name="monthly_sip" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" required value="<?= $num('monthly_sip', '60000') ?>"></label>
        </div>
        <div class="field-row">
          <label class="field"><span>Tracking from</span><input class="input" name="tracking_start" type="date" required value="<?= $v('tracking_start', $firstOfMonth) ?>"></label>
          <label class="field"><span>SIP starts</span><input class="input" name="plan_start" type="date" required value="<?= $v('plan_start', $firstOfMonth) ?>"></label>
          <label class="field"><span>Horizon, years</span><input class="input" name="horizon_years" type="number" min="5" max="40" required value="<?= $v('horizon_years', '20') ?>"></label>
        </div>
        <div class="field-row">
          <label class="field"><span>Step-up %</span><input class="input" name="stepup_pct" type="number" step="0.01" min="0" max="50" value="<?= $num('stepup_pct', '10') ?>"></label>
          <label class="field"><span>Step-up every</span>
            <select class="select" name="stepup_month"><option value="1"<?= (int)($g['stepup_month'] ?? 1) === 1 ? ' selected' : '' ?>>January</option><option value="4"<?= (int)($g['stepup_month'] ?? 1) === 4 ? ' selected' : '' ?>>April</option></select></label>
          <label class="field"><span>Behind below ×low</span><input class="input" name="band_low" type="number" step="0.01" min="0.5" max="1" value="<?= $num('band_low', '0.9') ?>"></label>
        </div>
        <div class="field-row">
          <label class="field"><span>Low return %</span><input class="input" name="return_low" type="number" step="0.01" min="0" max="30" value="<?= $num('return_low', '10') ?>"></label>
          <label class="field"><span>Base return %</span><input class="input" name="return_base" type="number" step="0.01" min="0" max="30" value="<?= $num('return_base', '12') ?>"></label>
          <label class="field"><span>High return %</span><input class="input" name="return_high" type="number" step="0.01" min="0" max="30" value="<?= $num('return_high', '14') ?>"></label>
        </div>
        <div class="field"><span>Count these investment types <span class="muted">(none ticked = all)</span></span>
          <div class="pill-row" style="margin-top:6px;">
            <?php foreach ($types as $t): ?>
              <label class="pill-btn goal-check"><input type="checkbox" name="types[]" value="<?= h($t['name']) ?>"<?= isset($sel[$t['name']]) ? ' checked' : '' ?>> <?= h($t['name']) ?><?= (int)$t['archived'] ? ' (archived)' : '' ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if (count($mems) > 1 && $role === ROLE_OWNER): ?>
          <label class="field"><span>Count only entries by</span>
            <select class="select" name="filter_member_id">
              <option value="0">Anyone</option>
              <?php foreach ($mems as $m): ?>
                <option value="<?= (int)$m['id'] ?>"<?= (int)($g['filter_member_id'] ?? 0) === (int)$m['id'] ? ' selected' : '' ?>><?= h($m['label']) ?></option>
              <?php endforeach; ?>
            </select></label>
        <?php endif; ?>
        <div class="dlg-actions">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('goal-dlg').close()">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </dialog>
    <?php
    return (string)ob_get_clean();
}

// ─── SVG ────────────────────────────────────────────────────────────

// Round a number for an SVG attribute: one decimal is plenty at 1000 units wide.
function goalPx(float $n): string { return number_format($n, 1, '.', ''); }

// "Nice" tick step for a linear axis with about $n divisions.
function goalNiceStep(float $range, int $n): float {
    $raw = $range / max(1, $n);
    $mag = 10 ** floor(log10(max($raw, 1e-9)));
    foreach ([1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10] as $m) if ($m * $mag >= $raw) return $m * $mag;
    return 10 * $mag;
}

// The fan chart: low–high band, base line, planned and actual invested, milestone rules,
// snapshot dots coloured by status, today's rule. Hover is a small inline script reading the
// rows out of a JSON block; without it the <title> on each dot still answers.
function goalSvgProjection(array $g, array $S, string $scale): string {
    $proj = $S['proj']; $n = count($proj);
    [$W, $H, $pl, $pr, $pt, $pb] = [1000, 420, 66, 18, 14, 30];
    $pw = $W - $pl - $pr; $ph = $H - $pt - $pb;
    $corpus = (float)$g['starting_corpus'];
    $target = (float)$g['target_amount'];
    $ymax = max(end($proj)['high'], $target, ...array_map(fn($s) => (float)$s['current_value'], $S['snaps'] ?: [['current_value' => 0]])) * 1.04;
    $ymin = $scale === 'log' ? max($corpus, 10000.0) : 0.0;
    if ($scale === 'log' && $ymin >= $ymax) $ymin = $ymax / 100;
    $X = fn(float $i): float => $pl + $i / $n * $pw;
    $Y = function (float $v) use ($scale, $ymin, $ymax, $pt, $ph): float {
        $f = $scale === 'log'
            ? (log10(max($v, $ymin)) - log10($ymin)) / (log10($ymax) - log10($ymin))
            : $v / $ymax;
        return $pt + $ph - max(0.0, min(1.0, $f)) * $ph;
    };
    $pt0 = fn(string $k) => goalPx($X(0)) . ',' . goalPx($Y($corpus));
    $line = function (string $k) use ($proj, $X, $Y, $pt0): string {
        $pts = [$pt0($k)];
        foreach ($proj as $r) $pts[] = goalPx($X($r['i'])) . ',' . goalPx($Y($r[$k]));
        return implode(' ', $pts);
    };
    $fan = $line('high') . ' ' . implode(' ', array_reverse(explode(' ', $line('low'))));

    $out = '<div class="goal-chart-wrap"><svg class="goal-chart" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="Projected value over time">';
    // Y ticks + grid.
    $ticks = [];
    if ($scale === 'log') {
        for ($e = floor(log10($ymin)); $e <= ceil(log10($ymax)); $e++) foreach ([1, 2, 5] as $m) { $t = $m * 10 ** $e; if ($t >= $ymin && $t <= $ymax) $ticks[] = $t; }
        if (count($ticks) > 8) $ticks = array_values(array_filter($ticks, fn($t) => in_array((int)round($t / 10 ** floor(log10($t))), [1, 5], true)));
        if (count($ticks) > 8) $ticks = array_values(array_filter($ticks, fn($t) => (int)round($t / 10 ** floor(log10($t))) === 1));
    } else {
        $step = goalNiceStep($ymax, 5);
        for ($t = 0; $t <= $ymax; $t += $step) $ticks[] = $t;
    }
    foreach ($ticks as $t) {
        $y = goalPx($Y((float)$t));
        $out .= '<line class="grid" x1="' . $pl . '" x2="' . ($W - $pr) . '" y1="' . $y . '" y2="' . $y . '"/>';
        $out .= '<text class="lbl" x="' . ($pl - 6) . '" y="' . $y . '" dy="3.5" text-anchor="end">' . h(goalFmtCompact((float)$t)) . '</text>';
    }
    // X ticks: every December (every other one past 15 years).
    $every = $n > 180 ? 2 : 1; $k = 0;
    foreach ($proj as $r) {
        if (substr($r['ym'], 5, 2) !== '12') continue;
        if ($k++ % $every) continue;
        $x = goalPx($X($r['i']));
        $out .= '<line class="grid" x1="' . $x . '" x2="' . $x . '" y1="' . $pt . '" y2="' . ($pt + $ph) . '"/>';
        $out .= '<text class="lbl" x="' . $x . '" y="' . ($H - 10) . '" text-anchor="middle">' . $r['cy'] . '</text>';
    }
    // Milestone rules. Labels crowd at the bottom of a linear axis, so one is dropped when it
    // would sit on top of the last; the rule itself is still drawn.
    $lastLabelY = INF;
    foreach (array_reverse($S['ms']) as $m) {
        if ($m['amount'] > $ymax || ($scale === 'log' && $m['amount'] < $ymin)) continue;
        $yv = $Y($m['amount']); $y = goalPx($yv);
        $out .= '<line class="ms" x1="' . $pl . '" x2="' . ($W - $pr) . '" y1="' . $y . '" y2="' . $y . '"/>';
        if ($yv - $lastLabelY < 14 && $lastLabelY !== INF) continue;
        $lastLabelY = $yv;
        $out .= '<text class="lbl ms-lbl" x="' . ($W - $pr - 2) . '" y="' . $y . '" dy="-3" text-anchor="end">' . h(goalFmtCompact($m['amount'])) . '</text>';
    }
    $out .= '<polygon class="fan" points="' . $fan . '"/>';
    $out .= '<polyline class="fan-low" points="' . $line('low') . '"/><polyline class="fan-high" points="' . $line('high') . '"/>';
    $out .= '<polyline class="planned" points="' . $line('planned_cum') . '"/>';
    $out .= '<polyline class="base" points="' . $line('base') . '"/>';
    // Actual cumulative invested, only as far as there is data.
    $start = $proj[0]['ym']; $apts = [];
    foreach ($S['actuals'] as $m => $a) {
        $i = goalMonthsBetween($start, $m) + 1;
        if ($i >= 1 && $i <= $n) $apts[] = goalPx($X($i)) . ',' . goalPx($Y($a['cum']));
    }
    if ($apts) $out .= '<polyline class="actual" points="' . goalPx($X(0)) . ',' . goalPx($Y(0)) . ' ' . implode(' ', $apts) . '"/>';
    // Today.
    $ti = goalMonthsBetween($start, $S['today_ym']) + 1;
    if ($ti >= 0 && $ti <= $n) {
        $x = goalPx($X($ti));
        $out .= '<line class="today" x1="' . $x . '" x2="' . $x . '" y1="' . $pt . '" y2="' . ($pt + $ph) . '"/>';
        $out .= '<text class="lbl" x="' . $x . '" y="' . ($pt + 10) . '" dx="4">today</text>';
    }
    // Snapshots.
    $band = (float)$g['band_low'];
    foreach ($S['snaps'] as $s) {
        $ym = substr($s['as_of'], 0, 7);
        $i  = goalMonthsBetween($start, $ym) + 1 + ((int)substr($s['as_of'], 8, 2) - 15) / 30;
        if ($i < 0 || $i > $n) continue;
        $v  = (float)$s['current_value'];
        $st = goalStatus($S['by'][$ym] ?? null, $v, $band);
        $out .= '<circle class="snap ' . $st . '" cx="' . goalPx($X($i)) . '" cy="' . goalPx($Y($v)) . '" r="5"><title>'
              . h(goalDateLabel($s['as_of']) . ' · ' . fmt($v) . ' · ' . ['ahead' => 'Ahead', 'ontrack' => 'On track', 'behind' => 'Behind', 'none' => ''][$st]) . '</title></circle>';
    }
    $out .= '<line class="cross" id="gc-cross" x1="0" x2="0" y1="' . $pt . '" y2="' . ($pt + $ph) . '" visibility="hidden"/>';
    $out .= '<rect class="hit" id="gc-hit" x="' . $pl . '" y="' . $pt . '" width="' . $pw . '" height="' . $ph . '" fill="transparent"/>';
    $out .= '</svg><div class="goal-tip" id="gc-tip" hidden></div></div>';

    // Hover data: one short row per month, labels pre-formatted so the script stays dumb.
    $rows = [];
    foreach ($proj as $r) {
        $a = $S['actuals'][$r['ym']] ?? null;
        $rows[] = [goalMonthLabel($r['ym']), goalFmtCompact($r['low']), goalFmtCompact($r['base']), goalFmtCompact($r['high']), goalFmtCompact($r['planned_cum']), $a ? goalFmtCompact($a['cum']) : null];
    }
    $json = json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP);
    $out .= <<<JS
<script>
(function () {
  var rows = $json, n = rows.length, pl = $pl, pw = $pw;
  var hit = document.getElementById('gc-hit'), cross = document.getElementById('gc-cross'), tip = document.getElementById('gc-tip');
  if (!hit) return;
  var svg = hit.ownerSVGElement, wrap = svg.parentNode;
  function show(ev) {
    var r = svg.getBoundingClientRect(), sx = 1000 / r.width;
    var x = (ev.clientX - r.left) * sx, i = Math.round((x - pl) / pw * n);
    if (i < 1 || i > n) { hide(); return; }
    var d = rows[i - 1], cx = pl + i / n * pw;
    cross.setAttribute('x1', cx); cross.setAttribute('x2', cx); cross.setAttribute('visibility', 'visible');
    tip.innerHTML = '<b>' + d[0] + '</b><br>Base ' + d[2] + '<br><span class="muted">Low ' + d[1] + ' · High ' + d[3] + '</span><br>Planned in ' + d[4] + (d[5] ? '<br>Actual in ' + d[5] : '');
    tip.hidden = false;
    var left = cx / sx; if (left > r.width - 180) left -= 190; else left += 12;
    tip.style.left = left + 'px'; tip.style.top = ((ev.clientY - r.top) - 10) + 'px';
  }
  function hide() { cross.setAttribute('visibility', 'hidden'); tip.hidden = true; }
  hit.addEventListener('mousemove', show); hit.addEventListener('mouseleave', hide);
  hit.addEventListener('touchstart', function (e) { show(e.touches[0]); }, { passive: true });
  hit.addEventListener('touchmove',  function (e) { show(e.touches[0]); }, { passive: true });
})();
</script>
JS;
    return $out;
}

// Grouped monthly bars: planned SIP (outline) beside what the ledger recorded (filled).
function goalSvgInvested(array $S, string $range): string {
    $months = [];
    foreach ($S['proj'] as $r) { if ($r['ym'] > $S['today_ym']) break; $months[] = $r; }
    if (!$months) return '<div class="muted">Tracking has not started yet.</div>';
    if ($range === '24') $months = array_slice($months, -24);
    $n = count($months);
    [$W, $H, $pl, $pr, $pt, $pb] = [1000, 260, 66, 18, 12, 30];
    $pw = $W - $pl - $pr; $ph = $H - $pt - $pb;
    $max = 1.0;
    foreach ($months as $r) $max = max($max, $r['sip'], $S['actuals'][$r['ym']]['month'] ?? 0.0);
    $max *= 1.1;
    $slot = $pw / $n; $bw = max(2.0, min(18.0, $slot * 0.36));
    $Y = fn(float $v): float => $pt + $ph - $v / $max * $ph;
    $out = '<div class="goal-chart-wrap"><svg class="goal-chart goal-chart-bars" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="Planned against actual investment by month">';
    $step = goalNiceStep($max, 4);
    for ($t = 0; $t <= $max; $t += $step) {
        $y = goalPx($Y((float)$t));
        $out .= '<line class="grid" x1="' . $pl . '" x2="' . ($W - $pr) . '" y1="' . $y . '" y2="' . $y . '"/>';
        $out .= '<text class="lbl" x="' . ($pl - 6) . '" y="' . $y . '" dy="3.5" text-anchor="end">' . h(goalFmtCompact((float)$t)) . '</text>';
    }
    foreach ($months as $k => $r) {
        $x0 = $pl + $k * $slot + $slot / 2;
        $a  = $S['actuals'][$r['ym']]['month'] ?? 0.0;
        $title = '<title>' . h(goalMonthLabel($r['ym']) . ' · planned ' . fmtShort($r['sip']) . ' · logged ' . fmtShort($a)) . '</title>';
        $out .= '<rect class="planned" x="' . goalPx($x0 - $bw - 1) . '" y="' . goalPx($Y($r['sip'])) . '" width="' . goalPx($bw) . '" height="' . goalPx($pt + $ph - $Y($r['sip'])) . '">' . $title . '</rect>';
        $out .= '<rect class="actual" x="' . goalPx($x0 + 1) . '" y="' . goalPx($Y($a)) . '" width="' . goalPx($bw) . '" height="' . goalPx($pt + $ph - $Y($a)) . '">' . $title . '</rect>';
        $mm = substr($r['ym'], 5, 2);
        if ($n <= 24 || $mm === '12' || ($n <= 60 && in_array($mm, ['03', '06', '09'], true))) {
            $lab = $n <= 24 ? (new DateTimeImmutable($r['ym'] . '-01'))->format($mm === '01' || $k === 0 ? 'M \'y' : 'M') : $r['cy'];
            $out .= '<text class="lbl" x="' . goalPx($x0) . '" y="' . ($H - 10) . '" text-anchor="middle">' . h((string)$lab) . '</text>';
        }
    }
    return $out . '</svg></div>';
}
