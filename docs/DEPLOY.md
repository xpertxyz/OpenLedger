# Deploy checklist

Run this before every push to production. Two steps: an automated preflight, then a short manual eye-test.

## 1. Automated preflight (30 seconds)

Locally, with your production `.env` values loaded:

```bash
php index.php --preflight
```

Green means every automated check passed. Non-zero exit = at least one **FAIL** — do not deploy.

Checks:
- PHP syntax on all source files
- Date math + input validators (parseAmount edge cases)
- DB connection with configured credentials
- All 9 expected tables present
- Every migrated column present (recurring.kind, recurring.type, expenses.recurring_id, investments.recurring_id, users.currency)
- Config sanity (real Google client id set, APP_DEBUG off, DB password not empty)
- All static assets committed
- .htaccess denies dotfiles and raw config.php fetch
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
- [ ] **Edit** — click pencil on any row (History, Invest, Recurring), modal pre-fills, save reflects immediately
- [ ] **Delete + cascade** — delete a recurring item with the "also delete past entries" checkbox, verify auto-posted entries disappear
- [ ] **Profile drawer** — avatar opens right-side drawer, all three sections collapsible, rename category → reflects in Add / History
- [ ] **Currency** — change symbol, verify it displays app-wide
- [ ] **Dark mode toggle** — sun/moon icon flips theme, persists across reload
- [ ] **Sign out confirm** — modal appears, Cancel keeps session, Confirm ends it
- [ ] **Unknown routes** — hit `/nope`: signed out it redirects to `/login`, signed in it 404s

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
