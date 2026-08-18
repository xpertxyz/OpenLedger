<?php
// Front-controller router for PHP's built-in server.
// Serve real files as-is (styles.css, favicon, etc.); everything else -> index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Same denials as .htaccess, because this server never reads it. Without this, running
// `php -S` on anything but loopback hands out the Android sources and, worse, EXECUTES
// tests/dual-driver.php — a CLI harness that opens the database and writes to it.
// docs/ is public on purpose and stays servable.
if (preg_match('~^/(android|tests)/~', (string)$path)) {
    http_response_code(403);
    exit("Forbidden.\n");
}
if ($path !== '/' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}
require __DIR__ . '/index.php';
