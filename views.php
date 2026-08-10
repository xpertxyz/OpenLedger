<?php
declare(strict_types=1);

// Dark-theme variable overrides — lifted verbatim from the prototype (THEME_DARK_VARS).
const THEME_DARK_VARS = '--color-bg:#201e1d;--color-surface:#2b2721;--color-text:#f3e9d8;'
  . '--color-divider:color-mix(in srgb, #f3e9d8 18%, transparent);'
  . '--color-neutral-100:#2c2822;--color-neutral-200:#363028;--color-neutral-300:#453d31;--color-neutral-400:#5a5040;--color-neutral-500:#786c56;--color-neutral-600:#9c8f76;--color-neutral-700:#bfb29a;--color-neutral-800:#dcd0ba;--color-neutral-900:#f3e9d8;'
  . '--color-accent:#e0864c;--color-accent-100:#3a2a1c;--color-accent-200:#4d3320;--color-accent-300:#6b4324;--color-accent-400:#95592c;--color-accent-500:#c06f35;--color-accent-600:#d97f42;--color-accent-700:#e79968;--color-accent-800:#f0b78c;--color-accent-900:#f8d7b8;'
  . '--color-accent-2:#93a86e;--color-accent-2-100:#2a301f;--color-accent-2-200:#333b26;--color-accent-2-300:#414c2f;--color-accent-2-400:#57643d;--color-accent-2-500:#71804f;--color-accent-2-600:#8a9863;--color-accent-2-700:#a8b884;--color-accent-2-800:#c4d1a8;--color-accent-2-900:#e2e8d0;'
  . '--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);';

// Lucide sprite (stroke-width 2.75).
const SVG_SPRITE = <<<SVG
<svg width="0" height="0" style="position:absolute;overflow:hidden" aria-hidden="true">
  <symbol id="icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></symbol>
  <symbol id="icon-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="icon-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></symbol>
  <symbol id="icon-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></symbol>
  <symbol id="icon-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 10v6m11-7h-6M7 12H1m16.24-6.24-4.24 4.24M9 15l-4.24 4.24M19.24 18.24 15 14m-6-6L4.76 5.76"/></symbol>
  <symbol id="icon-trash-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></symbol>
  <symbol id="icon-list" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></symbol>
  <symbol id="icon-repeat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 2l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></symbol>
  <symbol id="icon-credit-card" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></symbol>
  <symbol id="icon-trending-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></symbol>
  <symbol id="icon-shopping-cart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></symbol>
  <symbol id="icon-home" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-5H10v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></symbol>
  <symbol id="icon-zap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></symbol>
  <symbol id="icon-utensils" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h1a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></symbol>
  <symbol id="icon-car" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></symbol>
  <symbol id="icon-heart-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/></symbol>
  <symbol id="icon-shopping-bag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></symbol>
  <symbol id="icon-landmark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></symbol>
  <symbol id="icon-film" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="18" rx="2.18"/><line x1="7" y1="3" x2="7" y2="21"/><line x1="17" y1="3" x2="17" y2="21"/><line x1="2" y1="8" x2="7" y2="8"/><line x1="2" y1="16" x2="7" y2="16"/><line x1="17" y1="8" x2="22" y2="8"/><line x1="17" y1="16" x2="22" y2="16"/></symbol>
  <symbol id="icon-book-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></symbol>
  <symbol id="icon-more-horizontal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></symbol>
  <symbol id="icon-tag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42Z"/><circle cx="7.5" cy="7.5" r="1.5"/></symbol>
  <symbol id="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/><line x1="4.9" y1="4.9" x2="6.3" y2="6.3"/><line x1="17.7" y1="17.7" x2="19.1" y2="19.1"/><line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/><line x1="4.9" y1="19.1" x2="6.3" y2="17.7"/><line x1="17.7" y1="6.3" x2="19.1" y2="4.9"/></symbol>
  <symbol id="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></symbol>
  <symbol id="icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="icon-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></symbol>
  <symbol id="icon-archive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></symbol>
  <symbol id="icon-archive-restore" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h2"/><path d="M20 8v11a2 2 0 0 1-2 2h-2"/><path d="m9 15 3-3 3 3"/><path d="M12 12v9"/></symbol>
  <symbol id="icon-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></symbol>
  <symbol id="icon-corner-left-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="14 9 9 4 4 9"/><path d="M20 20h-7a4 4 0 0 1-4-4V4"/></symbol>
  <symbol id="icon-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="icon-copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></symbol>
  <symbol id="icon-log-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></symbol>
  <symbol id="icon-wallet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M18 12h.01"/></symbol>
</svg>
SVG;

// Cache-buster for the one external stylesheet. .htaccess tells browsers to keep it for a
// week, which is only safe because this changes the moment the file does.
function cssVersion(): string {
    static $v = null;
    return $v ??= (string)(@filemtime(__DIR__ . '/design-tokens/styles.css') ?: 0);
}

function icon(string $name, int $size = 20): string {
    $n = htmlspecialchars($name, ENT_QUOTES);
    return "<svg width=\"$size\" height=\"$size\" aria-hidden=\"true\"><use href=\"#icon-$n\"/></svg>";
}

// Horizontal swipe navigation for pages with exactly two neighbours (previous month/year,
// next month/year). Either destination may be null when there is nowhere to go.
// Content follows the finger: drag right for $older, left for $newer.
function swipeNavScript(?string $older, ?string $newer): string {
    $o = json_encode($older, JSON_UNESCAPED_SLASHES);
    $n = json_encode($newer, JSON_UNESCAPED_SLASHES);
    return <<<JS
    <script>
    (function () {
      var OLDER = $o, NEWER = $n;
      var x0 = 0, y0 = 0, t0 = 0;

      addEventListener('touchstart', function (e) {
        // Multi-touch is a pinch/zoom, not a swipe.
        if (e.touches.length !== 1) { t0 = 0; return; }
        var x = e.touches[0].clientX;
        // iOS Safari / Chrome Android claim edge swipes for back/forward navigation, and
        // these listeners are passive so preventDefault() isn't available. Don't compete —
        // just ignore gestures that start in the OS hot zone.
        if (x < 28 || x > innerWidth - 28) { t0 = 0; return; }
        x0 = x; y0 = e.touches[0].clientY; t0 = Date.now();
      }, { passive: true });

      addEventListener('touchend', function (e) {
        if (!t0) return;
        var elapsed = Date.now() - t0; t0 = 0;
        if (elapsed > 800) return;                       // a slow drag isn't a swipe
        // Don't navigate out from under an open dialog or the profile drawer.
        if (document.querySelector('dialog[open]')) return;
        var drawer = document.getElementById('drawer-panel');
        if (drawer && drawer.classList.contains('open')) return;

        var dx = e.changedTouches[0].clientX - x0;
        var dy = e.changedTouches[0].clientY - y0;
        // Horizontal-dominant only, so vertical scrolling is never hijacked.
        if (Math.abs(dx) < 60 || Math.abs(dx) < Math.abs(dy) * 1.5) return;

        if (dx > 0) { if (OLDER) location.href = OLDER; }
        else if (NEWER) location.href = NEWER;
      }, { passive: true });
    })();
    </script>
    JS;
}

// Shared favicon / apple-touch / OG / Twitter / description / theme-color block.
function metaHead(string $origin, string $themeColor = '#f5ead8'): string {
    $desc = 'Track household earnings, expenses, investments and recurring bills together.';
    // $origin is built from HTTP_HOST, which the client controls — escape before it lands
    // in content="" attributes.
    $origin = h($origin);
    $og   = $origin . '/assets/logo/og-image.png';
    $d    = htmlspecialchars($desc, ENT_QUOTES);
    return <<<META
<meta name="description" content="$d">
<meta name="theme-color" content="$themeColor">
<link rel="icon" type="image/svg+xml" href="/assets/app-icon/app-icon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/app-icon/icon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/app-icon/icon-16.png">
<link rel="apple-touch-icon" href="/assets/app-icon/icon-180.png">
<link rel="manifest" href="/manifest.webmanifest">
<meta property="og:title" content="Open Ledger">
<meta property="og:description" content="$d">
<meta property="og:image" content="$og">
<meta property="og:type" content="website">
<meta property="og:url" content="$origin/">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Open Ledger">
<meta name="twitter:description" content="$d">
<meta name="twitter:image" content="$og">
META;
}

// Theme bootstrap for the two public pages. Must be emitted AFTER metaHead(),
// which is where the theme-color meta it repaints comes from, and before any
// visible markup so an explicit choice never flashes the other theme.
//
// Signed-out visitors have no users.is_dark row, so the choice lives in
// localStorage. Absent means "follow the OS" — that path needs no JS at all,
// the prefers-color-scheme block handles it. paintStatusBar() exists because
// the meta is a fixed attribute: CSS can't reach it, so the mobile status bar
// stays on the old colour unless JS rewrites it.
function themeBootScript(): string {
    // Same two colours layout() picks from for a signed-in user.
    return <<<'JS'
<script>
  try { var t = localStorage.getItem('ol-theme');
        if (t === 'dark' || t === 'light') document.documentElement.dataset.theme = t; } catch (e) {}
  function paintStatusBar() {
    var m = document.querySelector('meta[name="theme-color"]');
    if (!m) return;
    var r = document.documentElement;
    var dark = r.dataset.theme
      ? r.dataset.theme === 'dark'
      : window.matchMedia('(prefers-color-scheme: dark)').matches;
    m.setAttribute('content', dark ? '#201e1d' : '#f5ead8');
  }
  paintStatusBar();
  // While no explicit choice is stored the page tracks the OS, so the status
  // bar has to follow it live too.
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', paintStatusBar);
</script>
JS;
}

// Shared page frame: header, content, bottom tabnav, toasts, right-side profile drawer.
function layout(PDO $db, array $user, string $tab, string $content, string $requestUri = '/'): void {
    $darkAttr  = $user['is_dark'] ? ' style="' . h(THEME_DARK_VARS) . '"' : '';
    $themeIcon = $user['is_dark'] ? 'sun' : 'moon';
    $initial   = h(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)));
    $sprite    = SVG_SPRITE;
    $backUri   = h($requestUri);
    $themeBtn  = icon($themeIcon, 18);
    $csrf      = csrfInput();
    $csrfTok   = csrfJs();

    $origin = originUrl();
    $themeColor = $user['is_dark'] ? '#201e1d' : '#f5ead8';
    $meta = metaHead($origin, $themeColor);
    $cssV = cssVersion();
    // Which ledger am I looking at? Once a person can hold several, every screen needs to say
    // so — a shared ledger and a personal one look identical otherwise. Truncated rather than
    // wrapped: the header is one line, and a long name must not push the avatar off a phone.
    //
    // What the tap does depends on how many you have, because the useful action does:
    //   one   — nothing to switch to, so it opens the settings page
    //   two   — the "other one" is unambiguous, so it just switches, in one tap
    //   three+ — there is a choice to make, so it asks
    // All three land back on the page you were already on, so switching mid-task keeps you there.
    $ledgerName = (string)($user['household_name'] ?? '');
    $ledgers    = $user['ledgers'] ?? [];
    $ledgerDlg  = '';
    $ledgerTag  = '';
    if ($ledgerName !== '') {
        $others = array_values(array_filter($ledgers, fn($l) => (int)$l['id'] !== (int)$user['household_id']));
        // Whose ledger is this — not whether it is shared. Once you both own one and belong to
        // someone else's, "shared" is true of both and tells you nothing; "mine or theirs" is
        // the fact that changes what you can edit and whose books you are adding to.
        $owned = ($user['role'] ?? ROLE_MEMBER) === ROLE_OWNER;
        $label = icon($owned ? 'wallet' : 'users', 13) . '<span>' . h($ledgerName) . '</span>';
        if (count($ledgers) === 2 && $others) {
            // display:contents so the form itself does not become a flex item and knock the
            // header's spacing about — the button stays a direct child for layout purposes.
            $ledgerTag = '<form method="post" action="/ledgers/switch" style="display:contents">'
                . csrfInput()
                . '<input type="hidden" name="household_id" value="' . (int)$others[0]['id'] . '">'
                . '<input type="hidden" name="back" value="' . h($requestUri) . '">'
                . '<button type="submit" class="hdr-ledger" title="Switch to '
                . h((string)$others[0]['name']) . '">' . $label . '</button></form>';
        } elseif (count($ledgers) > 2) {
            $ledgerTag = '<button type="button" class="hdr-ledger" title="Switch ledger"'
                . ' onclick="document.getElementById(\'ledger-dlg\').showModal()">' . $label . '</button>';
            $rows = '';
            foreach ($ledgers as $l) {
                $on = (int)$l['id'] === (int)$user['household_id'];
                $rows .= '<form method="post" action="/ledgers/switch">' . csrfInput()
                    . '<input type="hidden" name="household_id" value="' . (int)$l['id'] . '">'
                    . '<input type="hidden" name="back" value="' . h($requestUri) . '">'
                    // autofocus on the current one: showModal() rings whatever it focuses, and
                    // with the ring on the first row it read as a second "selected" marker
                    // competing with the real one. Now focus and selection are the same row.
                    . '<button type="submit" class="card elev-sm row"' . ($on ? ' autofocus' : '')
                    . ' style="width:100%; margin:0 0 8px; text-align:left; border:none; cursor:pointer;'
                    . ($on ? ' outline:2px solid var(--color-accent); outline-offset:-2px;' : '') . '">'
                    . '<span class="row-icon">' . icon($on ? 'check' : ($l['role'] === ROLE_OWNER ? 'wallet' : 'users'), 18) . '</span>'
                    . '<span class="row-main"><span class="title" style="display:block;">' . h((string)$l['name']) . '</span>'
                    . '<span class="sub" style="display:block;">'
                    . ($l['role'] === ROLE_OWNER ? 'You own this' : 'Shared with you') . '</span></span>'
                    . '</button></form>';
            }
            $ledgerDlg = '<dialog id="ledger-dlg" class="confirm" style="max-width:360px;">'
                . '<div class="dlg-title">Switch ledger</div>' . $rows
                . '<div class="dlg-actions">'
                . '<button type="button" class="btn btn-secondary" onclick="document.getElementById(\'ledger-dlg\').close()">Cancel</button>'
                . '<a class="btn" href="/ledgers">Manage</a></div></dialog>';
        } else {
            $ledgerTag = '<a class="hdr-ledger" href="/ledgers" title="' . h($ledgerName)
                . ($owned ? ' — your ledger' : ' — shared with you') . '">' . $label . '</a>';
        }
    }

    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Open Ledger</title>
$meta
<link rel="stylesheet" href="/design-tokens/styles.css?v={$cssV}">
<style>
  body { margin:0; background:var(--color-bg); -webkit-tap-highlight-color: transparent; }
  a, button, [role="button"], .row, .cat-chip, .pill-btn { -webkit-tap-highlight-color: transparent; }
  .tabnav a { transition: background .15s, color .15s, opacity .15s; }
  .tabnav a:active:not(.on) { opacity: 1; background: var(--color-neutral-200); }
  .icon-btn { transition: background .12s; }
  .icon-btn:active { background: var(--color-neutral-300); }
  .btn { transition: filter .12s, transform .05s; }
  .btn:active { transform: scale(.98); filter: brightness(.95); }
  .amount-submit:active { transform: scale(.94); }
  .col { max-width:480px; margin:0 auto; min-height:100vh; padding: 0 0 104px; box-sizing:border-box; }
  .hdr { display:flex; align-items:center; justify-content:space-between; padding: var(--space-4) var(--space-4) var(--space-2); }
  .brand { font-family:var(--font-heading); font-size:22px; }
  .hdr-actions { display:flex; align-items:center; gap:4px; min-width:0; }
  /* max-width in vw, not px: the brand is fixed-width, so the ledger name is the only thing
     that can give, and it must give before the avatar is pushed off a 320px screen. */
  .hdr-ledger {
    display:inline-flex; align-items:center; gap:5px; max-width:34vw;
    font-size:12px; padding:4px 10px; border-radius:999px; text-decoration:none;
    color:var(--color-text); background:var(--color-surface);
    border:1px solid color-mix(in srgb, var(--color-text) 12%, transparent);
  }
  /* Only the name gives when the header runs out of room — the icon must not be squashed
     out of existence, since it is the whole point of the distinction. */
  .hdr-ledger span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .hdr-ledger svg { flex:none; opacity:.75; }
  .hdr-ledger:active { transform:scale(0.97); }
  .hdr-actions form, .hdr-actions a { display:inline-flex; margin:0; }
  .avatar { width:34px; height:34px; border-radius:999px; background:var(--color-accent-100); color:var(--color-accent-700); border:none; cursor:pointer; font-family:var(--font-heading); font-size:13px; }
  .content { padding: 0 var(--space-4); display:flex; flex-direction:column; gap:var(--space-4); }
  .stack { display:flex; flex-direction:column; gap:var(--space-3); }
  .muted { color:var(--color-neutral-800); font-size:13px; }
  .empty { text-align:center; padding:var(--space-8) var(--space-4); color:var(--color-neutral-800); }
  /* .card from the design system defaults to flex-direction:column; force row + compact padding for list rows. */
  .row.card { flex-direction:row !important; align-items:center; gap:10px; padding:8px 12px; }
  .row-icon { width:30px; height:30px; border-radius:999px; background:var(--color-accent-100); color:var(--color-accent-700); display:grid; place-items:center; flex-shrink:0; }
  .row-icon.sage { background:var(--color-accent-2-100); color:var(--color-accent-2-700); }
  .row-icon.ink { background:var(--color-neutral-300); color:var(--color-neutral-800); }
  .row-main { flex:1; min-width:0; }
  .row-main .title { font-size:13px; font-weight:600; line-height:1.2; }
  .row-main .sub { font-size:11.5px; color:var(--color-neutral-800); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; line-height:1.2; }
  .row-amt { font-size:14px; font-weight:600; margin-inline: 4px 2px; }
  .icon-btn { background:none; border:none; color:var(--color-neutral-700); cursor:pointer; padding:6px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; }
  .icon-btn:hover { background:var(--color-neutral-200); }

  .amount-card { padding: var(--space-4) var(--space-4); }
  .amount-q { font-size:13px; color:var(--color-neutral-800); margin-bottom:6px; text-align:center; }
  .amount-row { display:flex; align-items:center; gap:10px; padding: 0 var(--space-2); }
  .amount-sym { font-family:var(--font-heading); font-size:30px; color:var(--color-accent-700); line-height:1; }
  .amount-input { border:none; background:transparent; font-family:var(--font-heading); font-size:38px; text-align:left; flex:1; min-width:0; outline:none; color:var(--color-text); padding:0; }
  .amount-submit { width:52px; height:52px; border-radius:999px; border:none; background:var(--color-accent); color:var(--color-bg); display:grid; place-items:center; cursor:pointer; flex-shrink:0; box-shadow: var(--shadow-sm); }
  .amount-submit:disabled { opacity:.45; cursor:not-allowed; box-shadow:none; }
  .cat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
  .cat-chip { border:1.5px solid var(--color-divider); background:var(--color-surface); border-radius:var(--radius-md); padding:12px 4px; display:flex; flex-direction:column; align-items:center; gap:6px; cursor:pointer; font-size:11.5px; color:var(--color-text); text-decoration:none; }
  .cat-chip.on { border-color:var(--color-accent); background:var(--color-accent-100); color:var(--color-accent-700); }
  .cat-chip.new { border-style:dashed; color:var(--color-neutral-800); }
  .pill-row { display:flex; gap:6px; flex-wrap:wrap; }
  /* The rule above sets display:flex, which outranks the browser's own [hidden]{display:none}
     — a class beats an attribute selector. Without this every parent's sub-category row shows
     at once, and you can light a pill under a parent that isn't even selected. */
  .pill-row.sub-row[hidden] { display:none; }
  .pill-btn { padding:6px 14px; border-radius:999px; border:1.5px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-size:12px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
  .pill-btn.on { background:var(--color-accent); color:var(--color-bg); border-color:transparent; }
  /* The add action shares the filter row. Outlined rather than filled, so it doesn't read as
     a selected filter sitting next to the real ones. */
  .pill-btn.act { color:var(--color-accent-700); border-color:var(--color-accent-400); font-weight:600; gap:4px; }
  .note-row { display:flex; gap:8px; }
  .note-row .input { flex:1; }

  .month-switch { display:flex; align-items:center; justify-content:space-between; }
  .month-switch .label { font-family:var(--font-heading); font-size:18px; }
  .total-card { padding: var(--space-4); text-align:center; }
  .total-card.accent { background:var(--color-accent-700); color:var(--color-bg); }
  .total-card.sage { background:var(--color-accent-2-700); color:var(--color-bg); }
  /* Earnings own the third series. The two accents are already spoken for (terracotta =
     spending, sage = investing), so income takes the neutral ramp — still a token, and the
     only ramp left that stays legible against both themes' backgrounds. */
  .total-card.ink { background:var(--color-neutral-700); color:var(--color-bg); }
  .total-card .big { font-family:var(--font-heading); font-size:32px; }
  .total-card .sub { font-size:13px; opacity:.85; }
  .cat-bar { padding: 10px 14px; }
  .cat-bar .top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
  .cat-bar .name { display:flex; align-items:center; gap:8px; font-size:14px; }
  .cat-bar .amt { font-size:13px; }
  .cat-bar .pct { font-size:11px; color:var(--color-neutral-800); margin-left:6px; }
  .bar { height:8px; background:var(--color-divider); border-radius:999px; margin-top:8px; overflow:hidden; }
  .bar > i { display:block; height:100%; background:var(--color-accent); border-radius:999px; }
  .bar.sage > i { background:var(--color-accent-2); }
  .bar.ink  > i { background:var(--color-neutral-700); }
  /* Budget states: at/under budget stays sage, over budget goes red. */
  .bar.under > i { background:var(--color-accent-2); }
  .bar.over  > i { background:#c0392b; }
  .cat-bar .budget-note { font-size:11px; color:var(--color-neutral-800); margin-top:5px; display:flex; justify-content:space-between; gap:8px; }
  .cat-bar .budget-note .over-amt { color:#c0392b; font-weight:600; }
  /* Category tree on /organise. One card per top-level category; children hang off a spine
     drawn with borders rather than ├─ glyphs, so it lines up at any font size. */
  .tree-node { padding: 10px 12px; gap:0; }
  .tree-head { display:flex; align-items:center; gap:4px; }
  .tree-head .tree-name { font-size:14px; font-weight:600; }
  /* Inline edit row — name always, budget on parents only. */
  .tree-row { display:flex; align-items:center; gap:6px; margin:0; flex:1; min-width:0; }
  .tree-row .input { flex:1; min-width:0; padding:6px 10px; font-size:13px; }
  .tree-row .budget-in { flex:0 0 62px; text-align:right; padding:6px 8px; }
  .tree-metaline { font-size:11px; color:var(--color-neutral-800); padding: 4px 2px 2px 26px; }
  .tree-ico { display:inline-flex; color:var(--color-accent-700); flex-shrink:0; }
  .tree-name { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .tree-meta { font-size:11px; color:var(--color-neutral-800); flex-shrink:0; }
  .tree-kid { position:relative; display:flex; align-items:center; gap:8px; padding-left:24px; min-height:32px; }
  .tree-kid .tree-name { font-size:13px; }
  .tree-kid .tree-ico { color:var(--color-neutral-700); }
  /* Elbow: down from the row above, then right into the icon. */
  .tree-kid::before { content:''; position:absolute; left:8px; top:0; height:50%; width:10px;
                      border-left:1.5px solid var(--color-divider);
                      border-bottom:1.5px solid var(--color-divider);
                      border-bottom-left-radius:6px; }
  /* Spine continuing past this row to the next sibling — .last is set server-side because the
     card can hold other markup after the children. */
  .tree-kid:not(.last)::after { content:''; position:absolute; left:8px; top:50%; bottom:0;
                                border-left:1.5px solid var(--color-divider); }

  /* Sub-category lines under a rolled-up parent bar. They always sum to the bar. */
  .cat-bar .sub-line { display:flex; justify-content:space-between; gap:8px; font-size:11.5px;
                       color:var(--color-neutral-800); margin-top:5px; padding-left:2px; }

  /* Invest tab: three-up active / archived / total summary.
     Same trap as .row.card — the design system's .card is flex-direction:column. */
  .split-card { display:flex; flex-direction:row !important; padding:0; overflow:hidden; text-align:center; }
  .split-card > div { flex:1; padding: var(--space-3) var(--space-2); min-width:0; }
  .split-card > div + div { border-left:1px solid color-mix(in srgb, var(--color-bg) 25%, transparent); }
  .split-card .k { font-size:11px; opacity:.85; text-transform:uppercase; letter-spacing:.05em; }
  /* Never break a number mid-digit — shrink the whole figure instead. Sized so a crore
     (₹1,50,25,000) still fits one line across a third of a 360px screen. */
  .split-card .v { font-family:var(--font-heading); font-size:clamp(11px, 3.6vw, 17px); margin-top:2px; white-space:nowrap; }
  .split-card .n { font-size:10.5px; opacity:.75; }
  .row.card.archived { opacity:.62; }

  /* Year page — mode toggle + twelve-month column chart. */
  .year-seg { display:flex; width:100%; }
  .year-seg .seg-opt { flex:1; justify-content:center; font-size:13px; padding:8px 4px;
                       text-decoration:none; color:var(--color-text); }
  .year-seg .seg-opt.on { background:var(--color-accent); color:var(--color-bg); }
  /* The shared .seg-opt is written for the radio pattern (:has(input:checked)) and for the
     Year tab's links. The grouping toggle on /ledgers is two submit buttons, so it needs the
     UA button chrome off and a selected state of its own. Kept here, not in the shared sheet —
     that sheet is linked by every page including the landing one. */
  /* A <button> does not inherit colour or font from the page — it takes the UA's own
     ButtonText, which is black. Black on cream reads fine, so the light theme hid this; on the
     dark theme the ledger picker's names were black on near-black. Any card or row that is a
     button has to say this out loud. */
  button.card, button.row { color:var(--color-text); font-family:inherit; }
  .seg-opt[type=submit] {
    background:none; border:0; margin:0; font-family:inherit; font-size:13px;
    color:var(--color-text); -webkit-appearance:none; appearance:none;
  }
  .seg-opt[type=submit].on { background:var(--color-accent); color:var(--color-bg); }
  .seg-opt[type=submit]:active { transform:scale(0.97); }
  /* Year summary card = figures row + investment filter footer, stacked. */
  .yearcard { flex-direction:column !important; padding:0; overflow:hidden; gap:0; }
  .yearcard .split-card { background:none; box-shadow:none; border-radius:0; }
  .invtoggle { display:flex; align-items:center; gap:4px; padding:8px 10px;
               border-top:1px solid color-mix(in srgb, var(--color-bg) 25%, transparent); }
  .invtoggle .lbl { font-size:11px; opacity:.8; margin-right:auto; }
  .invtoggle .opt { font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none;
                    color:var(--color-bg); opacity:.75;
                    border:1px solid color-mix(in srgb, var(--color-bg) 35%, transparent); }
  .invtoggle .opt.on { background:var(--color-bg); color:var(--color-accent-700); opacity:1; border-color:transparent; }
  /* Net line between the figures row and the investment filter. */
  .netrow { display:flex; align-items:baseline; gap:8px; padding:7px 10px;
            border-top:1px solid color-mix(in srgb, var(--color-bg) 25%, transparent); }
  .netrow .lbl { font-size:11px; opacity:.8; margin-right:auto; }
  .netrow .v { font-family:var(--font-heading); font-size:15px; }
  .ychart { padding: var(--space-3) var(--space-3) var(--space-2); }
  .ylegend { display:flex; gap:14px; font-size:11.5px; color:var(--color-neutral-800); margin-bottom:10px; }
  .ylegend span { display:inline-flex; align-items:center; gap:5px; }
  .ylegend .sw { width:9px; height:9px; border-radius:3px; display:inline-block; }
  .ylegend .sw.exp { background:var(--color-accent); }
  .ylegend .sw.inv { background:var(--color-accent-2); }
  .ylegend .sw.ern { background:var(--color-neutral-700); }
  .ygrid { display:grid; grid-template-columns:repeat(12, 1fr); gap:3px; height:132px; align-items:end; }
  .ycol { display:flex; flex-direction:column; height:100%; justify-content:flex-end; gap:5px;
          text-decoration:none; color:inherit; border-radius:var(--radius-sm); }
  a.ycol:active { background:var(--color-neutral-200); }
  .ystack { flex:1; display:flex; align-items:flex-end; justify-content:center; gap:2px; }
  /* 5px, not 6 — three series per column have to clear twelve columns on a 320px screen. */
  .ystack i { width:5px; display:block; border-radius:2px 2px 0 0; background:var(--color-accent); }
  .ystack i.inv { background:var(--color-accent-2); }
  .ystack i.ern { background:var(--color-neutral-700); }
  .ylab { font-size:9.5px; text-align:center; color:var(--color-neutral-800); }
  /* Drawer nav row — the entry point to the year page. */
  .drawer-nav { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:var(--radius-md);
                background:var(--color-surface); color:var(--color-text); text-decoration:none; font-size:14px; }
  .drawer-nav .ico { color:var(--color-accent-700); display:inline-flex; }
  .drawer-nav .chev { margin-left:auto; color:var(--color-neutral-700); }
  .tag-archived { font-size:10px; padding:1px 7px; border-radius:999px; background:var(--color-neutral-300); color:var(--color-neutral-800); margin-left:6px; vertical-align:1px; }
  .day-hdr { font-family:var(--font-heading); font-size:14px; color:var(--color-neutral-800); margin: var(--space-3) 2px var(--space-2); }

  /* Toasts drop in from the top and leave upwards. env() keeps them clear of a notch —
     the viewport is viewport-fit=cover, so without it they'd sit under the status bar.
     z-index outranks the profile drawer (201): half the actions that flash a toast are
     drawer forms, and a toast rendered behind the drawer may as well not exist. */
  .toast { position:fixed; left:50%; top: calc(env(safe-area-inset-top, 0px) + 12px); transform:translateX(-50%); padding:10px 18px; border-radius:999px; font-size:13px; z-index:250; max-width: calc(100% - 32px); text-align:center; box-shadow: var(--shadow-md); animation: toast-life 2.2s forwards; }
  .toast.success { background:var(--color-accent-2-700); color:var(--color-bg); }
  .toast.error   { background:var(--color-accent-700); color:var(--color-bg); animation: toast-life-long 3.6s forwards; }
  @keyframes toast-life {
    0%{opacity:0;transform:translate(-50%,-10px);} 10%{opacity:1;transform:translate(-50%,0);}
    80%{opacity:1;transform:translate(-50%,0);} 100%{opacity:0;transform:translate(-50%,-10px);}
  }
  @keyframes toast-life-long {
    0%{opacity:0;transform:translate(-50%,-10px);} 5%{opacity:1;transform:translate(-50%,0);}
    90%{opacity:1;transform:translate(-50%,0);} 100%{opacity:0;transform:translate(-50%,-10px);}
  }

  .tabnav { position:fixed; left:50%; bottom:16px; transform:translateX(-50%); width:calc(100% - 32px); max-width:448px; background:var(--color-surface); border-radius:999px; padding:6px; display:flex; gap:2px; box-shadow:var(--shadow-md); box-sizing:border-box; }
  /* Five tabs now — 10.5px and 2px side padding keep "Recurring" on one line at 320px. */
  .tabnav a { flex:1; min-width:0; padding:8px 2px; border-radius:999px; text-decoration:none; color:var(--color-text); font-size:10.5px; display:flex; flex-direction:column; align-items:center; gap:2px; opacity:.7; white-space:nowrap; overflow:hidden; }
  .tabnav a.on { background:var(--color-accent); color:var(--color-bg); opacity:1; }

  input[type="date"]::-webkit-calendar-picker-indicator { opacity:.6; }
  .select { padding:10px 12px; border-radius:var(--radius-sm); border:1px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-family:var(--font-body); font-size:14px; }
  .field-row { display:flex; gap:8px; }
  .field-row > * { flex:1; min-width:0; }

  /* Right-side profile drawer — slides in from the right, backdrop dim. */
  .drawer-backdrop { position:fixed; inset:0; background: color-mix(in srgb, #000 45%, transparent); opacity:0; pointer-events:none; transition: opacity .22s ease; z-index:200; }
  .drawer-backdrop.open { opacity:1; pointer-events:auto; }
  .drawer { position:fixed; top:0; right:0; bottom:0; width:min(340px, 92vw); background:var(--color-bg); box-shadow: var(--shadow-lg); transform: translateX(100%); transition: transform .25s ease; z-index:201; overflow-y:auto; display:flex; flex-direction:column; }
  .drawer.open { transform: translateX(0); }
  .drawer-hdr { padding: var(--space-4); border-bottom:1px solid var(--color-divider); display:flex; align-items:center; gap:12px; position:sticky; top:0; background:var(--color-bg); z-index:1; }
  .drawer-hdr .drawer-avatar { width:40px; height:40px; border-radius:999px; background:var(--color-accent-100); color:var(--color-accent-700); display:grid; place-items:center; font-family:var(--font-heading); font-size:16px; flex-shrink:0; }
  .drawer-hdr .drawer-who { flex:1; min-width:0; }
  .drawer-hdr .drawer-who .n { font-family:var(--font-heading); font-size:16px; }
  .drawer-hdr .drawer-who .e { font-size:12px; color:var(--color-neutral-800); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .drawer-body { padding: var(--space-4); display:flex; flex-direction:column; gap: var(--space-4); flex:1; }
  .drawer-body section { display:flex; flex-direction:column; gap:8px; }
  .drawer-body h4 { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--color-neutral-800); font-family:var(--font-body); font-weight:700; }
  .drawer-body hr { border:none; height:1px; background:var(--color-divider); margin:0; }
  .drawer-body a.plain-link { color:var(--color-accent-700); font-size:13px; text-decoration:underline; text-underline-offset:2px; }
  .row-form { display:flex; gap:6px; align-items:center; margin:0; }
  .row-form .input { flex:1; }
  .cat-row { display:flex; gap:6px; align-items:center; }
  .cat-row form { display:flex; gap:6px; align-items:center; margin:0; flex:1; }
  .cat-row .input { flex:1; padding: 6px 12px; font-size: 13px; }
  .cat-row .budget-in { flex:0 0 68px; text-align:right; padding: 6px 10px; }
  .cat-row.archived .input { opacity:.6; }
  .cat-icon-mini { width:26px; height:26px; border-radius:999px; background:var(--color-accent-100); color:var(--color-accent-700); display:grid; place-items:center; flex-shrink:0; }
  /* Collapsible <details> sections in the drawer */
  .drawer-body details > summary { list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; padding: 2px 0; }
  .drawer-body details > summary::-webkit-details-marker { display:none; }
  .drawer-body details > summary::after { content:''; width:8px; height:8px; border-right:2px solid var(--color-neutral-800); border-bottom:2px solid var(--color-neutral-800); transform: rotate(-45deg); transition: transform .18s; margin-right:4px; }
  .drawer-body details[open] > summary::after { transform: rotate(45deg); }
  .drawer-body details > .details-body { display:flex; flex-direction:column; gap:8px; margin-top:8px; }

  /* Confirmation modal — one shared native <dialog> reused for every destructive action + sign-out. */
  dialog.confirm { border:none; border-radius:var(--radius-md); padding:0; max-width:320px; width:calc(100% - 32px); background:var(--color-surface); color:var(--color-text); box-shadow:var(--shadow-lg); }
  dialog.confirm::backdrop { background: color-mix(in srgb, #000 55%, transparent); }
  dialog.confirm form { padding: var(--space-4); display:flex; flex-direction:column; gap:12px; margin:0; }
  dialog.confirm .dlg-title { font-family:var(--font-heading); font-size:18px; }
  dialog.confirm .dlg-body  { font-size:14px; color:var(--color-neutral-800); }
  dialog.confirm .dlg-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:8px; }
  .btn-danger { background:#c0392b; color:#fff; border:none; }
</style>
</head>
<body{$darkAttr}>
$sprite
<div class="col">
  <div class="hdr">
    <div class="brand">Open Ledger</div>
    <div class="hdr-actions">
      $ledgerTag
      <form method="post" action="/theme">$csrf<input type="hidden" name="back" value="{$backUri}"><button class="btn btn-icon" type="submit" aria-label="Toggle theme" style="color:var(--color-text);">$themeBtn</button></form>
      <button class="avatar" type="button" aria-label="Profile" onclick="openProfile()">$initial</button>
    </div>
  </div>
  <div class="content">$content</div>
</div>
HTML;

    if ($flash = consumeFlash()) {
        $cls = $flash['type'] === 'error' ? 'error' : 'success';
        echo "<div class='toast $cls'>" . h($flash['msg']) . "</div>";
    }

    $tabs = [
        ['add',       '/',          'plus',         'Add'],
        ['history',   '/history',   'list',         'History'],
        ['earn',      '/earn',      'wallet',       'Earn'],
        ['invest',    '/invest',    'trending-up',  'Invest'],
        ['recurring', '/recurring', 'repeat',       'Recurring'],
    ];
    echo '<nav class="tabnav">';
    foreach ($tabs as [$key, $href, $ic, $label]) {
        $on = $tab === $key ? ' class="on"' : '';
        echo "<a href=\"" . h($href) . "\"$on>" . icon($ic, 18) . h($label) . "</a>";
    }
    echo '</nav>';

    // Right-side profile drawer — triggered by the header avatar.
    renderProfileDrawer($db, $user, $requestUri);

    // Emitted only when there is actually a choice to make, so the id the header's onclick
    // reaches for exists exactly when something reaches for it.
    echo $ledgerDlg;

    // Shared confirmation dialog + trigger helper. Every destructive form / signout uses this.
    echo <<<DLG
<dialog id="confirm-dlg" class="confirm" aria-labelledby="dlg-title">
  <form method="post" id="confirm-form">
    <input type="hidden" name="_csrf" id="confirm-csrf" value="$csrfTok">
    <input type="hidden" name="id" id="confirm-id">
    <input type="hidden" name="back" id="confirm-back">
    <div class="dlg-title" id="dlg-title"></div>
    <div class="dlg-body" id="dlg-body"></div>
    <label id="confirm-extra-wrap" style="display:none; font-size:13px; align-items:center; gap:8px; cursor:pointer;">
      <input type="checkbox" name="cascade" value="1" id="confirm-extra-cb">
      <span id="confirm-extra-label"></span>
    </label>
    <div class="dlg-actions">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('confirm-dlg').close()">Cancel</button>
      <button type="submit" class="btn btn-danger" id="confirm-ok">Delete</button>
    </div>
  </form>
</dialog>
<script>
function askConfirm(opts) {
  var f = document.getElementById('confirm-form');
  f.action = opts.action;
  document.getElementById('confirm-id').value = opts.id || '';
  document.getElementById('confirm-back').value = opts.back || '';
  // Pre-filled from the page, so a caller only overrides it deliberately. Dropping it from
  // the per-row payloads takes a 32-char token off every one of a 200-row History page.
  if (opts.csrf) document.getElementById('confirm-csrf').value = opts.csrf;
  document.getElementById('dlg-title').textContent = opts.title || 'Are you sure?';
  document.getElementById('dlg-body').textContent  = opts.body  || '';
  document.getElementById('confirm-ok').textContent = opts.ok || 'Delete';
  document.getElementById('confirm-ok').className = 'btn ' + (opts.danger === false ? 'btn-primary' : 'btn-danger');
  var wrap = document.getElementById('confirm-extra-wrap');
  var cb   = document.getElementById('confirm-extra-cb');
  cb.checked = false;
  if (opts.extra) {
    document.getElementById('confirm-extra-label').textContent = opts.extra;
    wrap.style.display = 'flex';
  } else {
    wrap.style.display = 'none';
  }
  document.getElementById('confirm-dlg').showModal();
}
// Keeps every other query string the page is carrying — month, invest filter, year, mode —
// and drops only the row offset, because page 3 of "everyone" is not page 3 of one person.
function setWho(v) {
  var u = new URL(location.href);
  if (v && v !== '0') u.searchParams.set('who', v); else u.searchParams.delete('who');
  u.searchParams.delete('o');
  location.href = u.toString();
}
function openProfile() {
  document.getElementById('drawer-backdrop').classList.add('open');
  document.getElementById('drawer-panel').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeProfile() {
  document.getElementById('drawer-backdrop').classList.remove('open');
  document.getElementById('drawer-panel').classList.remove('open');
  document.body.style.overflow = '';
  if (location.hash === '#profile') history.replaceState(null, '', location.pathname + location.search);
}
// Auto-open when redirected back from a POST inside the drawer.
window.addEventListener('load', function () { if (location.hash === '#profile') openProfile(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeProfile(); });
</script>
</body></html>
DLG;
}

// Right-side drawer — replaces the old /manage page. All account/household controls live here.
function renderProfileDrawer(PDO $db, array $user, string $requestUri): void {
    $hid = (int)$user['household_id'];
    $iTypes = $db->prepare("SELECT * FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $iTypes->execute([$hid]); $iTypes = $iTypes->fetchAll();
    $canDeleteType = count($iTypes) > 1;
    $eCats = $db->prepare("SELECT * FROM earning_categories WHERE household_id = ? ORDER BY id");
    $eCats->execute([$hid]); $eCats = $eCats->fetchAll();
    $canDeleteECat = count($eCats) > 1;
    // How many earnings each category would orphan — shown in the delete confirmation.
    $s = $db->prepare("SELECT category_id, COUNT(*) n FROM earnings WHERE household_id = ? GROUP BY category_id");
    $s->execute([$hid]); $earnPerCat = array_column($s->fetchAll(), 'n', 'category_id');

    // Impact counts for the archive confirmation: how many entries move out of the active
    // view, and whether a recurring item will keep posting into the type after archiving.
    $s = $db->prepare("SELECT type, COUNT(*) n FROM investments WHERE household_id = ? GROUP BY type");
    $s->execute([$hid]); $invPerType = array_column($s->fetchAll(), 'n', 'type');
    $s = $db->prepare("SELECT type, COUNT(*) n FROM recurring WHERE household_id = ? AND kind = 'investment' GROUP BY type");
    $s->execute([$hid]); $recPerType = array_column($s->fetchAll(), 'n', 'type');

    $currency = $_SESSION['currency'] ?? '₹';
    $initial  = h(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)));
    $back     = h(strtok($requestUri, '#') . '#profile');

    ?>
    <div id="drawer-backdrop" class="drawer-backdrop" onclick="closeProfile()"></div>
    <aside id="drawer-panel" class="drawer" aria-label="Profile">
      <div class="drawer-hdr">
        <div class="drawer-avatar"><?= $initial ?></div>
        <div class="drawer-who">
          <div class="n"><?= h($user['name'] ?? 'You') ?></div>
          <div class="e"><?= h($user['email'] ?? '') ?></div>
        </div>
        <button class="icon-btn" type="button" aria-label="Close" onclick="closeProfile()"><?= icon('x', 18) ?></button>
      </div>

      <div class="drawer-body">
        <section>
          <a class="drawer-nav" href="/year">
            <span class="ico"><?= icon('calendar', 18) ?></span>
            <span>Yearly summary</span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
          <!-- Expense categories are managed entirely on /organise — rename, budget, nest,
               delete. Keeping a second copy of those controls here would mean two places to
               fix every time the rules change. -->
          <a class="drawer-nav" href="/organise">
            <span class="ico"><?= icon('tag', 18) ?></span>
            <span>Organise expense categories</span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
          <?php /* Sharing lives on its own page rather than in here. This drawer renders on
                   every request whether or not anyone opens it, and the invite link, the people
                   list and the ledger switcher would have cost three more queries per page for
                   a panel that starts closed. */ ?>
          <a class="drawer-nav" href="/ledgers">
            <span class="ico"><?= icon('users', 18) ?></span>
            <span>Ledgers &amp; sharing</span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
        </section>

        <hr>

        <details>
          <summary><h4>Investment types</h4></summary>
          <div class="details-body">
            <div class="muted" style="font-size:11.5px;">Archive a type when a scheme ends — its entries stay logged but drop out of active investments.</div>
            <?php foreach ($iTypes as $t): $isArch = (int)$t['archived'] === 1; ?>
              <div class="cat-row<?= $isArch ? ' archived' : '' ?>">
                <form method="post" action="/investment-types/update">
                  <?= csrfInput() ?>
                  <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                  <input type="hidden" name="back" value="<?= $back ?>">
                  <input class="input" name="name" value="<?= h($t['name']) ?>" maxlength="40">
                  <button class="icon-btn" type="submit" aria-label="Save"><?= icon('check', 15) ?></button>
                </form>
                <?php if ($isArch): ?>
                  <!-- Restoring only widens what's visible, so it goes straight through. -->
                  <form method="post" action="/investment-types/archive" style="margin:0; display:inline-flex;">
                    <?= csrfInput() ?>
                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                    <input type="hidden" name="back" value="<?= $back ?>">
                    <button class="icon-btn" type="submit" aria-label="Restore <?= h($t['name']) ?>"
                            title="Restore to active"><?= icon('archive-restore', 15) ?></button>
                  </form>
                <?php else:
                  $nInv = (int)($invPerType[$t['name']] ?? 0);
                  $nRec = (int)($recPerType[$t['name']] ?? 0);
                  $body = $nInv === 1 ? '1 investment moves out of the active view.'
                                      : "$nInv investments move out of the active view.";
                  $body .= ' Nothing is deleted — you can restore it any time.';
                  // The genuine surprise: a recurring item keeps auto-posting after archiving.
                  if ($nRec > 0) {
                      $body .= ' Heads up: ' . ($nRec === 1 ? '1 recurring item' : "$nRec recurring items")
                             . ' still post into this type, so new entries will keep arriving as archived.'
                             . ' Delete them on the Recurring tab to stop that.';
                  }
                ?>
                  <button type="button" class="icon-btn" aria-label="Archive <?= h($t['name']) ?>" title="Archive"
                          onclick='askConfirm(<?= h(json_encode([
                              "action" => "/investment-types/archive",
                              "id"     => (int)$t['id'],
                              "back"   => strtok($requestUri, '#') . '#profile',
                              "csrf"   => csrfToken(),
                              "title"  => "Archive " . $t['name'] . "?",
                              "body"   => $body,
                              "ok"     => "Archive",
                              "danger" => false,
                          ])) ?>)'><?= icon('archive', 15) ?></button>
                <?php endif; ?>
                <?php if ($canDeleteType): ?>
                  <button type="button" class="icon-btn" aria-label="Delete type"
                          onclick='askConfirm(<?= h(json_encode([
                              "action" => "/investment-types/delete",
                              "id"     => (int)$t['id'],
                              "back"   => strtok($requestUri, '#') . '#profile',
                              "csrf"   => csrfToken(),
                              "title"  => "Delete investment type?",
                              "body"   => "Existing investments of type '" . $t['name'] . "' stay logged.",
                              "ok"     => "Delete",
                          ])) ?>)'><?= icon('trash-2', 14) ?></button>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <form method="post" action="/investment-types" class="row-form" style="margin-top:6px;">
              <?= csrfInput() ?>
              <input type="hidden" name="back" value="<?= $back ?>">
              <input class="input" name="name" placeholder="e.g. Gold scheme" maxlength="40">
              <button class="btn btn-primary" type="submit">Add</button>
            </form>
          </div>
        </details>

        <hr>

        <details>
          <summary><h4>Earning categories</h4></summary>
          <div class="details-body">
            <div class="muted" style="font-size:11.5px;">Where money comes in from — salary, interest, anything else. Rename or delete freely; past earnings keep their amounts.</div>
            <?php foreach ($eCats as $c): ?>
              <div class="cat-row">
                <form method="post" action="/earning-categories/update">
                  <?= csrfInput() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="back" value="<?= $back ?>">
                  <div class="cat-icon-mini"><?= icon('wallet', 14) ?></div>
                  <input class="input" name="name" value="<?= h($c['name']) ?>" maxlength="50">
                  <button class="icon-btn" type="submit" aria-label="Save"><?= icon('check', 15) ?></button>
                </form>
                <?php if ($canDeleteECat):
                  $nEarn = (int)($earnPerCat[$c['id']] ?? 0);
                  $body  = $nEarn === 0
                      ? 'Nothing is logged under ' . $c['name'] . ' yet.'
                      : ($nEarn === 1 ? '1 earning stays' : "$nEarn earnings stay")
                        . ' logged under ' . $c['name'] . ' but becomes uncategorised.';
                ?>
                  <button type="button" class="icon-btn" aria-label="Delete earning category"
                          onclick='askConfirm(<?= h(json_encode([
                              "action" => "/earning-categories/delete",
                              "id"     => (int)$c['id'],
                              "back"   => strtok($requestUri, '#') . '#profile',
                              "csrf"   => csrfToken(),
                              "title"  => "Delete earning category?",
                              "body"   => $body,
                              "ok"     => "Delete",
                          ])) ?>)'><?= icon('trash-2', 14) ?></button>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <form method="post" action="/earning-categories" class="row-form" style="margin-top:6px;">
              <?= csrfInput() ?>
              <input type="hidden" name="back" value="<?= $back ?>">
              <input class="input" name="name" placeholder="e.g. Rent received" maxlength="50">
              <button class="btn btn-primary" type="submit">Add</button>
            </form>
          </div>
        </details>

        <div style="flex:1;"></div>

        <hr>

        <section>
          <a class="plain-link" href="/terms">Terms &amp; conditions</a>
        </section>

        <section>
          <button class="btn btn-danger btn-block" type="button"
                  onclick='askConfirm(<?= h(json_encode([
                      "action" => "/signout",
                      "csrf"   => csrfToken(),
                      "title"  => "Sign out?",
                      "body"   => "You will need to sign in with Google again.",
                      "ok"     => "Sign out",
                  ])) ?>)'>Sign out</button>
        </section>
      </div>
    </aside>
    <?php
}

// ─── Landing ────────────────────────────────────────────────────────
// Public marketing page at / for signed-out visitors. Deliberately has no
// Google script on it — the gsi/client load happens on /login only, so a
// visitor who never signs in makes zero third-party requests (which is the
// claim the trust section makes, so it had better be true).
//
// Illustrations are inline SVG drawn from the tokens: line work in
// currentColor, accents via var(--color-accent*). Nothing to download, and
// they flip with the theme for free. Dark comes from prefers-color-scheme
// re-using THEME_DARK_VARS — a signed-out visitor has no user row to read
// is_dark from.
function renderLanding(): void {
    $origin = originUrl();
    $repo   = 'https://github.com/xpertxyz/HomeLedger';
    ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Open Ledger — a free, open-source household ledger</title>
<?= metaHead($origin) ?>
<link rel="canonical" href="<?= h($origin) ?>/">
<link rel="stylesheet" href="/design-tokens/styles.css?v=<?= h(cssVersion()) ?>">
<?= themeBootScript() ?>
<style>
  /* :not([data-theme]) is what makes "system" the default and still lets an
     explicit light choice win on a dark OS — without it the media query would
     outrank the stored preference. */
  @media (prefers-color-scheme: dark) { :root:not([data-theme]) { <?= THEME_DARK_VARS ?> } }
  :root[data-theme="dark"] { <?= THEME_DARK_VARS ?> }

  /* The button shows the theme you'd switch *to*: moon while light, sun while dark. */
  .tt .sun { display:none; }
  @media (prefers-color-scheme: dark) {
    :root:not([data-theme]) .tt .sun { display:block; }
    :root:not([data-theme]) .tt .moon { display:none; }
  }
  :root[data-theme="dark"] .tt .sun { display:block; }
  :root[data-theme="dark"] .tt .moon { display:none; }
  /* overflow-x guards the .band full-bleed trick: 100vw counts the scrollbar
     and would otherwise open a sliver of horizontal scroll on desktop. */
  body { margin:0; background:var(--color-bg); overflow-x:hidden; }
  /* The mobile tap highlight is a rectangle over the element's box, which reads as a square
     flashing behind these pill buttons. Off for .btn only — and paired with the :active
     transform that replaces it, because a tappable with no :active state of its own still
     needs that highlight to feel pressed. The FAQ rows and the brand link are square-cornered
     and keep theirs. Same rules layout() applies inside the app, scoped just as narrowly. */
  .btn { -webkit-tap-highlight-color: transparent; transition: filter .12s, transform .05s; }
  .btn:active { transform: scale(.98); filter: brightness(.95); }
  .wrap { max-width:1000px; margin:0 auto; padding:0 var(--space-4); }
  .skip { position:absolute; left:-9999px; }
  .skip:focus { position:static; }

  /* Caprasimo at the stylesheet's fixed 42px overflows a 360px phone. */
  h1 { font-size:clamp(32px, 7.6vw, 54px); line-height:1.05; text-wrap:balance; margin:0; }
  h2 { font-size:clamp(24px, 4.4vw, 34px); line-height:1.12; text-wrap:balance; margin:0; }
  h3 { font-size:19px; margin:0; }
  p  { text-wrap:pretty; }

  .lp-nav { display:flex; align-items:center; gap:var(--space-3);
            padding:var(--space-4) 0; }
  .lp-nav .sp { margin-left:auto; }
  .lockup { display:flex; align-items:center; gap:9px; text-decoration:none; color:var(--color-text); }
  .lockup span { font-family:var(--font-heading); font-size:20px; line-height:1; }
  .quiet { color:var(--color-neutral-800); text-decoration:none; font-size:14px; }
  .quiet:hover { color:var(--color-accent-700); }

  .hero { display:grid; gap:var(--space-6); align-items:center;
          padding:var(--space-6) 0 var(--space-8); }
  .lede { font-size:17px; line-height:1.55; color:var(--color-neutral-800); max-width:46ch; }
  .cta-row { display:flex; flex-wrap:wrap; gap:var(--space-2); }
  .cta-row .btn { text-decoration:none; }
  .fine { font-size:12.5px; color:var(--color-neutral-700); margin:0; }

  .proof { display:flex; flex-wrap:wrap; gap:var(--space-2); padding-bottom:var(--space-8); }

  .feat { display:grid; gap:var(--space-4); align-items:center;
          padding:var(--space-6) 0; }
  .feat p { font-size:15px; line-height:1.6; color:var(--color-neutral-800); margin:var(--space-2) 0 0; max-width:52ch; }
  .art { width:100%; max-width:200px; margin:0 auto; color:var(--color-neutral-800); }
  /* One column: the drawing always leads, whichever side it takes on desktop. */
  .feat.flip .art { order:-1; }
  .art svg { width:100%; height:auto; display:block; }
  .hero .art { max-width:400px; color:var(--color-text); }

  .grid { display:grid; gap:var(--space-3);
          grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));
          padding:var(--space-4) 0 var(--space-8); }
  .grid .card { gap:var(--space-2); }
  .grid svg { color:var(--color-accent); }

  /* Full-bleed tint band without a wrapper element. */
  .band { background:var(--color-surface); padding:var(--space-8) 0;
          margin-inline:calc(50% - 50vw); padding-inline:calc(50vw - 50%); }
  .band > * { max-width:1000px; margin-inline:auto; }

  .faq { border-bottom:1px solid var(--color-divider); }
  .faq summary { cursor:pointer; list-style:none; padding:var(--space-3) 0;
                 font-family:var(--font-heading); font-size:16.5px; }
  .faq summary::-webkit-details-marker { display:none; }
  .faq summary::after { content:'+'; float:right; color:var(--color-accent); font-family:var(--font-body); }
  .faq[open] summary::after { content:'\2212'; }
  .faq p { font-size:14.5px; line-height:1.6; color:var(--color-neutral-800); margin:0 0 var(--space-3); }

  .close { text-align:center; padding:var(--space-8) 0; }
  .close .cta-row { justify-content:center; }
  .lp-foot { border-top:1px solid var(--color-divider); padding:var(--space-4) 0 var(--space-8);
             font-size:12.5px; color:var(--color-neutral-700); display:flex;
             flex-wrap:wrap; gap:var(--space-3); }

  @media (min-width: 820px) {
    .hero { grid-template-columns:1.05fr 0.95fr; gap:var(--space-8); padding:var(--space-8) 0; }
    .feat { grid-template-columns:200px 1fr; gap:var(--space-8); }
    .feat.flip { grid-template-columns:1fr 200px; }
    .feat.flip .art { order:2; }
    .art { max-width:none; }
  }
  @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } }
</style>
</head>
<body>
<?= SVG_SPRITE ?>
<a class="skip" href="#main">Skip to content</a>

<div class="wrap">
  <nav class="lp-nav">
    <a class="lockup" href="/">
      <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="var(--color-accent)"
           stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        <circle cx="12" cy="12" r="0.85" fill="var(--color-accent-2)" stroke="none"/>
      </svg>
      <span>Open Ledger</span>
    </a>
    <button class="btn btn-secondary btn-icon tt sp" type="button" onclick="toggleTheme()"
            aria-label="Switch between light and dark" title="Switch theme">
      <span class="moon"><?= icon('moon', 18) ?></span>
      <span class="sun"><?= icon('sun', 18) ?></span>
    </button>
    <a class="quiet" href="<?= h($repo) ?>">Source</a>
    <a class="btn btn-secondary" href="/login" style="text-decoration:none;">Sign in</a>
  </nav>
  <script>
    function toggleTheme() {
      var r = document.documentElement;
      var dark = r.dataset.theme
        ? r.dataset.theme === 'dark'
        : window.matchMedia('(prefers-color-scheme: dark)').matches;
      r.dataset.theme = dark ? 'light' : 'dark';
      try { localStorage.setItem('ol-theme', r.dataset.theme); } catch (e) {}
      paintStatusBar();
    }
  </script>
</div>

<main id="main" class="wrap">

  <section class="hero">
    <div style="display:flex;flex-direction:column;gap:var(--space-4);align-items:flex-start;">
      <h1>Where the money in a house actually goes.</h1>
      <p class="lede">A free, open-source ledger for the whole house. Log a spend in three
        taps, invite your family with one link, let the rent post itself, and let the
        year explain itself. Built for a phone, priced at nothing, yours to self-host.</p>
      <div class="cta-row">
        <a class="btn btn-primary" href="/login">Sign in with Google</a>
        <a class="btn btn-secondary" href="<?= h($repo) ?>">Read the source</a>
      </div>
      <p class="fine">No ads, no trackers, no billing page. Nothing to configure before you start.</p>
    </div>
    <div class="art">
      <svg viewBox="0 0 320 240" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" role="img"
           aria-label="A house above an open ledger, with coins beside it">
        <path d="M88 78 A72 54 0 0 1 232 78" stroke="var(--color-accent-2)"/>
        <path d="M132 88 V58 L160 36 L188 58 V88 Z"/>
        <path d="M152 88 V76 a8 8 0 0 1 16 0 V88"/>
        <path d="M95 105 h39 a26 26 0 0 1 26 26 v91 a19.5 19.5 0 0 0-19.5-19.5 H95 Z"
              stroke="var(--color-accent)"/>
        <path d="M225 105 h-39 a26 26 0 0 0-26 26 v91 a19.5 19.5 0 0 1 19.5-19.5 H225 Z"
              stroke="var(--color-accent)"/>
        <path d="M106 148 h36 M106 168 h24 M178 148 h36 M178 168 h24"/>
        <circle cx="160" cy="163.5" r="5.5" fill="var(--color-accent-2)" stroke="none"/>
        <circle cx="252" cy="62" r="14" stroke="var(--color-accent)"/>
        <circle cx="252" cy="62" r="5" fill="var(--color-accent-2)" stroke="none"/>
        <circle cx="64" cy="80" r="9" stroke="var(--color-accent-2)"/>
      </svg>
    </div>
  </section>

  <div class="proof">
    <span class="tag tag-accent">Open source</span>
    <span class="tag tag-neutral">No tracking</span>
    <span class="tag tag-accent-2">₹10,00,000, not ₹1,000,000</span>
    <span class="tag tag-neutral">PHP + MySQL, no build step</span>
    <span class="tag tag-neutral">Light &amp; dark</span>
  </div>

  <section class="feat">
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <g transform="translate(-5.7,-5.1) scale(1.08)">
          <path d="M14 40 h12 M6 54 h20 M16 68 h10" stroke="var(--color-accent-2)"/>
          <rect x="34" y="26" width="92" height="54" rx="16"/>
          <path d="M50 46 h34 M50 60 h20"/>
          <circle cx="120" cy="84" r="17" fill="var(--color-accent)" stroke="none"/>
          <path d="M120 76 v16 M112 84 h16" stroke="var(--color-bg)" stroke-width="2"/>
        </g>
      </svg>
    </div>
    <div>
      <h2>Three taps, then back to your evening.</h2>
      <p>Amount, category, done. The keypad opens on the amount and the category you
        used last is already picked, so the common case is a number and one tap.
        Attribute it to whoever spent it, add a note if it matters, or don't.</p>
    </div>
  </section>

  <section class="feat flip">
    <div>
      <h2>Categories that grow the way a house does.</h2>
      <p>Nest Rent and Maintenance under Household and their spending rolls up into
        the parent's bar. One level deep, on purpose — a tree you can hold in your
        head. Budgets sit on the parent and report what's left; they never block a spend.</p>
    </div>
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <g transform="translate(-7.9,-6) scale(1.1)">
          <circle cx="28" cy="60" r="11" stroke="var(--color-accent)"/>
          <path d="M40 60 h22 a10 10 0 0 0 10-10 v-12 a10 10 0 0 1 10-10 h18"/>
          <path d="M40 60 h60"/>
          <path d="M40 60 h22 a10 10 0 0 1 10 10 v12 a10 10 0 0 0 10 10 h18"/>
          <rect x="100" y="19" width="42" height="18" rx="9"/>
          <rect x="100" y="51" width="42" height="18" rx="9"/>
          <rect x="100" y="83" width="42" height="18" rx="9"/>
          <circle cx="110" cy="28" r="3" fill="var(--color-accent)" stroke="none"/>
          <circle cx="110" cy="60" r="3" fill="var(--color-accent-2)" stroke="none"/>
          <circle cx="110" cy="92" r="3" fill="var(--color-accent)" stroke="none"/>
        </g>
      </svg>
    </div>
  </section>

  <section class="feat">
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <g transform="translate(-13.6,-10.4) scale(1.17)">
          <path d="M114 58 A34 34 0 1 1 80 24" stroke="var(--color-accent)"/>
          <path d="M73 17 L81 24 L73 31" stroke="var(--color-accent)"/>
          <rect x="62" y="42" width="36" height="32" rx="8"/>
          <path d="M70 52 h20 M70 62 h12"/>
          <circle cx="90" cy="64" r="3" fill="var(--color-accent)" stroke="none"/>
          <circle cx="46" cy="104" r="3.5" fill="var(--color-accent-2)" stroke="none"/>
          <circle cx="80" cy="104" r="3.5" fill="var(--color-accent-2)" stroke="none"/>
          <circle cx="114" cy="104" r="3.5" fill="var(--color-accent-2)" stroke="none"/>
        </g>
      </svg>
    </div>
    <div>
      <h2>Rent doesn't need reminding.</h2>
      <p>Mark the rent, the EMI, the SIP, the salary as recurring and they post
        themselves on the day they fall due — expenses, earnings and investments
        alike. Come back after a month away and every missed period catches up in
        one sweep, dated correctly, in the right month.</p>
    </div>
  </section>

  <section class="feat flip">
    <div>
      <h2>The yearly bill, spread over the year.</h2>
      <p>Health insurance for twelve months, a domain for twenty-four — paid in one
        go, but used a month at a time. Enter the total and the length and Open
        Ledger posts an equal share into your category every month, back-filling the
        months that have already gone by and stopping itself after the last one.</p>
    </div>
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <!-- one payment up top, coming apart into equal months below; the two solid
             bars are the months already posted. Scaled about the centre of the box so
             it carries the same visual weight as the other four drawings. -->
        <g transform="translate(-12,-9) scale(1.15)">
        <rect x="52" y="14" width="56" height="34" rx="8"/>
        <circle cx="80" cy="31" r="7" stroke="var(--color-accent)"/>
        <path d="M60 21 h7 M93 41 h7"/>
        <path d="M80 48 v9 M74 53 l6 6 l6 -6" stroke="var(--color-accent)"/>
        <path d="M80 63 C80 73 28 72 28 82 M80 63 C80 73 54 72 54 82 M80 63 v19
                 M80 63 C80 73 106 72 106 82 M80 63 C80 73 132 72 132 82" opacity=".5"/>
        <rect x="22" y="84" width="12" height="21" rx="4" fill="var(--color-accent-2)" stroke="none"/>
        <rect x="48" y="84" width="12" height="21" rx="4" fill="var(--color-accent-2)" stroke="none"/>
        <rect x="74" y="84" width="12" height="21" rx="4"/>
        <rect x="100" y="84" width="12" height="21" rx="4"/>
        <rect x="126" y="84" width="12" height="21" rx="4"/>
        </g>
      </svg>
    </div>
  </section>

  <section class="feat">
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <g transform="translate(-17.6,-12.8) scale(1.22)">
          <path d="M84.7 24.3 A34 34 0 0 1 98 86.8" stroke="var(--color-accent-2)" stroke-width="2"/>
          <path d="M92.7 89.5 A34 34 0 0 1 46 59.2" stroke="var(--color-accent)" stroke-width="2"/>
          <path d="M46.3 53.3 A34 34 0 0 1 78.8 24" stroke-width="2"/>
          <path d="M80 18 v-5 M120 58 h5 M80 98 v5 M40 58 h-5"/>
          <circle cx="80" cy="58" r="4" fill="var(--color-accent-2)" stroke="none"/>
        </g>
      </svg>
    </div>
    <div>
      <h2>Earned, spent, invested — on one page.</h2>
      <p>A yearly summary in calendar year or the Indian financial year, with twelve
        months of earned against spent against invested and the saved line between
        them. Tap a month to open it in History. Amounts read ₹10,00,000, the way
        you'd say it.</p>
    </div>
  </section>

  <section class="feat flip">
    <div>
      <h2>One book, everyone's handwriting.</h2>
      <p>Send one link — it works once and dies in thirty minutes. Whoever opens it
        signs in and is in your ledger, and still keeps their own. Everyone reads
        everything; each person edits what they added; the owner can fix anything.
        A filter shows who's spending what.</p>
    </div>
    <div class="art">
      <svg viewBox="0 0 160 120" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <!-- two people, one open book between them, a one-shot link arriving -->
        <circle cx="34" cy="38" r="9" stroke="var(--color-accent)"/>
        <path d="M20 66 a14 14 0 0 1 28 0"/>
        <circle cx="126" cy="38" r="9" stroke="var(--color-accent-2)"/>
        <path d="M112 66 a14 14 0 0 1 28 0"/>
        <path d="M52 86 h24 a10 10 0 0 1 10 10 a10 10 0 0 1 10-10 h24 v18 a8 8 0 0 0-8-8 H94 a8 8 0 0 0-8 8 a8 8 0 0 0-8-8 H60 a8 8 0 0 0-8 8 Z"/>
        <path d="M60 94 h14 M60 100 h9 M100 94 h14 M100 100 h9" stroke-width="1.4"/>
        <path d="M52 38 h20 m8 0 h20" stroke-dasharray="3 5" stroke="var(--color-accent)"/>
        <circle cx="80" cy="38" r="7" stroke="var(--color-accent)"/>
        <path d="M80 34.5 v3.5 l2.5 2" stroke="var(--color-accent)"/>
      </svg>
    </div>
  </section>

  <h2 style="padding-top:var(--space-6);">Also in the book</h2>
  <div class="grid">
    <div class="card">
      <?= icon('list', 22) ?>
      <div class="card-title">History that adds up</div>
      <p class="card-body">Grouped by day with per-day totals, then a category
        breakdown for the month. Swipe left or right to change month.</p>
    </div>
    <div class="card">
      <?= icon('trending-up', 22) ?>
      <div class="card-title">Earnings and investments</div>
      <p class="card-body">Salary and interest on one tab; SIPs, stocks, FDs, gold and
        PPF on another. Retire a type to Archived and its entries stay logged.</p>
    </div>
    <div class="card">
      <?= icon('home', 22) ?>
      <div class="card-title">Everyone's, but yours</div>
      <p class="card-body">Up to ten people per ledger, and you keep a personal one
        too. Filter any tab by who spent it; names for kids and shared cards need
        no login at all.</p>
    </div>
    <div class="card">
      <?= icon('moon', 22) ?>
      <div class="card-title">Warm in both lights</div>
      <p class="card-body">Light and dark, per person, remembered. Add to Home Screen
        for a real icon and full-screen chrome on iOS and Android.</p>
    </div>
  </div>

  <div class="band">
    <div>
      <h2>Nothing is watching you use it.</h2>
      <p class="lede" style="max-width:60ch;">There is no analytics script, no ad SDK
        and no third-party pixel on this page or inside the app. The only outbound
        request Open Ledger ever makes is to Google, once, to check your sign-in
        token. That is the whole list.</p>
      <div class="card" style="margin:var(--space-4) 0;max-width:60ch;">
        <div class="card-kicker">Worth knowing</div>
        <p class="card-body">The instance at ledger.xpertxyz.com is <strong>not encrypted
          at rest</strong>. Anyone with database access could read your entries. If
          that isn't OK for your household, self-host it — PHP 8.1, MySQL, one daily
          cron line, no build step.</p>
      </div>
      <div class="cta-row">
        <a class="btn btn-primary" href="/login">Sign in with Google</a>
        <a class="btn btn-secondary" href="<?= h($repo) ?>#quick-start">Self-hosting guide</a>
      </div>
    </div>
  </div>

  <h2 style="padding-top:var(--space-8);">Before you sign in</h2>
  <div style="padding-top:var(--space-3);">
    <details class="faq" open>
      <summary>Is it really free?</summary>
      <p>Yes, and there's no paid tier to graduate to. It's open-source software
        running on a server that already existed.</p>
    </details>
    <details class="faq">
      <summary>Why only Google sign-in?</summary>
      <p>So there's no password to store, reset or leak. There's no signup form —
        you sign in and you're in your ledger.</p>
    </details>
    <details class="faq">
      <summary>Can I get my data out?</summary>
      <p>It's a plain MySQL database. Self-host and it never leaves your host in the
        first place.</p>
    </details>
    <details class="faq">
      <summary>Does it do currencies other than rupees?</summary>
      <p>Each ledger has its own symbol, set by its owner — and its own digit
        grouping, ₹10,00,000 or ₹1,000,000. Lakh-crore is what it was built for.</p>
    </details>
    <details class="faq">
      <summary>Can my family see my personal ledger?</summary>
      <p>No. Each ledger is its own book with its own people. Sharing one of yours
        means sending a link that works once and expires in thirty minutes — nobody
        gets in any other way.</p>
    </details>
    <details class="faq">
      <summary>What isn't in it?</summary>
      <p>Bank sync, receipt scanning, splitting bills with people outside the house,
        and encryption at rest. It's a ledger you type into.</p>
    </details>
  </div>

  <section class="close">
    <h2>Start the book tonight.</h2>
    <p class="lede" style="margin:var(--space-3) auto 0;">About thirty seconds to sign
      in. The categories are already there.</p>
    <div class="cta-row" style="margin-top:var(--space-4);">
      <a class="btn btn-primary" href="/login">Sign in with Google</a>
    </div>
  </section>

  <footer class="lp-foot">
    <a class="quiet" href="/terms">Terms &amp; conditions</a>
    <a class="quiet" href="<?= h($repo) ?>">Source on GitHub</a>
    <span style="margin-left:auto;">Built by <a class="quiet" href="https://xpertxyz.in"
      style="color:var(--color-accent-700);">XpertXYZ</a></span>
  </footer>

</main>
</body></html>
    <?php
}

// ─── Sign-in ────────────────────────────────────────────────────────
// Google Identity Services: One Tap auto-prompts, and the "Sign in with Google"
// button is Google's official standard rendering. Both submit the ID token to
// /signin along with a g_csrf_token cookie+field pair (double-submit CSRF).
function renderSignIn(): void {
    $sprite   = SVG_SPRITE;
    $clientId = h(GOOGLE_CLIENT_ID);
    // Dev-stub button only shows when placeholder client id AND APP_DEBUG=1 — matches server-side gate.
    $devStub  = isDevStubActive(GOOGLE_CLIENT_ID) && (getenv('APP_DEBUG') === '1');
    $csrf     = csrfInput();
    $flashHtml = '';
    if ($flash = consumeFlash()) {
        $cls = $flash['type'] === 'error' ? 'error' : '';
        $flashHtml = "<div class='toast $cls' style='position:static;transform:none;margin-top:12px;animation:none;display:inline-block;'>" . h($flash['msg']) . "</div>";
    }
    $devBlock = $devStub ? <<<DEV
    <div style="border-top:1px solid var(--color-divider);padding-top:12px;width:100%;">
      <div style="font-size:12px;color:var(--color-neutral-800);margin-bottom:8px;">Google client id not configured — dev mode</div>
      <form method="post" action="/signin">
        $csrf
        <input type="hidden" name="dev" value="1">
        <button class="btn btn-primary btn-block" type="submit">Continue as dev user</button>
      </form>
    </div>
DEV
    : '';
    $origin = originUrl();
    $meta = metaHead($origin);
    $dark = THEME_DARK_VARS;
    $boot = themeBootScript();
    $cssV = cssVersion();

    echo <<<HTML
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Open Ledger — Sign in</title>
$meta
<link rel="stylesheet" href="/design-tokens/styles.css?v={$cssV}">
$boot
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
  /* Signed-out visitor has no is_dark row to read, so follow the OS — unless
     they overrode it with the toggle on the landing page, which the inline
     script above replays from localStorage. Same constant the authed layout
     injects inline. */
  @media (prefers-color-scheme: dark) { :root:not([data-theme]) { $dark } }
  :root[data-theme="dark"] { $dark }
  body { margin:0; background:var(--color-bg); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .toast { padding:8px 14px; border-radius:999px; background:var(--color-accent-700); color:var(--color-bg); font-size:13px; }
</style>
</head>
<body>
$sprite
<div style="width:100%;max-width:340px;padding:var(--space-4);">
  <div class="card elev-lg" style="padding:var(--space-6);align-items:center;text-align:center;gap:var(--space-4);">
    <!-- Lockup drawn inline rather than <img src="…wordmark.svg">: an SVG loaded via <img>
         renders in isolation and cannot fetch the Google-hosted Caprasimo, so the name fell
         back to a generic serif. Inline it also tracks the theme and the app-icon artwork. -->
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
      <svg viewBox="0 0 24 24" width="62" height="62" fill="none" stroke="var(--color-accent)"
           stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        <circle cx="12" cy="12" r="0.85" fill="var(--color-accent-2)" stroke="none"/>
      </svg>
      <span style="font-family:var(--font-heading);font-size:29px;line-height:1;color:var(--color-text);">Open Ledger</span>
    </div>
    <div style="font-size:14px;color:var(--color-neutral-800);">Track household expenses and investments together.</div>

    <div id="g_id_onload"
         data-client_id="$clientId"
         data-login_uri="/signin"
         data-auto_prompt="true"
         data-context="signin"
         data-itp_support="true"></div>
    <div class="g_id_signin"
         data-type="standard"
         data-theme="outline"
         data-shape="pill"
         data-size="large"
         data-text="signin_with"
         data-logo_alignment="left"></div>

    $devBlock
    $flashHtml
  </div>
  <div style="display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; margin-top:14px; font-size:12px; color:var(--color-neutral-800);">
    <a href="/" style="color:inherit; text-decoration:underline; text-underline-offset:2px;">What is Open Ledger?</a>
    <a href="/terms" style="color:inherit; text-decoration:underline; text-underline-offset:2px;">Terms &amp; conditions</a>
    <span>Built by <a href="https://xpertxyz.in" style="color:var(--color-accent-700); text-decoration:none;">XpertXYZ</a></span>
  </div>
</div>
</body></html>
HTML;
}

// ─── Add ────────────────────────────────────────────────────────────
function renderAdd(PDO $db, array $user): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = $cats->fetchAll();
    $mems = $db->prepare("SELECT * FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();

    // Sticky category: default to the last one this household used; falls back to first if none.
    $lastCat = $db->prepare("SELECT category_id FROM expenses WHERE household_id = ? ORDER BY id DESC LIMIT 1");
    $lastCat->execute([$hid]);
    $defaultCat = (int)($lastCat->fetchColumn() ?: ($cats[0]['id'] ?? 0));
    $selectedCat = (int)($_GET['cat'] ?? $defaultCat);
    // Default to your own name. It used to be whichever member sorted first, which in a
    // shared ledger meant the Add screen opened pre-filled with somebody else's name.
    $uid     = (int)$user['id'];
    $mineIds = attributableIds($mems, $uid, $user['role'] ?? ROLE_MEMBER);
    $ownMem  = 0;
    foreach ($mems as $m) if (isset($m['user_id']) && (int)$m['user_id'] === $uid) $ownMem = (int)$m['id'];
    $selectedMem = (int)($_GET['mem'] ?? ($ownMem ?: ($mineIds[0] ?? 0)));
    if (!in_array($selectedMem, $mineIds, true)) $selectedMem = $ownMem ?: ($mineIds[0] ?? 0);
    $showNewCat  = isset($_GET['newcat']);

    // The grid holds top-level categories only; children hang off their parent in a pill row
    // that appears when that parent is picked. A sticky child selection therefore has to light
    // up its parent's chip as well as its own pill.
    $kids = [];
    foreach ($cats as $c) if (!empty($c['parent_id'])) $kids[(int)$c['parent_id']][] = $c;
    $byId = array_column($cats, null, 'id');
    // The sticky default is read off the last expense, which may name a category that has since
    // been deleted — easy to do now that /organise puts delete one tap away. Without this the
    // grid shows nothing selected while the hidden field still holds the dead id, and the next
    // expense lands uncategorised for no visible reason.
    if ($selectedCat && !isset($byId[$selectedCat])) $selectedCat = 0;
    $selectedParent = (int)($byId[$selectedCat]['parent_id'] ?? 0) ?: $selectedCat;
    $cats = array_values(array_filter($cats, fn($c) => empty($c['parent_id'])));
    if (!$selectedCat) $selectedCat = $selectedParent = (int)($cats[0]['id'] ?? 0);

    // Selected category sorts first, rest keep their original order.
    if ($selectedParent) {
        usort($cats, function ($a, $b) use ($selectedParent) {
            $aSel = $a['id'] == $selectedParent ? 0 : 1;
            $bSel = $b['id'] == $selectedParent ? 0 : 1;
            return $aSel <=> $bSel;
        });
    }

    $currency = $_SESSION['currency'] ?? '₹';

    ob_start();
    ?>
    <form method="post" action="/expenses" class="stack">
      <?= csrfInput() ?>
      <div class="card elev-sm amount-card">
        <div class="amount-q">How much did you spend?</div>
        <div class="amount-row">
          <span class="amount-sym"><?= h($currency) ?></span>
          <input class="amount-input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="0" autofocus
                 oninput="document.getElementById('add-btn').disabled = !(parseFloat(this.value) > 0)">
          <button class="amount-submit" id="add-btn" type="submit" aria-label="Add expense" disabled><?= icon('plus', 22) ?></button>
        </div>
      </div>

      <div class="note-row">
        <input class="input" name="note" placeholder="Add a note (optional)" maxlength="200">
        <input class="input" name="date" type="date" value="<?= h(today()) ?>" style="flex:0 0 auto; width:auto;">
      </div>

      <input type="hidden" name="category_id" id="cat-input" value="<?= (int)$selectedCat ?>">

      <?php foreach ($kids as $pid => $list): ?>
        <!-- One row per parent, all rendered up front and toggled client-side — picking a
             category shouldn't cost a page load. "General" posts the parent's own id.
             Sits ABOVE the grid on purpose: the amount field is autofocused, so on a phone
             the keyboard covers the bottom of the screen and anything below the grid is
             unreachable without dismissing it first. -->
        <div class="pill-row sub-row" id="sub-<?= (int)$pid ?>" <?= $pid == $selectedParent ? '' : 'hidden' ?>>
          <button type="button" class="pill-btn<?= $selectedCat == $pid ? ' on' : '' ?>"
                  data-cat="<?= (int)$pid ?>" onclick="pickSub(this)">General</button>
          <?php foreach ($list as $k): ?>
            <button type="button" class="pill-btn<?= $selectedCat == $k['id'] ? ' on' : '' ?>"
                    data-cat="<?= (int)$k['id'] ?>" onclick="pickSub(this)"><?= h($k['name']) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="cat-grid">
        <?php foreach ($cats as $c): ?>
          <button type="button" class="cat-chip<?= $c['id'] == $selectedParent ? ' on' : '' ?>"
                  data-cat="<?= (int)$c['id'] ?>" onclick="pickCat(this)">
            <?= icon($c['icon'], 21) ?>
            <span><?= h($c['name']) ?><?= isset($kids[(int)$c['id']]) ? ' <span style="opacity:.55">▾</span>' : '' ?></span>
          </button>
        <?php endforeach; ?>
        <?php if ($showNewCat): ?>
          <div class="cat-chip new" style="grid-column: span 3; flex-direction:row; gap:6px;">
            <input class="input" name="_newcat" placeholder="New category name" form="new-cat-form" maxlength="50" style="flex:1;">
            <button class="btn btn-primary" type="submit" form="new-cat-form">Add</button>
          </div>
        <?php else: ?>
          <a href="/add?newcat=1" class="cat-chip new"><?= icon('plus', 21) ?><span>New</span></a>
        <?php endif; ?>
      </div>

      <?php if (count($mineIds) > 1): ?>
        <input type="hidden" name="member_id" id="mem-input" value="<?= (int)$selectedMem ?>">
        <div class="pill-row">
          <?php foreach ($mems as $m): if (!in_array((int)$m['id'], $mineIds, true)) continue; ?>
            <?php /* The sweep is scoped to this row: a global .pill-row query would also clear
                     the sub-category pills sitting in their own row above. Keep this a PHP
                     comment — an HTML one here lands inside the tag, and the browser ends the
                     tag at its `>`, which turns the onclick into visible text. */ ?>
            <button type="button" class="pill-btn<?= $m['id'] == $selectedMem ? ' on' : '' ?>"
                    onclick="document.getElementById('mem-input').value=<?= (int)$m['id'] ?>;this.parentNode.querySelectorAll('.pill-btn').forEach(e=>e.classList.remove('on'));this.classList.add('on');">
              <?= h($m['name']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      <?php elseif ($selectedMem): ?>
        <?php /* One name you may file under, so there is nothing to choose — send it silently. */ ?>
        <input type="hidden" name="member_id" value="<?= (int)$selectedMem ?>">
      <?php endif; ?>
    </form>

    <script>
    function pickCat(el) {
      document.querySelectorAll('.cat-grid .cat-chip').forEach(e => e.classList.remove('on'));
      el.classList.add('on');
      document.getElementById('cat-input').value = el.dataset.cat;
      // Jump the picked card to the top-left of the grid, matching where the server puts the
      // sticky category on a fresh load — so the selection sits in the same place either way.
      var grid = el.parentNode;
      if (el !== grid.firstElementChild) grid.insertBefore(el, grid.firstElementChild);
      // Clear every sub-row, not just the one being replaced, so exactly one pill is ever lit
      // anywhere — otherwise a hidden row keeps a stale selection from an earlier tap.
      document.querySelectorAll('.sub-row').forEach(r => r.hidden = true);
      document.querySelectorAll('.sub-row .pill-btn').forEach(b => b.classList.remove('on'));
      var row = document.getElementById('sub-' + el.dataset.cat);
      // Reopening a parent resets to "General" — the previously picked child would otherwise
      // stay lit while the hidden input now holds the parent.
      if (row) {
        row.hidden = false;
        row.querySelectorAll('.pill-btn')[0].classList.add('on');
      }
    }
    function pickSub(el) {
      document.getElementById('cat-input').value = el.dataset.cat;
      // Clear across ALL sub-rows, not just this one: only one category can be selected, so a
      // pill left lit under another parent is a lie about what will be saved.
      document.querySelectorAll('.sub-row .pill-btn').forEach(b => b.classList.remove('on'));
      el.classList.add('on');
      // Picking a sub-category selects its parent card too — the child belongs to it, and the
      // grid is where you read back what you chose.
      var pid = el.closest('.sub-row').id.slice(4);   // "sub-<parentId>"
      document.querySelectorAll('.cat-grid .cat-chip').forEach(c => c.classList.toggle('on', c.dataset.cat === pid));
    }
    </script>

    <?php if ($showNewCat): ?>
      <form method="post" action="/categories" id="new-cat-form" style="display:none;">
        <?= csrfInput() ?>
        <input type="hidden" name="back" value="/add">
        <input type="hidden" name="name" id="new-cat-name">
      </form>
      <script>
        (function(){
          var f = document.getElementById('new-cat-form');
          f.addEventListener('submit', function(){
            document.getElementById('new-cat-name').value = document.querySelector('input[name="_newcat"]').value;
          });
        })();
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'add', $content, '/');
}

// ─── History ────────────────────────────────────────────────────────
function renderHistory(PDO $db, array $user, int $offset): void {
    $hid = (int)$user['household_id'];
    $uid = (int)$user['id'];
    if ($offset < 0)   $offset = 0;
    if ($offset > 600) $offset = 600; // sanity cap ~50 years
    $anchor     = (new DateTimeImmutable('first day of this month 00:00:00'))->modify("-{$offset} months");
    $monthStart = $anchor->format('Y-m-d');
    $monthEnd   = $anchor->modify('+1 month')->format('Y-m-d');
    $label      = $anchor->format('F Y');

    // Pagination for the transaction list. Aggregates (total, breakdown) still cover the
    // whole month via a separate cheap SUM; only the row list is capped.
    // The upper clamp matters: OFFSET is walked, not seeked, so ?o=9999999 would have MySQL
    // step through ten million index entries and discard every one of them.
    $pageSize = 200;
    $rowOffset = min(100000, max(0, (int)($_GET['o'] ?? 0)));

    // Filter by who spent it. Validated against this household's members, so a crafted id
    // can neither name someone else's member nor survive as a filter that matches nothing.
    $who = (int)($_GET['who'] ?? 0);
    $who = $who > 0 ? (int)(ownedId($db, 'members', $hid, $who) ?? 0) : 0;
    [$whoSql,  $whoBind]  = whoWhere($who);
    [$whoSqlE, $whoBindE] = whoWhere($who, 'e');
    // Appended to every link this page emits, so the filter survives month navigation, paging,
    // and the round trip through an edit or a delete.
    $whoQ = $who > 0 ? '&amp;who=' . $who : '';

    // Monthly aggregates — one indexed SUM per query, no fetchAll of the whole month.
    $sumStmt = $db->prepare(
        "SELECT COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total
         FROM expenses WHERE household_id = ? AND `date` >= ? AND `date` < ?$whoSql"
    );
    $sumStmt->execute([$hid, $monthStart, $monthEnd, ...$whoBind]);
    $agg = $sumStmt->fetch();
    $entryCount = (int)$agg['n'];
    $total      = (float)$agg['total'];

    // Category breakdown from an indexed GROUP BY, not from PHP-side accumulation.
    // ponytail: a budgeted category with zero spend this month is absent from this grouping,
    // so it shows no bar. Under-budget-with-no-spend needs no warning; if a full "every budget,
    // spent or not" view is wanted later, LEFT JOIN from categories instead of from expenses.
    // Grouped per category, then folded onto parents in PHP — a sub-category's spend belongs
    // on its parent's bar and against its parent's budget.
    $catStmt = $db->prepare(
        "SELECT c.id AS cid, COALESCE(c.name, 'Uncategorised') AS name,
                COALESCE(c.icon, 'tag') AS icon, COALESCE(c.budget, 0) AS budget,
                p.id AS pid, p.name AS pname, p.icon AS picon, p.budget AS pbudget,
                SUM(e.amount) AS amt
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id AND c.household_id = e.household_id
         LEFT JOIN categories p ON p.id = c.parent_id AND p.household_id = c.household_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE
         GROUP BY c.id, c.name, c.icon, c.budget, p.id, p.name, p.icon, p.budget"
    );
    $catStmt->execute([$hid, $monthStart, $monthEnd, ...$whoBindE]);
    $byCat = rollupCategories($catStmt->fetchAll());

    // Household budget = sum of every top-level category budget, including ones with no spend
    // this month. Children are held at 0 budget, but scope it anyway so a stale row can't double-count.
    $budStmt = $db->prepare("SELECT COALESCE(SUM(budget), 0) FROM categories WHERE household_id = ? AND parent_id IS NULL");
    $budStmt->execute([$hid]);
    $budgetTotal = (float)$budStmt->fetchColumn();

    // Paginated transaction list — LIMIT + OFFSET on the (household_id, date) index.
    $rows = $db->prepare(
        "SELECT e.*, c.name AS cat_name, c.icon AS cat_icon, m.name AS mem_name
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id
         LEFT JOIN members m ON m.id = e.member_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE
         ORDER BY e.`date` DESC, e.id DESC
         LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute([$hid, $monthStart, $monthEnd, ...$whoBindE]);
    $expenses = $rows->fetchAll();

    // For the edit-expense modal.
    $catList = $db->prepare("SELECT id, name, parent_id FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $catList->execute([$hid]); $catList = categoryTree($catList->fetchAll());
    $memList = $db->prepare("SELECT id, name, user_id FROM members WHERE household_id = ? ORDER BY id");
    $memList->execute([$hid]); $memList = $memList->fetchAll();

    ob_start();
    ?>
    <?= whoFilterRow($db, $hid, $memList, $who) ?>
    <div class="month-switch">
      <a href="/history?m=<?= $offset + 1 ?><?= $whoQ ?>" class="btn btn-icon" aria-label="Previous month"><?= icon('chevron-left', 20) ?></a>
      <div class="label"><?= h($label) ?></div>
      <?php if ($offset > 0): ?>
        <a href="/history?m=<?= $offset - 1 ?><?= $whoQ ?>" class="btn btn-icon" aria-label="Next month"><?= icon('chevron-right', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
      <?php endif; ?>
    </div>

    <?php if ($entryCount === 0): ?>
      <div class="empty">No expenses this month.</div>
    <?php else: ?>
      <div class="card total-card accent">
        <div class="big"><?= h(fmt($total)) ?></div>
        <div class="sub"><?= $entryCount ?> <?= $entryCount === 1 ? 'entry' : 'entries' ?></div>
        <?php if ($budgetTotal > 0): $left = $budgetTotal - $total; ?>
          <div class="sub" style="margin-top:4px;">
            <?= h(fmt($budgetTotal)) ?> budgeted ·
            <?php if ($left >= 0): ?>
              <?= h(fmt($left)) ?> left
            <?php else: ?>
              <strong><?= h(fmt(-$left)) ?> over</strong>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="stack">
        <?php foreach ($byCat as $c):
          $amt    = (float)$c['amt'];
          $budget = (float)$c['budget'];
          $pct    = $total > 0 ? ($amt / $total) * 100 : 0;
          // With a budget the bar tracks budget consumption, not share of spend.
          $barPct = $budget > 0 ? min(100, ($amt / $budget) * 100) : $pct;
          $barCls = $budget > 0 ? ($amt > $budget ? ' over' : ' under') : '';
        ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name"><?= icon($c['icon'], 18) ?> <?= h($c['name']) ?></div>
              <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
            </div>
            <div class="bar<?= $barCls ?>"><i style="width: <?= number_format(max(2, $barPct), 2) ?>%"></i></div>
            <?php foreach ($c['children'] as $k): ?>
              <div class="sub-line">
                <span>↳ <?= h($k['name']) ?></span>
                <span><?= h(fmt((float)$k['amt'])) ?></span>
              </div>
            <?php endforeach; ?>
            <?php if ($budget > 0): $left = $budget - $amt; ?>
              <div class="budget-note">
                <span><?= h(fmt($budget)) ?> budget · <?= number_format(($amt / $budget) * 100, 0) ?>% used</span>
                <?php if ($left >= 0): ?>
                  <span><?= h(fmt($left)) ?> left</span>
                <?php else: ?>
                  <span class="over-amt"><?= h(fmt(-$left)) ?> over</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php
      $byDay = [];
      foreach ($expenses as $e) { $byDay[$e['date']][] = $e; }
      foreach ($byDay as $day => $entries):
        $dayLabel = (new DateTimeImmutable($day))->format('D, M j');
        $dayTotal = array_sum(array_map(fn($x) => (float)$x['amount'], $entries));
      ?>
        <div class="day-hdr" style="display:flex; justify-content:space-between; align-items:baseline;">
          <span><?= h($dayLabel) ?></span>
          <span style="font-family:var(--font-body); font-size:12px; font-weight:600; color:var(--color-neutral-800);"><?= h(fmt($dayTotal)) ?></span>
        </div>
        <div class="stack">
          <?php foreach ($entries as $e): ?>
            <?php $rowJson = json_encode([
                'id'          => (int)$e['id'],
                'amount'      => (string)$e['amount'],
                'date'        => $e['date'],
                'category_id' => (int)($e['category_id'] ?? 0),
                'member_id'   => (int)($e['member_id'] ?? 0),
                'note'        => (string)($e['note'] ?? ''),
            ]); ?>
            <div class="card elev-sm row">
              <div class="row-icon"><?= icon($e['cat_icon'] ?? 'tag', 16) ?></div>
              <div class="row-main">
                <div class="title"><?= h($e['cat_name'] ?? 'Uncategorised') ?></div>
                <div class="sub"><?= h(trim(($e['note'] ?? '') . ($e['note'] && $e['mem_name'] ? ' · ' : '') . ($e['mem_name'] ?? ''))) ?></div>
              </div>
              <div class="row-amt"><?= h(fmt((float)$e['amount'])) ?></div>
              <?php if (mayEdit($e, $user)): ?>
                <button class="icon-btn" type="button" aria-label="Edit"
                        onclick='openEditExpense(<?= h($rowJson) ?>)'>
                  <?= icon('edit', 15) ?>
                </button>
                <button class="icon-btn" type="button" aria-label="Delete"
                        onclick='askConfirm(<?= h(json_encode([
                            "action" => "/expenses/delete",
                            "id"     => (int)$e['id'],
                            "back"   => "/history?m=$offset" . ($who > 0 ? "&who=$who" : ""),
                            "title"  => "Delete expense?",
                            "body"   => fmt((float)$e['amount']) . ' — ' . ($e['cat_name'] ?? 'Uncategorised'),
                            "ok"     => "Delete",
                        ])) ?>)'>
                  <?= icon('trash-2', 15) ?>
                </button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <?php
      $shown = $rowOffset + count($expenses);
      $hasMore = $shown < $entryCount;
      $hasPrev = $rowOffset > 0;
      ?>
      <?php if ($hasMore || $hasPrev): ?>
        <div style="display:flex; gap:8px; justify-content:space-between; margin-top: var(--space-3);">
          <?php if ($hasPrev): $prev = max(0, $rowOffset - $pageSize); ?>
            <a class="btn btn-secondary" href="/history?m=<?= $offset ?><?= $whoQ ?>&amp;o=<?= $prev ?>">← Newer</a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="muted" style="align-self:center;">Showing <?= $rowOffset + 1 ?>–<?= $shown ?> of <?= $entryCount ?></div>
          <?php if ($hasMore): ?>
            <a class="btn btn-secondary" href="/history?m=<?= $offset ?><?= $whoQ ?>&amp;o=<?= $rowOffset + $pageSize ?>">Older →</a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Edit-expense modal: one shared <dialog>; row click fills it via openEditExpense() -->
    <dialog id="edit-expense-dlg" class="confirm" style="max-width:360px;">
      <form method="post" action="/expenses/update">
        <?= csrfInput() ?>
        <input type="hidden" name="id" id="ed-id">
        <input type="hidden" name="back" value="/history?m=<?= $offset ?><?= $whoQ ?>">
        <div class="dlg-title">Edit expense</div>

        <div class="field-row">
          <input class="input" name="amount" id="ed-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
          <input class="input" name="date" id="ed-date" type="date" required style="flex:0 0 auto; width:auto;">
        </div>

        <select class="select" name="category_id" id="ed-category" required>
          <?php foreach ($catList as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <?php if (count($memList) > 1 && ($user['role'] ?? ROLE_MEMBER) === ROLE_OWNER): ?>
          <select class="select" name="member_id" id="ed-member">
            <option value="">— No member —</option>
            <?php $mine = attributableIds($memList, $uid, ROLE_OWNER); ?>
            <?php foreach ($memList as $m): $off = !in_array((int)$m['id'], $mine, true); ?>
              <option value="<?= (int)$m['id'] ?>"<?= $off ? ' disabled' : '' ?>><?= h($m['name']) ?><?= $off ? ' — signs in' : '' ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($memList): ?>
          <?php /* One name (or no say in the matter) — send it silently. The dialog JS writes
                   the row's current member over this, so an edit keeps the attribution. */ ?>
          <input type="hidden" name="member_id" id="ed-member" value="<?= (int)$memList[0]['id'] ?>">
        <?php endif; ?>

        <input class="input" name="note" id="ed-note" placeholder="Note (optional)" maxlength="200">

        <div class="dlg-actions">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-expense-dlg').close()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </dialog>
    <script>
    function openEditExpense(d) {
      document.getElementById('ed-id').value       = d.id;
      document.getElementById('ed-amount').value   = d.amount;
      document.getElementById('ed-date').value     = d.date;
      document.getElementById('ed-category').value = d.category_id;
      var mem = document.getElementById('ed-member');
      if (mem) mem.value = d.member_id || '';
      document.getElementById('ed-note').value     = d.note || '';
      document.getElementById('edit-expense-dlg').showModal();
    }

    </script>
    <?= swipeNavScript("/history?m=" . ($offset + 1) . $whoQ, $offset > 0 ? "/history?m=" . ($offset - 1) . $whoQ : null) ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'history', $content, "/history?m=$offset" . ($who > 0 ? "&who=$who" : ""));
}

// ─── Investments ────────────────────────────────────────────────────
function renderInvest(PDO $db, array $user, bool $showForm, string $filter = 'active'): void {
    $hid = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = $db->prepare("SELECT id, name, user_id FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();
    if (!in_array($filter, ['all', 'active', 'archived'], true)) $filter = 'active';

    // Filter by whose investment it is. Validated against this household's members.
    $who = (int)($_GET['who'] ?? 0);
    $who = $who > 0 ? (int)(ownedId($db, 'members', $hid, $who) ?? 0) : 0;
    [$whoSql, $whoBind] = whoWhere($who);

    // Archiving lives on the type, so an investment is archived iff its type name is.
    $archived = archivedTypeNames($db, $hid);
    $archSet  = array_flip($archived);

    // One grouped scan powers the three summary figures AND the per-type bars — the split
    // is a name lookup in PHP, which beats three near-identical SUM queries.
    $grp = $db->prepare(
        "SELECT type, COUNT(*) AS n, SUM(amount) AS amt FROM investments WHERE household_id = ?$whoSql GROUP BY type"
    );
    $grp->execute([$hid, ...$whoBind]);
    $allTypes = $grp->fetchAll();

    $sum = [
        'active'   => ['n' => 0, 'amt' => 0.0],
        'archived' => ['n' => 0, 'amt' => 0.0],
        'all'      => ['n' => 0, 'amt' => 0.0],
    ];
    foreach ($allTypes as $t) {
        $k = isset($archSet[$t['type']]) ? 'archived' : 'active';
        $sum[$k]['n']    += (int)$t['n'];
        $sum[$k]['amt']  += (float)$t['amt'];
        $sum['all']['n']   += (int)$t['n'];
        $sum['all']['amt'] += (float)$t['amt'];
    }
    $grandCount = $sum['all']['n'];
    $entryCount = $sum[$filter]['n'];      // rows the current filter will list
    $total      = $sum[$filter]['amt'];    // denominator for the visible bars

    // Bars for the filtered subset only.
    $byType = array_values(array_filter(
        $allTypes,
        fn($t) => $filter === 'all' || (isset($archSet[$t['type']]) === ($filter === 'archived'))
    ));
    usort($byType, fn($a, $b) => (float)$b['amt'] <=> (float)$a['amt']);

    // Paginated list, scoped to the filter.
    $pageSize  = 200;
    $rowOffset = min(100000, max(0, (int)($_GET['o'] ?? 0)));
    [$clause, $clauseParams] = investmentFilterSql($filter, $archived);
    $rows = $db->prepare(
        "SELECT * FROM investments WHERE household_id = ?$clause$whoSql ORDER BY date DESC, id DESC LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute(array_merge([$hid], $clauseParams, $whoBind)); $invs = $rows->fetchAll();

    // Archived types stay in the edit dialog (so existing entries remain editable) but drop
    // out of the add form — you don't log new money into a scheme that has ended.
    $typeStmt = $db->prepare("SELECT name, archived FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $typeStmt->execute([$hid]); $typeList = $typeStmt->fetchAll();
    $activeTypes = array_values(array_filter($typeList, fn($t) => !(int)$t['archived']));

    // Every link on the page keeps the person filter alongside the archived/active one.
    $whoQ = $who > 0 ? '&amp;who=' . $who : '';
    $qs = fn(string $f) => '/invest?f=' . $f . ($who > 0 ? '&who=' . $who : '');

    ob_start();
    ?>
    <?php if ($grandCount > 0): ?>
      <div class="card total-card sage split-card">
        <div>
          <div class="k">Active</div>
          <div class="v"><?= h(fmtShort($sum['active']['amt'])) ?></div>
          <div class="n"><?= $sum['active']['n'] ?> <?= $sum['active']['n'] === 1 ? 'entry' : 'entries' ?></div>
        </div>
        <div>
          <div class="k">Archived</div>
          <div class="v"><?= h(fmtShort($sum['archived']['amt'])) ?></div>
          <div class="n"><?= $sum['archived']['n'] ?> <?= $sum['archived']['n'] === 1 ? 'entry' : 'entries' ?></div>
        </div>
        <div>
          <div class="k">Total</div>
          <div class="v"><?= h(fmtShort($sum['all']['amt'])) ?></div>
          <div class="n"><?= $sum['all']['n'] ?> <?= $sum['all']['n'] === 1 ? 'entry' : 'entries' ?></div>
        </div>
      </div>

    <?php endif; ?>

    <!-- Filters and the add action share one row; the button hangs off the end via margin-left.
         Rendered outside the has-entries guard so an empty ledger still offers a way in. -->
    <div class="pill-row">
      <?php if ($grandCount > 0): ?>
        <a class="pill-btn<?= $filter === 'all' ? ' on' : '' ?>" href="<?= $qs('all') ?>">All</a>
        <a class="pill-btn<?= $filter === 'active' ? ' on' : '' ?>" href="<?= $qs('active') ?>">Active</a>
        <a class="pill-btn<?= $filter === 'archived' ? ' on' : '' ?>" href="<?= $qs('archived') ?>">Archived</a>
      <?php endif; ?>
      <button type="button" class="pill-btn act" style="margin-left:auto;"
              onclick="document.getElementById('add-inv-dlg').showModal()"><?= icon('plus', 13) ?> Add</button>
    </div>
    <?= whoFilterRow($db, $hid, $mems, $who) ?>

    <?php if ($grandCount > 0): ?>
      <div class="stack">
        <?php foreach ($byType as $t): $amt = (float)$t['amt']; $pct = $total > 0 ? ($amt / $total) * 100 : 0; ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name">
                <?= icon(isset($archSet[$t['type']]) ? 'archive' : 'trending-up', 18) ?> <?= h($t['type']) ?>
                <?php if (isset($archSet[$t['type']])): ?><span class="tag-archived">archived</span><?php endif; ?>
              </div>
              <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
            </div>
            <div class="bar sage"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Add opens in the same kind of modal as edit, rather than re-rendering the page with an
         inline form — one affordance for "fill in an investment", wherever you started from. -->
    <dialog id="add-inv-dlg" class="confirm" style="max-width:360px;">
      <?php if (!$activeTypes): ?>
        <form method="dialog">
          <div class="dlg-title">No active types</div>
          <div class="dlg-body">Every investment type is archived. Restore one from the profile drawer before adding a new investment.</div>
          <div class="dlg-actions"><button class="btn btn-secondary" value="cancel">Close</button></div>
        </form>
      <?php else: ?>
        <form method="post" action="/investments">
          <?= csrfInput() ?>
          <div class="dlg-title">Add investment</div>
          <input class="input" name="name" placeholder="e.g. SIP - Mutual Fund" required maxlength="80" id="inv-name"
                 oninput="document.getElementById('inv-save').disabled = !(this.value.trim() && parseFloat(document.getElementById('inv-amt').value) > 0)">
          <div class="field-row">
            <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Amount" id="inv-amt"
                   oninput="document.getElementById('inv-save').disabled = !(document.getElementById('inv-name').value.trim() && parseFloat(this.value) > 0)">
            <select class="select" name="type">
              <?php foreach ($activeTypes as $t): ?>
                <option><?= h($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-row">
            <input class="input" name="date" type="date" value="<?= h(today()) ?>">
            <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER) ?>
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-inv-dlg').close()">Cancel</button>
            <button class="btn btn-primary" type="submit" id="inv-save" disabled>Save</button>
          </div>
        </form>
      <?php endif; ?>
    </dialog>
    <?php if ($showForm): ?>
      <!-- /invest?new=1 still works — old links and the back button land with the form open. -->
      <script>document.getElementById('add-inv-dlg').showModal();</script>
    <?php endif; ?>

    <?php if ($grandCount === 0): ?>
      <div class="empty">Nothing logged yet.</div>
    <?php elseif ($entryCount === 0): ?>
      <div class="empty">No <?= h($filter) ?> investments.</div>
    <?php else: ?>
      <?php
      $byMonth = [];
      foreach ($invs as $i) { $byMonth[substr($i['date'], 0, 7)][] = $i; }
      foreach ($byMonth as $ym => $entries):
        $monthLabel = (new DateTimeImmutable($ym . '-01'))->format('F Y');
        $monthTotal = array_sum(array_map(fn($x) => (float)$x['amount'], $entries));
      ?>
      <div class="day-hdr" style="display:flex; justify-content:space-between; align-items:baseline;">
        <span><?= h($monthLabel) ?></span>
        <span style="font-family:var(--font-body); font-size:12px; font-weight:600; color:var(--color-neutral-800);"><?= h(fmt($monthTotal)) ?></span>
      </div>
      <div class="stack">
        <?php foreach ($entries as $i): ?>
          <?php $invJson = json_encode([
              'id'     => (int)$i['id'],
              'name'   => $i['name'],
              'amount' => (string)$i['amount'],
              'type'   => $i['type'],
              'date'   => $i['date'],
              'member_id' => (int)($i['member_id'] ?? 0),
          ]); ?>
          <?php $rowArch = isset($archSet[$i['type']]); ?>
          <div class="card elev-sm row<?= $rowArch ? ' archived' : '' ?>">
            <div class="row-icon sage"><?= icon($rowArch ? 'archive' : 'trending-up', 16) ?></div>
            <div class="row-main">
              <div class="title"><?= h($i['name']) ?></div>
              <div class="sub"><?= h($i['type']) ?><?= $rowArch ? ' (archived)' : '' ?> · <?= h((new DateTimeImmutable($i['date']))->format('M j')) ?></div>
            </div>
            <div class="row-amt"><?= h(fmt((float)$i['amount'])) ?></div>
            <?php if (mayEdit($i, $user)): ?>
              <button class="icon-btn" type="button" aria-label="Edit"
                      onclick='openEditInvestment(<?= h($invJson) ?>)'>
                <?= icon('edit', 15) ?>
              </button>
              <button class="icon-btn" type="button" aria-label="Delete"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/investments/delete",
                          "id"     => (int)$i['id'],
                          "back"   => "/invest?f=$filter" . ($who > 0 ? "&who=$who" : ""),
                          "title"  => "Delete investment?",
                          "body"   => $i['name'] . ' — ' . fmt((float)$i['amount']),
                          "ok"     => "Delete",
                      ])) ?>)'>
                <?= icon('trash-2', 15) ?>
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <?php
      $shown = $rowOffset + count($invs);
      $hasMore = $shown < $entryCount;
      $hasPrev = $rowOffset > 0;
      ?>
      <?php if ($hasMore || $hasPrev): ?>
        <div style="display:flex; gap:8px; justify-content:space-between; margin-top: var(--space-3);">
          <?php if ($hasPrev): $prev = max(0, $rowOffset - $pageSize); ?>
            <a class="btn btn-secondary" href="/invest?f=<?= h($filter) ?><?= $whoQ ?>&amp;o=<?= $prev ?>">← Newer</a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="muted" style="align-self:center;">Showing <?= $rowOffset + 1 ?>–<?= $shown ?> of <?= $entryCount ?></div>
          <?php if ($hasMore): ?>
            <a class="btn btn-secondary" href="/invest?f=<?= h($filter) ?><?= $whoQ ?>&amp;o=<?= $rowOffset + $pageSize ?>">Older →</a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>

      <dialog id="edit-investment-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/investments/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="ei-id">
          <input type="hidden" name="back" value="/invest?f=<?= h($filter) ?><?= $whoQ ?>">
          <div class="dlg-title">Edit investment</div>
          <input class="input" name="name" id="ei-name" required maxlength="80" placeholder="Name">
          <div class="field-row">
            <input class="input" name="amount" id="ei-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
            <!-- Archived types are listed here so an existing entry keeps its own type on save. -->
            <select class="select" name="type" id="ei-type">
              <?php foreach ($typeList as $t): ?>
                <option value="<?= h($t['name']) ?>"><?= h($t['name']) ?><?= (int)$t['archived'] ? ' (archived)' : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-row">
            <input class="input" name="date" id="ei-date" type="date" required>
            <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER, 'ei-member') ?>
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-investment-dlg').close()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </dialog>
      <script>
      function openEditInvestment(d) {
        document.getElementById('ei-id').value     = d.id;
        document.getElementById('ei-name').value   = d.name;
        document.getElementById('ei-amount').value = d.amount;
        document.getElementById('ei-type').value   = d.type;
        document.getElementById('ei-date').value   = d.date;
        var m = document.getElementById('ei-member');
        if (m) m.value = d.member_id || 0;
        document.getElementById('edit-investment-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'invest', $content, '/invest?f=' . $filter . ($who > 0 ? '&who=' . $who : ''));
}

// ─── Earnings ───────────────────────────────────────────────────────
// Mirror of the Invest tab: summary, breakdown, add form, paginated list. The extra piece is
// the twelve-month earned/spent/invested chart — the one place the three ledgers meet.
function renderEarn(PDO $db, array $user, bool $showForm): void {
    $hid = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = $db->prepare("SELECT id, name, user_id FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();

    // Filter by whose money it is. Validated against this household's members.
    $who = (int)($_GET['who'] ?? 0);
    $who = $who > 0 ? (int)(ownedId($db, 'members', $hid, $who) ?? 0) : 0;
    [$whoSql,  $whoBind]  = whoWhere($who);
    [$whoSqlE, $whoBindE] = whoWhere($who, 'e');
    $whoQ = $who > 0 ? '&amp;who=' . $who : '';

    // Headline figures in one pass. Bounded on both sides so a future-dated entry doesn't
    // inflate "this month" — the same range semantics the History tab uses.
    $monthStart = (new DateTimeImmutable('first day of this month'))->format('Y-m-d');
    $monthEnd   = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');
    $yearStart  = date('Y-01-01');
    $yearEnd    = (string)((int)date('Y') + 1) . '-01-01';
    $s = $db->prepare(
        "SELECT COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total,
                COALESCE(SUM(CASE WHEN `date` >= ? AND `date` < ? THEN amount END), 0) AS mtd,
                COALESCE(SUM(CASE WHEN `date` >= ? AND `date` < ? THEN amount END), 0) AS ytd
         FROM earnings WHERE household_id = ?$whoSql"
    );
    $s->execute([$monthStart, $monthEnd, $yearStart, $yearEnd, $hid, ...$whoBind]);
    $agg = $s->fetch();
    $entryCount = (int)$agg['n'];

    // Twelve-month comparison. One indexed GROUP BY per ledger; the tables differ only in name.
    [$winStart, $winEnd, $winKeys] = rollingMonths(today(), 12);
    $series = ['ern' => [], 'exp' => [], 'inv' => []];
    foreach (['ern' => 'earnings', 'exp' => 'expenses', 'inv' => 'investments'] as $k => $table) {
        $q = $db->prepare(
            "SELECT DATE_FORMAT(`date`, '%Y-%m') AS ym, SUM(amount) AS amt FROM $table
             WHERE household_id = ? AND `date` >= ? AND `date` < ?$whoSql GROUP BY ym"
        );
        $q->execute([$hid, $winStart, $winEnd, ...$whoBind]);
        foreach ($q->fetchAll() as $r) $series[$k][$r['ym']] = (float)$r['amt'];
    }
    $peak = 0.0; $winEarn = 0.0; $winSpent = 0.0; $winInv = 0.0;
    foreach ($winKeys as $ym) {
        $peak = max($peak, $series['ern'][$ym] ?? 0, $series['exp'][$ym] ?? 0, $series['inv'][$ym] ?? 0);
        $winEarn  += $series['ern'][$ym] ?? 0;
        $winSpent += $series['exp'][$ym] ?? 0;
        $winInv   += $series['inv'][$ym] ?? 0;
    }

    // All-time breakdown. The household guard on the JOIN means a stale category_id can never
    // pull a name out of another household.
    $catStmt = $db->prepare(
        "SELECT COALESCE(c.name, 'Uncategorised') AS name, SUM(e.amount) AS amt
         FROM earnings e
         LEFT JOIN earning_categories c ON c.id = e.category_id AND c.household_id = e.household_id
         WHERE e.household_id = ?$whoSqlE
         GROUP BY c.id, c.name ORDER BY amt DESC"
    );
    $catStmt->execute([$hid, ...$whoBindE]); $byCat = $catStmt->fetchAll();

    $pageSize  = 200;
    $rowOffset = min(100000, max(0, (int)($_GET['o'] ?? 0)));
    $rows = $db->prepare(
        "SELECT e.*, c.name AS cat_name
         FROM earnings e
         LEFT JOIN earning_categories c ON c.id = e.category_id AND c.household_id = e.household_id
         WHERE e.household_id = ?$whoSqlE
         ORDER BY e.`date` DESC, e.id DESC LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute([$hid, ...$whoBindE]); $earns = $rows->fetchAll();

    $catList = $db->prepare("SELECT id, name FROM earning_categories WHERE household_id = ? ORDER BY id");
    $catList->execute([$hid]); $catList = $catList->fetchAll();

    $total = (float)$agg['total'];

    ob_start();
    ?>
    <?php if ($entryCount > 0): ?>
      <div class="card total-card ink split-card">
        <div>
          <div class="k">This month</div>
          <div class="v"><?= h(fmtShort((float)$agg['mtd'])) ?></div>
          <div class="n"><?= h((new DateTimeImmutable($monthStart))->format('M Y')) ?></div>
        </div>
        <div>
          <div class="k"><?= h(date('Y')) ?></div>
          <div class="v"><?= h(fmtShort((float)$agg['ytd'])) ?></div>
          <div class="n">year to date</div>
        </div>
        <div>
          <div class="k">All time</div>
          <div class="v"><?= h(fmtShort($total)) ?></div>
          <div class="n"><?= $entryCount ?> <?= $entryCount === 1 ? 'entry' : 'entries' ?></div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($peak > 0): ?>
      <div class="card ychart">
        <div class="ylegend">
          <span><i class="sw ern"></i>Earned</span>
          <span><i class="sw exp"></i>Spent</span>
          <span><i class="sw inv"></i>Invested</span>
        </div>
        <div class="ygrid">
          <?php foreach ($winKeys as $ym):
            $e  = $series['ern'][$ym] ?? 0.0;
            $x  = $series['exp'][$ym] ?? 0.0;
            $iv = $series['inv'][$ym] ?? 0.0;
            $c  = new DateTimeImmutable($ym . '-01');
            $tip = $c->format('F Y') . ' — earned ' . fmt($e) . ', spent ' . fmt($x) . ', invested ' . fmt($iv);
          ?>
            <div class="ycol" title="<?= h($tip) ?>" aria-label="<?= h($tip) ?>">
              <div class="ystack">
                <i class="ern" style="height:<?= $e  > 0 ? number_format(max(3, ($e  / $peak) * 100), 2) : 0 ?>%"></i>
                <i class="exp" style="height:<?= $x  > 0 ? number_format(max(3, ($x  / $peak) * 100), 2) : 0 ?>%"></i>
                <i class="inv" style="height:<?= $iv > 0 ? number_format(max(3, ($iv / $peak) * 100), 2) : 0 ?>%"></i>
              </div>
              <span class="ylab"><?= h($c->format('M')) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="muted" style="margin-top:8px; font-size:11.5px;">
          Last 12 months · earned <?= h(fmtShort($winEarn)) ?> ·
          spent <?= h(fmtShort($winSpent)) ?> ·
          invested <?= h(fmtShort($winInv)) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($byCat): ?>
      <div class="stack">
        <?php foreach ($byCat as $c): $amt = (float)$c['amt']; $pct = $total > 0 ? ($amt / $total) * 100 : 0; ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name"><?= icon('wallet', 18) ?> <?= h($c['name']) ?></div>
              <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
            </div>
            <div class="bar ink"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- No filters to share the row with here, but the add action keeps the same shape and
         end-of-row position it has on Invest. -->
    <div class="pill-row">
      <button type="button" class="pill-btn act" style="margin-left:auto;"
              onclick="document.getElementById('add-ern-dlg').showModal()"><?= icon('plus', 13) ?> Add</button>
    </div>
    <?= whoFilterRow($db, $hid, $mems, $who) ?>

    <dialog id="add-ern-dlg" class="confirm" style="max-width:360px;">
      <?php if (!$catList): ?>
        <form method="dialog">
          <div class="dlg-title">No earning categories</div>
          <div class="dlg-body">Add an earning category in the profile drawer before logging an earning.</div>
          <div class="dlg-actions"><button class="btn btn-secondary" value="cancel">Close</button></div>
        </form>
      <?php else: ?>
        <form method="post" action="/earnings">
          <?= csrfInput() ?>
          <div class="dlg-title">Add earning</div>
          <input class="input" name="name" placeholder="e.g. July salary" required maxlength="80" id="ern-name"
                 oninput="document.getElementById('ern-save').disabled = !(this.value.trim() && parseFloat(document.getElementById('ern-amt').value) > 0)">
          <div class="field-row">
            <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Amount" id="ern-amt"
                   oninput="document.getElementById('ern-save').disabled = !(document.getElementById('ern-name').value.trim() && parseFloat(this.value) > 0)">
            <select class="select" name="category_id">
              <?php foreach ($catList as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-row">
            <input class="input" name="date" type="date" value="<?= h(today()) ?>">
            <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER) ?>
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-ern-dlg').close()">Cancel</button>
            <button class="btn btn-primary" type="submit" id="ern-save" disabled>Save</button>
          </div>
        </form>
      <?php endif; ?>
    </dialog>
    <?php if ($showForm): ?>
      <script>document.getElementById('add-ern-dlg').showModal();</script>
    <?php endif; ?>

    <?php if ($entryCount === 0): ?>
      <div class="empty">Nothing earned yet.</div>
    <?php else: ?>
      <?php
      $byMonth = [];
      foreach ($earns as $e) { $byMonth[substr($e['date'], 0, 7)][] = $e; }
      foreach ($byMonth as $ym => $entries):
        $monthLabel = (new DateTimeImmutable($ym . '-01'))->format('F Y');
        $monthTotal = array_sum(array_map(fn($x) => (float)$x['amount'], $entries));
      ?>
      <div class="day-hdr" style="display:flex; justify-content:space-between; align-items:baseline;">
        <span><?= h($monthLabel) ?></span>
        <span style="font-family:var(--font-body); font-size:12px; font-weight:600; color:var(--color-neutral-800);"><?= h(fmt($monthTotal)) ?></span>
      </div>
      <div class="stack">
        <?php foreach ($entries as $e): ?>
          <?php $ernJson = json_encode([
              'id'          => (int)$e['id'],
              'name'        => $e['name'],
              'amount'      => (string)$e['amount'],
              'category_id' => (int)($e['category_id'] ?? 0),
              'date'        => $e['date'],
              'member_id'   => (int)($e['member_id'] ?? 0),
          ]); ?>
          <div class="card elev-sm row">
            <div class="row-icon ink"><?= icon('wallet', 16) ?></div>
            <div class="row-main">
              <div class="title"><?= h($e['name']) ?></div>
              <div class="sub"><?= h(($e['cat_name'] ?? 'Uncategorised') . ' · ' . (new DateTimeImmutable($e['date']))->format('M j')) ?></div>
            </div>
            <div class="row-amt"><?= h(fmt((float)$e['amount'])) ?></div>
            <?php if (mayEdit($e, $user)): ?>
              <button class="icon-btn" type="button" aria-label="Edit"
                      onclick='openEditEarning(<?= h($ernJson) ?>)'>
                <?= icon('edit', 15) ?>
              </button>
              <button class="icon-btn" type="button" aria-label="Delete"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/earnings/delete",
                          "id"     => (int)$e['id'],
                          "back"   => "/earn" . ($who > 0 ? "?who=$who" : ""),
                          "title"  => "Delete earning?",
                          "body"   => $e['name'] . ' — ' . fmt((float)$e['amount']),
                          "ok"     => "Delete",
                      ])) ?>)'>
                <?= icon('trash-2', 15) ?>
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <?php
      $shown = $rowOffset + count($earns);
      $hasMore = $shown < $entryCount;
      $hasPrev = $rowOffset > 0;
      ?>
      <?php if ($hasMore || $hasPrev): ?>
        <div style="display:flex; gap:8px; justify-content:space-between; margin-top: var(--space-3);">
          <?php if ($hasPrev): $prev = max(0, $rowOffset - $pageSize); ?>
            <a class="btn btn-secondary" href="/earn?o=<?= $prev ?><?= $whoQ ?>">← Newer</a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="muted" style="align-self:center;">Showing <?= $rowOffset + 1 ?>–<?= $shown ?> of <?= $entryCount ?></div>
          <?php if ($hasMore): ?>
            <a class="btn btn-secondary" href="/earn?o=<?= $rowOffset + $pageSize ?><?= $whoQ ?>">Older →</a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>

      <dialog id="edit-earning-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/earnings/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="ee-id">
          <input type="hidden" name="back" value="/earn<?= $who > 0 ? '?who=' . $who : '' ?>">
          <div class="dlg-title">Edit earning</div>
          <input class="input" name="name" id="ee-name" required maxlength="80" placeholder="Name">
          <div class="field-row">
            <input class="input" name="amount" id="ee-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
            <select class="select" name="category_id" id="ee-category">
              <!-- Blank option so an earning whose category was deleted stays editable
                   without silently adopting whichever category happens to be first. -->
              <option value="">— Uncategorised —</option>
              <?php foreach ($catList as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-row">
            <input class="input" name="date" id="ee-date" type="date" required>
            <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER, 'ee-member') ?>
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-earning-dlg').close()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </dialog>
      <script>
      function openEditEarning(d) {
        document.getElementById('ee-id').value       = d.id;
        document.getElementById('ee-name').value     = d.name;
        document.getElementById('ee-amount').value   = d.amount;
        document.getElementById('ee-category').value = d.category_id || '';
        document.getElementById('ee-date').value     = d.date;
        var m = document.getElementById('ee-member');
        if (m) m.value = d.member_id || 0;
        document.getElementById('edit-earning-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'earn', $content, '/earn' . ($who > 0 ? '?who=' . $who : ''));
}

// ─── Year summary ───────────────────────────────────────────────────
// Calendar year (Jan–Dec) or Indian financial year (Apr–Mar). $y is the starting year:
// in fy mode y=2026 means FY 2026–27, running 1 Apr 2026 to 31 Mar 2027.
function renderYear(PDO $db, array $user, int $y, string $mode, string $invFilter = 'all'): void {
    $hid  = (int)$user['household_id'];
    $mode = $mode === 'fy' ? 'fy' : 'cal';
    if (!in_array($invFilter, ['all', 'active', 'archived'], true)) $invFilter = 'all';
    $now  = new DateTimeImmutable('today');
    $uid  = (int)$user['id'];
    $mems = $db->prepare("SELECT id, name, user_id FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();

    // Filter the whole year to one person. Validated against this household's members.
    $who = (int)($_GET['who'] ?? 0);
    $who = $who > 0 ? (int)(ownedId($db, 'members', $hid, $who) ?? 0) : 0;
    [$whoSql,  $whoBind]  = whoWhere($who);
    [$whoSqlE, $whoBindE] = whoWhere($who, 'e');

    // Default to the period we're currently in. The FY that contains today starts in
    // April, so Jan–Mar still belongs to the FY that began the previous calendar year.
    $thisYear = (int)$now->format('Y');
    $curStart = $mode === 'fy'
        ? ((int)$now->format('n') >= 4 ? $thisYear : $thisYear - 1)
        : $thisYear;
    if ($y <= 0) $y = $curStart;

    // Clamp to [earliest year with data, current period] so you can't wander into empty
    // decades. One indexed MIN() per table — cheap on (household_id, date).
    // One round trip, not three: each branch is still an index seek on (household_id, date),
    // but the year-nav clamp is not worth three PREPARE/EXECUTE pairs on the heaviest page.
    $s = $db->prepare(
        "SELECT MIN(d) FROM (
            SELECT MIN(`date`) AS d FROM expenses    WHERE household_id = ?
            UNION ALL SELECT MIN(`date`) FROM investments WHERE household_id = ?
            UNION ALL SELECT MIN(`date`) FROM earnings    WHERE household_id = ?
         ) x"
    );
    $s->execute([$hid, $hid, $hid]);
    $minDate = $s->fetchColumn() ?: null;
    $firstYear = $curStart;
    if ($minDate) {
        $md = new DateTimeImmutable($minDate);
        $firstYear = $mode === 'fy'
            ? ((int)$md->format('n') >= 4 ? (int)$md->format('Y') : (int)$md->format('Y') - 1)
            : (int)$md->format('Y');
    }
    $y = max($firstYear, min($y, $curStart));

    $start = $mode === 'fy' ? "$y-04-01" : "$y-01-01";
    $end   = (new DateTimeImmutable($start))->modify('+1 year')->format('Y-m-d');
    $label = $mode === 'fy' ? 'FY ' . $y . '–' . substr((string)($y + 1), -2) : (string)$y;

    // The invested side honours the active/archived toggle; spending never does.
    // Same helper the Invest tab uses, so both pages agree on what "archived" means.
    $archived = archivedTypeNames($db, $hid);
    $archSet  = array_flip($archived);
    [$invClause, $invParams] = investmentFilterSql($invFilter, $archived);

    // Per-month totals for both ledgers. Indexed range scan + GROUP BY, no row fetching.
    // Built by concatenation, not sprintf — MySQL's '%Y-%m' would be eaten as a format spec.
    $monthly = [];
    $selectYm = "SELECT DATE_FORMAT(`date`, '%Y-%m') AS ym, SUM(amount) AS amt, COUNT(*) AS n FROM ";
    $whereYm  = " WHERE household_id = ? AND `date` >= ? AND `date` < ?";

    $s = $db->prepare($selectYm . "expenses" . $whereYm . $whoSql . " GROUP BY ym");
    $s->execute([$hid, $start, $end, ...$whoBind]);
    foreach ($s->fetchAll() as $r) $monthly[$r['ym']]['exp'] = ['amt' => (float)$r['amt'], 'n' => (int)$r['n']];

    $s = $db->prepare($selectYm . "investments" . $whereYm . $invClause . $whoSql . " GROUP BY ym");
    $s->execute(array_merge([$hid, $start, $end], $invParams, $whoBind));
    foreach ($s->fetchAll() as $r) $monthly[$r['ym']]['inv'] = ['amt' => (float)$r['amt'], 'n' => (int)$r['n']];

    $s = $db->prepare($selectYm . "earnings" . $whereYm . $whoSql . " GROUP BY ym");
    $s->execute([$hid, $start, $end, ...$whoBind]);
    foreach ($s->fetchAll() as $r) $monthly[$r['ym']]['ern'] = ['amt' => (float)$r['amt'], 'n' => (int)$r['n']];

    // Unfiltered count, so "is this period empty?" is answered by the data rather than by
    // the current toggle. Otherwise picking Archived on a household with none would report
    // an empty year and hide the toggle that switches back — a dead end.
    $s = $db->prepare("SELECT COUNT(*) FROM investments" . $whereYm . $whoSql);
    $s->execute([$hid, $start, $end, ...$whoBind]);
    $invAllCount = (int)$s->fetchColumn();

    // Twelve buckets in period order, so an empty month still gets a column.
    $cursor = new DateTimeImmutable($start);
    $months = []; $expTotal = 0.0; $invTotal = 0.0; $earnTotal = 0.0;
    $expCount = 0; $earnCount = 0; $peak = 0.0;
    for ($i = 0; $i < 12; $i++) {
        $ym  = $cursor->format('Y-m');
        $e   = $monthly[$ym]['exp'] ?? ['amt' => 0.0, 'n' => 0];
        $iv  = $monthly[$ym]['inv'] ?? ['amt' => 0.0, 'n' => 0];
        $er  = $monthly[$ym]['ern'] ?? ['amt' => 0.0, 'n' => 0];
        // Months back from today, so a column can deep-link into the History tab.
        $back = ((int)$now->format('Y') * 12 + (int)$now->format('n'))
              - ((int)$cursor->format('Y') * 12 + (int)$cursor->format('n'));
        $months[] = [
            'ym' => $ym, 'label' => $cursor->format('M'), 'full' => $cursor->format('F Y'),
            'exp' => $e['amt'], 'inv' => $iv['amt'], 'ern' => $er['amt'],
            'n' => $e['n'] + $iv['n'] + $er['n'], 'back' => $back,
        ];
        $expTotal += $e['amt']; $invTotal += $iv['amt']; $earnTotal += $er['amt'];
        $expCount += $e['n']; $earnCount += $er['n'];
        $peak = max($peak, $e['amt'], $iv['amt'], $er['amt']);
        $cursor = $cursor->modify('+1 month');
    }

    // Breakdowns for the whole period.
    $catStmt = $db->prepare(
        "SELECT c.id AS cid, COALESCE(c.name, 'Uncategorised') AS name, COALESCE(c.icon, 'tag') AS icon,
                0 AS budget, p.id AS pid, p.name AS pname, p.icon AS picon, 0 AS pbudget,
                SUM(e.amount) AS amt
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id AND c.household_id = e.household_id
         LEFT JOIN categories p ON p.id = c.parent_id AND p.household_id = c.household_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE
         GROUP BY c.id, c.name, c.icon, p.id, p.name, p.icon"
    );
    $catStmt->execute([$hid, $start, $end, ...$whoBindE]); $byCat = rollupCategories($catStmt->fetchAll());

    $ernStmt = $db->prepare(
        "SELECT COALESCE(c.name, 'Uncategorised') AS name, SUM(e.amount) AS amt
         FROM earnings e
         LEFT JOIN earning_categories c ON c.id = e.category_id AND c.household_id = e.household_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE
         GROUP BY c.id, c.name ORDER BY amt DESC"
    );
    $ernStmt->execute([$hid, $start, $end, ...$whoBindE]); $byErn = $ernStmt->fetchAll();

    $typeStmt = $db->prepare(
        "SELECT type, SUM(amount) AS amt FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$invClause$whoSql
         GROUP BY type ORDER BY amt DESC"
    );
    $typeStmt->execute(array_merge([$hid, $start, $end], $invParams, $whoBind)); $byType = $typeStmt->fetchAll();

    $whoQ    = $who > 0 ? '&amp;who=' . $who : '';
    $qs      = fn(int $yy) => "/year?mode=$mode&amp;inv=$invFilter&amp;y=$yy$whoQ";
    $hasPrev = $y > $firstYear;
    $hasNext = $y < $curStart;
    // Elapsed months, so a period still in progress isn't averaged over a full 12.
    $elapsed = 12;
    if ($y === $curStart) {
        $n = (int)$now->format('n');
        $elapsed = $mode === 'fy' ? ($n >= 4 ? $n - 3 : $n + 9) : $n;
    }

    ob_start();
    ?>
    <!-- Plain links, not radios+onchange: a change handler can fire from scroll/state
         restoration and navigate unintentionally. Links only move when tapped. -->
    <div class="seg year-seg" role="group" aria-label="Year type">
      <a class="seg-opt<?= $mode === 'cal' ? ' on' : '' ?>" href="/year?mode=cal&amp;inv=<?= h($invFilter) ?><?= $whoQ ?>"
         <?= $mode === 'cal' ? 'aria-current="page"' : '' ?>>Calendar year</a>
      <a class="seg-opt<?= $mode === 'fy' ? ' on' : '' ?>" href="/year?mode=fy&amp;inv=<?= h($invFilter) ?><?= $whoQ ?>"
         <?= $mode === 'fy' ? 'aria-current="page"' : '' ?>>Financial year</a>
    </div>
    <?= whoFilterRow($db, $hid, $mems, $who) ?>

    <div class="month-switch">
      <?php if ($hasPrev): ?>
        <a href="<?= $qs($y - 1) ?>" class="btn btn-icon" aria-label="Previous year"><?= icon('chevron-left', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-left', 20) ?></span>
      <?php endif; ?>
      <div class="label"><?= h($label) ?></div>
      <?php if ($hasNext): ?>
        <a href="<?= $qs($y + 1) ?>" class="btn btn-icon" aria-label="Next year"><?= icon('chevron-right', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
      <?php endif; ?>
    </div>
    <?php if ($mode === 'fy'): ?>
      <div class="muted" style="text-align:center; margin-top:-6px;">1 Apr <?= $y ?> – 31 Mar <?= $y + 1 ?></div>
    <?php endif; ?>

    <?php if ($expCount === 0 && $invAllCount === 0 && $earnCount === 0): ?>
      <div class="empty">Nothing logged in <?= h($label) ?>.</div>
    <?php else: ?>
      <div class="card total-card accent yearcard">
        <div class="split-card">
          <div>
            <div class="k">Earned</div>
            <div class="v"><?= h(fmtShort($earnTotal)) ?></div>
            <div class="n"><?= h(fmtShort($earnTotal / $elapsed)) ?>/mo avg</div>
          </div>
          <div>
            <div class="k">Spent</div>
            <div class="v"><?= h(fmtShort($expTotal)) ?></div>
            <div class="n"><?= h(fmtShort($expTotal / $elapsed)) ?>/mo avg</div>
          </div>
          <div>
            <div class="k">Invested<?= $invFilter === 'all' ? '' : ' · ' . h($invFilter) ?></div>
            <div class="v"><?= h(fmtShort($invTotal)) ?></div>
            <div class="n"><?= h(fmtShort($invTotal / $elapsed)) ?>/mo avg</div>
          </div>
        </div>
        <?php if ($earnTotal > 0): $saved = $earnTotal - $expTotal; ?>
          <!-- Investing is a use of savings, not a cost, so it stays out of this subtraction —
               otherwise a disciplined saver reads as breaking even. -->
          <div class="netrow">
            <span class="lbl">Saved · earned − spent</span>
            <!-- abs(): fmt() puts the symbol first, so a raw negative renders as "₹-1,200". -->
            <span class="v"><?= h(fmtShort(abs($saved))) ?><?= $saved < 0 ? ' short' : '' ?></span>
          </div>
        <?php endif; ?>
        <div class="invtoggle">
          <span class="lbl">Investments</span>
          <?php foreach (['all' => 'All', 'active' => 'Active', 'archived' => 'Archived'] as $k => $txt): ?>
            <a class="opt<?= $invFilter === $k ? ' on' : '' ?>"
               href="/year?mode=<?= $mode ?>&amp;y=<?= $y ?>&amp;inv=<?= $k ?><?= $whoQ ?>"
               <?= $invFilter === $k ? 'aria-current="true"' : '' ?>><?= $txt ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card ychart">
        <div class="ylegend">
          <span><i class="sw ern"></i>Earnings</span>
          <span><i class="sw exp"></i>Expenses</span>
          <span><i class="sw inv"></i>Investments</span>
        </div>
        <div class="ygrid">
          <?php foreach ($months as $m):
            $eh = $peak > 0 ? ($m['exp'] / $peak) * 100 : 0;
            $ih = $peak > 0 ? ($m['inv'] / $peak) * 100 : 0;
            $rh = $peak > 0 ? ($m['ern'] / $peak) * 100 : 0;
            $tip = $m['full'] . ' — earned ' . fmt($m['ern']) . ', spent ' . fmt($m['exp']) . ', invested ' . fmt($m['inv']);
            // Past months link into History; a future month in the current year does not.
            $tag = $m['back'] >= 0 ? 'a' : 'div';
            $href = $m['back'] >= 0 ? ' href="/history?m=' . $m['back'] . ($who > 0 ? "&amp;who=$who" : '') . '"' : '';
          ?>
            <<?= $tag ?> class="ycol"<?= $href ?> title="<?= h($tip) ?>" aria-label="<?= h($tip) ?>">
              <div class="ystack">
                <i class="ern" style="height:<?= $m['ern'] > 0 ? number_format(max(3, $rh), 2) : 0 ?>%"></i>
                <i class="exp" style="height:<?= $m['exp'] > 0 ? number_format(max(3, $eh), 2) : 0 ?>%"></i>
                <i class="inv" style="height:<?= $m['inv'] > 0 ? number_format(max(3, $ih), 2) : 0 ?>%"></i>
              </div>
              <span class="ylab"><?= h($m['label']) ?></span>
            </<?= $tag ?>>
          <?php endforeach; ?>
        </div>
        <div class="muted" style="margin-top:8px; font-size:11.5px;">Tap a month to open it in History.</div>
      </div>

      <?php if ($byErn): ?>
        <div class="day-hdr">Where it came from · <?= h(fmt($earnTotal)) ?></div>
        <div class="stack">
          <?php foreach ($byErn as $c): $amt = (float)$c['amt']; $pct = $earnTotal > 0 ? ($amt / $earnTotal) * 100 : 0; ?>
            <div class="card cat-bar">
              <div class="top">
                <div class="name"><?= icon('wallet', 18) ?> <?= h($c['name']) ?></div>
                <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
              </div>
              <div class="bar ink"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($byCat): ?>
        <div class="day-hdr">Where it went · <?= h(fmt($expTotal)) ?></div>
        <div class="stack">
          <?php foreach ($byCat as $c): $amt = (float)$c['amt']; $pct = $expTotal > 0 ? ($amt / $expTotal) * 100 : 0; ?>
            <div class="card cat-bar">
              <div class="top">
                <div class="name"><?= icon($c['icon'], 18) ?> <?= h($c['name']) ?></div>
                <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
              </div>
              <div class="bar"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
              <?php foreach ($c['children'] as $k): ?>
                <div class="sub-line"><span>↳ <?= h($k['name']) ?></span><span><?= h(fmt((float)$k['amt'])) ?></span></div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($byType): ?>
        <div class="day-hdr">Invested by type · <?= h(fmt($invTotal)) ?></div>
        <div class="stack">
          <?php foreach ($byType as $t): $amt = (float)$t['amt']; $pct = $invTotal > 0 ? ($amt / $invTotal) * 100 : 0;
                $isArch = isset($archSet[$t['type']]); ?>
            <div class="card cat-bar">
              <div class="top">
                <div class="name">
                  <?= icon($isArch ? 'archive' : 'trending-up', 18) ?> <?= h($t['type']) ?>
                  <?php if ($isArch): ?><span class="tag-archived">archived</span><?php endif; ?>
                </div>
                <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
              </div>
              <div class="bar sage"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    <?php $whoR = $who > 0 ? "&who=$who" : ''; ?>
    <?= swipeNavScript($hasPrev ? "/year?mode=$mode&inv=$invFilter&y=" . ($y - 1) . $whoR : null,
                       $hasNext ? "/year?mode=$mode&inv=$invFilter&y=" . ($y + 1) . $whoR : null) ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'year', $content, "/year?mode=$mode&inv=$invFilter&y=$y" . ($who > 0 ? "&who=$who" : ""));
}

// ─── Recurring ──────────────────────────────────────────────────────
function renderRecurring(PDO $db, array $user, bool $showForm): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = categoryTree($cats->fetchAll());
    $uid  = (int)$user['id'];
    $mems = $db->prepare("SELECT id, name, user_id FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();
    // Same split as the Invest tab: new recurring investments can only target a live type,
    // but the edit dialog lists archived ones so an existing item keeps its type on save.
    $typeStmt = $db->prepare("SELECT name, archived FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $typeStmt->execute([$hid]); $typeList = $typeStmt->fetchAll();
    $activeTypes = array_values(array_filter($typeList, fn($t) => !(int)$t['archived']));
    $eCats = $db->prepare("SELECT id, name FROM earning_categories WHERE household_id = ? ORDER BY id");
    $eCats->execute([$hid]); $eCats = $eCats->fetchAll();
    // Both joins hang off the same `category_id`; only the one matching the row's kind is read.
    $rows = $db->prepare(
        "SELECT r.*, c.name AS cat_name, ec.name AS ecat_name FROM recurring r
         LEFT JOIN categories c ON c.id = r.category_id AND c.household_id = r.household_id
         LEFT JOIN earning_categories ec ON ec.id = r.category_id AND ec.household_id = r.household_id
         WHERE r.household_id = ? ORDER BY r.next_date, r.id"
    );
    $rows->execute([$hid]); $recs = $rows->fetchAll();

    ob_start();
    ?>
    <div class="muted">Salary, rent, EMIs and subscriptions that repeat.</div>

    <div class="pill-row">
      <button type="button" class="pill-btn" style="margin-left:auto;"
              onclick="openAddSplit()"><?= icon('calendar', 13) ?>&nbsp;Split a bill</button>
      <button type="button" class="pill-btn act"
              onclick="document.getElementById('add-rec-dlg').showModal()"><?= icon('plus', 13) ?> Add</button>
    </div>

    <dialog id="add-rec-dlg" class="confirm" style="max-width:360px;">
      <form method="post" action="/recurring">
        <?= csrfInput() ?>
        <div class="dlg-title">Add recurring item</div>
        <select class="select" name="kind" id="rec-kind" onchange="toggleRecKind()">
          <option value="expense">Expense — auto-post to History</option>
          <option value="earning">Earning — auto-post to Earn</option>
          <option value="investment">Investment — auto-post to Invest</option>
        </select>
        <input class="input" name="name" placeholder="e.g. Rent, Salary, Nifty SIP" required maxlength="80" id="rec-name"
               oninput="document.getElementById('rec-save').disabled = !(this.value.trim() && parseFloat(document.getElementById('rec-amt').value) > 0)">
        <div class="field-row">
          <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Amount" id="rec-amt"
                 oninput="document.getElementById('rec-save').disabled = !(document.getElementById('rec-name').value.trim() && parseFloat(this.value) > 0)">
          <select class="select" name="category_id" id="rec-cat">
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="select" name="type" id="rec-type" style="display:none;">
            <?php foreach ($activeTypes as $t): ?>
              <option value="<?= h($t['name']) ?>"><?= h($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <!-- Shares the name "category_id" with the expense picker: only the one matching the
               selected kind is ever enabled, so exactly one value is submitted. -->
          <select class="select" name="category_id" id="rec-ecat" style="display:none;" disabled>
            <?php foreach ($eCats as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">
          <select class="select" name="frequency">
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
          </select>
          <input class="input" name="next_date" type="date" value="<?= h(today()) ?>">
          <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER) ?>
        </div>
        <div class="dlg-actions">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-rec-dlg').close()">Cancel</button>
          <button class="btn btn-primary" type="submit" id="rec-save" disabled>Save</button>
        </div>
      </form>
    </dialog>
    <script>
    // Lives outside the dialog so it is defined whether or not the dialog has ever been opened.
    function toggleRecKind() {
      var kind = document.getElementById('rec-kind').value;
      [['rec-cat', 'expense'], ['rec-type', 'investment'], ['rec-ecat', 'earning']].forEach(function (p) {
        var el = document.getElementById(p[0]);
        el.style.display = kind === p[1] ? '' : 'none';
        el.disabled      = kind !== p[1];
      });
    }
    toggleRecKind();
    </script>

    <!-- Split a prepaid bill. Separate dialog rather than a fourth "kind" in the one above,
         because every field means something different: one total instead of a per-period
         amount, a length instead of a frequency, and a date that has already happened. -->
    <?php /* One dialog, two jobs. Editing a split asks for exactly the same four things as
             creating one, so a second dialog would mean two copies of the preview maths and two
             sets of ids to keep in step. openEditSplit() repoints the action and the copy. */ ?>
    <dialog id="split-dlg" class="confirm" style="max-width:360px;">
      <form method="post" action="/recurring/split" id="sp-form">
        <?= csrfInput() ?>
        <input type="hidden" name="id" id="sp-id" value="" disabled>
        <div class="dlg-title" id="sp-title">Split a prepaid bill</div>
        <div class="muted" style="margin-bottom:10px;" id="sp-blurb">
          Paid once, used over months — insurance, domains, hosting. An equal share posts to
          History every month, and months already past appear straight away.
        </div>
        <input class="input" name="name" id="sp-name" placeholder="e.g. Health insurance"
               required maxlength="80" oninput="splitPreview()">
        <div class="field-row">
          <input class="input" name="amount" id="sp-amt" type="text" inputmode="decimal"
                 pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Total paid" oninput="splitPreview()">
          <select class="select" name="category_id" id="sp-cat">
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">
          <select class="select" name="months" id="sp-months" onchange="splitPreview()">
            <option value="6">6 months</option>
            <option value="12" selected>1 year</option>
            <option value="18">18 months</option>
            <option value="24">2 years</option>
            <option value="36">3 years</option>
          </select>
          <input class="input" name="start_date" id="sp-date" type="date" value="<?= h(today()) ?>"
                 onchange="splitPreview()">
        </div>
        <?php if ($ms = memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER, 'sp-member')): ?><div class="field-row"><?= $ms ?></div><?php endif; ?>
        <div class="muted" id="sp-preview" style="margin-top:10px;"></div>
        <div class="dlg-actions">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('split-dlg').close()">Cancel</button>
          <button class="btn btn-primary" type="submit" id="sp-save" disabled>Save</button>
        </div>
      </form>
    </dialog>
    <script>
    // Previews exactly what the server will store, so an uneven split (10000 over 12 months
    // is 833.33 a month, four paise short) is visible before saving rather than after.
    var SPLIT_CUR   = <?= json_encode($_SESSION['currency'] ?? '₹') ?>;
    var SPLIT_TODAY = <?= json_encode(today()) ?>;
    function splitPreview() {
      var total  = parseFloat(document.getElementById('sp-amt').value);
      var months = parseInt(document.getElementById('sp-months').value, 10);
      var start  = document.getElementById('sp-date').value;
      var named  = document.getElementById('sp-name').value.trim() !== '';
      var per    = (total > 0 && months > 0) ? total / months : 0;
      // Matches the server: a share that rounds to zero is not a split.
      document.getElementById('sp-save').disabled = !(named && per >= 0.005 && start);
      var out = document.getElementById('sp-preview');
      if (!(per > 0) || !start) {
        out.textContent = 'Enter the total you paid to see the monthly share.';
        return;
      }
      var p = start.split('-');
      var span = function (offset) {
        var d = new Date(+p[0], +p[1] - 1 + offset, 1);
        return d.toLocaleString(undefined, { month: 'short', year: 'numeric' });
      };
      out.textContent = SPLIT_CUR + per.toFixed(2) + ' a month × ' + months
                      + ' — ' + span(0) + ' through ' + span(months - 1);
    }
    splitPreview();

    // Add mode: a fresh plan, posting to /recurring/split. The id field stays disabled so it
    // is not submitted at all, rather than submitted empty.
    function openAddSplit() {
      var f = document.getElementById('sp-form');
      f.action = '/recurring/split';
      document.getElementById('sp-id').disabled = true;
      document.getElementById('sp-title').textContent = 'Split a prepaid bill';
      document.getElementById('sp-blurb').textContent =
        'Paid once, used over months \u2014 insurance, domains, hosting. An equal share posts to '
        + 'History every month, and months already past appear straight away.';
      document.getElementById('sp-name').value = '';
      document.getElementById('sp-amt').value  = '';
      document.getElementById('sp-months').value = '12';
      document.getElementById('sp-date').value = SPLIT_TODAY;
      splitPreview();
      document.getElementById('split-dlg').showModal();
    }

    // Edit mode: the same four questions, restated. d.total is the whole bill again, not the
    // monthly share, because that is what was typed in the first place.
    function openEditSplit(d) {
      var f = document.getElementById('sp-form');
      f.action = '/recurring/split/update';
      var idf = document.getElementById('sp-id');
      idf.disabled = false;
      idf.value = d.id;
      document.getElementById('sp-title').textContent = 'Edit split bill';
      document.getElementById('sp-blurb').textContent =
        'Changing any of this recalculates every share and re-posts them to History. '
        + 'Anything you edited by hand on those posted entries is replaced.';
      document.getElementById('sp-name').value = d.name;
      document.getElementById('sp-amt').value  = d.total;
      // A split made before this list existed, or one whose length was never on it, still has
      // to be openable — so its own length joins the options rather than being rounded to one.
      var sel = document.getElementById('sp-months');
      if (!Array.prototype.some.call(sel.options, function (o) { return +o.value === +d.months; })) {
        var o = document.createElement('option');
        o.value = d.months; o.textContent = d.months + ' months';
        sel.appendChild(o);
      }
      sel.value = d.months;
      document.getElementById('sp-date').value = d.start_date;
      var m = document.getElementById('sp-member');
      if (m) m.value = d.member_id || 0;
      var c = document.getElementById('sp-cat');
      if (c) c.value = d.category_id || '';
      splitPreview();
      document.getElementById('split-dlg').showModal();
    }
    </script>
    <?php if ($showForm): ?>
      <script>document.getElementById('add-rec-dlg').showModal();</script>
    <?php endif; ?>

    <?php if (!$recs): ?>
      <div class="empty">No recurring items.</div>
    <?php else: ?>
      <div class="stack">
        <?php foreach ($recs as $r): ?>
          <?php $recJson = json_encode([
              'id'          => (int)$r['id'],
              'name'        => $r['name'],
              'amount'      => (string)$r['amount'],
              'kind'        => $r['kind'] ?? 'expense',
              'category_id' => (int)($r['category_id'] ?? 0),
              'type'        => (string)($r['type'] ?? ''),
              'frequency'   => $r['frequency'],
              'next_date'   => $r['next_date'],
              'member_id'   => (int)($r['member_id'] ?? 0),
          ]); ?>
          <?php
          // A split is a recurring row with an end date. Its dialog asks for the whole bill and
          // its length, so both are reconstructed here: the total from the share it posts, the
          // length from the two dates. start_date is NULL only on rows that predate it.
          $isSplit = $r['end_date'] !== null;
          $spMonths = $isSplit
              ? monthsSpan((string)($r['start_date'] ?: $r['next_date']), (string)$r['end_date'])
              : 0;
          $splitJson = $isSplit ? json_encode([
              'id'          => (int)$r['id'],
              'name'        => $r['name'],
              // What was typed, when we have it. Splits made before the column exists fall
              // back to the sum of their shares, which is the only figure they can offer.
              'total'       => $r['total_amount'] !== null
                                 ? number_format((float)$r['total_amount'], 2, '.', '')
                                 : number_format((float)$r['amount'] * $spMonths, 2, '.', ''),
              'months'      => $spMonths,
              'start_date'  => (string)($r['start_date'] ?: $r['next_date']),
              'category_id' => (int)($r['category_id'] ?? 0),
              'member_id'   => (int)($r['member_id'] ?? 0),
          ]) : '';
          ?>
          <?php
          $kind = $r['kind'] ?? 'expense';
          [$rowCls, $rowIcon, $rowWhat] = match ($kind) {
              'investment' => [' sage', 'trending-up', $r['type'] ?? 'Other'],
              'earning'    => [' ink',  'wallet',      $r['ecat_name'] ?? 'Uncategorised'],
              default      => ['',      'repeat',      $r['cat_name'] ?? 'Uncategorised'],
          };
          // A split bill has a last instalment, so it says where it ends rather than how often
          // it repeats — and once past it, that it is done and will post nothing more.
          $end = $r['end_date'] ?? null;
          if ($end !== null) {
              $rowIcon = 'calendar';
              $done    = $r['next_date'] > $end;
              $sub     = $rowWhat . ' · Split · ' . ($done
                  ? 'complete ' . (new DateTimeImmutable($end))->format('M Y')
                  : 'next ' . (new DateTimeImmutable($r['next_date']))->format('M j, Y')
                    . ' · last ' . (new DateTimeImmutable($end))->format('M Y'));
          } else {
              $sub = $rowWhat . ' · ' . ucfirst($r['frequency'])
                   . ' · next ' . (new DateTimeImmutable($r['next_date']))->format('M j, Y');
          }
          ?>
          <div class="card elev-sm row">
            <div class="row-icon<?= $rowCls ?>"><?= icon($rowIcon, 16) ?></div>
            <div class="row-main">
              <div class="title"><?= h($r['name']) ?></div>
              <div class="sub"><?= h($sub) ?></div>
            </div>
            <div class="row-amt"><?= h(fmt((float)$r['amount'])) ?></div>
            <?php if (mayEdit($r, $user)): ?>
              <button class="icon-btn" type="button" aria-label="Edit"
                      onclick='<?= $isSplit ? 'openEditSplit(' . h($splitJson) . ')' : 'openEditRecurring(' . h($recJson) . ')' ?>'>
                <?= icon('edit', 15) ?>
              </button>
              <button class="icon-btn" type="button" aria-label="Delete"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/recurring/delete",
                          "id"     => (int)$r['id'],
                          "title"  => "Delete recurring item?",
                          "body"   => $r['name'] . ' — ' . fmt((float)$r['amount']) . ' / ' . $r['frequency'],
                          "ok"     => "Delete",
                          "extra"  => "Also delete all past auto-posted entries for this item",
                      ])) ?>)'>
                <?= icon('trash-2', 15) ?>
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <dialog id="edit-recurring-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/recurring/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="er-id">
          <div class="dlg-title">Edit recurring item</div>
          <select class="select" name="kind" id="er-kind" onchange="toggleErKind()">
            <option value="expense">Expense</option>
            <option value="earning">Earning</option>
            <option value="investment">Investment</option>
          </select>
          <input class="input" name="name" id="er-name" required maxlength="80" placeholder="Name">
          <div class="field-row">
            <input class="input" name="amount" id="er-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
            <select class="select" name="category_id" id="er-category">
              <?php foreach ($cats as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="select" name="type" id="er-type" style="display:none;">
              <?php foreach ($typeList as $t): ?>
                <option value="<?= h($t['name']) ?>"><?= h($t['name']) ?><?= (int)$t['archived'] ? ' (archived)' : '' ?></option>
              <?php endforeach; ?>
            </select>
            <select class="select" name="category_id" id="er-ecat" style="display:none;" disabled>
              <option value="">— Uncategorised —</option>
              <?php foreach ($eCats as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-row">
            <select class="select" name="frequency" id="er-frequency">
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
            <input class="input" name="next_date" id="er-next" type="date" required>
            <?= memberSelect($mems, $uid, $user['role'] ?? ROLE_MEMBER, 'er-member') ?>
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-recurring-dlg').close()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </dialog>
      <script>
      function toggleErKind() {
        var kind = document.getElementById('er-kind').value;
        [['er-category', 'expense'], ['er-type', 'investment'], ['er-ecat', 'earning']].forEach(function (p) {
          var el = document.getElementById(p[0]);
          el.style.display = kind === p[1] ? '' : 'none';
          el.disabled      = kind !== p[1];
        });
      }
      function openEditRecurring(d) {
        document.getElementById('er-id').value        = d.id;
        document.getElementById('er-kind').value      = d.kind || 'expense';
        document.getElementById('er-name').value      = d.name;
        document.getElementById('er-amount').value    = d.amount;
        // category_id means a different table per kind, so it only loads into the matching picker.
        document.getElementById('er-category').value  = d.kind === 'expense' ? (d.category_id || '') : '';
        document.getElementById('er-ecat').value      = d.kind === 'earning' ? (d.category_id || '') : '';
        if (d.type) document.getElementById('er-type').value = d.type;
        document.getElementById('er-frequency').value = d.frequency;
        document.getElementById('er-next').value      = d.next_date;
        var m = document.getElementById('er-member');
        if (m) m.value = d.member_id || 0;
        toggleErKind();
        document.getElementById('edit-recurring-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'recurring', $content, '/recurring');
}

// ─── Organise categories ────────────────────────────────────────────
// Two jobs the profile drawer has no room for: merging one category's entries into another,
// and nesting a category under a parent.
function renderOrganise(PDO $db, array $user): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = $cats->fetchAll();
    $tree = categoryTree($cats);

    // Entry counts drive the "Move 23 expenses" button label, so the tap is never blind.
    $s = $db->prepare("SELECT category_id, COUNT(*) n FROM expenses WHERE household_id = ? GROUP BY category_id");
    $s->execute([$hid]); $counts = array_column($s->fetchAll(), 'n', 'category_id');
    // Same predicate the delete/move tools use, so the number on screen is exactly what they touch.
    $u = $db->prepare("SELECT COUNT(*) FROM expenses WHERE " . uncategorisedWhere());
    $u->execute([$hid, $hid]); $nUncat = (int)$u->fetchColumn();
    $currency = $_SESSION['currency'] ?? '₹';
    // Children grouped under their parent — the tree renders one card per top-level category.
    $kids = [];
    foreach ($cats as $c) if (!empty($c['parent_id'])) $kids[(int)$c['parent_id']][] = $c;
    $kidCount = array_map('count', $kids);
    $tops = array_values(array_filter($cats, fn($c) => empty($c['parent_id'])));
    // Categories that actually have children lead, so the hierarchy is the first thing on the
    // page rather than something you scroll to find. Ties keep their usual order.
    usort($tops, fn($a, $b) => ($kidCount[(int)$b['id']] ?? 0) <=> ($kidCount[(int)$a['id']] ?? 0));

    ob_start();
    ?>
    <div class="month-switch">
      <a href="/#profile" class="btn btn-icon" aria-label="Back"><?= icon('chevron-left', 20) ?></a>
      <div class="label" style="font-size:16px;">Organise expense categories</div>
      <span class="btn btn-icon" style="opacity:0; pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
    </div>

    <div class="muted" style="font-size:12px; margin: 0 2px;">
      Rename, budget, nest and delete — everything about expense categories lives here.
      A sub-category's spending rolls up into its parent's bar and budget, so only parents carry one.
    </div>

    <div class="stack">
      <?php foreach ($tops as $t):
        $list  = $kids[(int)$t['id']] ?? [];
        $nAll  = (int)($counts[$t['id']] ?? 0);
        foreach ($list as $k) $nAll += (int)($counts[$k['id']] ?? 0);
      ?>
        <div class="card tree-node">
          <div class="tree-head">
            <form method="post" action="/categories/update" class="tree-row">
              <?= csrfInput() ?>
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="back" value="/organise">
              <span class="tree-ico"><?= icon($t['icon'], 16) ?></span>
              <input class="input" name="name" value="<?= h($t['name']) ?>" maxlength="50" aria-label="Category name">
              <input class="input budget-in" name="budget" type="text" inputmode="decimal"
                     pattern="\d{0,10}(\.\d{1,2})?" maxlength="13"
                     value="<?= (float)$t['budget'] > 0 ? h(rtrim(rtrim(number_format((float)$t['budget'], 2, '.', ''), '0'), '.')) : '' ?>"
                     placeholder="<?= h($currency) ?>" aria-label="Monthly budget for <?= h($t['name']) ?>">
              <button class="icon-btn" type="submit" aria-label="Save <?= h($t['name']) ?>"><?= icon('check', 15) ?></button>
            </form>
            <?php if ($t['is_custom']): ?>
              <button type="button" class="icon-btn" aria-label="Delete <?= h($t['name']) ?>"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/categories/delete",
                          "id"     => (int)$t['id'],
                          "back"   => "/organise",
                          "csrf"   => csrfToken(),
                          "title"  => "Delete " . $t['name'] . "?",
                          "body"   => ($nAll === 0 ? 'Nothing is logged under it.' : "Its $nAll entr" . ($nAll === 1 ? 'y stays' : 'ies stay') . ' logged but become uncategorised.')
                                      . ($list ? ' Its ' . (count($list) === 1 ? 'sub-category moves' : count($list) . ' sub-categories move') . ' back to top level.' : ''),
                          "ok"     => "Delete",
                      ])) ?>)'><?= icon('trash-2', 14) ?></button>
            <?php endif; ?>
          </div>
          <div class="tree-metaline">
            <?php if ((float)$t['budget'] > 0): ?><?= h(fmtShort((float)$t['budget'])) ?> budget · <?php endif; ?>
            <?= $nAll ?> <?= $nAll === 1 ? 'entry' : 'entries' ?><?= $list ? ' incl. sub' : '' ?>
            <?= $t['is_custom'] ? '' : ' · built-in' ?>
          </div>

          <?php foreach ($list as $i => $k): $nK = (int)($counts[$k['id']] ?? 0); ?>
            <div class="tree-kid<?= $i === count($list) - 1 ? ' last' : '' ?>">
              <form method="post" action="/categories/update" class="tree-row">
                <?= csrfInput() ?>
                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                <input type="hidden" name="back" value="/organise">
                <!-- No budget field: /categories/update reads a missing `budget` as blank, which
                     parseBudget turns into 0 — exactly what a sub-category must hold. -->
                <span class="tree-ico"><?= icon($k['icon'], 14) ?></span>
                <input class="input" name="name" value="<?= h($k['name']) ?>" maxlength="50" aria-label="Sub-category name">
                <span class="tree-meta"><?= $nK ?></span>
                <button class="icon-btn" type="submit" aria-label="Save <?= h($k['name']) ?>"><?= icon('check', 15) ?></button>
              </form>
              <!-- The shared dialog posts _csrf + id + back and nothing else. That is exactly
                   right here: a missing parent_id reads as 0, which is how the handler spells
                   "back to top level". -->
              <button type="button" class="icon-btn" title="Move out to top level"
                      aria-label="Move <?= h($k['name']) ?> out of <?= h($t['name']) ?>"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/categories/parent",
                          "id"     => (int)$k['id'],
                          "back"   => "/organise",
                          "csrf"   => csrfToken(),
                          "title"  => "Move " . $k['name'] . " out?",
                          // No possessive on the parent name — half of them already end in "s".
                          "body"   => $k['name'] . ' becomes a top-level category again'
                                      . ($nK ? ", keeping its $nK entr" . ($nK === 1 ? 'y' : 'ies') : '')
                                      . '. Its spending stops rolling up into ' . $t['name']
                                      . ', so it no longer counts against that budget — and it can carry one of its own again.',
                          "ok"     => "Move out",
                          "danger" => false,
                      ])) ?>)'><?= icon('corner-left-up', 14) ?></button>
              <?php if ($k['is_custom']): ?>
                <button type="button" class="icon-btn" aria-label="Delete <?= h($k['name']) ?>"
                        onclick='askConfirm(<?= h(json_encode([
                            "action" => "/categories/delete",
                            "id"     => (int)$k['id'],
                            "back"   => "/organise",
                            "csrf"   => csrfToken(),
                            "title"  => "Delete " . $k['name'] . "?",
                            "body"   => $nK === 0 ? 'Nothing is logged under it.'
                                      : "Its $nK entr" . ($nK === 1 ? 'y stays' : 'ies stay') . ' logged but become uncategorised — they will not fall back to ' . $t['name'] . '.',
                            "ok"     => "Delete",
                        ])) ?>)'><?= icon('trash-2', 14) ?></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="post" action="/categories" class="row-form" style="margin-top:2px;">
      <?= csrfInput() ?>
      <input type="hidden" name="back" value="/organise">
      <input class="input" name="name" placeholder="New category" maxlength="50">
      <input class="input budget-in" name="budget" type="text" inputmode="decimal"
             pattern="\d{0,10}(\.\d{1,2})?" maxlength="13"
             placeholder="<?= h($currency) ?>" aria-label="Monthly budget">
      <button class="btn btn-primary" type="submit">Add</button>
    </form>

    <?php
    // Only a category with no children of its own can become one — one level, enforced server-side.
    $nestable = array_values(array_filter($cats, fn($c) => ($kidCount[(int)$c['id']] ?? 0) === 0));
    ?>
    <?php if ($nestable && count($tops) > 1): ?>
      <div class="day-hdr">Nest a category</div>
      <form method="post" action="/categories/parent" class="card stack" id="nest-form"
            style="padding:var(--space-4); gap:10px;" onsubmit="return askNest(event)">
        <?= csrfInput() ?>
        <input type="hidden" name="back" value="/organise">
        <div class="field-row" style="align-items:center; gap:6px;">
          <select class="select" name="id" id="nest-id" onchange="syncNest()">
            <?php foreach ($nestable as $c): ?>
              <!-- data-budget drives the confirmation: nesting zeroes a budget, and that value
                   is the one thing here you can't get back with a second tap. -->
              <option value="<?= (int)$c['id'] ?>" data-budget="<?= h((float)$c['budget'] > 0 ? fmt((float)$c['budget']) : '') ?>"><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="muted" style="flex:0 0 auto; font-size:12px;">under</span>
          <select class="select" name="parent_id" id="nest-parent" onchange="syncNest()">
            <?php foreach ($tops as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= h($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="muted" style="font-size:11.5px;" id="nest-note"></div>
        <button class="btn btn-primary btn-block" type="submit" id="nest-btn">Nest</button>
      </form>
    <?php endif; ?>

    <div class="day-hdr">Move entries</div>
    <form method="post" action="/categories/move" id="move-form" class="card stack"
          style="padding:var(--space-4); gap:10px;" onsubmit="return askMove(event)">
      <?= csrfInput() ?>
      <input type="hidden" name="back" value="/organise">
      <div class="muted" style="font-size:12px;">Merge one category into another. Every expense moves, and so does any recurring item that posts into it. The emptied category stays — delete it from the profile drawer if you're done with it.</div>
      <label class="muted" style="font-size:11px;">From</label>
      <select class="select" name="from_id" id="move-from" onchange="syncMove()">
        <?php if ($nUncat > 0): ?>
          <!-- id 0 is the pseudo-category; the handler maps it to uncategorisedWhere(). -->
          <option value="0" data-n="<?= $nUncat ?>" data-name="Uncategorised">Uncategorised (<?= $nUncat ?>)</option>
        <?php endif; ?>
        <?php foreach ($tree as $c): $n = (int)($counts[$c['id']] ?? 0); ?>
          <option value="<?= (int)$c['id'] ?>" data-n="<?= $n ?>" data-name="<?= h($c['name']) ?>">
            <?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?> (<?= $n ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <label class="muted" style="font-size:11px;">To</label>
      <select class="select" name="to_id" id="move-to" onchange="syncMove()">
        <?php foreach ($tree as $c): ?>
          <option value="<?= (int)$c['id'] ?>" data-name="<?= h($c['name']) ?>">
            <?= $c['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-block" type="submit" id="move-btn">Move entries</button>
    </form>

    <?php if ($nUncat > 0): ?>
      <div class="card cat-bar">
        <div class="top">
          <div class="name"><?= icon('more-horizontal', 18) ?> Uncategorised</div>
          <div><span class="amt"><?= $nUncat ?> <?= $nUncat === 1 ? 'entry' : 'entries' ?></span></div>
        </div>
        <div class="muted" style="font-size:11.5px; margin-top:6px;">
          Expenses with no category, or whose category was deleted. File them somewhere with
          <strong>Move entries</strong> above — "Uncategorised" is the first option in the From list.
        </div>
        <button type="button" class="btn btn-danger btn-block" style="margin-top:10px;"
                onclick='askConfirm(<?= h(json_encode([
                    "action" => "/categories/uncategorised/delete",
                    "back"   => "/organise",
                    "csrf"   => csrfToken(),
                    "title"  => "Delete uncategorised expenses?",
                    "body"   => "This permanently deletes " . $nUncat . ($nUncat === 1 ? ' expense' : ' expenses')
                                . ", not just their category. There is no undo. To keep the money logged, cancel and use Move entries instead.",
                    "ok"     => "Delete " . $nUncat,
                ])) ?>)'>Delete <?= $nUncat ?> uncategorised <?= $nUncat === 1 ? 'entry' : 'entries' ?></button>
      </div>
    <?php endif; ?>

    <!-- Own dialog rather than the shared askConfirm(): that one posts a fixed id/back pair,
         and this needs two selects' worth of state. -->
    <dialog id="move-dlg" class="confirm" aria-labelledby="move-title">
      <form method="dialog">
        <div class="dlg-title" id="move-title">Move entries?</div>
        <div class="dlg-body" id="move-body"></div>
        <div class="dlg-actions">
          <button type="submit" class="btn btn-secondary" value="cancel">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="doMove()">Move</button>
        </div>
      </form>
    </dialog>
    <!-- Its own dialog, not askConfirm(): that one posts a fixed id/back pair and this needs
         the parent from a second select. -->
    <dialog id="nest-dlg" class="confirm" aria-labelledby="nest-title">
      <form method="dialog">
        <div class="dlg-title" id="nest-title"></div>
        <div class="dlg-body" id="nest-dlg-body"></div>
        <div class="dlg-actions">
          <button type="submit" class="btn btn-secondary" value="cancel">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="doNest()">Nest</button>
        </div>
      </form>
    </dialog>
    <script>
    function syncMove() {
      var f = document.getElementById('move-from'), t = document.getElementById('move-to');
      var n = parseInt(f.selectedOptions[0].dataset.n || '0', 10);
      var same = f.value === t.value;
      var btn = document.getElementById('move-btn');
      btn.disabled = same || n === 0;
      btn.textContent = same ? 'Pick two different categories'
                     : n === 0 ? 'Nothing to move'
                     : 'Move ' + n + (n === 1 ? ' expense' : ' expenses');
    }
    function askMove(e) {
      e.preventDefault();
      var f = document.getElementById('move-from'), t = document.getElementById('move-to');
      document.getElementById('move-body').textContent =
        'Every expense in "' + f.selectedOptions[0].dataset.name + '" moves to "' +
        t.selectedOptions[0].dataset.name + '", along with any recurring item that posts into it. ' +
        'Moving them back afterwards would also carry entries that were already there.';
      document.getElementById('move-dlg').showModal();
      return false;
    }
    function doMove() {
      document.getElementById('move-dlg').close();
      document.getElementById('move-form').submit();
    }
    function syncNest() {
      var c = document.getElementById('nest-id'), p = document.getElementById('nest-parent');
      if (!c) return;
      var same = c.value === p.value;
      var btn = document.getElementById('nest-btn');
      btn.disabled = same;
      btn.textContent = same ? 'Pick a different parent'
                             : 'Nest ' + c.selectedOptions[0].text + ' under ' + p.selectedOptions[0].text;
      document.getElementById('nest-note').textContent = same ? ''
        : c.selectedOptions[0].text + ' keeps its own entries; they just roll up into '
          + p.selectedOptions[0].text + ' from then on.';
    }
    function askNest(e) {
      e.preventDefault();
      var c = document.getElementById('nest-id'), p = document.getElementById('nest-parent');
      var kid = c.selectedOptions[0].text, par = p.selectedOptions[0].text;
      var bud = c.selectedOptions[0].dataset.budget;
      document.getElementById('nest-title').textContent = 'Nest ' + kid + ' under ' + par + '?';
      document.getElementById('nest-dlg-body').textContent =
        kid + ' keeps its own entries, but its spending now rolls up into ' + par +
        ' and counts against that budget instead of standing on its own.' +
        // The budget is the one part that can't be undone by moving it back out.
        (bud ? ' Its ' + bud + ' monthly budget will be cleared — sub-categories don’t carry one.' : '');
      document.getElementById('nest-dlg').showModal();
      return false;
    }
    function doNest() {
      document.getElementById('nest-dlg').close();
      document.getElementById('nest-form').submit();
    }
    syncMove(); syncNest();
    </script>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'organise', $content, '/organise');
}

// ─── Terms & Conditions ─────────────────────────────────────────────

// Shared prose block — no chrome; both the authed and public wrappers embed this.
// ────────────────────────────────────────────────────────────────────
// /ledgers — pick which ledger you are looking at, and share it.
// This is also where sign-in lands anyone who belongs to more than one.
// ────────────────────────────────────────────────────────────────────
// The "who?" select, shared by every add and edit dialog outside the Add tab (which has its
// own chips). A ledger with one member has nothing to choose, so this emits nothing at all
// and the entry simply carries no member — same as before sharing existed.
// Only the owner gets a select: they may file under themselves or any name that does not
// sign in, and those options render pickable while other people's logins render disabled —
// present so an entry already filed under them keeps its name when the owner edits the
// amount, unpickable so a fresh entry cannot land in their name. Everyone else files as
// themselves, so they get a hidden field instead of a choice. It keeps the id because the
// edit dialogs' JS writes the row's current member into it — an unchanged value is always
// accepted, so a member editing an amount cannot silently re-attribute the entry.
function memberSelect(array $mems, int $uid, string $role, string $id = '', ?int $selected = null): string {
    if (count($mems) < 2) return '';
    if ($role !== ROLE_OWNER) {
        $own = 0;
        foreach ($mems as $m) if ((int)($m['user_id'] ?? 0) === $uid) $own = (int)$m['id'];
        return '<input type="hidden" name="member_id"' . ($id !== '' ? ' id="' . h($id) . '"' : '')
             . ' value="' . $own . '">';
    }
    $mine = attributableIds($mems, $uid, $role);
    $out = '<select class="select" name="member_id"' . ($id !== '' ? ' id="' . h($id) . '"' : '') . '>'
         . '<option value="0">Anyone</option>';
    foreach ($mems as $m) {
        $mid = (int)$m['id'];
        $on  = ($selected !== null && $mid === $selected) ? ' selected' : '';
        $off = in_array($mid, $mine, true) ? '' : ' disabled';
        $out .= '<option value="' . $mid . '"' . $on . $off . '>' . h($m['name'])
              . ($off !== '' ? ' — signs in' : '') . '</option>';
    }
    return $out . '</select>';
}

// The "who?" filter. Same pills as the Invest tab's All/Active/Archived row, so the two
// filters on this app look and behave alike; setWho() keeps whatever query string the page is
// already carrying, which is why these are buttons rather than per-page hrefs.
//
// Two conditions, both required. There must be more than one name to choose between, and the
// ledger must actually be shared — on a ledger only one person can open, "who spent it?" is a
// question nobody is asking, and the row is just clutter above every list.
function whoFilterRow(PDO $db, int $hid, array $mems, int $who): string {
    if (count($mems) < 2) return '';
    $n = $db->prepare("SELECT COUNT(*) FROM household_users WHERE household_id = ?");
    $n->execute([$hid]);
    if ((int)$n->fetchColumn() < 2) return '';

    $pills = '<button type="button" class="pill-btn' . ($who === 0 ? ' on' : '')
           . '" onclick="setWho(0)">All</button>';
    foreach ($mems as $m) {
        $id = (int)$m['id'];
        $pills .= '<button type="button" class="pill-btn' . ($id === $who ? ' on' : '')
                . '" onclick="setWho(' . $id . ')">' . h($m['name']) . '</button>';
    }
    return '<div class="pill-row" role="group" aria-label="Filter by person">' . $pills . '</div>';
}

function renderLedgers(PDO $db, array $user): void {
    $hid     = (int)$user['household_id'];
    $uid     = (int)$user['id'];
    $isOwner = ($user['role'] ?? ROLE_MEMBER) === ROLE_OWNER;

    $mine = $user['ledgers'] ?? ledgersFor($db, $uid);

    $people = $db->prepare(
        "SELECT u.id, u.name, u.email, hu.role
         FROM household_users hu JOIN users u ON u.id = hu.user_id
         WHERE hu.household_id = ? ORDER BY hu.joined_at, u.id"
    );
    $people->execute([$hid]);
    $people = $people->fetchAll();

    // Every name that can appear on an entry, including the ones belonging to people who sign
    // in. Two cards, two questions: the one above is who may open the ledger, this one is what
    // they are called on an entry. They are genuinely different — a household calls someone
    // "Appa" long after Google has decided he is "Rajesh Kumar".
    $labels = $db->prepare(
        "SELECT m.id, m.name, m.user_id, u.name AS user_name
         FROM members m LEFT JOIN users u ON u.id = m.user_id
         WHERE m.household_id = ? ORDER BY m.id"
    );
    $labels->execute([$hid]);
    $labels = $labels->fetchAll();
    $memberCount = (int)$db->query("SELECT COUNT(*) FROM members WHERE household_id = " . (int)$hid)->fetchColumn();

    // Only the owner can mint one, so only the owner pays for the lookup.
    $inv = null;
    if ($isOwner) {
        // Seconds-left comes from MySQL, not PHP. `expires_at` is written with MySQL's NOW()
        // and enforced against MySQL's NOW(), and the two clocks sit in different timezones —
        // doing the subtraction here read 360 minutes for a 30-minute link.
        $s = $db->prepare(
            "SELECT token, TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS secs_left FROM invites
             WHERE household_id = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1"
        );
        $s->execute([$hid]);
        $inv = $s->fetch() ?: null;
    }
    $full = count($people) >= HOUSEHOLD_USERS_MAX;
    $link = $inv ? originUrl() . '/join?t=' . $inv['token'] : '';
    $here = '';
    foreach ($mine as $l) if ((int)$l['id'] === $hid) $here = (string)$l['name'];

    ob_start();
    ?>
    <div class="month-switch">
      <a href="/#profile" class="btn btn-icon" aria-label="Back"><?= icon('chevron-left', 20) ?></a>
      <div class="label" style="font-size:16px;">Ledgers &amp; sharing</div>
      <span class="btn btn-icon" style="opacity:0; pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
    </div>

    <?php if (count($mine) > 1): ?>
      <div class="muted" style="font-size:12px; margin: 0 2px 6px;">
        You belong to <?= count($mine) ?> ledgers. Tap one to manage it below, or
        <strong>Open</strong> to start using it.
      </div>
      <?php /* Two submit buttons, one form, differing only in where they send you back to.
               Tapping the row keeps you on this page so its settings are right there; Open
               leaves for the ledger itself. Both do the same switch — the selected ledger IS
               the active one, which is what keeps every handler on this page unambiguous
               about which ledger it is writing to. */ ?>
      <?php foreach ($mine as $l): $on = (int)$l['id'] === $hid; ?>
        <form method="post" action="/ledgers/switch"
              style="display:flex; gap:8px; align-items:stretch; margin-bottom:8px;">
          <?= csrfInput() ?>
          <input type="hidden" name="household_id" value="<?= (int)$l['id'] ?>">
          <button type="submit" name="back" value="/ledgers" class="card elev-sm row"
                  aria-current="<?= $on ? 'true' : 'false' ?>"
                  style="flex:1; margin:0; text-align:left; border:none; cursor:pointer;<?= $on ? ' outline:2px solid var(--color-accent); outline-offset:-2px;' : '' ?>">
            <span class="row-icon"><?= icon($on ? 'check' : ($l['role'] === ROLE_OWNER ? 'wallet' : 'users'), 18) ?></span>
            <span class="row-main">
              <span class="title" style="display:block;"><?= h($l['name']) ?></span>
              <span class="sub" style="display:block;">
                <?= $l['role'] === ROLE_OWNER ? 'You own this' : 'Shared with you' ?>
                · <?= (int)$l['people'] ?> <?= (int)$l['people'] === 1 ? 'person' : 'people' ?>
              </span>
            </span>
          </button>
          <button type="submit" name="back" value="/" class="btn<?= $on ? ' btn-primary' : '' ?>"
                  style="white-space:nowrap;">Open</button>
        </form>
      <?php endforeach; ?>
      <hr style="margin:14px 0;">
    <?php endif; ?>

    <div class="card elev-sm" style="padding:14px;">
      <h4 style="margin:0 0 8px;">This ledger</h4>
      <?php if ($isOwner): ?>
        <form method="post" action="/ledgers/rename" class="row-form">
          <?= csrfInput() ?>
          <input type="hidden" name="back" value="/ledgers">
          <input class="input" name="name" maxlength="80" value="<?= h($here) ?>" placeholder="Ledger name">
          <button class="btn btn-primary" type="submit">Rename</button>
        </form>
      <?php else: ?>
        <div style="font-size:15px;"><?= h($here) ?></div>
      <?php endif; ?>

      <?php /* How money is written belongs to the ledger, not to whoever is reading it — a
               household keeps one set of books, and two people in it must not see the same row
               as ₹1,00,000 and $100,000. Members see the setting; the owner sets it. */ ?>
      <?php $cur = $_SESSION['currency'] ?? '₹'; $nf = $_SESSION['numfmt'] ?? 'indian'; ?>
      <hr style="margin:12px 0 10px;">
      <h4 style="margin:0 0 6px;">How money is written</h4>
      <?php if ($isOwner): ?>
        <form method="post" action="/currency" class="row-form" style="margin-bottom:8px;">
          <?= csrfInput() ?>
          <input type="hidden" name="back" value="/ledgers">
          <input class="input" name="symbol" value="<?= h($cur) ?>" maxlength="1" required
                 aria-label="Currency symbol" title="One symbol, like ₹, $ or €"
                 style="max-width:70px; text-align:center; font-family:var(--font-heading); font-size:18px;">
          <button class="btn btn-primary" type="submit">Save</button>
        </form>
        <form method="post" action="/number-format" class="row-form">
          <?= csrfInput() ?>
          <input type="hidden" name="back" value="/ledgers">
          <div class="seg" role="group" aria-label="Number grouping">
            <button class="seg-opt<?= $nf === 'indian' ? ' on' : '' ?>" type="submit" name="style" value="indian"
                    <?= $nf === 'indian' ? 'aria-current="true"' : '' ?>><?= h($cur) ?>10,00,000</button>
            <button class="seg-opt<?= $nf === 'world' ? ' on' : '' ?>" type="submit" name="style" value="world"
                    <?= $nf === 'world' ? 'aria-current="true"' : '' ?>><?= h($cur) ?>1,000,000</button>
          </div>
        </form>
      <?php else: ?>
        <div class="muted" style="font-size:13px;">
          <?= h(fmtShort(1000000)) ?> — set by the ledger's owner.
        </div>
      <?php endif; ?>
    </div>

    <div class="card elev-sm" style="padding:14px; margin-top:10px;">
      <h4 style="margin:0 0 2px;">Signed in (<?= count($people) ?> of <?= HOUSEHOLD_USERS_MAX ?>)</h4>
      <div class="muted" style="font-size:12px; margin-bottom:8px;">People who can open this ledger.</div>
      <?php foreach ($people as $p): ?>
        <div class="cat-row" style="padding:4px 0;">
          <div style="flex:1;">
            <div style="font-size:14px;"><?= h($p['name']) ?><?= (int)$p['id'] === $uid ? ' (you)' : '' ?></div>
            <div class="muted" style="font-size:12px;"><?= h($p['email']) ?></div>
          </div>
          <?php if ($p['role'] === ROLE_OWNER): ?>
            <span class="muted" style="font-size:12px;">Owner</span>
          <?php elseif ($isOwner): ?>
            <button type="button" class="icon-btn" aria-label="Remove person"
                    onclick='askConfirm(<?= h(json_encode([
                        "action" => "/household-users/remove",
                        "id"     => (int)$p['id'],
                        "back"   => "/ledgers",
                        "csrf"   => csrfToken(),
                        "title"  => "Remove " . $p['name'] . "?",
                        "body"   => "They lose access to this ledger. Everything they entered stays — it is the household's record, not theirs.",
                        "ok"     => "Remove",
                    ])) ?>)'><?= icon('x', 16) ?></button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <?php /* Adding and renaming are everyone's job — neither loses anything. Removing takes
               away a name the rest of the household files entries under, so that stays with
               the owner. The server enforces the same split; this only reflects it. */ ?>
        <hr style="margin:12px 0 8px;">
        <h4 style="margin:0 0 2px;">Names on entries</h4>
        <div class="muted" style="font-size:12px; margin-bottom:8px;">
          What each person is called on an entry and on the filter. Rename freely — entries stay
          attached to the person, not to the spelling. Add a name for someone who does not sign
          in: a child, a parent, a shared card. A name gets no access; only an invite does.
        </div>
        <?php foreach ($labels as $m): $linked = !empty($m['user_id']); ?>
          <div class="row-form" style="margin-bottom:6px; align-items:center;">
            <form method="post" action="/members/update" class="row-form" style="flex:1; margin:0;">
              <?= csrfInput() ?>
              <input type="hidden" name="back" value="/ledgers">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <input class="input" name="name" value="<?= h($m['name']) ?>" maxlength="60" required
                     aria-label="Name on entries">
              <button class="btn" type="submit">Save</button>
            </form>
            <?php if ($linked): ?>
              <span class="muted" style="font-size:11px; white-space:nowrap;" title="<?= h($m['user_name'] ?? '') ?>">signs in</span>
            <?php elseif ($isOwner && $memberCount > 1): ?>
              <button type="button" class="icon-btn" aria-label="Remove name"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/members/delete",
                          "id"     => (int)$m['id'],
                          "back"   => "/ledgers",
                          "csrf"   => csrfToken(),
                          "title"  => "Remove " . $m['name'] . "?",
                          "body"   => "Entries already filed under them stay logged and keep the name; new ones can no longer name them.",
                          "ok"     => "Remove",
                      ])) ?>)'><?= icon('x', 16) ?></button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
          <form method="post" action="/members" class="row-form" style="margin-top:10px;">
            <?= csrfInput() ?>
            <input type="hidden" name="back" value="/ledgers">
            <input class="input" name="name" placeholder="Add a name" maxlength="60">
            <button class="btn" type="submit">Add</button>
          </form>
    </div>

    <?php if ($isOwner): ?>
      <div class="card elev-sm" style="padding:14px; margin-top:10px;">
        <h4 style="margin:0 0 8px;">Invite someone</h4>
        <?php if ($full): ?>
          <div class="muted" style="font-size:13px;">
            This ledger is full — <?= HOUSEHOLD_USERS_MAX ?> people is the limit. Remove someone to make room.
          </div>
        <?php elseif ($link !== ''): ?>
          <div class="muted" style="font-size:12px; margin-bottom:6px;">
            Send this to one person. It works <strong>once</strong> and then stops —
            <span id="inv-left" data-secs="<?= max(0, (int)$inv['secs_left']) ?>">expires in
              <?= max(1, (int)ceil(max(0, (int)$inv['secs_left']) / 60)) ?> min</span>.
          </div>
          <div class="row-form">
            <input class="input" id="inv-link" readonly value="<?= h($link) ?>" onclick="this.select()">
            <button class="btn btn-primary" type="button" onclick="copyInvite()"><?= icon('copy', 16) ?></button>
          </div>
          <div class="row-form" style="margin-top:8px;">
            <form method="post" action="/invite" style="flex:1;">
              <?= csrfInput() ?><input type="hidden" name="back" value="/ledgers">
              <button class="btn btn-block" type="submit">New link</button>
            </form>
            <form method="post" action="/invite/revoke" style="flex:1;">
              <?= csrfInput() ?><input type="hidden" name="back" value="/ledgers">
              <button class="btn btn-danger btn-block" type="submit">Cancel link</button>
            </form>
          </div>
          <?php /* Emitted here, not at the foot of the page: both ids only exist while a live
                   link does, and a script reaching for an absent element is exactly what
                   `--preflight` fails the build over. */ ?>
          <script>
          function copyInvite() {
            var el = document.getElementById('inv-link');
            el.select();
            // The Clipboard API needs a secure context; select()+execCommand is the fallback
            // that still works when the app is opened over plain http on the home network.
            if (navigator.clipboard) navigator.clipboard.writeText(el.value).catch(function () { document.execCommand('copy'); });
            else document.execCommand('copy');
          }
          (function () {
            var el = document.getElementById('inv-left');
            // Anchored on the browser's own clock plus the seconds the server said were left,
            // so a phone whose clock is off by an hour still counts down correctly.
            var until = Date.now() + (parseInt(el.dataset.secs, 10) || 0) * 1000;
            setInterval(function tick() {
              var left = Math.ceil((until - Date.now()) / 60000);
              el.textContent = left > 0 ? 'expires in ' + left + ' min' : 'expired — make a new one';
              return tick;
            }(), 20000);
          })();
          </script>
        <?php else: ?>
          <div class="muted" style="font-size:12px; margin-bottom:8px;">
            Creates a single-use link that stops working after <?= INVITE_TTL_MINUTES ?> minutes.
            They sign in with Google and keep their own ledger too.
          </div>
          <form method="post" action="/invite">
            <?= csrfInput() ?><input type="hidden" name="back" value="/ledgers">
            <button class="btn btn-primary btn-block" type="submit">Create invite link</button>
          </form>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="card elev-sm" style="padding:14px; margin-top:10px;">
        <button class="btn btn-danger btn-block" type="button"
                onclick='askConfirm(<?= h(json_encode([
                    "action" => "/ledgers/leave",
                    "back"   => "/ledgers",
                    "csrf"   => csrfToken(),
                    "title"  => "Leave this ledger?",
                    "body"   => "You will need a fresh invite to come back. Everything you entered stays with the household.",
                    "ok"     => "Leave",
                ])) ?>)'>Leave this ledger</button>
      </div>
    <?php endif; ?>

    <?php
    layout($db, $user, '', ob_get_clean(), '/ledgers');
}

function termsBody(): string {
    ob_start();
    ?>
    <div class="card" style="padding: var(--space-6) var(--space-4); gap: var(--space-4); line-height:1.55;">
      <h2 style="margin:0; font-family:var(--font-heading); font-size:24px;">Terms &amp; conditions</h2>
      <div style="font-size:12px; color:var(--color-neutral-800);">Last updated 3 Aug 2026</div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">Open source</h3>
        <p style="margin:0; font-size:14px;">Open Ledger is an open-source project. The source lives at
          <a href="https://github.com/xpertxyz/OpenLedger" style="color:var(--color-accent-700);">github.com/xpertxyz/OpenLedger</a>.
          Inspect it, contribute, or self-host to keep full control of your data.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">Who can use it</h3>
        <p style="margin:0; font-size:14px;">Anyone with a Google account can sign in. Authentication uses Google
          Identity Services — this app never sees your Google password.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">How your data is stored</h3>
        <p style="margin:0; font-size:14px;">Your entries — expenses, investments, recurring items, categories and
          household members — are stored in a MySQL database. <strong>The data is not encrypted at rest.</strong>
          Anyone with access to the database server can read your entries. Only sign in with data you're comfortable
          storing this way. If that isn't OK for you, self-host so the database is yours.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">What we don't do</h3>
        <p style="margin:0; font-size:14px;">We do not sell, share, or otherwise transfer your data to third parties.
          No analytics tracker, no advertising, no third-party integrations receive your data. The only outbound
          request is Google's sign-in endpoint (for verifying your ID token at login).</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">No warranty</h3>
        <p style="margin:0; font-size:14px;">The app is provided as-is, without warranty. Keep backups of anything
          you care about.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">Contact</h3>
        <p style="margin:0; font-size:14px;">File issues at
          <a href="https://github.com/xpertxyz/OpenLedger/issues" style="color:var(--color-accent-700);">github.com/xpertxyz/OpenLedger/issues</a>.</p>
      </div>

      <a class="btn btn-block" href="/" style="text-align:center; margin-top:8px;">Back</a>
    </div>
    <?php
    return ob_get_clean();
}

// Authed: full app chrome (header, drawer, tabnav).
function renderTerms(PDO $db, array $user): void {
    layout($db, $user, '', termsBody(), '/terms');
}

// Unauthed: standalone page with the same minimal chrome the sign-in card uses.
function renderTermsPublic(): void {
    $sprite = SVG_SPRITE;
    $body   = termsBody();
    $cssV   = cssVersion();
    echo <<<HTML
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Open Ledger — Terms &amp; conditions</title>
<link rel="stylesheet" href="/design-tokens/styles.css?v={$cssV}">
<style>
  body { margin:0; background:var(--color-bg); }
  .col { max-width:480px; margin:0 auto; padding: var(--space-4) var(--space-4) var(--space-8); }
</style>
</head>
<body>
$sprite
<div class="col">$body</div>
</body></html>
HTML;
}
