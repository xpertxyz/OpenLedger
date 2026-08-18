# Open Ledger on Android

The same PHP that runs the website runs on the phone. There is no second implementation of the
ledger, no rewrite, and no API between them — the Android app bundles a PHP interpreter, serves
`index.php` to itself over loopback, and shows the result in a WebView.

The data lives in one SQLite file inside the app's private storage. Nothing is sent anywhere
unless the user turns on Drive backup, and then it goes to their own Drive.

---

## How it fits together

```
  MainActivity ──> WebView ──http://127.0.0.1:<random>──> libphp.so (php -S)
                                                              │
                                                        index.php / lib.php / views.php
                                                              │
                                                     filesDir/ledger.db  (SQLite)
```

| Piece | Where |
|---|---|
| PHP interpreter | `android/app/src/main/jniLibs/arm64-v8a/libphp.so` (built, not committed) |
| The app's PHP | copied into assets from the repo root at build time |
| Database | `filesDir/ledger.db` |
| Configuration | process environment, set by `PhpServer.kt` |

---

## Build

### 1. The interpreter

```bash
./android/build-php.sh
```

Downloads SQLite and PHP, cross-compiles both with the NDK, and writes
`app/src/main/jniLibs/arm64-v8a/libphp.so` — **7.5 MB**, giving a **10 MB** APK. Takes several
minutes; only needs rerunning when the PHP version changes.

Five things in that script are not obvious, and each one was a failed build before it was a
line of script:

- **The binary is named `libphp.so` even though it is an executable, not a library.** Since
  Android 10, an app may not execute anything from its writable data directory (W^X). The one
  place that stays executable is the extracted native-library directory, and the packager only
  extracts files matching `lib*.so`. Hence the name, and hence `android:extractNativeLibs="true"`
  in the manifest — set it false and the binary stays compressed inside the APK and cannot run.
- **SQLite has to be cross-compiled first.** PHP has not bundled a copy since 7.4, so
  `--with-pdo-sqlite` fails late with `sqlite3.h file not found` unless a target-built
  `libsqlite3.a` already exists.
- **DNS is compiled out by patching `main/php_config.h` after configure.** Android's bionic libc
  omits the BIND resolver API, so `ext/standard/dns.c` will not compile. `config.cache` cannot
  fix it because PHP's own `PHP_CHECK_FUNC` macro `unset`s those cache variables before testing
  — the generated header is the only place left to say no. The app resolves no hostnames.
- **`getdtablesize()` does not exist in bionic** and is not merely hidden — it is absent from the
  NDK headers. PHP reaches for it under `#ifdef HAVE_UNISTD_H`, which is true on Android and
  still wrong. Patched to `sysconf(_SC_OPEN_MAX)`, which answers the same question.
- **`mblen()` is missing too**, and only shows up at link time. `mbrlen(ptr, len, NULL)` is the
  same function with an internal static state — which is what PHP already uses on its
  `_REENTRANT` path a few lines above the one that breaks.
- **mbregex is disabled** because it needs oniguruma; the app only calls `mb_strlen`/`mb_substr`.

Extensions built: `pdo`, `pdo_sqlite`, `sqlite3`, `mbstring` (no mbregex — the app only calls
`mb_strlen`/`mb_substr`), `session`, `filter`, `ctype`, `tokenizer`, `hash`, `pcre`.
No OpenSSL: the local build has Google sign-in off, so nothing verifies an ID token.

### 2. The APK

```bash
cd android && gradle assembleDebug      # testing
cd android && gradle bundleRelease      # what goes to Play
```

### Size

| | |
|---|---|
| Debug APK | 10 MB — unminified, for testing only |
| **Release APK** | **4.7 MB** — arm64, what a user downloads |
| Release AAB | 5.5 MB — the upload artifact, not a download |
| Installed on device | ~12 MB |

Installed is larger than downloaded because `useLegacyPackaging = true` extracts the interpreter
uncompressed so it can be executed — that is the same requirement that forces the `libphp.so`
name, not waste.

Inside the release APK, uncompressed:

| | |
|---|---|
| PHP interpreter | 7.5 MB |
| dex (Kotlin + Drive libraries) | 2.3 MB |
| the app itself (PHP + CSS) | 547 KB |
| resources | 214 KB |

R8 takes the dex from **14.2 MB to 2.3 MB** — the Drive client libraries are enormous and this
app calls a sliver of them. That is the entire difference between the debug and release builds;
the interpreter cannot shrink further without dropping extensions the app uses.

`proguard-rules.pro` keeps back what R8 cannot see being used: the API client's `@Key`-annotated
model fields, WorkManager's reflective worker construction, and `BackupBridge`'s
`@JavascriptInterface` methods. Without the last one the backup panel's buttons would work
perfectly in debug and silently do nothing in release.

Gradle copies `index.php`, `lib.php`, `views.php`, `config.php`, `router.php`,
`design-tokens/` and `assets/` out of the repo root into the APK assets. **It deliberately
excludes `.env`** — that file holds the server's MySQL credentials and must never reach a phone.

---

## Configuration

`config.php` already prefers real environment variables over `.env`. The Android launcher
therefore configures the app exactly the way a production host does, and **no PHP file is
written on the device**:

| Variable | Value on Android | Effect |
|---|---|---|
| `DB_DRIVER` | `sqlite` | one local file instead of MySQL |
| `DB_PATH` | `filesDir/ledger.db` | app-private storage |
| `APP_TZ` | the device's zone | every date the app computes |
| `HL_LOCAL_TOKEN` | random per launch | loopback guard, below |
| `HL_SHARING` | `0` | invite/join routes return 404 |
| `HL_GOOGLE_SIGNIN` | `0` | no login screen; one local user |

`HL_SHARING` and `HL_GOOGLE_SIGNIN` are compiled in via `BuildConfig`, not read from a runtime
file or a remote switch. An app whose behaviour changes after review is the pattern that gets
it pulled from Play.

There was a second "connected" build flavour for an app talking to the hosted ledger. It was
deleted rather than tested, because it could not have worked: the bundled PHP is built
`--disable-all` with no OpenSSL, so it has no `https://` stream wrapper, and
`verifyGoogleIdToken()` fetches Google's tokeninfo endpoint over HTTPS. Enabling sign-in would
have produced a login screen that can never succeed, in a build still serving the local SQLite
file. A genuinely connected app is a WebView pointed at the website — different code, worth
writing when it is actually wanted.

---

## Four things that are easy to get wrong

### Never touch a socket on the main thread

`PhpServer.start()` waits for the interpreter by connecting to its port. Called from
`onCreate`, that connect throws `NetworkOnMainThreadException` — and a `runCatching` around
the probe swallows it, so the wait looks exactly like "not up yet" and runs until it times
out. The app crashed on launch with `php did not start listening`, while `php.log` showed the
server had been listening the whole time.

`startAsync()` exists for this: the interpreter starts on a worker thread and the WebView is
pointed at it from a callback on the main thread.

### Cleartext has to be allowed for 127.0.0.1

`usesCleartextTraffic="false"` blocks `http://127.0.0.1` too, and the WebView fails with
`ERR_CLEARTEXT_NOT_PERMITTED`. The fix is not to flip that flag globally — it is
`network_security_config.xml`, which keeps cleartext off everywhere and carves out loopback
only. Loopback never leaves the device; the Drive upload stays HTTPS-only.

### Loopback is not private

Any other app on the phone can open a socket to `127.0.0.1:<port>` and would be answered with
the household's entire ledger. So:

- the launcher generates a random token per process and passes it as `HL_LOCAL_TOKEN`
- it sets that token as a cookie on `http://127.0.0.1:<port>` **before the first page load**
- `index.php` rejects any request that cannot present it, before the session starts and before
  the database is opened

No other app can read this app's cookie jar, and the token is 24 random bytes.

### Never copy a live SQLite file

Copying `ledger.db` while the app is running captures a torn database plus a hot `-wal`, and it
restores as corruption. The backup path uses `VACUUM INTO`, which is one statement and always
produces a settled, consistent file:

```bash
php index.php --backup /path/to/snapshot.db
```

That mode refuses to overwrite an existing file and verifies `PRAGMA integrity_check` before
reporting success.

---

## Google Drive backup

Use the **`drive.appdata` scope only**. `drive` and `drive.readonly` are Google *restricted*
scopes: shipping either means an annual CASA security assessment — real money, weeks of process.
`appdata` is not restricted, is invisible in the user's Drive UI, and can only see files this
app created.

One rolling `openledger-backup.db.gz` is kept, replaced on each run. Not a dated history:
keeping N copies of a financial database in someone else's cloud is a bigger promise than this
app should make, and Drive keeps its own revisions of a replaced file anyway.

### Passphrase encryption

"We never see your data" is true — there is no server. But Google can read an unencrypted
backup, so the claim is only unconditional with a passphrase set. It is opt-in, from the same
panel, and off by default.

    "OLB1" | salt (16) | iv (12) | AES-GCM ciphertext+tag

PBKDF2-HMAC-SHA256, 210,000 iterations, 256-bit key. Compression happens **before** encryption —
ciphertext does not compress, so the other order would upload the full-size database.

Two decisions worth keeping:

- **The salt travels inside the blob**, not in local settings. The entire reason to hold a
  backup off-device is restoring onto a phone that has lost everything else, and that phone has
  no settings to read. Given the file and the passphrase, the key re-derives from nothing else.
  `BackupCryptoTest.saltTravelsInTheBlobSoAnotherDeviceCanOpenIt` is that scenario as a test.
- **The derived key lives in the Android Keystore, the passphrase is never stored.** A scheduled
  backup runs with nobody watching and cannot prompt, so the key has to persist; putting it in
  the Keystore keeps it out of app storage and usually inside hardware. The consequence is real
  and the UI says so before enabling: forget the passphrase and the Drive copy is gone. There is
  no recovery path because nobody else holds the key — that is the feature, not a gap.

GCM authenticates, so a wrong passphrase fails cleanly rather than producing a plausible-but-
wrong database, and the half-written plaintext is deleted.

Run the tests (they need a device — the Keystore has no JVM equivalent):

```bash
cd android && gradle connectedDebugAndroidTest
```

---

## Recurring items need no background job

`sweepRecurring()` runs on every authenticated request, and `MainActivity.onResume()` restarts
the server — so the first request after the app opens posts everything overdue. A phone that
was off for a month files that month on launch and then stops.

No cron, no WorkManager, no foreground service. This is the one place where Android is
*simpler* than the server deployment, which does need a cron job.

---

## Updates ship through Play

The app does **not** fetch code from git at runtime. That was considered and rejected:

- it is a channel for running new code on a device holding a household's finances, which
  contradicts the entire privacy claim
- Play's Device and Network Abuse policy treats downloaded behaviour-changing code as a
  violation; the interpreter carve-out makes it survivable, but it is a gray zone
- a `git pull` on-device means shipping a git client, where a signed release tarball would be
  a tenth of the code

If release friction ever justifies over-the-air updates, do it properly: signed bundles, an
Ed25519 public key pinned in the APK, signature verified before extraction, monotonic version
numbers, atomic swap, and rollback on failed boot.

`PhpServer.syncAppCode()` re-copies the PHP out of assets whenever `versionCode` changes, so an
app update always brings its code with it.

---

## Schema upgrades

The MySQL migration ladder in `lib.php` does not run on SQLite. Instead `sqliteSync()` makes the
database match `SCHEMA_STATEMENTS` declaratively: it creates missing tables, adds missing
columns, and creates missing indexes on every boot. A fresh install and an upgraded one converge
on the same shape, and a future schema change needs no new migration entry for Android.

Its one limit, marked in the code: it only *adds*. SQLite cannot drop or retype a column
without a table rebuild, and nothing in this app's history has needed to.

---

## iOS

This architecture does not port. iOS forbids `fork`/`exec`, so there is no way to spawn a PHP
process, and downloaded interpreted code is not permitted either. A future iOS app would have
to link PHP as a library and drive it in-process. Nothing in the PHP layer assumes a subprocess,
so that door is still open — but it is a different app, not a recompile.

---

## Play Store checklist

- Data Safety declaration must match the privacy claim exactly. Declare the Drive backup as a
  user-initiated transfer to the user's own account. A mismatch is a rejection.
- `allowBackup="false"` and the exclusions in `data_extraction_rules.xml` keep the ledger out
  of Google's automatic cloud backup and device-to-device transfer.
- arm64 only. Every current device is arm64, and each extra ABI adds another ~12 MB.
