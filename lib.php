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

    // A device that cannot sign into Google — a watch, a work laptop you will not put a
    // personal account on, a tablet in the kitchen. The row IS the pairing: it is minted
    // unclaimed, carrying a six-digit code the website shows and the device types back.
    // Redeeming clears the code and hands over `token`.
    //
    // `scope` is the whole safety of this. There are two, and they are not close:
    //
    //   api   a bearer token for /api/* — read a summary, add an expense, nothing else.
    //         What a watch gets. Cannot open a session or reach a single HTML page.
    //   full  a browser session, identical to having signed in with Google. Everything.
    //
    // Minting defaults to the narrow one. A pairing flow that handed out full access because
    // someone forgot an argument is the failure worth designing against here.
    //
    // The code travels website -> device, never the other way. A device that could mint its
    // own code and ask a signed-in human to approve it is the device-code phishing pattern:
    // the attacker's watch gets the ledger. This direction has no such shape — the code is
    // born inside an authenticated session and is worthless to anyone who cannot read it.
    "CREATE TABLE IF NOT EXISTS device_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        user_id INT NOT NULL,
        token CHAR(64) NOT NULL,
        label VARCHAR(60) NOT NULL DEFAULT 'Device',
        scope VARCHAR(10) NOT NULL DEFAULT 'api',
        pair_code CHAR(6) NULL,
        pair_expires_at DATETIME NULL,
        claimed_at DATETIME NULL,
        last_seen_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_token (token),
        INDEX ix_household (household_id),
        INDEX ix_pair_code (pair_code)
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
    // v20 — paired devices. The table itself is in SCHEMA_STATEMENTS, so a fresh database
    // gets `scope` with it and this line errors harmlessly. It is here for the databases that
    // saw the table land one deploy before the column did.
    "ALTER TABLE device_tokens ADD COLUMN scope VARCHAR(10) NOT NULL DEFAULT 'api'",
];

// Bump alongside any change to SCHEMA_STATEMENTS/MIGRATIONS. Its presence in data/ is what
// makes the bootstrap skip itself after the first request. Named here rather than inline so
// --preflight can report the exact file the running code looks for.
const SCHEMA_SENTINEL = '.schema-ok-v21';

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

// Dark-theme variable overrides — lifted verbatim from the prototype (THEME_DARK_VARS).
const THEME_DARK_VARS = '--color-bg:#201e1d;--color-surface:#2b2721;--color-text:#f3e9d8;'
  . '--color-divider:color-mix(in srgb, #f3e9d8 18%, transparent);'
  . '--color-neutral-100:#2c2822;--color-neutral-200:#363028;--color-neutral-300:#453d31;--color-neutral-400:#5a5040;--color-neutral-500:#786c56;--color-neutral-600:#9c8f76;--color-neutral-700:#bfb29a;--color-neutral-800:#dcd0ba;--color-neutral-900:#f3e9d8;'
  . '--color-accent:#e0864c;--color-accent-100:#3a2a1c;--color-accent-200:#4d3320;--color-accent-300:#6b4324;--color-accent-400:#95592c;--color-accent-500:#c06f35;--color-accent-600:#d97f42;--color-accent-700:#e79968;--color-accent-800:#f0b78c;--color-accent-900:#f8d7b8;'
  . '--color-accent-2:#93a86e;--color-accent-2-100:#2a301f;--color-accent-2-200:#333b26;--color-accent-2-300:#414c2f;--color-accent-2-400:#57643d;--color-accent-2-500:#71804f;--color-accent-2-600:#8a9863;--color-accent-2-700:#a8b884;--color-accent-2-800:#c4d1a8;--color-accent-2-900:#e2e8d0;'
  . '--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);'
  // Not a token: tells the browser to draw native widgets (date-picker calendar
  // icon, the open select list, scrollbars) in dark, instead of light-on-dark-invisible.
  // The CLOSED select is fully custom (.select in layout()); only the popup stays native.
  . 'color-scheme:dark;';

// The three palettes, each in both modes. Organic is the one the design system ships, so its
// light values ARE design-tokens/styles.css :root — nothing to repeat here but the status-bar
// colour. Harbor and Plum are generated in OKLCH on the SAME lightness scale as Organic
// (light 0.969→0.290, dark 0.290→0.930) and the same chroma arc, only the hues move. That is
// what keeps every component working unchanged: a rule that reads --color-accent-700 gets the
// same visual weight whichever palette is on. Retune a hue here, never a single step.
//
//   'sw' is what the picker draws — the four colours a palette is recognised by, and the
//   first of them is also the mobile status-bar colour.
const THEMES = [
    'organic' => [
        'name' => 'Organic', 'note' => 'Terracotta and sage on cream',
        'light' => [
            'vars' => '--theme-color:#f5ead8;',
            'sw' => ['bg' => '#f5ead8', 'surface' => '#ebddc5', 'accent' => '#c67139', 'accent2' => '#7a8a5e'],
        ],
        'dark' => [
            'vars' => THEME_DARK_VARS . '--theme-color:#201e1d;',
            'sw' => ['bg' => '#201e1d', 'surface' => '#2b2721', 'accent' => '#e0864c', 'accent2' => '#93a86e'],
        ],
    ],
    'harbor' => [
        'name' => 'Harbor', 'note' => 'Deep azure and teal on cool paper',
        'light' => [
            'vars' => '--color-bg:oklch(0.972 0.006 250);--color-surface:oklch(0.938 0.011 250);--color-text:oklch(0.250 0.022 258);--color-accent:oklch(0.560 0.155 258);--color-accent-2:oklch(0.600 0.095 196);--color-divider:color-mix(in srgb, var(--color-text) 16%, transparent);--color-neutral-100:oklch(0.969 0.006 250);--color-neutral-200:oklch(0.930 0.010 250);--color-neutral-300:oklch(0.870 0.013 250);--color-neutral-400:oklch(0.780 0.014 250);--color-neutral-500:oklch(0.680 0.015 250);--color-neutral-600:oklch(0.580 0.014 250);--color-neutral-700:oklch(0.479 0.012 250);--color-neutral-800:oklch(0.381 0.010 250);--color-neutral-900:oklch(0.290 0.006 250);--color-accent-100:oklch(0.969 0.015 258);--color-accent-200:oklch(0.930 0.033 258);--color-accent-300:oklch(0.870 0.063 258);--color-accent-400:oklch(0.780 0.111 258);--color-accent-500:oklch(0.680 0.155 258);--color-accent-600:oklch(0.580 0.148 258);--color-accent-700:oklch(0.479 0.130 258);--color-accent-800:oklch(0.381 0.100 258);--color-accent-900:oklch(0.290 0.065 258);--color-accent-2-100:oklch(0.969 0.044 196);--color-accent-2-200:oklch(0.930 0.061 196);--color-accent-2-300:oklch(0.870 0.074 196);--color-accent-2-400:oklch(0.780 0.083 196);--color-accent-2-500:oklch(0.680 0.086 196);--color-accent-2-600:oklch(0.580 0.083 196);--color-accent-2-700:oklch(0.479 0.074 196);--color-accent-2-800:oklch(0.381 0.061 196);--color-accent-2-900:oklch(0.290 0.044 196);--shadow-sm:0 1px 2px color-mix(in srgb, var(--color-neutral-900) 14%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, var(--color-neutral-900) 16%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, var(--color-neutral-900) 22%, transparent);--theme-color:#f3f6fa;color-scheme:light;',
            'sw' => ['bg' => '#f3f6fa', 'surface' => '#e5ebf2', 'accent' => '#3372cd', 'accent2' => '#209293'],
        ],
        'dark' => [
            'vars' => '--color-bg:oklch(0.235 0.016 255);--color-surface:oklch(0.278 0.021 255);--color-text:oklch(0.935 0.013 250);--color-accent:oklch(0.720 0.135 258);--color-accent-2:oklch(0.740 0.095 196);--color-divider:color-mix(in srgb, var(--color-text) 18%, transparent);--color-neutral-100:oklch(0.290 0.007 250);--color-neutral-200:oklch(0.340 0.010 250);--color-neutral-300:oklch(0.400 0.014 250);--color-neutral-400:oklch(0.480 0.017 250);--color-neutral-500:oklch(0.570 0.022 250);--color-neutral-600:oklch(0.660 0.023 250);--color-neutral-700:oklch(0.760 0.022 250);--color-neutral-800:oklch(0.850 0.020 250);--color-neutral-900:oklch(0.930 0.015 250);--color-accent-100:oklch(0.290 0.039 258);--color-accent-200:oklch(0.340 0.055 258);--color-accent-300:oklch(0.400 0.082 258);--color-accent-400:oklch(0.480 0.114 258);--color-accent-500:oklch(0.570 0.144 258);--color-accent-600:oklch(0.660 0.155 258);--color-accent-700:oklch(0.760 0.122 258);--color-accent-800:oklch(0.850 0.074 258);--color-accent-900:oklch(0.930 0.033 258);--color-accent-2-100:oklch(0.290 0.037 196);--color-accent-2-200:oklch(0.340 0.046 196);--color-accent-2-300:oklch(0.400 0.060 196);--color-accent-2-400:oklch(0.480 0.076 196);--color-accent-2-500:oklch(0.570 0.091 196);--color-accent-2-600:oklch(0.660 0.095 196);--color-accent-2-700:oklch(0.760 0.091 196);--color-accent-2-800:oklch(0.850 0.071 196);--color-accent-2-900:oklch(0.930 0.041 196);--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);--theme-color:#191f26;color-scheme:dark;',
            'sw' => ['bg' => '#191f26', 'surface' => '#212933', 'accent' => '#6da5f8', 'accent2' => '#57bebe'],
        ],
    ],
    'plum' => [
        'name' => 'Plum', 'note' => 'Berry and emerald on blush',
        'light' => [
            'vars' => '--color-bg:oklch(0.970 0.008 340);--color-surface:oklch(0.935 0.014 340);--color-text:oklch(0.245 0.024 335);--color-accent:oklch(0.560 0.175 348);--color-accent-2:oklch(0.600 0.105 160);--color-divider:color-mix(in srgb, var(--color-text) 16%, transparent);--color-neutral-100:oklch(0.969 0.006 340);--color-neutral-200:oklch(0.930 0.010 340);--color-neutral-300:oklch(0.870 0.013 340);--color-neutral-400:oklch(0.780 0.014 340);--color-neutral-500:oklch(0.680 0.015 340);--color-neutral-600:oklch(0.580 0.014 340);--color-neutral-700:oklch(0.479 0.012 340);--color-neutral-800:oklch(0.381 0.010 340);--color-neutral-900:oklch(0.290 0.006 340);--color-accent-100:oklch(0.969 0.018 348);--color-accent-200:oklch(0.930 0.042 348);--color-accent-300:oklch(0.870 0.083 348);--color-accent-400:oklch(0.780 0.155 348);--color-accent-500:oklch(0.680 0.161 348);--color-accent-600:oklch(0.580 0.154 348);--color-accent-700:oklch(0.479 0.135 348);--color-accent-800:oklch(0.381 0.104 348);--color-accent-900:oklch(0.290 0.068 348);--color-accent-2-100:oklch(0.969 0.041 160);--color-accent-2-200:oklch(0.930 0.056 160);--color-accent-2-300:oklch(0.870 0.068 160);--color-accent-2-400:oklch(0.780 0.077 160);--color-accent-2-500:oklch(0.680 0.079 160);--color-accent-2-600:oklch(0.580 0.077 160);--color-accent-2-700:oklch(0.479 0.068 160);--color-accent-2-800:oklch(0.381 0.056 160);--color-accent-2-900:oklch(0.290 0.041 160);--shadow-sm:0 1px 2px color-mix(in srgb, var(--color-neutral-900) 14%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, var(--color-neutral-900) 16%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, var(--color-neutral-900) 22%, transparent);--theme-color:#f9f3f7;color-scheme:light;',
            'sw' => ['bg' => '#f9f3f7', 'surface' => '#f0e6ec', 'accent' => '#b93e86', 'accent2' => '#3d936a'],
        ],
        'dark' => [
            'vars' => '--color-bg:oklch(0.232 0.019 330);--color-surface:oklch(0.275 0.025 330);--color-text:oklch(0.935 0.013 340);--color-accent:oklch(0.725 0.145 350);--color-accent-2:oklch(0.735 0.105 160);--color-divider:color-mix(in srgb, var(--color-text) 18%, transparent);--color-neutral-100:oklch(0.290 0.008 335);--color-neutral-200:oklch(0.340 0.010 335);--color-neutral-300:oklch(0.400 0.015 335);--color-neutral-400:oklch(0.480 0.018 335);--color-neutral-500:oklch(0.570 0.023 335);--color-neutral-600:oklch(0.660 0.025 335);--color-neutral-700:oklch(0.760 0.023 335);--color-neutral-800:oklch(0.850 0.021 335);--color-neutral-900:oklch(0.930 0.016 335);--color-accent-100:oklch(0.290 0.041 348);--color-accent-200:oklch(0.340 0.058 348);--color-accent-300:oklch(0.400 0.085 348);--color-accent-400:oklch(0.480 0.119 348);--color-accent-500:oklch(0.570 0.150 348);--color-accent-600:oklch(0.660 0.162 348);--color-accent-700:oklch(0.760 0.137 348);--color-accent-800:oklch(0.850 0.098 348);--color-accent-900:oklch(0.930 0.042 348);--color-accent-2-100:oklch(0.290 0.034 160);--color-accent-2-200:oklch(0.340 0.043 160);--color-accent-2-300:oklch(0.400 0.055 160);--color-accent-2-400:oklch(0.480 0.070 160);--color-accent-2-500:oklch(0.570 0.084 160);--color-accent-2-600:oklch(0.660 0.087 160);--color-accent-2-700:oklch(0.760 0.084 160);--color-accent-2-800:oklch(0.850 0.066 160);--color-accent-2-900:oklch(0.930 0.038 160);--shadow-sm:0 1px 2px color-mix(in srgb, #000000 40%, transparent);--shadow-md:0 3px 10px color-mix(in srgb, #000000 45%, transparent);--shadow-lg:0 12px 32px color-mix(in srgb, #000000 55%, transparent);--theme-color:#231a22;color-scheme:dark;',
            'sw' => ['bg' => '#231a22', 'surface' => '#2f232e', 'accent' => '#e87db3', 'accent2' => '#69be92'],
        ],
    ],
];

// ────────────────────────────────────────────────────────────────────
// Resolving a palette for the watch
//
// The Wear app cannot read a stylesheet, and hand-copying three palettes into Kotlin is how
// the two quietly stop matching the first time a hue is retuned here. So the server resolves
// whichever palette the user is on down to plain hex and sends it with every summary — which
// also means a FOURTH palette added above reaches the watch with no app update at all.
// ────────────────────────────────────────────────────────────────────

// OKLCH to sRGB hex. The palettes are authored in OKLCH precisely because it keeps the ramps
// perceptually even, and nothing in PHP converts it.
//
// Verified against the sRGB primaries in --selfcheck, and independently against the app's own
// data: the bg this computes for Harbor and Plum equals the --theme-color each palette already
// hardcodes, which was written by the design tool rather than by this function.
function oklchToHex(float $L, float $C, float $hDeg): string {
    $h = deg2rad($hDeg);
    $a = $C * cos($h);
    $b = $C * sin($h);

    // OKLab to LMS', cubed to LMS.
    $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

    // LMS to linear sRGB.
    $r  =  4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
    $g  = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
    $bl = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

    // Gamma-encode and clamp. Out-of-gamut values are clipped rather than mapped: every colour
    // in this app's palettes is inside sRGB, and a gamut-mapping algorithm to handle ones that
    // are not would be a lot of code for a case that cannot arise.
    $enc = fn(float $c): int => (int)max(0, min(255, round(
        255 * ($c <= 0.0031308 ? 12.92 * $c : 1.055 * pow(max($c, 0.0), 1 / 2.4) - 0.055)
    )));
    return sprintf('#%02x%02x%02x', $enc($r), $enc($g), $enc($bl));
}

// A CSS colour from the palettes, as hex. Organic's dark values are already hex; Harbor's and
// Plum's are OKLCH. Anything else — a color-mix(), a var() — is not a flat colour and returns
// null rather than a guess.
function cssColorToHex(string $value): ?string {
    $value = trim($value);
    if (preg_match('/^#[0-9a-f]{6}$/i', $value)) return strtolower($value);
    if (preg_match('/^oklch\(\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\)$/i', $value, $m)) {
        return oklchToHex((float)$m[1], (float)$m[2], (float)$m[3]);
    }
    return null;
}

// The eight colours the Wear app draws with, for one palette, as hex.
//
// Always the DARK variant, whatever the account is set to. A watch face is on an OLED panel a
// few inches from the eye and often always-on: a light one costs visible battery all day and
// is what every Wear design guideline tells you not to ship. The palette is honoured; the
// light/dark choice is not, deliberately.
function watchPalette(string $themeKey): array {
    $key  = isset(THEMES[$themeKey]) ? $themeKey : 'organic';
    $vars = (string)(THEMES[$key]['dark']['vars'] ?? '');

    $tokens = [];
    foreach (explode(';', $vars) as $pair) {
        if (!str_contains($pair, ':')) continue;
        [$n, $v] = explode(':', $pair, 2);
        $tokens[trim($n)] = trim($v);
    }
    $hex = function (string $name, string $fallback) use ($tokens): string {
        return cssColorToHex($tokens[$name] ?? '') ?? $fallback;
    };

    return [
        'name'       => $key,
        'bg'         => $hex('--color-bg',            '#201e1d'),
        'surface'    => $hex('--color-surface',       '#2b2721'),
        'text'       => $hex('--color-text',          '#f3e9d8'),
        'accent'     => $hex('--color-accent',        '#e0864c'),
        'accentSoft' => $hex('--color-accent-700',    '#e79968'),
        'accent2'    => $hex('--color-accent-2',      '#93a86e'),
        'muted'      => $hex('--color-neutral-600',   '#9c8f76'),
        'track'      => $hex('--color-neutral-300',   '#453d31'),
        // Not a palette token. "Over budget" has to read as wrong in every palette, and the
        // accent ramps are the wrong tool — Plum's accent IS pink.
        'over'       => '#e06c5a',
    ];
}

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
// A POST that carries X-PRG was sent by layout()'s submit handler with fetch(), not by the
// browser navigating. Handing it the target instead of sending it there lets the page replace
// the history entry it is standing on rather than push a second copy of the same screen —
// five saves on one tab used to cost five taps of the system Back button to get out of it.
// The fragment matters here: fetch() strips it from response.url, so #profile only survives
// because it comes back in a header of our own.
function redirect(string $to): never {
    $to = safeRedirectTarget($to);
    if (($_SERVER['HTTP_X_PRG'] ?? '') === '1') {
        header('X-Location: ' . $to);
        http_response_code(204);
        exit;
    }
    header('Location: ' . $to);
    exit;
}
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
// The decision, separated from the exit so it can be asserted. csrfCheck() below ends the
// request, which makes it untestable in-process — and this is the one rule in the app most
// worth a test.
//
// The empty case is rejected explicitly, because hash_equals('', '') is TRUE. It never bit
// while every csrfCheck() sat behind the auth gate: a signed-in session has always minted a
// token by the time it posts anything. The login routes broke that assumption — /pair and
// /signin/app run on a session that may have none — and there a request carrying no cookie and
// no token passed by matching nothing against nothing, which is precisely the forged post the
// check exists to stop.
function csrfValid(string $have, string $sent): bool {
    return $have !== '' && hash_equals($have, $sent);
}

function csrfCheck(): void {
    if (!csrfValid((string)($_SESSION['csrf'] ?? ''), (string)($_POST['_csrf'] ?? ''))) {
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
    //
    // "Expired" is measured against NOW, bound as $now. It used to be measured against the
    // INCOMING window_end — which is now + windowSeconds, and therefore newer than the stored
    // one on every tick of the clock. So the counter reset itself once a second and could
    // never exceed the hits that landed inside a single second: /signin, device pairing and
    // the watch API all had a limiter that did not limit. It read as correct because the
    // dual-driver test made its three calls fast enough to stay inside one second, and it
    // surfaced the day MySQL was slow enough not to.
    $sql = isSqlite($db)
        ? "INSERT INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)
           ON CONFLICT(bucket) DO UPDATE SET
              hits       = CASE WHEN rate_limits.window_end < ? THEN 1 ELSE rate_limits.hits + 1 END,
              window_end = CASE WHEN rate_limits.window_end < ? THEN excluded.window_end ELSE rate_limits.window_end END"
        : "INSERT INTO rate_limits (bucket, hits, window_end) VALUES (?, 1, ?)
           ON DUPLICATE KEY UPDATE
              hits       = IF(window_end < ?, 1, hits + 1),
              window_end = IF(window_end < ?, VALUES(window_end), window_end)";
    $db->prepare($sql)->execute([$bucket, $windowEnd, $now, $now]);

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

// ────────────────────────────────────────────────────────────────────
// Paired devices — the watch, and anything else that cannot sign into Google
//
// A device holds one opaque bearer token and nothing else: no session, no cookie, no CSRF.
// It is scoped to one household and one user, and every API call resolves back through
// deviceFromToken() to exactly the ($hid, $uid, $role) triple a browser request would have,
// so the device can do no more than the human who paired it.
// ────────────────────────────────────────────────────────────────────

// Six digits, ten minutes, one use. Long enough to type on a watch with a voice button,
// short-lived enough that guessing it is not a strategy — and the guess would have to land
// inside the window of a code that a signed-in person is looking at on another screen.
const DEVICE_PAIR_TTL_MINUTES = 10;
const DEVICE_TOKENS_MAX       = 8;

// What a paired device may do. See the device_tokens DDL for why the split exists.
const DEVICE_SCOPE_API  = 'api';    // /api/* only — a watch
const DEVICE_SCOPE_FULL = 'full';   // a browser session, same as signing in
const DEVICE_SCOPES     = [DEVICE_SCOPE_API, DEVICE_SCOPE_FULL];

// Mint an unclaimed device row and return the code to show the user.
//
// Replaces any other unclaimed row for this user first: two live codes on one account means a
// person reading the second one while the first is still redeemable, which is one code too
// many to reason about.
function mintDevicePairing(PDO $db, int $hid, int $uid, string $scope = DEVICE_SCOPE_API): string {
    // Not a cast, a rejection. A typo'd scope silently becoming 'full' is the one way this
    // function can hand out more than the caller asked for.
    if (!in_array($scope, DEVICE_SCOPES, true)) throw new UserErr('Unknown device type.');
    $db->prepare("DELETE FROM device_tokens WHERE user_id = ? AND claimed_at IS NULL")->execute([$uid]);

    // The code must be unique among LIVE codes, not for all time — reuse after expiry is fine
    // and is what keeps six digits enough. Retry rather than trust randomness: a collision is
    // vanishingly rare and silently catastrophic, because redeemDevicePairing() takes the
    // first match and would hand one household's token to another household's watch.
    $live = $db->prepare("SELECT 1 FROM device_tokens WHERE pair_code = ? AND claimed_at IS NULL AND pair_expires_at > ?");
    $code = '';
    for ($try = 0; $try < 8; $try++) {
        $candidate = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $live->execute([$candidate, nowSql()]);
        if (!$live->fetchColumn()) { $code = $candidate; break; }
    }
    if ($code === '') throw new UserErr('Could not generate a pairing code. Try again in a minute.');

    $db->prepare(
        "INSERT INTO device_tokens (household_id, user_id, token, label, scope, pair_code, pair_expires_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    )->execute([
        $hid, $uid,
        bin2hex(random_bytes(32)),
        $scope === DEVICE_SCOPE_FULL ? 'Device' : 'Watch',
        $scope,
        $code,
        // PHP's clock on both sides of the comparison, per the app's no-DB-clock rule.
        date('Y-m-d H:i:s', time() + DEVICE_PAIR_TTL_MINUTES * 60),
        nowSql(),
    ]);
    return $code;
}

// The code a user is currently looking at, or null. Drives the drawer's panel, which has to
// keep showing the same code across a reload rather than minting a fresh one each paint.
function liveDevicePairing(PDO $db, int $uid): ?array {
    $s = $db->prepare(
        "SELECT pair_code, pair_expires_at, scope FROM device_tokens
         WHERE user_id = ? AND claimed_at IS NULL AND pair_expires_at > ?"
    );
    $s->execute([$uid, nowSql()]);
    return $s->fetch() ?: null;
}

// Spend a code and hand back the bearer token. Like redeemInvite(), the UPDATE is the lock:
// `claimed_at IS NULL` in the WHERE means two devices racing one code produce exactly one
// winner, with no transaction.
function redeemDevicePairing(PDO $db, string $code, string $label, string $expectScope): ?array {
    if (!preg_match('/^\d{6}$/', $code)) return null;
    if (!in_array($expectScope, DEVICE_SCOPES, true)) return null;
    // The scope is part of the LOOKUP, not a check the caller does afterwards.
    //
    // It used to be the caller's job, and that was wrong in a way that only showed up when
    // someone typed a watch code into /pair: the row was claimed, then rejected. The code was
    // spent, the watch never got its token, and the only symptom was "it says the code is
    // wrong" about a code that had been correct a moment earlier. A mismatched scope now
    // means no row matches, so there is nothing to claim and nothing to burn.
    //
    // Exact match, not a hierarchy. 'full' is a superset of 'api' in what it permits, but a
    // code minted for a browser should not silently hand a watch more than a watch needs.
    $s = $db->prepare(
        "SELECT id, household_id, user_id, token, scope FROM device_tokens
         WHERE pair_code = ? AND scope = ? AND claimed_at IS NULL AND pair_expires_at > ?"
    );
    $s->execute([$code, $expectScope, nowSql()]);
    $row = $s->fetch();
    if (!$row) return null;

    // Clearing pair_code is not tidiness. Leaving it set keeps the row matching the SELECT
    // above forever, and the only thing standing between a replayed code and a second token
    // would be claimed_at — one condition instead of two, on the app's most sensitive row.
    $claim = $db->prepare(
        "UPDATE device_tokens SET claimed_at = ?, pair_code = NULL, label = ?
         WHERE id = ? AND claimed_at IS NULL"
    );
    $claim->execute([nowSql(), mb_substr(trim($label) ?: 'Watch', 0, 60), (int)$row['id']]);
    if ($claim->rowCount() !== 1) return null;

    // Oldest first, so pairing a sixth device retires the one nobody has used, not this one.
    $extra = $db->prepare(
        "SELECT id FROM device_tokens WHERE user_id = ? AND claimed_at IS NOT NULL ORDER BY claimed_at DESC"
    );
    $extra->execute([(int)$row['user_id']]);
    $keep = array_slice($extra->fetchAll(PDO::FETCH_COLUMN), 0, DEVICE_TOKENS_MAX);
    if ($keep) {
        $db->prepare(
            "DELETE FROM device_tokens WHERE user_id = ? AND claimed_at IS NOT NULL
             AND id NOT IN (" . implode(',', array_map('intval', $keep)) . ")"
        )->execute([(int)$row['user_id']]);
    }

    return [
        'id'           => (int)$row['id'],
        'token'        => (string)$row['token'],
        'scope'        => (string)$row['scope'],
        'household_id' => (int)$row['household_id'],
        'user_id'      => (int)$row['user_id'],
    ];
}

// The watches this person has connected TO THIS LEDGER, newest first. Read-only — the drawer
// lists them so that "which devices can see my ledger" has an answer that is not "no idea".
//
// Scoped by household as well as by user, because a device is pinned to the ledger it was
// paired with (deviceFromToken). Listing someone's watch from another ledger next to a
// Disconnect button would offer to unpair a device that is not filing here anyway.
function pairedDevices(PDO $db, int $hid, int $uid): array {
    $s = $db->prepare(
        "SELECT id, label, scope, claimed_at, last_seen_at FROM device_tokens
         WHERE household_id = ? AND user_id = ? AND claimed_at IS NOT NULL ORDER BY claimed_at DESC"
    );
    $s->execute([$hid, $uid]);
    return $s->fetchAll();
}

// Resolve a bearer token to the same ($hid, $uid, $role) a signed-in browser request carries.
// Null for anything that is not a live token — including one whose user has since been removed
// from the ledger, which roleIn() is what actually catches.
function deviceFromToken(PDO $db, string $token): ?array {
    if (!preg_match('/^[0-9a-f]{64}$/', $token)) return null;
    $s = $db->prepare(
        "SELECT id, household_id, user_id, scope FROM device_tokens WHERE token = ? AND claimed_at IS NOT NULL"
    );
    $s->execute([$token]);
    $row = $s->fetch();
    if (!$row) return null;

    // The device is pinned to the household it was paired with, not to wherever the user has
    // since switched their browser. Two ledgers, one watch, and the watch keeps filing into
    // the one you pointed it at.
    $hid  = (int)$row['household_id'];
    $uid  = (int)$row['user_id'];
    $role = roleIn($db, $hid, $uid);
    if ($role === null) return null;

    $db->prepare("UPDATE device_tokens SET last_seen_at = ? WHERE id = ?")->execute([nowSql(), (int)$row['id']]);
    return ['id' => (int)$row['id'], 'household_id' => $hid, 'user_id' => $uid, 'role' => $role, 'scope' => (string)$row['scope']];
}

// Is this browser session still allowed to exist?
//
// A session created by Google survives until it expires; there is nothing to check. A session
// created by a pairing code is only as alive as the row it came from, and that is the entire
// point of the drawer's Disconnect button: without this, revoking a device would kill its API
// access and leave its browser session merrily signed in for another thirty days.
//
// Cheap: one indexed lookup on a session that has a device_id, nothing at all on one that
// does not. The last_seen write is throttled to once every ten minutes, because "when did
// this device last use the ledger" does not need per-request precision and per-request
// precision would mean a write on every page load.
function deviceSessionValid(PDO $db): bool {
    $id = (int)($_SESSION['device_id'] ?? 0);
    if ($id <= 0) return true;

    $s = $db->prepare(
        "SELECT last_seen_at FROM device_tokens
         WHERE id = ? AND claimed_at IS NOT NULL AND scope = ?"
    );
    $s->execute([$id, DEVICE_SCOPE_FULL]);
    $row = $s->fetch();
    if (!$row) return false;

    $seen = $row['last_seen_at'] ? strtotime((string)$row['last_seen_at']) : 0;
    if (time() - $seen > 600) {
        $db->prepare("UPDATE device_tokens SET last_seen_at = ? WHERE id = ?")->execute([nowSql(), $id]);
    }
    return true;
}

// Throw away a session whose device was disconnected. Separate from signing out because there
// is nothing voluntary about it — the person is told what happened rather than asked.
//
// Rotates the session rather than destroying it. Destroying was the obvious first version and
// it silently ate the message: the next thing this request does is put "you were
// disconnected" somewhere the login page can read it, and a destroyed session has nowhere to
// put it — the delete-cookie header and the new session's Set-Cookie raced, and the visitor
// arrived at a bare sign-in page with no idea why. Rotating still invalidates the old id,
// which is the part that actually matters.
function endDeviceSession(): void {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);
}

// What to call a browser that just paired, from what it told us about itself.
//
// Best-effort and deliberately coarse: the drawer needs "is that the work laptop or the
// tablet", not a fingerprint. Anything unrecognised is just "Device", which is honest.
function deviceLabelFromAgent(string $ua): string {
    $ua = trim($ua);
    if ($ua === '') return 'Device';
    $os = match (true) {
        str_contains($ua, 'Windows')            => 'Windows',
        str_contains($ua, 'iPhone')             => 'iPhone',
        str_contains($ua, 'iPad')               => 'iPad',
        str_contains($ua, 'Android')            => 'Android',
        // Order matters: every Mac browser also says "Macintosh", and iPadOS says both.
        str_contains($ua, 'Mac OS X')           => 'Mac',
        str_contains($ua, 'Linux')              => 'Linux',
        default                                 => '',
    };
    $browser = match (true) {
        str_contains($ua, 'Edg/')               => 'Edge',
        str_contains($ua, 'OPR/')               => 'Opera',
        str_contains($ua, 'Firefox/')           => 'Firefox',
        str_contains($ua, 'Chrome/')            => 'Chrome',
        // Safari claims to be lots of things, so it is only Safari once nothing else matched.
        str_contains($ua, 'Safari/')            => 'Safari',
        default                                 => '',
    };
    $name = trim($browser . ($os !== '' ? " on $os" : ''));
    return $name !== '' ? mb_substr($name, 0, 60) : 'Device';
}

// ────────────────────────────────────────────────────────────────────
// Writing an expense — one implementation, two front doors
//
// The web form posts here and so does the watch. Keeping the validation in one place is the
// point: a second copy would be the one that forgets the daily cap, or attributes a member's
// entry to the owner, and it would be wrong only on the path nobody looks at.
// ────────────────────────────────────────────────────────────────────
function createExpense(PDO $db, array $cfg, int $hid, int $uid, string $role, array $in): void {
    $L     = $cfg['limits'];
    $amt   = parseAmount((string)($in['amount'] ?? ''), $cfg);
    $date  = requireDate((string)($in['date'] ?? today()), 'Date');
    $note  = optionalStr($in['note'] ?? '', $L['note_len_max'], 'Note');
    $catId = ownedId($db, 'categories', $hid, (int)($in['category_id'] ?? 0));
    $memId = attributableMember($db, $hid, $uid, $role, (int)($in['member_id'] ?? 0));
    assertUnderLimit(
        $db,
        "SELECT COUNT(*) FROM expenses WHERE household_id = ? AND date = ?",
        [$hid, $date],
        $L['expenses_per_day_max'],
        'Daily expenses'
    );
    $db->prepare(
        "INSERT INTO expenses (household_id, amount, category_id, member_id, note, date, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    )->execute([$hid, $amt, $catId, $memId, $note, $date, $uid]);
}

/**
 * The parts of a ledger a device caches rather than fetches: its name, its currency symbol,
 * and the categories to choose from. Sent with every reply, not just at pairing — a category
 * renamed on the website should reach the wrist on the next refresh, not on the next re-pair.
 */
function apiLedgerInfo(PDO $db, array $config, int $hid, int $uid = 0): array {
    // Currency and grouping live on the household, not the user — index.php copies them into
    // the session from exactly here. The watch formats its own numbers, so it needs both.
    $h = $db->prepare("SELECT name, currency, number_format FROM households WHERE id = ?");
    $h->execute([$hid]);
    $house = $h->fetch() ?: [];

    // Ordered by this month's spend so the categories a household actually uses sit at the top
    // of a list being scrolled with a bezel. Ties and unused categories keep the app's own
    // order behind them, so the list is stable rather than reshuffling day to day.
    $monthStart = date('Y-m-01');
    $monthEnd   = date('Y-m-d', strtotime($monthStart . ' +1 month'));
    $c = $db->prepare(
        "SELECT c.id, c.name, c.icon, COALESCE(SUM(e.amount), 0) AS used
         FROM categories c
         LEFT JOIN expenses e ON e.category_id = c.id AND e.household_id = c.household_id
                             AND e.`date` >= ? AND e.`date` < ?
         WHERE c.household_id = ?
         GROUP BY c.id, c.name, c.icon, c.is_custom
         ORDER BY used DESC, c.is_custom, c.id"
    );
    $c->execute([$monthStart, $monthEnd, $hid]);

    // The palette is per user, not per household — two people sharing a ledger each keep
    // their own. Resolved to hex here so the watch never has to know what OKLCH is.
    $themeKey = 'organic';
    if ($uid > 0) {
        $t = $db->prepare("SELECT theme FROM users WHERE id = ?");
        $t->execute([$uid]);
        $themeKey = (string)($t->fetchColumn() ?: 'organic');
    }

    return [
        'theme'      => watchPalette($themeKey),
        'ledger'     => (string)($house['name'] ?? 'Ledger'),
        'currency'   => (string)($house['currency'] ?? $config['currency'] ?? '₹'),
        'numfmt'     => (string)($house['number_format'] ?? 'indian'),
        'categories' => array_map(fn(array $r) => [
            'id'   => (int)$r['id'],
            'name' => (string)$r['name'],
            'icon' => (string)$r['icon'],
        ], $c->fetchAll()),
    ];
}

// ────────────────────────────────────────────────────────────────────
// The small screen's view of the ledger
//
// Everything a 1.5" round display can hold, in one round trip: today, the month, the three
// biggest categories with their budgets, and the last three entries. Called by the watch API
// after every write too, so the device never needs a second request to refresh itself.
// ────────────────────────────────────────────────────────────────────
function watchSummary(PDO $db, int $hid): array {
    $today      = today();
    $monthStart = date('Y-m-01');
    // Not "+1 month" from today: from Jan 31 that lands in March and the month total would
    // quietly include February. From the first of the month it is always right.
    $monthEnd   = date('Y-m-d', strtotime($monthStart . ' +1 month'));

    $dayStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE household_id = ? AND `date` = ?");
    $dayStmt->execute([$hid, $today]);

    $monStmt = $db->prepare(
        "SELECT COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total
         FROM expenses WHERE household_id = ? AND `date` >= ? AND `date` < ?"
    );
    $monStmt->execute([$hid, $monthStart, $monthEnd]);
    $mon = $monStmt->fetch();

    // The same GROUP BY and the same rollup the History tab draws its bars from, so a
    // sub-category's spend lands on its parent's budget here exactly as it does there.
    $catStmt = $db->prepare(
        "SELECT c.id AS cid, COALESCE(c.name, 'Uncategorised') AS name,
                COALESCE(c.icon, 'tag') AS icon, COALESCE(c.budget, 0) AS budget,
                p.id AS pid, p.name AS pname, p.icon AS picon, p.budget AS pbudget,
                SUM(e.amount) AS amt
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id AND c.household_id = e.household_id
         LEFT JOIN categories p ON p.id = c.parent_id AND p.household_id = c.household_id
         WHERE e.household_id = ? AND e.`date` >= ? AND e.`date` < ?
         GROUP BY c.id, c.name, c.icon, c.budget, p.id, p.name, p.icon, p.budget"
    );
    $catStmt->execute([$hid, $monthStart, $monthEnd]);
    // Already sorted by amount desc; three is what fits before the wrist has to scroll.
    $top = array_slice(rollupCategories($catStmt->fetchAll()), 0, 3);

    // Household budget = every top-level category's budget, spent or not, exactly as the
    // History tab totals it. 0 means nobody has set one, and the watch draws no bar.
    $budStmt = $db->prepare("SELECT COALESCE(SUM(budget), 0) FROM categories WHERE household_id = ? AND parent_id IS NULL");
    $budStmt->execute([$hid]);

    // The investing side, and the one figure worth a glance: how much of this month's target
    // is already in. Target is a monthly floor, summed over top-level types the same way the
    // Invest tab totals it — children sit at 0 so a branch cannot count its rupee twice.
    $invStmt = $db->prepare(
        "SELECT COALESCE(SUM(amount), 0) FROM investments
         WHERE household_id = ? AND `date` >= ? AND `date` < ?"
    );
    $invStmt->execute([$hid, $monthStart, $monthEnd]);
    $invested = roundMoney((float)$invStmt->fetchColumn());

    $invTgt = $db->prepare("SELECT COALESCE(SUM(target), 0) FROM investment_types WHERE household_id = ? AND parent_id IS NULL");
    $invTgt->execute([$hid]);
    $invTarget = roundMoney((float)$invTgt->fetchColumn());

    $recStmt = $db->prepare(
        "SELECT e.amount, e.note, e.date, COALESCE(c.name, 'Uncategorised') AS category
         FROM expenses e
         LEFT JOIN categories c ON c.id = e.category_id AND c.household_id = e.household_id
         WHERE e.household_id = ?
         ORDER BY e.`date` DESC, e.id DESC
         LIMIT 3"
    );
    $recStmt->execute([$hid]);

    return [
        'today'       => roundMoney((float)$dayStmt->fetchColumn()),
        'month'       => roundMoney((float)$mon['total']),
        'month_count' => (int)$mon['n'],
        'month_label' => date('F Y'),
        'budget'      => roundMoney((float)$budStmt->fetchColumn()),
        'invested'    => $invested,
        'invest_target' => $invTarget,
        // Sent computed rather than left to the client: a complication and a tile would
        // otherwise each do this division, and one of them would divide by zero.
        // Uncapped on purpose — beating the target is worth seeing, so 140% says 140%.
        'invest_pct'  => $invTarget > 0 ? (int)round($invested / $invTarget * 100) : 0,
        'date'        => $today,
        'top'         => array_map(fn(array $b) => [
            'name'   => (string)$b['name'],
            'amt'    => roundMoney((float)$b['amt']),
            'budget' => roundMoney((float)$b['budget']),
        ], $top),
        'recent'      => array_map(fn(array $r) => [
            'amount'   => roundMoney((float)$r['amount']),
            'category' => (string)$r['category'],
            'note'     => (string)$r['note'],
            'date'     => (string)$r['date'],
        ], $recStmt->fetchAll()),
    ];
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

// The names an Invest view may show under the archived/active filter it is on. Built from the
// type rows rather than from the entries, so a type with nothing in it this month still counts
// as a name the screen is allowed to group under.
function allowedTypeNames(array $types, array $archSet, string $filter): array {
    $out = [];
    foreach ($types as $name => $t) {
        $isArch = isset($archSet[$name]);
        if ($filter === 'all' || $isArch === ($filter === 'archived')) $out[(string)$name] = true;
    }
    return $out;
}

// Fold per-type money into parent buckets — the investment twin of rollupCategories(), down
// to the trailing "Direct" line, but keyed on names because that is what an investment stores.
// $rows are [type, n, amt]; $types is the household's type rows by name.
//
// $allowed is the set of names the archived/active filter permits, which is deliberately NOT
// the set of names that happen to have entries in this window. It used to be the latter, and
// that broke nesting exactly where it earns its keep: a parent whose money is all in its
// sub-types has no entries of its own, so it was absent from the window, so its children found
// no parent to fold into and each rendered as its own top-level card. Filtering on what the
// filter allows keeps the original point — an "active" screen can never be given an archived
// parent's name — without making a parent's own spending a condition of grouping.
function rollupTypes(array $rows, array $types, array $allowed): array {
    // id => row, so the parent lookup is not a scan per entry row.
    $byId = [];
    foreach ($types as $t) $byId[(int)$t['id']] = $t;
    $out = [];
    foreach ($rows as $r) {
        $name  = (string)$r['type'];
        $me    = $types[$name] ?? null;
        $par   = null;
        if ($me && !empty($me['parent_id'])) {
            // A parent_id with no row behind it (deleted out from under it, or carried in by a
            // restore) leaves the child standing on its own — there is nothing to group under.
            $cand = $byId[(int)$me['parent_id']] ?? null;
            if ($cand && isset($allowed[(string)$cand['name']])) $par = $cand;
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
        if (!$b['children']) continue;
        usort($out[$k]['children'], fn($a, $c) => $c['amt'] <=> $a['amt']);
        // Same closing line rollupCategories() adds: a parent usually holds money of its own
        // as well as its sub-types', and without this the listed children fall short of the
        // bar above them with nothing to say where the rest went.
        $direct = $b['amt'] - array_sum(array_column($b['children'], 'amt'));
        if ($direct > 0.004) $out[$k]['children'][] = ['name' => 'Direct', 'amt' => round($direct, 2)];
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
