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
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
        member_id INT NULL,
        note VARCHAR(200) NULL,
        date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_household_date (household_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        type VARCHAR(20) NOT NULL,
        date DATE NOT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS recurring (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
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
];

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
    foreach (SCHEMA_STATEMENTS as $sql) $db->exec($sql);
    return $db;
}

// ────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $to): never { header("Location: $to"); exit; }
function today(): string { return date('Y-m-d'); }

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
    $bucket = clientIp($cfg) . ':' . $key;
    $now = time();
    $sel = $db->prepare("SELECT hits, window_end FROM rate_limits WHERE bucket = ?");
    $sel->execute([$bucket]);
    $row = $sel->fetch();

    if (!$row || (int)$row['window_end'] < $now) {
        $db->prepare("REPLACE INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)")
           ->execute([$bucket, $now + $windowSeconds]);
        return;
    }
    if ((int)$row['hits'] >= $limit) {
        $retry = max(1, (int)$row['window_end'] - $now);
        http_response_code(429);
        header("Retry-After: $retry");
        header("Content-Type: text/plain; charset=utf-8");
        exit("Rate limit exceeded. Retry in {$retry}s.");
    }
    $db->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE bucket = ?")->execute([$bucket]);
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

// ────────────────────────────────────────────────────────────────────
// Recurring sweep (runs from web on every authed request AND from cron).
// Both call this — idempotent, only fires when next_date has passed.
// ────────────────────────────────────────────────────────────────────
function sweepRecurring(PDO $db, int $hid): void {
    $today = today();
    $rows = $db->prepare("SELECT * FROM recurring WHERE household_id = ? AND next_date <= ?");
    $rows->execute([$hid, $today]);
    $insExp = $db->prepare(
        "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date)
         VALUES (?, ?, ?, NULL, ?, ?)"
    );
    $upd = $db->prepare("UPDATE recurring SET next_date = ? WHERE id = ?");
    foreach ($rows->fetchAll() as $r) {
        $nd = $r['next_date'];
        while ($nd <= $today) {
            $insExp->execute([$hid, $r['amount'], $r['category_id'], '[recurring] ' . $r['name'], $nd]);
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
