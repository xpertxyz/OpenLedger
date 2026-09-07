# Handoff: Home Expense Tracker

## Overview
A mobile-first web app for a household to log every expense (fast entry: amount → category → done), track investments, manage recurring bills (rent, EMIs, subscriptions), and view monthly spending history. Multiple household members share one ledger. Google sign-in gates access; light/dark theme is user-toggleable.

## About the Design Files
The file `Home Expense Tracker.dc.html` in this bundle is a **design reference** — an interactive HTML/React prototype built to show exact look, copy, states and behavior. It is not production code to copy verbatim (it uses a proprietary component-streaming format specific to the design tool it was built in). Your task is to **recreate this design in a real backend + frontend stack** (see "Suggested framework" below), reproducing the visual design exactly (colors, type, spacing, radii — all listed under Design Tokens) and the interactions described below.

You can open the `.dc.html` file directly in a browser to click through the live prototype.

## Fidelity
**High-fidelity.** Colors, type, spacing, radii and copy are final — implement them pixel-for-pixel using the tokens in `design-tokens/styles.css`. Layout uses a single centered column, max-width 480px, mobile-first (also usable at desktop width, just centered with a cream page background around it).

## Suggested framework (shared hosting: PHP/Laravel/WordPress/Node all available)
For an app this size (single form-driven CRUD ledger, a handful of tables, one login), avoid anything that needs a build pipeline or persistent process unless your host's Node setup is solid:

- **Recommended: PHP + SQLite, no framework or a micro-framework (Slim/plain PHP router).** Zero build step, drop-in via FTP/cPanel, SQLite needs no database server to provision. Fastest path to "it just works" on typical shared hosting. Add Laravel only if you want its structure/Eloquent/migrations and your host supports Composer + `artisan` — it's heavier than this app needs but very productive if the team already knows it.
- **Alternative: Node + Express + SQLite (better-sqlite3) or lowdb.** Only if your host's "Node.js Selector" (Passenger/LSAPI) reliably keeps a persistent process alive — some shared hosts throttle or sleep these. Use this if the team is more comfortable in JS end-to-end.
- **Skip WordPress** — it's built for content sites; forcing a transactional ledger app into post types/custom tables fights the platform more than it helps.

Either way: server-render the four screens (or a small React/Vue bundle if you prefer client-side state) and move the state described below into real database tables instead of `localStorage`.

## Screens / Views
All screens sit inside a single column, max-width 480px, centered, background `var(--color-bg)`. A header (title + theme toggle + settings gear + avatar/sign-out) is fixed at top; a 4-tab bottom nav (Add / History / Invest / Recurring) is fixed at bottom, 104px of bottom padding reserved in scroll content so it never hides content.

### 1. Sign-in gate
- **Purpose:** Gate the app behind Google auth.
- **Layout:** Centered card (`.card`, `elev-lg`, max-width 340px, padding `--space-6`), vertically/horizontally centered in the full-height column.
- **Components:** App name (Caprasimo, 24px) · one line of body copy · a full-width secondary button with the Google "G" logo (18px) + "Sign in with Google" (15px).
- **Behavior in prototype:** clicking the button sets a mock user (`{name:'You', email:'you@gmail.com'}`) — **replace with real Google Identity Services / OAuth 2.0**, verify the ID token server-side, create/find the user record, start a session.

### 2. Add (default landing tab)
- **Purpose:** The primary flow — log an expense in 3 taps.
- **Amount card:** `.card elev-sm`, centered text, "How much did you spend?" (13px muted) above a currency symbol (30px, Caprasimo, `--color-accent-700`) + a borderless numeric input (46px, Caprasimo).
- **Category grid:** 3-column grid of chips (icon 21px + 11.5px label), each a bordered rounded box (`--radius-md`). Selected = `--color-accent` border + `--color-accent-100` fill + `--color-accent-700` icon; unselected = `--color-divider` border + `--color-surface` fill. Last cell is a dashed "+ New" chip that reveals an inline text input + Add button to create a custom category (icon defaults to a tag icon, user-deletable later).
- **Member row** (only shown when >1 household member exists): pill buttons, selected = solid `--color-accent` fill with `--color-bg` text.
- **Note + date row:** a flexible text input ("Add a note (optional)") + a native date input, defaulting to today.
- **Primary button:** full-width "Add Expense", disabled until a valid amount is entered. On save: appends the entry, clears amount/note, resets date to today, shows a 1.8s pill toast ("Expense added") floating above the bottom nav.

### 3. History
- **Month switcher:** chevron-left / month-year label (e.g. "August 2026") / chevron-right (right arrow disabled once you're on the current month — no future months).
- **Total card:** full-bleed color card, background `--color-accent-700`, text `--color-bg` — total spent (32px Caprasimo) + entry count.
- **Category breakdown:** one row per category with an expense that month, sorted by amount descending: icon + name, amount + percentage on the right, an 8px pill progress bar underneath (fill width = % of month total, `--color-accent`).
- **Transaction list:** grouped by day (header e.g. "Mon, Aug 3"), each row a `.card elev-sm`: 36px icon circle (`--color-accent-100` bg), category name (14px/600), sub-label (note · member, 12px muted), amount (15px/600), trash icon button to delete.
- **Empty state:** centered copy, no chart/list shown, when the selected month has zero entries.

### 4. Investments
- **Total card:** same treatment as History's total card but `--color-accent-2-700` / sage, "Total invested".
- **Add flow:** a secondary full-width "+ Add investment" button reveals an inline card form: name text input, amount + type (`select`: SIP / Stocks / FD-RD / Gold / PPF-EPF / Other) side by side, a date input, then Cancel/Save buttons. Save disabled until name + valid amount are present.
- **List:** newest first, same row treatment as transactions but sage icon tint and a trending-up icon; shows "{type} · {date}"; trash to delete.
- **Empty state:** "Nothing logged yet" copy.

### 5. Recurring
- **Intro line:** "Rent, EMIs and subscriptions that repeat."
- **Add flow:** secondary full-width button reveals a form: name, amount + category `select` (options = the user's live category list), frequency `select` (Monthly/Quarterly/Yearly) + a next-due-date input, Cancel/Save.
- **List:** one `.card elev-sm` per recurring item: icon circle (repeat icon, accent tint), name, "{category} · {frequency} · next {date}", amount, trash to delete. **No manual "pay now" button** — see behavior below.
- **Empty state:** "No recurring expenses" copy.

### 6. Manage sheet (categories & members)
- Opened from the header gear icon. `.dialog-backdrop` + `.dialog`.
- **Categories:** all categories shown as `.tag.tag-neutral` chips; only user-created (custom) categories get a small "×" to delete — the built-in defaults are not deletable.
- **Household members:** list rows with a trash-adjacent "×" (disabled/hidden when only one member remains — you can't delete the last member), plus an add-member text input + button.
- **Done** button closes the sheet.

## Interactions & Behavior
- **Tab switching:** simple client-side state, no route change needed (or use real routes `/add`, `/history`, `/invest`, `/recurring` if you prefer server-rendered pages).
- **Recurring auto-posting — important business rule:** a recurring item does **not** get logged as an expense when it's created or edited. It only becomes an expense once its `next_date` has actually passed (today ≥ next_date) — matching the household's mental model that "the bill is a real expense on the day you actually pay it." In the prototype this is checked client-side on page load and every 60s (to catch midnight rollover while the tab stays open) and can loop forward through multiple missed periods. **In production, do this server-side on a scheduled job (cron / queue worker) that runs at least daily**: for every recurring row with `next_date <= today`, insert an expense dated `next_date`, advance `next_date` by the frequency, repeat until `next_date > today`.
- **Credit card bills:** there's no separate "credit card transaction" entity — a card bill is logged as one ordinary expense (amount + the "Credit Card Bill" category, or any category the user picks) on the day it's paid. This is intentional per the household's request; don't build a separate statement-import feature unless asked.
- **Theme toggle:** header sun/moon icon flips `darkMode`; persist per-device (e.g. a cookie/localStorage) or per-user-profile — your call, it's a personal display preference not shared ledger data.
- **Deletion:** every list row (expense/investment/recurring/category/member) deletes immediately on click of its trash/× icon — no confirm dialog in the prototype; add one if you want extra safety for destructive actions.
- **Validation:** Save buttons are disabled (not just no-op) until required fields are valid — amount > 0, and name non-empty where relevant.
- **Toast:** expense-added confirmation auto-dismisses after ~1.8s.

## State Management
Move this out of `localStorage` into real per-household tables:
- **users**: id, name, email, google_sub (from verified ID token), household_id
- **households**: id, name — the shared ledger boundary; all members of a household see the same data
- **members**: effectively `users` scoped to a household (or a separate `household_members` join table if a user can belong to multiple households — not needed for v1)
- **categories**: id, household_id, name, icon, is_custom (defaults are seeded per household, or global + a `custom` flag as in the prototype)
- **expenses**: id, household_id, amount, category_id, member_id (nullable/defaults to creator), note, date, created_at
- **investments**: id, household_id, name, amount, type (enum: SIP/Stocks/FD-RD/Gold/PPF-EPF/Other), date
- **recurring**: id, household_id, name, amount, category_id, frequency (monthly/quarterly/yearly), next_date

State transitions: creating/deleting rows in any of the above just does normal CRUD; the one non-trivial transition is the recurring → expense auto-post job described above.

## Design Tokens
All values live in `design-tokens/styles.css` (the actual stylesheet used by the prototype) — use its CSS custom properties directly, or port them into your stack's theme file (Tailwind config, SCSS variables, etc.) verbatim:
- **Colors:** `--color-bg` #f5ead8, `--color-surface` #ebddc5, `--color-text` #201e1d, `--color-accent` #c67139 (terracotta) and `--color-accent-2` #7a8a5e (sage), each with a 100–900 tonal ramp; full dark-mode override block is inlined in the prototype's logic (`THEME_DARK_VARS` — swaps all the same variable names to warm dark equivalents, never plain greys).
- **Type:** `--font-heading` = Caprasimo (display, all headings/amounts), `--font-body` = Figtree (everything else).
- **Spacing:** `--space-1` through `--space-8` (4.4px–35.2px scale).
- **Radius:** `--radius-sm` 8px / `--radius-md` 16px / `--radius-lg` 28px; buttons, tags, inputs are pill-shaped (999px) per the system's component CSS.
- **Shadows:** `--shadow-sm/md/lg`.
- See `design-tokens/organic-design-system-guide.md` for the full component class reference (`.btn`, `.card`, `.tag`, `.dialog`, `.input`, etc.) if you keep using plain CSS classes rather than a component library.

## Assets
- **Icons:** Lucide icon set (stroke-width 2.75), inlined as an SVG `<symbol>` sprite at the top of the prototype file — copy those `<symbol>` definitions directly, or pull the same icons from lucide.dev.
- **Google "G" logo:** inlined as an SVG symbol (`#icon-google`) using the standard 4-color Google mark.
- No photos/illustrations are used anywhere in this design.

## Files
- `Home Expense Tracker.dc.html` — the full interactive prototype (open in a browser to click through every screen/state).
- `design-tokens/styles.css` — the source-of-truth stylesheet for every color/type/spacing/radius/shadow value and component class.
- `design-tokens/organic-design-system-guide.md` — written guide to the design system's components and usage rules.

## Investment goals (`goals.php`)

A goal is a target and the assumptions that reach it. Everything else on the page is worked out
again on every render, so there is no derived state to go stale and nothing that can disagree
with anything else.

### What is stored, and what is not

Stored, in `goals`: the target, the corpus already held when tracking began, the monthly SIP and
its yearly step-up (January or April), three annual return rates, the "behind" band, the horizon,
which investment types count, and whose entries count. Two more tables hold the only facts the
app cannot work out: `goal_snapshots` (what the portfolio was worth on a day, typed in from the
broker) and `goal_milestones` (a row exists only to carry a custom rung or an achieved date set
by hand — a default rung that has neither has no row at all).

Not stored: every projected value, every milestone date, every status, every total. Change a
return rate and all of them move together.

### The engine

`goalProject()` returns one row per month for the whole horizon:

```
sip_i    = 0                                    before plan_start
         = monthly_sip × (1 + stepup)^k         after, k = step-up boundaries passed
value_i  = value_{i-1} × (1 + r/12) + sip_i     for each of low, base and high
```

It is a pure function of the goal row, which is what lets `goalsSelfcheck()` assert it against a
reference workbook — 240 rows, the month each milestone is reached on each path, both step-up
modes, the FY labels, the status bands. `--selfcheck` runs it, so the maths cannot drift during a
tidy-up. `goalShiftYm()` does the month arithmetic rather than `addMonths()`, which only steps
forward; the year rows need to look back eleven months to find where their period began.

### What counts as invested

"Actual in" and *Invested* are a `SUM` over `investments` since the goal's tracking start. The
ledger already records every rupee on the Invest tab, and a goal never asks for it a second time.
Two filters narrow it, both optional:

- **Types.** None ticked means every type. A ticked type brings its sub-types with it —
  `goalTypeNames()` expands a parent to its children before the query runs, so "Equity" counts
  the SIP and the Stocks filed under it. That is what a type already means everywhere else:
  `rollupTypes()` folds the same child into the same parent's bar on the Invest tab. Matching
  names literally would report a household behind on a goal it is meeting. The Assumptions card
  prints the expanded list rather than the ticked one, so the two cannot silently differ.
- **Person.** "Count only entries by" restricts the sum to one member, for a shared ledger where
  two people log SIPs side by side. It is a different question from "whose goal", which is only a
  label, and the dialog captions both because two member pickers in one form are otherwise
  indistinguishable.

Both "in" columns — planned and actual — are money added since tracking began. The starting
corpus is in neither: counting it on the planned side alone would print a shortfall that never
happened. It is where the *value* columns start instead, which is why the projection chart's
dashed baseline is labelled "Planned in + corpus".

### Status

`goalStatus()` judges a snapshot against the projection row for the month it is dated in, never
against today: below `band_low ×` the low path is *behind*, above the high path is *ahead*,
between them is *on track*. Judging against its own month is what lets an old snapshot keep the
verdict it earned, and what lets the year table carry a status per year. A snapshot more than 45
days old earns a *stale* tag — a status is only as current as the number behind it.

### Pages

`/goals` lists a card per goal. `/goals/{id}` is the dashboard: a KPI strip, the projection fan
(linear or log), planned-against-actual bars by month, the milestone ladder, a year table in
calendar or financial years, the snapshots, and the assumptions. `/goals/{id}/print` is the same
page without chrome. All the toggles are query parameters, so a link carries the view it was
shared from and the back button works.

Milestones default to a ladder (₹25 L … ₹10 Cr, capped at 1.5× the target) merged with any custom
rungs. Each row shows when the three paths reach it and when it was actually crossed — the first
snapshot at or above the amount, unless a date was set by hand — plus how many months that was
ahead of or behind the base path.

The year table takes December rows in calendar mode and March rows in financial mode. A snapshot
inside that month stands in for the year; failing that, the nearest one within ±45 days, marked
`~`. The year in progress shows what has been invested so far, marked, because its labelled month
has not happened yet; years that have not started show nothing.

### Charts

Inline SVG emitted by PHP, no library and no build step. The fan is a `low → high` polygon under
the base line, with milestone rules, the planned baseline, what the ledger actually recorded, a
dot per snapshot coloured by status and a rule at today. A `<title>` on every dot means the chart
still answers questions with JavaScript off; the optional crosshair adds a per-month readout, and
releases it on `touchend` because a finger has no `mouseleave`.

### Layout

Desktop-first, which is the one place this app is not phone-first: a KPI strip, a two-column grid
and wide tables need room, so `.col` widens to 1200px on these pages only. Under 720px it folds to
one column, tables scroll inside their cards, and the dialog drops from three fields per row to
two. The 240-month projection keeps its width and scrolls sideways — it starts at today, the end
that matters — while the 24-month bar chart shrinks to fit, since a chart whose only data sits
past the right edge reads as empty.
