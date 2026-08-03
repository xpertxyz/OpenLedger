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
</svg>
SVG;

function icon(string $name, int $size = 20): string {
    $n = htmlspecialchars($name, ENT_QUOTES);
    return "<svg width=\"$size\" height=\"$size\" aria-hidden=\"true\"><use href=\"#icon-$n\"/></svg>";
}

function fmt(float $amount): string {
    return ($_SESSION['currency'] ?? '₹') . number_format($amount, 2);
}

// Shared page frame: header, content, bottom tabnav, toasts.
function layout(array $user, string $tab, string $content, string $requestUri = '/'): void {
    $darkAttr  = $user['is_dark'] ? ' style="' . h(THEME_DARK_VARS) . '"' : '';
    $themeIcon = $user['is_dark'] ? 'sun' : 'moon';
    $initial   = h(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)));
    $sprite    = SVG_SPRITE;
    $backUri   = h($requestUri);
    $themeBtn  = icon($themeIcon, 18);
    $gearBtn   = icon('settings', 19);
    $csrf      = csrfInput();
    $csrfTok   = csrfJs();

    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Home Ledger</title>
<link rel="stylesheet" href="/design-tokens/styles.css">
<style>
  body { margin:0; background:var(--color-bg); }
  .col { max-width:480px; margin:0 auto; min-height:100vh; padding: 0 0 104px; box-sizing:border-box; }
  .hdr { display:flex; align-items:center; justify-content:space-between; padding: var(--space-4) var(--space-4) var(--space-2); }
  .brand { font-family:var(--font-heading); font-size:22px; }
  .hdr-actions { display:flex; align-items:center; gap:4px; }
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
  .pill-btn { padding:6px 14px; border-radius:999px; border:1.5px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-size:12px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
  .pill-btn.on { background:var(--color-accent); color:var(--color-bg); border-color:transparent; }
  .note-row { display:flex; gap:8px; }
  .note-row .input { flex:1; }

  .month-switch { display:flex; align-items:center; justify-content:space-between; }
  .month-switch .label { font-family:var(--font-heading); font-size:18px; }
  .total-card { padding: var(--space-4); text-align:center; }
  .total-card.accent { background:var(--color-accent-700); color:var(--color-bg); }
  .total-card.sage { background:var(--color-accent-2-700); color:var(--color-bg); }
  .total-card .big { font-family:var(--font-heading); font-size:32px; }
  .total-card .sub { font-size:13px; opacity:.85; }
  .cat-bar { padding: 10px 14px; }
  .cat-bar .top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
  .cat-bar .name { display:flex; align-items:center; gap:8px; font-size:14px; }
  .cat-bar .amt { font-size:13px; }
  .cat-bar .pct { font-size:11px; color:var(--color-neutral-800); margin-left:6px; }
  .bar { height:8px; background:var(--color-divider); border-radius:999px; margin-top:8px; overflow:hidden; }
  .bar > i { display:block; height:100%; background:var(--color-accent); border-radius:999px; }
  .day-hdr { font-family:var(--font-heading); font-size:14px; color:var(--color-neutral-800); margin: var(--space-3) 2px var(--space-2); }

  .toast { position:fixed; left:50%; bottom:96px; transform:translateX(-50%); padding:10px 18px; border-radius:999px; font-size:13px; z-index:100; max-width: calc(100% - 32px); text-align:center; box-shadow: var(--shadow-md); animation: toast-life 2.2s forwards; }
  .toast.success { background:var(--color-accent-2-700); color:var(--color-bg); }
  .toast.error   { background:var(--color-accent-700); color:var(--color-bg); animation: toast-life-long 3.6s forwards; }
  @keyframes toast-life {
    0%{opacity:0;transform:translate(-50%,8px);} 10%{opacity:1;transform:translate(-50%,0);}
    80%{opacity:1;} 100%{opacity:0;}
  }
  @keyframes toast-life-long {
    0%{opacity:0;transform:translate(-50%,8px);} 5%{opacity:1;transform:translate(-50%,0);}
    90%{opacity:1;} 100%{opacity:0;}
  }

  .tabnav { position:fixed; left:50%; bottom:16px; transform:translateX(-50%); width:calc(100% - 32px); max-width:448px; background:var(--color-surface); border-radius:999px; padding:6px; display:flex; gap:2px; box-shadow:var(--shadow-md); box-sizing:border-box; }
  .tabnav a { flex:1; padding:8px 4px; border-radius:999px; text-decoration:none; color:var(--color-text); font-size:11px; display:flex; flex-direction:column; align-items:center; gap:2px; opacity:.7; }
  .tabnav a.on { background:var(--color-accent); color:var(--color-bg); opacity:1; }

  input[type="date"]::-webkit-calendar-picker-indicator { opacity:.6; }
  .select { padding:10px 12px; border-radius:var(--radius-sm); border:1px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-family:var(--font-body); font-size:14px; }
  .field-row { display:flex; gap:8px; }
  .field-row > * { flex:1; min-width:0; }

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
    <div class="brand">Home Ledger</div>
    <div class="hdr-actions">
      <form method="post" action="/theme">$csrf<input type="hidden" name="back" value="{$backUri}"><button class="btn btn-icon" type="submit" aria-label="Toggle theme" style="color:var(--color-text);">$themeBtn</button></form>
      <a href="/manage" class="btn btn-icon" aria-label="Manage" style="color:var(--color-text);">$gearBtn</a>
      <button class="avatar" type="button" aria-label="Sign out"
              onclick="askConfirm({action:'/signout', csrf:'{$csrfTok}', title:'Sign out?', body:'You will need to sign in again.', ok:'Sign out'})">$initial</button>
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
        ['invest',    '/invest',    'trending-up',  'Invest'],
        ['recurring', '/recurring', 'repeat',       'Recurring'],
    ];
    echo '<nav class="tabnav">';
    foreach ($tabs as [$key, $href, $ic, $label]) {
        $on = $tab === $key ? ' class="on"' : '';
        echo "<a href=\"" . h($href) . "\"$on>" . icon($ic, 18) . h($label) . "</a>";
    }
    echo '</nav>';

    // Shared confirmation dialog + trigger helper. Every destructive form / signout uses this.
    echo <<<DLG
<dialog id="confirm-dlg" class="confirm" aria-labelledby="dlg-title">
  <form method="post" id="confirm-form">
    <input type="hidden" name="_csrf" id="confirm-csrf">
    <input type="hidden" name="id" id="confirm-id">
    <input type="hidden" name="back" id="confirm-back">
    <div class="dlg-title" id="dlg-title"></div>
    <div class="dlg-body" id="dlg-body"></div>
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
  document.getElementById('confirm-csrf').value = opts.csrf || '';
  document.getElementById('dlg-title').textContent = opts.title || 'Are you sure?';
  document.getElementById('dlg-body').textContent  = opts.body  || '';
  document.getElementById('confirm-ok').textContent = opts.ok || 'Delete';
  document.getElementById('confirm-ok').className = 'btn ' + (opts.danger === false ? 'btn-primary' : 'btn-danger');
  document.getElementById('confirm-dlg').showModal();
}
</script>
</body></html>
DLG;
}

// ─── Sign-in ────────────────────────────────────────────────────────
// Google Identity Services: One Tap auto-prompts, and the "Sign in with Google"
// button is Google's official standard rendering. Both submit the ID token to
// /signin along with a g_csrf_token cookie+field pair (double-submit CSRF).
function renderSignIn(): void {
    $sprite   = SVG_SPRITE;
    $clientId = h(GOOGLE_CLIENT_ID);
    $devStub  = isDevStubActive(GOOGLE_CLIENT_ID);
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
    echo <<<HTML
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Home Ledger — Sign in</title>
<link rel="stylesheet" href="/design-tokens/styles.css">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
  body { margin:0; background:var(--color-bg); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .toast { padding:8px 14px; border-radius:999px; background:var(--color-accent-700); color:var(--color-bg); font-size:13px; }
</style>
</head>
<body>
$sprite
<div style="width:100%;max-width:340px;padding:var(--space-4);">
  <div class="card elev-lg" style="padding:var(--space-6);align-items:center;text-align:center;gap:var(--space-4);">
    <img src="/assets/logo/home-ledger-logo-wordmark.svg" alt="Home Ledger" style="max-width:220px;width:100%;height:auto;">
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
    $selectedMem = (int)($_GET['mem'] ?? ($mems[0]['id'] ?? 0));
    $showNewCat  = isset($_GET['newcat']);

    // Selected category sorts first, rest keep their original order.
    if ($selectedCat) {
        usort($cats, function ($a, $b) use ($selectedCat) {
            $aSel = $a['id'] == $selectedCat ? 0 : 1;
            $bSel = $b['id'] == $selectedCat ? 0 : 1;
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
      <div class="cat-grid">
        <?php foreach ($cats as $c): ?>
          <button type="button" class="cat-chip<?= $c['id'] == $selectedCat ? ' on' : '' ?>"
                  onclick="document.getElementById('cat-input').value=<?= (int)$c['id'] ?>;document.querySelectorAll('.cat-chip').forEach(e=>e.classList.remove('on'));this.classList.add('on');">
            <?= icon($c['icon'], 21) ?>
            <span><?= h($c['name']) ?></span>
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

      <?php if (count($mems) > 1): ?>
        <input type="hidden" name="member_id" id="mem-input" value="<?= (int)$selectedMem ?>">
        <div class="pill-row">
          <?php foreach ($mems as $m): ?>
            <button type="button" class="pill-btn<?= $m['id'] == $selectedMem ? ' on' : '' ?>"
                    onclick="document.getElementById('mem-input').value=<?= (int)$m['id'] ?>;document.querySelectorAll('.pill-row .pill-btn').forEach(e=>e.classList.remove('on'));this.classList.add('on');">
              <?= h($m['name']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      <?php elseif ($mems): ?>
        <input type="hidden" name="member_id" value="<?= (int)$mems[0]['id'] ?>">
      <?php endif; ?>
    </form>

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
    layout($user, 'add', $content, '/');
}

// ─── History ────────────────────────────────────────────────────────
function renderHistory(PDO $db, array $user, int $offset): void {
    $hid = (int)$user['household_id'];
    if ($offset < 0)   $offset = 0;
    if ($offset > 600) $offset = 600; // sanity cap ~50 years
    $anchor     = (new DateTimeImmutable('first day of this month 00:00:00'))->modify("-{$offset} months");
    $monthStart = $anchor->format('Y-m-d');
    $monthEnd   = $anchor->modify('+1 month')->format('Y-m-d');
    $label      = $anchor->format('F Y');

    // Range predicate hits the (household_id, date) index; portable across MySQL/MariaDB
    // configs where DATE_FORMAT or an unquoted `date` identifier could misbehave.
    $rows = $db->prepare(
        "SELECT e.*, c.name AS cat_name, c.icon AS cat_icon, m.name AS mem_name
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id
         LEFT JOIN members m ON m.id = e.member_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?
         ORDER BY e.`date` DESC, e.id DESC"
    );
    $rows->execute([$hid, $monthStart, $monthEnd]);
    $expenses = $rows->fetchAll();
    $total = array_sum(array_map(fn($r) => (float)$r['amount'], $expenses));

    // For the edit-expense modal.
    $catList = $db->prepare("SELECT id, name FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $catList->execute([$hid]); $catList = $catList->fetchAll();
    $memList = $db->prepare("SELECT id, name FROM members WHERE household_id = ? ORDER BY id");
    $memList->execute([$hid]); $memList = $memList->fetchAll();

    $byCat = [];
    foreach ($expenses as $e) {
        $k = (int)($e['category_id'] ?? 0);
        if (!isset($byCat[$k])) $byCat[$k] = ['name' => $e['cat_name'] ?? 'Uncategorised', 'icon' => $e['cat_icon'] ?? 'tag', 'amt' => 0.0];
        $byCat[$k]['amt'] += (float)$e['amount'];
    }
    uasort($byCat, fn($a, $b) => $b['amt'] <=> $a['amt']);

    ob_start();
    ?>
    <div class="month-switch">
      <a href="/history?m=<?= $offset + 1 ?>" class="btn btn-icon" aria-label="Previous month"><?= icon('chevron-left', 20) ?></a>
      <div class="label"><?= h($label) ?></div>
      <?php if ($offset > 0): ?>
        <a href="/history?m=<?= $offset - 1 ?>" class="btn btn-icon" aria-label="Next month"><?= icon('chevron-right', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
      <?php endif; ?>
    </div>

    <?php if (!$expenses): ?>
      <div class="empty">No expenses this month.</div>
    <?php else: ?>
      <div class="card total-card accent">
        <div class="big"><?= h(fmt((float)$total)) ?></div>
        <div class="sub"><?= count($expenses) ?> <?= count($expenses) === 1 ? 'entry' : 'entries' ?></div>
      </div>

      <div class="stack">
        <?php foreach ($byCat as $c): $pct = $total > 0 ? ($c['amt'] / $total) * 100 : 0; ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name"><?= icon($c['icon'], 18) ?> <?= h($c['name']) ?></div>
              <div><span class="amt"><?= h(fmt($c['amt'])) ?></span><span class="pct"><?= number_format($pct, 0) ?>%</span></div>
            </div>
            <div class="bar"><i style="width: <?= number_format(max(2, $pct), 2) ?>%"></i></div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php
      $byDay = [];
      foreach ($expenses as $e) { $byDay[$e['date']][] = $e; }
      foreach ($byDay as $day => $entries):
        $dayLabel = (new DateTimeImmutable($day))->format('D, M j');
      ?>
        <div class="day-hdr"><?= h($dayLabel) ?></div>
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
              <button class="icon-btn" type="button" aria-label="Edit"
                      onclick='openEditExpense(<?= h($rowJson) ?>)'>
                <?= icon('edit', 15) ?>
              </button>
              <button class="icon-btn" type="button" aria-label="Delete"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/expenses/delete",
                          "id"     => (int)$e['id'],
                          "back"   => "/history?m=$offset",
                          "csrf"   => csrfToken(),
                          "title"  => "Delete expense?",
                          "body"   => fmt((float)$e['amount']) . ' — ' . ($e['cat_name'] ?? 'Uncategorised'),
                          "ok"     => "Delete",
                      ])) ?>)'>
                <?= icon('trash-2', 15) ?>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Edit-expense modal: one shared <dialog>; row click fills it via openEditExpense() -->
    <dialog id="edit-expense-dlg" class="confirm" style="max-width:360px;">
      <form method="post" action="/expenses/update">
        <?= csrfInput() ?>
        <input type="hidden" name="id" id="ed-id">
        <input type="hidden" name="back" value="/history?m=<?= $offset ?>">
        <div class="dlg-title">Edit expense</div>

        <div class="field-row">
          <input class="input" name="amount" id="ed-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
          <input class="input" name="date" id="ed-date" type="date" required style="flex:0 0 auto; width:auto;">
        </div>

        <select class="select" name="category_id" id="ed-category" required>
          <?php foreach ($catList as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <?php if (count($memList) > 1): ?>
          <select class="select" name="member_id" id="ed-member">
            <option value="">— No member —</option>
            <?php foreach ($memList as $m): ?>
              <option value="<?= (int)$m['id'] ?>"><?= h($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($memList): ?>
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
    <?php
    $content = ob_get_clean();
    layout($user, 'history', $content, "/history?m=$offset");
}

// ─── Investments ────────────────────────────────────────────────────
function renderInvest(PDO $db, array $user, bool $showForm): void {
    $hid = (int)$user['household_id'];
    $rows = $db->prepare("SELECT * FROM investments WHERE household_id = ? ORDER BY date DESC, id DESC");
    $rows->execute([$hid]); $invs = $rows->fetchAll();
    $total = array_sum(array_map(fn($r) => (float)$r['amount'], $invs));

    ob_start();
    ?>
    <?php if ($invs): ?>
      <div class="card total-card sage">
        <div class="sub">Total invested</div>
        <div class="big"><?= h(fmt((float)$total)) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <form method="post" action="/investments" class="card stack" style="padding:var(--space-4); gap:10px;">
        <?= csrfInput() ?>
        <input class="input" name="name" placeholder="e.g. SIP - Mutual Fund" required maxlength="80" id="inv-name"
               oninput="document.getElementById('inv-save').disabled = !(this.value.trim() && parseFloat(document.getElementById('inv-amt').value) > 0)">
        <div class="field-row">
          <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Amount" id="inv-amt"
                 oninput="document.getElementById('inv-save').disabled = !(document.getElementById('inv-name').value.trim() && parseFloat(this.value) > 0)">
          <select class="select" name="type">
            <?php foreach (['SIP','Stocks','FD-RD','Gold','PPF-EPF','Other'] as $t): ?>
              <option><?= h($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <input class="input" name="date" type="date" value="<?= h(today()) ?>">
        <div class="field-row">
          <a class="btn btn-secondary btn-block" href="/invest">Cancel</a>
          <button class="btn btn-primary btn-block" type="submit" id="inv-save" disabled>Save</button>
        </div>
      </form>
    <?php else: ?>
      <a class="btn btn-secondary btn-block" href="/invest?new=1"><?= icon('plus', 16) ?> &nbsp;Add investment</a>
    <?php endif; ?>

    <?php if (!$invs): ?>
      <div class="empty">Nothing logged yet.</div>
    <?php else: ?>
      <div class="stack">
        <?php foreach ($invs as $i): ?>
          <?php $invJson = json_encode([
              'id'     => (int)$i['id'],
              'name'   => $i['name'],
              'amount' => (string)$i['amount'],
              'type'   => $i['type'],
              'date'   => $i['date'],
          ]); ?>
          <div class="card elev-sm row">
            <div class="row-icon sage"><?= icon('trending-up', 16) ?></div>
            <div class="row-main">
              <div class="title"><?= h($i['name']) ?></div>
              <div class="sub"><?= h($i['type']) ?> · <?= h((new DateTimeImmutable($i['date']))->format('M j, Y')) ?></div>
            </div>
            <div class="row-amt"><?= h(fmt((float)$i['amount'])) ?></div>
            <button class="icon-btn" type="button" aria-label="Edit"
                    onclick='openEditInvestment(<?= h($invJson) ?>)'>
              <?= icon('edit', 15) ?>
            </button>
            <button class="icon-btn" type="button" aria-label="Delete"
                    onclick='askConfirm(<?= h(json_encode([
                        "action" => "/investments/delete",
                        "id"     => (int)$i['id'],
                        "csrf"   => csrfToken(),
                        "title"  => "Delete investment?",
                        "body"   => $i['name'] . ' — ' . fmt((float)$i['amount']),
                        "ok"     => "Delete",
                    ])) ?>)'>
              <?= icon('trash-2', 15) ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <dialog id="edit-investment-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/investments/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="ei-id">
          <div class="dlg-title">Edit investment</div>
          <input class="input" name="name" id="ei-name" required maxlength="80" placeholder="Name">
          <div class="field-row">
            <input class="input" name="amount" id="ei-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
            <select class="select" name="type" id="ei-type">
              <?php foreach (['SIP','Stocks','FD-RD','Gold','PPF-EPF','Other'] as $t): ?>
                <option><?= h($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <input class="input" name="date" id="ei-date" type="date" required>
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
        document.getElementById('edit-investment-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($user, 'invest', $content, '/invest');
}

// ─── Recurring ──────────────────────────────────────────────────────
function renderRecurring(PDO $db, array $user, bool $showForm): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = $cats->fetchAll();
    $rows = $db->prepare(
        "SELECT r.*, c.name AS cat_name FROM recurring r
         LEFT JOIN categories c ON c.id = r.category_id
         WHERE r.household_id = ? ORDER BY r.next_date, r.id"
    );
    $rows->execute([$hid]); $recs = $rows->fetchAll();

    ob_start();
    ?>
    <div class="muted">Rent, EMIs and subscriptions that repeat.</div>

    <?php if ($showForm): ?>
      <form method="post" action="/recurring" class="card stack" style="padding:var(--space-4); gap:10px;">
        <?= csrfInput() ?>
        <input class="input" name="name" placeholder="e.g. Rent" required maxlength="80" id="rec-name"
               oninput="document.getElementById('rec-save').disabled = !(this.value.trim() && parseFloat(document.getElementById('rec-amt').value) > 0)">
        <div class="field-row">
          <input class="input" name="amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" placeholder="Amount" id="rec-amt"
                 oninput="document.getElementById('rec-save').disabled = !(document.getElementById('rec-name').value.trim() && parseFloat(this.value) > 0)">
          <select class="select" name="category_id">
            <?php foreach ($cats as $c): ?>
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
        </div>
        <div class="field-row">
          <a class="btn btn-secondary btn-block" href="/recurring">Cancel</a>
          <button class="btn btn-primary btn-block" type="submit" id="rec-save" disabled>Save</button>
        </div>
      </form>
    <?php else: ?>
      <a class="btn btn-secondary btn-block" href="/recurring?new=1"><?= icon('plus', 16) ?> &nbsp;Add recurring</a>
    <?php endif; ?>

    <?php if (!$recs): ?>
      <div class="empty">No recurring expenses.</div>
    <?php else: ?>
      <div class="stack">
        <?php foreach ($recs as $r): ?>
          <?php $recJson = json_encode([
              'id'          => (int)$r['id'],
              'name'        => $r['name'],
              'amount'      => (string)$r['amount'],
              'category_id' => (int)($r['category_id'] ?? 0),
              'frequency'   => $r['frequency'],
              'next_date'   => $r['next_date'],
          ]); ?>
          <div class="card elev-sm row">
            <div class="row-icon"><?= icon('repeat', 16) ?></div>
            <div class="row-main">
              <div class="title"><?= h($r['name']) ?></div>
              <div class="sub"><?= h(($r['cat_name'] ?? 'Uncategorised') . ' · ' . ucfirst($r['frequency']) . ' · next ' . (new DateTimeImmutable($r['next_date']))->format('M j, Y')) ?></div>
            </div>
            <div class="row-amt"><?= h(fmt((float)$r['amount'])) ?></div>
            <button class="icon-btn" type="button" aria-label="Edit"
                    onclick='openEditRecurring(<?= h($recJson) ?>)'>
              <?= icon('edit', 15) ?>
            </button>
            <button class="icon-btn" type="button" aria-label="Delete"
                    onclick='askConfirm(<?= h(json_encode([
                        "action" => "/recurring/delete",
                        "id"     => (int)$r['id'],
                        "csrf"   => csrfToken(),
                        "title"  => "Delete recurring item?",
                        "body"   => $r['name'] . ' — ' . fmt((float)$r['amount']) . ' / ' . $r['frequency'],
                        "ok"     => "Delete",
                    ])) ?>)'>
              <?= icon('trash-2', 15) ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <dialog id="edit-recurring-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/recurring/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="er-id">
          <div class="dlg-title">Edit recurring item</div>
          <input class="input" name="name" id="er-name" required maxlength="80" placeholder="Name">
          <div class="field-row">
            <input class="input" name="amount" id="er-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount">
            <select class="select" name="category_id" id="er-category">
              <?php foreach ($cats as $c): ?>
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
          </div>
          <div class="dlg-actions">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-recurring-dlg').close()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </dialog>
      <script>
      function openEditRecurring(d) {
        document.getElementById('er-id').value        = d.id;
        document.getElementById('er-name').value      = d.name;
        document.getElementById('er-amount').value    = d.amount;
        document.getElementById('er-category').value  = d.category_id;
        document.getElementById('er-frequency').value = d.frequency;
        document.getElementById('er-next').value      = d.next_date;
        document.getElementById('edit-recurring-dlg').showModal();
      }
      </script>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    layout($user, 'recurring', $content, '/recurring');
}

// ─── Manage (categories + members) ──────────────────────────────────
function renderManage(PDO $db, array $user): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = $cats->fetchAll();
    $mems = $db->prepare("SELECT * FROM members WHERE household_id = ? ORDER BY id");
    $mems->execute([$hid]); $mems = $mems->fetchAll();
    $canDeleteMember = count($mems) > 1;
    $currency = $_SESSION['currency'] ?? '₹';

    ob_start();
    ?>
    <div class="card" style="padding:var(--space-4); gap:var(--space-3);">
      <h3 style="margin:0; font-size:16px;">Currency</h3>
      <form method="post" action="/currency" class="note-row">
        <?= csrfInput() ?>
        <input class="input" name="symbol" value="<?= h($currency) ?>" maxlength="8" style="max-width:80px; text-align:center; font-family:var(--font-heading); font-size:20px;">
        <button class="btn btn-primary" type="submit">Save</button>
      </form>
      <div class="muted" style="font-size:12px;">Any symbol (₹, $, €, £, Rs., etc.) — displays in front of every amount.</div>
    </div>

    <div class="card" style="padding:var(--space-4); gap:var(--space-3);">
      <h3 style="margin:0; font-size:16px;">Categories</h3>
      <div style="display:flex; flex-wrap:wrap; gap:6px;">
        <?php foreach ($cats as $c): ?>
          <span class="tag tag-neutral" style="display:inline-flex; align-items:center; gap:6px;">
            <?= icon($c['icon'], 14) ?> <?= h($c['name']) ?>
            <?php if ($c['is_custom']): ?>
              <button type="button" aria-label="Delete category"
                      style="background:none;border:none;color:inherit;cursor:pointer;padding:0 0 0 2px;font-size:14px;line-height:1;"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/categories/delete",
                          "id"     => (int)$c['id'],
                          "csrf"   => csrfToken(),
                          "title"  => "Delete category?",
                          "body"   => 'Existing expenses in this category stay logged but become uncategorised: ' . $c['name'],
                          "ok"     => "Delete",
                      ])) ?>)'>×</button>
            <?php endif; ?>
          </span>
        <?php endforeach; ?>
      </div>
      <form method="post" action="/categories" class="note-row">
        <?= csrfInput() ?>
        <input class="input" name="name" placeholder="New category" maxlength="50">
        <button class="btn btn-primary" type="submit">Add</button>
      </form>
    </div>

    <div class="card" style="padding:var(--space-4); gap:var(--space-3);">
      <h3 style="margin:0; font-size:16px;">Household members</h3>
      <div class="stack">
        <?php foreach ($mems as $m): ?>
          <div class="row" style="padding: 6px 0;">
            <div class="row-main"><?= h($m['name']) ?></div>
            <?php if ($canDeleteMember): ?>
              <button class="icon-btn" type="button" aria-label="Remove member"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/members/delete",
                          "id"     => (int)$m['id'],
                          "csrf"   => csrfToken(),
                          "title"  => "Remove member?",
                          "body"   => 'Existing entries for ' . $m['name'] . ' stay logged; new ones can no longer be attributed to them.',
                          "ok"     => "Remove",
                      ])) ?>)'>×</button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <form method="post" action="/members" class="note-row">
        <?= csrfInput() ?>
        <input class="input" name="name" placeholder="Add member" maxlength="60">
        <button class="btn btn-primary" type="submit">Add</button>
      </form>
    </div>

    <a class="btn btn-block" href="/" style="text-align:center;">Done</a>
    <?php
    $content = ob_get_clean();
    layout($user, '', $content, '/manage');
}
