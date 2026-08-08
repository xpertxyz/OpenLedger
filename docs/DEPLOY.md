# Deploy checklist

Run this before every push to production: an automated preflight, an end-to-end run when auth or permissions changed, then a short manual eye-test.

## 1. Automated preflight (30 seconds)

Locally, with your production `.env` values loaded:

```bash
php index.php --preflight
```

Green means every automated check passed. Non-zero exit = at least one **FAIL** — do not deploy.

Checks:

**Code**
- PHP syntax on all source files
- The whole of `--selfcheck` — preflight shells out to it rather than keeping its own copy, with `zend.assertions=1` forced so a production `php.ini` can't silently compile the assertions away
- Every path a view links or posts to has a route, and no route is unreachable
- Every POST handler ends in `redirect`/`break`/`exit` (without one, PHP falls into the *next* case — usually a DELETE)
- Every `DELETE`/`UPDATE` in a POST handler is scoped by `household_id` (`users` and `households` excepted — both are addressed by an id the request bootstrap already checked)
- Every INSERT of an entry records `created_by`, and every single-row entry update/delete goes through `requireEditable()` — without both halves, sharing has no permission boundary
- Two handlers validating the same field accept the same values, and no form offers an option the server discards
- The POST switch sits behind the auth gate and exactly one `csrfCheck()`
- `redirect()` is the only thing that sends a `Location` header (everything else would bypass `safeRedirectTarget()`)
- Every configured limit is read, and every limit read is configured
- `INSERT`/`UPDATE` name only columns the schema actually has

**Schema** — derived from `lib.php`, not a hand-kept list, so a new migration verifies itself
- Every table, column and index in `SCHEMA_STATEMENTS` + `MIGRATIONS` exists in the live DB
- Everything a migration adds is also in its `CREATE TABLE`, so fresh installs match upgraded ones

**Data integrity**
- No category nested more than one level; no sub-category carries its own budget
- Every household has earning categories
- Every foreign id (`category_id`, `member_id`, `parent_id`, `recurring_id`) resolves to a row in the *same* household — catches both a dangling id and a cross-household leak
- Every `recurring.category_id` resolves in the table its `kind` implies
- Every `investments.type` name still exists in `investment_types`
- Every split bill records the date it started, keeps every posted share inside its own start–end window, and has posted no more shares than it has months — the three ways a split's replay can leave money in History that no plan accounts for
- Every membership points at a real user and a real ledger
- Every ledger has exactly one owner, and none exceeds the 10-person cap
- Every ledger's currency is a single symbol and its number style is one the app knows — an unknown style would make every amount silently fall back to Indian grouping
- Every claimed member label belongs to somebody actually in that ledger
- No live invite outlives its 30-minute window — this is the check that catches `expires_at` and `NOW()` disagreeing about the clock, which is how a 30-minute link once read as 360 minutes
- Every spent invite records who spent it

**Rendering** — all nine tabs rendered in-process against a real household
- No tab fatals
- No inline JS reaches for an element id the page never emits (a dead button)
- No page emits a duplicate id
- Every `<head>` is well-formed: one stylesheet link, one title, `viewport-fit=cover`, `theme-color`
- Every class the markup uses is styled somewhere
- A member who created nothing is offered no edit or delete control — the pages are rendered a second time as that member, and any surviving control would be an action the server refuses

**Style layers** — the shared stylesheet is linked by every page, so a rule added there changes all of them
- `design-tokens/styles.css` owns no `-webkit-tap-highlight-color` and exactly one universal (`box-sizing`) rule
- `layout()` still carries its five press-feedback rules, and `renderLanding()` its two

**Icons** — a name with no `<symbol>` renders a blank box, silently
- Literal `icon()` names, `DEFAULT_CATEGORIES` seeds, and every icon name stored in the DB are all in the sprite

**Environment**
- Config sanity (real Google client id set, APP_DEBUG off, DB password not empty)
- All static assets committed
- `.htaccess` denies dotfiles and raw `config.php` fetch
- Public pages: landing/sign-in structure, canonical, noindex pairing, status-bar repaint ordering
- Recurring sweep runs without exception

## 1b. Sharing end-to-end (only when auth or permissions changed)

```
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com APP_DEBUG=1 \
  php -S 127.0.0.1:8152 router.php &
bash tests/sharing-e2e.sh
```

Thirty-five assertions over real HTTP: invite minting and supersession, the 30-minute
window, single use, malformed and expired tokens, the 10-person cap, who may edit whose
entry, ledger switching, and the person filter. It writes and deletes rows in household 1
and refuses to run against anything but localhost. Preflight covers the invariants; this
covers the flow, which is the half a static check cannot see.

## 2. Manual eye-test (2 minutes after deploy)

Automated checks can't tell you whether the app *feels* right to a real user. Hit these in a fresh incognito window:

- [ ] **Landing page renders at `/`** — illustrations draw, theme button flips light/dark and survives a reload, all CTAs land on `/login`
- [ ] **`/robots.txt` and `/sitemap.xml`** — both 200, and the URLs inside start `https://` with the real domain (not `http://`, not localhost)
- [ ] **Sign-in card renders at `/login`** — Google button visible (real client id in prod), Terms link works even when signed out
- [ ] **Sign in with Google** — first-time users get a ledger named after them (first word of their Google name) + 13 categories + 6 investment types + a member label carrying their own name
- [ ] **Ledger name in the header** — the chip beside the theme icon shows a people icon on a shared ledger and a wallet on one only you can open
- [ ] **Invite someone** — Ledgers & sharing → Create invite link, send it to a second Google account on another device. They sign in, land in your ledger, and still have their own "Personal" one. Signing out and back in now shows them the ledger picker
- [ ] **The link really is one-shot** — open the same invite URL a second time: it must refuse. Leave one unopened for 31 minutes and it must refuse too (preflight proves the window; only a real wait proves the wall clock)
- [ ] **A member sees but cannot edit** — as the invited person, every row is visible and the totals match, but pencil and trash only appear on rows they added. Add one and confirm both appear on it
- [ ] **The owner can edit anything** — back on the owner account, the member's row has both controls
- [ ] **Filter by person** — the "Everyone" dropdown on History, Earn, Invest and Year; totals change with it, and it survives month and year navigation
- [ ] **Add expense** — amount + circular submit works, toast appears, entry shows on History
- [ ] **History** — day headers show per-day totals, category breakdown percentages sum to 100%, pagination "Older →" appears if >200 entries
- [ ] **Invest** — month grouping works, type breakdown renders with sage bars
- [ ] **Recurring — Expense kind** — set next_date to yesterday, reload any page, verify auto-post to History
- [ ] **Recurring — Investment kind** — same test, verify auto-post to Invest with the right type
- [ ] **Recurring — Split a bill** — split a total over 12 months dated a few months back: the preview shows the monthly share before saving, the past months appear in History straight away, and the row reads "Split · next … · last …"
- [ ] **Recurring — Edit a split** — reopen it, change the total and the start date, save. Every previously posted share is deleted and replayed: History should show the new amount, the new month range, and no leftovers from the old plan. Reopen once more and the dialog should show back exactly what you typed, not a rounded reconstruction of it
- [ ] **Edit** — click pencil on any row (History, Invest, Recurring), modal pre-fills, save reflects immediately
- [ ] **Delete + cascade** — delete a recurring item with the "also delete past entries" checkbox, verify auto-posted entries disappear
- [ ] **Profile drawer** — avatar opens right-side drawer, all three sections collapsible, rename category → reflects in Add / History
- [ ] **Currency and number grouping** — both live on Ledgers & sharing, under "How money is written", and belong to the ledger. As the owner, change the symbol and flip to 1,000,000, then check a figure over a lakh on Year or History. Switch to a ledger you only belong to: the controls are read-only there, and its own settings apply
- [ ] **Dark mode toggle** — sun/moon icon flips theme, persists across reload
- [ ] **Sign out confirm** — modal appears, Cancel keeps session, Confirm ends it
- [ ] **Unknown routes** — hit `/nope`: signed out it redirects to `/login`, signed in it 404s
- [ ] **Button press feedback, on a real phone** — tap a pill button on the landing page and a tab in the bottom nav: each should visibly react, and none should flash a square behind a rounded shape. Preflight asserts the CSS rules are present and in the right file, but `-webkit-tap-highlight-color` only exists on touch builds — desktop Chrome reports it as transparent no matter what, so this one cannot be automated

## 3. Ops confirmation

- [ ] Cron job installed: `0 3 * * * /usr/bin/php /path/to/index.php --cron`
- [ ] Latest DB backup ≤ 24h old
- [ ] Google Cloud Console has the production domain in "Authorised JavaScript origins"
- [ ] `APP_DEBUG` unset (or `0`) in production `.env`

## After a schema change

If you added/altered a table or column:

1. Bump the `SCHEMA_SENTINEL` constant in `lib.php` (e.g. `.schema-ok-v9` → `.schema-ok-v10`). Older sentinels in `data/` are deleted automatically on the next bootstrap.
2. On first request after deploy, migrations run. Verify by tailing the error log for any `[migrate]` messages.
3. Run `php index.php --preflight` again on the server to confirm every expected column landed.
