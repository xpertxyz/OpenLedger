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

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'homeledger',
        'user' => getenv('DB_USER') ?: 'homeledger',
        'pass' => getenv('DB_PASS') ?: '',
    ],

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
        'recurring_total_max'    => 100,
        'categories_total_max'   => 100,
        'members_total_max'      => 20,
        'rate_signin_per_15min'  => 10,            // per IP
        'rate_post_per_min'      => 60,            // per IP
    ],
];
