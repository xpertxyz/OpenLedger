<?php
// Open Ledger — configuration. Real values come from .env (copy .env.example).
// Never commit .env. This file itself ships with placeholders only.

// Minimal .env loader — supports KEY=value, quoted values, and # comments.
// Existing env vars (from the host) win over .env.
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if (preg_match('/^"(.*)"$/', $v, $m) || preg_match("/^'(.*)'$/", $v, $m)) $v = $m[1];
        if (getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}

// An unset env var and an env var set to "0" are different answers, and `?:` cannot tell them
// apart — it treats the string "0" as absent. Every boolean switch below goes through here.
// Guarded because this file is `require`d, not `require_once`d, and it returns an array —
// a second include would otherwise be a redeclare fatal rather than a harmless repeat.
if (!function_exists('envStr')) {
    function envStr(string $key, string $default): string {
        $v = getenv($key);
        return ($v === false || $v === '') ? $default : $v;
    }
    function envFlag(string $key, bool $default): bool {
        return envStr($key, $default ? '1' : '0') === '1';
    }
}

// Every date in this app is now computed by PHP and bound as a parameter — nothing asks the
// database what day it is. That makes the app's timezone a real setting rather than an
// accident of the host's php.ini, which defaulted to UTC: `today()` returned the UTC date, so
// an expense added between midnight and 05:29 IST filed under yesterday, and an invite's
// "minutes left" was computed against a clock 5.5 hours from MySQL's.
// Must run before anything calls date() — this file is the first thing index.php requires.
date_default_timezone_set(getenv('APP_TZ') ?: 'Asia/Kolkata');

return [
    'db' => [
        // 'mysql' on the web, 'sqlite' on Android where the whole ledger is one local file.
        // Same PHP either way; the dialect differences live in makeDb() and the sql*() helpers.
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'homeledger',
        'user' => getenv('DB_USER') ?: 'homeledger',
        'pass' => getenv('DB_PASS') ?: '',
        // SQLite only. Android passes its app-private files dir here; the default keeps a
        // local dev database beside the sentinel, inside the already-gitignored data/.
        'path' => getenv('DB_PATH') ?: __DIR__ . '/data/ledger.db',
    ],

    // Build-time switches, not user preferences. The Android build turns sharing and Google
    // sign-in off because a local-only ledger has no second device to share with and no
    // server to hold an account. Read via getenv() so the Android launcher can set them in
    // the PHP process environment without writing a file — see ANDROID.md.
    // Defaults are ON, so the web app behaves exactly as before.
    //
    // NOT written as `getenv(...) ?: '1'`: PHP counts the string "0" as falsy, so that form
    // reads an explicit HL_SHARING=0 and hands back '1'. The flags silently never turned off.
    'features' => [
        'sharing'        => envFlag('HL_SHARING',       true),
        'google_signin'  => envFlag('HL_GOOGLE_SIGNIN', true),
        // Off by default, and off on the web for good: the panel it draws talks to a native
        // Drive client over a WebView bridge that only the Android build provides.
        'backup'         => envFlag('HL_BACKUP',        false),
    ],
    'terms_url' => envStr('HL_TERMS_URL', ''),

    // Set by the Android launcher from its own BuildConfig; empty everywhere else, which is
    // what keeps the version line out of the website's drawer.
    'app_version' => envStr('HL_APP_VERSION', ''),

    // Google Cloud Console → APIs & Services → Credentials → OAuth 2.0 Client ID (Web).
    // Add your Hostinger domain to "Authorised JavaScript origins" (e.g. https://homeledger.example.com).
    // No client secret needed — Google Identity Services uses only the client id for One Tap / ID tokens.
    'google_client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID.apps.googleusercontent.com',

    'session_name' => 'HLSID',
    'currency'     => '₹',

    // Set APP_DEBUG=1 in .env to display PHP errors on-page. Off in production —
    // if you leave it on, PDO exceptions can leak DB credentials in stack traces.
    'debug' => (getenv('APP_DEBUG') ?: '0') === '1',

    // If Hostinger sits behind Cloudflare, flip this and Cloudflare's CF-Connecting-IP is trusted for rate-limit keys.
    // Leave false when serving direct — otherwise attackers can forge headers to bypass limits.
    'trust_cloudflare_ip' => false,

    // All caps are enforced server-side and return a friendly error toast if exceeded.
    'limits' => [
        'amount_max'             => 100_000_000,   // per single entry (fits DECIMAL(12,2))
        'name_len_max'           => 80,
        'note_len_max'           => 200,
        'expenses_per_day_max'   => 200,           // per household per day
        'investments_total_max'  => 1000,
        'earnings_total_max'     => 2000,
        'recurring_total_max'    => 100,
        'categories_total_max'   => 100,
        'members_total_max'      => 20,
        'rate_signin_per_15min'  => 10,            // per IP
        'rate_post_per_min'      => 60,            // per IP
    ],
];
