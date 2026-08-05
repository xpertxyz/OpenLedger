<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require __DIR__ . '/lib.php';

define('CURRENCY',         $config['currency']);
define('GOOGLE_CLIENT_ID', $config['google_client_id']);

// ────────────────────────────────────────────────────────────────────
// CLI modes: --selfcheck (smoke tests), --cron (daily maintenance)
// ────────────────────────────────────────────────────────────────────
if (PHP_SAPI === 'cli') {
    $mode = $argv[1] ?? '';
    if ($mode === '--selfcheck') {
        assert(advanceDate('2026-01-15', 'monthly')   === '2026-02-15');
        assert(advanceDate('2026-01-15', 'quarterly') === '2026-04-15');
        assert(advanceDate('2026-01-15', 'yearly')    === '2027-01-15');
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
        try {
            assert(advanceDate('2026-01-15', 'monthly')   === '2026-02-15');
            assert(advanceDate('2026-01-15', 'quarterly') === '2026-04-15');
            assert(advanceDate('2026-01-15', 'yearly')    === '2027-01-15');
            try { parseAmount('-1', $config);   assert(false); } catch (UserErr) {}
            try { parseAmount('abc', $config);  assert(false); } catch (UserErr) {}
            try { parseAmount('1e9', $config);  assert(false); } catch (UserErr) {}
            assert(parseAmount('12.34', $config) === 12.34);
            assert(parseBudget('', $config) === 0.0 && parseBudget('5000', $config) === 5000.0);
            try { parseBudget('abc', $config); assert(false); } catch (UserErr) {}
            assert(investmentFilterSql('archived', []) === [' AND 1 = 0', []]);
            assert(investmentFilterSql('active', ['Gold']) === [' AND type NOT IN (?)', ['Gold']]);
            assert(groupIndian(1000000) === '10,00,000.00' && groupIndian(10000000) === '1,00,00,000.00');
            $line('OK', 'date math + parseAmount/parseBudget + investment filter + lakh/crore formatting');
        } catch (Throwable $e) { $line('FAIL', 'selfcheck: ' . $e->getMessage()); }

        echo "\nDatabase:\n";
        try {
            $db = makeDb($config);
            $line('OK', "connected to {$config['db']['host']}/{$config['db']['name']} as {$config['db']['user']}");
            $expected = ['households','users','members','categories','expenses','investments','recurring','rate_limits','investment_types'];
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $missing = array_diff($expected, $tables);
            if ($missing) $line('FAIL', 'missing tables: ' . implode(', ', $missing));
            else          $line('OK',   'all ' . count($expected) . ' tables present');
            $cols = $db->query("SHOW COLUMNS FROM recurring")->fetchAll(PDO::FETCH_COLUMN);
            foreach (['kind','type','category_id','frequency','next_date'] as $c) {
                in_array($c, $cols, true) ? $line('OK', "recurring.$c") : $line('FAIL', "recurring.$c missing");
            }
            $ecols = $db->query("SHOW COLUMNS FROM expenses")->fetchAll(PDO::FETCH_COLUMN);
            in_array('recurring_id', $ecols, true) ? $line('OK', 'expenses.recurring_id') : $line('FAIL', 'expenses.recurring_id missing');
            $icols = $db->query("SHOW COLUMNS FROM investments")->fetchAll(PDO::FETCH_COLUMN);
            in_array('recurring_id', $icols, true) ? $line('OK', 'investments.recurring_id') : $line('FAIL', 'investments.recurring_id missing');
            $ucols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            in_array('currency', $ucols, true) ? $line('OK', 'users.currency') : $line('FAIL', 'users.currency missing');
            $ccols = $db->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
            in_array('budget', $ccols, true) ? $line('OK', 'categories.budget') : $line('FAIL', 'categories.budget missing');
            $tcols = $db->query("SHOW COLUMNS FROM investment_types")->fetchAll(PDO::FETCH_COLUMN);
            in_array('archived', $tcols, true) ? $line('OK', 'investment_types.archived') : $line('FAIL', 'investment_types.archived missing');
            // The Invest tab paginates with ORDER BY date DESC — without this composite index it filesorts.
            $idx = $db->query("SHOW INDEX FROM investments")->fetchAll(PDO::FETCH_COLUMN, 2);
            in_array('ix_household_date', $idx, true)
                ? $line('OK',   'investments.ix_household_date')
                : $line('FAIL', 'investments.ix_household_date missing — list queries will filesort');
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
            $sentinels = glob($dataDir . '/.schema-ok-*') ?: [];
            $line('OK', 'data/ writable' . ($sentinels ? ' (schema sentinel: ' . basename(end($sentinels)) . ')' : ', no sentinel yet — migrations run on first request'));
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
        $hids = $db->query(
            "SELECT DISTINCT household_id FROM recurring WHERE next_date <= CURDATE()"
        )->fetchAll(PDO::FETCH_COLUMN);
        foreach ($hids as $hid) sweepRecurring($db, (int)$hid);
        $db->exec("DELETE FROM rate_limits WHERE window_end < UNIX_TIMESTAMP() - 3600");
        echo "swept " . count($hids) . " households\n"; exit;
    }
    fwrite(STDERR, "usage: php index.php --selfcheck | --cron\n"); exit(1);
}

// Debug output — surface fatals + warnings in the browser response.
// Only for troubleshooting; production must stay off.
if (!empty($config['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ────────────────────────────────────────────────────────────────────
// Session hardening — before session_start(), so cookie flags land.
// ────────────────────────────────────────────────────────────────────
session_name($config['session_name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
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

$path   = parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
$user   = currentUser($db);

// Every request gets a light global limiter to blunt scanners; the per-endpoint
// limits below are the real controls.
if ($method === 'POST') {
    rateLimit($db, $config, 'post', $config['limits']['rate_post_per_min'], 60);
}

// ────────────────────────────────────────────────────────────────────
// Unauthed: only the sign-in gate, Google callback, and /terms are reachable.
// ────────────────────────────────────────────────────────────────────
if (!$user) {
    if ($method === 'GET' && $path === '/terms') {
        renderTermsPublic();
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
            redirect('/');
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
        redirect('/');
    }
    renderSignIn();
    exit;
}

$hid = (int)$user['household_id'];
$_SESSION['currency'] = $user['currency'] ?? '₹';
sweepRecurring($db, $hid);

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
                redirect('/');

            case '/theme':
                $db->prepare("UPDATE users SET is_dark = 1 - is_dark WHERE id = ?")->execute([$user['id']]);
                redirect($_POST['back'] ?? '/');

            case '/currency':
                $sym = requireStr((string)($_POST['symbol'] ?? ''), 8, 'Currency');
                $db->prepare("UPDATE users SET currency = ? WHERE id = ?")->execute([$sym, $user['id']]);
                $_SESSION['currency'] = $sym;
                flash('success', 'Currency updated');
                redirect($_POST['back'] ?? '/');

            case '/expenses':
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $note = optionalStr($_POST['note'] ?? '', $L['note_len_max'], 'Note');
                $catId = (int)($_POST['category_id'] ?? 0);
                $memId = (int)($_POST['member_id'] ?? 0);
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM expenses WHERE household_id = ? AND date = ?",
                    [$hid, $date],
                    $L['expenses_per_day_max'],
                    'Daily expenses'
                );
                $db->prepare(
                    "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date)
                     VALUES (?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $amt, $catId ?: null, $memId ?: null, $note, $date]);
                flash('success', 'Expense added');
                redirect('/');

            case '/expenses/delete':
                $db->prepare("DELETE FROM expenses WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Expense deleted');
                redirect($_POST['back'] ?? '/history');

            case '/expenses/update':
                $id    = (int)($_POST['id'] ?? 0);
                $amt   = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $date  = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $note  = optionalStr($_POST['note'] ?? '', $L['note_len_max'], 'Note');
                $catId = (int)($_POST['category_id'] ?? 0);
                $memId = (int)($_POST['member_id'] ?? 0);
                $db->prepare(
                    "UPDATE expenses SET amount = ?, category_id = ?, member_id = ?, note = ?, date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$amt, $catId ?: null, $memId ?: null, $note, $date, $id, $hid]);
                flash('success', 'Expense updated');
                redirect($_POST['back'] ?? '/history');

            case '/investments':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $type = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM investments WHERE household_id = ?",
                    [$hid],
                    $L['investments_total_max'],
                    'Investments'
                );
                $db->prepare(
                    "INSERT INTO investments (household_id, name, amount, type, date)
                     VALUES (?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $type, $date]);
                flash('success', 'Investment saved');
                redirect('/invest');

            case '/investments/delete':
                $db->prepare("DELETE FROM investments WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Investment deleted');
                redirect($_POST['back'] ?? '/invest');

            case '/investments/update':
                $id   = (int)($_POST['id'] ?? 0);
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $type = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                $date = requireDate((string)($_POST['date'] ?? today()), 'Date');
                $db->prepare(
                    "UPDATE investments SET name = ?, amount = ?, type = ?, date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $type, $date, $id, $hid]);
                flash('success', 'Investment updated');
                redirect($_POST['back'] ?? '/invest');

            case '/recurring':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $kind = in_array($_POST['kind'] ?? '', ['expense','investment'], true) ? $_POST['kind'] : 'expense';
                $freq = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                        ? $_POST['frequency'] : 'monthly';
                $date = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                if ($kind === 'investment') {
                    $catId = null;
                    $type  = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                } else {
                    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
                    $type  = null;
                }
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM recurring WHERE household_id = ?",
                    [$hid],
                    $L['recurring_total_max'],
                    'Recurring items'
                );
                $db->prepare(
                    "INSERT INTO recurring (household_id, name, amount, kind, category_id, type, frequency, next_date)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $kind, $catId, $type, $freq, $date]);
                flash('success', 'Recurring item saved');
                redirect('/recurring');

            case '/recurring/delete':
                $rid = (int)($_POST['id'] ?? 0);
                $cascade = !empty($_POST['cascade']);
                $db->beginTransaction();
                try {
                    $pastDeleted = 0;
                    if ($cascade) {
                        $delE = $db->prepare("DELETE FROM expenses WHERE household_id = ? AND recurring_id = ?");
                        $delE->execute([$hid, $rid]);
                        $delI = $db->prepare("DELETE FROM investments WHERE household_id = ? AND recurring_id = ?");
                        $delI->execute([$hid, $rid]);
                        $pastDeleted = $delE->rowCount() + $delI->rowCount();
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
                $name  = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt   = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $kind  = in_array($_POST['kind'] ?? '', ['expense','investment'], true) ? $_POST['kind'] : 'expense';
                $freq  = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                         ? $_POST['frequency'] : 'monthly';
                $date  = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                if ($kind === 'investment') {
                    $catId = null;
                    $type  = validInvestmentType($db, $hid, (string)($_POST['type'] ?? ''));
                } else {
                    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
                    $type  = null;
                }
                $db->prepare(
                    "UPDATE recurring SET name = ?, amount = ?, kind = ?, category_id = ?, type = ?, frequency = ?, next_date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $kind, $catId, $type, $freq, $date, $id, $hid]);
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
                $db->prepare("DELETE FROM categories WHERE id = ? AND household_id = ? AND is_custom = 1")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Category removed');
                redirect($_POST['back'] ?? '/');

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

            case '/members/delete':
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
    case '/recurring': renderRecurring($db, $user, isset($_GET['new'])); break;
    case '/year':      renderYear($db, $user, (int)($_GET['y'] ?? 0), (string)($_GET['mode'] ?? 'cal'), (string)($_GET['inv'] ?? 'all')); break;
    case '/terms':     renderTerms($db, $user); break;
    case '/manage':    redirect('/#profile');           // legacy path
    default:           http_response_code(404); exit('404');
}
