#!/usr/bin/env bash
set -euo pipefail

if [ $# -lt 1 ] || [ $# -gt 2 ]; then
  echo "Usage: $0 <STARSHIPCOIN_POOL_WALLET_ADDRESS> [WALLET_RPC_URL]" >&2
  exit 1
fi

POOL_WALLET="$1"
WALLET_RPC_URL="${2:-http://208.180.220.104:8070/json_rpc}"

echo "== daemon getinfo =="
curl -fsS http://127.0.0.1:35444/getinfo
echo
echo

echo "== daemon getblocktemplate =="
curl -fsS \
  -H 'Content-Type: application/json' \
  -d "{\"jsonrpc\":\"2.0\",\"id\":\"0\",\"method\":\"getblocktemplate\",\"params\":{\"wallet_address\":\"${POOL_WALLET}\",\"reserve_size\":8}}" \
  http://127.0.0.1:35444/json_rpc
echo
echo

echo "== wallet getStatus =="
curl -fsS \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":"0","method":"getStatus","params":{}}' \
  "$WALLET_RPC_URL"
echo
