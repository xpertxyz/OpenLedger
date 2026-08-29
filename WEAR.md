# The watch app

A Wear OS app for checking today's spend and logging one in a few seconds, without taking a
phone out. Built for a Galaxy Watch 8 Classic; anything on Wear OS 3 or later will run it.

It reads one of **two** ledgers, chosen in its settings:

```
  Online   watch ──HTTPS──────────▶ ledger.xpertxyz.com   (MySQL)
  Phone    watch ──Data Layer─────▶ the phone app          (SQLite on the phone)
```

These are **separate ledgers, not two views of one**. The phone app has never synced anywhere:
it serves its own SQLite file to its own WebView over `127.0.0.1`. Switching modes changes
which numbers you see and moves nothing between them, and the settings screen says so.

**Online** needs no Bluetooth code at all — Wear OS proxies TCP through the paired phone
whenever the watch has no wifi or LTE, so plain HTTPS works out of range of a router.

**Phone** needs the Data Layer, because loopback does not leave a device. The watch sends a
`MessageClient.sendRequest` and `LedgerWearService` on the phone answers by running
`index.php --wear-summary` / `--wear-add` through `PhpServer.cli()` — the same route
`--backup` and `--restore` already take, so no server need be running and the app need not
even be open. The replies are the same JSON `api.php` returns over the network, from the same
`watchSummary()` and `createExpense()`, so every screen above `Api` is unaware of which
backend answered.

No token and no pairing in Phone mode, and none is wanted: the Data Layer only routes between
apps sharing a package name **and** a signing key, so the only thing that can reach the service
is our own watch app on that phone's own paired watch. A bearer token would guard a channel
that is already closed. The phone advertises `open_ledger_phone` (`res/values/wear.xml`) and
the watch resolves it as a capability rather than hardcoding a node — a watch outlives its
phone.

## What is on the watch

**The tile** — one swipe right from the watch face. Today's total, the month to date, and a
`+ Add` button that goes straight to the amount screen. This is the point of the whole thing:
opening an app on a watch takes long enough that a card machine times out first.

**The app** — today's spend, month to date against the household budget, the three biggest
categories with their bars, and the last three entries. Scrolls on the bezel.

**Adding** — amount, then category, and it is filed. The amount uses the system input picker,
so it is voice, the tiny keyboard or handwriting, whichever you already use. No note field:
the category is the label, and a second input screen on every add is the wrong trade on a
wrist. Notes get typed later, on the website.

**Out of range** — an add that cannot go out is kept and posted by a WorkManager job the next
time there is a network. The watch says "Saved · will sync when connected", because at a till
you need to know the number is recorded, not that a request failed.

## Pairing

Some devices cannot sign into Google in any way anyone would tolerate — a watch, a work laptop
you will not put a personal account on, a shared tablet. They pair with a six-digit code
instead. There are two kinds, and the difference is the whole safety of the thing:

| scope | who gets it | what it can do |
|---|---|---|
| `api` | a watch, via **Watch code** | `/api/*` only: read a summary, add an expense. Cannot open a session or reach a single HTML page. |
| `full` | a browser, via **Device code** at `/pair` | A signed-in session. Everything Google sign-in gives, including edit and delete. |

`mintDevicePairing()` defaults to `api` and rejects a scope it does not recognise, so a
forgotten argument or a typo'd form field cannot widen into full access. The scope is part of
the *lookup* in `redeemDevicePairing()`, not a check the caller makes afterwards — a watch code
typed into `/pair` finds no row at all, so it is refused **and survives unspent**. It used to be
claimed and then rejected, which burned the code and reported it as "wrong".

### Getting one

**A watch:**

1. Website → profile drawer → **Connected devices** → **Watch code**.
2. On the watch, open Open Ledger → **Enter code** → say or type it.
3. The watch trades the code for a token and keeps it. The code is spent.

The code travels **website → watch**, never the other way. A device that could mint its own
code and ask a signed-in human to approve it is the device-code phishing pattern — the
attacker's watch gets the ledger. This direction has no such shape: the code is born inside an
authenticated session and is worthless to anyone who cannot read the screen it is on.

**A browser** (laptop, tablet, second phone):

1. Website → profile drawer → **Connected devices** → **Device code**.
2. On the other device, open `/pair` and type the six digits.
3. It is signed in, as you, until you disconnect it.

### Revoking actually revokes

A paired browser session is only as alive as its row. `$_SESSION['device_id']` ties the two
together and `deviceSessionValid()` checks it on every request, so **Disconnect** signs a live
browser out on its very next page load — it does not merely stop future API calls and leave
the session running for another thirty days. The device lands on the sign-in page and is told
what happened. One indexed lookup, only for sessions that came from a code; a Google session
carries no `device_id` and is never touched.

### Why the code goes this way round

The code lasts ten minutes and works once. The token is scoped to one household and one user,
and resolves through `deviceFromToken()` to exactly the `(household, user, role)` a browser
request would carry — so a watch can do no more than the person who paired it. Disconnecting
one from the drawer kills it immediately; the watch notices on its next call, forgets what it
is holding and returns to the pairing screen.

An `api` token can read a summary and add an expense. It cannot edit, delete, invite, or change
a setting. That is not an oversight — it is what makes a credential sitting on a wrist a
reasonable thing to have.

## The API

Three endpoints in `api.php`, dispatched from `index.php` **before** `session_start()` — a
device authenticates on every request, so a session file and a `Set-Cookie` per poll would be
litter with no reader.

| | |
|---|---|
| `POST /api/pair` | `{code, label}` → `{token, ledger, currency, numfmt, categories}`. The only unauthenticated one, and it redeems `api` codes only. Rate limited to 10/hour/IP. |
| `GET /api/summary` | Everything a 1.5" screen holds, in one round trip. |
| `POST /api/expense` | `{amount, category_id, note, retry}` → `{ok}` **plus the fresh summary**, so an add never costs a second round trip over a Bluetooth proxy. |

Everything it writes goes through `createExpense()` in `lib.php` — the same function the web
form posts to. A second INSERT here is how the watch would end up exempt from the daily cap or
filing under the wrong member, so `--preflight` fails the build if `api.php` grows one.

### `retry`, and why it exists

The offline queue can only be flushed by retrying, and "the request was sent, the reply was
lost" is indistinguishable from "the request never left". So a flush could file the same coffee
twice. `PendingSync` sets `retry: true`; the server then refuses an identical
(date, amount, note, author) row from the last fifteen minutes. A first attempt never sets it,
which is what keeps two genuine coffees ten seconds apart as two rows.

### Authorization on shared hosting

A fair number of hosts run PHP through CGI or LiteSpeed, and those strip the `Authorization`
header before PHP sees it — producing a watch stuck on "Missing token" while sending one.
Two defences, because this is miserable to diagnose from a wrist:

- `.htaccess` passes it through as an environment variable (`E=HTTP_AUTHORIZATION`).
- `api.php` also accepts `X-Ledger-Token`, which nothing strips, and the watch sends both.

## Building and installing

The module is `android/wear`, alongside `android/app`. It shares the phone's `applicationId`
(`com.xpertxyz.ledger`) so Play can carry both in one listing under the Wear OS form factor —
it does **not** mean they talk to each other. Its `versionCode` is in its own range (10001+)
because Play scopes version codes to the whole app rather than to a form factor.

```bash
cd android
./gradlew :wear:assembleDebug          # or :wear:assembleRelease
./gradlew :wear:testDebugUnitTest      # the money formatter and the dictation parser
```

Point a build at a dev server instead of production:

```bash
./gradlew :wear:installDebug -PledgerUrl=http://192.168.1.5:8477
```

The address is compiled in, not typed on the watch — there is nowhere sensible to enter a URL
on a wrist and six digits have no room to carry one. Debug builds allow cleartext so a LAN
server works; release builds do not, at all.

### Onto a Galaxy Watch

The watch has no USB, and on Wear OS 5+ the old `adb connect <ip>:5555` does not work — port
5555 is closed and it uses the pairing-based Wireless debugging flow instead. What works:

1. On the watch: **Settings → About watch → Software → tap Software version 5 times**.
2. **Settings → Developer options → Wireless debugging**, on. Then **Pair new device**, which
   shows a six-digit code and a *pairing* port — a different number from the one on the main
   Wireless debugging screen.
3. Let adb find both ports itself rather than reading them off a watch face:

   ```bash
   adb mdns services          # _adb-tls-pairing._tcp and _adb-tls-connect._tcp, with ports
   adb pair 192.168.1.10:<pairing-port> <code>
   adb connect 192.168.1.10:<connect-port>
   ```

4. `./gradlew :wear:installDebug`

Pairing persists; the ports do not. They are regenerated every time the transport drops, so
re-run `adb mdns services` rather than reusing the last number that worked.

### It keeps disconnecting

Wear OS turns Wi-Fi **off** whenever Bluetooth to the phone is up, to save battery — which
takes adb with it, mid-session, repeatedly. The watch stays in the Mac's ARP table looking
present and answers nothing.

**Put it on the charger.** Wear keeps Wi-Fi up while charging, and the transport stays stable.
Turning Bluetooth off on the watch works too, at the cost of the thing you are usually
testing.

The same behaviour matters for pointing a build at a dev server: with Wi-Fi off the watch
reaches the network by proxying through the phone, so a LAN address like `192.168.1.5` only
resolves if the *phone* is on that LAN. Testing against the deployed site avoids the whole
question, and is the configuration the watch is actually worn in.

Then add the tile: long-press the watch face → **Tiles** → **+** → Open Ledger.

The debug APK is signed with the debug key and the Play build is not, so the two cannot replace
each other on the same device — but they are on different devices here, and the watch app
shares no data with the phone app, so nothing collides.

## Deploying the server side

The API needs three things live on the website:

- `api.php` (new file — it is in the deploy and in the phone app's assets, so the two copies of
  the app cannot drift).
- The `.htaccess` change, for the `Authorization` passthrough.
- `device_tokens`. `SCHEMA_SENTINEL` moved to `v21`, so the first request after deploy creates
  it. Nothing to run by hand. (A `MIGRATIONS` line adds `scope` to any database that saw the
  table land one deploy before the column did — harmless on a fresh one, where
  `SCHEMA_STATEMENTS` already includes it.)

Gates, as ever:

```bash
php index.php --preflight
DB_DRIVER=sqlite DB_PATH=/tmp/t.db php index.php --preflight
php tests/dual-driver.php                                     # and again under sqlite
```

`--preflight` now also checks that every `/api/` endpoint but `/api/pair` sits behind the
bearer check; that `api.php` never logs a token or emits markup and writes expenses through
`createExpense()`; that pairing defaults to the narrow scope in both the signature and the
column; that `/pair` refuses a code not minted for a full session; and that a disconnected
device loses its browser session.

## Play

The watch APK goes into the **same release** as the phone AAB, under the Wear OS form factor.
Same package name, same Play app-signing key, different version code. It declares
`android.hardware.type.watch`, so Play will never offer it to a phone, and
`standalone=false`, truthfully — it is useless without a network, which on a Bluetooth-only
watch means without the phone.

## On the watch face

Two **complications**, not a watch face of our own:

- **Spent today** — short text, `₹460`
- **Investment target** — ranged value, an arc plus `62%` of this month's target

Add them the way you add any complication: long-press the watch face, Customise, tap a slot,
pick Open Ledger. Tapping either opens the app.

Complications rather than a bespoke face on purpose. Steps and battery already exist as
complications on every face Samsung ships, so a face of ours would mean reimplementing two
things the system does better — and forcing one design on someone who already chose theirs.
This way the ledger sits on *their* face, beside their step count.

Both read the cached summary and never fetch: a complication is refreshed by the system on a
hard deadline that a Bluetooth-proxied request would miss. `LedgerComplication.refresh()`
pushes a new value whenever one lands, and the 30-minute `UPDATE_PERIOD_SECONDS` is only the
floor under a watch nobody has touched.

## Signing out, and switching ledgers

Settings is at the foot of the ledger screen, and also on the pairing screen — without that
second entry point a watch that has never paired online could never reach the setting that
lets it read the phone instead.

**Sign out** clears the token, the cached summary and anything still queued. It keeps the mode,
which is a preference rather than a credential. In Phone mode there is nothing to sign out of.

## Deliberately not built

- **A complication** (today's total as a field on the watch face). Cheap to add now that the
  data sync exists; the tile covers the same need one swipe away.
- **Editing or deleting from the watch.** A wrist is for capture. Corrections want a screen.
- **A note field.** See above.
- **A watch face of our own.** See above — complications get the numbers onto the face the
  wearer already likes, next to the step count we would otherwise have to reimplement.
- **Merging the two ledgers.** Online and Phone stay separate. Syncing a local SQLite ledger
  into a shared MySQL one is a whole product, not a setting.
