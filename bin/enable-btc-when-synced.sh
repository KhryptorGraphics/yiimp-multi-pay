#!/bin/bash
# Polls bitcoind until fully synced, then enables BTC coin and starts stratum.
# Run with: nohup bash /home/kp/repos/yiimp/bin/enable-btc-when-synced.sh &

RPCUSER=opencex
RPCPASS=RiP8djVNGici
MYSQL="/usr/bin/mysql -u yaamp -pyaampdb2024 -h 127.0.0.1 yaamp"

echo "[$(date)] Waiting for bitcoind to finish initial block download..."

while true; do
    IBD=$(bitcoin-cli -rpcuser=$RPCUSER -rpcpassword=$RPCPASS getblockchaininfo 2>/dev/null | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['initialblockdownload'])" 2>/dev/null)
    PROGRESS=$(bitcoin-cli -rpcuser=$RPCUSER -rpcpassword=$RPCPASS getblockchaininfo 2>/dev/null | python3 -c "import sys,json; d=json.load(sys.stdin); print(round(d['verificationprogress']*100,2))" 2>/dev/null)

    echo "[$(date)] IBD=$IBD  progress=${PROGRESS}%"

    if [ "$IBD" = "False" ]; then
        echo "[$(date)] Sync complete! Enabling BTC coin..."
        $MYSQL -e "UPDATE coins SET auto_ready=1 WHERE symbol='BTC'"
        echo "[$(date)] Starting stratum-sha256..."
        sudo systemctl start stratum-sha256
        echo "[$(date)] Done. BTC is live."
        break
    fi

    sleep 300  # check every 5 minutes
done
