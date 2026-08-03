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
        echo "ok\n"; exit;
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
// Unauthed: only the sign-in gate + Google callback are reachable.
// ────────────────────────────────────────────────────────────────────
if (!$user) {
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
                redirect('/invest');

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
                redirect('/invest');

            case '/recurring':
                $name = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt  = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $freq = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                        ? $_POST['frequency'] : 'monthly';
                $date = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                $catId = (int)($_POST['category_id'] ?? 0);
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM recurring WHERE household_id = ?",
                    [$hid],
                    $L['recurring_total_max'],
                    'Recurring items'
                );
                $db->prepare(
                    "INSERT INTO recurring (household_id, name, amount, category_id, frequency, next_date)
                     VALUES (?, ?, ?, ?, ?, ?)"
                )->execute([$hid, $name, $amt, $catId ?: null, $freq, $date]);
                flash('success', 'Recurring item saved');
                redirect('/recurring');

            case '/recurring/delete':
                $db->prepare("DELETE FROM recurring WHERE id = ? AND household_id = ?")
                   ->execute([(int)$_POST['id'], $hid]);
                flash('success', 'Recurring item deleted');
                redirect('/recurring');

            case '/recurring/update':
                $id    = (int)($_POST['id'] ?? 0);
                $name  = requireStr((string)($_POST['name'] ?? ''), $L['name_len_max'], 'Name');
                $amt   = parseAmount((string)($_POST['amount'] ?? ''), $config);
                $freq  = in_array($_POST['frequency'] ?? '', ['monthly','quarterly','yearly'], true)
                         ? $_POST['frequency'] : 'monthly';
                $date  = requireDate((string)($_POST['next_date'] ?? today()), 'Next date');
                $catId = (int)($_POST['category_id'] ?? 0);
                $db->prepare(
                    "UPDATE recurring SET name = ?, amount = ?, category_id = ?, frequency = ?, next_date = ?
                     WHERE id = ? AND household_id = ?"
                )->execute([$name, $amt, $catId ?: null, $freq, $date, $id, $hid]);
                flash('success', 'Recurring item updated');
                redirect('/recurring');

            case '/categories':
                $name = requireStr((string)($_POST['name'] ?? ''), 50, 'Category');
                assertUnderLimit(
                    $db,
                    "SELECT COUNT(*) FROM categories WHERE household_id = ?",
                    [$hid],
                    $L['categories_total_max'],
                    'Categories'
                );
                $db->prepare("INSERT INTO categories (household_id, name, icon, is_custom) VALUES (?, ?, 'tag', 1)")
                   ->execute([$hid, $name]);
                flash('success', 'Category added');
                redirect($_POST['back'] ?? '/');

            case '/categories/update':
                $id   = (int)($_POST['id'] ?? 0);
                $name = requireStr((string)($_POST['name'] ?? ''), 50, 'Category');
                $db->prepare("UPDATE categories SET name = ? WHERE id = ? AND household_id = ?")
                   ->execute([$name, $id, $hid]);
                flash('success', 'Category renamed');
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
    case '/invest':    renderInvest($db, $user, isset($_GET['new'])); break;
    case '/recurring': renderRecurring($db, $user, isset($_GET['new'])); break;
    case '/terms':     renderTerms($db, $user); break;
    case '/manage':    redirect('/#profile');           // legacy path
    default:           http_response_code(404); exit('404');
}
