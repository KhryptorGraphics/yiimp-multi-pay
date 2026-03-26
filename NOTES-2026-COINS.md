# New 2026 PoW Coins - Setup Guide

Created: 2026-03-22

## Coins Added

| Symbol | Name | Algorithm | Stratum Port | RPC Port | Config File |
|--------|------|-----------|--------------|----------|-------------|
| SILVR | Silver Protocol | sha256d | 17333 | 17333 | silvr.conf |
| BRICS | BricsCoin | sha256 | 17334 | 17334 | brics.conf |
| QNTM | Quantum Token | scrypt | 17335 | 17335 | qntm.conf |
| NXF | NexaFlow | sha256d | 17336 | 17336 | nexaflow.conf |

## Files Created

- `sql/2026-03-22-add-new-2026-coins.sql` - Database migration
- `stratum/config.sample/silvr.conf` - SILVR stratum config
- `stratum/config.sample/brics.conf` - BricsCoin stratum config
- `stratum/config.sample/qntm.conf` - QNTM stratum config
- `stratum/config.sample/nexaflow.conf` - NexaFlow stratum config

## Setup Steps

### 1. Import SQL Migration

```bash
mysql yaamp < /home/kp/repos/yiimp/sql/2026-03-22-add-new-2026-coins.sql
```

### 2. Deploy Coin Wallets

Download and install wallets from ANN threads:

- **SILVR**: https://github.com/him-SILVR/SILVR (MAINNET LIVE as of March 16, 2026)
- **BricsCoin**: https://codeberg.org/Bricscoin_26/Bricscoin
- **QNTM**: https://quantum-token.net/ (Windows/Linux wallets available)
- **NexaFlow**: https://github.com/nexaflow-ledger (Mainnet launch March 31, 2026)

### 3. Configure Coin Daemons

Create daemon config files in `/etc/` (or wallet directory):

**SILVR** (`silvr.conf`):
```ini
rpcuser=silvr
rpcpassword=S1lv3r#Rpc$K9x2mNq8
rpcport=17333
rpcallowip=127.0.0.1
server=1
daemon=1
listen=1
# Note: P2P Port is 8633, Chain ID 2026
# Addnodes: Contact SILVR dev for addnode list
```

**BRICS** (`brics.conf`):
```ini
rpcuser=brics
rpcpassword=Br1csC0in#Rpc$J5k9mNv2
rpcport=17334
rpcallowip=127.0.0.1
server=1
daemon=1
listen=1
# Note: Contact BricsCoin dev for addnode list
# Website brics-coin.com is currently suspended
```

**QNTM** (`qntm.conf`):
```ini
rpcuser=qntm
rpcpassword=Qn7mT0k3n#Rpc$W8x3pLv1
rpcport=17335
rpcallowip=127.0.0.1
server=1
daemon=1
listen=1
addnode=77.105.161.72
```

**NXF** (`nxf.conf`):
```ini
rpcuser=nxf
rpcpassword=N3x4Fl0w#Rpc$M2k7qRv9
rpcport=17336
rpcallowip=127.0.0.1
server=1
daemon=1
listen=1
# Note: Uses RPCA/BFT consensus, not traditional PoW addnodes
# Main coin is not mineable - only Programmable Micro Coins use PoW
```

### 4. Copy Stratum Configs

```bash
cp /home/kp/repos/yiimp/stratum/config.sample/silvr.conf /etc/yiimp/
cp /home/kp/repos/yiimp/stratum/config.sample/brics.conf /etc/yiimp/
cp /home/kp/repos/yiimp/stratum/config.sample/qntm.conf /etc/yiimp/
cp /home/kp/repos/yiimp/stratum/config.sample/nexaflow.conf /etc/yiimp/
```

### 5. Update supervisord.conf

Add stratum processes for each coin:

```ini
[program:stratum-silvr]
command=/usr/local/bin/stratum /etc/yiimp/silvr.conf
autostart=true
autorestart=true
stdout_logfile=/var/log/stratum/silvr.log
stderr_logfile=/var/log/stratum/silvr.error.log

[program:stratum-brics]
command=/usr/local/bin/stratum /etc/yiimp/brics.conf
...

[program:stratum-qntm]
command=/usr/local/bin/stratum /etc/yiimp/qntm.conf
...

[program:stratum-nexaflow]
command=/usr/local/bin/stratum /etc/yiimp/nexaflow.conf
...
```

### 6. Enable Mining (After Wallet Sync)

```sql
UPDATE coins SET mining=1, enabled=1 WHERE symbol IN ('SILVR','BRICS','QNTM','NXF');
```

Or via Yiimp admin panel.

### 7. Restart Services

```bash
supervisorctl reread
supervisorctl update
supervisorctl restart all
```

## ANN Thread References

- SILVR: https://bitcointalk.org/index.php?topic=5577498.0
- BricsCoin: https://bitcointalk.org/index.php?topic=5577339.0
- QNTM: https://bitcointalk.org/index.php?topic=5576829.0
- NexaFlow: https://bitcointalk.org/index.php?topic=5576225.0

## Addnodes Summary

### Found
- **QNTM**: `addnode=77.105.161.72`

### NOT Found (Contact coin devs)
- **SILVR**: No addnodes in ANN. Contact via GitHub or mine solo initially.
- **BricsCoin**: Website suspended. Contact via Codeberg or Bitcointalk.
- **NexaFlow**: Uses RPCA/BFT consensus. No traditional PoW addnodes.

## Existing Pools

- **QNTM**: Already has pools running (e.g., miningcoin.online:3433, altcoinspool.cc:8611)
