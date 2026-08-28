<?php
declare(strict_types=1);

// Dark-theme variable overrides — lifted verbatim from the prototype (THEME_DARK_VARS).
const THEME_DARK_VARS = '--color-bg:#201e1d;--color-surface:#2b2721;--color-text:#f3e9d8;'
  . '--color-divider:color-mix(in srgb, #f3e9d8 18%, transparent);'
  . '--color-neutral-100:#2c2822;--color-neutral-200:#363028;--color-neutral-300:#453d31;--color-neutral-400:#5a5040;--color-neutral-500:#786c56;--color-neutral-600:#9c8f76;--color-neutral-700:#bfb29a;--color-neutral-800:#dcd0ba;--color-neutral-900:#f3e9d8;'
  . '--color-accent:#e0864c;--color-accent-100:#3a2a1c;--color-accent-200:#4d3320;--color-accent-300:#6b4324;--color-accent-400:#95592c;--color-accent-500:#c06f35;--color-accent-600:#d97f42;--color-accent-700:#e79968;--color-accent-800:#f0b78c;--color-accent-900:#f8d7b8;'
  . '--color-accent-2:#93a86e;--color-accent-2-100:#2a301f;--color-accent-2-200:#333b26;--color-accent-2-300:#414c2f;--color-accent-2-400:#57643d;--color-accent-2-500:#71804f;--color-accent-2-600:#8a9863;--color-accent-2-700:#a8b884;--color-accent-2-800:#c4d1a8;--color-accent-2-900:#e2e8d0;'
  . '--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);'
  // Not a token: tells the browser to draw native widgets (date-picker calendar
  // icon, the open select list, scrollbars) in dark, instead of light-on-dark-invisible.
  // The CLOSED select is fully custom (.select in layout()); only the popup stays native.
  . 'color-scheme:dark;';

// The three palettes, each in both modes. Organic is the one the design system ships, so its
// light values ARE design-tokens/styles.css :root — nothing to repeat here but the status-bar
// colour. Harbor and Plum are generated in OKLCH on the SAME lightness scale as Organic
// (light 0.969→0.290, dark 0.290→0.930) and the same chroma arc, only the hues move. That is
// what keeps every component working unchanged: a rule that reads --color-accent-700 gets the
// same visual weight whichever palette is on. Retune a hue here, never a single step.
//
//   'sw' is what the picker draws — the four colours a palette is recognised by, and the
//   first of them is also the mobile status-bar colour.
const THEMES = [
    'organic' => [
        'name' => 'Organic', 'note' => 'Terracotta and sage on cream',
        'light' => [
            'vars' => '--theme-color:#f5ead8;',
            'sw' => ['bg' => '#f5ead8', 'surface' => '#ebddc5', 'accent' => '#c67139', 'accent2' => '#7a8a5e'],
        ],
        'dark' => [
            'vars' => THEME_DARK_VARS . '--theme-color:#201e1d;',
            'sw' => ['bg' => '#201e1d', 'surface' => '#2b2721', 'accent' => '#e0864c', 'accent2' => '#93a86e'],
        ],
    ],
    'harbor' => [
        'name' => 'Harbor', 'note' => 'Deep azure and teal on cool paper',
        'light' => [
            'vars' => '--color-bg:oklch(0.972 0.006 250);--color-surface:oklch(0.938 0.011 250);--color-text:oklch(0.250 0.022 258);--color-accent:oklch(0.560 0.155 258);--color-accent-2:oklch(0.600 0.095 196);--color-divider:color-mix(in srgb, var(--color-text) 16%, transparent);--color-neutral-100:oklch(0.969 0.006 250);--color-neutral-200:oklch(0.930 0.010 250);--color-neutral-300:oklch(0.870 0.013 250);--color-neutral-400:oklch(0.780 0.014 250);--color-neutral-500:oklch(0.680 0.015 250);--color-neutral-600:oklch(0.580 0.014 250);--color-neutral-700:oklch(0.479 0.012 250);--color-neutral-800:oklch(0.381 0.010 250);--color-neutral-900:oklch(0.290 0.006 250);--color-accent-100:oklch(0.969 0.015 258);--color-accent-200:oklch(0.930 0.033 258);--color-accent-300:oklch(0.870 0.063 258);--color-accent-400:oklch(0.780 0.111 258);--color-accent-500:oklch(0.680 0.155 258);--color-accent-600:oklch(0.580 0.148 258);--color-accent-700:oklch(0.479 0.130 258);--color-accent-800:oklch(0.381 0.100 258);--color-accent-900:oklch(0.290 0.065 258);--color-accent-2-100:oklch(0.969 0.044 196);--color-accent-2-200:oklch(0.930 0.061 196);--color-accent-2-300:oklch(0.870 0.074 196);--color-accent-2-400:oklch(0.780 0.083 196);--color-accent-2-500:oklch(0.680 0.086 196);--color-accent-2-600:oklch(0.580 0.083 196);--color-accent-2-700:oklch(0.479 0.074 196);--color-accent-2-800:oklch(0.381 0.061 196);--color-accent-2-900:oklch(0.290 0.044 196);--shadow-sm:0 1px 2px color-mix(in srgb, var(--color-neutral-900) 14%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, var(--color-neutral-900) 16%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, var(--color-neutral-900) 22%, transparent);--theme-color:#f3f6fa;color-scheme:light;',
            'sw' => ['bg' => '#f3f6fa', 'surface' => '#e5ebf2', 'accent' => '#3372cd', 'accent2' => '#209293'],
        ],
        'dark' => [
            'vars' => '--color-bg:oklch(0.235 0.016 255);--color-surface:oklch(0.278 0.021 255);--color-text:oklch(0.935 0.013 250);--color-accent:oklch(0.720 0.135 258);--color-accent-2:oklch(0.740 0.095 196);--color-divider:color-mix(in srgb, var(--color-text) 18%, transparent);--color-neutral-100:oklch(0.290 0.007 250);--color-neutral-200:oklch(0.340 0.010 250);--color-neutral-300:oklch(0.400 0.014 250);--color-neutral-400:oklch(0.480 0.017 250);--color-neutral-500:oklch(0.570 0.022 250);--color-neutral-600:oklch(0.660 0.023 250);--color-neutral-700:oklch(0.760 0.022 250);--color-neutral-800:oklch(0.850 0.020 250);--color-neutral-900:oklch(0.930 0.015 250);--color-accent-100:oklch(0.290 0.039 258);--color-accent-200:oklch(0.340 0.055 258);--color-accent-300:oklch(0.400 0.082 258);--color-accent-400:oklch(0.480 0.114 258);--color-accent-500:oklch(0.570 0.144 258);--color-accent-600:oklch(0.660 0.155 258);--color-accent-700:oklch(0.760 0.122 258);--color-accent-800:oklch(0.850 0.074 258);--color-accent-900:oklch(0.930 0.033 258);--color-accent-2-100:oklch(0.290 0.037 196);--color-accent-2-200:oklch(0.340 0.046 196);--color-accent-2-300:oklch(0.400 0.060 196);--color-accent-2-400:oklch(0.480 0.076 196);--color-accent-2-500:oklch(0.570 0.091 196);--color-accent-2-600:oklch(0.660 0.095 196);--color-accent-2-700:oklch(0.760 0.091 196);--color-accent-2-800:oklch(0.850 0.071 196);--color-accent-2-900:oklch(0.930 0.041 196);--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);--theme-color:#191f26;color-scheme:dark;',
            'sw' => ['bg' => '#191f26', 'surface' => '#212933', 'accent' => '#6da5f8', 'accent2' => '#57bebe'],
        ],
    ],
    'plum' => [
        'name' => 'Plum', 'note' => 'Berry and emerald on blush',
        'light' => [
            'vars' => '--color-bg:oklch(0.970 0.008 340);--color-surface:oklch(0.935 0.014 340);--color-text:oklch(0.245 0.024 335);--color-accent:oklch(0.560 0.175 348);--color-accent-2:oklch(0.600 0.105 160);--color-divider:color-mix(in srgb, var(--color-text) 16%, transparent);--color-neutral-100:oklch(0.969 0.006 340);--color-neutral-200:oklch(0.930 0.010 340);--color-neutral-300:oklch(0.870 0.013 340);--color-neutral-400:oklch(0.780 0.014 340);--color-neutral-500:oklch(0.680 0.015 340);--color-neutral-600:oklch(0.580 0.014 340);--color-neutral-700:oklch(0.479 0.012 340);--color-neutral-800:oklch(0.381 0.010 340);--color-neutral-900:oklch(0.290 0.006 340);--color-accent-100:oklch(0.969 0.018 348);--color-accent-200:oklch(0.930 0.042 348);--color-accent-300:oklch(0.870 0.083 348);--color-accent-400:oklch(0.780 0.155 348);--color-accent-500:oklch(0.680 0.161 348);--color-accent-600:oklch(0.580 0.154 348);--color-accent-700:oklch(0.479 0.135 348);--color-accent-800:oklch(0.381 0.104 348);--color-accent-900:oklch(0.290 0.068 348);--color-accent-2-100:oklch(0.969 0.041 160);--color-accent-2-200:oklch(0.930 0.056 160);--color-accent-2-300:oklch(0.870 0.068 160);--color-accent-2-400:oklch(0.780 0.077 160);--color-accent-2-500:oklch(0.680 0.079 160);--color-accent-2-600:oklch(0.580 0.077 160);--color-accent-2-700:oklch(0.479 0.068 160);--color-accent-2-800:oklch(0.381 0.056 160);--color-accent-2-900:oklch(0.290 0.041 160);--shadow-sm:0 1px 2px color-mix(in srgb, var(--color-neutral-900) 14%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, var(--color-neutral-900) 16%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, var(--color-neutral-900) 22%, transparent);--theme-color:#f9f3f7;color-scheme:light;',
            'sw' => ['bg' => '#f9f3f7', 'surface' => '#f0e6ec', 'accent' => '#b93e86', 'accent2' => '#3d936a'],
        ],
        'dark' => [
            'vars' => '--color-bg:oklch(0.232 0.019 330);--color-surface:oklch(0.275 0.025 330);--color-text:oklch(0.935 0.013 340);--color-accent:oklch(0.725 0.145 350);--color-accent-2:oklch(0.735 0.105 160);--color-divider:color-mix(in srgb, var(--color-text) 18%, transparent);--color-neutral-100:oklch(0.290 0.008 335);--color-neutral-200:oklch(0.340 0.010 335);--color-neutral-300:oklch(0.400 0.015 335);--color-neutral-400:oklch(0.480 0.018 335);--color-neutral-500:oklch(0.570 0.023 335);--color-neutral-600:oklch(0.660 0.025 335);--color-neutral-700:oklch(0.760 0.023 335);--color-neutral-800:oklch(0.850 0.021 335);--color-neutral-900:oklch(0.930 0.016 335);--color-accent-100:oklch(0.290 0.041 348);--color-accent-200:oklch(0.340 0.058 348);--color-accent-300:oklch(0.400 0.085 348);--color-accent-400:oklch(0.480 0.119 348);--color-accent-500:oklch(0.570 0.150 348);--color-accent-600:oklch(0.660 0.162 348);--color-accent-700:oklch(0.760 0.137 348);--color-accent-800:oklch(0.850 0.098 348);--color-accent-900:oklch(0.930 0.042 348);--color-accent-2-100:oklch(0.290 0.034 160);--color-accent-2-200:oklch(0.340 0.043 160);--color-accent-2-300:oklch(0.400 0.055 160);--color-accent-2-400:oklch(0.480 0.070 160);--color-accent-2-500:oklch(0.570 0.084 160);--color-accent-2-600:oklch(0.660 0.087 160);--color-accent-2-700:oklch(0.760 0.084 160);--color-accent-2-800:oklch(0.850 0.066 160);--color-accent-2-900:oklch(0.930 0.038 160);--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);--theme-color:#231a22;color-scheme:dark;',
            'sw' => ['bg' => '#231a22', 'surface' => '#2f232e', 'accent' => '#e87db3', 'accent2' => '#69be92'],
        ],
    ],
];

// Selector list for one palette. Organic doubles as the default: a page that carries no
// data-palette at all already renders it, because the stylesheet's :root IS organic light.
function paletteSel(string $key, string $suffix): string {
    $bases = $key === 'organic'
        ? [':root:not([data-palette])', ':root[data-palette="organic"]']
        : [':root[data-palette="' . $key . '"]'];
    return implode(',', array_map(fn(string $b): string => $b . $suffix, $bases));
}

// Every palette, both modes, as one stylesheet. Shipping all six up front is what makes the
// picker instant: switching is two attributes on <html>, with nothing left to fetch or reload.
// $followOs adds the prefers-color-scheme fallback the signed-out pages need — inside the app
// the server always knows the mode and writes data-theme, so those blocks would be dead weight.
function themeCss(bool $followOs = true): string {
    $out = '';
    foreach (THEMES as $key => $t) {
        $out .= paletteSel($key, '') . '{' . $t['light']['vars'] . '}';
        // :not([data-theme]) is what lets an explicit light choice win on a dark OS — without
        // it the media query would outrank the stored preference.
        if ($followOs) {
            $out .= '@media (prefers-color-scheme: dark){'
                  . paletteSel($key, ':not([data-theme])') . '{' . $t['dark']['vars'] . '}}';
        }
        $out .= paletteSel($key, '[data-theme="dark"]') . '{' . $t['dark']['vars'] . '}';
    }
    return $out;
}

// Which palette this user is on. An unknown or missing value is the shipped one, so a row
// written by a newer release (or none at all) still renders something.
function themeKey(array $user): string {
    $k = (string)($user['theme'] ?? '');
    return isset(THEMES[$k]) ? $k : 'organic';
}

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
// Bar click → smooth-scroll to the group header, and flash it only once the scroll
// has settled (no scroll events for 120ms), so the feedback isn't over before arrival.
// Shared by every screen with a .day-strip chart over a grouped list.
function stripNavScript(): string {
    return <<<'JS'
    <script>
    document.querySelectorAll('.day-strip a').forEach(a => a.addEventListener('click', e => {
      const el = document.getElementById(a.getAttribute('href').slice(1));
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      let t;
      const settle = () => { clearTimeout(t); t = setTimeout(done, 120); };
      const done = () => {
        removeEventListener('scroll', settle);
        el.classList.remove('flash');
        void el.offsetWidth;            // restart the animation on a repeat click
        el.classList.add('flash');
      };
      addEventListener('scroll', settle, { passive: true });
      settle();                          // already in view → no scroll events → flash anyway
    }));
    </script>
    JS;
}

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
        // A drag that starts inside a horizontally-scrollable row (category filter pills)
        // is that row's own scroll, not a month swipe.
        if (e.target.closest && e.target.closest('.pill-row.scroll')) { t0 = 0; return; }
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

        // Same screen, one month over — replace, exactly as the arrows above it do.
        if (dx > 0) { if (OLDER) location.replace(OLDER); }
        else if (NEWER) location.replace(NEWER);
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
// Signed-out visitors have no users row to read, so the choice lives in
// localStorage. Absent means "follow the OS" — that path needs no JS at all,
// the prefers-color-scheme block handles it. paintStatusBar() exists because
// the meta is a fixed attribute: CSS can't reach it, so the mobile status bar
// stays on the old colour unless JS rewrites it.
//
// Must be emitted AFTER themeCss(), which is where --theme-color comes from:
// getComputedStyle can only see stylesheets the parser has already reached.
function themeBootScript(): string {
    // Every palette declares its own --theme-color, so this stays one line whatever
    // the user picked — including the OS-driven case, where no attribute is set at all.
    return <<<'JS'
<script>
  try { var r = document.documentElement,
            t = localStorage.getItem('ol-theme'), p = localStorage.getItem('ol-palette');
        // Only fills gaps: inside the app the server has already written both attributes from
        // the user's row, and that row outranks whatever this browser last stored.
        if (!r.dataset.theme && (t === 'dark' || t === 'light')) r.dataset.theme = t;
        if (!r.dataset.palette && p && /^[a-z]+$/.test(p)) r.dataset.palette = p; } catch (e) {}
  function paintStatusBar() {
    var c = getComputedStyle(document.documentElement).getPropertyValue('--theme-color').trim();
    if (!c) return;
    var m = document.querySelector('meta[name="theme-color"]');
    if (m) m.setAttribute('content', c);
    // A WebView ignores that meta, so the Android shell is told the same colour and paints
    // the bars itself. Absent everywhere else, which is why this is the last thing here.
    if (window.HLTheme) { try { HLTheme.paint(c); } catch (e) {} }
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
    // The theme is two attributes on <html>, both server-rendered: no flash, and switching
    // later is the same two attributes rewritten in place.
    $palette   = themeKey($user);
    $mode      = $user['is_dark'] ? 'dark' : 'light';
    $themeVars = themeCss(false);
    $boot      = themeBootScript();
    $moonBtn   = icon('moon', 18);
    $sunBtn    = icon('sun', 18);
    $initial   = h(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)));
    $sprite    = SVG_SPRITE;
    $backUri   = h($requestUri);
    $csrf      = csrfInput();
    $csrfTok   = csrfJs();

    $origin = originUrl();
    $meta = metaHead($origin, THEMES[$palette][$mode]['sw']['bg']);
    // Which card reads as chosen is decided by the attributes on <html>, not by anything
    // rendered here — that is what lets a pick light up without a reload.
    $pickCss = '';
    foreach (THEMES as $key => $t) {
        $pickCss .= paletteSel($key, ' .th-card[data-pick="' . $key . '"]')
                  . '{border-color:var(--color-accent);background:var(--color-accent-100);}';
    }
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
<html lang="en" data-palette="$palette" data-theme="$mode">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Open Ledger</title>
$meta
<link rel="stylesheet" href="/design-tokens/styles.css?v={$cssV}">
<style>$themeVars</style>
$boot
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
  /* Sticky on every tab. The ledger switcher, the light/dark toggle and the way into the
     drawer all live up here, and on a year of History they were a long scroll away.
     Opaque background because the page passes underneath it. z-index deliberately far below
     the drawer (200) and the toast (250): those have to cover the header, never the reverse.
     The env() adds nothing on Android — the native shell already insets the WebView — and is
     there for iOS standalone, where viewport-fit=cover puts the status bar over the page and
     a header that is always on screen would otherwise always be under it. */
  .hdr { display:flex; align-items:center; justify-content:space-between;
         padding: calc(var(--space-4) + env(safe-area-inset-top, 0px)) var(--space-4) var(--space-2);
         position:sticky; top:0; z-index:40; background:var(--color-bg); }
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
  /* Same reason as .pill-btn below: the grid mixes <button> chips with an <a> chip. */
  .cat-chip { border:1.5px solid var(--color-divider); background:var(--color-surface); border-radius:var(--radius-md); padding:12px 4px; display:flex; flex-direction:column; align-items:center; gap:6px; cursor:pointer; font-family:var(--font-body); font-size:11.5px; line-height:1.35; color:var(--color-text); text-decoration:none; }
  .cat-chip.on { border-color:var(--color-accent); background:var(--color-accent-100); color:var(--color-accent-700); }
  .cat-chip.new { border-style:dashed; color:var(--color-neutral-800); }
  .pill-row { display:flex; gap:6px; flex-wrap:wrap; }
  /* One line that scrolls, for a row long enough that wrapping it would cost the card a
     second row of height on every screen. flex:none because the default would shrink each
     pill to fit instead of overflowing, which is the whole point. */
  .pill-row.scroll { flex-wrap:nowrap; overflow-x:auto; scrollbar-width:none; }
  .pill-row.scroll::-webkit-scrollbar { display:none; }
  .pill-row.scroll > * { flex:none; }
  /* The rule above sets display:flex, which outranks the browser's own [hidden]{display:none}
     — a class beats an attribute selector. Without this every parent's sub-category row shows
     at once, and you can light a pill under a parent that isn't even selected. */
  .pill-row.sub-row[hidden] { display:none; }
  /* font-family and line-height are spelled out because a <button> inherits neither: without
     them an <a class="pill-btn"> and a <button class="pill-btn"> sitting in the same row came
     out in different typefaces at different heights (Figtree/33px against Arial/28px). */
  .pill-btn { padding:6px 14px; border-radius:999px; border:1.5px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-family:var(--font-body); font-size:12px; line-height:1.35; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
  .pill-btn.on { background:var(--color-accent); color:var(--color-bg); border-color:transparent; }
  /* The add action shares the filter row. Outlined rather than filled, so it doesn't read as
     a selected filter sitting next to the real ones. */
  .pill-btn.act { color:var(--color-accent-700); border-color:var(--color-accent-400); font-weight:600; gap:4px; }
  .note-row { display:flex; gap:8px; }
  .note-row .input { flex:1; }

  .month-switch { display:flex; align-items:center; justify-content:space-between; }
  .month-switch .label { font-family:var(--font-heading); font-size:18px; }
  /* Back is the only control on a sub-page header and it sits straight on the page ground,
     where a bare chevron reads as decoration rather than as something to press. A soft disc
     of the ink colour gives it an edge in every palette without painting a solid button. */
  .btn-back { background: color-mix(in srgb, var(--color-text) 8%, transparent); }
  .btn-back:hover { background: color-mix(in srgb, var(--color-text) 14%, transparent); }
  .btn-back:active { background: color-mix(in srgb, var(--color-text) 18%, transparent); }
  .total-card { padding: var(--space-4); text-align:center; }
  .total-card.accent { background:var(--color-accent-700); color:var(--color-bg); }
  .total-card.sage { background:var(--color-accent-2-700); color:var(--color-bg); }
  /* Earnings own the third series. The two accents are already spoken for (terracotta =
     spending, sage = investing), so income takes the neutral ramp — still a token, and the
     only ramp left that stays legible against both themes' backgrounds. */
  .total-card.ink { background:var(--color-neutral-700); color:var(--color-bg); }
  .total-card .big { font-family:var(--font-heading); font-size:32px; }
  .total-card .sub { font-size:13px; opacity:.85; }
  /* Day-wise spend strip. Bars are bg-on-accent, scaled to the month's peak day;
     zero days keep a 2px stub so the strip always reads as the whole month.
     Spend days are anchors that jump to that date's section in the list below. */
  .total-card .day-strip { display:flex; align-items:flex-end; gap:2px; height:44px; margin-top:10px; }
  .total-card .day-strip i, .total-card .day-strip a { flex:1; min-height:2px; border-radius:2px 2px 0 0; background:var(--color-bg); opacity:.5; }
  .total-card .day-strip a:hover { opacity:.8; }
  .total-card .day-strip .peak { opacity:1; }
  .total-card .day-strip .z { opacity:.18; }
  /* Today pulses, so "where am I in the month" needs no counting along the axis. Nothing is
     drawn around it: height on this strip means money, and any added mark reads as one. */
  .total-card .day-strip .today { animation: day-now 1.6s ease-in-out infinite; }
  @keyframes day-now { 0%, 100% { opacity:1; } 50% { opacity:.25; } }
  @media (prefers-reduced-motion: reduce) {
    .total-card .day-strip .today { animation:none; opacity:1; }
  }
  .total-card .day-axis { display:flex; gap:2px; margin-top:3px; font-size:8px; line-height:1; opacity:.7; }
  .total-card .day-axis span { flex:1; text-align:center; }
  /* 31 two-digit labels don't fit a phone: show every other one there.
     Scoped to .dense so the 12-label month axis keeps all of its labels. */
  @media (max-width: 480px) { .total-card .day-axis.dense span:nth-child(even) { visibility:hidden; } }
  html { scroll-behavior:smooth; }
  /* Landing feedback: the jumped-to day (header + its rows) fades in, holds, fades out
     once the scroll has settled. A class (not :target) so the script can start it on
     arrival and restart on re-click. */
  .day-group { scroll-margin-top: 8px; }
  /* 2.5s total: fade in 500ms (20%), hold 1s (20→60%), fade out 1s (60→100%). */
  .day-group.flash { animation: hl-flash 2.5s linear; border-radius: var(--radius-md); }
  @keyframes hl-flash {
    0%, 100% { background: transparent; }
    20%, 60% { background: var(--color-accent-300); }
  }
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
  .sw { width:9px; height:9px; border-radius:3px; display:inline-block; flex:none; }
  .sw.exp { background:var(--color-accent); }
  .sw.inv { background:var(--color-accent-2); }
  .sw.ern { background:var(--color-neutral-700); }
  /* Share-of-total pie under the 12-month bars. Slices are one conic-gradient — no SVG, no library. */
  .pieblock { display:flex; align-items:center; gap:14px; margin-top:10px; padding-top:12px;
              border-top:1px solid var(--color-neutral-300); }
  .pie { width:92px; height:92px; border-radius:50%; flex:none; }
  .pielist { flex:1; display:flex; flex-direction:column; gap:7px; font-size:12px; }
  .pielist .r { display:flex; align-items:center; gap:6px; }
  .pielist .nm { color:var(--color-neutral-800); }
  .pielist .amt { margin-left:auto; font-weight:600; font-variant-numeric:tabular-nums; }
  .pielist .pct { width:46px; text-align:right; color:var(--color-neutral-800); font-variant-numeric:tabular-nums; }
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

  /* In-app update, Android only — drawn here rather than by Play so an update offer looks
     like the rest of the app instead of a blue system sheet. Sits above the tab bar, below
     the drawer (200) and the toasts (250), because neither should ever end up behind it. */
  .upd { position:fixed; left:50%; transform:translateX(-50%); width:calc(100% - 32px); max-width:448px;
         z-index:150; background:var(--color-surface); border-radius:var(--radius-md);
         box-shadow:var(--shadow-lg); padding:12px 14px; display:flex; flex-direction:column; gap:10px;
         animation: upd-in .22s ease both; }
  @keyframes upd-in { from { opacity:0; transform:translate(-50%, 12px); } to { opacity:1; transform:translate(-50%, 0); } }
  .upd-acts { display:flex; justify-content:flex-end; gap:8px; }
  .upd-ttl { font-family:var(--font-heading); font-size:14px; }
  .upd-sub { color:var(--color-neutral-800); font-size:12px; margin-top:1px; }
  .upd .btn { padding-block:6px; font-size:13px; white-space:nowrap; }
  .upd-track { height:5px; border-radius:999px; background:var(--color-neutral-300); overflow:hidden; }
  .upd-fill { height:100%; width:0; border-radius:999px; background:var(--color-accent); transition:width .3s ease; }
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
  /* Themed selects: hide the OS chrome, draw the chevron with gradients so it follows
     --color-text across every palette/mode. The OPEN list is still native (color-scheme). */
  .select, select.input { appearance:none; -webkit-appearance:none; padding:10px 34px 10px 14px; border-radius:999px; border:1px solid var(--color-divider); background:var(--color-surface); color:var(--color-text); font-family:var(--font-body); font-size:14px;
    background-image:linear-gradient(45deg, transparent 50%, color-mix(in srgb, var(--color-text) 70%, transparent) 50%), linear-gradient(135deg, color-mix(in srgb, var(--color-text) 70%, transparent) 50%, transparent 50%);
    background-repeat:no-repeat; background-position:calc(100% - 19px) calc(50% + 1px), calc(100% - 14px) calc(50% + 1px); background-size:5px 5px; }
  .select:hover, select.input:hover { border-color: color-mix(in srgb, var(--color-text) 45%, transparent); }
  .select:focus-visible, select.input:focus-visible { border-color: var(--color-accent); outline-offset:0; }
  /* …and the list it opens. The native popup is an OS widget — a Material sheet in the Android
     WebView, a platform listbox on desktop — so no amount of styling on the closed control
     made the open one match. The <select> stays as the trigger and the value; the wrapper
     takes the tap and openSelect() draws the list out of the same tokens as everything else. */
  .sel-wrap { position:relative; display:block; }
  .sel-wrap > select { width:100%; pointer-events:none; }
  .sel-pop { position:fixed; z-index:300; display:flex; flex-direction:column; gap:2px; padding:6px;
    max-height:min(52vh, 320px); max-width:calc(100vw - 16px); overflow-y:auto; overscroll-behavior:contain;
    background:var(--color-surface); border:1px solid var(--color-divider);
    border-radius:var(--radius-md); box-shadow:var(--shadow-lg); }
  .sel-opt { flex:none; text-align:left; padding:9px 13px; border:none; border-radius:999px;
    background:transparent; color:var(--color-text); font-family:var(--font-body); font-size:14px;
    line-height:1.3; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .sel-opt:hover { background: color-mix(in srgb, var(--color-text) 8%, transparent); }
  .sel-opt.on { background:var(--color-accent); color:var(--color-bg); }
  .sel-opt:disabled { opacity:.45; cursor:not-allowed; background:transparent; }
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

  /* Backup panel. The account row answers "where is it going and did it work" in one glance,
     which is the whole reason anyone opens this section. The icon is aligned to the first
     line rather than centred, so a two-line failure message does not push it off-centre. */
  .bk-acct { display:flex; gap:10px; align-items:flex-start; padding:10px 12px;
             background:var(--color-neutral-100); border-radius:var(--radius-md); }
  .bk-acct > svg { color:var(--color-accent-700); flex:none; margin-top:1px; }
  .bk-row { padding-top:var(--space-1); }

  /* Theme picker — three palettes over a light/dark pair. Both previews are rendered and
     CSS shows the one matching the mode you are in, so the whole panel restyles itself from
     the two attributes on <html> and nothing here needs re-rendering to stay truthful. */
  .th-mode { display:flex; gap:6px; }
  .th-mode .pill-btn { flex:1; justify-content:center; gap:5px; }
  :root[data-theme="dark"] .th-mode .dk,
  :root:not([data-theme="dark"]) .th-mode .lt { background:var(--color-accent); color:var(--color-bg); border-color:transparent; }
  .th-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
  .th-card { gap:7px; padding:8px 5px; }
  .th-prev { width:100%; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:5px; border:1px solid color-mix(in srgb, var(--color-text) 14%, transparent); }
  .th-prev i { width:9px; height:9px; border-radius:999px; display:block; }
  :root[data-theme="dark"] .th-prev.l, :root:not([data-theme="dark"]) .th-prev.d { display:none; }
  $pickCss
  /* The header button shows the theme you'd switch *to*: moon while light, sun while dark. */
  .tt .sun { display:none; }
  :root[data-theme="dark"] .tt .sun { display:block; }
  :root[data-theme="dark"] .tt .moon { display:none; }
</style>
</head>
<body>
$sprite
<div class="col">
  <div class="hdr">
    <div class="brand">Open Ledger</div>
    <div class="hdr-actions">
      $ledgerTag
      <button class="btn btn-icon tt" type="button" onclick="toggleTheme()" aria-label="Switch between light and dark" title="Switch theme" style="color:var(--color-text);"><span class="moon">$moonBtn</span><span class="sun">$sunBtn</span></button>
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
        ['history',   '/history',   'list',         'Expense'],
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
var CSRF = "$csrfTok";
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
// The system Back button should leave the screen, not undo the last save. A form posted the
// ordinary way is a navigation, so every save, edit and delete left another copy of the same
// page behind it in history: four entries deep on one tab, four taps to get off that tab.
// Posting through fetch() and replacing the entry we are standing on leaves none behind.
// redirect() answers a POST carrying X-PRG with 204 + X-Location instead of a 302, so the page
// underneath is still rendered exactly once — no worse than before — and the fragment survives.
function toast(msg, kind) {
  var t = document.createElement('div');
  t.className = 'toast ' + (kind || 'success');
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(function () { t.remove(); }, 4000);
}
function goReplace(to) {
  var u = new URL(to, location.href);
  if (u.pathname + u.search === location.pathname + location.search) {
    // Same document: location.replace() would move the fragment and never reload, leaving the
    // row that was just saved off the screen. replaceState + reload does both, still in place.
    if (u.hash !== location.hash) history.replaceState(null, '', u.href);
    location.reload();
  } else {
    location.replace(u.href);
  }
}
document.addEventListener('submit', function (e) {
  var f = e.target;
  // method="dialog" closes a dialog and posts nothing; a handler that already called
  // preventDefault() is holding the form back for a confirmation. Without fetch there is no
  // way to post without navigating, and a screen that saves beats a tidy history.
  if (f.method !== 'post' || e.defaultPrevented || !window.fetch) return;
  e.preventDefault();
  // Navigating away used to end the page's own ability to submit again. It no longer does, so
  // a double tap — or Enter twice, which names no button to disable — is now a second expense.
  if (f.dataset.busy) return;
  f.dataset.busy = '1';
  var btn = e.submitter, body;
  try { body = new FormData(f, btn); } catch (err) { body = new FormData(f); }
  if (btn) btn.disabled = true;                       // no second save while this one is away
  var free = function () { delete f.dataset.busy; if (btn) btn.disabled = false; };
  fetch(f.action, { method: 'POST', body: body, headers: { 'X-PRG': '1' } })
    .then(function (r) {
      var to = r.headers.get('X-Location');
      if (to) { goReplace(to); return; }
      // Anything that is not a redirect is the server refusing — a rate limit, a dead route.
      // Say so where the user is, instead of replacing the screen with a bare error page.
      return r.text().then(function (t) {
        toast(t.slice(0, 140) || ('Could not save (' + r.status + ')'), 'error');
        free();
      });
    })
    .catch(function () {
      toast('Could not save. Check your connection.', 'error');
      free();
    });
});
// The same reasoning, for the controls that are links rather than script: a month arrow, a
// filter pill, a view toggle. Same path, different query string means a control on the screen
// you are already on, so replace the entry rather than stack another copy of that screen. A
// link to a different path is a real move and still stacks — Back walks back tab by tab.
document.addEventListener('click', function (e) {
  var a = e.target.closest && e.target.closest('.month-switch a, .pill-row a, .seg a');
  if (!a || a.target || e.metaKey || e.ctrlKey || e.shiftKey) return;
  var u = new URL(a.href, location.href);
  if (u.origin !== location.origin || u.pathname !== location.pathname || u.hash) return;
  e.preventDefault();
  location.replace(u.href);
});
// Keeps every other query string the page is carrying — month, invest filter, year, mode —
// and drops only the row offset, because page 3 of "everyone" is not page 3 of one person.
// replace(), not href: picking a filter narrows the screen you are on, so Back should leave
// the tab rather than walk back out through every filter you tried.
function setWho(v) {
  var u = new URL(location.href);
  if (v && v !== '0') u.searchParams.set('who', v); else u.searchParams.delete('who');
  u.searchParams.delete('o');
  location.replace(u.toString());
}
// Theme changes are applied by rewriting the two attributes on <html>: every palette in both
// modes is already in the page's CSS, so the repaint is immediate and nothing reloads. The
// POST only records the choice for the next page — its 204 is never read, and a failed one
// costs the user a preference, not the page they were on.
function setTheme(p, m) {
  var r = document.documentElement;
  if (p) r.dataset.palette = p;
  if (m) r.dataset.theme = m;
  try {
    localStorage.setItem('ol-palette', r.dataset.palette);
    localStorage.setItem('ol-theme', r.dataset.theme);
  } catch (e) {}
  paintStatusBar();
  fetch('/theme', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(CSRF)
        + '&palette=' + encodeURIComponent(r.dataset.palette)
        + '&mode=' + encodeURIComponent(r.dataset.theme),
    keepalive: true
  });
}
function toggleTheme() {
  setTheme(null, document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
}
function openProfile() {
  document.getElementById('drawer-backdrop').classList.add('open');
  document.getElementById('drawer-panel').classList.add('open');
  document.body.style.overflow = 'hidden';
  // The backup panel paints itself once, when the page loads. The drawer is only shown and
  // hidden with CSS after that, so a backup finishing later — including every scheduled one,
  // which by definition runs with nobody watching — left a stale "last backed up" time
  // sitting there until the next full page load. Defined only in the Android build.
  if (window.bkRender) window.bkRender();
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

// ── Themed dropdowns ────────────────────────────────────────────────
// Every <select> keeps doing its job — it holds the value, it submits, it is what page
// scripts read and write, and it still paints the closed control. Only the list it opens is
// ours: the wrapper swallows the tap that would have summoned the OS popup, and openSelect()
// draws the options in the app's own tokens instead. window.hlSelect enhances a <select>
// built after load (the Android backup panel makes one).
var selPop = null, selFor = null;
function closeSelect() { if (selPop) { selPop.remove(); selPop = null; selFor = null; } }
function openSelect(sel) {
  closeSelect();
  selPop = document.createElement('div');
  selPop.className = 'sel-pop';
  selPop.setAttribute('role', 'listbox');
  Array.prototype.forEach.call(sel.options, function (o, i) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'sel-opt' + (i === sel.selectedIndex ? ' on' : '');
    b.setAttribute('role', 'option');
    b.textContent = o.text;
    b.disabled = o.disabled;
    b.onclick = function () {
      // By index, not value: plenty of these options carry no value attribute at all.
      sel.selectedIndex = i;
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      closeSelect();
    };
    selPop.appendChild(b);
  });
  // Into the dialog when there is one. A modal puts the rest of the document behind its
  // backdrop and out of reach, so a list appended to <body> would be dimmed and inert.
  (sel.closest('dialog') || document.body).appendChild(selPop);
  selFor = sel;
  var r = sel.getBoundingClientRect();
  selPop.style.minWidth = r.width + 'px';
  selPop.style.left = Math.max(8, Math.min(r.left, innerWidth - selPop.offsetWidth - 8)) + 'px';
  // Below the control, unless that runs off the bottom and there is room above.
  var below = r.bottom + 4, above = r.top - selPop.offsetHeight - 4;
  selPop.style.top = (below + selPop.offsetHeight > innerHeight && above > 4 ? above : below) + 'px';
  var on = selPop.querySelector('.sel-opt.on');
  if (on) on.scrollIntoView({ block: 'nearest' });
}
function hlSelect(sel) {
  if (sel.parentNode && sel.parentNode.classList.contains('sel-wrap')) return;
  var w = document.createElement('span');
  w.className = 'sel-wrap';
  sel.parentNode.insertBefore(w, sel);
  w.appendChild(sel);
  var mirror = function () { w.style.display = sel.style.display === 'none' ? 'none' : ''; };
  mirror();
  // Page scripts show and hide these by writing style.display straight onto the select — the
  // recurring dialog swaps three of them by kind. The wrapper is the box in the layout now,
  // so it has to follow, and mirroring here beats editing every call site.
  new MutationObserver(mirror).observe(sel, { attributes: true, attributeFilter: ['style'] });
  w.addEventListener('click', function () {
    if (sel.disabled) return;
    if (selFor === sel) closeSelect(); else openSelect(sel);
  });
  sel.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') { e.preventDefault(); openSelect(sel); }
  });
}
window.hlSelect = hlSelect;
document.querySelectorAll('select').forEach(hlSelect);
document.addEventListener('pointerdown', function (e) {
  if (!selPop || selPop.contains(e.target)) return;
  // A tap on the open control's own wrapper is a toggle, handled by its click listener.
  if (selFor && selFor.parentNode.contains(e.target)) return;
  closeSelect();
});
// Capture, so a dropdown open inside a dialog closes itself instead of closing the dialog.
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape' && selPop) { e.preventDefault(); e.stopPropagation(); closeSelect(); }
}, true);
addEventListener('scroll', function (e) { if (selPop && !selPop.contains(e.target)) closeSelect(); }, true);
addEventListener('resize', closeSelect);

/*
 * The in-app update bar. Defined only where the bridge is — the web build has no HLUpdate and
 * this whole block returns on its first line.
 *
 * Play's flexible flow hands us the download and asks us to draw everything around it, which is
 * the point: the alternative is its own full-screen sheet, in Google's colours, over a ledger
 * the user was reading. State comes from one poll rather than a callback, because the bridge
 * cannot call into JavaScript and the numbers only move while someone is looking anyway.
 */
(function () {
  if (!window.HLUpdate) return;
  var el = null, painted = null;

  // Play's failure text is the only string here that did not come from this file.
  function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
  function mb(n) { return (n / 1048576).toFixed(1) + ' MB'; }
  function pct(s) { return s.total > 0 ? Math.min(100, Math.round(s.bytes * 100 / s.total)) : 0; }

  function bar() {
    if (el) return el;
    el = document.createElement('div');
    el.className = 'upd';
    el.addEventListener('click', function (e) {
      var b = e.target.closest('[data-act]');
      if (!b) return;
      if (b.dataset.act === 'later')   { HLUpdate.dismiss(); }
      if (b.dataset.act === 'begin')   { HLUpdate.begin(); }
      if (b.dataset.act === 'install') { HLUpdate.install(); }
      painted = null;
      render();
    });
    document.body.appendChild(el);
    // Above the tab bar, measured rather than guessed: the pill is a different height on the
    // screens that have no tab bar at all, where this falls back to sitting on the margin.
    var nav = document.querySelector('.tabnav');
    el.style.bottom = (nav ? nav.offsetHeight + 32 : 16) + 'px';
    return el;
  }

  function render() {
    var s;
    try { s = JSON.parse(HLUpdate.status()); } catch (e) { return; }
    var key = s.state + ':' + pct(s) + ':' + s.error;
    if (key === painted) return;
    painted = key;
    if (!s.state) { if (el) { el.remove(); el = null; } return; }

    var size = s.total > 0 ? mb(s.total) : '';
    // One shape for every state: a heading, a line under it, then the two optional pieces.
    // Buttons get a row of their own — sharing one with the text squeezed both at 320px.
    var card = function (title, sub, track, acts) {
      return '<div><div class="upd-ttl">' + title + '</div>'
           + '<div class="upd-sub">' + sub + '</div></div>'
           + (track ? '<div class="upd-track" role="progressbar" aria-valuemin="0" aria-valuemax="100"'
                    + ' aria-valuenow="' + pct(s) + '"><div class="upd-fill" style="width:'
                    + pct(s) + '%"></div></div>' : '')
           + (acts ? '<div class="upd-acts">' + acts + '</div>' : '');
    };
    var later   = '<button class="btn btn-ghost" data-act="later">Later</button>';
    var html;
    if (s.state === 'available') {
      html = card('Update available', size || 'A newer version is ready to install.', false,
                  later + '<button class="btn btn-primary" data-act="begin">Update</button>');
    } else if (s.state === 'downloading') {
      html = card('Downloading update',
                  pct(s) + '%' + (size ? ' of ' + size : '') + ' — carry on, this runs in the background.',
                  true, '');
    } else if (s.state === 'downloaded') {
      html = card('Update ready', 'Restart to finish installing it.', false,
                  later + '<button class="btn btn-primary" data-act="install">Restart</button>');
    } else {
      html = card('Update failed', esc(s.error || 'Play could not download it.'), false,
                  '<button class="btn btn-ghost" data-act="later">Dismiss</button>'
                  + '<button class="btn btn-primary" data-act="begin">Try again</button>');
    }
    bar().innerHTML = html;
  }

  render();
  setInterval(render, 900);
})();
</script>
</body></html>
DLG;
}

// Right-side drawer — replaces the old /manage page. All account/household controls live here.
function renderProfileDrawer(PDO $db, array $user, string $requestUri): void {
    $hid = (int)$user['household_id'];
    $eCats = $db->prepare("SELECT * FROM earning_categories WHERE household_id = ? ORDER BY id");
    $eCats->execute([$hid]); $eCats = $eCats->fetchAll();
    $canDeleteECat = count($eCats) > 1;
    // How many earnings each category would orphan — shown in the delete confirmation.
    $s = $db->prepare("SELECT category_id, COUNT(*) n FROM earnings WHERE household_id = ? GROUP BY category_id");
    $s->execute([$hid]); $earnPerCat = array_column($s->fetchAll(), 'n', 'category_id');

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
          <?php /* Omitted rather than blank: on the phone the email is optional, and an empty
                   line under the name reads as something that failed to load. */ ?>
          <?php if (trim((string)($user['email'] ?? '')) !== ''): ?>
          <div class="e"><?= h($user['email']) ?></div>
          <?php endif; ?>
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
          <!-- Categories and types are managed entirely on their own pages — rename, budget
               or target, nest, delete, and moving entries between them. Keeping a second copy
               of those controls here would mean two places to fix every rule change. -->
          <a class="drawer-nav" href="/organise">
            <span class="ico"><?= icon('tag', 18) ?></span>
            <span>Organise expense categories</span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
          <?php /* Same story for investment types, and the same page shape — so the two
                   sit together rather than one here and one buried further down. */ ?>
          <a class="drawer-nav" href="/organise-invest">
            <span class="ico"><?= icon('trending-up', 18) ?></span>
            <span>Organise investment types</span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
          <?php /* Sharing lives on its own page rather than in here. This drawer renders on
                   every request whether or not anyone opens it, and the invite link, the people
                   list and the ledger switcher would have cost three more queries per page for
                   a panel that starts closed. */ ?>
          <?php /* The page still earns its place with sharing off — renaming the ledger and
                   seeing its currency live there — but promising "sharing" to a build that
                   has none is a link that lies about where it goes. */ ?>
          <a class="drawer-nav" href="/ledgers">
            <span class="ico"><?= icon('users', 18) ?></span>
            <span><?= FEATURE_SHARING ? 'Ledgers &amp; sharing' : 'Ledger settings' ?></span>
            <span class="chev"><?= icon('chevron-right', 16) ?></span>
          </a>
        </section>

        <hr>

        <?php /* Not a <details> like the sections below it: the point of a theme picker is
                 that you see it apply, and the drawer itself repaints under your thumb the
                 moment you tap. Hiding it behind a disclosure would cost the demonstration.
                 Every card carries both previews; CSS shows whichever matches the mode. */ ?>
        <section>
          <h4>Theme</h4>
          <div class="th-mode" role="group" aria-label="Light or dark">
            <button type="button" class="pill-btn lt" onclick="setTheme(null,'light')"><?= icon('sun', 14) ?> Light</button>
            <button type="button" class="pill-btn dk" onclick="setTheme(null,'dark')"><?= icon('moon', 14) ?> Dark</button>
          </div>
          <div class="th-grid" style="margin-top:8px;">
            <?php foreach (THEMES as $key => $t): ?>
              <button type="button" class="cat-chip th-card" data-pick="<?= h($key) ?>"
                      onclick="setTheme('<?= h($key) ?>',null)" title="<?= h($t['note']) ?>">
                <?php foreach (['light' => 'l', 'dark' => 'd'] as $m => $cls): $sw = $t[$m]['sw']; ?>
                  <span class="th-prev <?= $cls ?>" style="background:<?= h($sw['bg']) ?>;">
                    <i style="background:<?= h($sw['accent']) ?>"></i>
                    <i style="background:<?= h($sw['accent2']) ?>"></i>
                    <i style="background:<?= h($sw['surface']) ?>"></i>
                  </span>
                <?php endforeach; ?>
                <?= h($t['name']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </section>

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

        <?php if (FEATURE_BACKUP): ?>
        <?php /* Android only. The buttons drive the native Drive client through the HLBackup
                 bridge — see BackupBridge.kt. Rendered here rather than as a native screen so
                 there is one set of controls in one design system. */ ?>
        <hr>
        <section>
          <h4 style="margin:0 0 8px;">Backup</h4>
          <div id="bk-panel" class="stack" style="gap:8px;">
            <p class="muted" style="margin:0;">Loading…</p>
          </div>
        </section>

        <?php /* This panel's own dialog, and the reason it exists: an Android WebView with no
                 WebChromeClient does not show window.confirm() or window.prompt() at all — it
                 returns false and null immediately. Every button behind one silently did
                 nothing, which is precisely how they were found. A native dialog would have
                 fixed that and looked like a different app; this is the same <dialog> the rest
                 of the ledger confirms with, so it works the same on the web too.

                 Separate from #confirm-dlg because that one submits a form to a URL, and every
                 action here is a JavaScript call into the bridge. */ ?>
        <dialog id="bk-dlg" class="confirm" aria-labelledby="bk-dlg-title">
          <form method="dialog">
            <div class="dlg-title" id="bk-dlg-title"></div>
            <div class="dlg-body" id="bk-dlg-body"></div>
            <label class="field" id="bk-f1-wrap" style="display:none;">
              <span id="bk-f1-label"></span>
              <input class="input" type="password" id="bk-f1" autocomplete="off" autocapitalize="none" spellcheck="false">
            </label>
            <label class="field" id="bk-f2-wrap" style="display:none;">
              <span id="bk-f2-label"></span>
              <input class="input" type="password" id="bk-f2" autocomplete="off" autocapitalize="none" spellcheck="false">
            </label>
            <div class="dlg-body" id="bk-dlg-err" style="display:none; color:#c0392b;"></div>
            <div class="dlg-actions">
              <button type="button" class="btn btn-secondary" id="bk-dlg-cancel">Cancel</button>
              <button type="button" class="btn" id="bk-dlg-ok"></button>
            </div>
          </form>
        </dialog>
        <script>
        (function () {
          var el = document.getElementById('bk-panel');
          if (!el || !window.HLBackup) { if (el) el.innerHTML = '<p class="muted">Unavailable.</p>'; return; }

          function esc(s) { return String(s).replace(/[&<>"]/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]; }); }

          /**
           * The panel's only way to say anything back.
           *
           * `.toast` was a server-rendered flash class with no JavaScript behind it, so every
           * call here — including 'Restore failed: ' + the reason — threw a ReferenceError and
           * was swallowed by the WebView. A restore that stopped for a nameable reason looked
           * exactly like a button that does nothing, which is how it was reported.
           */
          function toast(msg, kind) {
            var t = document.createElement('div');
            t.className = 'toast ' + (kind || 'success');
            t.textContent = msg;
            document.body.appendChild(t);
            // The CSS animation ends at 2.2s (3.6s for .error) but leaves the node in place.
            setTimeout(function () { t.remove(); }, 4000);
          }

          /**
           * Ask, then act. Replaces confirm()/prompt(), which a WebView drops on the floor.
           *
           * opts: {title, body, ok, danger, fields:[label,…], validate(values) -> errorOrNull}
           * done() gets the entered values, or null if the user backed out — including by
           * pressing Escape, which fires the dialog's own close event and must count as "no"
           * rather than leaving a half-finished action waiting for a callback that never comes.
           */
          function ask(opts, done) {
            var dlg = document.getElementById('bk-dlg');
            var fields = opts.fields || [], settled = false;
            document.getElementById('bk-dlg-title').textContent = opts.title;
            document.getElementById('bk-dlg-body').textContent = opts.body || '';
            var okBtn = document.getElementById('bk-dlg-ok');
            okBtn.textContent = opts.ok || 'Continue';
            okBtn.className = 'btn ' + (opts.danger ? 'btn-danger' : 'btn-primary');
            var err = document.getElementById('bk-dlg-err');
            err.style.display = 'none';

            [1, 2].forEach(function (n) {
              var wrap = document.getElementById('bk-f' + n + '-wrap');
              var input = document.getElementById('bk-f' + n);
              input.value = '';
              // '' not 'flex': the dialog's form is already a column flex container, so an
              // unstyled label blockifies and the caption sits above its input. Forcing flex
              // here laid them side by side and wrapped the longer caption onto two lines.
              wrap.style.display = fields[n - 1] ? '' : 'none';
              if (fields[n - 1]) document.getElementById('bk-f' + n + '-label').textContent = fields[n - 1];
            });

            function settle(v) {
              if (settled) return;
              settled = true;
              dlg.close();
              done(v);
            }
            okBtn.onclick = function () {
              var values = fields.map(function (_, i) { return document.getElementById('bk-f' + (i + 1)).value; });
              var problem = opts.validate ? opts.validate(values) : null;
              // Shown inside the dialog, not as a toast behind it: the mistake and the field
              // it belongs to have to be on screen together, and the dialog stays open.
              if (problem) { err.textContent = problem; err.style.display = 'block'; return; }
              settle(fields.length ? values : true);
            };
            document.getElementById('bk-dlg-cancel').onclick = function () { settle(null); };
            dlg.addEventListener('close', function once() {
              dlg.removeEventListener('close', once);
              if (!settled) { settled = true; done(null); }
            });
            dlg.showModal();
            if (fields.length) document.getElementById('bk-f1').focus();
          }

          // "today at 09:04" beats "8/18/2026 09:04 AM". The only thing anyone reads a backup
          // timestamp for is how stale it is, and a date makes you work that out yourself.
          function when(ms) {
            if (!ms) return '';
            var d = new Date(Number(ms)), now = new Date();
            var at = ' at ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            var days = Math.round(
              (new Date(now.getFullYear(), now.getMonth(), now.getDate())
               - new Date(d.getFullYear(), d.getMonth(), d.getDate())) / 86400000);
            if (days <= 0) return 'today' + at;
            if (days === 1) return 'yesterday' + at;
            if (days < 7)  return days + ' days ago';
            return 'on ' + d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
          }

          // What the panel is currently showing, and whether a backup we started is still in
          // flight. Held outside render() because render() rewrites the whole panel: keeping
          // the in-progress state on the button element itself lost it the moment anything
          // else repainted.
          var shown = null, busy = false, baseOk = 0, baseErr = '', baseNote = '', watcher = null;

          function drawerOpen() {
            var d = document.getElementById('drawer-panel');
            return !!(d && d.classList.contains('open'));
          }

          /**
           * Repaint when the answer changes underneath us.
           *
           * A backup is queued work, not a function call — it finishes whenever WorkManager
           * gets to it, which on a cold radio can be minutes. The first version of this waited
           * a fixed 45 seconds and then stopped looking, so a slower run landed after the last
           * repaint and the panel sat there showing the previous time until the drawer was
           * closed and opened again. This keeps looking for as long as it matters, and stops
           * on its own once nothing is in flight and nobody is looking.
           */
          function tick() {
            if (!busy && !drawerOpen()) { clearInterval(watcher); watcher = null; return; }
            var s2 = JSON.parse(HLBackup.status());
            if (busy) {
              var done = s2.lastOk !== baseOk;
              var noted = s2.lastNote && s2.lastNote !== baseNote;
              var failed = s2.lastError && s2.lastError !== baseErr;
              if (!done && !failed && !noted) return;
              busy = false;
              toast(done ? 'Backed up to Drive.' : noted ? s2.lastNote : 'Backup failed.',
                    done ? 'success' : 'error');
              render();
              return;
            }
            // Not our backup: a scheduled one finished, or the account changed. Same repaint.
            if (!shown || s2.lastOk !== shown.lastOk || s2.lastError !== shown.lastError
                || s2.lastNote !== shown.lastNote
                || s2.account !== shown.account) render();
          }

          function watch() { if (!watcher) watcher = setInterval(tick, 2000); }

          function render() {
            var s = JSON.parse(HLBackup.status());
            shown = s;
            if (!s.configured) {
              el.innerHTML = '<p class="muted" style="margin:0;">Drive backup is not set up in this build.</p>';
              return;
            }
            if (!s.account) {
              // Why the last attempt came back without an account. Every reason is a setting
              // in the Google console rather than anything the phone can fix, and none of them
              // is guessable from a chooser that closed and a page that reloaded — which is
              // all this said before, whichever of them it was.
              el.innerHTML = '<p class="muted" style="margin:0 0 8px;">Your ledger stays on this phone. '
                + 'Connect Google Drive to keep an encrypted-at-rest copy in your own account.</p>'
                + (s.lastError
                    ? '<p style="margin:0 0 8px;color:#c0392b;font-size:13px;">' + esc(s.lastError) + '</p>'
                    : '')
                + '<button class="btn btn-primary btn-block" id="bk-connect">Connect Google Drive</button>';
              document.getElementById('bk-connect').onclick = function () { HLBackup.connect(); };
              return;
            }
            var opts = ['off', 'daily', 'weekly'].map(function (f) {
              return '<option value="' + f + '"' + (s.frequency === f ? ' selected' : '') + '>'
                + (f === 'off' ? 'Only when I ask' : f.charAt(0).toUpperCase() + f.slice(1)) + '</option>';
            }).join('');

            // One status line that answers the only question anyone opens this panel with:
            // is my ledger actually backed up? An error outranks a stale success — a panel
            // reading "Last backup: 3 days ago" in calm grey while every run since has failed
            // is worse than no panel. #c0392b matches .btn-danger; the design system has no
            // danger ramp, and this is the one colour for "something went wrong".
            var status = s.lastNote
              // A run that declined to upload is not a failure and must not be dressed as one:
              // the empty-ledger guard fires exactly when the copy in Drive is intact and the
              // user is one tap from getting it back. Red "Backup failed" there reads as "your
              // backup is broken", which is the opposite of what happened.
              ? '<p class="muted" style="margin:0;font-size:13px;">' + esc(s.lastNote) + '</p>'
              : s.lastError
              ? '<p style="margin:0;color:#c0392b;font-size:13px;"><strong>Backup failed.</strong> '
                  + esc(s.lastError) + '</p>'
              : s.lastOk
                ? '<p class="muted" style="margin:0;">Last backed up ' + when(s.lastOk) + '</p>'
                : '<p class="muted" style="margin:0;">Not backed up yet.</p>';

            el.innerHTML =
                '<div class="bk-acct">'
              +   '<svg width="20" height="20" aria-hidden="true"><use href="#icon-archive"></use></svg>'
              +   '<div style="min-width:0;">'
              +     '<div style="font-size:14px; overflow:hidden; text-overflow:ellipsis;">' + esc(s.account) + '</div>'
              +     status
              +   '</div>'
              + '</div>'
              + '<button class="btn btn-primary btn-block" id="bk-now"' + (busy ? ' disabled' : '') + '>'
              +   (busy ? 'Backing up…' : 'Back up now') + '</button>'
              + '<label class="field"><span>Automatically</span>'
              +   '<select class="input" id="bk-freq">' + opts + '</select></label>'
              + '<div class="bk-row">'
              +   '<p class="muted" style="margin:0;">' + (s.encrypted
                    ? 'Encrypted with your passphrase — Google cannot read it.'
                    : 'Not encrypted — Google can read this backup.') + '</p>'
              +   '<button class="btn btn-secondary btn-block" id="bk-pass" style="margin-top:8px;">'
              +     (s.encrypted ? 'Change or remove passphrase' : 'Encrypt with a passphrase') + '</button>'
              + '</div>'
              // Restore and disconnect both throw something away, so they sit below the line
              // rather than in the row of everyday actions, and neither is the loudest thing
              // on the screen. "Back up now" is what this panel is for.
              + '<hr style="margin:4px 0;">'
              + '<button class="btn btn-secondary btn-block" id="bk-restore">Restore from Drive</button>'
              + '<button class="btn btn-ghost" id="bk-off" style="color:#c0392b; align-self:flex-start;">'
              +   'Disconnect this account</button>';

            // Built after load, so it misses the sweep that themes every other dropdown.
            if (window.hlSelect) window.hlSelect(document.getElementById('bk-freq'));
            document.getElementById('bk-freq').onchange = function () {
              HLBackup.setFrequency(this.value);
              toast(this.value === 'off' ? 'Automatic backup off.'
                                         : 'Backing up ' + this.options[this.selectedIndex].text.toLowerCase() + '.');
            };
            // Queue it, then let the watcher report what happened. The button's in-progress
            // state comes from `busy` via render(), so it survives any repaint in between.
            document.getElementById('bk-now').onclick = function () {
              busy = true; baseOk = s.lastOk; baseErr = s.lastError; baseNote = s.lastNote;
              HLBackup.backupNow();
              render();
              watch();
            };
            document.getElementById('bk-off').onclick = function () {
              ask({
                title: 'Disconnect this account?',
                body: 'Automatic backups stop and ' + s.account + ' is signed out. The copy '
                    + 'already in Drive is left alone — you can reconnect and restore from it.',
                ok: 'Disconnect', danger: true,
              }, function (yes) {
                if (!yes) return;
                HLBackup.disconnect();
                toast('Drive disconnected.');
                render();
              });
            };

            document.getElementById('bk-pass').onclick = function () {
              if (s.encrypted) {
                ask({
                  title: 'Stop encrypting backups?',
                  body: 'Future backups will be readable by Google. The copy already in Drive '
                      + 'stays encrypted, and without its passphrase it can never be opened again.',
                  ok: 'Stop encrypting', danger: true,
                }, function (yes) {
                  if (!yes) return;
                  HLBackup.clearPassphrase();
                  toast('Future backups will not be encrypted.');
                  render();
                });
                return;
              }
              // Said plainly and before the fact: there is no recovery path, because nobody
              // else holds the key. That is the feature, not a gap — so it is stated where the
              // decision is made, not in a help page.
              ask({
                title: 'Encrypt your backups',
                body: 'Your passphrase is never sent anywhere, so nobody — including us — can '
                    + 'recover it. Forget it and the backup in Drive is lost for good.',
                ok: 'Encrypt backups',
                fields: ['Passphrase', 'Type it again'],
                validate: function (v) {
                  if (v[0].length < 8) return 'Use at least 8 characters.';
                  if (v[0] !== v[1]) return 'Those two do not match.';
                  return null;
                },
              }, function (v) {
                if (!v) return;
                var err = HLBackup.setPassphrase(v[0]);
                if (err) { toast(err, 'error'); return; }
                toast('Backups will be encrypted from now on.');
                render();
              });
            };

            document.getElementById('bk-restore').onclick = function () {
              var btn = this;
              // Restoring overwrites every entry on this phone, so it asks first — and says
              // what it costs, not just "are you sure".
              ask({
                title: 'Restore from Drive?',
                body: 'Everything on this phone is replaced with the copy in Drive'
                    + (s.lastOk ? ', backed up ' + when(s.lastOk) : '')
                    + '. Anything added since then is lost.',
                ok: 'Replace my ledger', danger: true,
              }, function (yes) {
                if (!yes) return;
                run('');
              });

              // The bridge call blocks — it stops the interpreter, swaps the database and
              // starts it again — so the button has to be repainted before it is made, not
              // after. Hence the timeout: without it the "Restoring…" state never renders and
              // the app just appears frozen for several seconds.
              function run(passphrase) {
                btn.disabled = true; btn.textContent = 'Restoring…';
                setTimeout(function () {
                  var err = HLBackup.restore(passphrase);
                  btn.disabled = false; btn.textContent = 'Restore from Drive';
                  // The blob says whether it is encrypted, not this phone's settings —
                  // restoring onto a device that has never seen this backup is the case
                  // that matters.
                  if (err === 'PASSPHRASE_REQUIRED') {
                    ask({
                      title: 'This backup is encrypted',
                      body: 'Enter the passphrase it was sealed with.',
                      ok: 'Restore', danger: true,
                      fields: ['Passphrase'],
                      validate: function (v) { return v[0] ? null : 'Enter the passphrase.'; },
                    }, function (v) { if (v) run(v[0]); });
                    return;
                  }
                  if (err) { toast('Restore failed: ' + err, 'error'); return; }
                  location.href = '/';
                }, 50);
              }
            };
          }
          // Re-read on every drawer open, not just on page load — see openProfile(). Opening
          // also starts the watcher, so a scheduled backup finishing while the panel is on
          // screen updates it there and then.
          window.bkRender = function () { render(); watch(); };
          render();
        })();
        </script>
        <?php endif; ?>

        <div style="flex:1;"></div>

        <hr>

        <section>
          <a class="plain-link" href="/terms">Terms &amp; conditions</a>
          <div style="margin-top:8px;font-size:12px;color:var(--color-neutral-800);">
            Built by <a href="https://xpertxyz.in" style="color:var(--color-accent-700);text-decoration:none;">XpertXYZ</a>
          </div>
        </section>

        <?php /* Nothing to sign out of in the local build. There is no account, and the one
                 local user is resolved again on the very next request (index.php), so this was
                 a confirmation dialog that put you back exactly where you already were. */ ?>
        <?php if (FEATURE_SIGNIN): ?>
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
        <?php endif; ?>

        <?php /* Last line in the drawer, and only on the phone. The website is whatever is
                 deployed at the moment you load it, so a version number there names something
                 nobody can act on; an APK sits on a device for months. */ ?>
        <?php if (APP_VERSION !== ''): ?>
          <div style="margin-top:10px; font-size:11px; text-align:center; color:var(--color-neutral-800);">
            Version <?= h(APP_VERSION) ?>
          </div>
        <?php endif; ?>
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
<style><?= themeCss() ?></style>
<?= themeBootScript() ?>
<style>
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
    <span class="tag tag-neutral">3 themes, light &amp; dark</span>
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
        them. Tap a month to open it in Expense. Amounts read ₹10,00,000, the way
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
      <div class="card-title">Expenses that add up</div>
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
    $themeVars = themeCss();
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
<style>$themeVars</style>
$boot
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
  /* Signed-out visitor has no users row to read, so follow the OS — unless they picked a
     theme while signed in or used the landing page toggle, which the inline script above
     replays from localStorage. Same six palette blocks the authed layout emits. */
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

// ─── First run (local build only) ───────────────────────────────────
//
// The phone build has no Google account to take a name from, so it asks once. A full page
// rather than a dialog: nothing exists yet to put a dialog on top of, and the answers decide
// what gets created — the household row, the user row, and the member label on every entry
// they will ever add.
//
// Nothing here leaves the device and the page says so, because being asked for a name and an
// email by an app that has just promised it has no server is otherwise a fair thing to be
// suspicious of.
function renderSetup(string $error, array $old): void {
    $sprite    = SVG_SPRITE;
    $origin    = originUrl();
    $meta      = metaHead($origin);
    $themeVars = themeCss();
    $boot      = themeBootScript();
    $cssV      = cssVersion();
    $csrf      = csrfInput();
    // Re-shown on a rejected submit, so nobody retypes three fields to fix one.
    $name   = h(trim((string)($old['name']   ?? '')));
    $ledger = h(trim((string)($old['ledger'] ?? '')));
    $email  = h(trim((string)($old['email']  ?? '')));
    $errBox = $error === ''
        ? ''
        : '<div style="width:100%;padding:10px 12px;border-radius:var(--radius-md);'
          . 'background:color-mix(in srgb, #c0392b 12%, transparent);color:#c0392b;font-size:13px;">'
          . h($error) . '</div>';

    // Said here, once, at the only moment the person is deciding whether to trust this thing
    // with a year of their household's money. The terms page says the same in more words, and
    // nobody reads the terms page. Static text, so the <strong> in it is safe unescaped.
    $facts = [
        'There is <strong>no account and no server</strong>. Nothing to sign in to, nothing to be locked out of.',
        'Every entry lives in <strong>one file on this phone</strong>, in storage only this app can open.',
        '<strong>We never get a copy.</strong> Nothing for us to sell, leak, lose, or hand to anyone who asks.',
    ];
    if (FEATURE_BACKUP) {
        $facts[] = 'A backup, if you turn one on, goes to <strong>your own Google Drive</strong> — '
                 . 'and can be sealed with a passphrase only you know.';
    }
    $facts[] = 'Uninstall and it is <strong>gone</strong>. No copy survives somewhere else.';
    $factList = implode('', array_map(
        fn($t) => '<li style="display:flex;gap:8px;align-items:flex-start;">'
                . '<span style="color:var(--color-accent);flex:none;margin-top:1px;">' . icon('check', 15) . '</span>'
                . '<span>' . $t . '</span></li>',
        $facts
    ));

    echo <<<HTML
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex">
<title>Open Ledger — Set up</title>
$meta
<link rel="stylesheet" href="/design-tokens/styles.css?v={$cssV}">
<style>$themeVars</style>
$boot
<style>
  body { margin:0; background:var(--color-bg); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .field > span { display:block; font-size:12px; margin-bottom:5px; color:color-mix(in srgb, var(--color-text) 70%, transparent); }
  .btn:disabled { opacity:.45; }
</style>
</head>
<body>
$sprite
<div style="width:100%;max-width:340px;padding:var(--space-4);">
  <div class="card elev-lg" style="padding:var(--space-6);gap:var(--space-4);">
    <div style="display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center;">
      <svg viewBox="0 0 24 24" width="52" height="52" fill="none" stroke="var(--color-accent)"
           stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        <circle cx="12" cy="12" r="0.85" fill="var(--color-accent-2)" stroke="none"/>
      </svg>
      <h1 style="margin:0;font-family:var(--font-heading);font-size:24px;font-weight:normal;">Set up your ledger</h1>
      <p style="margin:0;font-family:var(--font-heading);font-size:19px;line-height:1.3;color:var(--color-accent-700);">
        You own your data.<br>Every last rupee of it.</p>
    </div>

    <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px;
               font-size:13px;line-height:1.45;color:var(--color-neutral-800);">
      $factList
    </ul>

    <hr style="width:100%;border:none;border-top:1px solid var(--color-divider);margin:0;">

    $errBox

    <form method="post" action="/setup" id="setup-form"
          style="display:flex;flex-direction:column;gap:var(--space-3);width:100%;">
      <p style="margin:0;font-size:13px;color:var(--color-neutral-800);">
        These two names are only so the app knows what to call you and your ledger.</p>
      $csrf
      <label class="field"><span>Your name</span>
        <input class="input" id="s-name" name="name" maxlength="80" autocomplete="name"
               autocapitalize="words" value="$name" required></label>
      <label class="field"><span>Ledger name</span>
        <input class="input" id="s-ledger" name="ledger" maxlength="80"
               autocapitalize="words" value="$ledger" required></label>
      <label class="field"><span>Email — optional</span>
        <input class="input" id="s-email" name="email" type="email" maxlength="190"
               autocomplete="email" autocapitalize="none" spellcheck="false" value="$email"></label>
      <button class="btn btn-primary btn-block" id="s-save" type="submit" disabled>Create my ledger</button>
    </form>
  </div>
  <div style="display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;
              margin-top:14px;font-size:12px;color:var(--color-neutral-800);">
    <a href="/terms" style="color:inherit;text-decoration:underline;text-underline-offset:2px;">Terms &amp; conditions</a>
    <span>Built by <a href="https://xpertxyz.in" style="color:var(--color-accent-700);text-decoration:none;">XpertXYZ</a></span>
  </div>
</div>
<script>
(function () {
  var n = document.getElementById('s-name'),
      l = document.getElementById('s-ledger'),
      b = document.getElementById('s-save');
  // The ledger name follows the first name until it is edited, which is the same guess
  // ledgerNameFor() makes on the server — shown here so it can be corrected rather than
  // discovered later. Once touched it is left alone, including on a rejected submit.
  var touched = l.value.trim() !== '';
  function sync() { b.disabled = !(n.value.trim() && l.value.trim()); }
  n.addEventListener('input', function () {
    if (!touched) l.value = n.value.trim().split(/\s+/)[0] || '';
    sync();
  });
  l.addEventListener('input', function () { touched = true; sync(); });
  sync();
  n.focus();
})();
</script>
</body></html>
HTML;
}

// ─── Add ────────────────────────────────────────────────────────────
function renderAdd(PDO $db, array $user): void {
    $hid = (int)$user['household_id'];
    $cats = $db->prepare("SELECT * FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $cats->execute([$hid]); $cats = $cats->fetchAll();
    $mems = membersFor($db, $hid, (int)$user['id']);

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

    // Remaining budget this month — household budget minus this month's spend.
    $budStmt = $db->prepare("SELECT COALESCE(SUM(budget), 0) FROM categories WHERE household_id = ? AND parent_id IS NULL");
    $budStmt->execute([$hid]);
    $budgetTotal = (float)$budStmt->fetchColumn();
    $budgetLeft  = 0.0;
    if ($budgetTotal > 0) {
        $spentStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE household_id = ? AND `date` >= ? AND `date` < ?");
        $spentStmt->execute([$hid, date('Y-m-01'), (new DateTimeImmutable('first day of next month'))->format('Y-m-d')]);
        $budgetLeft = $budgetTotal - (float)$spentStmt->fetchColumn();
    }

    ob_start();
    ?>
    <form method="post" action="/expenses" class="stack">
      <?= csrfInput() ?>
      <div class="card elev-sm amount-card">
        <div class="amount-q" style="display:flex; justify-content:space-between; align-items:baseline; gap:8px;">
          <span>How much did you spend?</span>
          <?php if ($budgetTotal > 0): ?>
            <span style="font-size:12px; font-weight:400; color:var(--color-neutral-800); white-space:nowrap;">
              <?php if ($budgetLeft >= 0): ?>
                <?= h(fmt($budgetLeft)) ?> left
              <?php else: ?>
                <strong style="color:#c0392b;"><?= h(fmt(-$budgetLeft)) ?> over</strong>
              <?php endif; ?>
            </span>
          <?php endif; ?>
        </div>
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
              <?= h($m['label']) ?>
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

    // Category filter for the transaction list. The pill rows sit at the top of the list, so
    // only the list (and its pagination) narrows — the month summary above stays whole-month.
    // Validated against this household's categories the same way `who` is; a parent id also
    // matches its sub-categories, a sub-category id matches only itself.
    // (This list also feeds the edit-expense modal below.)
    $catList = $db->prepare("SELECT id, name, parent_id FROM categories WHERE household_id = ? ORDER BY is_custom, id");
    $catList->execute([$hid]); $catList = categoryTree($catList->fetchAll());
    $catById = array_column($catList, null, 'id');
    $cat = (int)($_GET['cat'] ?? 0);
    if (!isset($catById[$cat])) $cat = 0;
    $catParent = 0; $catIds = [];
    if ($cat > 0) {
        $pid = (int)($catById[$cat]['parent_id'] ?? 0);
        // A sub whose parent vanished sits at top level in the picker; treat it the same here.
        $catParent = ($pid && isset($catById[$pid])) ? $pid : $cat;
        $catIds = [$cat];
        if ($catParent === $cat) { // top-level pick sweeps in its subs, like the rollup above
            foreach ($catList as $c) if ((int)($c['parent_id'] ?? 0) === $cat) $catIds[] = (int)$c['id'];
        }
    }
    $catSql = $catIds ? ' AND e.category_id IN (' . implode(',', array_fill(0, count($catIds), '?')) . ')' : '';
    $catQ   = $cat > 0 ? '&amp;cat=' . $cat : '';
    // Plain-& variant of both filters, for URLs that travel through JS or json_encode.
    $filterQ = ($who > 0 ? "&who=$who" : '') . ($cat > 0 ? "&cat=$cat" : '');

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

    // Day-wise spend for the strip chart in the total card — one indexed GROUP BY.
    $dayExpr = sqlDay($db, '`date`');
    $dayStmt = $db->prepare(
        "SELECT $dayExpr AS d, SUM(amount) AS amt
         FROM expenses WHERE household_id = ? AND `date` >= ? AND `date` < ?$whoSql
         GROUP BY $dayExpr"
    );
    $dayStmt->execute([$hid, $monthStart, $monthEnd, ...$whoBind]);
    // Not named $byDay — the transaction list below reuses that name for its own grouping.
    $stripDays = array_column($dayStmt->fetchAll(), 'amt', 'd');
    $dayMax    = $stripDays ? max(array_map('floatval', $stripDays)) : 0.0;

    // Paginated transaction list — LIMIT + OFFSET on the (household_id, date) index.
    $rows = $db->prepare(
        "SELECT e.*, c.name AS cat_name, c.icon AS cat_icon
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE$catSql
         ORDER BY e.`date` DESC, e.id DESC
         LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute([$hid, $monthStart, $monthEnd, ...$whoBindE, ...$catIds]);
    $expenses = $rows->fetchAll();

    // Pagination must count what the list shows — with a category filter active that is
    // narrower than the whole-month $entryCount above.
    $listCount = $entryCount;
    if ($catIds) {
        $cnt = $db->prepare(
            "SELECT COUNT(*) FROM expenses e
             WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?$whoSqlE$catSql"
        );
        $cnt->execute([$hid, $monthStart, $monthEnd, ...$whoBindE, ...$catIds]);
        $listCount = (int)$cnt->fetchColumn();
    }

    // For the add/edit-expense modal, and for naming the spender on each row. The name comes
    // from here rather than from a join on the list query, because who a row is filed under
    // reads differently depending on who is looking — see memberLabel().
    $memList  = membersFor($db, $hid, $uid);
    $memLabel = array_column($memList, 'label', 'id');
    // Adding from this screen files under you unless you say otherwise, like the Add tab does.
    $ownMem = 0;
    foreach ($memList as $m) if (isset($m['user_id']) && (int)$m['user_id'] === $uid) $ownMem = (int)$m['id'];

    ob_start();
    ?>
    <?php // The add action hangs off the end of the person filter, as on Earn. ?>
    <?= whoFilterRow($db, $hid, $memList, $who,
          '<button type="button" class="pill-btn act" style="margin-left:auto;"'
        . ' onclick="openAddExpense()">' . icon('plus', 13) . ' Add</button>') ?>
    <div class="month-switch">
      <a href="/history?m=<?= $offset + 1 ?><?= $whoQ ?><?= $catQ ?>" class="btn btn-icon" aria-label="Previous month"><?= icon('chevron-left', 20) ?></a>
      <div class="label"><?= h($label) ?></div>
      <?php if ($offset > 0): ?>
        <a href="/history?m=<?= $offset - 1 ?><?= $whoQ ?><?= $catQ ?>" class="btn btn-icon" aria-label="Next month"><?= icon('chevron-right', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
      <?php endif; ?>
    </div>

    <?php if ($entryCount === 0): ?>
      <div class="empty">No expenses this month.</div>
    <?php else: ?>
      <div class="card total-card accent">
        <div class="big"><?= h(fmt($total)) ?></div>
        <div class="sub">
          <?= $entryCount ?> <?= $entryCount === 1 ? 'entry' : 'entries' ?><?php if ($budgetTotal > 0): ?> ·
            <strong><?= h(fmt($budgetTotal)) ?></strong> budgeted
          <?php endif; ?>
        </div>
        <?php if ($dayMax > 0): $daysInMonth = (int)$anchor->format('t');
          // Today is only on this strip while the month being looked at is the current one —
          // page back to July and nothing should pulse. Computed from today(), like every
          // other date in this app, rather than asked of the database.
          $todayD = str_starts_with(today(), $anchor->format('Y-m')) ? (int)substr(today(), 8, 2) : 0;
        ?>
          <div class="day-strip">
            <?php for ($d = 1; $d <= $daysInMonth; $d++):
              $amt  = (float)($stripDays[$d] ?? 0);
              $hPct = $amt > 0 ? max(6, ($amt / $dayMax) * 100) : 0;
              $cls  = $amt <= 0 ? 'z' : ($amt >= $dayMax ? 'peak' : '');
              if ($d === $todayD) $cls = trim($cls . ' today');
              $tip  = $d . ' ' . h($anchor->format('M')) . ' — ' . h(fmt($amt))
                    . ($d === $todayD ? ' (today)' : '');
            ?><?php if ($amt > 0): // a bar with spend jumps to that date in the list ?><a<?=
              $cls ? ' class="' . $cls . '"' : '' ?> href="#d<?= $d ?>" style="height:<?= number_format($hPct, 1) ?>%" title="<?= $tip ?>" aria-label="<?= $tip ?>"></a><?php
            else: ?><i class="<?= $cls ?: 'z' ?>" title="<?= $tip ?>"></i><?php endif; ?><?php endfor; ?>
          </div>
          <div class="day-axis dense" aria-hidden="true">
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?><span><?= $d ?></span><?php endfor; ?>
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

      <?php // Category filter pills. Row one is every top-level category; row two appears
            // once a parent with subs is picked, scoped to that parent. Both default to All.
        $topCats = array_values(array_filter($catList, fn($c) => empty($c['depth'])));
        $subCats = $catParent
            ? array_values(array_filter($catList, fn($c) => (int)($c['parent_id'] ?? 0) === $catParent))
            : [];
      ?>
      <div class="pill-row scroll" role="group" aria-label="Filter by category">
        <button type="button" class="pill-btn<?= $cat === 0 ? ' on' : '' ?>" onclick="setCat(0)">All</button>
        <?php foreach ($topCats as $c): ?>
          <button type="button" class="pill-btn<?= (int)$c['id'] === $catParent ? ' on' : '' ?>"
                  onclick="setCat(<?= (int)$c['id'] ?>)"><?= h($c['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <?php if ($subCats): ?>
        <div class="pill-row scroll" role="group" aria-label="Filter by sub-category">
          <button type="button" class="pill-btn<?= $cat === $catParent ? ' on' : '' ?>"
                  onclick="setCat(<?= $catParent ?>)">All <?= h($catById[$catParent]['name'] ?? '') ?></button>
          <?php foreach ($subCats as $k): ?>
            <button type="button" class="pill-btn<?= $cat === (int)$k['id'] ? ' on' : '' ?>"
                    onclick="setCat(<?= (int)$k['id'] ?>)"><?= h($k['name']) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php
      $byDay = [];
      foreach ($expenses as $e) { $byDay[$e['date']][] = $e; }
      foreach ($byDay as $day => $entries):
        $dayLabel = (new DateTimeImmutable($day))->format('D, M j');
        $dayTotal = array_sum(array_map(fn($x) => (float)$x['amount'], $entries));
      ?>
        <div class="day-group" id="d<?= (int)substr($day, 8, 2) ?>">
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
                <?php $who1 = $memLabel[(int)($e['member_id'] ?? 0)] ?? ''; ?>
                <div class="sub"><?= h(trim(($e['note'] ?? '') . ($e['note'] && $who1 ? ' · ' : '') . $who1)) ?></div>
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
                            "back"   => "/history?m=$offset" . $filterQ,
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
        </div>
      <?php endforeach; ?>

      <?php if ($catIds && !$expenses): ?>
        <div class="empty">Nothing in this category this month.</div>
      <?php endif; ?>

      <?php
      $shown = $rowOffset + count($expenses);
      $hasMore = $shown < $listCount;
      $hasPrev = $rowOffset > 0;
      ?>
      <?php if ($hasMore || $hasPrev): ?>
        <div style="display:flex; gap:8px; justify-content:space-between; margin-top: var(--space-3);">
          <?php if ($hasPrev): $prev = max(0, $rowOffset - $pageSize); ?>
            <a class="btn btn-secondary" href="/history?m=<?= $offset ?><?= $whoQ ?><?= $catQ ?>&amp;o=<?= $prev ?>">← Newer</a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="muted" style="align-self:center;">Showing <?= $rowOffset + 1 ?>–<?= $shown ?> of <?= $listCount ?></div>
          <?php if ($hasMore): ?>
            <a class="btn btn-secondary" href="/history?m=<?= $offset ?><?= $whoQ ?><?= $catQ ?>&amp;o=<?= $rowOffset + $pageSize ?>">Older →</a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- One shared <dialog> for both jobs, on the openAddSplit/openEditSplit pattern: adding
         and editing ask for exactly the same five things, so a second dialog would be a second
         copy of the same fields. openAddExpense() repoints the action and clears it. -->
    <dialog id="edit-expense-dlg" class="confirm" style="max-width:360px;">
      <form method="post" action="/expenses/update" id="ed-form">
        <?= csrfInput() ?>
        <input type="hidden" name="id" id="ed-id">
        <input type="hidden" name="back" value="/history?m=<?= $offset ?><?= $whoQ ?><?= $catQ ?>">
        <div class="dlg-title" id="ed-dlg-title">Edit expense</div>

        <div class="field-row">
          <input class="input" name="amount" id="ed-amount" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" maxlength="13" required placeholder="Amount" oninput="edSync()">
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
            <?php foreach ($memList as $m): ?>
              <option value="<?= (int)$m['id'] ?>"><?= h($m['label']) ?></option>
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
          <button type="submit" class="btn btn-primary" id="ed-save">Save</button>
        </div>
      </form>
    </dialog>
    <script>
    var ED_TODAY = <?= json_encode(today()) ?>;
    var ED_CAT   = <?= json_encode((string)($cat ?: ($catList[0]['id'] ?? ''))) ?>;
    var ED_MEM   = <?= json_encode((string)($ownMem ?: '')) ?>;

    // Save follows the same rule as every other form here: off until the amount is real.
    function edSync() {
      document.getElementById('ed-save').disabled =
        !(parseFloat(document.getElementById('ed-amount').value) > 0);
    }

    // Add mode: a fresh expense, posting to /expenses. The id field stays disabled so it is
    // not submitted at all, rather than submitted empty. Defaults match the Add tab — today,
    // your own name — except the category, which follows whichever filter you are looking at.
    function openAddExpense() {
      document.getElementById('ed-form').action = '/expenses';
      document.getElementById('ed-id').disabled = true;
      document.getElementById('ed-dlg-title').textContent = 'Add expense';
      document.getElementById('ed-amount').value = '';
      document.getElementById('ed-date').value   = ED_TODAY;
      document.getElementById('ed-note').value   = '';
      if (ED_CAT) document.getElementById('ed-category').value = ED_CAT;
      var mem = document.getElementById('ed-member');
      if (mem) mem.value = ED_MEM;
      edSync();
      document.getElementById('edit-expense-dlg').showModal();
    }

    function openEditExpense(d) {
      document.getElementById('ed-form').action = '/expenses/update';
      var idf = document.getElementById('ed-id');
      idf.disabled = false;
      idf.value = d.id;
      document.getElementById('ed-dlg-title').textContent = 'Edit expense';
      document.getElementById('ed-amount').value   = d.amount;
      document.getElementById('ed-date').value     = d.date;
      document.getElementById('ed-category').value = d.category_id;
      var mem = document.getElementById('ed-member');
      if (mem) mem.value = d.member_id || '';
      document.getElementById('ed-note').value     = d.note || '';
      edSync();
      document.getElementById('edit-expense-dlg').showModal();
    }

    // Mirrors setWho() in layout(): keeps every other param (who, m), resets paging.
    // The pills sit mid-page, so the reload remembers where you were: scrollY goes into
    // sessionStorage and is put back below — the tap reads as an in-place update, not a
    // jump to the top of the screen.
    function setCat(v) {
      var u = new URL(location.href);
      if (v) u.searchParams.set('cat', v); else u.searchParams.delete('cat');
      u.searchParams.delete('o');
      sessionStorage.setItem('hlScroll', scrollY);
      location.replace(u.toString());
    }
    (function () {
      var s = sessionStorage.getItem('hlScroll');
      if (s !== null) { sessionStorage.removeItem('hlScroll'); scrollTo(0, +s); }
      // The pill rows' own horizontal scroll also resets on reload — center the picked
      // pill in each row so a filter chosen off the right edge doesn't come back hidden.
      document.querySelectorAll('.pill-row.scroll').forEach(function (r) {
        var on = r.querySelector('.pill-btn.on');
        if (!on) return;
        r.scrollLeft += on.getBoundingClientRect().left - r.getBoundingClientRect().left
                      - (r.clientWidth - on.clientWidth) / 2;
      });
    })();
    </script>
    <?= stripNavScript() ?>
    <?php // $filterQ, not $whoQ: these URLs go through json_encode into JS, where the
          // HTML-entity form would literally send an "amp;who" parameter. ?>
    <?= swipeNavScript("/history?m=" . ($offset + 1) . $filterQ, $offset > 0 ? "/history?m=" . ($offset - 1) . $filterQ : null) ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'history', $content, "/history?m=$offset" . $filterQ);
}

// ─── Investments ────────────────────────────────────────────────────
function renderInvest(PDO $db, array $user, bool $showForm, string $filter = 'active'): void {
    $hid = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = membersFor($db, $hid, $uid);
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

    // Bars for the filtered subset only, with sub-types folded onto their parent — the same
    // rollup the Expense tab does for sub-categories, keyed on names because that is what an
    // investment stores. A child only folds into a parent that passes the same filter.
    $byType = array_values(array_filter(
        $allTypes,
        fn($t) => $filter === 'all' || (isset($archSet[$t['type']]) === ($filter === 'archived'))
    ));
    $typeRows = $db->prepare("SELECT id, name, target, parent_id FROM investment_types WHERE household_id = ?");
    $typeRows->execute([$hid]);
    $typeByName = array_column($typeRows->fetchAll(), null, 'name');
    // Every name this filter permits, whether or not it has entries — see rollupTypes().
    $allowed = allowedTypeNames($typeByName, $archSet, $filter);
    $byType  = rollupTypes($byType, $typeByName, $allowed);

    // A target is per month, so it needs this month's figure to sit against — the totals above
    // are lifetime. One extra grouped scan on the same index, folded through the same rollup.
    $mStart = (new DateTimeImmutable('first day of this month 00:00:00'))->format('Y-m-d');
    $mEnd   = (new DateTimeImmutable('first day of next month 00:00:00'))->format('Y-m-d');
    $mtd = $db->prepare(
        "SELECT type, COUNT(*) AS n, SUM(amount) AS amt FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$whoSql GROUP BY type"
    );
    $mtd->execute([$hid, $mStart, $mEnd, ...$whoBind]);
    $thisMonth = array_column(rollupTypes($mtd->fetchAll(), $typeByName, $allowed), 'amt', 'name');

    // Paginated list, scoped to the filter.
    $pageSize  = 200;
    $rowOffset = min(100000, max(0, (int)($_GET['o'] ?? 0)));
    [$clause, $clauseParams] = investmentFilterSql($filter, $archived);
    $rows = $db->prepare(
        "SELECT * FROM investments WHERE household_id = ?$clause$whoSql ORDER BY date DESC, id DESC LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute(array_merge([$hid], $clauseParams, $whoBind)); $invs = $rows->fetchAll();

    // Month-wise sums for this year's strip chart in the top card, same filters as the list.
    $stripYear = (int)date('Y');
    $monExpr = sqlMonth($db, '`date`');
    $mStmt = $db->prepare(
        "SELECT $monExpr AS m, SUM(amount) AS amt FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql GROUP BY $monExpr"
    );
    $mStmt->execute(array_merge([$hid, "$stripYear-01-01", ($stripYear + 1) . "-01-01"], $clauseParams, $whoBind));
    $stripMonths = array_column($mStmt->fetchAll(), 'amt', 'm');
    $stripMax    = $stripMonths ? max(array_map('floatval', $stripMonths)) : 0.0;

    // Archived types stay in the edit dialog (so existing entries remain editable) but drop
    // out of the add form — you don't log new money into a scheme that has ended.
    $typeStmt = $db->prepare("SELECT name, archived FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $typeStmt->execute([$hid]); $typeList = $typeStmt->fetchAll();
    $activeTypes = array_values(array_filter($typeList, fn($t) => !(int)$t['archived']));

    // Every link on the page keeps the person filter alongside the archived/active one.
    $whoQ = $who > 0 ? '&amp;who=' . $who : '';
    $qs = fn(string $f) => '/invest?f=' . $f . ($who > 0 ? '&amp;who=' . $who : '');
    $back = '/invest?f=' . $filter . ($who > 0 ? '&who=' . $who : '');

    ob_start();
    ?>
    <!-- Person filter first: every figure, bar and row below is scoped to $who. The add action
         hangs off the end of it, as on Earn and Expense — and whoFilterRow still emits the row
         for the tail alone when a solo ledger has no people to choose between. -->
    <?= whoFilterRow($db, $hid, $mems, $who,
          '<button type="button" class="pill-btn act" style="margin-left:auto;"'
        . ' onclick="document.getElementById(\'add-inv-dlg\').showModal()">' . icon('plus', 13) . ' Add</button>') ?>

    <?php /* All time or one month at a time. All-time is the default and carries no `v` at
             all, so an old link, a bookmark and the tab bar all still land here. Rendered
             outside the has-entries guard: an empty ledger can still be switched. */ ?>
    <div class="pill-row th-mode" role="group" aria-label="Choose a view">
      <a class="pill-btn on" href="<?= $qs($filter) ?>"><?= icon('trending-up', 13) ?> All time</a>
      <a class="pill-btn" href="/invest?v=m&amp;f=<?= h($filter) ?><?= $whoQ ?>"><?= icon('calendar', 13) ?> Monthly</a>
    </div>

    <!-- Then active/archived, above the card rather than below it: it scopes the card's own
         totals and the month strip as well as the list, so sitting underneath them read as
         though it only filtered the rows it happened to precede. -->
    <?php if ($grandCount > 0): ?>
      <div class="pill-row">
        <a class="pill-btn<?= $filter === 'all' ? ' on' : '' ?>" href="<?= $qs('all') ?>">All</a>
        <a class="pill-btn<?= $filter === 'active' ? ' on' : '' ?>" href="<?= $qs('active') ?>">Active</a>
        <a class="pill-btn<?= $filter === 'archived' ? ' on' : '' ?>" href="<?= $qs('archived') ?>">Archived</a>
      </div>
    <?php endif; ?>

    <?php if ($grandCount > 0): ?>
      <div class="card total-card sage yearcard">
        <div class="split-card">
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
        <?php if ($stripMax > 0): ?>
          <div style="padding: 0 var(--space-3) var(--space-3);">
            <div class="day-strip">
              <?php
              // Same marker as the Expense day strip, one scale up. $stripYear is always the
              // current year today, so the year check never fails — it is here so the marker
              // stays right if this strip ever gains the year navigation History already has.
              $nowM = (int)$stripYear === (int)substr(today(), 0, 4) ? (int)substr(today(), 5, 2) : 0;
              for ($m = 1; $m <= 12; $m++):
                $amt  = (float)($stripMonths[$m] ?? 0);
                $hPct = $amt > 0 ? max(6, ($amt / $stripMax) * 100) : 0;
                $cls  = $amt <= 0 ? 'z' : ($amt >= $stripMax ? 'peak' : '');
                if ($m === $nowM) $cls = trim($cls . ' today');
                $mon  = date('M', mktime(0, 0, 0, $m, 1, $stripYear));
                $tip  = $mon . ' ' . $stripYear . ' — ' . h(fmt($amt)) . ($m === $nowM ? ' (this month)' : '');
              ?><?php if ($amt > 0): ?><a<?=
                $cls ? ' class="' . $cls . '"' : '' ?> href="#m<?= sprintf('%d-%02d', $stripYear, $m) ?>" style="height:<?= number_format($hPct, 1) ?>%" title="<?= $tip ?>" aria-label="<?= $tip ?>"></a><?php
              else: ?><i class="<?= $cls ?: 'z' ?>" title="<?= $tip ?>"></i><?php endif; ?><?php endfor; ?>
            </div>
            <div class="day-axis" aria-hidden="true">
              <?php for ($m = 1; $m <= 12; $m++): ?><span><?= date('M', mktime(0, 0, 0, $m, 1, $stripYear)) ?></span><?php endfor; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

    <?php endif; ?>

    <?php if ($grandCount > 0): ?>
      <div class="stack">
        <?php foreach ($byType as $t):
          $amt    = (float)$t['amt'];
          $pct    = $total > 0 ? ($amt / $total) * 100 : 0;
          $target = (float)$t['target'];
          $mAmt   = (float)($thisMonth[$t['name']] ?? 0);
          // With a target the bar tracks this month against it, exactly as a budgeted category
          // bar tracks the month's spend. Without one it stays a share of the total.
          $barPct = $target > 0 ? min(100, ($mAmt / $target) * 100) : $pct;
        ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name">
                <?= icon(isset($archSet[$t['name']]) ? 'archive' : 'trending-up', 18) ?> <?= h($t['name']) ?>
                <?php if (isset($archSet[$t['name']])): ?><span class="tag-archived">archived</span><?php endif; ?>
              </div>
              <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
            </div>
            <div class="bar sage"><i style="width: <?= number_format(max(2, $barPct), 2) ?>%"></i></div>
            <?php foreach ($t['children'] as $k): ?>
              <div class="sub-line">
                <span>↳ <?= h($k['name']) ?></span>
                <span><?= h(fmt((float)$k['amt'])) ?></span>
              </div>
            <?php endforeach; ?>
            <?php if ($target > 0): $short = $target - $mAmt; ?>
              <?php /* No red on a shortfall: falling behind a target is not the same kind of
                       news as going over a budget, and the colour would say it was. */ ?>
              <div class="budget-note">
                <span><?= h(fmt($target)) ?> a month · <?= number_format(($mAmt / $target) * 100, 0) ?>% this month</span>
                <?php if ($short > 0): ?>
                  <span><?= h(fmt($short)) ?> to go</span>
                <?php else: ?>
                  <span>target met</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
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
      <div class="day-group" id="m<?= h($ym) ?>">
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

    <?php endif; ?>
    <?= investDialogs($mems, $uid, $user, $activeTypes, $typeList, $back, $showForm) ?>
    <?= stripNavScript() ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'invest', $content, $back);
}

// ─── Invest, by month ───────────────────────────────────────────────
// The Expense tab's shape applied to the other side of the ledger: one month at a time, a day
// strip, per-type bars with their targets, a type filter and the month's entries grouped by
// day. It is a second *view*, not a second screen — the toggle at the top swaps between this
// and the all-time view, which stays the default and is unchanged.
//
// The archived/active filter and the person filter scope this exactly as they scope the other
// view, so the two answer the same question over different windows.
function renderInvestMonth(PDO $db, array $user, bool $showForm, string $filter, int $offset): void {
    $hid  = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = membersFor($db, $hid, $uid);
    if (!in_array($filter, ['all', 'active', 'archived'], true)) $filter = 'active';
    if ($offset < 0)   $offset = 0;
    if ($offset > 600) $offset = 600;   // same sanity cap the Expense tab uses, ~50 years

    $anchor     = (new DateTimeImmutable('first day of this month 00:00:00'))->modify("-{$offset} months");
    $monthStart = $anchor->format('Y-m-d');
    $monthEnd   = $anchor->modify('+1 month')->format('Y-m-d');
    $label      = $anchor->format('F Y');

    $who = (int)($_GET['who'] ?? 0);
    $who = $who > 0 ? (int)(ownedId($db, 'members', $hid, $who) ?? 0) : 0;
    [$whoSql, $whoBind] = whoWhere($who);

    $archived = archivedTypeNames($db, $hid);
    $archSet  = array_flip($archived);
    [$clause, $clauseParams] = investmentFilterSql($filter, $archived);

    // Type rows carry the tree, the targets and the names the fact table joins on.
    $typeStmt = $db->prepare("SELECT id, name, archived, target, parent_id FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $typeStmt->execute([$hid]); $typeList = $typeStmt->fetchAll();
    $activeTypes = array_values(array_filter($typeList, fn($t) => !(int)$t['archived']));
    $typeByName  = array_column($typeList, null, 'name');
    $typeTree    = categoryTree($typeList);
    $typeById    = array_column($typeTree, null, 'id');

    // Type filter for the entry list, the twin of the Expense tab's category pills. Validated
    // against this household's types, so a crafted id resets to All rather than matching
    // nothing. A parent sweeps in its sub-types; a sub-type matches only itself.
    $ty = (int)($_GET['ty'] ?? 0);
    if (!isset($typeById[$ty])) $ty = 0;
    $tyParent = 0; $tyNames = [];
    if ($ty > 0) {
        $pid = (int)($typeById[$ty]['parent_id'] ?? 0);
        $tyParent = ($pid && isset($typeById[$pid])) ? $pid : $ty;
        $tyNames  = [(string)$typeById[$ty]['name']];
        if ($tyParent === $ty) {
            foreach ($typeTree as $t) if ((int)($t['parent_id'] ?? 0) === $ty) $tyNames[] = (string)$t['name'];
        }
    }
    $tySql = $tyNames ? ' AND type IN (' . implode(',', array_fill(0, count($tyNames), '?')) . ')' : '';
    $tyQ   = $ty > 0 ? '&amp;ty=' . $ty : '';
    // Plain-& variant, for URLs that travel through JS or json_encode.
    $filterQ = "&v=m&f=$filter" . ($who > 0 ? "&who=$who" : '') . ($ty > 0 ? "&ty=$ty" : '');

    // Month aggregates — the whole month, not the filtered list, exactly as Expense does.
    $sumStmt = $db->prepare(
        "SELECT COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql"
    );
    $sumStmt->execute(array_merge([$hid, $monthStart, $monthEnd], $clauseParams, $whoBind));
    $agg = $sumStmt->fetch();
    $entryCount = (int)$agg['n'];
    $total      = (float)$agg['total'];

    // Per-type bars for the month, folded onto parents by the shared rollup.
    $grp = $db->prepare(
        "SELECT type, COUNT(*) AS n, SUM(amount) AS amt FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql GROUP BY type"
    );
    $grp->execute(array_merge([$hid, $monthStart, $monthEnd], $clauseParams, $whoBind));
    $monthTypes = $grp->fetchAll();
    $allowed    = allowedTypeNames($typeByName, $archSet, $filter);
    $byType     = rollupTypes($monthTypes, $typeByName, $allowed);

    // Household monthly target = every top-level target, including types with nothing in them
    // this month. Children are held at 0, but scope it anyway so a stale row can't double-count.
    $tgtStmt = $db->prepare("SELECT COALESCE(SUM(target), 0) FROM investment_types WHERE household_id = ? AND parent_id IS NULL");
    $tgtStmt->execute([$hid]);
    $targetTotal = (float)$tgtStmt->fetchColumn();

    // Day-wise sums for the strip chart in the total card.
    $dayExpr = sqlDay($db, '`date`');
    $dayStmt = $db->prepare(
        "SELECT $dayExpr AS d, SUM(amount) AS amt FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql GROUP BY $dayExpr"
    );
    $dayStmt->execute(array_merge([$hid, $monthStart, $monthEnd], $clauseParams, $whoBind));
    $stripDays = array_column($dayStmt->fetchAll(), 'amt', 'd');
    $dayMax    = $stripDays ? max(array_map('floatval', $stripDays)) : 0.0;

    // Paginated entry list for the month, narrowed by the type filter.
    $pageSize  = 200;
    $rowOffset = min(100000, max(0, (int)($_GET['o'] ?? 0)));
    $rows = $db->prepare(
        "SELECT * FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql$tySql
         ORDER BY `date` DESC, id DESC LIMIT $pageSize OFFSET $rowOffset"
    );
    $rows->execute(array_merge([$hid, $monthStart, $monthEnd], $clauseParams, $whoBind, $tyNames));
    $invs = $rows->fetchAll();

    // Pagination counts what the list shows, which a type filter makes narrower than the month.
    $listCount = $entryCount;
    if ($tyNames) {
        $cnt = $db->prepare(
            "SELECT COUNT(*) FROM investments
             WHERE household_id = ? AND `date` >= ? AND `date` < ?$clause$whoSql$tySql"
        );
        $cnt->execute(array_merge([$hid, $monthStart, $monthEnd], $clauseParams, $whoBind, $tyNames));
        $listCount = (int)$cnt->fetchColumn();
    }

    $whoQ = $who > 0 ? '&amp;who=' . $who : '';
    $back = "/invest?v=m&f=$filter&m=$offset" . ($who > 0 ? "&who=$who" : '') . ($ty > 0 ? "&ty=$ty" : '');
    // The view toggle and the archived/active pills each keep everything the other one set.
    $qs  = fn(string $f) => '/invest?v=m&amp;f=' . $f . '&amp;m=' . $offset . $whoQ . $tyQ;
    $mQ  = fn(int $o) => '/invest?v=m&amp;f=' . h($filter) . '&amp;m=' . $o . $whoQ . $tyQ;

    ob_start();
    ?>
    <?= whoFilterRow($db, $hid, $mems, $who,
          '<button type="button" class="pill-btn act" style="margin-left:auto;"'
        . ' onclick="document.getElementById(\'add-inv-dlg\').showModal()">' . icon('plus', 13) . ' Add</button>') ?>

    <?php /* The view toggle. All-time is the default and carries no `v` at all, so an old
             link, a bookmark and the tab bar all still land on it. */ ?>
    <div class="pill-row th-mode" role="group" aria-label="Choose a view">
      <a class="pill-btn" href="/invest?f=<?= h($filter) ?><?= $whoQ ?>"><?= icon('trending-up', 13) ?> All time</a>
      <a class="pill-btn on" href="<?= $mQ($offset) ?>"><?= icon('calendar', 13) ?> Monthly</a>
    </div>

    <div class="pill-row">
      <a class="pill-btn<?= $filter === 'all' ? ' on' : '' ?>" href="<?= $qs('all') ?>">All</a>
      <a class="pill-btn<?= $filter === 'active' ? ' on' : '' ?>" href="<?= $qs('active') ?>">Active</a>
      <a class="pill-btn<?= $filter === 'archived' ? ' on' : '' ?>" href="<?= $qs('archived') ?>">Archived</a>
    </div>

    <div class="month-switch">
      <a href="<?= $mQ($offset + 1) ?>" class="btn btn-icon" aria-label="Previous month"><?= icon('chevron-left', 20) ?></a>
      <div class="label"><?= h($label) ?></div>
      <?php if ($offset > 0): ?>
        <a href="<?= $mQ($offset - 1) ?>" class="btn btn-icon" aria-label="Next month"><?= icon('chevron-right', 20) ?></a>
      <?php else: ?>
        <span class="btn btn-icon" style="opacity:.35;pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
      <?php endif; ?>
    </div>

    <?php if ($entryCount === 0): ?>
      <div class="empty">Nothing invested this month.</div>
    <?php else: ?>
      <div class="card total-card sage">
        <div class="big"><?= h(fmt($total)) ?></div>
        <div class="sub">
          <?= $entryCount ?> <?= $entryCount === 1 ? 'entry' : 'entries' ?><?php if ($targetTotal > 0): ?> ·
            <strong><?= h(fmt($targetTotal)) ?></strong> targeted
          <?php endif; ?>
        </div>
        <?php if ($dayMax > 0): $daysInMonth = (int)$anchor->format('t');
          // Today only pulses while the month on screen is the current one.
          $todayD = str_starts_with(today(), $anchor->format('Y-m')) ? (int)substr(today(), 8, 2) : 0;
        ?>
          <div class="day-strip">
            <?php for ($d = 1; $d <= $daysInMonth; $d++):
              $amt  = (float)($stripDays[$d] ?? 0);
              $hPct = $amt > 0 ? max(6, ($amt / $dayMax) * 100) : 0;
              $cls  = $amt <= 0 ? 'z' : ($amt >= $dayMax ? 'peak' : '');
              if ($d === $todayD) $cls = trim($cls . ' today');
              $tip  = $d . ' ' . h($anchor->format('M')) . ' — ' . h(fmt($amt))
                    . ($d === $todayD ? ' (today)' : '');
            ?><?php if ($amt > 0): ?><a<?=
              $cls ? ' class="' . $cls . '"' : '' ?> href="#d<?= $d ?>" style="height:<?= number_format($hPct, 1) ?>%" title="<?= $tip ?>" aria-label="<?= $tip ?>"></a><?php
            else: ?><i class="<?= $cls ?: 'z' ?>" title="<?= $tip ?>"></i><?php endif; ?><?php endfor; ?>
          </div>
          <div class="day-axis dense" aria-hidden="true">
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?><span><?= $d ?></span><?php endfor; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="stack">
        <?php foreach ($byType as $t):
          $amt    = (float)$t['amt'];
          $pct    = $total > 0 ? ($amt / $total) * 100 : 0;
          $target = (float)$t['target'];
          // Here the bar and the target measure the same window, so it tracks the target
          // directly — no second query, unlike the all-time view.
          $barPct = $target > 0 ? min(100, ($amt / $target) * 100) : $pct;
        ?>
          <div class="card cat-bar">
            <div class="top">
              <div class="name">
                <?= icon(isset($archSet[$t['name']]) ? 'archive' : 'trending-up', 18) ?> <?= h($t['name']) ?>
                <?php if (isset($archSet[$t['name']])): ?><span class="tag-archived">archived</span><?php endif; ?>
              </div>
              <div><span class="amt"><?= h(fmt($amt)) ?></span><span class="pct"><?= number_format($pct, 2) ?>%</span></div>
            </div>
            <div class="bar sage"><i style="width: <?= number_format(max(2, $barPct), 2) ?>%"></i></div>
            <?php foreach ($t['children'] as $k): ?>
              <div class="sub-line">
                <span>↳ <?= h($k['name']) ?></span>
                <span><?= h(fmt((float)$k['amt'])) ?></span>
              </div>
            <?php endforeach; ?>
            <?php if ($target > 0): $short = $target - $amt; ?>
              <div class="budget-note">
                <span><?= h(fmt($target)) ?> target · <?= number_format(($amt / $target) * 100, 0) ?>% put in</span>
                <?php if ($short > 0): ?>
                  <span><?= h(fmt($short)) ?> to go</span>
                <?php else: ?>
                  <span>target met</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php // Type filter pills. Row one is every top-level type; row two appears once a
            // parent with sub-types is picked, scoped to that parent. Both default to All.
        $topTypes = array_values(array_filter($typeTree, fn($t) => empty($t['depth'])));
        $subTypes = $tyParent
            ? array_values(array_filter($typeTree, fn($t) => (int)($t['parent_id'] ?? 0) === $tyParent))
            : [];
      ?>
      <div class="pill-row scroll" role="group" aria-label="Filter by type">
        <button type="button" class="pill-btn<?= $ty === 0 ? ' on' : '' ?>" onclick="setTy(0)">All</button>
        <?php foreach ($topTypes as $t): ?>
          <button type="button" class="pill-btn<?= (int)$t['id'] === $tyParent ? ' on' : '' ?>"
                  onclick="setTy(<?= (int)$t['id'] ?>)"><?= h($t['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <?php if ($subTypes): ?>
        <div class="pill-row scroll" role="group" aria-label="Filter by sub-type">
          <button type="button" class="pill-btn<?= $ty === $tyParent ? ' on' : '' ?>"
                  onclick="setTy(<?= $tyParent ?>)">All <?= h($typeById[$tyParent]['name'] ?? '') ?></button>
          <?php foreach ($subTypes as $k): ?>
            <button type="button" class="pill-btn<?= $ty === (int)$k['id'] ? ' on' : '' ?>"
                    onclick="setTy(<?= (int)$k['id'] ?>)"><?= h($k['name']) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php
      $byDay = [];
      foreach ($invs as $i) { $byDay[$i['date']][] = $i; }
      foreach ($byDay as $day => $entries):
        $dayLabel = (new DateTimeImmutable($day))->format('D, M j');
        $dayTotal = array_sum(array_map(fn($x) => (float)$x['amount'], $entries));
      ?>
        <div class="day-group" id="d<?= (int)substr($day, 8, 2) ?>">
        <div class="day-hdr" style="display:flex; justify-content:space-between; align-items:baseline;">
          <span><?= h($dayLabel) ?></span>
          <span style="font-family:var(--font-body); font-size:12px; font-weight:600; color:var(--color-neutral-800);"><?= h(fmt($dayTotal)) ?></span>
        </div>
        <div class="stack">
          <?php foreach ($entries as $i): ?>
            <?php $invJson = json_encode([
                'id'        => (int)$i['id'],
                'name'      => $i['name'],
                'amount'    => (string)$i['amount'],
                'type'      => $i['type'],
                'date'      => $i['date'],
                'member_id' => (int)($i['member_id'] ?? 0),
            ]); ?>
            <?php $rowArch = isset($archSet[$i['type']]); ?>
            <div class="card elev-sm row<?= $rowArch ? ' archived' : '' ?>">
              <div class="row-icon sage"><?= icon($rowArch ? 'archive' : 'trending-up', 16) ?></div>
              <div class="row-main">
                <div class="title"><?= h($i['name']) ?></div>
                <div class="sub"><?= h($i['type']) ?><?= $rowArch ? ' (archived)' : '' ?></div>
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
                            "back"   => "/invest?m=$offset" . $filterQ,
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
        </div>
      <?php endforeach; ?>

      <?php if ($tyNames && !$invs): ?>
        <div class="empty">Nothing in this type this month.</div>
      <?php endif; ?>

      <?php
      $shown   = $rowOffset + count($invs);
      $hasMore = $shown < $listCount;
      $hasPrev = $rowOffset > 0;
      ?>
      <?php if ($hasMore || $hasPrev): ?>
        <div style="display:flex; gap:8px; justify-content:space-between; margin-top: var(--space-3);">
          <?php if ($hasPrev): $prev = max(0, $rowOffset - $pageSize); ?>
            <a class="btn btn-secondary" href="<?= $mQ($offset) ?>&amp;o=<?= $prev ?>">← Newer</a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="muted" style="align-self:center;">Showing <?= $rowOffset + 1 ?>–<?= $shown ?> of <?= $listCount ?></div>
          <?php if ($hasMore): ?>
            <a class="btn btn-secondary" href="<?= $mQ($offset) ?>&amp;o=<?= $rowOffset + $pageSize ?>">Older →</a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?= investDialogs($mems, $uid, $user, $activeTypes, $typeList, $back, $showForm) ?>
    <script>
    // The twin of setCat() on the Expense tab, down to remembering where you were: the pills
    // sit mid-page, so the reload puts scrollY back and centres the pill you picked.
    function setTy(v) {
      var u = new URL(location.href);
      if (v) u.searchParams.set('ty', v); else u.searchParams.delete('ty');
      u.searchParams.delete('o');
      sessionStorage.setItem('hlScroll', scrollY);
      location.replace(u.toString());
    }
    (function () {
      var s = sessionStorage.getItem('hlScroll');
      if (s !== null) { sessionStorage.removeItem('hlScroll'); scrollTo(0, +s); }
      document.querySelectorAll('.pill-row.scroll').forEach(function (r) {
        var on = r.querySelector('.pill-btn.on');
        if (!on) return;
        r.scrollLeft += on.getBoundingClientRect().left - r.getBoundingClientRect().left
                      - (r.clientWidth - on.clientWidth) / 2;
      });
    })();
    </script>
    <?= stripNavScript() ?>
    <?= swipeNavScript("/invest?m=" . ($offset + 1) . $filterQ, $offset > 0 ? "/invest?m=" . ($offset - 1) . $filterQ : null) ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'invest', $content, $back);
}

// ─── Investment dialogs, shared by both Invest views ────────────────
// Add and edit both open a <dialog> rather than re-rendering the page with an inline form —
// one affordance for "fill in an investment", wherever you started from. Lifted out of
// renderInvest() when the monthly view arrived: the two views differ in what they summarise,
// never in how you enter or correct an entry, so a second copy would be two places to fix.
//
// $back is the page's own URL, so a save lands you back on the view (and month) you were on.
function investDialogs(array $mems, int $uid, array $user, array $activeTypes, array $typeList,
                       string $back, bool $showForm): string {
    ob_start();
    ?>
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

      <dialog id="edit-investment-dlg" class="confirm" style="max-width:360px;">
        <form method="post" action="/investments/update">
          <?= csrfInput() ?>
          <input type="hidden" name="id" id="ei-id">
          <input type="hidden" name="back" value="<?= h($back) ?>">
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
    <?php
    return (string)ob_get_clean();
}

// ─── Earnings ───────────────────────────────────────────────────────
// Mirror of the Invest tab: summary, breakdown, add form, paginated list. The extra piece is
// the twelve-month earned/spent/invested chart — the one place the three ledgers meet.
function renderEarn(PDO $db, array $user, bool $showForm): void {
    $hid = (int)$user['household_id'];
    $uid  = (int)$user['id'];
    $mems = membersFor($db, $hid, $uid);

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
            "SELECT " . sqlYm($db, '`date`') . " AS ym, SUM(amount) AS amt FROM $table
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
    <!-- Person filter sits above everything: every figure, bar and row below is already scoped
         to $who, so the control belongs where its effect starts, not at the bottom. The add
         action hangs off the end of the same row. -->
    <?= whoFilterRow($db, $hid, $mems, $who,
          '<button type="button" class="pill-btn act" style="margin-left:auto;"'
        . ' onclick="document.getElementById(\'add-ern-dlg\').showModal()">' . icon('plus', 13) . ' Add</button>') ?>
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

    <?php if ($peak > 0):
      // Share of the same 12-month window the bars cover, so the two halves of the card always agree.
      $winTot = $winEarn + $winSpent + $winInv;
      $slices = [
        ['ern', 'Earned',   $winEarn],
        ['exp', 'Spent',    $winSpent],
        ['inv', 'Invested', $winInv],
      ];

      /**
       * One period's worth of pie: the gradient, and each slice's amount and share.
       *
       * Built here rather than in JavaScript because the amounts have to go through fmt(),
       * which knows the ledger's currency symbol and whether it groups in lakh or thousands.
       * Reimplementing that in the browser is how the two would eventually disagree.
       */
      $pieOf = function (float $e, float $x, float $iv, string $period): array {
        $tot = $e + $x + $iv;
        $stops = []; $rows = []; $said = []; $at = 0.0;
        foreach ([['ern', 'Earned', $e], ['exp', 'Spent', $x], ['inv', 'Invested', $iv]] as [$k, $label, $amt]) {
          $pct  = $tot > 0 ? ($amt / $tot) * 100 : 0;
          $at  += $pct;
          $var  = $k === 'exp' ? 'var(--color-accent)' : ($k === 'inv' ? 'var(--color-accent-2)' : 'var(--color-neutral-700)');
          $stops[] = "$var 0 " . number_format($at, 2) . '%';
          $rows[]  = ['amt' => fmt($amt), 'pct' => number_format($pct, 1) . '%'];
          $said[]  = $label . ' ' . number_format($pct, 1) . '%';
        }
        return [
          // A month with nothing in it has no shares to draw. Three stops all at 0% would
          // paint the whole disc the last colour, which reads as "everything was invested".
          'grad'  => $tot > 0 ? 'conic-gradient(' . implode(', ', $stops) . ')' : 'var(--color-neutral-300)',
          'rows'  => $rows,
          'label' => $tot > 0 ? "Share of $period: " . implode(', ', $said) : "Nothing recorded in $period",
        ];
      };

      // The month pills need no extra query: $series already holds every month of the rolling
      // window, and every elapsed month of the current year is necessarily inside twelve.
      $thisYear  = substr(today(), 0, 4);
      $pie       = ['all' => $pieOf($winEarn, $winSpent, $winInv, 'the last 12 months')];
      $pieMonths = [];
      foreach ($winKeys as $ym) {
        if (substr($ym, 0, 4) !== $thisYear) continue;
        $mLabel = (new DateTimeImmutable($ym . '-01'))->format('F Y');
        $pie[$ym] = $pieOf(
          $series['ern'][$ym] ?? 0.0, $series['exp'][$ym] ?? 0.0, $series['inv'][$ym] ?? 0.0, $mLabel
        );
        $pieMonths[] = $ym;
      }
    ?>
      <div class="card ychart">
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
        <div class="muted" style="margin-top:8px; font-size:11.5px;">Last 12 months</div>
        <?php
        // Opens on this month, not on the whole window: the question people arrive with is
        // "how is this month going", and the bars above already answer the twelve-month one.
        $pieNow  = substr(today(), 0, 7);
        $pieOpen = isset($pie[$pieNow]) ? $pieNow : 'all';
        $init    = $pie[$pieOpen];
        ?>
        <?php /* Scopes the pie only — the bars above always show the full twelve months, so
                 you can see a single month's mix without losing the shape it sits in. */ ?>
        <div class="pill-row scroll" role="group" aria-label="Period for the share breakdown"
             id="pie-pills" style="margin-top:10px;">
          <button type="button" class="pill-btn<?= $pieOpen === 'all' ? ' on' : '' ?>" data-pie="all">All</button>
          <?php foreach ($pieMonths as $ym): ?>
            <button type="button" class="pill-btn<?= $pieOpen === $ym ? ' on' : '' ?>" data-pie="<?= h($ym) ?>"><?=
              h((new DateTimeImmutable($ym . '-01'))->format('M')) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="pieblock">
          <div class="pie" id="pie-disc" role="img" aria-label="<?= h($init['label']) ?>"
               style="background: <?= h($init['grad']) ?>;"></div>
          <div class="pielist">
            <?php foreach ($slices as $i => [$k, $label, ]): ?>
              <div class="r">
                <i class="sw <?= $k ?>"></i>
                <span class="nm"><?= h($label) ?></span>
                <span class="amt" id="pie-amt-<?= $k ?>"><?= h($init['rows'][$i]['amt']) ?></span>
                <span class="pct" id="pie-pct-<?= $k ?>"><?= h($init['rows'][$i]['pct']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <script>
        (function () {
          // Every period is computed and formatted server-side and shipped with the page, so
          // switching is a repaint rather than a request — thirteen periods of three numbers
          // is smaller than the round trip would be.
          var pie = <?= json_encode($pie, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
          var keys = ['ern', 'exp', 'inv'];
          var pills = document.getElementById('pie-pills');
          var disc  = document.getElementById('pie-disc');
          // The row opens on the current month, which is the last pill — off-screen on a phone
          // until it is scrolled to. scrollLeft rather than scrollIntoView(), which would also
          // scroll the page and land the visitor halfway down the card.
          var on = pills.querySelector('.pill-btn.on');
          if (on) pills.scrollLeft = on.offsetLeft - (pills.clientWidth - on.offsetWidth) / 2;
          pills.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-pie]');
            if (!btn) return;
            var d = pie[btn.getAttribute('data-pie')];
            if (!d) return;
            pills.querySelectorAll('.pill-btn').forEach(function (b) { b.classList.toggle('on', b === btn); });
            disc.style.background = d.grad;
            disc.setAttribute('aria-label', d.label);
            keys.forEach(function (k, i) {
              document.getElementById('pie-amt-' + k).textContent = d.rows[i].amt;
              document.getElementById('pie-pct-' + k).textContent = d.rows[i].pct;
            });
          });
        })();
        </script>
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
    $mems = membersFor($db, $hid, $uid);

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
    // Built by concatenation, not sprintf — the '%Y-%m' would be eaten as a format spec.
    $monthly = [];
    $selectYm = "SELECT " . sqlYm($db, '`date`') . " AS ym, SUM(amount) AS amt, COUNT(*) AS n FROM ";
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
    <!-- Person filter first: every figure, bar and row below is scoped to $who. -->
    <?= whoFilterRow($db, $hid, $mems, $who) ?>
    <!-- Plain links, not radios+onchange: a change handler can fire from scroll/state
         restoration and navigate unintentionally. Links only move when tapped. -->
    <div class="seg year-seg" role="group" aria-label="Year type">
      <a class="seg-opt<?= $mode === 'cal' ? ' on' : '' ?>" href="/year?mode=cal&amp;inv=<?= h($invFilter) ?><?= $whoQ ?>"
         <?= $mode === 'cal' ? 'aria-current="page"' : '' ?>>Calendar year</a>
      <a class="seg-opt<?= $mode === 'fy' ? ' on' : '' ?>" href="/year?mode=fy&amp;inv=<?= h($invFilter) ?><?= $whoQ ?>"
         <?= $mode === 'fy' ? 'aria-current="page"' : '' ?>>Financial year</a>
    </div>

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
        <div class="muted" style="margin-top:8px; font-size:11.5px;">Tap a month to open it in Expense.</div>
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
    $mems = membersFor($db, $hid, $uid);
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
          <option value="expense">Expense — auto-post to the Expense tab</option>
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
          Expense every month, and months already past appear straight away.
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
        + 'Expense every month, and months already past appear straight away.';
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
        'Changing any of this recalculates every share and re-posts them to Expense. '
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
      <a href="/#profile" class="btn btn-icon btn-back" aria-label="Back"><?= icon('chevron-left', 20) ?></a>
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

    <?= organiseTools('expense', 'category', 'budget', 'spending') ?>
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
// The nest/move confirmations, shared by both organise pages. Own dialogs rather than the
// shared askConfirm(): that one posts a fixed id/back pair, and these need two selects' worth
// of state. The wording is the only thing that differs between expense categories and
// investment types, so it comes in as four nouns rather than as a second copy of the file.
//
// $entry  — what a row is ("expense" / "investment")
// $thing  — what it is filed under ("category" / "type")
// $limit  — the monthly figure ("budget" / "target")
// $money  — what rolls up ("spending" / "money")
function organiseTools(string $entry, string $thing, string $limit, string $money): string {
    $entries = $entry . 's';
    $things  = $thing === 'category' ? 'categories' : $thing . 's';
    $j = fn(string $v): string => json_encode($v, JSON_UNESCAPED_UNICODE);
    [$jEntry, $jEntries, $jThings, $jLimit, $jMoney] =
        [$j($entry), $j($entries), $j($things), $j($limit), $j($money)];
    return <<<TOOLS
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
    var ORG = {entry: $jEntry, entries: $jEntries, things: $jThings, limit: $jLimit, money: $jMoney};
    function syncMove() {
      var f = document.getElementById('move-from'), t = document.getElementById('move-to');
      var n = parseInt(f.selectedOptions[0].dataset.n || '0', 10);
      var same = f.value === t.value;
      var btn = document.getElementById('move-btn');
      btn.disabled = same || n === 0;
      btn.textContent = same ? 'Pick two different ' + ORG.things
                     : n === 0 ? 'Nothing to move'
                     : 'Move ' + n + ' ' + (n === 1 ? ORG.entry : ORG.entries);
    }
    function askMove(e) {
      // Second time round — doMove() already asked — let it through to the submit handler.
      if (e.target.dataset.ok) { delete e.target.dataset.ok; return true; }
      e.preventDefault();
      var f = document.getElementById('move-from'), t = document.getElementById('move-to');
      document.getElementById('move-body').textContent =
        'Every ' + ORG.entry + ' in "' + f.selectedOptions[0].dataset.name + '" moves to "' +
        t.selectedOptions[0].dataset.name + '", along with any recurring item that posts into it. ' +
        'Moving them back afterwards would also carry entries that were already there.';
      document.getElementById('move-dlg').showModal();
      return false;
    }
    function doMove() {
      document.getElementById('move-dlg').close();
      var f = document.getElementById('move-form');
      f.dataset.ok = '1';
      f.requestSubmit();
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
      if (e.target.dataset.ok) { delete e.target.dataset.ok; return true; }
      e.preventDefault();
      var c = document.getElementById('nest-id'), p = document.getElementById('nest-parent');
      var kid = c.selectedOptions[0].text, par = p.selectedOptions[0].text;
      var bud = c.selectedOptions[0].dataset.budget;
      document.getElementById('nest-title').textContent = 'Nest ' + kid + ' under ' + par + '?';
      document.getElementById('nest-dlg-body').textContent =
        kid + ' keeps its own entries, but its ' + ORG.money + ' now rolls up into ' + par +
        ' and counts towards that ' + ORG.limit + ' instead of standing on its own.' +
        // The one part that can't be undone by moving it back out.
        (bud ? ' Its ' + bud + ' monthly ' + ORG.limit + ' will be cleared — sub-'
             + ORG.things + ' don’t carry one.' : '');
      document.getElementById('nest-dlg').showModal();
      return false;
    }
    function doNest() {
      document.getElementById('nest-dlg').close();
      var f = document.getElementById('nest-form');
      f.dataset.ok = '1';
      f.requestSubmit();
    }
    syncMove(); syncNest();
    </script>
TOOLS;
}

// ─── Organise investment types ──────────────────────────────────────
// The twin of renderOrganise(), for the other side of the ledger. Same shape on purpose: one
// card per top-level type, sub-types on a spine beneath it, then nest / add / move.
//
// The one real difference is underneath. An expense names its category by id; an investment
// names its type by the type's *name*, so every tool here works on strings — moving entries
// rewrites `investments.type`, and renaming cascades. That is also why two types may not share
// a name (typeNameTaken), which categories never had to care about.
function renderOrganiseInvest(PDO $db, array $user): void {
    $hid = (int)$user['household_id'];
    $types = $db->prepare("SELECT * FROM investment_types WHERE household_id = ? ORDER BY archived, id");
    $types->execute([$hid]); $types = $types->fetchAll();
    $tree = categoryTree($types);   // id/parent_id only — the shape is identical

    // Entry counts drive the "Move 23 investments" label, so the tap is never blind. Keyed by
    // name, because that is the column an investment actually holds.
    $s = $db->prepare("SELECT type, COUNT(*) n FROM investments WHERE household_id = ? GROUP BY type");
    $s->execute([$hid]); $counts = array_column($s->fetchAll(), 'n', 'type');
    // Recurring items matter here too: they keep posting into whatever name they hold.
    $r = $db->prepare("SELECT type, COUNT(*) n FROM recurring WHERE household_id = ? AND kind = 'investment' GROUP BY type");
    $r->execute([$hid]); $recCounts = array_column($r->fetchAll(), 'n', 'type');
    // Same predicate the move tool uses, so the number on screen is exactly what it touches.
    $u = $db->prepare("SELECT COUNT(*) FROM investments WHERE " . unknownTypeWhere());
    $u->execute([$hid, $hid]); $nUnknown = (int)$u->fetchColumn();

    $currency = $_SESSION['currency'] ?? '₹';
    $kids = [];
    foreach ($types as $t) if (!empty($t['parent_id'])) $kids[(int)$t['parent_id']][] = $t;
    $kidCount = array_map('count', $kids);
    $tops = array_values(array_filter($types, fn($t) => empty($t['parent_id'])));
    // Types that actually have sub-types lead, so the hierarchy is the first thing on the page.
    usort($tops, fn($a, $b) => ($kidCount[(int)$b['id']] ?? 0) <=> ($kidCount[(int)$a['id']] ?? 0));
    $canDelete = count($types) > 1;

    ob_start();
    ?>
    <div class="month-switch">
      <a href="/#profile" class="btn btn-icon btn-back" aria-label="Back"><?= icon('chevron-left', 20) ?></a>
      <div class="label" style="font-size:16px;">Organise investment types</div>
      <span class="btn btn-icon" style="opacity:0; pointer-events:none;"><?= icon('chevron-right', 20) ?></span>
    </div>

    <div class="muted" style="font-size:12px; margin: 0 2px;">
      Rename, set a target per month, nest, archive and delete — everything about investment
      types lives here. A sub-type's money rolls up into its parent's bar and target, so only
      parents carry one. Archive a type when a scheme ends: its entries stay logged and drop
      out of the active view.
    </div>

    <div class="stack">
      <?php foreach ($tops as $t):
        $list  = $kids[(int)$t['id']] ?? [];
        $nAll  = (int)($counts[$t['name']] ?? 0);
        foreach ($list as $k) $nAll += (int)($counts[$k['name']] ?? 0);
        $isArch = (int)$t['archived'] === 1;
        $nRec   = (int)($recCounts[$t['name']] ?? 0);
      ?>
        <div class="card tree-node<?= $isArch ? ' archived' : '' ?>">
          <div class="tree-head">
            <form method="post" action="/investment-types/update" class="tree-row">
              <?= csrfInput() ?>
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="back" value="/organise-invest">
              <span class="tree-ico"><?= icon($isArch ? 'archive' : 'trending-up', 16) ?></span>
              <input class="input" name="name" value="<?= h($t['name']) ?>" maxlength="40" aria-label="Type name">
              <input class="input budget-in" name="target" type="text" inputmode="decimal"
                     pattern="\d{0,10}(\.\d{1,2})?" maxlength="13"
                     value="<?= (float)$t['target'] > 0 ? h(rtrim(rtrim(number_format((float)$t['target'], 2, '.', ''), '0'), '.')) : '' ?>"
                     placeholder="<?= h($currency) ?>" aria-label="Target per month for <?= h($t['name']) ?>">
              <button class="icon-btn" type="submit" aria-label="Save <?= h($t['name']) ?>"><?= icon('check', 15) ?></button>
            </form>
            <?php if ($isArch): ?>
              <?php /* Restoring only widens what is visible, so it goes straight through. */ ?>
              <form method="post" action="/investment-types/archive" style="margin:0; display:inline-flex;">
                <?= csrfInput() ?>
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <input type="hidden" name="back" value="/organise-invest">
                <button class="icon-btn" type="submit" aria-label="Restore <?= h($t['name']) ?>"
                        title="Restore to active"><?= icon('archive-restore', 15) ?></button>
              </form>
            <?php else: ?>
              <button type="button" class="icon-btn" title="Archive" aria-label="Archive <?= h($t['name']) ?>"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/investment-types/archive",
                          "id"     => (int)$t['id'],
                          "back"   => "/organise-invest",
                          "csrf"   => csrfToken(),
                          "title"  => "Archive " . $t['name'] . "?",
                          "body"   => ($nAll === 1 ? '1 investment moves' : "$nAll investments move")
                                      . ' out of the active view. Nothing is deleted — you can restore it any time.'
                                      // The genuine surprise: a recurring item keeps posting after archiving.
                                      . ($nRec > 0 ? ' Heads up: ' . ($nRec === 1 ? '1 recurring item' : "$nRec recurring items")
                                          . ' still post into this type, so new entries will keep arriving as archived.'
                                          . ' Delete them on the Recurring tab to stop that.' : ''),
                          "ok"     => "Archive",
                          "danger" => false,
                      ])) ?>)'><?= icon('archive', 15) ?></button>
            <?php endif; ?>
            <?php if ($canDelete): ?>
              <button type="button" class="icon-btn" aria-label="Delete <?= h($t['name']) ?>"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/investment-types/delete",
                          "id"     => (int)$t['id'],
                          "back"   => "/organise-invest",
                          "csrf"   => csrfToken(),
                          "title"  => "Delete " . $t['name'] . "?",
                          // Deletion is refused server-side while anything still names the type,
                          // so the dialog says which it will be rather than promising either.
                          "body"   => ($nAll === 0
                                        ? 'Nothing is logged under it.'
                                        : "It still holds $nAll entr" . ($nAll === 1 ? 'y' : 'ies')
                                          . ', so this will be refused — move them into another type first.')
                                      . ($list ? ' Its ' . (count($list) === 1 ? 'sub-type moves' : count($list) . ' sub-types move')
                                               . ' back to top level.' : ''),
                          "ok"     => "Delete",
                      ])) ?>)'><?= icon('trash-2', 14) ?></button>
            <?php endif; ?>
          </div>
          <div class="tree-metaline">
            <?php if ((float)$t['target'] > 0): ?><?= h(fmtShort((float)$t['target'])) ?> a month · <?php endif; ?>
            <?= $nAll ?> <?= $nAll === 1 ? 'entry' : 'entries' ?><?= $list ? ' incl. sub' : '' ?>
            <?= $isArch ? ' · archived' : '' ?>
          </div>

          <?php foreach ($list as $i => $k): $nK = (int)($counts[$k['name']] ?? 0); ?>
            <div class="tree-kid<?= $i === count($list) - 1 ? ' last' : '' ?>">
              <form method="post" action="/investment-types/update" class="tree-row">
                <?= csrfInput() ?>
                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                <input type="hidden" name="back" value="/organise-invest">
                <!-- No target field: /investment-types/update reads a missing `target` as blank,
                     which parseBudget turns into 0 — exactly what a sub-type must hold. -->
                <span class="tree-ico"><?= icon('trending-up', 14) ?></span>
                <input class="input" name="name" value="<?= h($k['name']) ?>" maxlength="40" aria-label="Sub-type name">
                <span class="tree-meta"><?= $nK ?></span>
                <button class="icon-btn" type="submit" aria-label="Save <?= h($k['name']) ?>"><?= icon('check', 15) ?></button>
              </form>
              <button type="button" class="icon-btn" title="Move out to top level"
                      aria-label="Move <?= h($k['name']) ?> out of <?= h($t['name']) ?>"
                      onclick='askConfirm(<?= h(json_encode([
                          "action" => "/investment-types/parent",
                          "id"     => (int)$k['id'],
                          "back"   => "/organise-invest",
                          "csrf"   => csrfToken(),
                          "title"  => "Move " . $k['name'] . " out?",
                          "body"   => $k['name'] . ' becomes a top-level type again'
                                      . ($nK ? ", keeping its $nK entr" . ($nK === 1 ? 'y' : 'ies') : '')
                                      . '. Its money stops rolling up into ' . $t['name']
                                      . ', so it no longer counts towards that target — and it can carry one of its own again.',
                          "ok"     => "Move out",
                          "danger" => false,
                      ])) ?>)'><?= icon('corner-left-up', 14) ?></button>
              <?php if ($canDelete): ?>
                <button type="button" class="icon-btn" aria-label="Delete <?= h($k['name']) ?>"
                        onclick='askConfirm(<?= h(json_encode([
                            "action" => "/investment-types/delete",
                            "id"     => (int)$k['id'],
                            "back"   => "/organise-invest",
                            "csrf"   => csrfToken(),
                            "title"  => "Delete " . $k['name'] . "?",
                            "body"   => $nK === 0 ? 'Nothing is logged under it.'
                                      : "It still holds $nK entr" . ($nK === 1 ? 'y' : 'ies')
                                        . ', so this will be refused — move them into another type first.',
                            "ok"     => "Delete",
                        ])) ?>)'><?= icon('trash-2', 14) ?></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="post" action="/investment-types" class="row-form" style="margin-top:2px;">
      <?= csrfInput() ?>
      <input type="hidden" name="back" value="/organise-invest">
      <input class="input" name="name" placeholder="New type" maxlength="40">
      <input class="input budget-in" name="target" type="text" inputmode="decimal"
             pattern="\d{0,10}(\.\d{1,2})?" maxlength="13"
             placeholder="<?= h($currency) ?>" aria-label="Target per month">
      <button class="btn btn-primary" type="submit">Add</button>
    </form>

    <?php
    // Only a type with no sub-types of its own can become one — one level, enforced server-side.
    $nestable = array_values(array_filter($types, fn($t) => ($kidCount[(int)$t['id']] ?? 0) === 0));
    ?>
    <?php if ($nestable && count($tops) > 1): ?>
      <div class="day-hdr">Nest a type</div>
      <form method="post" action="/investment-types/parent" class="card stack" id="nest-form"
            style="padding:var(--space-4); gap:10px;" onsubmit="return askNest(event)">
        <?= csrfInput() ?>
        <input type="hidden" name="back" value="/organise-invest">
        <div class="field-row" style="align-items:center; gap:6px;">
          <select class="select" name="id" id="nest-id" onchange="syncNest()">
            <?php foreach ($nestable as $t): ?>
              <!-- data-budget drives the confirmation: nesting zeroes a target, and that value
                   is the one thing here you can't get back with a second tap. -->
              <option value="<?= (int)$t['id'] ?>" data-budget="<?= h((float)$t['target'] > 0 ? fmt((float)$t['target']) : '') ?>"><?= h($t['name']) ?></option>
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
    <form method="post" action="/investment-types/move" id="move-form" class="card stack"
          style="padding:var(--space-4); gap:10px;" onsubmit="return askMove(event)">
      <?= csrfInput() ?>
      <input type="hidden" name="back" value="/organise-invest">
      <div class="muted" style="font-size:12px;">Merge one type into another. Every investment moves, and so does any recurring item that posts into it. The emptied type stays — delete it above once you're done with it.</div>
      <label class="muted" style="font-size:11px;">From</label>
      <select class="select" name="from_id" id="move-from" onchange="syncMove()">
        <?php if ($nUnknown > 0): ?>
          <!-- id 0 is the pseudo-type; the handler maps it to unknownTypeWhere(). -->
          <option value="0" data-n="<?= $nUnknown ?>" data-name="Unrecognised">Unrecognised (<?= $nUnknown ?>)</option>
        <?php endif; ?>
        <?php foreach ($tree as $t): $n = (int)($counts[$t['name']] ?? 0); ?>
          <option value="<?= (int)$t['id'] ?>" data-n="<?= $n ?>" data-name="<?= h($t['name']) ?>">
            <?= $t['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($t['name']) ?> (<?= $n ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <label class="muted" style="font-size:11px;">To</label>
      <select class="select" name="to_id" id="move-to" onchange="syncMove()">
        <?php foreach ($tree as $t): ?>
          <option value="<?= (int)$t['id'] ?>" data-name="<?= h($t['name']) ?>">
            <?= $t['depth'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($t['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-block" type="submit" id="move-btn">Move entries</button>
    </form>

    <?php if ($nUnknown > 0): ?>
      <div class="card cat-bar">
        <div class="top">
          <div class="name"><?= icon('more-horizontal', 18) ?> Unrecognised</div>
          <div><span class="amt"><?= $nUnknown ?> <?= $nUnknown === 1 ? 'entry' : 'entries' ?></span></div>
        </div>
        <div class="muted" style="font-size:11.5px; margin-top:6px;">
          Investments naming a type this ledger no longer has. They are invisible in every
          by-type view until you file them somewhere with <strong>Move entries</strong> above —
          "Unrecognised" is the first option in the From list.
        </div>
      </div>
    <?php endif; ?>
    <?= organiseTools('investment', 'type', 'target', 'money') ?>
    <?php
    $content = ob_get_clean();
    layout($db, $user, 'organise', $content, '/organise-invest');
}

// The "who?" select, shared by every add and edit dialog outside the Add tab (which has its
// own chips). A ledger with one member has nothing to choose, so this emits nothing at all
// and the entry simply carries no member — same as before sharing existed.
// Only the owner gets a select: they keep the books, so they may file an entry under any
// name in the household. Everyone else files as themselves, so they get a hidden field
// instead of a choice — attributableMember overrules whatever it holds. It keeps the id because the
// edit dialogs' JS writes the row's current member into it — an unchanged value is always
// accepted, so a member editing an amount cannot silently re-attribute the entry.
function memberSelect(array $mems, int $uid, string $role, string $id = '', ?int $selected = null): string {
    if (count($mems) < 2) return '';
    $own = 0;
    foreach ($mems as $m) if ((int)($m['user_id'] ?? 0) === $uid) $own = (int)$m['id'];
    if ($role !== ROLE_OWNER) {
        return '<input type="hidden" name="member_id"' . ($id !== '' ? ' id="' . h($id) . '"' : '')
             . ' value="' . $own . '">';
    }
    // Most of what you log is your own, so an add dialog opens on your name rather than on
    // "Anyone" — same default as the Add tab. Edit dialogs pass no $selected either, but their
    // JS writes the row's own member in before showing, so this never overrides an edit.
    if ($selected === null) $selected = $own;
    $out = '<select class="select" name="member_id"' . ($id !== '' ? ' id="' . h($id) . '"' : '') . '>'
         . '<option value="0">Anyone</option>';
    foreach ($mems as $m) {
        $mid = (int)$m['id'];
        $on  = ($selected !== null && $mid === $selected) ? ' selected' : '';
        $out .= '<option value="' . $mid . '"' . $on . '>' . h($m['label']) . '</option>';
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
//
// $tail rides along at the end of the same row (the Add button on Earn). It renders even when
// the pills don't, so a solo ledger still gets its action — hence the guards return the tail
// rather than an empty string, and role/aria only apply when there is a filter to describe.
function whoFilterRow(PDO $db, int $hid, array $mems, int $who, string $tail = ''): string {
    $bare = fn() => $tail === '' ? '' : '<div class="pill-row">' . $tail . '</div>';
    if (count($mems) < 2) return $bare();
    $n = $db->prepare("SELECT COUNT(*) FROM household_users WHERE household_id = ?");
    $n->execute([$hid]);
    if ((int)$n->fetchColumn() < 2) return $bare();

    $pills = '<button type="button" class="pill-btn' . ($who === 0 ? ' on' : '')
           . '" onclick="setWho(0)">All</button>';
    foreach ($mems as $m) {
        $id = (int)$m['id'];
        $pills .= '<button type="button" class="pill-btn' . ($id === $who ? ' on' : '')
                . '" onclick="setWho(' . $id . ')">' . h($m['label']) . '</button>';
    }
    return '<div class="pill-row" role="group" aria-label="Filter by person">' . $pills . $tail . '</div>';
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

    // Only the owner can mint one, so only the owner pays for the lookup. The Android build
    // turns sharing off entirely, and then there is nothing to mint — index.php 404s the
    // route, so the panel must not offer a button that leads there.
    $inv = null;
    if ($isOwner && FEATURE_SHARING) {
        // Seconds-left is subtracted here, in PHP. It used to be TIMESTAMPDIFF against MySQL's
        // NOW(), because expires_at was written by MySQL and PHP's clock sat in a different
        // timezone — the subtraction read 360 minutes for a 30-minute link. mintInvite() now
        // writes that column from PHP's clock, so both ends finally agree and SQLite, which has
        // no TIMESTAMPDIFF, needs no special case.
        $s = $db->prepare(
            "SELECT token, expires_at FROM invites
             WHERE household_id = ? AND used_at IS NULL AND expires_at > ? LIMIT 1"
        );
        $s->execute([$hid, nowSql()]);
        $inv = $s->fetch() ?: null;
        if ($inv) $inv['secs_left'] = max(0, strtotime((string)$inv['expires_at']) - time());
    }
    $full = count($people) >= HOUSEHOLD_USERS_MAX;
    $link = $inv ? originUrl() . '/join?t=' . $inv['token'] : '';
    $here = '';
    foreach ($mine as $l) if ((int)$l['id'] === $hid) $here = (string)$l['name'];

    ob_start();
    ?>
    <div class="month-switch">
      <a href="/#profile" class="btn btn-icon btn-back" aria-label="Back"><?= icon('chevron-left', 20) ?></a>
      <div class="label" style="font-size:16px;"><?= FEATURE_SHARING ? 'Ledgers &amp; sharing' : 'Ledger settings' ?></div>
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
      <?php /* Accounts, not people. Without sign-in there are none — one local user, no
               second device to let in — so the local build opens this card straight onto the
               names entries are actually filed under. */ ?>
      <?php if (FEATURE_SIGNIN): ?>
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
      <hr style="margin:12px 0 8px;">
      <?php endif; ?>

      <?php /* Adding and renaming are everyone's job — neither loses anything. Removing takes
               away a name the rest of the household files entries under, so that stays with
               the owner. The server enforces the same split; this only reflects it. */ ?>
        <h4 style="margin:0 0 2px;">Names on entries</h4>
        <div class="muted" style="font-size:12px; margin-bottom:8px;">
          What each person is called on an entry and on the filter.
          <?php if (FEATURE_SIGNIN): ?>
            Add a name for someone who does not sign in: a child, a parent, a shared card —
            rename those freely, entries stay attached to the person and not to the spelling.
            A name gets no access; only an invite does. Anyone who signs in brings their own
            name and reads as "Me" in their own login.
          <?php else: ?>
            Add one for everybody who spends: a child, a parent, a shared card. Rename them
            freely — entries stay attached to the person, not to the spelling.
          <?php endif; ?>
        </div>
        <?php foreach ($labels as $m): $linked = !empty($m['user_id']); ?>
          <div class="row-form" style="margin-bottom:6px; align-items:center;">
            <?php if ($linked): ?>
              <?php /* No rename box: a row with a login is labelled by its own account name,
                       and "Me" to the person themselves, so whatever were typed here would be
                       shown to nobody. See memberLabel(). */ ?>
              <div class="input" style="flex:1; display:flex; align-items:center;"><?= h(memberLabel($m, $uid)) ?></div>
              <span class="muted" style="font-size:11px; white-space:nowrap;" title="<?= h($m['user_name'] ?? '') ?>">signs in</span>
            <?php else: ?>
            <form method="post" action="/members/update" class="row-form" style="flex:1; margin:0;">
              <?= csrfInput() ?>
              <input type="hidden" name="back" value="/ledgers">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <input class="input" name="name" value="<?= h($m['name']) ?>" maxlength="60" required
                     aria-label="Name on entries">
              <button class="btn" type="submit">Save</button>
            </form>
            <?php if ($isOwner && $memberCount > 1): ?>
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

    <?php /* FEATURE_SHARING as well as $isOwner: index.php 404s /invite in the local build, so
             without this the page would still offer a "Create invite link" button that leads
             nowhere. There is no second device to hand a link to. */ ?>
    <?php if ($isOwner && FEATURE_SHARING): ?>
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
    <?php /* elseif, not else: with sharing off the owner is the only person here, and falling
             through to the branch below would offer them a "Leave this ledger" button for a
             ledger nobody else can hold. Local builds show neither card. */ ?>
    <?php elseif (FEATURE_SHARING): ?>
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

      <?php /* The same page describes two different apps, so it reads the flags that make them
               different rather than keeping a second copy of the file. On the phone there is no
               account, no server and nothing to sign in to: FEATURE_SIGNIN off IS the local
               build. Saying "stored in a MySQL database" there would be a lie about where a
               household's money lives, which is the one thing this page exists to get right. */ ?>
      <?php if (FEATURE_SIGNIN): ?>
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
      <?php else: ?>
      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">We have no access to your data</h3>
        <p style="margin:0; font-size:14px;">There is no account to create and no server behind this app.
          <strong>Everything you enter stays on this phone</strong>, in a single database file that only this app can
          open. We cannot read it, we never receive a copy of it, and there is nothing for us to hand over or lose.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">Your phone's lock is the lock</h3>
        <p style="margin:0; font-size:14px;">The app asks for your fingerprint, face or screen lock every time you
          open it, and again whenever you come back to it. That is Android's own check — the app never sees your PIN
          or your fingerprint, only whether the phone accepted it.</p>
      </div>

      <?php if (FEATURE_BACKUP): ?>
      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">Backups go to your Drive, not ours</h3>
        <p style="margin:0; font-size:14px;">If you turn on backup, the app copies that one database file into
          <strong>your own Google Drive</strong>, into a private folder only this app can see. It does not pass
          through us. Set a passphrase and the file is encrypted on this phone before it leaves, so not even Google
          can read it — but if you forget that passphrase, nobody can recover the backup, including us.</p>
      </div>
      <?php endif; ?>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">What leaves the phone</h3>
        <p style="margin:0; font-size:14px;">Nothing, unless you ask for it. No analytics, no advertising, no crash
          reporting, no third-party integrations. The <?= FEATURE_BACKUP ? 'only network request the app ever makes is
          the backup upload to your own Drive, if you switch it on' : 'app makes no network requests at all' ?>.</p>
      </div>

      <div>
        <h3 style="font-family:var(--font-heading); font-size:17px; margin: 0 0 6px;">If you lose the phone</h3>
        <p style="margin:0; font-size:14px;">The ledger goes with it. Uninstalling the app deletes the database, and a
          factory reset deletes it too. A backup is the only copy that survives, so if these entries matter, turn one
          on.</p>
      </div>
      <?php endif; ?>

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
