<?php
declare(strict_types=1);

// ────────────────────────────────────────────────────────────────────
// Schema (MySQL / InnoDB). Idempotent — CREATE IF NOT EXISTS is a cheap
// metadata check on subsequent requests. First hit against a fresh DB
// creates everything.
// ────────────────────────────────────────────────────────────────────
const SCHEMA_STATEMENTS = [
    "CREATE TABLE IF NOT EXISTS households (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        google_sub VARCHAR(64) NOT NULL,
        email VARCHAR(190) NOT NULL,
        name VARCHAR(80) NOT NULL,
        is_dark TINYINT(1) NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT '₹',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_google_sub (google_sub),
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(60) NOT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        icon VARCHAR(30) NOT NULL,
        is_custom TINYINT(1) NOT NULL DEFAULT 0,
        budget DECIMAL(12,2) NOT NULL DEFAULT 0,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
        member_id INT NULL,
        recurring_id INT NULL,
        note VARCHAR(200) NULL,
        date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_household_date (household_id, date),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        type VARCHAR(40) NOT NULL,
        recurring_id INT NULL,
        date DATE NOT NULL,
        INDEX ix_household_date (household_id, date),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS recurring (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        kind VARCHAR(20) NOT NULL DEFAULT 'expense',
        category_id INT NULL,
        type VARCHAR(40) NULL,
        frequency ENUM('monthly','quarterly','yearly') NOT NULL,
        next_date DATE NOT NULL,
        INDEX ix_household_next (household_id, next_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS rate_limits (
        bucket VARCHAR(160) NOT NULL PRIMARY KEY,
        hits INT NOT NULL DEFAULT 0,
        window_end INT UNSIGNED NOT NULL,
        INDEX ix_window (window_end)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS investment_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(40) NOT NULL,
        archived TINYINT(1) NOT NULL DEFAULT 0,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Earnings are the mirror of expenses, but categorised by their own list — an FK id,
    // not a name (unlike investment_types), so a rename needs no cascade.
    "CREATE TABLE IF NOT EXISTS earning_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS earnings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
        recurring_id INT NULL,
        date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_household_date (household_id, date),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

// Applied in order after SCHEMA_STATEMENTS, each independently. Re-running is a no-op —
// MySQL errors on a duplicate column/index and the loop logs it and moves on. Append only.
const MIGRATIONS = [
    "ALTER TABLE users ADD COLUMN currency VARCHAR(8) NOT NULL DEFAULT '₹'",
    "ALTER TABLE expenses ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
    "ALTER TABLE investments ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
    "ALTER TABLE investments MODIFY COLUMN type VARCHAR(40) NOT NULL",
    "ALTER TABLE recurring ADD COLUMN kind VARCHAR(20) NOT NULL DEFAULT 'expense'",
    "ALTER TABLE recurring ADD COLUMN type VARCHAR(40) NULL",
    // v6 — investments paginate with ORDER BY date DESC; ix_household alone forced a filesort.
    "ALTER TABLE investments ADD INDEX ix_household_date (household_id, date)",
    "ALTER TABLE investments DROP INDEX ix_household",
    "ALTER TABLE categories ADD COLUMN budget DECIMAL(12,2) NOT NULL DEFAULT 0",
    "ALTER TABLE investment_types ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0",
    // v8 — recurring earnings. Only bites on databases where `earnings` was created by the
    // first cut of this table, before it carried the recurring FK.
    "ALTER TABLE earnings ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
];

const DEFAULT_INVESTMENT_TYPES = ['SIP', 'Stocks', 'FD-RD', 'Gold', 'PPF-EPF', 'Other'];

const DEFAULT_EARNING_CATEGORIES = ['Salary', 'Interest', 'Other'];

const DEFAULT_CATEGORIES = [
    ['Groceries', 'shopping-cart'], ['Rent', 'home'], ['Utilities', 'zap'],
    ['Dining Out', 'utensils'], ['Transport', 'car'], ['Health', 'heart-pulse'],
    ['Shopping', 'shopping-bag'], ['EMI / Loans', 'landmark'], ['Subscriptions', 'repeat'],
    ['Credit Card Bill', 'credit-card'], ['Entertainment', 'film'], ['Education', 'book-open'],
    ['Other', 'more-horizontal'],
];

class UserErr extends Exception {}

// ────────────────────────────────────────────────────────────────────
// DB bootstrap
// ────────────────────────────────────────────────────────────────────
function makeDb(array $cfg): PDO {
    $dsn = "mysql:host={$cfg['db']['host']};dbname={$cfg['db']['name']};charset=utf8mb4";
    $db = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // Schema/migration bootstrap runs once, then a sentinel file skips it on every subsequent
    // request. Delete the sentinel to force a re-run after schema changes.
    $sentinel = __DIR__ . '/data/.schema-ok-v8';
    if (!file_exists($sentinel)) {
        foreach (SCHEMA_STATEMENTS as $sql) $db->exec($sql);
        foreach (MIGRATIONS as $sql) {
            try { $db->exec($sql); }
            catch (PDOException $e) { error_log('[migrate] ' . $sql . ' — ' . $e->getMessage()); }
        }
        // Backfill defaults for households that predate a lookup table. Keyed on "has no rows
        // at all", so a household that deliberately deleted one down to a smaller set is left alone.
        foreach ([
            ['investment_types',   DEFAULT_INVESTMENT_TYPES],
            ['earning_categories', DEFAULT_EARNING_CATEGORIES],
        ] as [$table, $defaults]) {
            $orphaned = $db->query(
                "SELECT h.id FROM households h WHERE NOT EXISTS (SELECT 1 FROM $table t WHERE t.household_id = h.id)"
            )->fetchAll(PDO::FETCH_COLUMN);
            if (!$orphaned) continue;
            // Guarded insert, not a plain one: two requests can both find the sentinel missing
            // right after a deploy and run this block concurrently, which would hand the
            // household two of every default.
            $ins = $db->prepare(
                "INSERT INTO $table (household_id, name)
                 SELECT ?, ? FROM DUAL
                 WHERE NOT EXISTS (SELECT 1 FROM (SELECT 1 FROM $table WHERE household_id = ? AND name = ?) x)"
            );
            foreach ($orphaned as $hid) {
                foreach ($defaults as $t) $ins->execute([(int)$hid, $t, (int)$hid, $t]);
            }
        }
        if (!is_dir(dirname($sentinel))) @mkdir(dirname($sentinel), 0755, true);
        // Without this sentinel the whole schema + migration set re-runs on EVERY request,
        // which is slow and floods the error log. A silent @touch failure would hide that,
        // so say so plainly — `--preflight` checks the same thing before you deploy.
        if (!@touch($sentinel)) {
            error_log('[migrate] CANNOT WRITE ' . $sentinel
                . ' — schema bootstrap will re-run on every request. Make data/ writable.');
        }
    }
    return $db;
}

// ────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Indian digit grouping (lakh/crore): the last three digits, then pairs.
// 10,00,000.00 — not number_format()'s 1,000,000.00. Applies to every money value in the
// app; percentages, CSS widths and byte counts deliberately keep plain number_format().
function groupIndian(float $amount, int $decimals = 2): string {
    $n   = number_format(abs($amount), $decimals, '.', '');
    $int = $n; $dec = '';
    if ($decimals > 0) { [$int, $frac] = explode('.', $n); $dec = '.' . $frac; }
    if (strlen($int) > 3) {
        $int = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($int, 0, -3)) . ',' . substr($int, -3);
    }
    return ($amount < 0 ? '-' : '') . $int . $dec;
}
function fmt(float $amount): string { return ($_SESSION['currency'] ?? '₹') . groupIndian($amount); }
// Rounded to the rupee — for summary tiles, where paise are noise and three figures
// share one row. Detail rows keep full precision via fmt().
function fmtShort(float $amount): string { return ($_SESSION['currency'] ?? '₹') . groupIndian($amount, 0); }
// Most redirect targets come from a `back` form field, which is attacker-controllable.
// Keep them same-site: must be a root-relative path ("/x"), never a protocol-relative
// "//host" or absolute URL, and never contain CR/LF (header injection).
function safeRedirectTarget(string $to): string {
    if ($to === '' || $to[0] !== '/' || str_starts_with($to, '//') || preg_match('/[\r\n]/', $to)) {
        return '/';
    }
    return $to;
}
function redirect(string $to): never { header('Location: ' . safeRedirectTarget($to)); exit; }
function today(): string { return date('Y-m-d'); }

// Shared hosts, CDNs and load balancers commonly terminate TLS at a proxy: PHP then sees a
// plain HTTP request with $_SERVER['HTTPS'] unset, and the real scheme only in
// X-Forwarded-Proto. Missing that would silently drop the `Secure` flag from the session
// cookie and emit http:// canonical/OG URLs on an https site.
// Trusting the header errs safe — a spoofed value only makes the cookie stricter, never laxer.
function isHttps(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    // May arrive as a comma-separated chain ("https, http"); the client-facing hop is first.
    $proto = strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
    if ($proto === 'https') return true;
    return (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function originUrl(): string {
    return (isHttps() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function advanceDate(string $dateStr, string $freq): string {
    $spec = match ($freq) { 'quarterly' => 'P3M', 'yearly' => 'P1Y', default => 'P1M' };
    return (new DateTimeImmutable($dateStr))->add(new DateInterval($spec))->format('Y-m-d');
}

function currentUser(PDO $db): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $s = $db->prepare("SELECT * FROM users WHERE id = ?");
    $s->execute([$_SESSION['user_id']]);
    return $s->fetch() ?: null;
}

function clientIp(array $cfg): string {
    if (!empty($cfg['trust_cloudflare_ip']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ────────────────────────────────────────────────────────────────────
// CSRF (double-submit via session token; hash_equals-compared)
// ────────────────────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrfInput(): string {
    return '<input type="hidden" name="_csrf" value="' . h(csrfToken()) . '">';
}
// Raw token for JS attribute contexts (e.g. onclick="askConfirm({csrf:'...'})").
function csrfJs(): string { return h(csrfToken()); }
function csrfCheck(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(400); exit('Bad CSRF token — refresh and retry.');
    }
}

// ────────────────────────────────────────────────────────────────────
// Rate limiting — one bucket per (ip, key). Not perfectly atomic under
// concurrent hits; a couple of extra requests can leak through. Fine
// for a household-scale app. ponytail: swap to Redis if scale hurts.
// ────────────────────────────────────────────────────────────────────
function rateLimit(PDO $db, array $cfg, string $key, int $limit, int $windowSeconds): void {
    $bucket   = clientIp($cfg) . ':' . $key;
    $now      = time();
    $windowEnd = $now + $windowSeconds;

    // Atomic upsert: if the row's window has expired, reset it in one statement;
    // otherwise increment hits. Beats SELECT + REPLACE which lets two concurrent
    // requests at a window boundary both reset the counter.
    $db->prepare(
        "INSERT INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)
         ON DUPLICATE KEY UPDATE
            hits       = IF(window_end < VALUES(window_end), 1, hits + 1),
            window_end = IF(window_end < VALUES(window_end), VALUES(window_end), window_end)"
    )->execute([$bucket, $windowEnd]);

    $sel = $db->prepare("SELECT hits, window_end FROM rate_limits WHERE bucket = ?");
    $sel->execute([$bucket]);
    $row = $sel->fetch();
    if ($row && (int)$row['hits'] > $limit) {
        $retry = max(1, (int)$row['window_end'] - $now);
        http_response_code(429);
        header("Retry-After: $retry");
        header("Content-Type: text/plain; charset=utf-8");
        exit("Rate limit exceeded. Retry in {$retry}s.");
    }
}

// ────────────────────────────────────────────────────────────────────
// Validation — throws UserErr on failure (rendered as an error toast).
// ────────────────────────────────────────────────────────────────────
function parseAmount(string $raw, array $cfg): float {
    $raw = trim($raw);
    if ($raw === '' || !preg_match('/^\d{1,10}(\.\d{1,2})?$/', $raw)) {
        throw new UserErr('Invalid amount.');
    }
    $a = (float)$raw;
    if ($a <= 0) throw new UserErr('Amount must be positive.');
    if ($a > $cfg['limits']['amount_max']) throw new UserErr('Amount too large.');
    return round($a, 2);
}
function requireStr(string $raw, int $max, string $label): string {
    $s = trim($raw);
    if ($s === '')            throw new UserErr("$label is required.");
    if (mb_strlen($s) > $max) throw new UserErr("$label too long (max $max).");
    return $s;
}
function optionalStr(?string $raw, int $max, string $label): string {
    $s = trim((string)$raw);
    if (mb_strlen($s) > $max) throw new UserErr("$label too long (max $max).");
    return $s;
}
function requireDate(string $raw, string $label): string {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
    if (!$d || $d->format('Y-m-d') !== $raw) throw new UserErr("$label must be a valid date.");
    return $raw;
}
function assertUnderLimit(PDO $db, string $sqlCount, array $params, int $max, string $label): void {
    $s = $db->prepare($sqlCount);
    $s->execute($params);
    if ((int)$s->fetchColumn() >= $max) throw new UserErr("$label limit reached ($max).");
}

// Row ids arrive from <select> fields, which are attacker-controllable: without this a
// crafted POST could attach an entry to another household's category or member, and the
// LEFT JOINs that render lists would then show that household's name back. Returns the id
// only if it belongs here; anything else (0, missing, foreign) degrades to NULL — the same
// "uncategorised" state a deleted category leaves behind.
// $table is a caller-supplied literal, never user input — it cannot be bound as a parameter.
function ownedId(PDO $db, string $table, int $hid, int $id): ?int {
    if ($id <= 0) return null;
    $s = $db->prepare("SELECT id FROM $table WHERE id = ? AND household_id = ?");
    $s->execute([$id, $hid]);
    return $s->fetchColumn() ? $id : null;
}

// Rolling window of $n whole months ending with the month that contains $todayYmd.
// Returns [startDate, endDateExclusive, ['Y-m', …]] — the exclusive end keeps callers on
// `date >= ? AND date < ?`, which uses the (household_id, date) index. Anchoring on the 1st
// keeps the month arithmetic honest (a naive "-1 month" from the 31st lands in the month after next).
function rollingMonths(string $todayYmd, int $n): array {
    $first = (new DateTimeImmutable($todayYmd))->modify('first day of this month');
    $start = $first->modify('-' . ($n - 1) . ' months');
    $keys  = [];
    for ($i = 0, $c = $start; $i < $n; $i++, $c = $c->modify('+1 month')) $keys[] = $c->format('Y-m');
    return [$start->format('Y-m-d'), $first->modify('+1 month')->format('Y-m-d'), $keys];
}

// Confirms the submitted investment type belongs to this household. Rejects free-text.
function validInvestmentType(PDO $db, int $hid, string $type): string {
    $s = $db->prepare("SELECT name FROM investment_types WHERE household_id = ? AND name = ?");
    $s->execute([$hid, $type]);
    if ($row = $s->fetchColumn()) return (string)$row;
    throw new UserErr('Unknown investment type — pick one from the list (edit types in the profile drawer).');
}

// Budgets are optional and 0 means "no budget", so this can't reuse parseAmount (which
// rejects 0). Blank input is also 0 — clearing the field removes the budget.
function parseBudget(string $raw, array $cfg): float {
    $raw = trim($raw);
    if ($raw === '') return 0.0;
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $raw)) throw new UserErr('Invalid budget.');
    $b = round((float)$raw, 2);
    if ($b > $cfg['limits']['amount_max']) throw new UserErr('Budget too large.');
    return $b;
}

// Archiving is per investment *type*. `investments.type` stores the type name (not an FK —
// renames cascade in /investment-types/update), so membership is a name match. Types are
// capped at 30 per household, so callers can safely splat this into an IN() list.
function archivedTypeNames(PDO $db, int $hid): array {
    $s = $db->prepare("SELECT name FROM investment_types WHERE household_id = ? AND archived = 1");
    $s->execute([$hid]);
    return $s->fetchAll(PDO::FETCH_COLUMN);
}

// Type-scoping clause for an investment list: returns [sqlFragment, params] to append to
// "WHERE household_id = ?". The nothing-archived case is the trap — "archived" must match
// zero rows, not fall through to an unfiltered list of everything.
function investmentFilterSql(string $filter, array $archivedNames): array {
    if ($filter === 'all')      return ['', []];
    if (!$archivedNames)        return $filter === 'archived' ? [' AND 1 = 0', []] : ['', []];
    $in = implode(',', array_fill(0, count($archivedNames), '?'));
    return [$filter === 'archived' ? " AND type IN ($in)" : " AND type NOT IN ($in)", $archivedNames];
}

// ────────────────────────────────────────────────────────────────────
// Recurring sweep (runs from web on every authed request AND from cron).
// Both call this — idempotent, only fires when next_date has passed.
// ────────────────────────────────────────────────────────────────────
function sweepRecurring(PDO $db, int $hid): void {
    $today = today();
    // Cheap guard: single indexed lookup. The common case (nothing due) exits here
    // without opening the prepare/fetch/update path on every authed request.
    $probe = $db->prepare("SELECT 1 FROM recurring WHERE household_id = ? AND next_date <= ? LIMIT 1");
    $probe->execute([$hid, $today]);
    if (!$probe->fetchColumn()) return;

    $rows = $db->prepare("SELECT * FROM recurring WHERE household_id = ? AND next_date <= ?");
    $rows->execute([$hid, $today]);
    $insExp = $db->prepare(
        "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date, recurring_id)
         VALUES (?, ?, ?, NULL, ?, ?, ?)"
    );
    $insInv = $db->prepare(
        "INSERT INTO investments (household_id, name, amount, type, date, recurring_id)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insErn = $db->prepare(
        "INSERT INTO earnings (household_id, name, amount, category_id, date, recurring_id)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $upd = $db->prepare("UPDATE recurring SET next_date = ? WHERE id = ?");
    foreach ($rows->fetchAll() as $r) {
        $nd = $r['next_date'];
        $kind = $r['kind'] ?? 'expense';
        // Cap iterations — a stale/bad next_date shouldn't insert years of catch-up rows
        // synchronously in one request. 120 = 10 years of monthly / 30 years of quarterly.
        for ($i = 0; $i < 120 && $nd <= $today; $i++) {
            if ($kind === 'investment') {
                $insInv->execute([$hid, $r['name'], $r['amount'], (string)($r['type'] ?? 'Other'), $nd, (int)$r['id']]);
            } elseif ($kind === 'earning') {
                // `recurring.category_id` is read against whichever category table the kind
                // implies — expense categories here, earning categories there. The POST
                // handlers re-validate it per kind on every save, so it can't cross over.
                $insErn->execute([$hid, $r['name'], $r['amount'], $r['category_id'], $nd, (int)$r['id']]);
            } else {
                $insExp->execute([$hid, $r['amount'], $r['category_id'], '[recurring] ' . $r['name'], $nd, (int)$r['id']]);
            }
            $nd = advanceDate($nd, $r['frequency']);
        }
        $upd->execute([$nd, $r['id']]);
    }
}

// ────────────────────────────────────────────────────────────────────
// Bootstrap a household for a new Google user.
// ────────────────────────────────────────────────────────────────────
function bootstrapHousehold(PDO $db, string $name, string $email, string $googleSub): int {
    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO households (name) VALUES (?)")->execute(['My Household']);
        $hid = (int)$db->lastInsertId();
        $db->prepare("INSERT INTO users (household_id, google_sub, email, name) VALUES (?, ?, ?, ?)")
           ->execute([$hid, $googleSub, $email, $name]);
        $uid = (int)$db->lastInsertId();
        $db->prepare("INSERT INTO members (household_id, name) VALUES (?, ?)")
           ->execute([$hid, 'Me']);
        $ins = $db->prepare("INSERT INTO categories (household_id, name, icon, is_custom) VALUES (?, ?, ?, 0)");
        foreach (DEFAULT_CATEGORIES as [$n, $i]) $ins->execute([$hid, $n, $i]);
        $insIt = $db->prepare("INSERT INTO investment_types (household_id, name) VALUES (?, ?)");
        foreach (DEFAULT_INVESTMENT_TYPES as $t) $insIt->execute([$hid, $t]);
        $insEc = $db->prepare("INSERT INTO earning_categories (household_id, name) VALUES (?, ?)");
        foreach (DEFAULT_EARNING_CATEGORIES as $c) $insEc->execute([$hid, $c]);
        $db->commit();
        return $uid;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

// ────────────────────────────────────────────────────────────────────
// Google ID token verification via Google's tokeninfo endpoint.
// ponytail: `tokeninfo` is Google's simplest verifier — one HTTPS call,
// no dependencies. Google's own docs prefer local JWKS verify for lower
// latency and offline resilience. If you want that later, swap in
// firebase/php-jwt via composer and cache https://www.googleapis.com/oauth2/v3/certs.
// ────────────────────────────────────────────────────────────────────
function verifyGoogleIdToken(string $idToken, string $expectedClientId): ?array {
    if ($idToken === '' || strlen($idToken) > 4096) return null;
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $resp = @file_get_contents(
        'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken),
        false, $ctx
    );
    if (!$resp) return null;
    $j = json_decode($resp, true);
    if (!is_array($j) || !empty($j['error'])) return null;

    // Audience must match the client id we minted the token for.
    if (($j['aud'] ?? '') !== $expectedClientId) return null;
    // Issuer must be Google.
    $iss = $j['iss'] ?? '';
    if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') return null;
    // Not expired.
    if ((int)($j['exp'] ?? 0) < time()) return null;
    // Email must be verified — tokeninfo returns the string "true".
    $ev = $j['email_verified'] ?? null;
    if ($ev !== true && $ev !== 'true') return null;
    // Subject required.
    if (empty($j['sub'])) return null;

    return $j;
}

// Dev-mode stub sign-in — active only while google_client_id is still the placeholder.
// Lets you click through the app without configuring Google OAuth locally.
function isDevStubActive(string $clientId): bool {
    return str_ends_with($clientId, 'YOUR_CLIENT_ID.apps.googleusercontent.com');
}

// ────────────────────────────────────────────────────────────────────
// Flash message (survives one PRG redirect, then cleared).
// ────────────────────────────────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function consumeFlash(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    return $f;
}
