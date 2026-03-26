# Droid Handoff: GiggaHash / YiiMP Recovery

## Goal

Bring `giggahash.com` back to a working local-host deployment with the full intended currency set, despite the original production MySQL instance being unrecoverable from its encrypted datadir.

This document is for continuing the work in-place on this host.

## Current State

- Live site: `https://giggahash.com`
- Live web root: `/var/www`
- Live config: `/etc/yiimp/serverconfig.php`
- Live DB config:
  - host: `127.0.0.1`
  - database: `yaamp`
  - user: `yaamp`
  - password: `teamrsi123teamrsi123teamrsi123`
- Site name in config: `GiggaHash`
- Site URL in config: `giggahash.com`

## Root Cause Of The Outage

The live frontend is truncated because YiiMP cannot connect to MySQL. MySQL itself is down because the original encrypted InnoDB datadir cannot be opened anymore.

Observed facts:

- `mysql.service` is failed.
- `/var/log/mysql/error.log` shows repeated:
  - `Decryption of redo log block ... failed because there's no keyring configured`
  - `Failed to initialize DD Storage Engine`
  - `Data Dictionary initialization failed`
- `/var/lib/mysql-keyring` is empty.
- The original datadir still exists at `/var/lib/mysql`, including `/var/lib/mysql/yaamp/*.ibd`.

Conclusion:

- The original production database was real and populated.
- The actual keyring material appears to be gone.
- The old DB is not realistically recoverable from this host alone unless the missing keyring file is recovered from somewhere else.

## What Was Already Verified

- The live Apache vhost serves `/var/www`, not the repo checkout.
- The frontend truncation is DB-related, not only template-related.
- `/site/history_results` on the live site returns empty because the app dies during DB-backed rendering.
- The repo already contains a frontend fragment fix in:
  - [`web/yaamp/modules/site/results/history_results.php`](/home/kp/repos/yiimp/web/yaamp/modules/site/results/history_results.php)
- The repo also contains local-config bootstrapping support in:
  - [`web/index.php`](/home/kp/repos/yiimp/web/index.php)
  - [`yiimp2/config/web.php`](/home/kp/repos/yiimp/yiimp2/config/web.php)
  - [`yiimp2/config/console.php`](/home/kp/repos/yiimp/yiimp2/config/console.php)

## Important Local Files

- Live config:
  - [`/etc/yiimp/serverconfig.php`](/etc/yiimp/serverconfig.php)
- Live web root:
  - [`/var/www/index.php`](/var/www/index.php)
- Local repo:
  - [`README.md`](/home/kp/repos/yiimp/README.md)
  - [`web/index.php`](/home/kp/repos/yiimp/web/index.php)
  - [`web/yaamp/modules/site/results/history_results.php`](/home/kp/repos/yiimp/web/yaamp/modules/site/results/history_results.php)
- Recovery helper already added:
  - [`bin/recover-giggahash-mysql-keyring.sh`](/home/kp/repos/yiimp/bin/recover-giggahash-mysql-keyring.sh)

Note:

- The recovery helper does not solve the production outage because the keyring directory is empty. It only restores bootstrap config, not lost encryption keys.

## Currency Inventory Recovered From Disk

The strongest source is `strings` output from `/var/lib/mysql/yaamp/coins.ibd`, plus the local sync script and seed SQL.

### Confirmed coins found in `coins.ibd`

- BTC
- LTC
- DOGE
- VIA
- DGB
- GAME
- GLC
- MONA
- BELLS
- FLO
- SOH
- PEP
- EMC2
- NXE
- IFC
- CAT

### Coins with stronger configuration recovery

These have enough local evidence to reconstruct with good confidence:

- BTC
- LTC
- DOGE
- VIA
- DGB
- EMC2
- GAME
- MONA
- GLC
- FLO
- SOH
- PEP
- BELLS
- NXE
- IFC
- CAT

## Best Local Sources For Rebuild

### 1. Repo base schema/export

Base SQL dump:

- [`sql/2024-03-06-complete_export.sql.gz`](/home/kp/repos/yiimp/sql/2024-03-06-complete_export.sql.gz)

What it gives:

- Full YiiMP schema
- Base tables
- Base BTC coin row

What it does not give:

- The full live GiggaHash coin set

### 2. Recovered production coin list and ports

Recovered from:

- [`/home/kp/bin/check-coin-sync.sh`](/home/kp/bin/check-coin-sync.sh)

Useful symbol-to-port mapping found there:

- BTC: `8332`
- LTC: `9332`
- SOH: `32717`
- PEP: `33873`
- DOGE: `22555`
- DGB: `14022`
- BELLS: `19918`
- FLO: `7313`
- GLC: `8122`
- MONA: `9401`
- CAT: `9335`
- IFC: `9322`
- NXE: `29432`

That file also contains coin IDs used in the last live system:

- BTC: `7`
- LTC: `8`
- DOGE: `9`
- DGB: `11`
- MONA: `14`
- GLC: `15`
- FLO: `17`
- SOH: `18`
- PEP: `19`
- BELLS: `20`
- CAT: `409`
- IFC: `410`
- NXE: `414`

### 3. Additional coin seed SQL

Existing coin inserts:

- [`/home/kp/repos/OpenCEX/yiimp_new_coins.sql`](/home/kp/repos/OpenCEX/yiimp_new_coins.sql)

This file already contains inserts for:

- VIA
- DGB
- EMC2
- GAME
- MONA

## Reconstruction Strategy

Because the original DB is effectively lost, the fastest sane path is:

1. Preserve the dead datadir.
2. Stand up a fresh local MySQL instance on `127.0.0.1:3306`.
3. Recreate `yaamp` and the `yaamp` DB user.
4. Import the base export.
5. Apply the repo SQL migrations in chronological order.
6. Seed the recovered currencies manually.
7. Repoint or restart Apache/PHP so `/var/www` talks to the new local MySQL.
8. Verify the live site and browser-rendered pages.

## Concrete Rebuild Plan

### Phase 1: Preserve existing state

- Do not delete `/var/lib/mysql`.
- Move the current broken datadir aside instead of overwriting it.
- Keep `/var/lib/mysql-keyring` untouched in case the missing key file appears later.

Suggested shape:

- `/var/lib/mysql.broken-<timestamp>`

### Phase 2: Fresh MySQL

Initialize a new MySQL datadir and start MySQL on the normal local port.

Requirements:

- MySQL must listen on `127.0.0.1:3306`
- PHP/YiiMP config should continue using `127.0.0.1`

### Phase 3: DB bootstrap

Create:

- database: `yaamp`
- user: `yaamp`
- password: `teamrsi123teamrsi123teamrsi123`

Grant that user full access to `yaamp`.

### Phase 4: Base import

Import:

- [`sql/2024-03-06-complete_export.sql.gz`](/home/kp/repos/yiimp/sql/2024-03-06-complete_export.sql.gz)

Then apply repo SQL files in order:

- [`sql/2024-03-18-add_aurum_algo.sql`](/home/kp/repos/yiimp/sql/2024-03-18-add_aurum_algo.sql)
- [`sql/2024-03-29-add_github_version.sql`](/home/kp/repos/yiimp/sql/2024-03-29-add_github_version.sql)
- [`sql/2024-03-31-add_payout_threshold.sql`](/home/kp/repos/yiimp/sql/2024-03-31-add_payout_threshold.sql)
- [`sql/2024-04-01-add_auto_exchange.sql`](/home/kp/repos/yiimp/sql/2024-04-01-add_auto_exchange.sql)
- [`sql/2024-04-01-shares_blocknumber.sql`](/home/kp/repos/yiimp/sql/2024-04-01-shares_blocknumber.sql)
- [`sql/2024-04-05-algos_port_color.sql`](/home/kp/repos/yiimp/sql/2024-04-05-algos_port_color.sql)
- [`sql/2024-04-22-add_equihash_algos.sql`](/home/kp/repos/yiimp/sql/2024-04-22-add_equihash_algos.sql)
- [`sql/2024-04-23-add_pers_string.sql`](/home/kp/repos/yiimp/sql/2024-04-23-add_pers_string.sql)
- [`sql/2024-04-29-add_sellthreshold.sql`](/home/kp/repos/yiimp/sql/2024-04-29-add_sellthreshold.sql)
- [`sql/2024-05-04-add_neoscrypt_xaya_algo.sql`](/home/kp/repos/yiimp/sql/2024-05-04-add_neoscrypt_xaya_algo.sql)
- [`sql/2025-02-06-add_usemweb.sql`](/home/kp/repos/yiimp/sql/2025-02-06-add_usemweb.sql)
- [`sql/2025-02-13-add_xelisv2-pepew.sql`](/home/kp/repos/yiimp/sql/2025-02-13-add_xelisv2-pepew.sql)
- [`sql/2025-02-23-add_algo_kawpow.sql`](/home/kp/repos/yiimp/sql/2025-02-23-add_algo_kawpow.sql)
- [`sql/2025-03-31-rename_table_exchange.sql`](/home/kp/repos/yiimp/sql/2025-03-31-rename_table_exchange.sql)
- later 2025 algo files as needed

### Phase 5: Coin seeding

Seed with three data sources:

1. Base BTC row from the export
2. SQL from [`/home/kp/repos/OpenCEX/yiimp_new_coins.sql`](/home/kp/repos/OpenCEX/yiimp_new_coins.sql)
3. Manual inserts/updates for the rest of the recovered live coins

Priority symbols to restore visibly on the site:

- BTC
- LTC
- DOGE
- DGB
- BELLS
- FLO
- GLC
- MONA
- SOH
- PEP
- VIA
- EMC2
- GAME
- IFC
- NXE
- CAT

For the manual seed work:

- Use `rpcuser=opencex`
- Pull RPC password values from [`/home/kp/bin/check-coin-sync.sh`](/home/kp/bin/check-coin-sync.sh)
- Use the symbol-to-port mapping from the same file
- Use visible/enabled flags conservatively at first:
  - `visible=1`
  - `enable=0`
  - `auto_ready=0`
  - `installed=0`

Then enable coins one by one once their wallets are confirmed reachable and synced.

### Phase 6: Apache / live site verification

After DB import and seeding:

- restart MySQL
- reload Apache
- verify:
  - `/`
  - `/site/mining`
  - `/site/current_results`
  - `/site/history_results`
  - `/site/coins_info`

Use browser automation after the site starts rendering fully again.

## Recovered Operational Behavior

The last live setup auto-enabled synced coins using:

- [`/home/kp/bin/check-coin-sync.sh`](/home/kp/bin/check-coin-sync.sh)

That script is useful for post-rebuild validation because it already encodes:

- expected symbols
- expected RPC ports
- expected RPC credentials
- previous coin IDs

## Known Gaps

- The original balances, miners, earnings, and historical pool state are not recoverable from the broken encrypted DB without the missing keyring file.
- Some coin rows were only partially recoverable from `coins.ibd` string extraction.
- `CAT`, `IFC`, and `NXE` are definitely present in the old DB, but some nonessential fields may need to be reconstructed from wallet defaults and daemon configs.

## Recommended Next Actions For Droid

1. Stop trying to recover the old encrypted datadir unless a real keyring file appears.
2. Build a fresh MySQL instance locally.
3. Import the base YiiMP dump.
4. Apply all repo SQL migrations.
5. Create a dedicated seed SQL for the recovered GiggaHash currency set.
6. Seed the coins using:
   - repo SQL
   - `check-coin-sync.sh`
   - `yiimp_new_coins.sql`
   - `coins.ibd` string evidence
7. Verify frontend rendering in a browser against the live Apache-served site.

## Local Development Setup (COMPLETED)

### MySQL Database

- **Host**: `127.0.0.1:3306`
- **Root Password**: `teamrsi123teamrsi123teamrsi123`
- **Database**: `yaamp`
- **DB User**: `yaamp`
- **DB Password**: `teamrsi123teamrsi123teamrsi123`

### Web Config

- **Config File**: `/home/kp/repos/yiimp/web/serverconfig.php`
- **Site Name**: `GiggaHash Local`
- **Site URL**: `localhost`
- **Apache Port**: `8888`
- **Web Root**: `/home/kp/repos/yiimp/web`

### Coins Imported

15 coins seeded with RPC ports:
- BTC (sha256, port 10301)
- DOGE (scrypt, port 22555)
- DGB (sha256d, port 14022)
- VIA (scrypt, port 5222)
- EMC2 (scrypt, port 4188)
- GAME (scrypt, port 40001)
- MONA (lyra2re2, port 9401)
- GLC (scrypt, port 8122)
- FLO (scrypt, port 7313)
- SOH (x11, port 32717)
- PEP (x11, port 33873)
- BELLS (scrypt, port 19918)
- IFC (scrypt, port 9322)
- NXE (x11, port 29432)
- CAT (scrypt, port 9335)

### To Enable a Coin

```sql
UPDATE coins SET enable=1, installed=1 WHERE symbol='BTC';
```

## Notes On Security

This host stores credentials in plaintext in:

- [`/etc/yiimp/serverconfig.php`](/etc/yiimp/serverconfig.php)
- [`/home/kp/bin/check-coin-sync.sh`](/home/kp/bin/check-coin-sync.sh)
- [`/home/kp/repos/yiimp/web/serverconfig.php`](/home/kp/repos/yiimp/web/serverconfig.php)

Do not duplicate secrets more than necessary. Prefer reading from those files instead of spreading them into new docs or scripts.
