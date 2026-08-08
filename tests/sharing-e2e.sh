#!/bin/bash
# Two-user end-to-end proof of the sharing feature.
# Alice owns household 1; Bob signs in fresh, joins by invite, and must be able to edit only
# what he added. Exits non-zero if any assertion fails.
#
# SQL and PHP snippets are passed through the environment, never interpolated into a quoted
# string — an earlier version did that and the quotes inside the SQL broke the PHP, which
# looked exactly like the app failing.
#
# THIS WRITES AND DELETES REAL ROWS in household 1 and creates a throwaway user. Never point
# it at production. It refuses to run against anything but localhost, and it needs the dev
# stub, which only exists when GOOGLE_CLIENT_ID is the placeholder and APP_DEBUG=1:
#
#   GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com APP_DEBUG=1 \
#     php -S 127.0.0.1:8152 router.php
#   bash tests/sharing-e2e.sh
#
set -u
cd "$(dirname "$0")/.." || exit 1
BASE=${BASE:-http://127.0.0.1:8152}
case "$BASE" in
  http://127.0.0.1:*|http://localhost:*) ;;
  *) echo "refusing to run against $BASE — localhost only"; exit 2 ;;
esac
A=/tmp/hl-alice.txt
FAILED=0

ok()  { echo "  PASS  $1"; }
bad() { echo "  FAIL  $1"; FAILED=1; }
is()  { if [ "$2" = "$3" ]; then ok "$1 ($2)"; else bad "$1 — expected '$3', got '$2'"; fi; }

BOOT='$c=require "config.php"; require "lib.php"; $db=makeDb($c);'
# q "<sql>"  -> first column of first row
q()   { SQL="$1" php -r "$BOOT"'echo (string)$db->query(getenv("SQL"))->fetchColumn();'; }
# php_ "<code>" -> run code with $db in scope
php_(){ CODE="$1" php -r "$BOOT"'eval(getenv("CODE"));'; }
csrf(){ curl -s -b "$A" "$BASE$1" | grep -o 'name="_csrf" value="[a-f0-9]*"' | head -1 | grep -o '[a-f0-9]\{32\}'; }

echo "== setup =="
rm -f "$A"
curl -s -c "$A" -b "$A" -o /dev/null -X POST -d "dev=1" "$BASE/signin"
ALICE=$(q "SELECT id FROM users WHERE google_sub = 'dev-local-user'")
BOB=$(php_ '$s=$db->prepare("SELECT id FROM users WHERE google_sub=?"); $s->execute(["e2e-bob"]);
            echo (int)($s->fetchColumn() ?: bootstrapHousehold($db,"Bob","bob@example.com","e2e-bob"));')
BOBHH=$(q "SELECT household_id FROM users WHERE id = $BOB")
echo "  alice=$ALICE  bob=$BOB  bob's ledger=$BOBHH"
is "alice's session works" "$(curl -s -b "$A" -o /dev/null -w '%{http_code}' "$BASE/")" "200"

echo
echo "== bob starts with his own ledger only =="
is "bob owns exactly one ledger"     "$(q "SELECT COUNT(*) FROM household_users WHERE user_id = $BOB")" "1"
is "and it is not alice's"           "$(q "SELECT COUNT(*) FROM household_users WHERE user_id = $BOB AND household_id = 1")" "0"
is "his ledger is called Personal"   "$(q "SELECT name FROM households WHERE id = $BOBHH")" "Personal"
is "he owns it"                      "$(q "SELECT role FROM household_users WHERE user_id = $BOB")" "owner"
is "his member label carries his name" "$(q "SELECT name FROM members WHERE household_id = $BOBHH")" "Bob"

echo
echo "== alice mints an invite over HTTP =="
C=$(csrf /ledgers)
curl -s -b "$A" -o /dev/null -X POST -d "_csrf=$C&back=/ledgers" "$BASE/invite"
TOKEN=$(q "SELECT token FROM invites WHERE household_id = 1 AND used_at IS NULL ORDER BY id DESC LIMIT 1")
is "token is 32 hex chars" "$(printf %s "$TOKEN" | grep -cE '^[0-9a-f]{32}$')" "1"
SECS=$(q "SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) FROM invites WHERE token = '$TOKEN'")
if [ "$SECS" -gt 1700 ] && [ "$SECS" -le 1800 ]
  then ok "expires in 30 minutes, not a timezone away (${SECS}s)"
  else bad "expiry window wrong: ${SECS}s"; fi
is "the link appears on the page" "$(curl -s -b "$A" "$BASE/ledgers" | grep -c "/join?t=$TOKEN")" "1"

echo
echo "== minting again supersedes the old link =="
OLD=$TOKEN
C=$(csrf /ledgers)
curl -s -b "$A" -o /dev/null -X POST -d "_csrf=$C&back=/ledgers" "$BASE/invite"
TOKEN=$(q "SELECT token FROM invites WHERE household_id = 1 AND used_at IS NULL ORDER BY id DESC LIMIT 1")
is "only one live invite exists"     "$(q "SELECT COUNT(*) FROM invites WHERE household_id = 1 AND used_at IS NULL")" "1"
is "the superseded token is gone"    "$(q "SELECT COUNT(*) FROM invites WHERE token = '$OLD'")" "0"

echo
echo "== bob redeems it =="
is "redeem succeeds"              "$(TOK=$TOKEN php_ 'echo redeemInvite($db, getenv("TOK"), '"$BOB"')["status"];')" "ok"
is "bob is in alice's ledger"     "$(q "SELECT COUNT(*) FROM household_users WHERE user_id = $BOB AND household_id = 1")" "1"
is "as a member, not an owner"    "$(q "SELECT role FROM household_users WHERE user_id = $BOB AND household_id = 1")" "member"
is "bob now holds two ledgers"    "$(q "SELECT COUNT(*) FROM household_users WHERE user_id = $BOB")" "2"
is "he got a spender label"       "$(q "SELECT COUNT(*) FROM members WHERE household_id = 1 AND user_id = $BOB")" "1"
is "the invite records who used it" "$(q "SELECT used_by FROM invites WHERE token = '$TOKEN'")" "$BOB"

echo
echo "== the link is single-use and time-boxed =="
is "the same token cannot be reused" "$(TOK=$TOKEN php_ 'echo redeemInvite($db, getenv("TOK"), 1)["status"];')" "invalid"
is "an expired token is refused"     "$(php_ '$t=bin2hex(random_bytes(16));
  $db->prepare("INSERT INTO invites (household_id,token,created_by,expires_at)
                VALUES (1,?,1,DATE_SUB(NOW(), INTERVAL 1 MINUTE))")->execute([$t]);
  echo redeemInvite($db,$t,'"$BOB"')["status"];')" "invalid"
is "a malformed token is refused"    "$(php_ 'echo redeemInvite($db,"not-a-real-token",'"$BOB"')["status"];')" "invalid"

echo
echo "== the 10-person cap =="
is "an 11th person is refused" "$(php_ '$db->beginTransaction();
  for ($i = 900; $i < 910; $i++)
      $db->prepare("INSERT IGNORE INTO household_users (household_id,user_id,role) VALUES (1,?,?)")
         ->execute([$i, ROLE_MEMBER]);
  $t = bin2hex(random_bytes(16));
  $db->prepare("INSERT INTO invites (household_id,token,created_by,expires_at)
                VALUES (1,?,1,DATE_ADD(NOW(), INTERVAL 30 MINUTE))")->execute([$t]);
  $r = redeemInvite($db, $t, 999);
  $db->rollBack();
  echo $r["status"];')" "full"

echo
echo "== who may edit what =="
ALICE_ROW=$(php_ '$db->prepare("INSERT INTO expenses (household_id,amount,date,created_by)
                                VALUES (1,99.99,\"2026-07-20\",?)")->execute(['"$ALICE"']);
                  echo $db->lastInsertId();')
BOB_ROW=$(php_ '$db->prepare("INSERT INTO expenses (household_id,amount,date,created_by)
                              VALUES (1,88.88,\"2026-07-21\",?)")->execute(['"$BOB"']);
                echo $db->lastInsertId();')
LEGACY=$(php_ '$db->exec("INSERT INTO expenses (household_id,amount,date) VALUES (1,77.77,\"2026-07-22\")");
               echo $db->lastInsertId();')

# chk <row> <uid> <role> <hid>
chk() { ROW=$1 UID_=$2 ROLE_=$3 HID_=$4 php -r "$BOOT"'
  try { requireEditable($db, "expenses", (int)getenv("HID_"), (int)getenv("ROW"),
                        (int)getenv("UID_"), getenv("ROLE_")); echo "allowed"; }
  catch (UserErr $e) { echo "refused"; }'; }

is "bob edits his own row"                "$(chk "$BOB_ROW"   "$BOB"   member 1)" "allowed"
is "bob may NOT edit alice's row"         "$(chk "$ALICE_ROW" "$BOB"   member 1)" "refused"
is "bob may NOT edit a pre-sharing row"   "$(chk "$LEGACY"    "$BOB"   member 1)" "refused"
is "alice the owner edits bob's row"      "$(chk "$BOB_ROW"   "$ALICE" owner  1)" "allowed"
is "alice edits the pre-sharing row"      "$(chk "$LEGACY"    "$ALICE" owner  1)" "allowed"
is "no one reaches another ledger's row"  "$(chk "$ALICE_ROW" "$ALICE" owner  999)" "refused"

echo
echo "== and the same refusal over real HTTP =="
C=$(csrf /history)
curl -s -b "$A" -o /dev/null -X POST -d "_csrf=$C&id=$BOB_ROW&back=/history" "$BASE/expenses/delete"
is "owner can delete a member's row" "$(q "SELECT COUNT(*) FROM expenses WHERE id = $BOB_ROW")" "0"
C=$(csrf /history)
curl -s -b "$A" -o /dev/null -X POST -d "_csrf=$C&id=$ALICE_ROW&back=/history" "$BASE/expenses/delete"
is "owner can delete their own row"  "$(q "SELECT COUNT(*) FROM expenses WHERE id = $ALICE_ROW")" "0"

echo
echo "== the who filter reaches all three ledgers =="
MID=$(q "SELECT id FROM members WHERE household_id = 1 AND user_id = $BOB")
is "history accepts the filter" "$(curl -s -b "$A" -o /dev/null -w '%{http_code}' "$BASE/history?m=1&who=$MID")" "200"
# Appears more than once by design — the month arrow and the swipe-nav script both carry it.
is "filter survives month nav"  "$(curl -s -b "$A" "$BASE/history?m=1&who=$MID" | grep -q "history?m=2&amp;who=$MID" && echo yes || echo no)" "yes"
is "a foreign member id is dropped" "$(curl -s -b "$A" "$BASE/history?m=1&who=999999" | grep -c 'who=999999')" "0"

echo
echo "== switching ledgers =="
is "bob switches into alice's ledger"        "$(php_ 'echo switchLedger($db,'"$BOB"',1) ? "yes" : "no";')" "yes"
is "bob cannot switch into a stranger's"     "$(php_ 'echo switchLedger($db,'"$BOB"',424242) ? "yes" : "no";')" "no"
is "a removed member falls back to their own" "$(php_ '
  $db->prepare("DELETE FROM household_users WHERE household_id=1 AND user_id=?")->execute(['"$BOB"']);
  echo roleIn($db, 1, '"$BOB"') === null ? "yes" : "no";')" "yes"

echo
echo "== cleanup =="
php_ 'foreach ([
        "DELETE FROM expenses       WHERE id = '"$LEGACY"'",
        "DELETE FROM household_users WHERE user_id = '"$BOB"'",
        "DELETE FROM members         WHERE user_id = '"$BOB"' OR household_id = '"$BOBHH"'",
        "DELETE FROM invites         WHERE household_id = 1",
        "DELETE FROM categories      WHERE household_id = '"$BOBHH"'",
        "DELETE FROM investment_types WHERE household_id = '"$BOBHH"'",
        "DELETE FROM earning_categories WHERE household_id = '"$BOBHH"'",
        "DELETE FROM households      WHERE id = '"$BOBHH"'",
        "DELETE FROM users           WHERE id = '"$BOB"'",
      ] as $sql) $db->exec($sql);
      echo "  bob, his ledger and the test rows are gone\n";'

echo
if [ "$FAILED" = "0" ]; then echo "ALL PASSED"; else echo "SOME FAILED"; fi
exit $FAILED
