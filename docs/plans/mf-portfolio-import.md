<!-- Status: PLANNED, not implemented. Written 7 Sep 2026. Nothing here has been built.
     The regulatory and API facts were checked against live sources on that date and are
     the part most likely to have moved — re-check them before starting. -->

# Importing mutual fund holdings, and computing snapshots from them

## Context

Goal snapshots are typed in by hand today: you open the broker app, read the number, and enter
it. The ask was to import a mutual fund portfolio into the ledger instead, so the snapshot
arrives on its own — and to look at how Groww does it.

## How Groww actually does it, and why that door is now shut

Groww's own help pages describe the mechanism: enabling auto-tracking "places a request for your
Consolidated Account Statement (CAS) through MFCentral", and you "give permission to Groww to
read your CAS". So it is a consented statement fetch, not a holdings API. Two things follow.

**Groww's public Trade API cannot help.** Its `/v1/holdings/user` returns demat equity only —
ISIN, trading symbol, quantity, pledge and T1 quantities. There is no mutual fund folio or unit
in it, because units bought through Groww sit with the registrars, not in a demat account.

**The MF Central route closed to apps in September 2025.** AMFI asked MF Central to stop sharing
investor data with third-party applications, as part of a regulatory push toward the Account
Aggregator framework as the official channel. Reportedly this left something like a hundred
fintech platforms without portfolio import.

**Account Aggregator is not available to a personal project.** A Financial Information User must
be registered with and regulated by RBI, SEBI, IRDAI or PFRDA. A ledger for one household cannot
become one, and the alternative — partnering with a regulated entity — is not a weekend's work.

So the integration Groww uses is genuinely closed. What is wide open is the same data requested
by the person it belongs to: **mfcentral.com is still live for investors**, and CAMS and KFintech
will both send you your own CAS after a PAN and OTP check. That is the identical statement Groww
was reading. The only difference is who asks for it, and since this is your own ledger, you are
the one with standing to ask.

## The reframing that makes this easy

The ledger records **money in** — a contribution on a date. A snapshot is **what it is worth** —
units × NAV. Those are different facts on different clocks:

| Fact | Changes | Where it comes from |
|---|---|---|
| Units held | Only when you buy or sell | A CAS you request, once in a while |
| NAV | Every business day | AMFI, free, no authentication |

So there is no need for a live broker connection at all. Enter units occasionally; value them
daily. That turns a licensing problem into a file-parsing problem, and then into arithmetic.

### The NAV feed, verified

`https://portal.amfiindia.com/spages/NAVAll.txt`, fetched 7 Sep 2026:

```
Scheme Code;ISIN Div Payout/ ISIN Growth;ISIN Div Reinvestment;Scheme Name;Plan;Option;Net Asset Value;Date
135762;INF846K01WO1;-;Axis Children's Fund;Direct Plan;Growth Option;30.3228;04-Sep-2026
```

1.4 MB, 18,021 lines, semicolon-delimited, keyed by ISIN, no authentication and no key. Two
things learned by testing rather than reading:

- **Conditional GET is useless here.** A request with `If-Modified-Since` set to the exact
  `Last-Modified` it had just returned came back `200`, with a new `Last-Modified` two minutes
  later. The file is regenerated constantly. Do not build around `304`; fetch it and compare the
  NAV *date inside the file* instead.
- **Blank rows exist.** Section headers, fund-house names and empty lines are interleaved with
  data. A parser must skip any line without seven semicolons, and skip `-` in the ISIN columns.

## Design

### Units and prices are new; contributions are not

Two new tables, and no change to `investments`:

- **`holdings`** — `household_id`, `isin`, `scheme_name`, `units DECIMAL(18,4)`, `folio`,
  `type VARCHAR(40)`, `member_id`, `source`, `as_of DATE`, `created_by`. The `type` and
  `member_id` columns exist so a goal's existing filters apply unchanged: `goalTypeNames()`
  already expands a ticked parent type to its children, and a holding tagged "SIP" is counted by
  a goal tracking "Equity" for free.
- **`nav`** — `isin` primary key, `scheme_name`, `nav DECIMAL(12,4)`, `nav_date DATE`,
  `fetched_at`. Only ISINs actually held, so a few dozen rows rather than 18,000.

`nav` carries no `household_id`, which is correct — a NAV is a public fact, not household data —
and it means `deleteAccount()` leaves it alone. `holdings` does carry one, so it must join
`EXPORT_TABLES` and `deleteAccount()`'s list in `lib.php`, and `SCHEMA_SENTINEL` gets bumped from
`.schema-ok-v22`. That is the same three-registry dance the goals tables went through.

**Match on ISIN, never on scheme name.** "HDFC Flexi Cap" is four different instruments once
Direct/Regular and Growth/IDCW are considered, and the CAS and AMFI spell names differently.

### Getting units in without building an upload path

There is no file upload anywhere in this app today — no `$_FILES`, no multipart form. Rather than
make this feature the first one, **paste the rows into a textarea**. A converted CAS is tens of
lines, not thousands. That skips multipart handling, temp files, upload size limits and the whole
class of bugs that comes with them, and `str_getcsv()` is stdlib.

Accepted shape, one line per holding, ISIN first:

```
INF846K01WO1,Axis Children's Fund - Direct Growth,124.5670,SIP
```

The parser resolves the type name through `validInvestmentType()` (which scopes it to the
household) and attributes through `attributableMember()`. Note that `validInvestmentType()` does
**not** reject archived types, so the importer needs its own check or an archived scheme silently
lands in a bucket no active view shows.

### Where the CAS becomes a CSV

**Not on the server.** A CAS is a password-protected PDF; parsing it needs a PDF library and
decryption, PHP has neither in this build, and the Android interpreter is compiled `--disable-all`
with no OpenSSL and no zlib. Converting it in PHP is not a corner worth turning.

Convert it locally instead. `casparser` (Python, open source) reads CAMS/KFintech CAS and emits
JSON or CSV; it takes the PDF password on the command line and never leaves the machine. Document
that as the recommended route.

**Say no to the hosted parsers.** Several services will parse a CAS for you over an API. A CAS is
every rupee you hold across every fund house, with folio numbers and your PAN. Sending it to a
third party to save a command is a bad trade for a household ledger.

### The daily job

`index.php --cron` already runs at 03:00 on Hostinger with full database and config access, and
`sweepRecurring()` is the precedent for writing rows with nobody signed in. Add to it:

1. Fetch `NAVAll.txt` with `file_get_contents()` and a stream context timeout — the same
   mechanism `verifyGoogleIdToken()` already uses, which is the app's only outbound request.
2. Parse, keep only ISINs present in `holdings`, upsert into `nav`.
3. For each goal with auto-snapshot on, sum `units × nav` over holdings matching that goal's type
   and member filters, and write one `goal_snapshots` row.

**This is website-only.** The phone's PHP has no `https://` stream wrapper at all, so the fetch
cannot run there. The Android build gets values the way it already gets everything else — by
adopting the online ledger — and its own goals keep manual snapshots. Say so in `ANDROID.md`
rather than leaving someone to discover it.

Two details that bite:

- **`goal_snapshots.created_by` is `NOT NULL` with no sentinel.** Use the goal's own `created_by`,
  which is exactly what `sweepRecurring()` does with a recurring item's author.
- **`GOAL_SNAPS_MAX` is 500 per goal.** Daily snapshots exhaust that in sixteen months. Write
  **weekly** — 500 weeks is nine and a half years — plus one on demand from a button. Pruning to
  one a month beyond a year is the upgrade if it ever matters.

Mark them in the existing `note` column: `auto · NAV 4 Sep 2026`. No schema change, and a human
reading the snapshot list can tell which numbers they typed.

### Importing transactions is a separate, later thing

Bringing CAS *purchases* into `investments` makes "Actual in" real without hand-entry, but it is
where the damage lives, so it ships after holdings work.

- **There is no dedupe for investments and no unique constraint.** The one precedent, the `retry`
  window in `api.php`, keys on `created_at` — and `investments` has no `created_at` column, unlike
  `expenses` and `earnings`. So that shape cannot be borrowed. Match on
  `(household_id, date, amount, type, name)` and show what will be skipped.
- **Preview before writing.** Parse, show every row with a duplicate flag and a checkbox, and
  write only on a second submit. Anyone who already logs SIPs by hand will otherwise double-count
  their whole history in one click.
- **`assertUnderLimit()` checks once, before an insert.** A bulk import must check
  `count + N <= investments_total_max` up front, not per row, or it fails halfway with rows
  already written.

### Route constraints worth knowing before writing code

- **`--preflight` pins `csrfCheck()` at exactly four occurrences**, in a specific order. The
  import route must live inside the existing authed `switch ($path)`, not carry its own check.
- **`redirect()` must stay the only thing emitting a `Location` header.**
- **`rateLimit()` is the only upsert in the app** and carries both dialect branches. The `nav`
  upsert would be the second, so it needs its own `isSqlite($db)` branch — `ON CONFLICT … DO
  UPDATE` with `excluded.`, not `ON DUPLICATE KEY UPDATE` with `VALUES()`.
- Units want four decimals where money wants two; `roundMoney()` is for the product, not the
  units.

## Build order

1. **`holdings` and `nav` tables**, sentinel bump, both registry lists, a plain holdings screen
   with add, edit, delete. Value them against a NAV typed in by hand. Useful on its own: the
   ledger can now say what the portfolio is worth.
2. **The AMFI fetch in `--cron`**, the `nav` upsert with its SQLite branch, and a `--nav` CLI flag
   so it can be run on demand while developing.
3. **Auto-snapshots**, weekly, per goal, opt-in per goal with a toggle. Requires lifting the
   insert out of `goalSnapshotSave()` into something callable without `$_POST` — the same
   extraction the AI access plan needs, so whichever lands first pays for it.
4. **Paste-a-CSV import for holdings**, with the archived-type check and a preview.
5. **Transaction import into `investments`**, with dedupe, preview and the cap pre-check.
6. **Docs**: the CAS request steps, the `casparser` command, and the Android limitation.

## Verification

The usual gate after every phase:

```bash
php index.php --selfcheck
php index.php --preflight
DB_DRIVER=sqlite DB_PATH=/tmp/t.db php index.php --preflight
php tests/dual-driver.php > /tmp/a.txt
DB_DRIVER=sqlite DB_PATH=/tmp/dd.db php tests/dual-driver.php > /tmp/b.txt
diff <(sed 1d /tmp/a.txt) <(sed 1d /tmp/b.txt) && echo SAME
```

Specific to this feature:

- **A pure parser, asserted.** The NAV parser and the holdings-CSV parser are pure functions of a
  string, so `--selfcheck` can hold a dozen lines of real AMFI text — a header, a section title, a
  blank line, a `-` ISIN, a normal row — and assert what comes out. That is the same treatment
  `goalProject()` got, and it is what stops a format change becoming silent wrong numbers.
- **Cross-check one scheme by hand** on the day of the first run: units × NAV from the app against
  the broker's own screen. They should agree to the paisa.
- **Run the cron twice** and confirm the second run writes no second snapshot for the week and
  changes no NAV.
- **Re-import the same holdings CSV** and confirm it updates rather than duplicates.
- `EXPLAIN` the holdings-by-goal query and confirm it uses an index rather than scanning.

## What not to do

- **Do not chase MF Central or Account Aggregator.** One is closed to third-party apps, the other
  needs a regulated FIU registration. This is the finding that decides the whole design.
- **Do not parse the CAS PDF on the server.** Encrypted PDF, no library, and an interpreter built
  without OpenSSL or zlib on the phone.
- **Do not send the CAS to a hosted parsing service.** It is your entire portfolio and your PAN.
- **Do not scrape Groww.** Terms aside, an unofficial scrape breaks silently and takes the
  numbers with it.
- **Do not match schemes by name.** ISIN or nothing.
- **Do not write snapshots daily.** The 500-per-goal cap turns that into a failure in sixteen
  months.
