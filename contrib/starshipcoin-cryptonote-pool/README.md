# Starshipcoin CryptoNote Pool Bundle

This bundle integrates Starshipcoin into the `yiimp` project as a sidecar CryptoNote pool.

The stock `yiimp` C++ stratum in this repository builds Bitcoin-style `mining.notify` jobs and does not understand CryptoNote `blocktemplate_blob` mining. Starshipcoin exposes standard CryptoNote mining RPC with `blocktemplate_blob` and `reserved_offset`, so the correct stratum for this coin is a CryptoNote-native pool stack such as `dvandal/cryptonote-nodejs-pool`.

This directory gives you a deployable Starshipcoin configuration and service layout while keeping it alongside the rest of your pool project.

## What This Bundle Assumes

- Starshipcoin daemon RPC is reachable on `127.0.0.1:35444` from the Linux pool server.
- Starshipcoin wallet RPC (`walletd`) runs locally on `127.0.0.1:8070` on the Linux pool host.
- Redis is available on `127.0.0.1:6379`.
- The pool host you want miners to use is `pool.giggahash.com`.

## Chain Parameters

- Coin: `Starshipcoin`
- Symbol: `STC`
- Algorithm: `cryptonight`
- Variant: `0`
- Blob type: `2`
- Pool blob offset override: `3`
- Address prefix: `5461059` (`0x535443`)
- Decimal places: `12`
- Block target: `120`
- Daemon P2P/RPC ports: `35443` / `35444`

## Files

- `config.example.json`: Starshipcoin config for `cryptonote-nodejs-pool`
- `install.sh`: clones and prepares the upstream pool in `/opt/cryptonote-nodejs-pool-starshipcoin`
- `check-rpc.sh`: validates daemon `getinfo`, daemon `getblocktemplate`, and wallet `getStatus`
- `../../config/systemd/starshipcoin-cryptonote-pool.service`: systemd unit for the pool
- `../../config/systemd/starshipcoin-walletd-pool.service`: systemd unit for the Linux-local pool wallet RPC

## Install

Run the install script from the `yiimp` checkout:

```bash
cd /home/kp/repos/yiimp/contrib/starshipcoin-cryptonote-pool
./install.sh
```

That script will:

1. Clone `https://github.com/dvandal/cryptonote-nodejs-pool.git`
2. Pin the legacy-compatible `redis` client and remove the incompatible `turtlecoin-multi-hashing` dependency
3. Run `npm install`
4. Apply the Starshipcoin post-genesis blob type patch to `lib/pool.js`
5. Copy `config.example.json` to `/opt/cryptonote-nodejs-pool-starshipcoin/config.json`
6. Print the next systemd commands

## Validate RPC Before Starting The Pool

```bash
cd /home/kp/repos/yiimp/contrib/starshipcoin-cryptonote-pool
./check-rpc.sh "YOUR_POOL_WALLET_ADDRESS"
```

The address must be a valid Starshipcoin public address that receives mined rewards.

The validated pool path uses a Linux-local `walletd` instance on `127.0.0.1:8070`. The Windows node can remain a standalone peer and does not need to be reachable for pool payouts.

## What Was Verified

- Daemon RPC answered `getinfo` and `getblocktemplate` on `127.0.0.1:35444`.
- The Linux-local `walletd` on `127.0.0.1:8070` answered `getStatus` and `getAddresses`.
- The sidecar pool API answered on `127.0.0.1:8117/stats`.
- A direct stratum `login` to `127.0.0.1:36333` returned a valid mining job.

## Why The Bundle Uses These Defaults

- `offset: 3`: the upstream pool's default CryptoNote template offset was off by one for Starshipcoin, causing `reserved_offset` mismatch logs and bad job construction.
- `cnBlobType: 2`: Starshipcoin post-genesis block templates parse as CryptoNote blob type `2`, and the bundled `pool.js` patch also auto-resolves blob type per template so height transitions continue working after the first mined block.
- `clusterForks: 1`: keeps bring-up stable and avoids excessive memory use during initial deployment.
- `charts` disabled: prevents the optional charts collector from crash-looping on an incomplete chart config while the pool is being brought online.

## Enable The Service

```bash
sudo cp /home/kp/repos/yiimp/config/systemd/starshipcoin-walletd-pool.service /etc/systemd/system/
sudo cp /home/kp/repos/yiimp/config/systemd/starshipcoin-cryptonote-pool.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable starshipcoin-walletd-pool
sudo systemctl start starshipcoin-walletd-pool
sudo systemctl enable starshipcoin-cryptonote-pool
sudo systemctl start starshipcoin-cryptonote-pool
sudo systemctl status starshipcoin-walletd-pool --no-pager
sudo systemctl status starshipcoin-cryptonote-pool --no-pager
```

## Notes

- This is the correct Starshipcoin mining path inside the `yiimp` project layout.
- It does not retrofit the existing `yiimp` C++ stratum into a CryptoNote pool.
- If you want a unified frontend later, treat this sidecar pool as the mining engine and bridge stats into the site separately.
