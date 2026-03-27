#!/bin/bash
# Sync yaamp config from repo to production
# Usage: ./sync-config.sh

REPO_CONFIG="/home/kp/repos/yiimp/web/yaamp/config.php"
PROD_CONFIG="/var/www/yaamp/config.php"

if [ ! -f "$REPO_CONFIG" ]; then
    echo "Error: Repo config not found at $REPO_CONFIG"
    exit 1
fi

if [ -w "$PROD_CONFIG" ]; then
    cp "$REPO_CONFIG" "$PROD_CONFIG"
    echo "Config synced to production"
else
    echo "Need sudo to update $PROD_CONFIG"
    sudo cp "$REPO_CONFIG" "$PROD_CONFIG"
    echo "Config synced with sudo"
fi
