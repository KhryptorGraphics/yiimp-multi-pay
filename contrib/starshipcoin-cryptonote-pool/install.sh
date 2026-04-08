#!/usr/bin/env bash
set -euo pipefail

POOL_ROOT="/opt/cryptonote-nodejs-pool-starshipcoin"
UPSTREAM_REPO="https://github.com/dvandal/cryptonote-nodejs-pool.git"
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if ! command -v git >/dev/null 2>&1; then
  echo "git is required" >&2
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "npm is required" >&2
  exit 1
fi

if [ ! -d "$POOL_ROOT/.git" ]; then
  sudo mkdir -p /opt
  sudo git clone "$UPSTREAM_REPO" "$POOL_ROOT"
fi

sudo chown -R "$USER":"$USER" "$POOL_ROOT"
cd "$POOL_ROOT"

# Starshipcoin needs a non-default CryptoNote blob offset, a Starshipcoin-specific
# block-template blob type patch for post-genesis templates, and dependency pinning
# to avoid known runtime breakage.
node -e 'const fs=require("fs"); const p=require("./package.json"); p.dependencies.redis="3.1.2"; delete p.dependencies["turtlecoin-multi-hashing"]; fs.writeFileSync("package.json", JSON.stringify(p,null,2)+"\n");'
npm install

cp "$HERE/config.example.json" "$POOL_ROOT/config.json"

if ! grep -q "resolveBlockBlobType" "$POOL_ROOT/lib/pool.js"; then
  cp "$POOL_ROOT/lib/pool.js" "$POOL_ROOT/lib/pool.js.orig"
  patch -d "$POOL_ROOT" -p0 < "$HERE/patches/starshipcoin-v2-blobtype.patch"
fi

echo "Pool installed in $POOL_ROOT"
echo "Edit $POOL_ROOT/config.json and set poolServer.poolAddress before starting the service."
echo "Also confirm the wallet endpoint in config.json is reachable from this host before enabling payments."
echo "The bundled config already pins clusterForks=1, cnBlobType=2, offset=3, and disables charts to avoid known bring-up crashes."
echo "Then run:"
echo "  sudo cp /home/kp/repos/yiimp/config/systemd/starshipcoin-cryptonote-pool.service /etc/systemd/system/"
echo "  sudo systemctl daemon-reload"
echo "  sudo systemctl enable starshipcoin-cryptonote-pool"
echo "  sudo systemctl start starshipcoin-cryptonote-pool"
