#!/usr/bin/env bash
set -euo pipefail

REPO="/home/kp/repos/yiimp"
WEB_ROOT="$REPO/web"
RECOVERY_DIR="/tmp/yiimp-recovery"
MYSQL=(mysql -h127.0.0.1 -P3306 -uyaamp -pteamrsi123teamrsi123teamrsi123 -D yaamp)

mkdir -p "$RECOVERY_DIR" "$WEB_ROOT/log" "$WEB_ROOT/bin"

echo "[1/6] Repairing live coin rows"
"${MYSQL[@]}" <<'SQL'
UPDATE coins SET rpcpasswd='opencex' WHERE symbol IN ('BELLS','PEP');
UPDATE coins SET dedicatedport=14436 WHERE symbol='DGB';
UPDATE coins SET dedicatedport=14533 WHERE symbol='MONA';
UPDATE coins SET rpcpasswd='Qn7mT0k3nRpcW8x3pLv1', dedicatedport=17337 WHERE symbol='QNTM';
DELETE FROM stratums;
SQL

echo "[2/6] Writing corrected stratum configs"
cat > "$RECOVERY_DIR/sha256.conf" <<'EOF'
[TCP]
server = giggahash.com
port = 13333
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]
btc = http://opencex:opencex@127.0.0.1:8332/

[STRATUM]
algo = sha256
difficulty = 256
max_ttf = 40000
reconnect = 1
logdir = /tmp/

[DEBUGLOG]
client = 1
EOF

cat > "$RECOVERY_DIR/scrypt.conf" <<'EOF'
[TCP]
server = giggahash.com
port = 14434
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]

[STRATUM]
algo = scrypt
difficulty = 128
max_ttf = 40000
reconnect = 1
logdir = /tmp/

[DEBUGLOG]
client = 1
EOF

cat > "$RECOVERY_DIR/dgb-sha256d.conf" <<'EOF'
[TCP]
server = giggahash.com
port = 14436
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]
include = DGB

[STRATUM]
algo = sha256d
difficulty = 128
max_ttf = 40000
reconnect = 1
logdir = /tmp/
EOF

cat > "$RECOVERY_DIR/lyra2v2-mona.conf" <<'EOF'
[TCP]
server = giggahash.com
port = 14533
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]
include = MONA

[STRATUM]
algo = lyra2v2
difficulty = 1
max_ttf = 40000
reconnect = 1
logdir = /tmp/
EOF

for coin in FLO IFC NXE PEP; do
  lower="$(echo "$coin" | tr 'A-Z' 'a-z')"
  port=""
  case "$coin" in
    FLO) port=14439 ;;
    IFC) port=14438 ;;
    NXE) port=14435 ;;
    PEP) port=14437 ;;
  esac
  cat > "$RECOVERY_DIR/scrypt-${lower}.conf" <<EOF
[TCP]
server = giggahash.com
port = ${port}
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]
include = ${coin}

[STRATUM]
algo = scrypt
difficulty = 128
max_ttf = 40000
reconnect = 1
logdir = /tmp/
EOF
done

cat > "$RECOVERY_DIR/scrypt-qntm.conf" <<'EOF'
[TCP]
server = giggahash.com
port = 17337
password = tu8tu5

[SQL]
host = 127.0.0.1
database = yaamp
username = yaamp
password = teamrsi123teamrsi123teamrsi123

[WALLETS]
include = QNTM

[STRATUM]
algo = scrypt
difficulty = 256
max_ttf = 40000
reconnect = 1
logdir = /tmp/
EOF

echo "[3/6] Stopping stale recovery processes"
pkill -f "$RECOVERY_DIR" || true
pkill -f "$WEB_ROOT/runconsole.php cronjob/run" || true
pkill -f "$WEB_ROOT/runconsole.php cronjob/runLoop2" || true
pkill -f "$WEB_ROOT/runconsole.php cronjob/runBlocks" || true

echo "[4/6] Starting stratum processes"
nohup /usr/local/bin/stratum "$RECOVERY_DIR/sha256.conf" > "$RECOVERY_DIR/sha256.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt.conf" > "$RECOVERY_DIR/scrypt.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/dgb-sha256d.conf" > "$RECOVERY_DIR/dgb-sha256d.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/lyra2v2-mona.conf" > "$RECOVERY_DIR/lyra2v2-mona.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt-flo.conf" > "$RECOVERY_DIR/scrypt-flo.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt-ifc.conf" > "$RECOVERY_DIR/scrypt-ifc.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt-nxe.conf" > "$RECOVERY_DIR/scrypt-nxe.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt-pep.conf" > "$RECOVERY_DIR/scrypt-pep.log" 2>&1 &
nohup /usr/local/bin/stratum "$RECOVERY_DIR/scrypt-qntm.conf" > "$RECOVERY_DIR/scrypt-qntm.log" 2>&1 &

echo "[5/6] Starting pool loops from repo"
nohup bash -lc "cd '$WEB_ROOT' && while true; do php -d max_execution_time=120 runconsole.php cronjob/run; sleep 90; done" > "$RECOVERY_DIR/main-loop.log" 2>&1 &
nohup bash -lc "cd '$WEB_ROOT' && while true; do php -d max_execution_time=120 runconsole.php cronjob/runLoop2; sleep 90; done" > "$RECOVERY_DIR/loop2.log" 2>&1 &
nohup bash -lc "cd '$WEB_ROOT' && while true; do php -d max_execution_time=120 runconsole.php cronjob/runBlocks; sleep 90; done" > "$RECOVERY_DIR/blocks.log" 2>&1 &

echo "[6/6] Done"
echo "Logs: $RECOVERY_DIR"
