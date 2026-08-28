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
| `HL_BACKUP` | `1` | draws the Drive panel in the drawer |
| `HL_APP_VERSION` | e.g. `1.0.5 (5)` | version line at the foot of the drawer |

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

## The app lock

The ledger is one file on the phone with no account behind it, so the phone's own lock is the
only thing between it and whoever is holding the device. `MainActivity` asks for it in `onStart`
and re-arms in `onStop`: every launch, and every return from the background, the way a payments
app does. Fingerprint or face, falling back to the PIN/pattern/password — `BIOMETRIC_STRONG or
DEVICE_CREDENTIAL` on API 30+, `setDeviceCredentialAllowed(true)` below that, where the combined
form throws.

Three details that are easy to get wrong:

- **Ask `BiometricManager.canAuthenticate()` first.** A phone with no lock set has nothing to
  check against; opening anyway is right, because refusing would leave the ledger unreachable
  rather than protected. Do not infer this from `onAuthenticationError` — the codes that mean
  "nothing enrolled" overlap with ones that mean an ordinary failed attempt, so treating them
  alike either locks the user out permanently or lets a failure through. The terms page says
  plainly what a phone with no lock means.
- **`onStop` must not re-arm while the prompt is up.** Confirming a PIN on API 30+ is a separate
  system activity, so `onStop` fires mid-authentication; without the `prompting` guard a second
  prompt queues behind the one being answered.
- **The lock gates the view, not the data.** No key hangs off it. The database is protected by
  app-private storage, the same thing that protects it from other apps. If a stolen-and-rooted
  phone is in the threat model, the answer is a passphrase-derived key on the database itself.

Connecting a Drive account leaves the app, so returning from the account chooser asks for the
lock again. That is correct, and it is what a payments app does too.

Testing it on an emulator, which ships with no lock at all:

```bash
adb shell locksettings set-pin 1234     # now the prompt appears on launch
adb shell input text 1234 && adb shell input keyevent 66
adb shell locksettings clear --old 1234 # back to the no-lock path
```

`adb exec-out screencap` renders the prompt as a black frame — it is a secure window. Check
`adb shell dumpsys window | grep mCurrentFocus` instead; it reads `Window{… BiometricPrompt}`.

---

## Google Drive backup

Use the **`drive.appdata` scope only**. `drive` and `drive.readonly` are Google *restricted*
scopes: shipping either means an annual CASA security assessment — real money, weeks of process.
`appdata` is not restricted, is invisible in the user's Drive UI, and can only see files this
app created.

One rolling `openledger-backup.db.gz` is kept, replaced on each run. Not a dated history:
keeping N copies of a financial database in someone else's cloud is a bigger promise than this
app should make, and Drive keeps its own revisions of a replaced file anyway.

### Setting up the OAuth client id

Backup stays switched off, cleanly, until `DriveAuth.WEB_CLIENT_ID` is filled in — the panel
says "not set up in this build" and nothing throws. To turn it on:

1. **Get the signing fingerprints you will register.** Debug first, so it works while developing:

   ```bash
   keytool -list -v -keystore ~/.android/debug.keystore \
           -alias androiddebugkey -storepass android -keypass android | grep SHA1
   ```

   And the release key you will actually ship with — for a Play App Signing upload, the SHA-1
   that matters is the **app signing certificate** Google shows under Play Console → Test and
   release → App integrity, not your upload key. Register both or Drive fails only in production.

2. **Google Cloud Console → new project** (or an existing one) **→ APIs & Services → Library →
   Google Drive API → Enable.**

3. **OAuth consent screen.** User type *External*. App name, support email, developer email.
   Under *Scopes → Add or remove scopes*, add `.../auth/drive.appdata` — it is listed as
   non-sensitive, so there is no verification queue and no CASA assessment. Do **not** add
   `drive` or `drive.readonly`; either one is restricted and costs an annual third-party
   security audit. While the app is in *Testing*, add your own Google account under
   *Test users* or sign-in returns `access_denied`.

4. **Credentials → Create credentials → OAuth client ID → Android.**

   - Package name: `com.xpertxyz.ledger` — must equal `applicationId` exactly.
   - SHA-1: from step 1. Create one entry per fingerprint (debug and release).

   You never paste this id anywhere. Google matches the app by package name plus signing
   fingerprint; the entry only has to exist.

5. **Credentials → Create credentials → OAuth client ID → Web application.** Name it anything;
   it needs no redirect URIs. Copy **this** client id.

6. Paste it into `DriveAuth.WEB_CLIENT_ID`:

   ```kotlin
   const val WEB_CLIENT_ID = "1234567890-abcdefg.apps.googleusercontent.com"
   ```

7. Rebuild, open the drawer, **Connect Google Drive**.

**The one mistake everyone makes:** putting the *Android* client id into `WEB_CLIENT_ID`. The
SDK wants the **web** one — the Android entry exists only so Google can recognise the caller.
Get it backwards and you get `APIException: 10 (DEVELOPER_ERROR)` with no further explanation.
Same error if the SHA-1 registered does not match the build you are running, which is why the
debug fingerprint has to be registered too.

The id is not a secret: it is a public identifier, and an attacker holding it still cannot
impersonate the app without the signing key. It is fine in the repository.

**When Drive works in debug and not from Play**, it is this and not something subtler: Play
re-signs the upload with its own app signing key, so the build on the tester's phone carries a
fingerprint that no `keytool` run against a local keystore will ever print. Ask the installed
build itself rather than guessing:

```bash
adb shell pm path com.xpertxyz.ledger          # → package:/data/app/.../base.apk
adb pull <that path> /tmp/installed.apk
$ANDROID_HOME/build-tools/*/apksigner verify --print-certs /tmp/installed.apk | grep SHA-1
```

`apksigner`, not `keytool -printcert -jarfile`: these APKs carry no v1 JAR signature, so keytool
answers "Not a signed jar file" and tells you nothing. Whatever that SHA-1 is has to be on an
Android OAuth client for `com.xpertxyz.ledger`; a client holds one fingerprint, so debug, upload
and Play app signing keys need one client each. The app now writes the reason into the backup
panel itself, so the phone names this rather than reloading in silence.

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

### The in-app update bar

`UpdateBridge` (`AppUpdate.kt`) drives Play's **flexible** update flow. Not the immediate one:
that is a full-screen Google-blue sheet that cannot be themed and blocks the ledger until it
finishes. Flexible downloads in the background and leaves the UI to us, so the offer, the
progress bar and the restart prompt are drawn by `layout()` out of the same tokens as the rest
of the app — the same arrangement as the backup panel. The one screen Google keeps is the
single dialog asking permission to download, which is not ours to skip.

The bridge is exposed to JavaScript as `HLUpdate` with `status()` / `begin()` / `install()` /
`dismiss()`. The page polls `status()` and draws nothing at all when `state` is empty, so the
web build — which has no bridge — never sees any of it. `refresh()` runs on every `onResume`,
because a download that finished while the app was in the background sends no callback to a
process that was not listening.

"Later" is deliberately session-only: the offer comes back the next time the app is opened,
rather than never.

**It cannot be tested from a debug build.** Play answers `appUpdateInfo` only for an app it
installed itself, with a higher `versionCode` live on a track the tester is on. From anything
else the check fails, the state stays empty, and nothing is drawn — which is the correct
behaviour, not a bug. To see it: install from internal testing, then upload a build with a
higher `versionCode` and wait for Play to notice.

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
