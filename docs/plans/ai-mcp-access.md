<!-- Status: PLANNED, not implemented. Written 7 Sep 2026. Nothing in this document has
     been built; the "What I verified" section was checked against the live spec and this
     machine on that date, so re-check it before starting. -->

# Connecting the ledger to AI over MCP

## Context

The question was whether MCP is the thing that lets an AI read, write and summarise the ledger,
and what it would take. Yes, and most of the parts already exist. `api.php` is a 149-line
token-authenticated JSON API that the watch uses, and `device_tokens` is a working
machine-credential model with scopes, a cap and one-tap revocation. What is missing is breadth
(three endpoints, all about expenses) and an MCP envelope in front of them.

Decisions taken:

| Question | Answer |
|---|---|
| Which client | Any MCP client, from anywhere — so the **website** serves MCP, not a shim on a laptop |
| Permissions | **Read plus add** new entries. No edit, no delete |
| Auth | **Reuse the existing device pairing** (`device_tokens`) |

## What was verified rather than assumed

- **A plain JSON reply is legal.** The spec says the server "**MUST** return either
  `Content-Type: application/json` (a single JSON object) or `Content-Type: text/event-stream`".
  Request-scoped PHP on shared hosting is a conforming MCP server. No persistent process, no SSE.
- **Sessions and the GET stream are gone** in revision `2026-07-28`. `Mcp-Session-Id` is ignored;
  `GET`/`DELETE` answer `405`.
- **Modern clients send no `initialize`.** Protocol version, client info and capabilities ride in
  `params._meta`, mirrored into `MCP-Protocol-Version`, `Mcp-Method` and `Mcp-Name` headers.
  Header and body must agree or the server returns `400` with JSON-RPC `-32020`. Older clients
  (`2025-03-26` … `2025-11-25`) still do the `initialize` handshake, so both eras need answering.
- **Bearer auth works with Claude Code today.** `claude mcp add --help` documents
  `claude mcp add --transport http <name> <url> --header "Authorization: Bearer ..."`.
- **The header plumbing exists.** `.htaccess` forwards `Authorization` (`E=HTTP_AUTHORIZATION`)
  because CGI strips it — added for the watch, reusable unchanged.

Not confirmed, and worth checking before relying on them: whether claude.ai web and mobile accept
a static bearer or demand OAuth 2.1, and which protocol era the chosen client speaks. Neither
blocks the build; both decide how far "from anywhere" actually reaches.

## The shape

Three layers, each shippable alone. The REST layer earns its place independently: it is what any
script or agent framework can call, and MCP is a thin translation over it.

### 1. A third scope

`lib.php:1305` gains `DEVICE_SCOPE_AI = 'ai'` in `DEVICE_SCOPES`. No migration —
`scope VARCHAR(10)` already holds it. `mintDevicePairing()` already rejects unknown scopes and
`redeemDevicePairing()` already matches scope in the WHERE clause, so both work untouched.
`/api/pair` hardcodes `api` and `/pair` hardcodes `full`, so neither can mint an `ai` credential.
The drawer is the only door.

**Enforcement is one fail-closed table, not a check per handler.** `api.php` reads
`$device['scope']` nowhere today; a `full` token can call everything, which is harmless and must
keep working or paired browsers break mid-flight. Put `apiScopeAllows(string $path, string $scope)`
in `lib.php` holding a path→scopes map, and gate once in `api.php` immediately after
`deviceFromToken()`:

- `?? []` on the lookup, so an endpoint whose author forgot the map entry is unreachable loudly on
  the first curl rather than silently open.
- Answer **404, not 403** — a watch token probing a read endpoint learns nothing, and it matches
  the existing fall-through body.
- In `lib.php` so `--selfcheck` can assert the truth table directly: the watch keeps exactly what
  it has, `ai` cannot reach `/api/mcp`'s write tools, an unknown path is refused, `full` still
  works. `--preflight` runs `--selfcheck` with assertions forced on, so this gates itself.

### 2. Getting the token into a config file

The pairing flow never shows the token because a watch has a bezel: a human reads six digits off
one screen and types them into another. An MCP client is a config file with no keypad, so the
code exchange has nothing to do.

**For `scope='ai'` only, mint and redeem in the same server-side request** and show the raw token
once in the drawer, from `$_SESSION['ai_token_once']`, unset on render. That reuses
`mintDevicePairing()`'s "replace the unclaimed row" and `redeemDevicePairing()`'s
`DEVICE_TOKENS_MAX` pruning for free, adds no SQL, and leaves no six-digit code alive to leak.
A label input lets someone with two clients tell the rows apart.

**Refuse to mint one when `!isHttps()`.** One line in the `/watch/pair` branch, and it is the
check that actually holds: the drawer cannot hand out a credential that is about to travel in
clear.

The trade-off, stated plainly: the token now lives in the DOM once, the clipboard, and a plaintext
config file. Bought back by the scope being read-plus-append only, `last_seen_at` making a stale
token visible, and Disconnect killing it on the next call. Rejected: OAuth 2.1 with dynamic client
registration — several hundred lines and a new table to save one paste. Known ceiling: clients
that speak only OAuth cannot connect.

### 3. REST endpoints

Reads on the existing `api-read` limiter, writes on `api-write`. **No `api*()` function may touch
`$_GET` or `$_POST`** — each takes an array and returns an array, which is what lets the MCP path
call the same code instead of looping back over HTTP. Make that a preflight grep.

| Endpoint | Reuses |
|---|---|
| `GET /api/summary` | exists — `watchSummary()` + `apiLedgerInfo()` |
| `GET /api/reference` | `apiLedgerInfo()`, `categoryTree()`, `membersFor()` — every id the model needs, **plus `today`**, so it stops filing entries under its training-cutoff year |
| `GET /api/entries` | `whoWhere()`, `ownedId()`, the `(household_id, date)` index |
| `GET /api/months` | `sqlYm()` grouped scans — one row per month, expense/earning/investment/net |
| `GET /api/breakdown` | `rollupCategories()`, the same query `renderHistory` runs |
| `GET /api/goals` | `goalSummary()`, stripped of `proj`/`by`/`actuals`/`snaps`/`ms` |
| `POST /api/expense` | exists — `createExpense()`, keeps the `retry` dedupe |
| `POST /api/earning` | `createEarning()` (new) |
| `POST /api/investment` | `createInvestment()` (new) |
| `POST /api/goals/snapshot` | `goalSnapshotCreate()` (new) |

Two details that decide whether the numbers are right:

- **A paginated list must carry the whole-range total and count** from a separate SUM, or the model
  sums a truncated page and reports a confidently wrong figure.
- **Cap the responses**: `limit` 1..500, date span ≤ 366 days, months ≤ 60. That protects the
  database from one expensive call and stops a model pulling the ledger into a context window.

`/api/expense` gains optional `date` and `member_id` by forwarding the keys only when present, so
watch behaviour stays byte-identical. `attributableMember()` already decides who may attribute to
whom. Recurring items are deliberately not exposed: a standing instruction that posts forever is
not obviously "add an entry".

**Endpoint names must match `[a-z/]` only.** The preflight guard that proves every endpoint sits
behind the bearer check greps `~\$path === \'(/api/[a-z/]+)\'~`. A hyphen, digit or underscore in
a path silently escapes the guard entirely — so `/api/goals/snapshot`, never
`/api/goal-snapshot`. Add an assertion that fails on any `/api/…` literal the regex cannot parse.

### 4. One door per table

`createExpense()` exists so the watch and the web form cannot diverge, and `--preflight` fails if
`api.php` ever writes to `expenses` itself. Earnings, investments and goal snapshots have no such
helper — they are inline in the POST switch and end in `redirect()`, so nothing else can call them.

Extract `createEarning()` and `createInvestment()` into `lib.php` beside `createExpense()`, and
`goalSnapshotCreate()` into `goals.php` (the goal module owns its table). Rewire the switch to
call them. Then generalise the preflight rule from one hardcoded line into a table of
table → creator → home file, asserting for each that the creator is defined exactly once, that
`INSERT INTO <table>` appears **only** in its home file, and that `api.php` calls the creator and
contains no INSERT. That last form is stronger than today's check and covers `views.php` and
`goals.php` too. It holds because `sweepRecurring()` also inserts into those tables and already
lives in `lib.php`.

Extend `tests/dual-driver.php` with round trips for the three new creators — money precision and
the `assertUnderLimit` COUNT both differ by driver — plus a cross-scope redemption that must still
be refused.

### 5. The MCP endpoint

**`POST /api/mcp`, not a top-level `/mcp`.** `index.php:1769` already routes `/api/*` into
`api.php` before `session_start()` and before the main DB connect, so it inherits the PDO, the
bearer parse, `deviceFromToken()`, the JSON writers and the scope gate for nothing. A top-level
route would mean a second auth path, a second JSON writer, a session file minted per call, and a
path outside the positional preflight guard.

`mcp.php` holds only the JSON-RPC envelope and the tool schemas, roughly 200 lines. Tools map to
the same functions the REST routes call, over parameters they are handed.

| Method | Answer |
|---|---|
| `initialize` | `protocolVersion`, `capabilities.tools`, `serverInfo` — for the legacy era |
| `notifications/initialized` | **HTTP 202, empty body.** Returning a result for a notification hangs clients. Comment it so nobody "fixes" it back |
| `tools/list` | ~9 tools, no cursor |
| `tools/call` | `content` text block **and** `structuredContent`, so both client generations work |
| `ping` | `{}` |
| unknown | `-32601`; a batch array → `-32600`, which also stops fifty writes smuggling past one limit check |

`UserErr` inside `tools/call` returns `isError: true` with the message, **not** a JSON-RPC error:
the former lets the model read "Amount must be greater than zero" and retry, the latter makes it
give up. That wording is already user-facing.

Also required by the spec: validate `Origin` when present and answer `403` otherwise, answer
`405` to `GET`/`DELETE`, and reject a header/body mismatch with `-32020`.

### A new PHP file joins three registries

Miss any and it fails in a way that looks unrelated:

- **`phpAppFiles`** in `android/app/build.gradle.kts`, or the APK ships an `index.php` requiring a
  file it does not have and answers 500 on every page. `--preflight` catches this now, but the
  list is still the thing to edit.
- **The `.htaccess` `FilesMatch` deny list**, so the source cannot be fetched directly.
- **The preflight `php -l` list** at `index.php:347`.

While checking this: **`goals.php` is missing from the `.htaccess` deny list** — an oversight from
the goals work, not from this. `/goals.php` returns a blank 200 rather than anything sensitive,
but it is inconsistent with every other include and belongs in the same one-line fix.

## Security

- **HTTPS is the blocker, and it is Phase 0.** There is no redirect anywhere in this app today.
  A bearer token over plain http is in the clear on every call, and a config file containing
  `http://` is never upgraded by anything. The rewrite needs **both** conditions — `%{HTTPS} off`
  *and* `X-Forwarded-Proto !https` — because TLS may terminate at a proxy, which `isHttps()`
  already knows; a rewrite that disagrees with it loops forever. Add HSTS in the same block.
- **The scope is the safety model**, not the tool descriptions. Read-plus-add is the absence of any
  edit or delete endpoint plus a server-side check.
- **Prompt injection is real.** Notes, member names and category names are free text, and on a
  shared ledger another member's text is untrusted input. Mitigations in order of value: the scope
  forbids edit and delete, so the worst case is spurious rows a human can see and remove; return
  ledger text only as JSON string values, never interpolated into a sentence the server composes;
  truncate notes on the way out as well as in; `api-write` caps a successful injection at 120 rows
  an hour. **Adding edit or delete tools later invalidates this analysis** — say so in `mcp.php`'s
  header, where whoever adds one will read it.
- **Tool descriptions are a control.** Tell the model there is no way to edit or delete, that ids
  come from `get_reference` and must not be guessed, and that the ledger's `today` beats its own
  idea of the date. Mark reads `readOnlyHint`.
- **Keep CORS absent.** `api.php` omits it deliberately; an MCP client is not a browser page.
- **Rate limits are per-IP**, so a hosted client behind a shared egress may see spurious 429s, and
  a stolen token used elsewhere gets a fresh bucket. Measure before adding a family; the response
  caps are the real protection.
- **The drawer must not lie.** Its scope wording is a binary ternary today, so an AI device would
  read "Totals and adding only" when it can in fact read the entire ledger. Make it three-way.
- **No per-device audit trail.** `created_by` records the user, not the device, so "which entries
  did the AI add?" has no answer without a `device_id` column on four tables. Out of scope; the
  mitigation is that entries are visible like any other.

## Build order

Each phase ships alone and ends with the gate below.

0. **HTTPS redirect and HSTS.** No PHP. Verify a 301 and no loop.
1. **Extraction.** The three creators, the switch rewired, the generalised one-door preflight,
   dual-driver extended. No new endpoints; the web app behaves identically.
2. **Scope.** The constant, `apiScopeAllows()`, the gate, the selfcheck truth table, the drawer
   wording. Still no new endpoints; the watch must be provably unaffected.
3. **Token handout.** The `ai` branch in `/watch/pair`, the `isHttps()` refusal, the one-shot panel.
4. **Reads.** Five functions with no superglobals, five routes, five map entries.
5. **Writes.** Earning, investment, goal snapshot, plus `date`/`member_id` on expense.
6. **MCP.** `mcp.php` and the `/api/mcp` route. Pure protocol over phases 4 and 5.
7. **Docs.** README API section and a copy-pasteable config snippet.

## Verification

```bash
php index.php --preflight
DB_DRIVER=sqlite DB_PATH=/tmp/t.db php index.php --preflight
php tests/dual-driver.php > /tmp/a.txt
DB_DRIVER=sqlite DB_PATH=/tmp/dd.db php tests/dual-driver.php > /tmp/b.txt
diff <(sed 1d /tmp/a.txt) <(sed 1d /tmp/b.txt) && echo SAME
```

`--selfcheck` runs inside `--preflight` with assertions forced on, so it needs no separate line.
Every new query must work on MySQL and SQLite: no `NOW()`, date parts only through `sqlYm()` and
friends, no `HAVING` without `GROUP BY`.

By hand, the checks that actually catch things:

```bash
curl -s -H "Authorization: Bearer $TOK"      https://<site>/api/summary | jq .
curl -s -H "Authorization: Bearer $WATCHTOK" https://<site>/api/entries      # must be 404
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<site>/api/mcp \
  -H "Authorization: Bearer $TOK" -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","method":"notifications/initialized"}'                # must be 202, empty
claude mcp add --transport http ledger https://<site>/api/mcp --header "Authorization: Bearer $TOK"
```

Then ask a real client something only the ledger knows, and confirm an added expense appears in
the app with the right member and `created_by`. Cross-check `/api/entries`'s range total against
the same month in `/history` — a paginated total that disagrees with the page is the bug this
whole design is trying not to ship.

## Not worth doing

- **OAuth 2.1 for this app.** Hundreds of lines and a new table so one person can skip one paste.
  Revisit only when a client that cannot send a static header is actually wanted.
- **A top-level `/mcp` route.** Second auth path, second JSON writer, a session file per call, and
  outside the preflight guard.
- **SSE or any long-lived connection.** No persistent process here. Stateless POST is not a
  compromise, it is the only correct choice.
- **Edit or delete tools.** The ledger is the only copy of this data.
- **Extracting every read query out of `views.php` for symmetry.** Big diff, no safety gain — reads
  cannot corrupt. The new read functions are a second, smaller copy of the page's aggregates;
  unify only if a number ever disagrees with the page.
