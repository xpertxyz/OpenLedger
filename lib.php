<?php
declare(strict_types=1);

// ────────────────────────────────────────────────────────────────────
// Schema (MySQL / InnoDB). Idempotent — CREATE IF NOT EXISTS is a cheap
// metadata check on subsequent requests. First hit against a fresh DB
// creates everything.
// ────────────────────────────────────────────────────────────────────
const SCHEMA_STATEMENTS = [
    "CREATE TABLE IF NOT EXISTS households (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        currency VARCHAR(8) NOT NULL DEFAULT '₹',
        number_format VARCHAR(12) NOT NULL DEFAULT 'indian',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        google_sub VARCHAR(64) NOT NULL,
        email VARCHAR(190) NOT NULL,
        name VARCHAR(80) NOT NULL,
        is_dark TINYINT(1) NOT NULL DEFAULT 0,
        theme VARCHAR(16) NOT NULL DEFAULT 'organic',
        currency VARCHAR(8) NOT NULL DEFAULT '₹',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_google_sub (google_sub),
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Who a ledger belongs to. `role` is the whole permission model: an owner edits anything,
    // a member edits only what they added. A user may sit in several households; the one they
    // are looking at right now is `users.household_id`.
    "CREATE TABLE IF NOT EXISTS household_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(10) NOT NULL DEFAULT 'member',
        joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_household_user (household_id, user_id),
        INDEX ix_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // One-shot join tokens. `used_at` is what makes a link single-use; `expires_at` is set 30
    // minutes out at mint time. Rows are kept after use so the owner can see who joined and when.
    "CREATE TABLE IF NOT EXISTS invites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        token CHAR(32) NOT NULL,
        created_by INT NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        used_by INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_token (token),
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // A spender label, not a login. `user_id` links one to the person who signed in, so
    // "who spent it" and "who may edit it" name the same human. NULL for a label nobody
    // logs in as — a child, a shared card.
    "CREATE TABLE IF NOT EXISTS members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(60) NOT NULL,
        user_id INT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        icon VARCHAR(30) NOT NULL,
        is_custom TINYINT(1) NOT NULL DEFAULT 0,
        budget DECIMAL(12,2) NOT NULL DEFAULT 0,
        parent_id INT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
        member_id INT NULL,
        recurring_id INT NULL,
        created_by INT NULL,
        note VARCHAR(200) NULL,
        date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_household_date (household_id, date),
        INDEX ix_household_cat (household_id, category_id),
        INDEX ix_household_member (household_id, member_id),
        INDEX ix_household_recent (household_id, id),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        type VARCHAR(40) NOT NULL,
        member_id INT NULL,
        recurring_id INT NULL,
        created_by INT NULL,
        date DATE NOT NULL,
        INDEX ix_household_date (household_id, date),
        INDEX ix_household_type (household_id, type),
        INDEX ix_household_member (household_id, member_id),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS recurring (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        kind VARCHAR(20) NOT NULL DEFAULT 'expense',
        category_id INT NULL,
        type VARCHAR(40) NULL,
        member_id INT NULL,
        frequency ENUM('monthly','quarterly','yearly') NOT NULL,
        next_date DATE NOT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        total_amount DECIMAL(12,2) NULL,
        created_by INT NULL,
        INDEX ix_household_next (household_id, next_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS rate_limits (
        bucket VARCHAR(160) NOT NULL PRIMARY KEY,
        hits INT NOT NULL DEFAULT 0,
        window_end INT UNSIGNED NOT NULL,
        INDEX ix_window (window_end)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // `target` is the monthly figure this household means to put in — the investment side of
    // a category budget, and it hangs off the parent for the same reason: a sub-type's money
    // rolls up, so two targets on one branch would count the same rupee twice.
    "CREATE TABLE IF NOT EXISTS investment_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(40) NOT NULL,
        archived TINYINT(1) NOT NULL DEFAULT 0,
        target DECIMAL(12,2) NOT NULL DEFAULT 0,
        parent_id INT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Earnings are the mirror of expenses, but categorised by their own list — an FK id,
    // not a name (unlike investment_types), so a rename needs no cascade.
    "CREATE TABLE IF NOT EXISTS earning_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        INDEX ix_household (household_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS earnings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        category_id INT NULL,
        member_id INT NULL,
        recurring_id INT NULL,
        created_by INT NULL,
        date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_household_date (household_id, date),
        INDEX ix_household_cat (household_id, category_id),
        INDEX ix_household_member (household_id, member_id),
        INDEX ix_recurring (recurring_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

// Applied in order after SCHEMA_STATEMENTS, each independently. Re-running is a no-op —
// MySQL errors on a duplicate column/index and the loop logs it and moves on. Append only.
const MIGRATIONS = [
    "ALTER TABLE users ADD COLUMN currency VARCHAR(8) NOT NULL DEFAULT '₹'",
    "ALTER TABLE expenses ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
    "ALTER TABLE investments ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
    "ALTER TABLE investments MODIFY COLUMN type VARCHAR(40) NOT NULL",
    "ALTER TABLE recurring ADD COLUMN kind VARCHAR(20) NOT NULL DEFAULT 'expense'",
    "ALTER TABLE recurring ADD COLUMN type VARCHAR(40) NULL",
    // v6 — investments paginate with ORDER BY date DESC; ix_household alone forced a filesort.
    "ALTER TABLE investments ADD INDEX ix_household_date (household_id, date)",
    "ALTER TABLE investments DROP INDEX ix_household",
    "ALTER TABLE categories ADD COLUMN budget DECIMAL(12,2) NOT NULL DEFAULT 0",
    "ALTER TABLE investment_types ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0",
    // v8 — recurring earnings. Only bites on databases where `earnings` was created by the
    // first cut of this table, before it carried the recurring FK.
    "ALTER TABLE earnings ADD COLUMN recurring_id INT NULL, ADD INDEX ix_recurring (recurring_id)",
    // v9 — expense sub-categories. One level: a row with a parent_id can never be a parent
    // itself. No index — a household has at most 100 categories and they load as one set.
    "ALTER TABLE categories ADD COLUMN parent_id INT NULL",
    // v10 — every "how many entries per category/type" count grouped on a column that
    // ix_household_date doesn't hold, so MySQL read the household's whole table and then
    // sorted it: measured on 20k expenses, EXPLAIN reported key=NULL, rows=19785. Two of
    // those counts feed the profile drawer, which renders on EVERY page. Adding the grouped
    // column makes them covering index scans.
    // ADD INDEX is ONLINE on InnoDB (MySQL 5.6+/MariaDB 10.0+) — reads and writes continue.
    "ALTER TABLE expenses ADD INDEX ix_household_cat (household_id, category_id)",
    "ALTER TABLE earnings ADD INDEX ix_household_cat (household_id, category_id)",
    "ALTER TABLE investments ADD INDEX ix_household_type (household_id, type)",
    // v11 — split bills: a prepaid lump sum posted as equal monthly shares. NULL means the
    // item repeats forever, which is every row that existed before this column.
    "ALTER TABLE recurring ADD COLUMN end_date DATE NULL",
    // v12 — shared ledgers. `created_by` is the author of an entry, which is what decides who
    // may edit it later; NULL means the row predates sharing and only the ledger owner may
    // touch it. `member_id` reaches earnings and investments so one "who?" filter can span all
    // three ledgers — expenses have had it since the beginning. The (household, member) indexes
    // are what keep that filter from re-reading the household's whole table.
    "ALTER TABLE expenses    ADD COLUMN created_by INT NULL",
    "ALTER TABLE earnings    ADD COLUMN created_by INT NULL, ADD COLUMN member_id INT NULL",
    "ALTER TABLE investments ADD COLUMN created_by INT NULL, ADD COLUMN member_id INT NULL",
    "ALTER TABLE recurring   ADD COLUMN created_by INT NULL, ADD COLUMN member_id INT NULL",
    "ALTER TABLE members     ADD COLUMN user_id INT NULL",
    "ALTER TABLE expenses    ADD INDEX ix_household_member (household_id, member_id)",
    "ALTER TABLE earnings    ADD INDEX ix_household_member (household_id, member_id)",
    "ALTER TABLE investments ADD INDEX ix_household_member (household_id, member_id)",
    // The Add screen pre-selects the category you used last, via
    // `ORDER BY id DESC LIMIT 1`. Neither (household_id, date) nor (household_id, category_id)
    // can produce that order, so MySQL read every expense the household owned and sorted the
    // lot to keep one row — on the app's home page, the only unbounded sort left in it.
    "ALTER TABLE expenses ADD INDEX ix_household_recent (household_id, id)",
    // v13 — a split bill has to be editable, and editing one means knowing where it began.
    // `next_date` cannot answer that: the sweep advances it with every share it posts, so by
    // the second month the original date is gone. Only splits set this; a plain recurring item
    // leaves it NULL, which is also what every row that predates this column holds.
    "ALTER TABLE recurring ADD COLUMN start_date DATE NULL",
    // The bill as it was actually paid. `amount` is the monthly share and stays authoritative
    // for what gets posted; this is the figure the household typed. Reconstructing it as
    // share x months loses the rounding — 19,000 over 12 comes back as 18,999.96 — and a
    // number nobody entered reads as a bug when the dialog is reopened.
    "ALTER TABLE recurring ADD COLUMN total_amount DECIMAL(12,2) NULL",
    // v16 — how money is written belongs to the ledger, not to whoever is reading it. A
    // household keeps one set of books: two people sharing it must not see the same row as
    // ₹1,00,000 and $100,000. `users.currency` stays where it is, unread, so a rollback to the
    // previous release still finds the column it expects.
    "ALTER TABLE households ADD COLUMN currency VARCHAR(8) NOT NULL DEFAULT '₹'",
    "ALTER TABLE households ADD COLUMN number_format VARCHAR(12) NOT NULL DEFAULT 'indian'",
    // v18 — which palette a person reads the ledger in. Their own, not the household's:
    // is_dark already worked that way, and the pair together is the whole choice. An
    // unknown value renders as 'organic', so this can never leave a page unstyled.
    "ALTER TABLE users ADD COLUMN theme VARCHAR(16) NOT NULL DEFAULT 'organic'",
    // v19 — investment types get the two things expense categories always had: somewhere to
    // nest, and a figure to aim at. "Target" rather than "budget" because the number means the
    // opposite thing — a budget is a ceiling you would rather stay under, a target is a floor.
    "ALTER TABLE investment_types ADD COLUMN target DECIMAL(12,2) NOT NULL DEFAULT 0",
    "ALTER TABLE investment_types ADD COLUMN parent_id INT NULL",
];

// Bump alongside any change to SCHEMA_STATEMENTS/MIGRATIONS. Its presence in data/ is what
// makes the bootstrap skip itself after the first request. Named here rather than inline so
// --preflight can report the exact file the running code looks for.
const SCHEMA_SENTINEL = '.schema-ok-v19';

// A ledger is a household; these are the only two roles it has. The owner is whoever created
// it — they can edit every entry, invite people and remove them. Everyone else edits their own.
const ROLE_OWNER  = 'owner';
const ROLE_MEMBER = 'member';

// How long a join link stays alive, and how many people one ledger may hold.
const INVITE_TTL_MINUTES  = 30;
const HOUSEHOLD_USERS_MAX = 10;

const DEFAULT_INVESTMENT_TYPES = ['SIP', 'Stocks', 'FD-RD', 'Gold', 'PPF-EPF', 'Other'];

const DEFAULT_EARNING_CATEGORIES = ['Salary', 'Interest', 'Other'];

const DEFAULT_CATEGORIES = [
    ['Groceries', 'shopping-cart'], ['Rent', 'home'], ['Utilities', 'zap'],
    ['Dining Out', 'utensils'], ['Transport', 'car'], ['Health', 'heart-pulse'],
    ['Shopping', 'shopping-bag'], ['EMI / Loans', 'landmark'], ['Subscriptions', 'repeat'],
    ['Credit Card Bill', 'credit-card'], ['Entertainment', 'film'], ['Education', 'book-open'],
    ['Other', 'more-horizontal'],
];

class UserErr extends Exception {}

// ────────────────────────────────────────────────────────────────────
// SQLite dialect
//
// SCHEMA_STATEMENTS above stays the single source of truth and stays written in MySQL. This
// translates it for SQLite rather than keeping a second copy of fourteen tables in sync by
// hand — a duplicate set is how the two dialects silently drift apart.
//
// The MySQL path never calls any of this, so translating cannot regress the web app.
// ────────────────────────────────────────────────────────────────────

// Split a CREATE TABLE body on top-level commas only. A naive explode(',') would cut
// DECIMAL(12,2), ENUM('monthly','quarterly') and INDEX ix (household_id, date) in half.
function splitTopLevel(string $body): array {
    $parts = [''];
    $depth = 0;
    $quote = '';
    foreach (str_split($body) as $ch) {
        if ($quote !== '') {
            if ($ch === $quote) $quote = '';
        } elseif ($ch === "'" || $ch === '"') {
            $quote = $ch;
        } elseif ($ch === '(') {
            $depth++;
        } elseif ($ch === ')') {
            $depth--;
        } elseif ($ch === ',' && $depth === 0) {
            $parts[] = '';
            continue;
        }
        $parts[array_key_last($parts)] .= $ch;
    }
    return array_values(array_filter(array_map('trim', $parts), fn($p) => $p !== ''));
}

// MySQL column type -> SQLite. Order matters: the AUTO_INCREMENT form has to be rewritten
// before the bare INT rule can reach it.
function sqliteColumn(string $def): string {
    return preg_replace(
        [
            '/\bINT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b/i',
            '/\bTINYINT\(\d+\)/i',
            '/\bINT\s+UNSIGNED\b/i',
            '/\b(?:VAR)?CHAR\(\d+\)/i',
            '/\bDECIMAL\(\d+\s*,\s*\d+\)/i',
            '/\bENUM\s*\([^)]*\)/i',
            '/\bINT\b/i',
        ],
        [
            'INTEGER PRIMARY KEY AUTOINCREMENT',
            'INTEGER',
            'INTEGER',
            'TEXT',
            'NUMERIC',   // ponytail: float affinity, not exact decimal — see roundMoney()
            'TEXT',      // frequency: the app already validates the three allowed values
            'INTEGER',
        ],
        $def
    );
}

// Parse SCHEMA_STATEMENTS into [table => ['cols' => [name => full def], 'indexes' => [sql]]].
// Used both to create a fresh database and to reconcile an existing one after an app update.
function sqliteSchema(): array {
    $out = [];
    foreach (SCHEMA_STATEMENTS as $sql) {
        if (!preg_match('/CREATE TABLE IF NOT EXISTS\s+(\w+)\s*\((.*)\)\s*ENGINE=/is', $sql, $m)) {
            throw new RuntimeException('sqliteSchema: unparsable DDL: ' . substr($sql, 0, 60));
        }
        [$table, $body] = [$m[1], $m[2]];
        $cols = $idx = [];
        foreach (splitTopLevel($body) as $part) {
            // MySQL index names are scoped to their table; SQLite's share one namespace for the
            // whole database. Six tables declare ix_household, so the table name has to go in.
            if (preg_match('/^INDEX\s+(\w+)\s*\((.+)\)$/is', $part, $i)) {
                $idx[] = "CREATE INDEX IF NOT EXISTS {$table}_{$i[1]} ON $table ({$i[2]})";
            } elseif (preg_match('/^UNIQUE\s+KEY\s+\w+\s*\((.+)\)$/is', $part, $u)) {
                $cols[] = "UNIQUE ({$u[1]})";      // keep inline: it is a constraint, not an index
            } else {
                $name = strtolower(strtok(trim($part), " \t\n("));
                $cols[$name] = sqliteColumn($part);
            }
        }
        $out[$table] = ['cols' => $cols, 'indexes' => $idx];
    }
    return $out;
}

// Bring a SQLite database up to SCHEMA_STATEMENTS. A fresh file gets everything; an existing
// one — an Android install whose data survived an app update — gets whatever columns and
// indexes it is missing. This replaces the MIGRATIONS ladder on SQLite entirely: the desired
// shape is declared once, so a future schema change needs no new migration entry here.
//
// ponytail: adds only. SQLite cannot drop or retype a column without a table rebuild, and
// nothing in this app's history has needed to — if that changes, rebuild via the
// create-copy-drop-rename dance rather than extending this.
function sqliteSync(PDO $db): void {
    foreach (sqliteSchema() as $table => $spec) {
        $db->exec("CREATE TABLE IF NOT EXISTS $table (" . implode(",\n", $spec['cols']) . ")");
        $have = array_column($db->query("PRAGMA table_info($table)")->fetchAll(), 'name');
        $have = array_map('strtolower', $have);
        foreach ($spec['cols'] as $name => $def) {
            // Numeric key = a UNIQUE(...) constraint, which ADD COLUMN cannot express.
            // It only matters on a fresh table, where CREATE TABLE above already applied it.
            if (is_int($name) || in_array($name, $have, true)) continue;
            $db->exec("ALTER TABLE $table ADD COLUMN $def");
        }
        foreach ($spec['indexes'] as $sql) $db->exec($sql);
    }
}

// ────────────────────────────────────────────────────────────────────
// DB bootstrap
// ────────────────────────────────────────────────────────────────────
function makeDb(array $cfg): PDO {
    $driver = $cfg['db']['driver'] ?? 'mysql';
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if ($driver === 'sqlite') {
        $path = $cfg['db']['path'];
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0755, true);
        $db = new PDO('sqlite:' . $path, null, null, $opts);
        // WAL keeps a read during the nightly backup from blocking the write that posts a
        // recurring item. foreign_keys is OFF by default in SQLite and the schema declares
        // them. busy_timeout stops a concurrent writer from failing instantly.
        $db->exec('PRAGMA journal_mode = WAL');
        $db->exec('PRAGMA foreign_keys = ON');
        $db->exec('PRAGMA busy_timeout = 5000');

        // No sentinel gate here. sqliteSync() is a handful of PRAGMA reads against a local
        // file with no network in front of it, and running it every request is what makes an
        // app update that adds a column just work.
        sqliteSync($db);
        return $db;
    }

    $dsn = "mysql:host={$cfg['db']['host']};dbname={$cfg['db']['name']};charset=utf8mb4";
    $db = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], $opts);
    // Schema/migration bootstrap runs once, then a sentinel file skips it on every subsequent
    // request. Delete the sentinel to force a re-run after schema changes.
    $sentinel = __DIR__ . '/data/' . SCHEMA_SENTINEL;
    if (!file_exists($sentinel)) {
        foreach (SCHEMA_STATEMENTS as $sql) $db->exec($sql);
        foreach (MIGRATIONS as $sql) {
            try { $db->exec($sql); }
            catch (PDOException $e) { error_log('[migrate] ' . $sql . ' — ' . $e->getMessage()); }
        }
        // Backfill defaults for households that predate a lookup table. Keyed on "has no rows
        // at all", so a household that deliberately deleted one down to a smaller set is left alone.
        foreach ([
            ['investment_types',   DEFAULT_INVESTMENT_TYPES],
            ['earning_categories', DEFAULT_EARNING_CATEGORIES],
        ] as [$table, $defaults]) {
            $orphaned = $db->query(
                "SELECT h.id FROM households h WHERE NOT EXISTS (SELECT 1 FROM $table t WHERE t.household_id = h.id)"
            )->fetchAll(PDO::FETCH_COLUMN);
            if (!$orphaned) continue;
            // Guarded insert, not a plain one: two requests can both find the sentinel missing
            // right after a deploy and run this block concurrently, which would hand the
            // household two of every default.
            $ins = $db->prepare(
                "INSERT INTO $table (household_id, name)
                 SELECT ?, ? FROM DUAL
                 WHERE NOT EXISTS (SELECT 1 FROM (SELECT 1 FROM $table WHERE household_id = ? AND name = ?) x)"
            );
            foreach ($orphaned as $hid) {
                foreach ($defaults as $t) $ins->execute([(int)$hid, $t, (int)$hid, $t]);
            }
        }
        // v13 backfill — existing splits lost their start date to the sweep. The earliest
        // share it posted is that date; a split that has not posted yet still holds it in
        // next_date. Guarded on IS NULL so this never overwrites a real one.
        $db->exec(
            "UPDATE recurring r SET r.start_date = COALESCE(
                 (SELECT MIN(e.date) FROM (SELECT date, recurring_id FROM expenses) e
                  WHERE e.recurring_id = r.id),
                 r.next_date)
             WHERE r.end_date IS NOT NULL AND r.start_date IS NULL"
        );
        // v17 backfill — ledgers still carrying a name nobody typed. Both of these were
        // defaults, so renaming them takes away no one's choice, and leaving them means two
        // people who share a ledger see two identical rows in the picker. A household that was
        // deliberately renamed is untouched, because its name is not in this list.
        $db->exec(
            "UPDATE households h
             JOIN (SELECT hu.household_id, MIN(hu.user_id) uid FROM household_users hu
                   WHERE hu.role = '" . ROLE_OWNER . "' GROUP BY hu.household_id) o
               ON o.household_id = h.id
             JOIN users u ON u.id = o.uid
             SET h.name = SUBSTRING_INDEX(TRIM(u.name), ' ', 1)
             WHERE h.name IN ('My Household', 'Personal')
               AND TRIM(u.name) <> '' AND SUBSTRING_INDEX(TRIM(u.name), ' ', 1) <> ''"
        );
        // v16 backfill — the owner's symbol becomes the ledger's, so the person who set it up
        // sees exactly what they saw before the upgrade. Guarded on the default so a ledger
        // whose currency was already set here is never overwritten.
        $db->exec(
            "UPDATE households h
             JOIN (SELECT hu.household_id, MIN(hu.user_id) uid FROM household_users hu
                   WHERE hu.role = '" . ROLE_OWNER . "' GROUP BY hu.household_id) o
               ON o.household_id = h.id
             JOIN users u ON u.id = o.uid
             SET h.currency = u.currency
             WHERE h.currency = '₹' AND u.currency <> '₹'"
        );
        // No record of the original figure for splits that predate the column, so the sum of
        // the shares is the closest true statement available.
        $db->exec(
            "UPDATE recurring SET total_amount = ROUND(amount * (
                 (YEAR(end_date) - YEAR(start_date)) * 12 + (MONTH(end_date) - MONTH(start_date)) + 1
             ), 2)
             WHERE end_date IS NOT NULL AND start_date IS NOT NULL AND total_amount IS NULL"
        );
        // v12 backfill — before sharing existed, a ledger had exactly one user and that user
        // owned it. Lowest id wins the owner seat, so re-running this can never hand a
        // household a second owner. NOT EXISTS rather than a plain insert because two requests
        // can both find the sentinel missing right after a deploy; the unique key would reject
        // the loser, but throwing inside the bootstrap path is worse than skipping.
        $db->exec(
            "INSERT INTO household_users (household_id, user_id, role)
             SELECT u.household_id, u.id,
                    CASE WHEN u.id = (SELECT MIN(u2.id) FROM users u2 WHERE u2.household_id = u.household_id)
                         THEN '" . ROLE_OWNER . "' ELSE '" . ROLE_MEMBER . "' END
             FROM users u
             WHERE NOT EXISTS (
                 SELECT 1 FROM (SELECT household_id, user_id FROM household_users) x
                 WHERE x.household_id = u.household_id AND x.user_id = u.id
             )"
        );
        if (!is_dir(dirname($sentinel))) @mkdir(dirname($sentinel), 0755, true);
        // Without this sentinel the whole schema + migration set re-runs on EVERY request,
        // which is slow and floods the error log. A silent @touch failure would hide that,
        // so say so plainly — `--preflight` checks the same thing before you deploy.
        if (!@touch($sentinel)) {
            error_log('[migrate] CANNOT WRITE ' . $sentinel
                . ' — schema bootstrap will re-run on every request. Make data/ writable.');
        }
        // Sweep superseded sentinels so data/ holds exactly one and it always names the live
        // schema. Left to pile up, a plain sort puts v10 before v8 and anything reading the
        // directory reports the wrong version.
        foreach (glob(dirname($sentinel) . '/.schema-ok-*') ?: [] as $old) {
            if ($old !== $sentinel) @unlink($old);
        }
    }
    return $db;
}

// ────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Indian digit grouping (lakh/crore): the last three digits, then pairs.
// 10,00,000.00 — not number_format()'s 1,000,000.00. Applies to every money value in the
// app; percentages, CSS widths and byte counts deliberately keep plain number_format().
// The two ways to group digits. "indian" puts the first comma after three and every two
// after that (10,00,000); "world" groups in threes throughout (1,000,000). Stored per user.
const NUM_STYLES = ['indian', 'world'];

// Group by the reader's convention. Kept as one function so every amount on every screen
// answers to a single rule — fmt() and fmtShort() are its only callers by design.
function groupNumber(float $amount, int $decimals = 2, ?string $style = null): string {
    $style = $style ?? ($_SESSION['numfmt'] ?? 'indian');
    return $style === 'world' ? number_format($amount, $decimals) : groupIndian($amount, $decimals);
}

function groupIndian(float $amount, int $decimals = 2): string {
    $n   = number_format(abs($amount), $decimals, '.', '');
    $int = $n; $dec = '';
    if ($decimals > 0) { [$int, $frac] = explode('.', $n); $dec = '.' . $frac; }
    if (strlen($int) > 3) {
        $int = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($int, 0, -3)) . ',' . substr($int, -3);
    }
    return ($amount < 0 ? '-' : '') . $int . $dec;
}
function fmt(float $amount): string { return ($_SESSION['currency'] ?? '₹') . groupNumber($amount); }
// Rounded to the rupee — for summary tiles, where paise are noise and three figures
// share one row. Detail rows keep full precision via fmt().
function fmtShort(float $amount): string { return ($_SESSION['currency'] ?? '₹') . groupNumber($amount, 0); }
// Most redirect targets come from a `back` form field, which is attacker-controllable.
// Keep them same-site: must be a root-relative path ("/x"), never a protocol-relative
// "//host" or absolute URL, and never contain CR/LF (header injection).
function safeRedirectTarget(string $to): string {
    if ($to === '' || $to[0] !== '/' || str_starts_with($to, '//') || preg_match('/[\r\n]/', $to)) {
        return '/';
    }
    return $to;
}
function redirect(string $to): never { header('Location: ' . safeRedirectTarget($to)); exit; }
function today(): string { return date('Y-m-d'); }
// The wall clock, in the app's timezone, as the databases spell it. Every NOW() and CURDATE()
// this app used to send is now one of these two, computed here and bound as a parameter —
// which is what keeps MySQL and SQLite from each answering with their own idea of the time.
function nowSql(): string { return date('Y-m-d H:i:s'); }

// ────────────────────────────────────────────────────────────────────
// Date extraction, per dialect. Only the GROUP BY / SELECT cases live here: anything that
// needed the *current* time is computed in PHP instead (see nowSql()/today()). Passed the
// PDO so the driver answers for itself and nothing has to track global state.
// ────────────────────────────────────────────────────────────────────
function isSqlite(PDO $db): bool { return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'; }
function sqlYm(PDO $db, string $col): string {
    return isSqlite($db) ? "strftime('%Y-%m', $col)" : "DATE_FORMAT($col, '%Y-%m')";
}
function sqlYear(PDO $db, string $col): string {
    return isSqlite($db) ? "CAST(strftime('%Y', $col) AS INTEGER)" : "YEAR($col)";
}
function sqlMonth(PDO $db, string $col): string {
    return isSqlite($db) ? "CAST(strftime('%m', $col) AS INTEGER)" : "MONTH($col)";
}
function sqlDay(PDO $db, string $col): string {
    return isSqlite($db) ? "CAST(strftime('%d', $col) AS INTEGER)" : "DAY($col)";
}
// Characters, not bytes. Not interchangeable with a bare LENGTH(): MySQL's counts bytes, so
// LENGTH('₹') is 3 there and 1 in SQLite — which is the whole reason this is checked at all.
function sqlCharLen(PDO $db, string $col): string {
    return isSqlite($db) ? "LENGTH($col)" : "CHAR_LENGTH($col)";
}

// SQLite stores DECIMAL as float, MySQL as exact decimal, so a SUM() of many rows can come
// back a hair off on Android and match to the paisa on the web. Every aggregate the app
// shows goes through here so the two agree.
// ponytail: rounding at read. If a balance ever disagrees with the rows above it, the real
// fix is storing paise as INTEGER — which changes both dialects and every read/write path.
function roundMoney(float $n): float { return round($n, 2); }

// Shared hosts, CDNs and load balancers commonly terminate TLS at a proxy: PHP then sees a
// plain HTTP request with $_SERVER['HTTPS'] unset, and the real scheme only in
// X-Forwarded-Proto. Missing that would silently drop the `Secure` flag from the session
// cookie and emit http:// canonical/OG URLs on an https site.
// Trusting the header errs safe — a spoofed value only makes the cookie stricter, never laxer.
function isHttps(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    // May arrive as a comma-separated chain ("https, http"); the client-facing hop is first.
    $proto = strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
    if ($proto === 'https') return true;
    return (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function originUrl(): string {
    return (isHttps() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

// Month arithmetic that clamps to the end of the target month instead of overflowing into
// the next one. PHP's "+1 month" from Jan 31 lands on Mar 3 — which for a monthly recurring
// item means February is never posted at all and the due day drifts from then on. Anchoring
// on the 1st and re-applying the day is the standard fix.
// Known ceiling: the day only ever ratchets down (Jan 31 → Feb 28 → Mar 28, not Mar 31),
// because the sweep iterates off the previous posting rather than an original anchor date.
// Every month still gets exactly one posting, which is the part that matters.
function addMonths(string $dateStr, int $months): string {
    $d     = new DateTimeImmutable($dateStr);
    $first = $d->modify('first day of this month')->add(new DateInterval("P{$months}M"));
    $day   = min((int)$d->format('j'), (int)$first->format('t'));
    return $first->setDate((int)$first->format('Y'), (int)$first->format('n'), $day)->format('Y-m-d');
}

function advanceDate(string $dateStr, string $freq): string {
    return addMonths($dateStr, match ($freq) { 'quarterly' => 3, 'yearly' => 12, default => 1 });
}

// Split bill: one prepaid lump sum (a year of health insurance, two years of hosting) turned
// into an equal monthly share plus the date the last share falls on. The row it produces is
// an ordinary monthly recurring item — the sweep back-fills every month from `start` to today
// and then stops itself at `end`, so nothing special happens at post time.
// Returns [perMonth, endDate].
// ponytail: an equal split can't always hit the total exactly — 10000/12 is 833.33, which
// leaves 4 paise on the table. The dialog previews `per × months` before saving so the
// shortfall is visible rather than silent; carrying the residue on the final instalment
// would need the original total stored on the row.
// How many shares a split covers, counting both ends — the inverse of splitPlan's end date.
// Compares year and month only, never days: addMonths() clamps a 31st onto a short month, so
// 31 Jan + 11 months is 31 Dec but 31 Jan + 1 month is 28 Feb, and any day-level arithmetic
// would report the wrong length for exactly the splits that start at a month end.
function monthsSpan(string $start, string $end): int {
    $a = new DateTimeImmutable($start);
    $b = new DateTimeImmutable($end);
    $n = ((int)$b->format('Y') - (int)$a->format('Y')) * 12
       + ((int)$b->format('n') - (int)$a->format('n'));
    return max(1, $n + 1);
}

function splitPlan(float $total, int $months, string $start): array {
    if ($months < 2 || $months > 120) throw new UserErr('Split length must be between 2 and 120 months.');
    $per = round($total / $months, 2);
    if ($per <= 0) throw new UserErr('That amount is too small to split over ' . $months . ' months.');
    return [$per, addMonths($start, $months - 1)];
}

function currentUser(PDO $db): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $s = $db->prepare("SELECT * FROM users WHERE id = ?");
    $s->execute([$_SESSION['user_id']]);
    return $s->fetch() ?: null;
}

function clientIp(array $cfg): string {
    if (!empty($cfg['trust_cloudflare_ip']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ────────────────────────────────────────────────────────────────────
// CSRF (double-submit via session token; hash_equals-compared)
// ────────────────────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrfInput(): string {
    return '<input type="hidden" name="_csrf" value="' . h(csrfToken()) . '">';
}
// Raw token for JS attribute contexts (e.g. onclick="askConfirm({csrf:'...'})").
function csrfJs(): string { return h(csrfToken()); }
function csrfCheck(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(400); exit('Bad CSRF token — refresh and retry.');
    }
}

// ────────────────────────────────────────────────────────────────────
// Rate limiting — one bucket per (ip, key). Not perfectly atomic under
// concurrent hits; a couple of extra requests can leak through. Fine
// for a household-scale app. ponytail: swap to Redis if scale hurts.
// ────────────────────────────────────────────────────────────────────
function rateLimit(PDO $db, array $cfg, string $key, int $limit, int $windowSeconds): void {
    $bucket   = clientIp($cfg) . ':' . $key;
    $now      = time();
    $windowEnd = $now + $windowSeconds;

    // Atomic upsert: if the row's window has expired, reset it in one statement;
    // otherwise increment hits. Beats SELECT + REPLACE which lets two concurrent
    // requests at a window boundary both reset the counter.
    // The only upsert in the app, so the two dialects sit here rather than behind a helper.
    // SQLite spells the incoming row `excluded` and has no IF(); the logic is identical.
    $sql = isSqlite($db)
        ? "INSERT INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)
           ON CONFLICT(bucket) DO UPDATE SET
              hits       = CASE WHEN rate_limits.window_end < excluded.window_end THEN 1 ELSE rate_limits.hits + 1 END,
              window_end = CASE WHEN rate_limits.window_end < excluded.window_end THEN excluded.window_end ELSE rate_limits.window_end END"
        : "INSERT INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)
           ON DUPLICATE KEY UPDATE
              hits       = IF(window_end < VALUES(window_end), 1, hits + 1),
              window_end = IF(window_end < VALUES(window_end), VALUES(window_end), window_end)";
    $db->prepare($sql)->execute([$bucket, $windowEnd]);

    $sel = $db->prepare("SELECT hits, window_end FROM rate_limits WHERE bucket = ?");
    $sel->execute([$bucket]);
    $row = $sel->fetch();
    if ($row && (int)$row['hits'] > $limit) {
        $retry = max(1, (int)$row['window_end'] - $now);
        http_response_code(429);
        header("Retry-After: $retry");
        header("Content-Type: text/plain; charset=utf-8");
        exit("Rate limit exceeded. Retry in {$retry}s.");
    }
}

// ────────────────────────────────────────────────────────────────────
// Validation — throws UserErr on failure (rendered as an error toast).
// ────────────────────────────────────────────────────────────────────
function parseAmount(string $raw, array $cfg): float {
    $raw = trim($raw);
    if ($raw === '' || !preg_match('/^\d{1,10}(\.\d{1,2})?$/', $raw)) {
        throw new UserErr('Invalid amount.');
    }
    $a = (float)$raw;
    if ($a <= 0) throw new UserErr('Amount must be positive.');
    if ($a > $cfg['limits']['amount_max']) throw new UserErr('Amount too large.');
    return round($a, 2);
}
function requireStr(string $raw, int $max, string $label): string {
    $s = trim($raw);
    if ($s === '')            throw new UserErr("$label is required.");
    if (mb_strlen($s) > $max) throw new UserErr("$label too long (max $max).");
    return $s;
}
function optionalStr(?string $raw, int $max, string $label): string {
    $s = trim((string)$raw);
    if (mb_strlen($s) > $max) throw new UserErr("$label too long (max $max).");
    return $s;
}
function requireDate(string $raw, string $label): string {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
    if (!$d || $d->format('Y-m-d') !== $raw) throw new UserErr("$label must be a valid date.");
    return $raw;
}
function assertUnderLimit(PDO $db, string $sqlCount, array $params, int $max, string $label): void {
    $s = $db->prepare($sqlCount);
    $s->execute($params);
    if ((int)$s->fetchColumn() >= $max) throw new UserErr("$label limit reached ($max).");
}

// A currency symbol is exactly one character. The old rule was "at most eight", which is how
// "₹tt" got saved and then prefixed every amount in the app. mb_strlen, never strlen — ₹ is
// three bytes and one symbol, so a byte count would reject the app's own default.
// \p{C} and \p{Z} catch what trim() cannot: a lone control character, or a non-breaking space.
function parseCurrency(string $raw): string {
    $s = trim($raw);
    if ($s === '') throw new UserErr('Currency symbol is required.');
    if (mb_strlen($s, 'UTF-8') !== 1 || preg_match('/^[\p{C}\p{Z}]$/u', $s)) {
        throw new UserErr('Use a single currency symbol, like ₹, $ or €.');
    }
    return $s;
}

// Row ids arrive from <select> fields, which are attacker-controllable: without this a
// crafted POST could attach an entry to another household's category or member, and the
// LEFT JOINs that render lists would then show that household's name back. Returns the id
// only if it belongs here; anything else (0, missing, foreign) degrades to NULL — the same
// "uncategorised" state a deleted category leaves behind.
// $table is a caller-supplied literal, never user input — it cannot be bound as a parameter.
function ownedId(PDO $db, string $table, int $hid, int $id): ?int {
    if ($id <= 0) return null;
    $s = $db->prepare("SELECT id FROM $table WHERE id = ? AND household_id = ?");
    $s->execute([$id, $hid]);
    return $s->fetchColumn() ? $id : null;
}

// ────────────────────────────────────────────────────────────────────
// Shared ledgers — membership, roles, and who may edit what
// ────────────────────────────────────────────────────────────────────

// Every ledger this user belongs to, in the order they joined. Drives the sign-in picker and
// the drawer's switcher, so it carries the name they will read and the role they hold.
function ledgersFor(PDO $db, int $uid): array {
    $s = $db->prepare(
        "SELECT h.id, h.name, hu.role,
                (SELECT COUNT(*) FROM household_users x WHERE x.household_id = h.id) AS people
         FROM household_users hu
         JOIN households h ON h.id = hu.household_id
         WHERE hu.user_id = ?
         ORDER BY hu.joined_at, h.id"
    );
    $s->execute([$uid]);
    return $s->fetchAll();
}

// Role and ledger name in one round trip, for the request bootstrap. Separate from roleIn()
// because that one is asked a yes/no question by callers who do not need the name; this one
// runs on every authed request, where a second query for a string would be a waste.
function activeLedger(PDO $db, int $hid, int $uid): ?array {
    $s = $db->prepare(
        "SELECT hu.role, h.name, h.currency, h.number_format FROM household_users hu
         JOIN households h ON h.id = hu.household_id
         WHERE hu.household_id = ? AND hu.user_id = ?"
    );
    $s->execute([$hid, $uid]);
    return $s->fetch() ?: null;
}

// This user's role in one ledger, or NULL if they are not in it. NULL is the signal that an
// active-ledger id has gone stale — they were removed from it while still signed in — and
// every request re-asks, because `users.household_id` is a cache of a fact that can change.
function roleIn(PDO $db, int $hid, int $uid): ?string {
    $s = $db->prepare("SELECT role FROM household_users WHERE household_id = ? AND user_id = ?");
    $s->execute([$hid, $uid]);
    $r = $s->fetchColumn();
    return $r === false ? null : (string)$r;
}

// The whole permission model, in one expression. The owner edits anything in their ledger;
// everyone else edits only what they added. A row with no author predates sharing, so it
// falls to the owner — which is exactly right, since back then they were the only user.
function canEditRow(?int $createdBy, int $uid, string $role): bool {
    return $role === ROLE_OWNER || ($createdBy !== null && $createdBy === $uid);
}

// Fetch a row for editing or refuse to. Household scope and author scope live together here
// so a new handler cannot accidentally keep one and drop the other — every update and delete
// of an entry goes through this, and `--preflight` fails the build if one stops doing so.
function requireEditable(PDO $db, string $table, int $hid, int $id, int $uid, string $role): array {
    $s = $db->prepare("SELECT * FROM $table WHERE id = ? AND household_id = ?");
    $s->execute([$id, $hid]);
    $row = $s->fetch();
    if (!$row) throw new UserErr('That entry no longer exists.');
    $author = $row['created_by'] === null ? null : (int)$row['created_by'];
    if (!canEditRow($author, $uid, $role)) {
        throw new UserErr('Only whoever added this — or the ledger owner — can change it.');
    }
    return $row;
}

// Whose name an entry may be filed under. The owner may name anyone in the household — they
// keep the books, and half of what they log (a card bill, a school fee, a partner's SIP) is
// somebody else's spend regardless of whether that somebody has a login of their own.
// Everyone else files as themselves, always: whatever member_id their form posts, the entry
// lands under their own linked row.
//
// $current is the value the row already holds. An edit that leaves it alone is always allowed,
// so correcting the amount on an entry cannot silently re-attribute it.
function attributableMember(PDO $db, int $hid, int $uid, string $role, int $memberId, ?int $current = null): ?int {
    if ($current !== null && $memberId === $current) return $memberId;
    if ($role !== ROLE_OWNER) {
        // linkMember first: it is idempotent, and it heals anyone who joined before linked
        // rows existed — without it their entries would silently fall to "no member".
        linkMember($db, $hid, $uid);
        $s = $db->prepare("SELECT id FROM members WHERE household_id = ? AND user_id = ?");
        $s->execute([$hid, $uid]);
        return ($mid = (int)$s->fetchColumn()) > 0 ? $mid : null;
    }
    if ($memberId <= 0) return null;
    // Still scoped to the household: an id from someone else's ledger is not a name here.
    return ownedId($db, 'members', $hid, $memberId);
}

// The same rule, for building a picker: the ids you may choose. The owner gets every name in
// the household. For everyone else this is at most their own row — the pickers see a single
// choice and collapse to nothing, which is the point: members just file as "me".
function attributableIds(array $mems, int $uid, string $role): array {
    $out = [];
    foreach ($mems as $m) {
        $own = isset($m['user_id']) && $m['user_id'] !== null && (int)$m['user_id'] === $uid;
        if ($role === ROLE_OWNER || $own) $out[] = (int)$m['id'];
    }
    return $out;
}

// ── What a member row is called, from the viewer's side of the ledger ─────────
//
// "Me" is a pronoun, not a name: it has to resolve to whoever is reading. Praveen calls his
// own row "Me", which is right in his login and a lie in his wife's — she was reading his
// word for himself. So the row belonging to whoever is signed in always renders as "Me",
// whatever it is stored as, and a row belonging to somebody else who signs in renders with
// their own account name: theirs to spell, and the only name that means the same thing to
// both of them.
//
// A row nobody signs in as keeps whatever the household stored. That is the whole point of
// those rows — "Appa" is a person in this ledger long after Google has decided he is Rajesh
// Kumar — and nothing about them is relative to who is looking.
function memberLabel(array $m, int $uid): string {
    $linked = isset($m['user_id']) && $m['user_id'] !== null;
    if ($linked && (int)$m['user_id'] === $uid) return 'Me';
    if ($linked && trim((string)($m['user_name'] ?? '')) !== '') {
        // First name only: these render in pills and dropdowns, where a full Google name wraps.
        return ledgerNameFor((string)$m['user_name']);
    }
    return (string)$m['name'];
}

// Every member of a household, ready to render. Each row carries the `label` this viewer
// should see, and the viewer's own row sorts first so "Me" sits in the same place for
// everybody rather than wherever their row happens to have been created.
function membersFor(PDO $db, int $hid, int $uid): array {
    $s = $db->prepare(
        "SELECT m.id, m.name, m.user_id, u.name AS user_name
         FROM members m LEFT JOIN users u ON u.id = m.user_id
         WHERE m.household_id = ? ORDER BY m.id"
    );
    $s->execute([$hid]);
    $mems = $s->fetchAll();
    $mine = fn(array $m): int => (isset($m['user_id']) && (int)$m['user_id'] === $uid) ? 0 : 1;
    foreach ($mems as &$m) $m['label'] = memberLabel($m, $uid);
    unset($m);
    // Stable since PHP 8.0, so everyone else keeps their creation order behind you.
    usort($mems, fn(array $a, array $b): int => $mine($a) <=> $mine($b));
    return $mems;
}

// The view-side twin of requireEditable: same rule, no query, so a list row can hide the two
// controls the server would refuse anyway. Never the only check — the server still decides.
function mayEdit(array $row, array $user): bool {
    return canEditRow(
        ($row['created_by'] ?? null) === null ? null : (int)$row['created_by'],
        (int)$user['id'],
        (string)($user['role'] ?? ROLE_MEMBER)
    );
}

// The default name for somebody's own ledger: the first word of what Google calls them.
// Falls back to "Personal" for a name that is empty or has no word characters in it, which
// is rare but not impossible — a display name can be an emoji.
function ledgerNameFor(string $userName): string {
    $first = preg_split('/\s+/', trim($userName))[0] ?? '';
    $first = trim($first);
    return $first === '' ? 'Personal' : mb_substr($first, 0, 80);
}

// Mint a join link. Minting supersedes any unused predecessor, so a link the owner shared
// and then thought better of stops working the moment they generate a fresh one.
function mintInvite(PDO $db, int $hid, int $uid): string {
    $db->prepare("DELETE FROM invites WHERE household_id = ? AND used_at IS NULL")->execute([$hid]);
    $token = bin2hex(random_bytes(16));
    // Written by PHP's clock, and read back below against PHP's clock. It used to be MySQL's
    // on both sides, which worked only because nothing else compared the two.
    $expires = date('Y-m-d H:i:s', time() + INVITE_TTL_MINUTES * 60);
    $db->prepare(
        "INSERT INTO invites (household_id, token, created_by, expires_at)
         VALUES (?, ?, ?, ?)"
    )->execute([$hid, $token, $uid, $expires]);
    return $token;
}

// A token that is real, unspent and unexpired — anything else is NULL. One lookup, so
// "wrong link", "already used" and "too late" can never drift apart into three answers.
function liveInvite(PDO $db, string $token): ?array {
    if (!preg_match('/^[0-9a-f]{32}$/', $token)) return null;
    $s = $db->prepare("SELECT * FROM invites WHERE token = ? AND used_at IS NULL AND expires_at > ?");
    $s->execute([$token, nowSql()]);
    return $s->fetch() ?: null;
}

// Spend a token and put the user in the ledger. The UPDATE is the lock: `used_at IS NULL` in
// its WHERE means two people opening the same link race for one row and exactly one wins,
// with no transaction and no table lock. Returns why it failed, for the caller to phrase.
function redeemInvite(PDO $db, string $token, int $uid): array {
    $inv = liveInvite($db, $token);
    if (!$inv) return ['status' => 'invalid', 'household_id' => 0];
    $hid = (int)$inv['household_id'];
    $out = fn(string $s) => ['status' => $s, 'household_id' => $hid];
    if (roleIn($db, $hid, $uid) !== null) return $out('already');

    $n = $db->prepare("SELECT COUNT(*) FROM household_users WHERE household_id = ?");
    $n->execute([$hid]);
    if ((int)$n->fetchColumn() >= HOUSEHOLD_USERS_MAX) return $out('full');

    $claim = $db->prepare("UPDATE invites SET used_at = ?, used_by = ? WHERE id = ? AND used_at IS NULL");
    $claim->execute([nowSql(), $uid, (int)$inv['id']]);
    if ($claim->rowCount() !== 1) return $out('invalid');

    $db->prepare("INSERT INTO household_users (household_id, user_id, role) VALUES (?, ?, ?)")
       ->execute([$hid, $uid, ROLE_MEMBER]);
    linkMember($db, $hid, $uid);
    return $out('ok');
}

// Point a user at one of their ledgers. The membership check is the whole job: the id arrives
// in a POST field or a query string, so it is attacker-controlled, and `users.household_id` is
// what every scoped query in the app trusts.
function switchLedger(PDO $db, int $uid, int $hid): bool {
    if (roleIn($db, $hid, $uid) === null) return false;
    $db->prepare("UPDATE users SET household_id = ? WHERE id = ?")->execute([$hid, $uid]);
    return true;
}

// Where a freshly signed-in person lands. A join link waiting in the session is spent first —
// they clicked it wanting the shared ledger, so that is where they go, picker or no picker.
function afterSignIn(PDO $db, int $uid): string {
    $token = trim((string)($_SESSION['pending_invite'] ?? ''));
    unset($_SESSION['pending_invite']);
    if ($token !== '') {
        $r = redeemInvite($db, $token, $uid);
        if ($r['status'] === 'ok' || $r['status'] === 'already') {
            switchLedger($db, $uid, (int)$r['household_id']);
            flash('success', $r['status'] === 'ok' ? "You're in." : 'You were already in that ledger.');
            return '/';
        }
        flash('error', $r['status'] === 'full'
            ? 'That ledger is full — it already has ' . HOUSEHOLD_USERS_MAX . ' people.'
            : 'That invite link has expired or been used. Ask for a fresh one.');
    }
    return count(ledgersFor($db, $uid)) > 1 ? '/ledgers' : '/';
}

// Give a user a spender label in this ledger, so "who spent it" and "who may edit it" name
// the same human. Claims a same-named unclaimed label first — a household usually writes its
// people down before it invites them, and two rows called Arjun would be worse than the cap.
function linkMember(PDO $db, int $hid, int $uid): void {
    $s = $db->prepare("SELECT id FROM members WHERE household_id = ? AND user_id = ?");
    $s->execute([$hid, $uid]);
    if ($s->fetchColumn()) return;

    $u = $db->prepare("SELECT name FROM users WHERE id = ?");
    $u->execute([$uid]);
    $name = mb_substr(trim((string)($u->fetchColumn() ?: '')) ?: 'Member', 0, 60);

    $m = $db->prepare("SELECT id FROM members WHERE household_id = ? AND user_id IS NULL AND name = ? LIMIT 1");
    $m->execute([$hid, $name]);
    if ($mid = (int)($m->fetchColumn() ?: 0)) {
        $db->prepare("UPDATE members SET user_id = ? WHERE id = ? AND household_id = ?")->execute([$uid, $mid, $hid]);
        return;
    }
    // Deliberately not capped by members_total_max: that cap keeps the label list readable,
    // and a person who has actually joined the ledger has more claim to a row than the cap has.
    $db->prepare("INSERT INTO members (household_id, name, user_id) VALUES (?, ?, ?)")
       ->execute([$hid, $name, $uid]);
}

// The "who?" filter as a SQL fragment plus its bindings — 0 means everyone. One definition,
// so History, Earn, Invest and Year cannot drift apart on what filtering by a person means.
// The (household_id, member_id) index added in v12 is what keeps it from re-reading the table.
function whoWhere(int $who, string $alias = ''): array {
    if ($who <= 0) return ['', []];
    return [' AND ' . ($alias !== '' ? $alias . '.' : '') . 'member_id = ?', [$who]];
}

// Rolling window of $n whole months ending with the month that contains $todayYmd.
// Returns [startDate, endDateExclusive, ['Y-m', …]] — the exclusive end keeps callers on
// `date >= ? AND date < ?`, which uses the (household_id, date) index. Anchoring on the 1st
// keeps the month arithmetic honest (a naive "-1 month" from the 31st lands in the month after next).
function rollingMonths(string $todayYmd, int $n): array {
    $first = (new DateTimeImmutable($todayYmd))->modify('first day of this month');
    $start = $first->modify('-' . ($n - 1) . ' months');
    $keys  = [];
    for ($i = 0, $c = $start; $i < $n; $i++, $c = $c->modify('+1 month')) $keys[] = $c->format('Y-m');
    return [$start->format('Y-m-d'), $first->modify('+1 month')->format('Y-m-d'), $keys];
}

// ────────────────────────────────────────────────────────────────────
// Expense sub-categories. One level deep: `categories.parent_id` points at another category
// in the same household, and a row that has a parent can never be given children.
// ────────────────────────────────────────────────────────────────────

// Display order for every category picker: each parent immediately followed by its children.
// Adds a `depth` key (0 or 1). A child whose parent has vanished is shown at top level rather
// than dropped — losing a category from a picker is worse than showing it unindented.
function categoryTree(array $cats): array {
    $topIds = [];
    $kids   = [];
    foreach ($cats as $c) {
        if (empty($c['parent_id'])) $topIds[(int)$c['id']] = true;
        else                        $kids[(int)$c['parent_id']][] = $c;
    }
    $out = [];
    foreach ($cats as $c) {
        if (!empty($c['parent_id'])) continue;
        $c['depth'] = 0; $out[] = $c;
        foreach ($kids[(int)$c['id']] ?? [] as $k) { $k['depth'] = 1; $out[] = $k; }
    }
    foreach ($kids as $pid => $list) {
        if (isset($topIds[$pid])) continue;
        foreach ($list as $k) { $k['depth'] = 0; $out[] = $k; }
    }
    return $out;
}

// Fold per-category spend into parent buckets. Each input row carries the category's own
// columns (cid/name/icon/budget) plus its parent's (pid/pname/picon/pbudget) and `amt`.
// Child spend lands on the parent's bar — that is the whole point of the feature — and the
// children come back as sub-lines. A parent that ALSO has direct spend of its own gets a
// "Direct" sub-line, so the lines under a bar always add up to it.
// Returns buckets sorted by amount desc, children likewise.
function rollupCategories(array $rows): array {
    $out = [];
    foreach ($rows as $r) {
        $isChild = !empty($r['pid']);
        // Both a parent's own row and its children's rows must land on ONE key, or a parent
        // with direct spend and children would render as two separate bars.
        $key = 'c' . (int)($isChild ? $r['pid'] : ($r['cid'] ?? 0));
        if (!isset($out[$key])) {
            $out[$key] = [
                'name'     => (string)($isChild ? $r['pname'] : $r['name']),
                'icon'     => (string)($isChild ? $r['picon'] : $r['icon']),
                'budget'   => (float)($isChild ? $r['pbudget'] : $r['budget']),
                'amt'      => 0.0,
                'children' => [],
            ];
        }
        $out[$key]['amt'] += (float)$r['amt'];
        if ($isChild) $out[$key]['children'][] = ['name' => (string)$r['name'], 'amt' => (float)$r['amt']];
    }
    foreach ($out as $k => $b) {
        if (!$b['children']) continue;
        usort($out[$k]['children'], fn($a, $c) => $c['amt'] <=> $a['amt']);
        $direct = $b['amt'] - array_sum(array_column($b['children'], 'amt'));
        if ($direct > 0.004) $out[$k]['children'][] = ['name' => 'Direct', 'amt' => round($direct, 2)];
    }
    uasort($out, fn($a, $b) => $b['amt'] <=> $a['amt']);
    return array_values($out);
}

// What the History and Year breakdowns label "Uncategorised": an expense with no category at
// all, OR one pointing at a category that has since been deleted. Both look identical in the
// UI, so every tool that counts, files or clears that bucket must use this one predicate —
// otherwise the count on screen disagrees with what the button actually touches.
// Takes the household id twice: once for the row, once for the sub-select.
function uncategorisedWhere(): string {
    return "household_id = ? AND (category_id IS NULL OR category_id NOT IN
            (SELECT id FROM categories WHERE household_id = ?))";
}

// Confirms the submitted investment type belongs to this household. Rejects free-text.
function validInvestmentType(PDO $db, int $hid, string $type): string {
    $s = $db->prepare("SELECT name FROM investment_types WHERE household_id = ? AND name = ?");
    $s->execute([$hid, $type]);
    if ($row = $s->fetchColumn()) return (string)$row;
    throw new UserErr('Unknown investment type — pick one from the list (edit types in the profile drawer).');
}

// Budgets are optional and 0 means "no budget", so this can't reuse parseAmount (which
// rejects 0). Blank input is also 0 — clearing the field removes the budget. $label is what
// the error calls it: investment types set a "target", which is the same field with the
// opposite meaning.
function parseBudget(string $raw, array $cfg, string $label = 'Budget'): float {
    $raw = trim($raw);
    if ($raw === '') return 0.0;
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $raw)) throw new UserErr('Invalid ' . strtolower($label) . '.');
    $b = round((float)$raw, 2);
    if ($b > $cfg['limits']['amount_max']) throw new UserErr($label . ' too large.');
    return $b;
}

// Archiving is per investment *type*. `investments.type` stores the type name (not an FK —
// renames cascade in /investment-types/update), so membership is a name match. Types are
// capped at 30 per household, so callers can safely splat this into an IN() list.
function archivedTypeNames(PDO $db, int $hid): array {
    $s = $db->prepare("SELECT name FROM investment_types WHERE household_id = ? AND archived = 1");
    $s->execute([$hid]);
    return $s->fetchAll(PDO::FETCH_COLUMN);
}

// The investments twin of uncategorisedWhere(). `investments.type` is a name, not an FK, so
// a row can name a type this household no longer has — deleting one is refused while it is in
// use, but a restore from an older backup can land entries whose type went away. Those are
// invisible in every by-type view until something offers to re-file them.
function unknownTypeWhere(): string {
    return "household_id = ? AND type NOT IN (SELECT name FROM investment_types WHERE household_id = ?)";
}

// Fold per-type money into parent buckets — the investment twin of rollupCategories(), but
// keyed on names because that is what an investment stores. $rows are [type, n, amt]; $types
// is the household's type rows by name. A child only folds into a parent that is itself in
// $visible, so the archived/active filter can never pull a hidden name onto the screen.
function rollupTypes(array $rows, array $types, array $visible): array {
    $out = [];
    foreach ($rows as $r) {
        $name  = (string)$r['type'];
        $me    = $types[$name] ?? null;
        $par   = null;
        if ($me && !empty($me['parent_id'])) {
            foreach ($types as $t) {
                if ((int)$t['id'] === (int)$me['parent_id'] && isset($visible[$t['name']])) { $par = $t; break; }
            }
        }
        $key = $par ? (string)$par['name'] : $name;
        if (!isset($out[$key])) {
            $out[$key] = [
                'name'     => $key,
                'target'   => (float)($par['target'] ?? $me['target'] ?? 0),
                'amt'      => 0.0,
                'n'        => 0,
                'children' => [],
            ];
        }
        $out[$key]['amt'] += (float)$r['amt'];
        $out[$key]['n']   += (int)$r['n'];
        if ($par) $out[$key]['children'][] = ['name' => $name, 'amt' => (float)$r['amt']];
    }
    foreach ($out as $k => $b) {
        if ($b['children']) usort($out[$k]['children'], fn($a, $c) => $c['amt'] <=> $a['amt']);
    }
    usort($out, fn($a, $b) => $b['amt'] <=> $a['amt']);
    return array_values($out);
}

// Names are the join key between investment_types and investments, so two types sharing one
// would make "which type is this?" unanswerable — the rollup, the archive filter and the
// rename cascade all match on the string.
function typeNameTaken(PDO $db, int $hid, string $name, int $exceptId = 0): bool {
    $s = $db->prepare("SELECT COUNT(*) FROM investment_types WHERE household_id = ? AND name = ? AND id <> ?");
    $s->execute([$hid, $name, $exceptId]);
    return (int)$s->fetchColumn() > 0;
}

// Type-scoping clause for an investment list: returns [sqlFragment, params] to append to
// "WHERE household_id = ?". The nothing-archived case is the trap — "archived" must match
// zero rows, not fall through to an unfiltered list of everything.
function investmentFilterSql(string $filter, array $archivedNames): array {
    if ($filter === 'all')      return ['', []];
    if (!$archivedNames)        return $filter === 'archived' ? [' AND 1 = 0', []] : ['', []];
    $in = implode(',', array_fill(0, count($archivedNames), '?'));
    return [$filter === 'archived' ? " AND type IN ($in)" : " AND type NOT IN ($in)", $archivedNames];
}

// ────────────────────────────────────────────────────────────────────
// Recurring sweep (runs from web on every authed request AND from cron).
// Both call this — idempotent, only fires when next_date has passed.
// ────────────────────────────────────────────────────────────────────
function sweepRecurring(PDO $db, int $hid): void {
    $today = today();
    // Cheap guard: single indexed lookup. The common case (nothing due) exits here
    // without opening the prepare/fetch/update path on every authed request.
    // The end_date clause is what stops a finished split bill from being re-read on every
    // request for the rest of time: its next_date stays in the past forever, so without this
    // it would keep matching the probe and posting nothing.
    $due = "household_id = ? AND next_date <= ? AND (end_date IS NULL OR next_date <= end_date)";
    $probe = $db->prepare("SELECT 1 FROM recurring WHERE $due LIMIT 1");
    $probe->execute([$hid, $today]);
    if (!$probe->fetchColumn()) return;

    $rows = $db->prepare("SELECT * FROM recurring WHERE $due");
    $rows->execute([$hid, $today]);
    // The sweep runs with no signed-in user — from a cron job as often as from a request — so
    // the posted rows inherit the recurring item's own author and member. That keeps them
    // editable by whoever set the item up, instead of falling to the owner as authorless rows.
    $insExp = $db->prepare(
        "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date, recurring_id, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insInv = $db->prepare(
        "INSERT INTO investments (household_id, name, amount, type, member_id, date, recurring_id, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insErn = $db->prepare(
        "INSERT INTO earnings (household_id, name, amount, category_id, member_id, date, recurring_id, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $upd = $db->prepare("UPDATE recurring SET next_date = ? WHERE id = ?");
    foreach ($rows->fetchAll() as $r) {
        $nd   = $r['next_date'];
        $kind = $r['kind'] ?? 'expense';
        $end  = $r['end_date'] ?? null;   // split bills only; NULL = repeats forever
        $note = ($end === null ? '[recurring] ' : '[split] ') . $r['name'];
        // Cap iterations — a stale/bad next_date shouldn't insert years of catch-up rows
        // synchronously in one request. 120 = 10 years of monthly / 30 years of quarterly.
        for ($i = 0; $i < 120 && $nd <= $today && ($end === null || $nd <= $end); $i++) {
            if ($kind === 'investment') {
                $insInv->execute([$hid, $r['name'], $r['amount'], (string)($r['type'] ?? 'Other'),
                                  $r['member_id'], $nd, (int)$r['id'], $r['created_by']]);
            } elseif ($kind === 'earning') {
                // `recurring.category_id` is read against whichever category table the kind
                // implies — expense categories here, earning categories there. The POST
                // handlers re-validate it per kind on every save, so it can't cross over.
                $insErn->execute([$hid, $r['name'], $r['amount'], $r['category_id'],
                                  $r['member_id'], $nd, (int)$r['id'], $r['created_by']]);
            } else {
                $insExp->execute([$hid, $r['amount'], $r['category_id'], $r['member_id'],
                                  $note, $nd, (int)$r['id'], $r['created_by']]);
            }
            $nd = advanceDate($nd, $r['frequency']);
        }
        $upd->execute([$nd, $r['id']]);
    }
}

// ────────────────────────────────────────────────────────────────────
// Bootstrap a household for a new Google user.
// ────────────────────────────────────────────────────────────────────
function bootstrapHousehold(
    PDO $db, string $name, string $email, string $googleSub, ?string $ledgerName = null
): int {
    $db->beginTransaction();
    try {
        // Named after its owner, because this string is what tells ledgers apart in the
        // picker — and a fixed default cannot. Everyone's first ledger used to be called
        // "Personal", so the moment two people shared one they saw two rows reading "Personal".
        //
        // $ledgerName overrides that guess, and only the local build passes one: it is the one
        // place a person is asked outright, so their answer beats anything derived from a name.
        $ledger = ($ledgerName !== null && trim($ledgerName) !== '')
            ? mb_substr(trim($ledgerName), 0, 80)
            : ledgerNameFor($name);
        $db->prepare("INSERT INTO households (name) VALUES (?)")->execute([$ledger]);
        $hid = (int)$db->lastInsertId();
        $db->prepare("INSERT INTO users (household_id, google_sub, email, name) VALUES (?, ?, ?, ?)")
           ->execute([$hid, $googleSub, $email, $name]);
        $uid = (int)$db->lastInsertId();
        $db->prepare("INSERT INTO household_users (household_id, user_id, role) VALUES (?, ?, ?)")
           ->execute([$hid, $uid, ROLE_OWNER]);
        // Their spender label carries their own name and is linked to them from the start, so
        // if this ledger later becomes the shared one it already reads correctly next to guests.
        $db->prepare("INSERT INTO members (household_id, name, user_id) VALUES (?, ?, ?)")
           ->execute([$hid, mb_substr(trim($name) ?: 'Me', 0, 60), $uid]);
        $ins = $db->prepare("INSERT INTO categories (household_id, name, icon, is_custom) VALUES (?, ?, ?, 0)");
        foreach (DEFAULT_CATEGORIES as [$n, $i]) $ins->execute([$hid, $n, $i]);
        $insIt = $db->prepare("INSERT INTO investment_types (household_id, name) VALUES (?, ?)");
        foreach (DEFAULT_INVESTMENT_TYPES as $t) $insIt->execute([$hid, $t]);
        $insEc = $db->prepare("INSERT INTO earning_categories (household_id, name) VALUES (?, ?)");
        foreach (DEFAULT_EARNING_CATEGORIES as $c) $insEc->execute([$hid, $c]);
        $db->commit();
        return $uid;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

// ────────────────────────────────────────────────────────────────────
// Google ID token verification via Google's tokeninfo endpoint.
// ponytail: `tokeninfo` is Google's simplest verifier — one HTTPS call,
// no dependencies. Google's own docs prefer local JWKS verify for lower
// latency and offline resilience. If you want that later, swap in
// firebase/php-jwt via composer and cache https://www.googleapis.com/oauth2/v3/certs.
// ────────────────────────────────────────────────────────────────────
function verifyGoogleIdToken(string $idToken, string $expectedClientId): ?array {
    if ($idToken === '' || strlen($idToken) > 4096) return null;
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $resp = @file_get_contents(
        'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken),
        false, $ctx
    );
    if (!$resp) return null;
    $j = json_decode($resp, true);
    if (!is_array($j) || !empty($j['error'])) return null;

    // Audience must match the client id we minted the token for.
    if (($j['aud'] ?? '') !== $expectedClientId) return null;
    // Issuer must be Google.
    $iss = $j['iss'] ?? '';
    if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') return null;
    // Not expired.
    if ((int)($j['exp'] ?? 0) < time()) return null;
    // Email must be verified — tokeninfo returns the string "true".
    $ev = $j['email_verified'] ?? null;
    if ($ev !== true && $ev !== 'true') return null;
    // Subject required.
    if (empty($j['sub'])) return null;

    return $j;
}

// Dev-mode stub sign-in — active only while google_client_id is still the placeholder.
// Lets you click through the app without configuring Google OAuth locally.
function isDevStubActive(string $clientId): bool {
    return str_ends_with($clientId, 'YOUR_CLIENT_ID.apps.googleusercontent.com');
}

// ────────────────────────────────────────────────────────────────────
// Flash message (survives one PRG redirect, then cleared).
// ────────────────────────────────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function consumeFlash(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    return $f;
}
