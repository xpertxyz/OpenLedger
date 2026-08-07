# Deploy checklist

Run this before every push to production. Two steps: an automated preflight, then a short manual eye-test.

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
- Every `DELETE`/`UPDATE` in a POST handler is scoped by `household_id`
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

**Rendering** — all eight tabs rendered in-process against a real household
- No tab fatals
- No inline JS reaches for an element id the page never emits (a dead button)
- No page emits a duplicate id
- Every `<head>` is well-formed: one stylesheet link, one title, `viewport-fit=cover`, `theme-color`
- Every class the markup uses is styled somewhere

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

## 2. Manual eye-test (2 minutes after deploy)

Automated checks can't tell you whether the app *feels* right to a real user. Hit these in a fresh incognito window:

- [ ] **Landing page renders at `/`** — illustrations draw, theme button flips light/dark and survives a reload, all CTAs land on `/login`
- [ ] **`/robots.txt` and `/sitemap.xml`** — both 200, and the URLs inside start `https://` with the real domain (not `http://`, not localhost)
- [ ] **Sign-in card renders at `/login`** — Google button visible (real client id in prod), Terms link works even when signed out
- [ ] **Sign in with Google** — first-time users get a bootstrapped household + 13 categories + 6 investment types + "Me" member
- [ ] **Add expense** — amount + circular submit works, toast appears, entry shows on History
- [ ] **History** — day headers show per-day totals, category breakdown percentages sum to 100%, pagination "Older →" appears if >200 entries
- [ ] **Invest** — month grouping works, type breakdown renders with sage bars
- [ ] **Recurring — Expense kind** — set next_date to yesterday, reload any page, verify auto-post to History
- [ ] **Recurring — Investment kind** — same test, verify auto-post to Invest with the right type
- [ ] **Recurring — Split a bill** — split a total over 12 months dated a few months back: the preview shows the monthly share before saving, the past months appear in History straight away, and the row reads "Split · next … · last …"
- [ ] **Edit** — click pencil on any row (History, Invest, Recurring), modal pre-fills, save reflects immediately
- [ ] **Delete + cascade** — delete a recurring item with the "also delete past entries" checkbox, verify auto-posted entries disappear
- [ ] **Profile drawer** — avatar opens right-side drawer, all three sections collapsible, rename category → reflects in Add / History
- [ ] **Currency** — change symbol, verify it displays app-wide
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
