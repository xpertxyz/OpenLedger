<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require __DIR__ . '/lib.php';

define('CURRENCY',         $config['currency']);
define('GOOGLE_CLIENT_ID', $config['google_client_id']);
// Build-time switches, defined here so views.php can read them the same way it reads
// GOOGLE_CLIENT_ID. Both are on for the web; the Android build sets them off. See ANDROID.md.
define('FEATURE_SHARING',  $config['features']['sharing']);
define('FEATURE_SIGNIN',   $config['features']['google_signin']);
// Only the Android build sets this: the panel it renders drives a native Google Drive client
// through a WebView bridge that does not exist on the web.
define('FEATURE_BACKUP',   $config['features']['backup']);

// ────────────────────────────────────────────────────────────────────
// CLI modes: --selfcheck (smoke tests), --cron (daily maintenance)
// ────────────────────────────────────────────────────────────────────
if (PHP_SAPI === 'cli') {
    $mode = $argv[1] ?? '';
    if ($mode === '--selfcheck') {
        assert(advanceDate('2026-01-15', 'monthly')   === '2026-02-15');
        assert(advanceDate('2026-01-15', 'quarterly') === '2026-04-15');
        assert(advanceDate('2026-01-15', 'yearly')    === '2027-01-15');
        // Month math clamps instead of overflowing: plain PHP "+1 month" from Jan 31 lands on
        // Mar 3, so a monthly item would never post in February and would drift after that.
        assert(advanceDate('2026-01-31', 'monthly')   === '2026-02-28');
        assert(advanceDate('2028-01-31', 'monthly')   === '2028-02-29');   // leap February
        assert(advanceDate('2026-03-31', 'monthly')   === '2026-04-30');
        assert(advanceDate('2026-11-30', 'quarterly') === '2027-02-28');
        assert(addMonths('2026-01-01', 11) === '2026-12-01');
        assert(addMonths('2026-08-07', 0)  === '2026-08-07');
        // Split bills: n equal shares, the last one n-1 months after the payment date.
        assert(splitPlan(24000.0, 12, '2026-01-01') === [2000.0, '2026-12-01']);
        assert(splitPlan(1200.0,  24, '2026-06-15') === [50.0,   '2028-05-15']);
        try { splitPlan(100.0, 1, '2026-01-01');   assert(false, 'a 1-month split should throw'); } catch (UserErr) {}
        try { splitPlan(100.0, 999, '2026-01-01'); assert(false, 'a 999-month split should throw'); } catch (UserErr) {}
        try { splitPlan(0.05, 24, '2026-01-01');   assert(false, 'a sub-paise share should throw'); } catch (UserErr) {}
        // The sweep's stop condition, run without a database: how many times a split posts by
        // a given day. A split must fire once per month, no more and no fewer, and must stop
        // itself at end_date instead of running on forever.
        $postings = function (string $start, int $months, string $today): int {
            [, $end] = splitPlan(1200.0, $months, $start);
            $nd = $start; $n = 0;
            while ($n < 120 && $nd <= $today && $nd <= $end) { $n++; $nd = advanceDate($nd, 'monthly'); }
            return $n;
        };
        assert($postings('2026-01-01', 12, '2027-06-01') === 12);
        assert($postings('2026-01-31', 12, '2027-06-01') === 12);  // month-end start, February included
        assert($postings('2026-01-01', 24, '2030-01-01') === 24);
        assert($postings('2026-01-01', 12, '2026-03-15') === 3);   // only the months that have come due
        assert($postings('2027-01-01', 12, '2026-08-07') === 0);   // paid in advance, nothing due yet
        // parseAmount edge cases
        try { parseAmount('-1', $config); assert(false, 'neg should throw'); } catch (UserErr) {}
        try { parseAmount('abc', $config); assert(false, 'nan should throw'); } catch (UserErr) {}
        try { parseAmount('1e9', $config); assert(false, 'sci-notation should throw'); } catch (UserErr) {}
        assert(parseAmount('12.34', $config) === 12.34);
        // parseBudget: blank and 0 are valid ("no budget"), garbage is not.
        assert(parseBudget('', $config)     === 0.0);
        assert(parseBudget('0', $config)    === 0.0);
        assert(parseBudget('5000', $config) === 5000.0);
        assert(parseBudget('99.50', $config) === 99.5);
        try { parseBudget('abc', $config); assert(false, 'nan budget should throw'); } catch (UserErr) {}
        try { parseBudget('-5', $config);  assert(false, 'neg budget should throw'); } catch (UserErr) {}
        // investmentFilterSql: "archived" with nothing archived must match zero rows,
        // not silently degrade into an unfiltered list.
        assert(investmentFilterSql('all', ['Gold'])      === ['', []]);
        assert(investmentFilterSql('archived', [])       === [' AND 1 = 0', []]);
        assert(investmentFilterSql('active', [])         === ['', []]);
        assert(investmentFilterSql('archived', ['Gold']) === [' AND type IN (?)', ['Gold']]);
        assert(investmentFilterSql('active', ['Gold','FD']) === [' AND type NOT IN (?,?)', ['Gold','FD']]);
        // Rolling 12-month window for the Earnings chart. Anchored on the 1st, so it must not
        // skip a month when "today" is the 29th–31st, and it must cross the year boundary.
        assert(rollingMonths('2026-08-07', 12)[0] === '2025-09-01');
        assert(rollingMonths('2026-08-07', 12)[1] === '2026-09-01');   // end is exclusive
        assert(count(rollingMonths('2026-08-07', 12)[2]) === 12);
        assert(rollingMonths('2026-08-07', 12)[2][0]  === '2025-09');
        assert(rollingMonths('2026-08-07', 12)[2][11] === '2026-08');
        assert(rollingMonths('2026-03-31', 12)[2][11] === '2026-03');  // no month-end overflow
        assert(rollingMonths('2026-01-15', 3)  === ['2025-11-01', '2026-02-01', ['2025-11','2025-12','2026-01']]);
        // Sub-categories: parents first, each followed by its own children.
        $flat = [
            ['id' => 1, 'name' => 'Household', 'parent_id' => null],
            ['id' => 2, 'name' => 'Rent',      'parent_id' => 1],
            ['id' => 3, 'name' => 'Transport', 'parent_id' => null],
            ['id' => 4, 'name' => 'Repairs',   'parent_id' => 1],
        ];
        assert(array_column(categoryTree($flat), 'name') === ['Household','Rent','Repairs','Transport']);
        assert(array_column(categoryTree($flat), 'depth') === [0, 1, 1, 0]);
        // A child whose parent was deleted still appears, at top level — never dropped.
        assert(array_column(categoryTree([['id' => 9, 'name' => 'Orphan', 'parent_id' => 77]]), 'name') === ['Orphan']);
        assert(categoryTree([])  === []);

        // Rollup: child spend lands on the parent's bar and the parent's own spend must not
        // split it into a second bar. Budget always comes from the parent.
        $rows = [
            ['cid'=>2,'name'=>'Rent','icon'=>'home','budget'=>0.0,'pid'=>1,'pname'=>'Household','picon'=>'tag','pbudget'=>9000.0,'amt'=>5000.0],
            ['cid'=>1,'name'=>'Household','icon'=>'tag','budget'=>9000.0,'pid'=>null,'pname'=>null,'picon'=>null,'pbudget'=>null,'amt'=>250.0],
            ['cid'=>4,'name'=>'Repairs','icon'=>'zap','budget'=>0.0,'pid'=>1,'pname'=>'Household','picon'=>'tag','pbudget'=>9000.0,'amt'=>1500.0],
            ['cid'=>3,'name'=>'Transport','icon'=>'car','budget'=>2000.0,'pid'=>null,'pname'=>null,'picon'=>null,'pbudget'=>null,'amt'=>800.0],
        ];
        $roll = rollupCategories($rows);
        assert(count($roll) === 2);                                  // two bars, not three
        assert($roll[0]['name'] === 'Household' && $roll[0]['amt'] === 6750.0);
        assert($roll[0]['budget'] === 9000.0 && $roll[0]['icon'] === 'tag');
        // Sub-lines are sorted, and the parent's own 250 shows as "Direct" so they add up.
        assert(array_column($roll[0]['children'], 'name') === ['Rent','Repairs','Direct']);
        assert(array_sum(array_column($roll[0]['children'], 'amt')) === $roll[0]['amt']);
        assert($roll[1]['name'] === 'Transport' && $roll[1]['children'] === []);
        // No children, no "Direct" line; uncategorised (cid null) still gets its own bucket.
        $solo = rollupCategories([['cid'=>null,'name'=>'Uncategorised','icon'=>'tag','budget'=>0.0,'pid'=>null,'pname'=>null,'picon'=>null,'pbudget'=>null,'amt'=>10.0]]);
        assert(count($solo) === 1 && $solo[0]['children'] === [] && $solo[0]['amt'] === 10.0);
        assert(rollupCategories([]) === []);
        // Indian grouping: identical to Western up to 5 digits, then diverges.
        assert(groupIndian(0)          === '0.00');
        assert(groupIndian(999.99)     === '999.99');
        assert(groupIndian(1000)       === '1,000.00');
        assert(groupIndian(10000)      === '10,000.00');
        assert(groupIndian(100000)     === '1,00,000.00');        // 1 lakh
        assert(groupIndian(1000000)    === '10,00,000.00');       // 10 lakh
        assert(groupIndian(10000000)   === '1,00,00,000.00');     // 1 crore
        assert(groupIndian(1234567.89) === '12,34,567.89');
        assert(groupIndian(-1200)      === '-1,200.00');
        assert(groupIndian(-250000)    === '-2,50,000.00');
        // Rounded variant used by the summary tiles.
        assert(groupIndian(1234567.89, 0) === '12,34,568');   // rounds, no orphaned paise
        assert(groupIndian(10000000, 0)   === '1,00,00,000');
        assert(groupIndian(999.49, 0)     === '999');
        // Redirect targets come from a POSTed `back` field — keep them same-site.
        assert(safeRedirectTarget('/history?m=2')          === '/history?m=2');
        assert(safeRedirectTarget('/invest?f=archived#x')  === '/invest?f=archived#x');
        assert(safeRedirectTarget('https://evil.example/') === '/');
        assert(safeRedirectTarget('//evil.example/')       === '/');
        assert(safeRedirectTarget("/ok\r\nX-Injected: 1")  === '/');
        assert(safeRedirectTarget('javascript:alert(1)')   === '/');
        assert(safeRedirectTarget('')                      === '/');
        // HTTPS detection must survive a TLS-terminating proxy, or the session cookie
        // silently loses its Secure flag in production.
        $srv = $_SERVER;
        $_SERVER = [];                                             assert(isHttps() === false);
        $_SERVER = ['HTTPS' => 'off'];                             assert(isHttps() === false);
        $_SERVER = ['HTTPS' => 'on'];                              assert(isHttps() === true);
        $_SERVER = ['HTTP_X_FORWARDED_PROTO' => 'https'];          assert(isHttps() === true);
        $_SERVER = ['HTTP_X_FORWARDED_PROTO' => 'https, http'];    assert(isHttps() === true);
        $_SERVER = ['HTTP_X_FORWARDED_PROTO' => 'http'];           assert(isHttps() === false);
        $_SERVER = ['SERVER_PORT' => 443];                         assert(isHttps() === true);
        $_SERVER = ['SERVER_PORT' => 80];                          assert(isHttps() === false);
        $_SERVER = ['HTTPS' => 'on', 'HTTP_HOST' => 'ledger.xpertxyz.com'];
        assert(originUrl() === 'https://ledger.xpertxyz.com');
        $_SERVER = $srv;

        // Who may edit what. The owner edits anything in their ledger; everyone else edits only
        // what they added. A row with no author predates sharing and falls to the owner — back
        // then they were the only user, so nothing is taken away from anyone.
        assert(canEditRow(7,    7, ROLE_MEMBER) === true);
        assert(canEditRow(8,    7, ROLE_MEMBER) === false);
        assert(canEditRow(null, 7, ROLE_MEMBER) === false);
        assert(canEditRow(null, 7, ROLE_OWNER)  === true);
        assert(canEditRow(8,    7, ROLE_OWNER)  === true);
        // Zero is a user id nobody has; it must not collapse into NULL and match an authorless row.
        assert(canEditRow(0,    0, ROLE_MEMBER) === true);
        assert(canEditRow(null, 0, ROLE_MEMBER) === false);
        // mayEdit reads that same rule off a row and a user, and has to agree with it — the
        // server refuses on canEditRow, the view hides on mayEdit, and a disagreement between
        // them shows a control that does nothing.
        assert(mayEdit(['created_by' => 7],    ['id' => 7, 'role' => ROLE_MEMBER]) === true);
        assert(mayEdit(['created_by' => '7'],  ['id' => 7, 'role' => ROLE_MEMBER]) === true);  // PDO gives strings
        assert(mayEdit(['created_by' => 8],    ['id' => 7, 'role' => ROLE_MEMBER]) === false);
        assert(mayEdit(['created_by' => null], ['id' => 7, 'role' => ROLE_MEMBER]) === false);
        assert(mayEdit([],                     ['id' => 7, 'role' => ROLE_OWNER])  === true);
        assert(mayEdit(['created_by' => 8],    ['id' => 7])                        === false); // role absent = member

        // Who a fresh entry may be filed under. The owner picks themselves or any name that
        // does not sign in; everyone else files as themselves, full stop — the picker never
        // renders for them and attributableMember enforces the same rule server-side.
        $mm = [['id' => 1, 'user_id' => 7], ['id' => 2, 'user_id' => null], ['id' => 3, 'user_id' => 9]];
        assert(attributableIds($mm, 7, ROLE_OWNER)  === [1, 2]);
        assert(attributableIds($mm, 9, ROLE_OWNER)  === [2, 3]);  // a second owner-role user, same rule
        assert(attributableIds($mm, 7, ROLE_MEMBER) === [1]);     // own linked row only
        assert(attributableIds($mm, 9, ROLE_MEMBER) === [3]);
        assert(attributableIds($mm, 5, ROLE_MEMBER) === []);      // not linked yet — nothing to pick

        // monthsSpan is the inverse of splitPlan's end date, and editing a split depends on it
        // round-tripping: the dialog re-derives "how many months" from the dates it stored.
        foreach ([2, 6, 12, 18, 24, 36, 120] as $n) {
            foreach (['2026-01-01', '2026-01-31', '2026-02-28', '2026-11-30'] as $from) {
                [, $end] = splitPlan(1200.0, $n, $from);
                assert(monthsSpan($from, $end) === $n, "span $n from $from gave " . monthsSpan($from, $end));
            }
        }
        assert(monthsSpan('2026-03-10', '2026-03-10') === 1);
        assert(monthsSpan('2026-12-01', '2027-01-01') === 2);   // across a year boundary
        // The month-end clamp is why this compares months, not days: 31 Jan + 1 month is 28 Feb,
        // and a day-level diff would call that span 1 instead of 2.
        assert(addMonths('2026-01-31', 1) === '2026-02-28');
        assert(monthsSpan('2026-01-31', '2026-02-28') === 2);

        // The two grouping conventions. The Indian one is not "commas every three" with a
        // different separator — the first group is three digits and every group after it is
        // two, which is why it needs its own function rather than a number_format() argument.
        assert(groupNumber(1000000,  0, 'indian') === '10,00,000');
        assert(groupNumber(1000000,  0, 'world')  === '1,000,000');
        assert(groupNumber(10000000, 0, 'indian') === '1,00,00,000');   // one crore
        assert(groupNumber(10000000, 0, 'world')  === '10,000,000');
        assert(groupNumber(999,      0, 'indian') === '999');           // no comma either way
        assert(groupNumber(999,      0, 'world')  === '999');
        assert(groupNumber(1234.5,   2, 'indian') === '1,234.50');
        assert(groupNumber(1234.5,   2, 'world')  === '1,234.50');      // identical under 100k
        assert(groupNumber(-1234567, 0, 'indian') === '-12,34,567');    // sign survives grouping
        assert(groupNumber(-1234567, 0, 'world')  === '-1,234,567');
        assert(groupNumber(0,        2, 'indian') === '0.00');
        // An unknown or missing style falls back to Indian rather than to an ungrouped number.
        assert(groupNumber(1000000, 0, 'nonsense') === '10,00,000');

        // A ledger is named after its owner so a picker never shows two identical rows.
        assert(ledgerNameFor('Praveen Kumar Boddupalli') === 'Praveen');
        assert(ledgerNameFor('Meera')                    === 'Meera');
        assert(ledgerNameFor('  Anita  Rao ')            === 'Anita');   // collapses whitespace
        assert(ledgerNameFor('Jean-Luc Picard')          === 'Jean-Luc'); // a hyphen is not a break
        assert(ledgerNameFor('')                         === 'Personal'); // nothing usable
        assert(ledgerNameFor('   ')                      === 'Personal');
        assert(mb_strlen(ledgerNameFor(str_repeat('a', 200))) === 80);    // fits households.name

        // One symbol, and one means one character — not one byte, and not "up to eight".
        assert(parseCurrency('₹')   === '₹');
        assert(parseCurrency('$')   === '$');
        assert(parseCurrency('€')   === '€');
        assert(parseCurrency(' ₹ ') === '₹');   // trimmed, not rejected
        foreach (['₹tt', 'Rs', '', '   ', '$$', "\n", "\u{00A0}", '₹ $'] as $bad) {
            $threw = false;
            try { parseCurrency($bad); } catch (UserErr $e) { $threw = true; }
            assert($threw, 'parseCurrency accepted ' . var_export($bad, true));
        }

        // The "who?" filter binds its value instead of interpolating it, and 0 means everyone.
        assert(whoWhere(0)       === ['', []]);
        assert(whoWhere(-1)      === ['', []]);
        assert(whoWhere(5)       === [' AND member_id = ?',   [5]]);
        assert(whoWhere(5, 'e')  === [' AND e.member_id = ?', [5]]);

        echo "ok\n"; exit;
    }
    if ($mode === '--preflight') {
        // Fast, no-HTTP smoke test: run before every deploy.
        // Exit 0 = green, non-zero = at least one FAIL. Prints a checklist to stdout.
        require __DIR__ . '/views.php';
        $pass = 0; $fail = 0; $warn = 0;
        $line = function (string $tag, string $msg) use (&$pass, &$fail, &$warn) {
            $icons = ['OK' => "\033[32m✓\033[0m", 'FAIL' => "\033[31m✗\033[0m", 'WARN' => "\033[33m!\033[0m"];
            echo "  {$icons[$tag]} {$msg}\n";
            if ($tag === 'OK') $pass++; elseif ($tag === 'FAIL') $fail++; else $warn++;
        };
        echo "Open Ledger — preflight\n\n";

        echo "PHP syntax:\n";
        foreach (['index.php','lib.php','views.php','config.php','router.php'] as $f) {
            $r = shell_exec("php -l " . escapeshellarg(__DIR__ . "/$f") . " 2>&1");
            str_contains((string)$r, 'No syntax errors')
                ? $line('OK',   "$f")
                : $line('FAIL', "$f  → $r");
        }

        echo "\nStdlib self-check:\n";
        // Run the real --selfcheck instead of a copy of it. This block used to inline a subset,
        // and the subset drifted: assertions written to stop a shipped bug from returning (the
        // HTTPS-behind-a-proxy matrix, redirect safety, the sweep stop condition) never ran at
        // the deploy gate, which is the only place DEPLOY.md tells you to look.
        // -d zend.assertions=1 because a production php.ini usually compiles assert() away —
        // without it the child prints a cheerful "ok" having checked nothing.
        $sc = trim((string)shell_exec(
            escapeshellarg(PHP_BINARY) . ' -d zend.assertions=1 ' . escapeshellarg(__FILE__) . ' --selfcheck 2>&1'
        ));
        $sc === 'ok'
            ? $line('OK',   'every --selfcheck assertion passes (date math, split plans, amount/budget parsing, investment filter, rolling window, lakh/crore, redirect safety, HTTPS behind a proxy, category tree + rollup, sweep stop condition, who-may-edit, who-filter binding)')
            : $line('FAIL', '--selfcheck: ' . ($sc === '' ? 'no output — assertions did not run' : $sc));

        echo "\nDatabase:\n";
        try {
            $db = makeDb($config);
            $sqlite = isSqlite($db);
            $line('OK', $sqlite
                ? "connected to sqlite {$config['db']['path']}"
                : "connected to {$config['db']['host']}/{$config['db']['name']} as {$config['db']['user']}");
            if ($sqlite) {
                // The three pragmas makeDb() sets. journal_mode persists in the file itself, the
                // other two are per-connection and silently revert to OFF/0 if a future edit
                // drops them — foreign_keys especially, which the schema depends on.
                $jm = strtolower((string)$db->query('PRAGMA journal_mode')->fetchColumn());
                $jm === 'wal' ? $line('OK', 'journal_mode=wal')
                              : $line('FAIL', "journal_mode=$jm — a backup read can block the writer");
                (int)$db->query('PRAGMA foreign_keys')->fetchColumn() === 1
                    ? $line('OK',   'foreign_keys=ON')
                    : $line('FAIL', 'foreign_keys=OFF — SQLite defaults it off and the schema declares them');
                (int)$db->query('PRAGMA busy_timeout')->fetchColumn() > 0
                    ? $line('OK',   'busy_timeout set')
                    : $line('FAIL', 'busy_timeout=0 — a concurrent writer fails instantly instead of waiting');
            }
            // What the schema *should* look like is read out of lib.php rather than copied here:
            // CREATE TABLE gives the base shape, MIGRATIONS the columns and indexes added since.
            // A hand-kept list only covers the migrations someone remembered to also add to this
            // file; this covers the next one automatically.
            $want = [];
            foreach (SCHEMA_STATEMENTS as $sql) {
                if (!preg_match('/CREATE TABLE IF NOT EXISTS (\w+) \((.*)\) ENGINE/s', $sql, $m)) continue;
                $want[$m[1]] = ['cols' => [], 'idx' => [], 'uniq' => []];
                foreach (preg_split('/,\n/', $m[2]) as $part) {
                    $part = trim($part);
                    // UNIQUE is tracked by its columns as well as its name: SQLite keeps it as an
                    // inline table constraint and auto-names the index, so on that driver the name
                    // is gone and only the columns can prove google_sub is still unique.
                    if (preg_match('/^UNIQUE KEY\s+(\w+)\s*\((.+)\)/i', $part, $k)) {
                        $want[$m[1]]['idx'][]  = $k[1];
                        $want[$m[1]]['uniq'][$k[1]] = array_map('trim', explode(',', $k[2]));
                    }
                    elseif (preg_match('/^(?:INDEX|KEY)\s+(\w+)/i', $part, $k))                      $want[$m[1]]['idx'][]  = $k[1];
                    elseif (preg_match('/^(\w+)\s/', $part, $c) && strtoupper($c[1]) !== 'PRIMARY')  $want[$m[1]]['cols'][] = $c[1];
                }
            }
            foreach (MIGRATIONS as $sql) {
                if (!preg_match('/^ALTER TABLE (\w+) (.*)$/s', $sql, $m) || !isset($want[$m[1]])) continue;
                foreach (explode(',', $m[2]) as $clause) {
                    if (preg_match('/ADD COLUMN (\w+)/i', $clause, $c)) $want[$m[1]]['cols'][] = $c[1];
                    if (preg_match('/ADD INDEX (\w+)/i',  $clause, $i)) $want[$m[1]]['idx'][]  = $i[1];
                    // A dropped index must stop being expected, or preflight fails on a correct DB.
                    if (preg_match('/DROP INDEX (\w+)/i', $clause, $i)) $want[$m[1]]['idx'] = array_diff($want[$m[1]]['idx'], [$i[1]]);
                }
            }
            $tables  = $sqlite
                ? $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN)
                : $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $missing = array_diff(array_keys($want), $tables);
            $missing ? $line('FAIL', 'missing tables: ' . implode(', ', $missing))
                     : $line('OK',   'all ' . count($want) . ' tables in lib.php exist');
            $fixHint = $sqlite
                ? ' — sqliteSync() did not apply it'
                : ' — a migration did not apply; delete data/' . SCHEMA_SENTINEL . ' to re-run it';
            foreach ($want as $t => $w) {
                if (in_array($t, $missing, true)) continue;
                if ($sqlite) {
                    $have = array_column($db->query("PRAGMA table_info(`$t`)")->fetchAll(), 'name');
                    // sqliteSchema() prefixes index names with their table, because MySQL scopes
                    // them per table and SQLite shares one namespace across the database.
                    $haveIdx = array_column($db->query("PRAGMA index_list(`$t`)")->fetchAll(), 'name');
                    $mi = [];
                    foreach (array_unique($w['idx']) as $ix) {
                        if (isset($w['uniq'][$ix])) continue;             // checked by columns below
                        if (!in_array("{$t}_{$ix}", $haveIdx, true)) $mi[] = $ix;
                    }
                    // A UNIQUE KEY became an unnamed table constraint; find it by its columns.
                    foreach ($w['uniq'] as $ix => $ucols) {
                        $found = false;
                        foreach ($db->query("PRAGMA index_list(`$t`)")->fetchAll() as $row) {
                            if ((string)$row['origin'] !== 'u' && (string)$row['origin'] !== 'pk') continue;
                            $on = array_column($db->query("PRAGMA index_info(`{$row['name']}`)")->fetchAll(), 'name');
                            if (!array_diff($ucols, $on) && !array_diff($on, $ucols)) { $found = true; break; }
                        }
                        if (!$found) $mi[] = "$ix (unique on " . implode('+', $ucols) . ')';
                    }
                } else {
                    $have    = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
                    $haveIdx = $db->query("SHOW INDEX FROM `$t`")->fetchAll(PDO::FETCH_COLUMN, 2);
                    $mi      = array_diff(array_unique($w['idx']), $haveIdx);
                }
                $mc = array_diff(array_unique($w['cols']), $have);
                $mc || $mi
                    ? $line('FAIL', "$t is missing " . implode(', ', array_merge($mc, $mi)) . $fixHint)
                    : $line('OK',   "$t: " . count(array_unique($w['cols'])) . ' columns, '
                                  . count(array_unique($w['idx'])) . ' indexes match lib.php');
            }
            // The mirror of the above, with no DB involved: a column added only by MIGRATIONS
            // and never folded into CREATE TABLE means an upgraded database and a freshly
            // installed one end up with different shapes.
            $drift = [];
            foreach (MIGRATIONS as $sql) {
                if (!preg_match('/^ALTER TABLE (\w+) (.*)$/s', $sql, $m)) continue;
                $create = implode("\n", array_filter(SCHEMA_STATEMENTS, fn($s) => str_contains($s, "EXISTS {$m[1]} (")));
                foreach (explode(',', $m[2]) as $clause) {
                    if (preg_match('/ADD (?:COLUMN|INDEX) (\w+)/i', $clause, $c) && !str_contains($create, $c[1])) {
                        $drift[] = "{$m[1]}.{$c[1]}";
                    }
                }
            }
            $drift ? $line('FAIL', 'in MIGRATIONS but not in CREATE TABLE: ' . implode(', ', $drift)
                                 . ' — a fresh install will not have it')
                   : $line('OK',   'every migrated column/index is also in its CREATE TABLE (fresh installs match upgrades)');
            // One level only, and a child must never hold a budget the parent already covers.
            $bad = (int)$db->query(
                "SELECT COUNT(*) FROM categories c JOIN categories p ON p.id = c.parent_id WHERE p.parent_id IS NOT NULL"
            )->fetchColumn();
            $bad === 0 ? $line('OK', 'no category nested more than one level')
                       : $line('FAIL', "$bad categor(y/ies) nested two levels deep");
            $budKids = (int)$db->query("SELECT COUNT(*) FROM categories WHERE parent_id IS NOT NULL AND budget > 0")->fetchColumn();
            $budKids === 0 ? $line('OK', 'no sub-category carries its own budget')
                           : $line('WARN', "$budKids sub-categor(y/ies) still carry a budget — the household total will double-count");
            // The list-query indexes (ix_household_date, ix_household_cat, ix_household_type) used
            // to be asserted by name here. They are declared in lib.php's CREATE TABLEs, so the
            // derived check above already covers them — and covers the next one for free.

            // Data integrity — nothing here is derivable from the schema; these are rules the
            // application enforces and a bad write could still break.
            // Every household needs at least one earning category or the Earn add form is unusable.
            $noCats = (int)$db->query(
                "SELECT COUNT(*) FROM households h WHERE NOT EXISTS
                 (SELECT 1 FROM earning_categories ec WHERE ec.household_id = h.id)"
            )->fetchColumn();
            $noCats === 0
                ? $line('OK',   'every household has earning categories')
                : $line('FAIL', "$noCats household(s) have no earning categories — delete data/" . SCHEMA_SENTINEL . " to re-run the backfill");
            // Cross-household references. Every id that arrives in a POST goes through ownedId(),
            // so these are all zero — which is the point: if a new handler ever forgets, a forged
            // id silently files an entry against another household and joins their name back out.
            // NOT EXISTS rather than a join: this catches both halves at once — an id belonging
            // to another household, and an id whose row is simply gone.
            foreach ([
                'expenses.category_id'   => ['expenses',   'category_id', 'categories'],
                'expenses.member_id'     => ['expenses',   'member_id',   'members'],
                'earnings.category_id'   => ['earnings',   'category_id', 'earning_categories'],
                'investments.recurring_id' => ['investments', 'recurring_id', 'recurring'],
                'expenses.recurring_id'  => ['expenses',   'recurring_id', 'recurring'],
                'earnings.recurring_id'  => ['earnings',   'recurring_id', 'recurring'],
                'categories.parent_id'   => ['categories', 'parent_id',   'categories'],
            ] as $what => [$tbl, $col, $ref]) {
                $n = (int)$db->query(
                    "SELECT COUNT(*) FROM `$tbl` t WHERE t.`$col` IS NOT NULL AND NOT EXISTS
                     (SELECT 1 FROM `$ref` r WHERE r.id = t.`$col` AND r.household_id = t.household_id)"
                )->fetchColumn();
                $n === 0 ? $line('OK',   "$what always resolves inside its own household")
                         : $line('FAIL', "$n row(s): $what points at a missing or another household's $ref row");
            }
            // recurring has one category_id column read against whichever table `kind` implies.
            // A kind that changes without its category being re-validated leaves a live item
            // pointing at a row in the wrong table — it then posts uncategorised, silently.
            $crossKind = (int)$db->query(
                "SELECT COUNT(*) FROM recurring r WHERE r.category_id IS NOT NULL AND (
                     (r.kind = 'earning' AND NOT EXISTS (SELECT 1 FROM earning_categories c WHERE c.id = r.category_id AND c.household_id = r.household_id))
                  OR (r.kind = 'expense' AND NOT EXISTS (SELECT 1 FROM categories c         WHERE c.id = r.category_id AND c.household_id = r.household_id)))"
            )->fetchColumn();
            $crossKind === 0
                ? $line('OK',   'every recurring category_id resolves in the table its kind implies')
                : $line('FAIL', "$crossKind recurring item(s) carry a category_id from the wrong table — they will post uncategorised");
            // investments.type is stored as a name, not a foreign key: the one place a rename
            // can orphan rows. The app repoints them on rename; this proves it kept up.
            $orphanType = (int)$db->query(
                "SELECT COUNT(*) FROM investments i WHERE NOT EXISTS
                 (SELECT 1 FROM investment_types t WHERE t.household_id = i.household_id AND t.name = i.type)"
            )->fetchColumn();
            $orphanType === 0
                ? $line('OK',   'every investment type name still exists in investment_types')
                : $line('FAIL', "$orphanType investment(s) name a type that no longer exists — a rename did not repoint them");

            // ── Sharing ────────────────────────────────────────────────
            // Membership rows are the only thing standing between one household's numbers and
            // another's, so every way they can be wrong is worth naming separately.
            $ghost = (int)$db->query(
                "SELECT COUNT(*) FROM household_users hu
                 WHERE NOT EXISTS (SELECT 1 FROM users h WHERE h.id = hu.user_id)
                    OR NOT EXISTS (SELECT 1 FROM households x WHERE x.id = hu.household_id)"
            )->fetchColumn();
            $ghost === 0
                ? $line('OK',   'every membership points at a real user and a real ledger')
                : $line('FAIL', "$ghost membership row(s) point at a deleted user or ledger");

            // Exactly one owner. Zero means nobody can invite, rename or edit another's entry —
            // the ledger is stuck. More than one is a backfill that ran twice.
            // Filtered in an outer WHERE rather than a bare HAVING: this groups nothing, and
            // only MySQL lets HAVING stand in for WHERE on a computed alias.
            $badOwner = $db->query(
                "SELECT id, owners FROM (
                     SELECT h.id, (SELECT COUNT(*) FROM household_users hu
                                   WHERE hu.household_id = h.id AND hu.role = '" . ROLE_OWNER . "') AS owners
                     FROM households h
                 ) x WHERE owners <> 1"
            )->fetchAll();
            $badOwner
                ? $line('FAIL', count($badOwner) . ' ledger(s) do not have exactly one owner: '
                    . implode(', ', array_map(fn($r) => "#{$r['id']} has {$r['owners']}", array_slice($badOwner, 0, 5))))
                : $line('OK',   'every ledger has exactly one owner');

            $over = $db->query(
                "SELECT household_id, COUNT(*) n FROM household_users
                 GROUP BY household_id HAVING n > " . HOUSEHOLD_USERS_MAX
            )->fetchAll();
            $over
                ? $line('FAIL', 'ledger(s) past the ' . HOUSEHOLD_USERS_MAX . '-person cap: '
                    . implode(', ', array_map(fn($r) => "#{$r['household_id']}={$r['n']}", $over)))
                : $line('OK',   'no ledger holds more than ' . HOUSEHOLD_USERS_MAX . ' people');

            // A member row linked to someone who is not in that ledger would put a stranger's
            // name on the "who spent it" filter.
            $badLink = (int)$db->query(
                "SELECT COUNT(*) FROM members m WHERE m.user_id IS NOT NULL AND NOT EXISTS
                 (SELECT 1 FROM household_users hu
                  WHERE hu.user_id = m.user_id AND hu.household_id = m.household_id)"
            )->fetchColumn();
            $badLink === 0
                ? $line('OK',   'every claimed member belongs to someone in that ledger')
                : $line('FAIL', "$badLink member label(s) are linked to a user who is not in that ledger");

            // A live invite must not outlive its stated TTL. This is the check that caught the
            // clock bug when MySQL wrote expires_at and PHP read it: the two sat in different
            // timezones and a 30-minute link reported 360 minutes left. PHP now owns both
            // sides, so this check is really asking "is APP_TZ still the zone the rows were
            // written in" — which is exactly what breaks if someone changes it.
            $ttlStmt = $db->prepare(
                "SELECT COUNT(*) FROM invites WHERE used_at IS NULL AND expires_at > ?"
            );
            $ttlStmt->execute([date('Y-m-d H:i:s', time() + INVITE_TTL_MINUTES * 60)]);
            $longLived = (int)$ttlStmt->fetchColumn();
            $longLived === 0
                ? $line('OK',   'no live invite outlives its ' . INVITE_TTL_MINUTES . '-minute window')
                : $line('FAIL', "$longLived invite(s) expire later than " . INVITE_TTL_MINUTES
                    . ' minutes from now — APP_TZ disagrees with the clock they were written in');

            // ── Split bills ────────────────────────────────────────────
            // A split is defined by its dates; editing one deletes every share it posted and
            // replays them. Each of these is a way that replay can go wrong and leave money
            // in History that no plan accounts for.
            $splitNoStart = (int)$db->query(
                "SELECT COUNT(*) FROM recurring WHERE end_date IS NOT NULL AND start_date IS NULL"
            )->fetchColumn();
            $splitNoStart === 0
                ? $line('OK',   'every split records the date it started')
                : $line('FAIL', "$splitNoStart split(s) have no start_date — their edit dialog cannot reconstruct the plan");

            // A share posted outside the plan's window is one the sweep should never have
            // written, or one a replay failed to clear.
            $strayShare = (int)$db->query(
                "SELECT COUNT(*) FROM expenses e JOIN recurring r ON r.id = e.recurring_id
                 WHERE r.end_date IS NOT NULL
                   AND (e.date < r.start_date OR e.date > r.end_date)"
            )->fetchColumn();
            $strayShare === 0
                ? $line('OK',   'every posted split share falls inside its own start–end window')
                : $line('FAIL', "$strayShare split share(s) sit outside the plan that posted them");

            // More shares than months means a replay added without clearing.
            $overPosted = $db->query(
                "SELECT r.id, r.name, COUNT(e.id) n,
                        (" . sqlYear($db, 'r.end_date') . " - " . sqlYear($db, 'r.start_date') . ") * 12
                        + (" . sqlMonth($db, 'r.end_date') . " - " . sqlMonth($db, 'r.start_date') . ") + 1 AS months
                 FROM recurring r LEFT JOIN expenses e ON e.recurring_id = r.id
                 WHERE r.end_date IS NOT NULL AND r.start_date IS NOT NULL
                 GROUP BY r.id, r.name, r.start_date, r.end_date HAVING n > months"
            )->fetchAll();
            $overPosted
                ? $line('FAIL', 'split(s) with more posted shares than months: '
                    . implode(', ', array_map(fn($r) => "{$r['name']} ({$r['n']}/{$r['months']})", $overPosted)))
                : $line('OK',   'no split has posted more shares than it has months');

            // Currency prefixes every amount on every screen, so a bad one is loud. WARN, not
            // FAIL: these are legacy rows saved when the rule was "up to eight characters",
            // not corruption, and their owner fixes one by re-saving it.
            $wideCur = $db->query(
                "SELECT id, name, currency FROM households WHERE " . sqlCharLen($db, 'currency') . " <> 1"
            )->fetchAll();
            $wideCur
                ? $line('WARN', count($wideCur) . ' ledger(s) have a multi-character currency saved: '
                    . implode(', ', array_map(fn($r) => "#{$r['id']} '{$r['currency']}'", array_slice($wideCur, 0, 5)))
                    . ' — one symbol is the rule now; the owner is asked to pick again on next save')
                : $line('OK',   'every ledger currency is a single symbol');

            // Both settings belong to the ledger now. A style outside the allow-list would make
            // every amount silently fall back to Indian grouping, which reads as a bug.
            $badStyle = $db->query(
                "SELECT id, name, number_format FROM households
                 WHERE number_format NOT IN ('" . implode("','", NUM_STYLES) . "')"
            )->fetchAll();
            $badStyle
                ? $line('FAIL', count($badStyle) . ' ledger(s) hold an unknown number style: '
                    . implode(', ', array_map(fn($r) => "#{$r['id']} '{$r['number_format']}'", $badStyle)))
                : $line('OK',   'every ledger number style is one the app knows');

            $spentAnon = (int)$db->query(
                "SELECT COUNT(*) FROM invites WHERE used_at IS NOT NULL AND used_by IS NULL"
            )->fetchColumn();
            $spentAnon === 0
                ? $line('OK',   'every spent invite records who spent it')
                : $line('FAIL', "$spentAnon used invite(s) have no used_by");
        } catch (Throwable $e) { $line('FAIL', 'DB: ' . $e->getMessage()); }

        echo "\nConfig / env:\n";
        if (isDevStubActive(GOOGLE_CLIENT_ID)) {
            !empty($config['debug'])
                ? $line('WARN', 'GOOGLE_CLIENT_ID is placeholder — dev-stub sign-in is enabled (APP_DEBUG=1). OK for local, NEVER deploy this to prod.')
                : $line('WARN', 'GOOGLE_CLIENT_ID is placeholder — sign-in will fail until you set a real one.');
        } else {
            $line('OK', 'GOOGLE_CLIENT_ID set (' . substr(GOOGLE_CLIENT_ID, 0, 20) . '...)');
        }
        // If data/ isn't writable the schema sentinel can never be written, and every
        // request re-runs all CREATE TABLE + ALTER TABLE statements. Silent, and slow.
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            $line('WARN', 'data/ does not exist yet — it is created on first run; ensure PHP can write there.');
        } elseif (!is_writable($dataDir)) {
            $line('FAIL', 'data/ is NOT writable — schema bootstrap will re-run on every request (slow). chmod 755 data/');
        } else {
            // Name the exact file the running code looks for. Globbing and taking the last
            // match sorts .schema-ok-v10 before .schema-ok-v8 and reports a stale version.
            $line('OK', 'data/ writable' . (file_exists($dataDir . '/' . SCHEMA_SENTINEL)
                ? ' (schema ' . SCHEMA_SENTINEL . ' applied)'
                : ' — ' . SCHEMA_SENTINEL . ' not yet written, migrations run on the first request'));
            $stale = array_filter(glob($dataDir . '/.schema-ok-*') ?: [], fn($f) => basename($f) !== SCHEMA_SENTINEL);
            if ($stale) $line('WARN', count($stale) . ' superseded sentinel(s) in data/ (' . implode(', ', array_map('basename', $stale)) . ') — harmless, cleared on the next bootstrap');
        }
        if (!empty($config['debug'])) $line('WARN', 'APP_DEBUG=1 — PHP errors show on-page. Disable for prod.');
        else                          $line('OK',   'APP_DEBUG off');
        $line(($config['db']['pass'] ?? '') === '' ? 'WARN' : 'OK', 'DB password ' . (($config['db']['pass'] ?? '') === '' ? 'is empty' : 'present'));

        echo "\nStatic assets:\n";
        foreach ([
            // Everything the app actually serves. App icons are referenced by metaHead()
            // and manifest.webmanifest; the wordmark by the sign-in screen.
            'design-tokens/styles.css',
            'assets/app-icon/app-icon.svg',
            'assets/app-icon/icon-16.png',
            'assets/app-icon/icon-32.png',
            'assets/app-icon/icon-180.png',
            'assets/app-icon/icon-192.png',
            'assets/app-icon/icon-512.png',
            'manifest.webmanifest',
            'assets/logo/open-ledger-logo-wordmark.svg',
            'assets/logo/og-image.png',
            '.htaccess',
        ] as $a) {
            file_exists(__DIR__ . "/$a")
                ? $line('OK',   "$a (" . number_format(filesize(__DIR__ . "/$a")) . " bytes)")
                : $line('FAIL', "$a missing");
        }

        echo "\nHTTP guards (regex tests, no live server):\n";
        // Very cheap sanity: does .htaccess deny .env?
        $ht = @file_get_contents(__DIR__ . '/.htaccess') ?: '';
        preg_match('/FilesMatch "\^\\\\\." >/s', $ht) || str_contains($ht, '^\\.')
            ? $line('OK', '.htaccess denies dotfiles')
            : $line('WARN', '.htaccess may not deny dotfiles — check .env is web-inaccessible');
        str_contains($ht, 'config\\.php') ? $line('OK', '.htaccess denies raw config.php fetch') : $line('WARN', 'config.php may be web-accessible');

        // Deployment is `git pull` onto the web root, so every committed directory lands where
        // Apache can serve it. Two of them must not be: android/ is the phone app (it lives
        // here so its build can copy this app's PHP and the two cannot drift), and tests/
        // holds a CLI harness that, fetched over HTTP, EXECUTES against the live database.
        // This check is why that has to be noticed before a deploy rather than after.
        //
        // docs/ is absent from this list on purpose — it is public documentation for an
        // open-source app and is meant to be readable on the site as well as in the repo.
        $rt = @file_get_contents(__DIR__ . '/router.php') ?: '';
        foreach (['android', 'tests'] as $dir) {
            if (!is_dir(__DIR__ . '/' . $dir)) continue;
            $inHt = (bool)preg_match('~RewriteRule \^\(([a-z|]*\b' . $dir . '\b[a-z|]*)\)/~', $ht);
            $inRt = str_contains($rt, $dir);
            $inHt && $inRt
                ? $line('OK',   "$dir/ is denied by .htaccess and router.php")
                : $line('FAIL', "$dir/ ships in the repo but "
                    . (!$inHt ? '.htaccess does not deny it' : 'router.php does not deny it')
                    . ' — it will be served from the web root after a git pull');
        }

        echo "\nSource invariants (static):\n";
        // Only the request-handling half of this file. The checks below quote the same marker
        // strings they search for ('csrfCheck();', the GET-routes comment), so scanning the
        // whole file would find this block instead of the router.
        $full = (string)file_get_contents(__FILE__);
        // Anchored to the start of a line: every copy of these markers inside this block is
        // indented, so ^// only ever matches the real one further down the file.
        preg_match('~^// Debug output~m', $full, $mk, PREG_OFFSET_CAPTURE);
        $src  = substr($full, $mk[0][1]);
        $vsrc = (string)file_get_contents(__DIR__ . '/views.php');
        $lsrc = (string)file_get_contents(__DIR__ . '/lib.php');

        // Every path a view links or posts to must have a handler. A miss falls through to the
        // bare 404 with nothing logged, so it reads as a dead button rather than an error.
        preg_match_all('~(?:^\s*case |\$path === )\'(/[^\']*)\'~m', $src, $m);
        $routes = array_unique($m[1]);
        // Four ways a view names a route: a literal attribute, an askConfirm payload, a form
        // whose action JS repoints — the split dialog serves both add and edit that way, and
        // without that pattern its edit route reads as unreachable — and a bare fetch(), which
        // is how the theme picker records a choice without reloading the page.
        preg_match_all('~(?:action|href)="(/[^"?#]*)|"action"\s*=>\s*"(/[^"?#]*)|\.action\s*=\s*\'(/[^\']*)|fetch\(\s*\'(/[^\']*)~', $vsrc, $u);
        // is_file, not file_exists: href="/" would otherwise match the project directory.
        $linked = array_filter(array_unique(array_merge($u[1], $u[2], $u[3], $u[4])), fn($p) => $p !== '' && !is_file(__DIR__ . $p));
        $orphan = array_diff($linked, $routes);
        $orphan ? $line('FAIL', 'a view links or posts to a path with no route: ' . implode(', ', $orphan))
                : $line('OK',   count($linked) . ' linked paths all resolve to a route');
        // Reached by the crawler or by an old bookmark, never by a link in the app.
        // /join is reached from outside entirely — a link pasted into a chat, not rendered here.
        $dead = array_diff($routes, $linked, ['/sitemap.xml', '/robots.txt', '/manage', '/join']);
        $dead ? $line('WARN', 'route nothing links to: ' . implode(', ', $dead))
              : $line('OK',   'no unreachable routes');

        // Split the POST switch into case => body so the next two checks can read each handler.
        $postAt  = strpos($src, "if (\$method === 'POST') {");
        $postBlk = substr($src, $postAt, strpos($src, '// Authed GET routes.') - $postAt);
        $parts   = preg_split('~^(\s*case \'[^\']*\':|\s*default:)~m', $postBlk, -1, PREG_SPLIT_DELIM_CAPTURE);
        array_shift($parts);
        $fall = $unscoped = [];
        for ($i = 0; $i + 1 < count($parts); $i += 2) {
            [$case, $body] = [trim($parts[$i]), $parts[$i + 1]];
            if (trim($body) === '') continue;   // deliberate stacking: case '/a': case '/b':
            // PHP falls into the *next* case without a terminator. A handler that forgets its
            // redirect() silently runs the following one — which is usually a DELETE.
            if (!preg_match('~\bredirect\(|\bbreak;|\bexit\b~', $body)) $fall[] = $case;
            // Every write must name household_id, or a forged id reaches another household's row.
            // Two exemptions, both addressed by an id that never came from the request:
            // `users` by $user['id'] out of the session, and `households` by $hid, which the
            // request bootstrap has already checked against this user's memberships.
            preg_match_all('~"((?:DELETE FROM|UPDATE)\s+(\w+)[^"]*)"(\s*\.\s*\w+\(\))?~s', $body, $q, PREG_SET_ORDER);
            foreach ($q as $x) {
                if (!str_contains($x[1], 'household_id')
                    && !in_array($x[2], ['users', 'households'], true) && trim($x[3] ?? '') === '') {
                    $unscoped[] = $case . ' → ' . preg_replace('~\s+~', ' ', substr($x[1], 0, 60));
                }
            }
        }
        // Sharing means household scope is no longer the whole answer: a member may edit only
        // what they added. Two checks, because the two halves fail differently — an insert that
        // forgets created_by makes a row nobody but the owner can ever touch, and an update that
        // forgets requireEditable lets anyone in the ledger rewrite anyone else's entry.
        $entryTables = ['expenses', 'earnings', 'investments', 'recurring'];
        $noStamp = $noGuard = [];
        for ($i = 0; $i + 1 < count($parts); $i += 2) {
            [$case, $body] = [trim($parts[$i]), $parts[$i + 1]];
            if (trim($body) === '') continue;
            preg_match_all('~"INSERT INTO (\w+)~', $body, $ins);
            foreach (array_intersect($ins[1], $entryTables) as $t) {
                if (!str_contains($body, 'created_by')) $noStamp[] = "$case → $t";
            }
            preg_match_all('~"(?:UPDATE|DELETE FROM)\s+(\w+)[^"]*WHERE id = \?~s', $body, $upd);
            foreach (array_intersect($upd[1], $entryTables) as $t) {
                if (!str_contains($body, 'requireEditable(')) $noGuard[] = "$case → $t";
            }
        }
        $noStamp ? $line('FAIL', 'entry INSERT that does not record created_by: ' . implode(' ; ', array_unique($noStamp)))
                 : $line('OK',   'every entry INSERT records who created it');
        $noGuard ? $line('FAIL', 'single-row entry write with no requireEditable() guard: ' . implode(' ; ', array_unique($noGuard)))
                 : $line('OK',   'every single-row entry update/delete goes through requireEditable()');

        $fall ? $line('FAIL', 'POST handler with no redirect/break/exit (falls into the next case): ' . implode(', ', $fall))
              : $line('OK',   ((int)(count($parts) / 2)) . ' POST handlers all terminate');
        $unscoped ? $line('FAIL', 'write not scoped by household_id: ' . implode(' ; ', $unscoped))
                  : $line('OK',   'every DELETE/UPDATE in a POST handler is household-scoped');

        // Two handlers validating the same field must accept the same values. /recurring and
        // /recurring/update disagreed once, and editing a recurring earning silently turned it
        // into an expense — the form offered a kind the server quietly discarded.
        preg_match_all('~in_array\(\$_POST\[\'(\w+)\'\]\s*\?\?\s*\'\',\s*(\[[^\]]*\])~', $src, $m2, PREG_SET_ORDER);
        $lists = [];
        foreach ($m2 as $x) $lists[$x[1]][] = preg_replace('~\s+~', '', $x[2]);
        $split = array_keys(array_filter($lists, fn($l) => count(array_unique($l)) > 1));
        $split ? $line('FAIL', 'POST field validated against different allow-lists: '
                             . implode(', ', array_map(fn($f) => "$f (" . implode(' vs ', array_unique($lists[$f])) . ')', $split)))
               : $line('OK',   count($lists) . ' POST allow-list(s) agree across every handler');
        // …and the form must not offer a value the server throws away.
        foreach ($lists as $field => $l) {
            $allowed = json_decode(str_replace("'", '"', $l[0]), true) ?: [];
            if (!preg_match('~name="' . $field . '"[^>]*>(.*?)</select>~s', $vsrc, $sel)) continue;
            preg_match_all('~<option value="([a-z0-9_-]+)"~', $sel[1], $o);
            $extra = array_diff($o[1], $allowed);
            $extra ? $line('FAIL', "a form offers $field=" . implode('/', $extra) . ' but the server rejects it')
                   : $line('OK',   "every $field option a form offers is accepted");
        }

        // Auth gate, then one CSRF check, then the switch. The ordering is the whole guarantee:
        // there is no per-route audit to do as long as nothing sneaks in above csrfCheck().
        // There are two POST blocks — the public one (/signin) and the authed one. Find the
        // authed switch by starting from the end of the unauthed gate, not from the top.
        $gate     = strpos($src, "redirect('/login');\n}");
        $authPost = $gate === false ? false : strpos($src, "if (\$method === 'POST') {", $gate);
        $csrf     = strpos($src, 'csrfCheck();');
        $sw       = $authPost === false ? false : strpos($src, 'switch ($path) {', $authPost);
        $gate !== false && $authPost !== false && $sw !== false
            && $gate < $authPost && $authPost < $csrf && $csrf < $sw && substr_count($src, 'csrfCheck();') === 1
            ? $line('OK',   'every POST route sits behind the auth gate and a single csrfCheck()')
            : $line('FAIL', 'the POST switch is no longer uniformly auth/CSRF gated — check the order in index.php');
        // `back` is attacker-controlled and only redirect() runs it through safeRedirectTarget().
        $loc = 0;
        foreach (['index.php', 'lib.php', 'views.php'] as $f) $loc += preg_match_all('~header\(\s*[\'"]Location~', (string)file_get_contents(__DIR__ . "/$f"));
        $loc === 1 ? $line('OK',   'redirect() is the only place that sends a Location header')
                   : $line("FAIL", "$loc Location headers — one of them bypasses safeRedirectTarget()");

        // Limits are config, and a typo'd key reads as null: the cap silently stops applying.
        preg_match_all('~\$L\[\'(\w+)\'\]|\[\'limits\'\]\[\'(\w+)\'\]~', $src . $vsrc . $lsrc, $lk);
        $usedL = array_filter(array_unique(array_merge($lk[1], $lk[2])));
        $ghost = array_diff($usedL, array_keys($config['limits']));
        $ghost ? $line('FAIL', 'limit key read but never defined: ' . implode(', ', $ghost) . ' — that cap is not being enforced')
               : $line('OK',   count($usedL) . ' limit keys all defined in config');
        $unusedL = array_diff(array_keys($config['limits']), $usedL);
        $unusedL ? $line('WARN', 'limit defined but never read: ' . implode(', ', $unusedL))
                 : $line('OK',   'every configured limit is read somewhere');

        // Column names in INSERT/UPDATE, checked against the schema parsed out of lib.php.
        // SELECT is deliberately not checked — the list queries alias and join, so resolving a
        // bare column to a table needs a parser, and a regex would only produce noise.
        $schemaCols = [];
        foreach (($want ?? []) as $t => $w) $schemaCols[$t] = array_unique($w['cols']);
        $badCol = [];
        $all = $src . $vsrc . (string)file_get_contents(__DIR__ . '/lib.php');
        preg_match_all('~INSERT INTO\s+(\w+)\s*\(([^)]*)\)~i', $all, $ins, PREG_SET_ORDER);
        foreach ($ins as $x) {
            if (!isset($schemaCols[$x[1]])) continue;   // skips interpolated table names
            foreach (preg_split('~\s*,\s*~', trim($x[2])) as $c) {
                if ($c !== '' && !in_array($c, $schemaCols[$x[1]], true)) $badCol[] = "INSERT {$x[1]}.$c";
            }
        }
        preg_match_all('~UPDATE\s+(\w+)\s+SET\s+(.*?)\s+WHERE~is', $all, $upd, PREG_SET_ORDER);
        foreach ($upd as $x) {
            if (!isset($schemaCols[$x[1]])) continue;
            preg_match_all('~(\w+)\s*=~', $x[2], $cc);
            foreach ($cc[1] as $c) if (!in_array($c, $schemaCols[$x[1]], true)) $badCol[] = "UPDATE {$x[1]}.$c";
        }
        $badCol ? $line('FAIL', 'SQL writes a column the schema does not have: ' . implode(', ', array_unique($badCol)))
                : $line('OK',   count($ins) . ' INSERTs and ' . count($upd) . ' UPDATEs name only real columns');

        echo "\nPublic pages:\n";
        try {
            ob_start(); renderLanding(); $lp = (string)ob_get_clean();
            ob_start(); renderSignIn(); $sp = (string)ob_get_clean();
            substr_count($lp, '<h1') === 1
                ? $line('OK',   'landing has exactly one h1')
                : $line('FAIL', 'landing has ' . substr_count($lp, '<h1') . ' h1 elements');
            substr_count($lp, 'href="/login"') >= 3
                ? $line('OK',   'landing CTAs point at /login (' . substr_count($lp, 'href="/login"') . ')')
                : $line('FAIL', 'landing is missing its /login CTAs');
            // The trust section claims no third-party request happens until you sign in.
            // Google's script must therefore live on /login and nowhere else.
            !str_contains($lp, 'accounts.google.com')
                ? $line('OK',   'landing loads nothing third-party')
                : $line('FAIL', 'landing references accounts.google.com — the "no third-party" claim is false');
            str_contains($sp, 'accounts.google.com/gsi/client')
                ? $line('OK',   '/login loads Google Identity Services')
                : $line('FAIL', '/login is missing the gsi/client script — sign-in is broken');
            str_contains($sp, 'data-login_uri="/signin"')
                ? $line('OK',   '/login posts its credential to /signin')
                : $line('FAIL', '/login lost its data-login_uri — Google has nowhere to post');
            // The pair the sitemap depends on: the one page it lists must be
            // indexable, and the gate it links to must not be.
            !str_contains($lp, 'noindex')
                ? $line('OK',   'landing is indexable')
                : $line('FAIL', 'landing carries a noindex — the sitemap lists a page Google will drop');
            str_contains($sp, 'name="robots" content="noindex"')
                ? $line('OK',   '/login is noindex')
                : $line('WARN', '/login lost its noindex — the sign-in gate may get indexed');
            str_contains($lp, '<link rel="canonical"')
                ? $line('OK',   'landing declares a canonical')
                : $line('FAIL', 'landing lost its canonical — it must match the sitemap <loc>');
            foreach (['landing' => $lp, 'sign-in' => $sp] as $name => $html) {
                str_contains($html, 'prefers-color-scheme: dark')
                    ? $line('OK',   "$name follows the OS dark preference")
                    : $line('WARN', "$name has no dark-mode block");
                // paintStatusBar() rewrites the theme-color meta, so the meta has
                // to already exist when the script runs. Emit them the other way
                // round and the mobile status bar silently stops following.
                $meta = strpos($html, 'name="theme-color"');
                $boot = strpos($html, 'function paintStatusBar');
                $meta !== false && $boot !== false && $meta < $boot
                    ? $line('OK',   "$name repaints the mobile status bar")
                    : $line('FAIL', "$name: theme-color meta missing or emitted after the script that repaints it");
            }
        } catch (Throwable $e) { @ob_end_clean(); $line('FAIL', 'public pages: ' . $e->getMessage()); }

        echo "\nApp pages (rendered in-process, no HTTP):\n";
        $pages = [];
        try {
            // Render against the household with the most conditional markup, not merely the most
            // rows: several blocks only appear above a threshold — the member chips need two
            // members, the nest dialog needs a category, #ed-member needs a member list. Ordering
            // by members first is what makes those paths visible here at all. (Picking by category
            // count alone once chose a one-member household and hid a broken member chip.)
            $hid0 = (int)($db->query(
                "SELECT h.id FROM households h
                 ORDER BY (SELECT COUNT(*) FROM members     m WHERE m.household_id = h.id) DESC,
                          (SELECT COUNT(*) FROM categories  c WHERE c.household_id = h.id) DESC,
                          (SELECT COUNT(*) FROM expenses    e WHERE e.household_id = h.id) DESC
                 LIMIT 1"
            )->fetchColumn() ?: 0);
            // Rendered as the owner, because the owner sees the most markup: a member's view
            // hides every edit and delete control, and the id/duplicate/comment checks below
            // would then never look at them. The member's view gets its own pass afterwards.
            $stub = ['id' => 0, 'household_id' => $hid0, 'name' => 'Preflight',
                     'email' => 'preflight@local', 'is_dark' => 0, 'role' => ROLE_OWNER];
            foreach ([
                'add'       => ['renderAdd',       [$db, $stub]],
                'history'   => ['renderHistory',   [$db, $stub, 0]],
                'earn'      => ['renderEarn',      [$db, $stub, true]],
                'invest'    => ['renderInvest',    [$db, $stub, true, 'active']],
                'recurring' => ['renderRecurring', [$db, $stub, true]],
                'year'      => ['renderYear',      [$db, $stub, (int)date('Y'), 'cal', 'all']],
                'organise'  => ['renderOrganise',  [$db, $stub]],
                'ledgers'   => ['renderLedgers',   [$db, $stub]],
                'terms'     => ['renderTerms',     [$db, $stub]],
            ] as $name => [$fn, $args]) {
                ob_start(); $fn(...$args); $pages[$name] = (string)ob_get_clean();
            }
            $line('OK', count($pages) . ' tabs render without fataling against household ' . $hid0
                      . ' (' . number_format(array_sum(array_map('strlen', $pages))) . ' bytes)');
            if ($hid0 === 0) $line('WARN', 'no household has categories — pages rendered empty, so their conditional dialogs went unchecked');

            // The same pages as somebody who joined and has added nothing. Every edit and delete
            // control must be gone: leaving one visible offers an action the server will refuse,
            // which reads as a broken app rather than as a permission boundary.
            // User id -1 can own no row, so this is the strictest possible member.
            $guest = ['id' => -1, 'household_id' => $hid0, 'name' => 'Guest',
                      'email' => 'guest@local', 'is_dark' => 0, 'role' => ROLE_MEMBER];
            $leaked = $pickers = [];
            foreach ([
                'add'       => ['renderAdd',       [$db, $guest]],
                'history'   => ['renderHistory',   [$db, $guest, 0]],
                'earn'      => ['renderEarn',      [$db, $guest, true]],
                'invest'    => ['renderInvest',    [$db, $guest, true, 'active']],
                'recurring' => ['renderRecurring', [$db, $guest, true]],
            ] as $name => [$fn, $args]) {
                ob_start(); $fn(...$args); $html = (string)ob_get_clean();
                // The row controls, and only those: the drawer and the dialogs legitimately keep
                // their own buttons, so match the confirm payloads that name an entry endpoint.
                if (preg_match('~/(expenses|earnings|investments|recurring)/delete~', $html)
                    || preg_match("~openEdit(Expense|Earning|Investment|Recurring)\(~", $html)) {
                    // The edit dialogs define openEdit* as a function; a row *calls* it.
                    if (preg_match("~onclick='openEdit~", $html)) $leaked[] = $name;
                }
                // A member files every entry as themselves, so no page may offer them a "who?"
                // choice — neither the select the dialogs use nor the Add tab's chip row. The
                // hidden member_id inputs are fine: those carry, they don't ask.
                if (preg_match('~<select[^>]*name="member_id"~', $html)
                    || strpos($html, 'id="mem-input"') !== false) $pickers[] = $name;
            }
            $leaked ? $line('FAIL', 'a member who created nothing is still offered edit controls on: ' . implode(', ', $leaked))
                    : $line('OK',   'a member sees no edit or delete control on rows they did not create');
            $pickers ? $line('FAIL', 'a member is still offered the who-picker on: ' . implode(', ', $pickers))
                     : $line('OK',   'a member is never asked who an entry is for — it is always their own');
        } catch (Throwable $e) { @ob_end_clean(); $line('FAIL', 'render: ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()); }

        if ($pages) {
            // A button whose handler calls getElementById on an id the page never emits does
            // nothing at all, silently — the usual result of renaming a dialog in one place.
            $deadIds = $dupIds = $badHead = $undefCls = [];
            foreach ($pages as $name => $html) {
                preg_match_all("~getElementById\('([^']+)'\)~", $html, $r);
                preg_match_all('~\baria-labelledby="([^"]+)"~', $html, $a);
                preg_match_all('~\bid="([^"]+)"~', $html, $d);
                if ($hid0 > 0 && $dead = array_diff(array_unique(array_merge($r[1], $a[1])), $d[1])) {
                    $deadIds[] = "$name: " . implode(', ', $dead);
                }
                // Duplicated ids are worse than dead ones: getElementById silently takes the
                // first, so every write lands in whichever copy is not on screen.
                if ($dup = array_keys(array_filter(array_count_values($d[1]), fn($n) => $n > 1))) {
                    $dupIds[] = "$name: " . implode(', ', $dup);
                }
                // viewport-fit=cover is load-bearing, not cosmetic: without it env(safe-area-inset-*)
                // resolves to 0 and every toast renders under the notch.
                if (!(str_starts_with(ltrim($html), '<!doctype')
                      && substr_count($html, '/design-tokens/styles.css') === 1
                      && substr_count($html, '<title>') === 1
                      && str_contains($html, 'viewport-fit=cover')
                      && str_contains($html, 'name="theme-color"'))) $badHead[] = $name;
            }
            $deadIds ? $line('FAIL', 'JS references an id the page never emits — ' . implode(' ; ', $deadIds))
                     : $line('OK',   'every element id the inline JS reaches for is emitted');
            // An HTML comment written between attributes ends the tag at its own `>`: the rest of
            // the attributes become body text and the handler is simply gone. Silent, and php -l
            // cannot see it. This shipped once on the member chips.
            $inTag = [];
            foreach ($pages + ['landing' => $lp ?? '', 'sign-in' => $sp ?? ''] as $name => $html) {
                if ($html !== '' && preg_match('~<[a-zA-Z][^<>]*<!--~', $html)) $inTag[] = $name;
            }
            $inTag ? $line('FAIL', 'HTML comment inside a tag on: ' . implode(', ', $inTag)
                                 . ' — the tag ends at the comment and the remaining attributes render as text')
                   : $line('OK',   'no page opens a comment inside a tag');
            $dupIds  ? $line('FAIL', 'duplicate ids on one page — ' . implode(' ; ', $dupIds))
                     : $line('OK',   'no page emits a duplicate id');
            $badHead ? $line('FAIL', 'malformed <head> on: ' . implode(', ', $badHead) . ' — doctype, one stylesheet, one title, viewport-fit, theme-color')
                     : $line('OK',   'every tab has a well-formed head (one stylesheet, viewport-fit, theme-color)');

            // Class names are resolved by the time they reach the HTML, so this compares what
            // the markup actually asks for against every selector the app defines.
            $css = (string)file_get_contents(__DIR__ . '/design-tokens/styles.css');
            preg_match_all('~<style>(.*?)</style>~s', $vsrc, $inline);
            preg_match_all('~\.([a-zA-Z][a-zA-Z0-9_-]*)~', $css . "\n" . implode("\n", $inline[1]), $defined);
            $defined = array_unique($defined[1]);
            foreach ($pages as $name => $html) {
                preg_match_all('~\bclass="([^"]*)"~', $html, $cu);
                $used  = array_filter(array_unique(preg_split('~\s+~', trim(implode(' ', $cu[1]))) ?: []));
                if ($un = array_diff($used, $defined)) $undefCls[] = "$name: " . implode(', ', $un);
            }
            $undefCls ? $line('WARN', 'class used but never styled — ' . implode(' ; ', $undefCls))
                      : $line('OK',   'every class the markup uses is defined somewhere');
        }

        echo "\nStyle layers:\n";
        // The regression this section exists for: press feedback and the tap-highlight reset were
        // moved into the shared stylesheet, which every page links. The `*` reset then killed the
        // native highlight on every tappable that had no :active of its own, and the more specific
        // .btn rule outranked .amount-submit's. Both belong to the page that owns them.
        $css = (string)file_get_contents(__DIR__ . '/design-tokens/styles.css');

        // A var(--typo) is not an error anywhere — CSS drops the declaration and the element
        // renders with no background, or no border, and looks merely "a bit off". The design
        // system is the whole point of this app's look, so a token that does not exist is a bug.
        preg_match_all('~var\(\s*(--[a-z0-9-]+)~i', $vsrc . $css, $used);
        preg_match_all('~(--[a-z0-9-]+)\s*:~i',     $vsrc . $css, $defined);
        $ghostVars = array_values(array_unique(array_diff($used[1], $defined[1])));
        $ghostVars
            ? $line('FAIL', 'CSS uses a token nothing defines: ' . implode(', ', $ghostVars))
            : $line('OK',   count(array_unique($used[1])) . ' CSS tokens used, all defined');

        // Palettes must define the SAME set of variables, all of them. A palette that omits one
        // does not fall back to anything sensible — the previous palette's value stays on the
        // page, so switching from Plum to Harbor would leave one stray berry-coloured thing.
        // Organic light is the reference and is exempt from carrying it: its values are the
        // stylesheet's :root, which is what makes it the default with no attribute at all.
        $varNames = function (string $block): array {
            preg_match_all('~(--[a-z0-9-]+)\s*:~i', $block, $m);
            $n = array_unique($m[1]); sort($n); return $n;
        };
        $ref = $varNames(THEMES['organic']['dark']['vars']);
        $short = [];
        foreach (THEMES as $key => $t) {
            foreach (['light', 'dark'] as $m) {
                $have = $key === 'organic' && $m === 'light'
                    ? array_merge($varNames($t[$m]['vars']), $varNames($css))   // :root does the rest
                    : $varNames($t[$m]['vars']);
                if ($miss = array_diff($ref, $have)) $short[] = "$key $m (" . implode(' ', $miss) . ')';
            }
        }
        $short ? $line('FAIL', 'palette misses variables another palette sets: ' . implode(', ', $short))
               : $line('OK',   count(THEMES) . ' palettes x light/dark all define the same ' . count($ref) . ' variables');
        // The picker and the stylesheet have to name the same palettes: a card whose key has no
        // CSS block is a tap that does nothing, and a block no card offers is dead weight.
        $shell   = $pages['add'] ?? '';
        $unwired = array_values(array_filter(array_keys(THEMES), fn($k) =>
            !str_contains($shell, 'data-pick="' . $k . '"') || !str_contains($shell, '[data-palette="' . $k . '"]')));
        $unwired ? $line('FAIL', 'palette offered without CSS, or styled without a card: ' . implode(', ', $unwired))
                 : $line('OK',   'every palette has both a card in the drawer and a block in the page CSS');

        !str_contains($css, '-webkit-tap-highlight-color')
            ? $line('OK',   'the shared stylesheet owns no tap-highlight rule')
            : $line('FAIL', 'design-tokens/styles.css sets -webkit-tap-highlight-color — that lands on every page; scope it to layout() or renderLanding()');
        preg_match_all('~(?:^|\}|\*/)\s*(\*[^{;@]*)\{~m', $css, $uni);
        count($uni[1]) === 1 && str_contains($css, '*, *::before, *::after { box-sizing: border-box; }')
            ? $line('OK',   'the shared stylesheet has one universal rule (box-sizing)')
            : $line('FAIL', count($uni[1]) . ' universal selectors in styles.css [' . implode(' | ', array_map('trim', $uni[1]))
                          . '] — only the box-sizing reset may be global');
        // …and the pages that do own press feedback must still carry it. Exact strings: a
        // reformat trips this, and re-approving one line is the price of catching a deletion.
        $shell = $pages['add'] ?? '';
        foreach ([
            'a, button, [role="button"], .row, .cat-chip, .pill-btn { -webkit-tap-highlight-color: transparent; }',
            '.tabnav a:active:not(.on) { opacity: 1; background: var(--color-neutral-200); }',
            '.icon-btn:active { background: var(--color-neutral-300); }',
            '.btn:active { transform: scale(.98); filter: brightness(.95); }',
            '.amount-submit:active { transform: scale(.94); }',
        ] as $rule) {
            if ($shell === '') break;
            str_contains($shell, $rule)
                ? $line('OK',   'app shell keeps ' . rtrim(strtok($rule, '{')))
                : $line('FAIL', 'layout() lost its press feedback: ' . $rule);
        }
        foreach ([
            '.btn { -webkit-tap-highlight-color: transparent; transition: filter .12s, transform .05s; }',
            '.btn:active { transform: scale(.98); filter: brightness(.95); }',
        ] as $rule) {
            str_contains($lp ?? '', $rule)
                ? $line('OK',   'landing keeps ' . rtrim(strtok($rule, '{')))
                : $line('FAIL', 'renderLanding() lost its press feedback: ' . $rule);
        }

        echo "\nIcons:\n";
        // A name with no matching <symbol> renders a blank box — no error, nothing in the log.
        preg_match_all('~<symbol id="icon-([a-z0-9-]+)"~', SVG_SPRITE, $sym);
        preg_match_all("~\bicon\('([a-z0-9-]+)'~", $vsrc, $calls);
        $missLit = array_diff(array_unique($calls[1]), $sym[1]);
        $missLit ? $line('FAIL', 'icon() called with a name the sprite lacks: ' . implode(', ', $missLit) . ' — renders blank')
                 : $line('OK',   count(array_unique($calls[1])) . ' literal icon names all in the sprite');
        // Most call sites pass a name loaded from the database, which grep cannot see. Seed
        // list and stored rows both have to keep up with a sprite rename.
        $missSeed = array_diff(array_column(DEFAULT_CATEGORIES, 1), $sym[1]);
        $missSeed ? $line('FAIL', 'DEFAULT_CATEGORIES seeds an icon the sprite lacks: ' . implode(', ', $missSeed))
                  : $line('OK',   count(DEFAULT_CATEGORIES) . ' seeded category icons all in the sprite');
        $missDb = array_diff(array_filter($db->query("SELECT DISTINCT icon FROM categories")->fetchAll(PDO::FETCH_COLUMN)), $sym[1]);
        $missDb ? $line('FAIL', 'a stored category icon is not in the sprite: ' . implode(', ', $missDb) . ' — those rows render blank')
                : $line('OK',   'every icon name stored in categories is in the sprite');

        echo "\nRecurring sweep (dry run):\n";
        try {
            if (isset($db)) { sweepRecurring($db, 0); $line('OK', 'sweepRecurring runs cleanly against household_id=0 (no-op)'); }
        } catch (Throwable $e) { $line('FAIL', 'sweepRecurring: ' . $e->getMessage()); }

        echo "\n────────────────────────\n";
        printf("  %d passed, %d warnings, %d failed\n", $pass, $warn, $fail);
        exit($fail > 0 ? 1 : 0);
    }
    if ($mode === '--cron') {
        // Runs from Hostinger cron. Only touches households that actually have a due
        // recurring item — uses the (household_id, next_date) index.
        $db = makeDb($config);
        $due = $db->prepare("SELECT DISTINCT household_id FROM recurring WHERE next_date <= ?");
        $due->execute([today()]);
        $hids = $due->fetchAll(PDO::FETCH_COLUMN);
        foreach ($hids as $hid) sweepRecurring($db, (int)$hid);
        $stale = $db->prepare("DELETE FROM rate_limits WHERE window_end < ?");
        $stale->execute([time() - 3600]);
        echo "swept " . count($hids) . " households\n"; exit;
    }
    if ($mode === '--backup') {
        // A consistent snapshot of a SQLite ledger, for the Android build's Google Drive
        // backup. VACUUM INTO is the whole job: it reads through the WAL and writes one
        // settled file, which plain-copying the .db cannot do — that captures a torn database
        // plus a hot -wal and restores as corruption.
        //
        // Android calls this and uploads the result to the Drive appDataFolder scope. That
        // scope is deliberate: full `drive` access is a Google-restricted scope needing an
        // annual CASA security assessment, and appdata needs none of it.
        $out = $argv[2] ?? '';
        if ($out === '')                       { fwrite(STDERR, "usage: php index.php --backup /path/to/out.db\n"); exit(1); }
        if (($config['db']['driver'] ?? '') !== 'sqlite') { fwrite(STDERR, "--backup is for DB_DRIVER=sqlite only\n"); exit(1); }
        if (file_exists($out))                 { fwrite(STDERR, "refusing to overwrite $out\n"); exit(1); }
        $db = makeDb($config);
        // Bound as a parameter is not possible here — VACUUM INTO takes a literal — so quote
        // it the way SQLite does, by doubling single quotes.
        $db->exec("VACUUM INTO '" . str_replace("'", "''", $out) . "'");
        $check = (new PDO('sqlite:' . $out))->query('PRAGMA integrity_check')->fetchColumn();
        if ($check !== 'ok') { fwrite(STDERR, "backup failed integrity_check: $check\n"); exit(1); }
        echo "backed up to $out (" . number_format((int)filesize($out)) . " bytes, integrity ok)\n";
        exit;
    }
    if ($mode === '--restore') {
        // Put a snapshot back. Run with the server stopped — the Android launcher kills the
        // PHP process first — because this replaces the file the interpreter has open.
        //
        // Takes a plain .db, never a .gz: the Android PHP build has no zlib (--disable-all),
        // so whoever fetched the archive decompresses it. See DriveBackup.kt.
        $src = $argv[2] ?? '';
        if ($src === '')                       { fwrite(STDERR, "usage: php index.php --restore /path/to/snapshot.db\n"); exit(1); }
        if (($config['db']['driver'] ?? '') !== 'sqlite') { fwrite(STDERR, "--restore is for DB_DRIVER=sqlite only\n"); exit(1); }
        if (!is_readable($src))                { fwrite(STDERR, "cannot read $src\n"); exit(1); }

        // Validate BEFORE touching the live ledger. Restoring is the one operation that can
        // destroy every entry the household has, so a corrupt or unrelated file has to be
        // rejected while the real database is still untouched.
        try {
            $in = new PDO('sqlite:' . $src, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            if ($in->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
                fwrite(STDERR, "$src fails integrity_check — refusing to restore it\n"); exit(1);
            }
            // A valid SQLite file is not necessarily one of ours. Without this, pointing the
            // restore at any random .db would wipe the ledger and leave nothing behind.
            $have = $in->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
            $need = array_keys(sqliteSchema());
            if ($missing = array_diff($need, $have)) {
                fwrite(STDERR, "$src is not an Open Ledger database (missing: "
                    . implode(', ', $missing) . ")\n"); exit(1);
            }
            $entries = (int)$in->query("SELECT COUNT(*) FROM expenses")->fetchColumn()
                     + (int)$in->query("SELECT COUNT(*) FROM earnings")->fetchColumn()
                     + (int)$in->query("SELECT COUNT(*) FROM investments")->fetchColumn();
            $in = null;
        } catch (PDOException $e) {
            fwrite(STDERR, "$src is not a readable SQLite database: {$e->getMessage()}\n"); exit(1);
        }

        $dest = $config['db']['path'];
        // Keep what is being replaced. If the copy below dies halfway there is still a ledger.
        if (file_exists($dest) && !@copy($dest, $dest . '.pre-restore')) {
            fwrite(STDERR, "could not stash the current ledger at $dest.pre-restore\n"); exit(1);
        }
        // The -wal and -shm belong to the OLD database. Leave them and SQLite will happily
        // replay a stale write-ahead log on top of the restored file, which is a corrupt
        // ledger that passes every check until the missing rows are noticed.
        foreach (['-wal', '-shm'] as $suffix) @unlink($dest . $suffix);
        if (!@copy($src, $dest)) { fwrite(STDERR, "copy to $dest failed\n"); exit(1); }

        // Prove the thing now on disk opens, and bring it up to the running code's schema —
        // a backup taken by an older app version can be a column short.
        $db = makeDb($config);
        $n = (int)$db->query("SELECT COUNT(*) FROM expenses")->fetchColumn();
        echo "restored $dest from $src — $entries entries in the snapshot, $n expenses live\n";
        echo "previous ledger kept at $dest.pre-restore\n";
        exit;
    }
    fwrite(STDERR, "usage: php index.php --selfcheck | --preflight | --cron"
        . " | --backup <path> | --restore <path>\n"); exit(1);
}

// Debug output — surface fatals + warnings in the browser response.
// Only for troubleshooting; production must stay off.
if (!empty($config['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

$path   = parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ────────────────────────────────────────────────────────────────────
// Loopback guard (Android only — never set on the web, where this is a no-op).
//
// The Android build serves this app from 127.0.0.1 on a random port. A loopback socket is
// NOT private on Android: any other app on the phone can connect to it, and this one answers
// with the household's whole ledger. So the launcher mints a random token per process, drops
// it into the WebView as a cookie before the first page load, and PHP refuses anything that
// cannot present it. Another app cannot read our cookie jar and cannot guess the token.
//
// Checked here — before the session starts and before the DB is opened — so an unauthorised
// caller cannot even make us do work.
// ────────────────────────────────────────────────────────────────────
$localToken = (string)(getenv('HL_LOCAL_TOKEN') ?: '');
if ($localToken !== '' && !hash_equals($localToken, (string)($_COOKIE['hl_local'] ?? ''))) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Forbidden.\n");
}

// ────────────────────────────────────────────────────────────────────
// Crawler files, answered before the session starts and before the DB is
// touched — a Googlebot fetch shouldn't mint a session file and a cookie.
// Built from the request host rather than a hardcoded domain, so they stay
// correct on any deployment.
//
// Only the landing page is listed: every app route 302s to /login for a
// crawler, and /login carries a noindex, so nothing else is indexable.
// ────────────────────────────────────────────────────────────────────
if ($method === 'GET' && ($path === '/sitemap.xml' || $path === '/robots.txt')) {
    $origin = originUrl();
    header('Cache-Control: public, max-age=3600');
    if ($path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        // Deliberately no Disallow: blocking the app paths would also block the
        // stylesheet, and Google renders the landing page before judging it.
        echo "User-agent: *\nAllow: /\n\nSitemap: $origin/sitemap.xml\n";
        exit;
    }
    // lastmod tracks the file the page is rendered from, so it moves on deploy
    // and not before.
    $lastmod = gmdate('Y-m-d', (int)(@filemtime(__DIR__ . '/views.php') ?: time()));
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
       . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
       . "  <url>\n    <loc>" . h($origin) . "/</loc>\n    <lastmod>$lastmod</lastmod>\n"
       . "    <changefreq>monthly</changefreq>\n  </url>\n"
       . "</urlset>\n";
    exit;
}

// ────────────────────────────────────────────────────────────────────
// Session hardening — before session_start(), so cookie flags land.
// ────────────────────────────────────────────────────────────────────
session_name($config['session_name']);
$sessionLife = 60 * 60 * 24 * 30;   // stay signed in for 30 days, even after the browser closes
session_set_cookie_params([
    'lifetime' => $sessionLife,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => isHttps(),
]);
ini_set('session.gc_maxlifetime', (string)$sessionLife);
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode',  '1');
session_start();

// ────────────────────────────────────────────────────────────────────
// DB
// ────────────────────────────────────────────────────────────────────
try {
    $db = makeDb($config);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Database connection failed. Edit config.php with your Hostinger MySQL credentials.\n\n" . $e->getMessage());
}

require __DIR__ . '/views.php';

$user = currentUser($db);

// ────────────────────────────────────────────────────────────────────
// Local mode (Android): there is no Google, no account and no second device — the ledger is
// one SQLite file on one phone. So the first request creates the single local household and
// signs into it, and /login is never reached.
//
// Gated on the feature flag, which the Android launcher sets in the process environment at
// build time. On the web the flag is on and none of this runs.
//
// This is deliberately NOT a "skip auth" switch for the server: it only ever resolves to the
// one local user, and the loopback guard above is what stands between that user and anything
// else on the device.
// ────────────────────────────────────────────────────────────────────
if (!$user && !$config['features']['google_signin']) {
    $localSub = 'local-device-user';
    $stmt = $db->prepare("SELECT id FROM users WHERE google_sub = ?");
    $stmt->execute([$localSub]);
    $uid = $stmt->fetchColumn()
        ?: bootstrapHousehold($db, 'Me', 'me@localhost', $localSub);
    $_SESSION['user_id'] = (int)$uid;
    $user = currentUser($db);
}

// Every request gets a light global limiter to blunt scanners; the per-endpoint
// limits below are the real controls.
if ($method === 'POST') {
    rateLimit($db, $config, 'post', $config['limits']['rate_post_per_min'], 60);
}

// ────────────────────────────────────────────────────────────────────
// Unauthed: the landing page at /, the sign-in gate at /login, the Google
// callback, and /terms. Everything else bounces to /login.
// ────────────────────────────────────────────────────────────────────
if (!$user) {
    if ($method === 'GET' && $path === '/terms') {
        renderTermsPublic();
        exit;
    }
    if ($method === 'GET' && $path === '/') {
        renderLanding();
        exit;
    }
    // A join link opened by someone not signed in. Hold the token across the Google round-trip
    // and redeem it on the way back — nothing is spent until we know who is spending it.
    if ($method === 'GET' && $path === '/join') {
        $_SESSION['pending_invite'] = trim((string)($_GET['t'] ?? ''));
        redirect('/login');
    }
    if ($method === 'GET' && $path === '/login') {
        renderSignIn();
        exit;
    }
    if ($method === 'POST' && $path === '/signin') {
        rateLimit($db, $config, 'signin', $config['limits']['rate_signin_per_15min'], 900);

        // Dev-mode stub: only reachable while google_client_id is the placeholder AND APP_DEBUG=1.
        // The APP_DEBUG requirement means a prod deploy that forgets to set GOOGLE_CLIENT_ID
        // can't accidentally leave dev sign-in live.
        if (!empty($config['debug']) && isDevStubActive(GOOGLE_CLIENT_ID) && !empty($_POST['dev'])) {
            $devSub = 'dev-local-user';
            $stmt = $db->prepare("SELECT id FROM users WHERE google_sub = ?");
            $stmt->execute([$devSub]);
            $uid = $stmt->fetchColumn() ?: bootstrapHousehold($db, 'You', 'you@localhost', $devSub);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$uid;
            redirect(afterSignIn($db, (int)$uid));
        }

        // Google's own double-submit CSRF: g_csrf_token cookie must equal the POST field.
        $cookie = $_COOKIE['g_csrf_token'] ?? '';
        $body   = (string)($_POST['g_csrf_token'] ?? '');
        if ($cookie === '' || !hash_equals($cookie, $body)) {
            http_response_code(400); exit('CSRF token mismatch.');
        }

        $payload = verifyGoogleIdToken((string)($_POST['credential'] ?? ''), GOOGLE_CLIENT_ID);
        if (!$payload) {
            http_response_code(401); exit('Google sign-in failed.');
        }

        $stmt = $db->prepare("SELECT id FROM users WHERE google_sub = ?");
        $stmt->execute([$payload['sub']]);
        $uid = $stmt->fetchColumn();
        if (!$uid) {
            $uid = bootstrapHousehold(
                $db,
                (string)($payload['name']  ?? 'User'),
                (string)($payload['email'] ?? ''),
                (string)$payload['sub']
            );
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$uid;
        redirect(afterSignIn($db, (int)$uid));
    }
    redirect('/login');
}

// `users.household_id` is the ledger this person is currently looking at — a cache of a fact
// that can change under them, since an owner may remove someone between two clicks. Re-ask on
// every request, and if the answer is "you are not in that one any more", move them to one
// they are in rather than showing an empty ledger or, worse, someone else's.
$uid    = (int)$user['id'];
$hid    = (int)$user['household_id'];
$active = activeLedger($db, $hid, $uid);
$role   = $active['role'] ?? null;
$ledgerName = (string)($active['name'] ?? '');
if ($role === null) {
    $mine = ledgersFor($db, $uid);
    if ($mine) {
        $hid        = (int)$mine[0]['id'];
        $role       = (string)$mine[0]['role'];
        $ledgerName = (string)$mine[0]['name'];
        $db->prepare("UPDATE users SET household_id = ? WHERE id = ?")->execute([$hid, $uid]);
    } else {
        // No memberships at all — a row that predates v12 and slipped past the backfill.
        // They were the only user of this ledger, so they own it. Self-healing beats a 500.
        $db->prepare("INSERT INTO household_users (household_id, user_id, role) VALUES (?, ?, ?)")
           ->execute([$hid, $uid, ROLE_OWNER]);
        $role       = ROLE_OWNER;
        $ledgerName = (string)(activeLedger($db, $hid, $uid)['name'] ?? '');
    }
}
// Both keys ride along on $user, which every render function and every handler already
// receives — that is why none of their signatures had to change to learn about sharing.
$user['household_id']   = $hid;
$user['role']           = $role;
$user['household_name'] = $ledgerName;
// The header switcher needs to know how many there are before it can decide whether tapping
// it should toggle or ask. One small query on a table that holds a handful of rows per person;
// /ledgers reuses this rather than asking again.
$user['ledgers']        = ledgersFor($db, $uid);

// Both come off the ledger, so switching ledgers switches how its money reads. activeLedger()
// already fetched them; there is no second query and no per-user copy to fall out of step.
$_SESSION['currency'] = (string)($active['currency'] ?? '₹');
$_SESSION['numfmt']   = (string)($active['number_format'] ?? 'indian');
// Catch-up posting. Runs on every authed request, which on Android means "every time the app
// is opened" — a phone that was off for a month posts the month's missed periods on launch and
// then stops. That is why the Android build needs no cron and no WorkManager for this.
sweepRecurring($db, $hid);

// ────────────────────────────────────────────────────────────────────
// Sharing off (Android): minting and redeeming a join link is the part that genuinely cannot
// work on a local-only ledger — there is no second device to hand the link to and no server
// in the middle. Blocked here rather than only hidden in the markup, so the routes are not
// reachable by typing the URL.
//
// Members are NOT blocked: a spender label is a label, not a login, and a solo household
// still wants one per person. Nor is /ledgers — with one household the switcher simply shows
// one row, and renaming it still works.
// ────────────────────────────────────────────────────────────────────
if (!$config['features']['sharing']
    && ($path === '/join' || $path === '/invite' || str_starts_with($path, '/invite/'))) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Not found.\n");
}

// ────────────────────────────────────────────────────────────────────
// Authed POST actions — PRG pattern, CSRF-checked, limit-checked.
// UserErr thrown by validators surfaces as an error toast on the next page.
// ────────────────────────────────────────────────────────────────────
$L = $config['limits'];

if ($method === 'POST') {
    try {
        // All state-changing POSTs share one CSRF token.
        csrfCheck();

        switch ($path) {
            case '/signout':
                $_SESSION = []; session_destroy();
                redirect('/login');

            case '/theme':
                // The page that posts this has already repainted itself — every palette ships
                // in its CSS, so switching is two attributes on <html>. All that is left is to
                // remember the choice for the next page, which is why the reply is an empty
                // 204 nobody reads. Absent or unknown values keep what the user already had,
                // so a half-filled post can never silently flip somebody to light Organic.
                $key  = isset(THEMES[(string)($_POST['palette'] ?? '')])
                        ? (string)$_POST['palette'] : themeKey($user);
                $dark = match ($_POST['mode'] ?? '') { 'dark' => 1, 'light' => 0, default => (int)$user['is_dark'] };
                $db->prepare("UPDATE users SET theme = ?, is_dark = ? WHERE id = ?")
                   ->execute([$key, $dark, $user['id']]);
                if (isset($_POST['back'])) redirect((string)$_POST['back']);
                http_response_code(204);
                exit;

            case '/number-format':
                // The allow-list is the same constant the view builds its buttons from, so a
                // style the form offers and one the server accepts cannot drift apart.
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can change this.');
                $style = in_array($_POST['style'] ?? '', NUM_STYLES, true) ? $_POST['style'] : 'indian';
                $db->prepare("UPDATE households SET number_format = ? WHERE id = ?")->execute([$style, $hid]);
                $_SESSION['numfmt'] = $style;
                flash('success', $style === 'world' ? 'Grouping in thousands' : 'Grouping in lakh and crore');
                redirect($_POST['back'] ?? '/');

            case '/currency':
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can change this.');
                $sym = parseCurrency((string)($_POST['symbol'] ?? ''));
                $db->prepare("UPDATE households SET currency = ? WHERE id = ?")->execute([$sym, $hid]);
                $_SESSION['currency'] = $sym;
                flash('success', 'Currency updated');
                redirect($_POST['back'] ?? '/');

            // ── Sharing ────────────────────────────────────────────────
            // These five write `household_users`, `invites` and `households`, none of which
            // carry a household_id-scoped row the way an entry does. They scope by membership
            // instead — `switchLedger` and the `$role` check below are the equivalent guard.
            case '/ledgers/switch':
                if (!switchLedger($db, $uid, (int)($_POST['household_id'] ?? 0))) {
                    throw new UserErr('That ledger is not yours to open.');
                }
                // Where you land depends on which button you pressed: the row keeps you on
                // /ledgers with the settings for the ledger you just picked, Open takes you
                // into it. safeRedirectTarget() still refuses anything that is not a local path.
                redirect($_POST['back'] ?? '/');

            case '/ledgers/rename':
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can rename it.');
                $nm = requireStr((string)($_POST['name'] ?? ''), 80, 'Ledger name');
                $db->prepare("UPDATE households SET name = ? WHERE id = ?")->execute([$nm, $hid]);
                flash('success', 'Ledger renamed');
                redirect($_POST['back'] ?? '/');

            case '/ledgers/leave':
                if ($role === ROLE_OWNER) {
                    throw new UserErr('You own this ledger — it cannot be left, only renamed or emptied.');
                }
                $db->prepare("DELETE FROM household_users WHERE household_id = ? AND user_id = ?")
                   ->execute([$hid, $uid]);
                // Their entries stay: the household paid for them, and deleting a departing
                // member's history would silently rewrite everyone else's totals.
                flash('success', 'You have left that ledger.');
                redirect('/');

            case '/invite':
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can invite people.');
                $people = $db->prepare("SELECT COUNT(*) FROM household_users WHERE household_id = ?");
                $people->execute([$hid]);
                if ((int)$people->fetchColumn() >= HOUSEHOLD_USERS_MAX) {
                    throw new UserErr('This ledger is full — ' . HOUSEHOLD_USERS_MAX . ' people is the limit.');
                }
                mintInvite($db, $hid, $uid);
                flash('success', 'Invite link ready — it works once, for the next '
                    . INVITE_TTL_MINUTES . ' minutes.');
                redirect($_POST['back'] ?? '/');

            case '/invite/revoke':
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can revoke invites.');
                $db->prepare("DELETE FROM invites WHERE household_id = ? AND used_at IS NULL")->execute([$hid]);
                flash('success', 'Invite link cancelled');
                redirect($_POST['back'] ?? '/');

            case '/household-users/remove':
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can remove people.');
                // `id`, not `user_id` — the shared confirm dialog posts one fixed field name,
                // and bending it for this one caller would break every other use of it.
                $drop = (int)($_POST['id'] ?? 0);
                if ($drop === $uid) throw new UserErr('You cannot remove yourself from a ledger you own.');
                $db->prepare("DELETE FROM household_users WHERE household_id = ? AND user_id = ? AND role <> ?")
                   ->execute([$hid, $drop, ROLE_OWNER]);
                // The spender label outlives the login, so past entries keep saying who spent.
                $db->prepare("UPDATE members SET user_id = NULL WHERE household_id = ? AND user_id = ?")
                   ->execute([$hid, $drop]);
                flash('success', 'Removed from this ledger');
                redirect($_POST['back'] ?? '/');

            case '/expenses':
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $note = optionalStr($_POST['note'] ?? '', $L['note_len_max'], 'Note');
                $catId = ownedId($db, 'categories', $hid, (int)($_POST['category_id'] ?? 0));
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0));
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM expenses WHERE household_id = ? AND date = ?",
                    [$hid, $date],
                    $L['expenses_per_day_max'],
                    'Daily expenses'
                );
                $db->prepare(
                    "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $amt, $catId, $memId, $note, $date, $uid]);
                flash('success', 'Expense added');
                redirect('/');

            case '/expenses/delete':
                requireEditable($db, 'expenses', $hid, (int)$_POST['id'], $uid, $role);
                $db->prepare("DELETE FROM expenses WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Expense deleted');
                redirect($_POST['back'] ?? '/history');

            case '/expenses/update':
                $id    = (int)($_POST['id'] ?? 0);
                $row = requireEditable($db, 'expenses', $hid, $id, $uid, $role);
                $amt   = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $date  = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $note  = optionalStr($_POST['note'] ?? '', $L['note_len_max'], 'Note');
                $catId = ownedId($db, 'categories', $hid, (int)($_POST['category_id'] ?? 0));
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                          $row['member_id'] === null ? null : (int)$row['member_id']);
                $db->prepare(
                    "UPDATE expenses SET amount = ?, category_id = ?, member_id = ?, note = ?, date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$amt, $catId, $memId, $note, $date, $id, $hid]);
                flash('success', 'Expense updated');
                redirect($_POST['back'] ?? '/history');

            case '/investments':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $type = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0));
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM investments WHERE household_id = ?",
                    [$hid],
                    $L['investments_total_max'],
                    'Investments'
                );
                $db->prepare(
                    "INSERT INTO investments (household_id, name, amount, type, member_id, date, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $type, $memId, $date, $uid]);
                flash('success', 'Investment saved');
                redirect('/invest');

            case '/investments/delete':
                requireEditable($db, 'investments', $hid, (int)$_POST['id'], $uid, $role);
                $db->prepare("DELETE FROM investments WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Investment deleted');
                redirect($_POST['back'] ?? '/invest');

            case '/investments/update':
                $id   = (int)($_POST['id'] ?? 0);
                $row = requireEditable($db, 'investments', $hid, $id, $uid, $role);
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $type = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                          $row['member_id'] === null ? null : (int)$row['member_id']);
                $db->prepare(
                    "UPDATE investments SET name = ?, amount = ?, type = ?, member_id = ?, date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $type, $memId, $date, $id, $hid]);
                flash('success', 'Investment updated');
                redirect($_POST['back'] ?? '/invest');

            case '/earnings':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $cat  = ownedId($db, 'earning_categories', $hid, (int)($_POST['category_id'] ?? 0));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0));
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM earnings WHERE household_id = ?",
                    [$hid],
                    $L['earnings_total_max'],
                    'Earnings'
                );
                $db->prepare(
                    "INSERT INTO earnings (household_id, name, amount, category_id, member_id, date, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $cat, $memId, $date, $uid]);
                flash('success', 'Earning saved');
                redirect('/earn');

            case '/earnings/delete':
                requireEditable($db, 'earnings', $hid, (int)$_POST['id'], $uid, $role);
                $db->prepare("DELETE FROM earnings WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Earning deleted');
                redirect($_POST['back'] ?? '/earn');

            case '/earnings/update':
                $id   = (int)($_POST['id'] ?? 0);
                $row = requireEditable($db, 'earnings', $hid, $id, $uid, $role);
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $cat  = ownedId($db, 'earning_categories', $hid, (int)($_POST['category_id'] ?? 0));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                          $row['member_id'] === null ? null : (int)$row['member_id']);
                $db->prepare(
                    "UPDATE earnings SET name = ?, amount = ?, category_id = ?, member_id = ?, date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $cat, $memId, $date, $id, $hid]);
                flash('success', 'Earning updated');
                redirect($_POST['back'] ?? '/earn');

            case '/earning-categories':
                $name = requireStr((string)($_POST['name'] ?? ''), 50, 'Earning category');
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM earning_categories WHERE household_id = ?",
                    [$hid],
                    $L['categories_total_max'],
                    'Earning categories'
                );
                $db->prepare("INSERT INTO earning_categories (household_id, name) VALUES (?, ?)")
                   ->execute([$hid, $name]);
                flash('success', 'Earning category added');
                redirect($_POST['back'] ?? '/earn');

            case '/earning-categories/update':
                $id   = (int)($_POST['id'] ?? 0);
                $name = requireStr((string)($_POST['name'] ?? ''), 50, 'Earning category');
                $db->prepare("UPDATE earning_categories SET name = ? WHERE id = ? AND household_id = ?")
                   ->execute([$name, $id, $hid]);
                flash('success', 'Earning category saved');
                redirect($_POST['back'] ?? '/earn');

            case '/earning-categories/delete':
                // The add form picks from this list, so emptying it would lock earnings out.
                $countStmt = $db->prepare("SELECT COUNT(*) FROM earning_categories WHERE household_id = ?");
                $countStmt->execute([$hid]);
                if ((int)$countStmt->fetchColumn() <= 1) {
                    flash('error', 'Keep at least one earning category.');
                    redirect($_POST['back'] ?? '/earn');
                }
                // Past earnings keep their amount and date and fall back to "Uncategorised" —
                // same as deleting an expense category.
                $db->prepare("DELETE FROM earning_categories WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Earning category removed');
                redirect($_POST['back'] ?? '/earn');

            case '/recurring':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $kind = in_array($_POST['kind'] ?? '', ['expense','investment','earning'], true) ? $_POST['kind'] : 'expense';
                $freq = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                        ? $_POST['frequency'] : 'monthly';
                $date = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                // One `category_id` column, read against the table the kind implies — so it is
                // re-validated here on every save, and switching kind can never carry an
                // expense category id over into an earning (or the reverse).
                $type = null;
                if ($kind === 'investment') {
                    $catId = null;
                    $type  = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                } else {
                    $catTable = $kind === 'earning' ? 'earning_categories' : 'categories';
                    $catId = ownedId($db, $catTable, $hid, (int)($_POST['category_id'] ?? 0));
                }
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM recurring WHERE household_id = ?",
                    [$hid],
                    $L['recurring_total_max'],
                    'Recurring items'
                );
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0));
                $db->prepare(
                    "INSERT INTO recurring (household_id, name, amount, kind, category_id, type, member_id, frequency, next_date, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $kind, $catId, $type, $memId, $freq, $date, $uid]);
                flash('success', 'Recurring item saved');
                redirect('/recurring');

            // A bill paid once but used over months — insurance for the year, a domain for two.
            // Stored as an ordinary monthly expense item with an end_date, so the existing sweep
            // back-fills every month from the payment date to today and then stops on its own.
            case '/recurring/split':
                $name  = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $total = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $start = requireDate((string)($_POST['start_date'] ?? today()), 'Paid on');
                [$per, $end] = splitPlan($total, (int)($_POST['months'] ?? 0), $start);
                $catId = ownedId($db, 'categories', $hid, (int)($_POST['category_id'] ?? 0));
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM recurring WHERE household_id = ?",
                    [$hid],
                    $L['recurring_total_max'],
                    'Recurring items'
                );
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0));
                $db->prepare(
                    "INSERT INTO recurring (household_id, name, amount, kind, category_id, type, member_id, frequency, next_date, start_date, end_date, total_amount, created_by)
                     VALUES (?, ?, ?, 'expense', ?, NULL, ?, 'monthly', ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $per, $catId, $memId, $start, $start, $end, $total, $uid]);
                flash('success', 'Split into ' . (int)$_POST['months'] . ' × ' . fmt($per)
                    . ' — last on ' . (new DateTimeImmutable($end))->format('M j, Y'));
                redirect('/recurring');

            // Editing a split is not editing a row — it is restating the whole plan. A wrong
            // total or start date has already been posted into History as monthly shares, so
            // the shares have to be thrown away and recomputed. Anything hand-edited on one of
            // those auto-posted rows is lost with them; that is what the dialog warns about.
            case '/recurring/split/update':
                $rid  = (int)($_POST['id'] ?? 0);
                $prev = requireEditable($db, 'recurring', $hid, $rid, $uid, $role);
                if ($prev['end_date'] === null) {
                    throw new UserErr('That is a repeating item, not a split — edit it from its own dialog.');
                }
                $name  = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $total = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $start = requireDate((string)($_POST['start_date'] ?? today()), 'Paid on');
                $mths  = (int)($_POST['months'] ?? 0);
                [$per, $end] = splitPlan($total, $mths, $start);
                $catId = ownedId($db, 'categories', $hid, (int)($_POST['category_id'] ?? 0));
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                          $prev['member_id'] === null ? null : (int)$prev['member_id']);
                $db->beginTransaction();
                try {
                    // Only this item's own postings, and only in this household.
                    $del = $db->prepare("DELETE FROM expenses WHERE household_id = ? AND recurring_id = ?");
                    $del->execute([$hid, $rid]);
                    $wiped = $del->rowCount();
                    // next_date returns to the start so the sweep replays the whole plan from
                    // the beginning; start_date is what lets this dialog be opened again later.
                    $db->prepare(
                        "UPDATE recurring SET name = ?, amount = ?, category_id = ?, member_id = ?,
                                kind = 'expense', frequency = 'monthly',
                                next_date = ?, start_date = ?, end_date = ?, total_amount = ?
                         WHERE id = ? AND household_id = ?"
                    )->execute([$name, $per, $catId, $memId, $start, $start, $end, $total, $rid, $hid]);
                    $db->commit();
                } catch (Throwable $e) { $db->rollBack(); throw $e; }
                // Re-post immediately rather than waiting for the next request's sweep, so the
                // History tab is already correct when the redirect lands.
                sweepRecurring($db, $hid);
                flash('success', 'Split updated — ' . $mths . ' × ' . fmt($per)
                    . ($wiped ? ', ' . $wiped . ' posted ' . ($wiped === 1 ? 'entry' : 'entries') . ' recalculated' : ''));
                redirect('/recurring');

            case '/recurring/delete':
                $rid = (int)($_POST['id'] ?? 0);
                requireEditable($db, 'recurring', $hid, $rid, $uid, $role);
                $cascade = !empty($_POST['cascade']);
                $db->beginTransaction();
                try {
                    $pastDeleted = 0;
                    if ($cascade) {
                        // Sweep all three ledgers, not just the one matching the current kind —
                        // an item edited from expense to earning has posted rows in both.
                        foreach (['expenses', 'investments', 'earnings'] as $t) {
                            $del = $db->prepare("DELETE FROM $t WHERE household_id = ? AND recurring_id = ?");
                            $del->execute([$hid, $rid]);
                            $pastDeleted += $del->rowCount();
                        }
                    }
                    $db->prepare("DELETE FROM recurring WHERE id = ? AND household_id = ?")
                       ->execute([$rid, $hid]);
                    $db->commit();
                } catch (Throwable $e) { $db->rollBack(); throw $e; }
                flash('success', $cascade
                    ? "Recurring item deleted (plus {$pastDeleted} past entry(ies))"
                    : 'Recurring item deleted');
                redirect('/recurring');

            case '/recurring/update':
                $id    = (int)($_POST['id'] ?? 0);
                $row = requireEditable($db, 'recurring', $hid, $id, $uid, $role);
                $name  = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt   = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $kind  = in_array($_POST['kind'] ?? '', ['expense','investment','earning'], true) ? $_POST['kind'] : 'expense';
                $freq  = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                         ? $_POST['frequency'] : 'monthly';
                $date  = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                // One `category_id` column, read against the table the kind implies — so it is
                // re-validated here on every save, and switching kind can never carry an
                // expense category id over into an earning (or the reverse).
                $type = null;
                if ($kind === 'investment') {
                    $catId = null;
                    $type  = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                } else {
                    $catTable = $kind === 'earning' ? 'earning_categories' : 'categories';
                    $catId = ownedId($db, $catTable, $hid, (int)($_POST['category_id'] ?? 0));
                }
                $memId = attributableMember($db, $hid, $uid, $role, (int)($_POST['member_id'] ?? 0),
                                          $row['member_id'] === null ? null : (int)$row['member_id']);
                $db->prepare(
                    "UPDATE recurring SET name = ?, amount = ?, kind = ?, category_id = ?, type = ?, member_id = ?, frequency = ?, next_date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $kind, $catId, $type, $memId, $freq, $date, $id, $hid]);
                flash('success', 'Recurring item updated');
                redirect('/recurring');

            case '/categories':
                $name   = requireStr((string)($_POST['name'] ?? ''), 50, 'Category');
                $budget = parseBudget((string)($_POST['budget'] ?? ''), $config);
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM categories WHERE household_id = ?",
                    [$hid],
                    $L['categories_total_max'],
                    'Categories'
                );
                $db->prepare("INSERT INTO categories (household_id, name, icon, is_custom, budget) VALUES (?, ?, 'tag', 1, ?)")
                   ->execute([$hid, $name, $budget]);
                flash('success', 'Category added');
                redirect($_POST['back'] ?? '/');

            case '/categories/update':
                $id     = (int)($_POST['id'] ?? 0);
                $name   = requireStr((string)($_POST['name'] ?? ''), 50, 'Category');
                $budget = parseBudget((string)($_POST['budget'] ?? ''), $config);
                $db->prepare("UPDATE categories SET name = ?, budget = ? WHERE id = ? AND household_id = ?")
                   ->execute([$name, $budget, $id, $hid]);
                flash('success', 'Category saved');
                redirect($_POST['back'] ?? '/');

            case '/categories/delete':
                $id = (int)$_POST['id'];
                $db->beginTransaction();
                try {
                    // Children outlive their parent as top-level categories. Leaving them
                    // pointing at a deleted row would strand their spend in a bar with no name.
                    $db->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ? AND household_id = ?")
                       ->execute([$id, $hid]);
                    $db->prepare("DELETE FROM categories WHERE id = ? AND household_id = ? AND is_custom = 1")
                       ->execute([$id, $hid]);
                    $db->commit();
                } catch (Throwable $e) { $db->rollBack(); throw $e; }
                flash('success', 'Category removed');
                redirect($_POST['back'] ?? '/');

            case '/categories/uncategorised/delete':
                // Deletes the expenses themselves — the only irreversible bulk action in the
                // app, so the UI gates it behind a confirmation carrying the exact count.
                $delU = $db->prepare("DELETE FROM expenses WHERE " . uncategorisedWhere());
                $delU->execute([$hid, $hid]);
                $nU = $delU->rowCount();
                flash('success', $nU === 0
                    ? 'Nothing uncategorised to delete'
                    : "Deleted $nU uncategorised " . ($nU === 1 ? 'expense' : 'expenses'));
                redirect($_POST['back'] ?? '/organise');

            case '/categories/parent':
                // Assign or clear a category's parent. One level only, so the target must be
                // top-level and the category being moved must not have children of its own.
                $id     = (int)($_POST['id'] ?? 0);
                $parent = (int)($_POST['parent_id'] ?? 0);
                if (!ownedId($db, 'categories', $hid, $id)) throw new UserErr('Unknown category.');

                $kids = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ? AND household_id = ?");
                $kids->execute([$id, $hid]);
                if ($parent && (int)$kids->fetchColumn() > 0) {
                    throw new UserErr('This category already has sub-categories, so it cannot become one itself.');
                }
                if ($parent) {
                    if ($parent === $id) throw new UserErr('A category cannot be its own parent.');
                    $p = $db->prepare("SELECT parent_id FROM categories WHERE id = ? AND household_id = ?");
                    $p->execute([$parent, $hid]);
                    $row = $p->fetch();
                    if (!$row) throw new UserErr('Unknown parent category.');
                    if (!empty($row['parent_id'])) throw new UserErr('Sub-categories only go one level deep — pick a top-level category.');
                }

                // A child never carries its own budget, or the household total counts it twice.
                $cur = $db->prepare("SELECT name, budget FROM categories WHERE id = ? AND household_id = ?");
                $cur->execute([$id, $hid]);
                $me = $cur->fetch();
                $clearing = $parent && (float)$me['budget'] > 0;
                $db->prepare("UPDATE categories SET parent_id = ?, budget = ? WHERE id = ? AND household_id = ?")
                   ->execute([$parent ?: null, $parent ? 0 : (float)$me['budget'], $id, $hid]);
                flash('success', $parent
                    ? $me['name'] . ' is now a sub-category'
                      . ($clearing ? ' — its ' . fmt((float)$me['budget']) . ' budget was cleared, the parent budget now covers it' : '')
                    : $me['name'] . ' moved back to top level');
                redirect($_POST['back'] ?? '/organise');

            case '/categories/move':
                // Bulk re-categorise: every expense in $from becomes $to. Recurring items go
                // with them, otherwise the next sweep posts straight back into the old category.
                // from_id 0 is the pseudo-category "Uncategorised" — the one way to file entries
                // that have no category, or whose category was deleted out from under them.
                $fromRaw = (int)($_POST['from_id'] ?? 0);
                $to      = ownedId($db, 'categories', $hid, (int)($_POST['to_id'] ?? 0));
                if (!$to) throw new UserErr('Pick a category to move into.');
                $db->beginTransaction();
                try {
                    if ($fromRaw === 0) {
                        $movE = $db->prepare("UPDATE expenses SET category_id = ? WHERE " . uncategorisedWhere());
                        $movE->execute([$to, $hid, $hid]);
                        $nE = $movE->rowCount(); $nR = 0;
                    } else {
                        $from = ownedId($db, 'categories', $hid, $fromRaw);
                        if (!$from)        throw new UserErr('Unknown category.');
                        if ($from === $to) throw new UserErr('Pick two different categories.');
                        $movE = $db->prepare("UPDATE expenses SET category_id = ? WHERE household_id = ? AND category_id = ?");
                        $movE->execute([$to, $hid, $from]);
                        $movR = $db->prepare("UPDATE recurring SET category_id = ? WHERE household_id = ? AND category_id = ? AND kind = 'expense'");
                        $movR->execute([$to, $hid, $from]);
                        $nE = $movE->rowCount(); $nR = $movR->rowCount();
                    }
                    $db->commit();
                } catch (Throwable $t) { $db->rollBack(); throw $t; }
                flash('success', $nE === 0 && $nR === 0
                    ? 'Nothing to move'
                    : "Moved $nE " . ($nE === 1 ? 'expense' : 'expenses')
                      . ($nR ? " and $nR recurring " . ($nR === 1 ? 'item' : 'items') : ''));
                redirect($_POST['back'] ?? '/organise');

            case '/investment-types':
                $name = requireStr((string)($_POST['name'] ?? ''), 40, 'Investment type');
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM investment_types WHERE household_id = ?",
                    [$hid],
                    30,
                    'Investment types'
                );
                $db->prepare("INSERT INTO investment_types (household_id, name) VALUES (?, ?)")
                   ->execute([$hid, $name]);
                flash('success', 'Investment type added');
                redirect($_POST['back'] ?? '/');

            case '/investment-types/update':
                $id      = (int)($_POST['id'] ?? 0);
                $newName = requireStr((string)($_POST['name'] ?? ''), 40, 'Investment type');
                // Fetch the old name so we can cascade-rename existing investments.
                $old = $db->prepare("SELECT name FROM investment_types WHERE id = ? AND household_id = ?");
                $old->execute([$id, $hid]);
                $oldName = (string)$old->fetchColumn();
                if ($oldName !== '' && $oldName !== $newName) {
                    $db->beginTransaction();
                    try {
                        $db->prepare("UPDATE investment_types SET name = ? WHERE id = ? AND household_id = ?")
                           ->execute([$newName, $id, $hid]);
                        $db->prepare("UPDATE investments SET type = ? WHERE household_id = ? AND type = ?")
                           ->execute([$newName, $hid, $oldName]);
                        $db->commit();
                    } catch (Throwable $e) { $db->rollBack(); throw $e; }
                }
                flash('success', 'Investment type renamed');
                redirect($_POST['back'] ?? '/');

            case '/investment-types/archive':
                // Toggle. Archiving hides a type's investments from the active view and
                // removes it from the "add" pickers; existing entries are untouched.
                $id = (int)($_POST['id'] ?? 0);
                $db->prepare("UPDATE investment_types SET archived = 1 - archived WHERE id = ? AND household_id = ?")
                   ->execute([$id, $hid]);
                $now = $db->prepare("SELECT archived FROM investment_types WHERE id = ? AND household_id = ?");
                $now->execute([$id, $hid]);
                flash('success', $now->fetchColumn() ? 'Type archived' : 'Type restored');
                redirect($_POST['back'] ?? '/invest');

            case '/investment-types/delete':
                $id = (int)($_POST['id'] ?? 0);
                $countStmt = $db->prepare("SELECT COUNT(*) FROM investment_types WHERE household_id = ?");
                $countStmt->execute([$hid]);
                if ((int)$countStmt->fetchColumn() <= 1) {
                    flash('error', 'Keep at least one investment type.');
                    redirect($_POST['back'] ?? '/');
                }
                // Refuse if any investment still uses this type — silent deletion would surprise the user.
                $nameStmt = $db->prepare("SELECT name FROM investment_types WHERE id = ? AND household_id = ?");
                $nameStmt->execute([$id, $hid]);
                $typeName = (string)$nameStmt->fetchColumn();
                if ($typeName !== '') {
                    $useStmt = $db->prepare("SELECT COUNT(*) FROM investments WHERE household_id = ? AND type = ?");
                    $useStmt->execute([$hid, $typeName]);
                    $inUse = (int)$useStmt->fetchColumn();
                    if ($inUse > 0) {
                        flash('error', "Cannot delete — {$inUse} investment(s) still use '{$typeName}'. Change or delete them first.");
                        redirect($_POST['back'] ?? '/');
                    }
                }
                $db->prepare("DELETE FROM investment_types WHERE id = ? AND household_id = ?")
                   ->execute([$id, $hid]);
                flash('success', 'Investment type removed');
                redirect($_POST['back'] ?? '/');

            case '/members':
                $name = requireStr((string)($_POST['name'] ?? ''), 60, 'Member name');
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM members WHERE household_id = ?",
                    [$hid],
                    $L['members_total_max'],
                    'Members'
                );
                $db->prepare("INSERT INTO members (household_id, name) VALUES (?, ?)")
                   ->execute([$hid, $name]);
                flash('success', 'Member added');
                redirect($_POST['back'] ?? '/');

            case '/members/update':
                // A rename, not a replace. Entries hold `member_id`, so every expense, earning
                // and investment already filed under this person follows the new name on its
                // own — nothing is re-pointed, and no history is orphaned by a nickname.
                $mid = (int)($_POST['id'] ?? 0);
                $nm  = requireStr((string)($_POST['name'] ?? ''), 60, 'Name');
                $db->prepare("UPDATE members SET name = ? WHERE id = ? AND household_id = ?")
                   ->execute([$nm, $mid, $hid]);
                flash('success', 'Name updated');
                redirect($_POST['back'] ?? '/');

            case '/members/delete':
                // Adding a name is everyone's job; removing one rewrites what the whole
                // household sees on its filters, so it stays with the owner — and the UI
                // only offers the button to them, which this makes true rather than assumed.
                if ($role !== ROLE_OWNER) throw new UserErr('Only the ledger owner can remove a name.');
                $countStmt = $db->prepare("SELECT COUNT(*) FROM members WHERE household_id = ?");
                $countStmt->execute([$hid]);
                if ((int)$countStmt->fetchColumn() > 1) {
                    $db->prepare("DELETE FROM members WHERE id = ? AND household_id = ?")
                       ->execute([(int)$_POST['id'], $hid]);
                    flash('success', 'Member removed');
                } else {
                    flash('error', 'Cannot remove the last member.');
                }
                redirect($_POST['back'] ?? '/');

            default:
                http_response_code(404); exit('404');
        }
    } catch (UserErr $e) {
        flash('error', $e->getMessage());
        redirect($_POST['back'] ?? ($_SERVER['HTTP_REFERER'] ?? '/'));
    }
}

// ────────────────────────────────────────────────────────────────────
// Authed GET routes.
// ────────────────────────────────────────────────────────────────────
switch ($path) {
    case '/':
    case '/add':       renderAdd($db, $user); break;
    case '/history':   renderHistory($db, $user, (int)($_GET['m'] ?? 0)); break;
    case '/invest':    renderInvest($db, $user, isset($_GET['new']), (string)($_GET['f'] ?? 'active')); break;
    case '/earn':      renderEarn($db, $user, isset($_GET['new'])); break;
    case '/organise':  renderOrganise($db, $user); break;
    case '/recurring': renderRecurring($db, $user, isset($_GET['new'])); break;
    case '/year':      renderYear($db, $user, (int)($_GET['y'] ?? 0), (string)($_GET['mode'] ?? 'cal'), (string)($_GET['inv'] ?? 'all')); break;
    case '/terms':     renderTerms($db, $user); break;
    case '/ledgers':   renderLedgers($db, $user); break;
    // A join link opened while already signed in — redeem straight away, no detour. The token
    // goes through the session either way so there is one redemption path, not two.
    case '/join':
        $_SESSION['pending_invite'] = trim((string)($_GET['t'] ?? ''));
        redirect(afterSignIn($db, $uid));
    case '/login':     redirect('/');                   // already signed in
    case '/manage':    redirect('/#profile');           // legacy path
    default:           http_response_code(404); exit('404');
}
