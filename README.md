<p align="center">
  <img src="assets/logo/og-image.png" alt="Home Ledger" width="640">
</p>

<h1 align="center">Home Ledger</h1>

<p align="center">
  A warm, mobile-first household ledger — expenses, investments, recurring bills — for one shared household.<br>
  PHP + MySQL + Google One Tap sign-in. No build step. Deploys to any shared host.
</p>

<p align="center">
  <a href="https://home.xpertxyz.com"><strong>🌐 Try it live at home.xpertxyz.com</strong></a>
</p>

<p align="center">
  <a href="#quick-start">Quick start</a> ·
  <a href="#self-hosting">Self-hosting</a> ·
  <a href="#local-development">Local development</a> ·
  <a href="#features">Features</a> ·
  <a href="#architecture">Architecture</a> ·
  <a href="docs/DESIGN.md">Design spec</a>
</p>

> **Live instance disclaimer.** [home.xpertxyz.com](https://home.xpertxyz.com) is a real, running deployment you're welcome to use. **Data is not encrypted at rest** — see [`/terms`](https://home.xpertxyz.com/terms) inside the app. If that isn't OK for you, self-host in ten minutes with the [Quick start](#quick-start) below.

---

## Features

- **Add expense in three taps** — amount, category, done.
- **History** grouped by day with per-day totals + monthly category breakdown.
- **Investments** grouped by month with per-type breakdown (SIP, Stocks, FD-RD, Gold, PPF-EPF, and any custom types you add).
- **Recurring items** that auto-post on their due date — for both expenses (rent, EMIs, subscriptions) and investments (SIPs, RDs, auto-debits). Cascade delete of past auto-entries is optional per item.
- **Household sharing** — multiple members share one ledger. Attribute each expense to a member.
- **Editable everything** — every list row opens an inline edit modal. Rename categories and investment types anywhere and it reflects app-wide.
- **Google One Tap sign-in** — no passwords, no signup form, no account management surface.
- **Warm light + dark themes**, per-user, persisted.
- **Any currency symbol** — free text, per user.
- **CSRF-guarded**, rate-limited (10 sign-ins / 15 min, 60 POSTs / min per IP), data-caps enforced server-side.
- **Pagination** on history + investments, with SQL-side aggregates so month/all-time totals stay cheap.
- **No build step, no npm, no composer** — just PHP + MySQL + a stylesheet.

## Screens

<table>
  <tr>
    <td align="center"><img src="docs/screenshots/signin.png" alt="Sign-in" width="240"><br><sub><b>Sign in with Google</b></sub></td>
    <td align="center"><img src="docs/screenshots/add.png" alt="Add expense" width="240"><br><sub><b>Add an expense</b></sub></td>
    <td align="center"><img src="docs/screenshots/history.png" alt="History" width="240"><br><sub><b>History &amp; breakdown</b></sub></td>
  </tr>
  <tr>
    <td align="center"><img src="docs/screenshots/invest.png" alt="Invest" width="240"><br><sub><b>Investments</b></sub></td>
    <td align="center"><img src="docs/screenshots/recurring.png" alt="Recurring" width="240"><br><sub><b>Recurring (expense &amp; investment)</b></sub></td>
    <td align="center"><img src="docs/screenshots/profile.png" alt="Profile drawer" width="240"><br><sub><b>Profile drawer</b></sub></td>
  </tr>
</table>

---

## Quick start

The fastest path if you already have PHP 8.1+ and a MySQL/MariaDB running locally.

```bash
git clone https://github.com/xpertxyz/HomeLedger
cd HomeLedger

# 1) Create the database (empty; schema self-installs on first request)
mysql -u root -e "CREATE DATABASE homeledger CHARACTER SET utf8mb4;
                  CREATE USER 'homeledger'@'localhost' IDENTIFIED BY 'homeledger';
                  GRANT ALL PRIVILEGES ON homeledger.* TO 'homeledger'@'localhost';"

# 2) Point config at it
cp .env.example .env
# then edit .env — for local, set:
#   DB_HOST=127.0.0.1
#   DB_NAME=homeledger
#   DB_USER=homeledger
#   DB_PASS=homeledger
#   APP_DEBUG=1                 # enables dev-stub sign-in (skip Google OAuth locally)
#   GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com   # any placeholder is fine for dev

# 3) Serve
php -S 127.0.0.1:8152 router.php
```

Open <http://127.0.0.1:8152/> and click **Continue as dev user**.

> The dev-stub sign-in only activates when *both* `APP_DEBUG=1` **and** the Google client id is still the placeholder. Setting a real client id disables the stub — the way it should be in production.

---

## Self-hosting

Home Ledger is designed for typical PHP shared hosts (Hostinger, cPanel, Bluehost, DreamHost, etc). You need:

- PHP **8.1+** with PDO/MySQL enabled (default on all modern hosts)
- **MySQL 5.7+** or **MariaDB 10.3+**
- Apache with `mod_rewrite` (or LiteSpeed) — `.htaccess` is committed
- The ability to add a **cron job** (once daily is enough)
- A domain, and access to **Google Cloud Console** to create an OAuth client

### 1. Create a Google OAuth client

1. Visit <https://console.cloud.google.com/apis/credentials>.
2. Create Project → **Credentials** → **Create Credentials → OAuth 2.0 Client ID**.
3. Application type: **Web application**.
4. **Authorised JavaScript origins** — add your deploy URL (e.g. `https://ledger.example.com`) plus `http://localhost:8152` for local dev if you'll test that way.
5. No client secret is used (Google Identity Services doesn't need it for ID token flow). Copy the **Client ID** (looks like `1234567890-abcdef.apps.googleusercontent.com`).

### 2. Provision the MySQL database

In your host's control panel (Hostinger: hPanel → Databases → MySQL Databases), create:
- An empty database (e.g. `u123_ledger`)
- A user with all privileges on that database
- Note the host (usually `localhost` on shared hosting)

**No schema import needed.** The app creates its tables on the first request via `CREATE TABLE IF NOT EXISTS`.

### 3. Deploy the code

**Option A — Git deploy (recommended)**
Most hosts support automatic git deploys. Point their integration at your fork of this repo. Every push pulls into `public_html/`.

**Option B — SFTP**
Upload everything to `public_html/` except:
- `.env` (create on server — see step 4)
- `data/` (created automatically)
- `docs/` (optional — only if you want the design spec on the server)
- `router.php` (only needed for `php -S` local dev)

### 4. Create `.env` on the server

In your `public_html/` root:

```
DB_HOST=localhost
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_db_password

GOOGLE_CLIENT_ID=1234567890-abcdef.apps.googleusercontent.com

# Leave APP_DEBUG unset (or 0) in production.
```

`chmod 600 .env` for good measure. The `.htaccess` already denies web access to dotfiles.

### 5. Add a daily cron job

For recurring bills/SIPs to auto-post reliably, run:

```
0 3 * * * /usr/bin/php /home/USER/public_html/index.php --cron
```

(Substitute your actual PHP path and web root. On Hostinger: hPanel → Advanced → Cron Jobs.)

The cron job:
- Sweeps only households with **due** recurring items (indexed query, cheap even at scale)
- Cascades through missed periods safely (capped at 120 iterations per item)
- Garbage-collects the `rate_limits` table

The sweep also runs opportunistically on every authed request as a fallback — the cron is belt-and-braces.

### 6. Visit the site

Hit your domain. The schema installs on the first request. Sign in with Google. Done.

---

## Local development

```bash
# From the repo root
php -S 127.0.0.1:8152 router.php
```

- `router.php` is a 4-line front controller for PHP's built-in server (serves static files as-is, delegates everything else to `index.php`). Only used locally — on real hosts, `.htaccess` handles this.
- Set `APP_DEBUG=1` in `.env` to make PHP errors show on-page (never do this in production).
- Self-check the CLI logic: `php index.php --selfcheck`
- Dry-run the cron sweep: `php index.php --cron`

To reset the DB during dev, delete the schema sentinel and drop the database:
```bash
rm -f data/.schema-ok-v5
mysql -u root -e "DROP DATABASE homeledger; CREATE DATABASE homeledger CHARACTER SET utf8mb4;"
# Next page load runs the fresh schema
```

---

## Architecture

Small, single-language, single-file-ish. The whole app is five PHP files.

| File | Role |
|---|---|
| `index.php` | Front controller — routes, POST handlers, CLI modes (`--cron`, `--selfcheck`). |
| `lib.php` | Schema, migrations, DB bootstrap, session/CSRF helpers, rate limiter, Google ID-token verifier, recurring sweep. |
| `views.php` | All rendering — layout wrapper, per-page render functions, right-side profile drawer, shared confirm & edit modals. |
| `config.php` | `.env` loader + config array. User-editable. |
| `router.php` | Local-dev front controller for `php -S`. Not used on real hosts. |
| `.htaccess` | Rewrites everything through `index.php`, denies raw access to internals, sets security headers. |
| `design-tokens/styles.css` | The Organic design system — colours, type, spacing, radii, shadow tokens + component classes (`.btn`, `.card`, `.tag`, `.dialog`, `.input`). Linked directly, never rebuilt. |
| `assets/icons/`, `assets/logo/` | Standalone SVG icons (Lucide) + brand mark, wordmark, OG image, favicon. |

### Data model

```
households ─┬─ users        (Google-authenticated, one per person)
            ├─ members      (attributable spender per entry)
            ├─ categories   (expense categorization; defaults + custom)
            ├─ investment_types  (SIP, Stocks, ...; editable)
            ├─ expenses     (fact table, indexed on (household_id, date))
            ├─ investments  (fact table, indexed on household_id)
            └─ recurring    (kind: 'expense' | 'investment' — auto-posts to the right table)
rate_limits (bucket, hits, window_end)  — GC'd by cron
```

Expenses and investments carry a nullable `recurring_id` FK so cascade delete of a recurring item can optionally sweep every auto-posted entry it created.

### Security

- **Auth**: Google Identity Services (One Tap + fallback button). ID token verified server-side against Google's `tokeninfo` endpoint (audience, issuer, expiry, `email_verified` checked).
- **Sessions**: `HttpOnly` + `SameSite=Lax` + `Secure` (on HTTPS) cookies, `session_regenerate_id(true)` on login, `use_only_cookies` + `use_strict_mode`.
- **CSRF**: Session-scoped token on every POST form (`hash_equals` compared). Google's own `g_csrf_token` double-submit for the sign-in callback.
- **Rate limiting**: Atomic per-IP upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) — 10 sign-ins / 15 min, 60 POSTs / min. Returns `429` with `Retry-After`.
- **Data caps**: Amounts, note lengths, per-household counts (200 expenses/day, 1000 investments, 100 recurring, 100 categories, 20 members) enforced at insert time.
- **SQL**: PDO prepared statements with `EMULATE_PREPARES=false`.
- **XSS**: `htmlspecialchars` on every output; JSON payloads inside `onclick=""` attributes go through both `json_encode` and `h()`.

Full audit and disclosures live in `docs/DESIGN.md` and the `/terms` route in-app.

### What's intentionally missing

Home Ledger is **not** encrypted at rest. Anyone with database access can read your entries. Self-host if that isn't OK.

No analytics, no third-party trackers, no ad SDKs. The only outbound request the app makes is to Google's `tokeninfo` endpoint at sign-in time.

---

## Contributing

Fork, PR. Keep the ponytail — new features prefer the simplest thing that works. Do not add build steps, package managers, or dependencies unless the alternative is worse.

Coding style:
- No frameworks. Plain PHP with `<?php ?>` templates in `views.php`.
- One file per concern (rendering, routing, helpers).
- Add server-side validation for every user input at the trust boundary.
- Add a small `?selfcheck` assertion if a helper has non-trivial branching.

---

## License

MIT. See `LICENSE` (add one if forking).

---

<p align="center">
  Built by <a href="https://xpertxyz.in"><strong>XpertXYZ</strong></a> — digital solutions across platforms.<br>
  <sub>Live at <a href="https://home.xpertxyz.com">home.xpertxyz.com</a> · Source at <a href="https://github.com/xpertxyz/HomeLedger">github.com/xpertxyz/HomeLedger</a></sub>
</p>
