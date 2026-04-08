# New 2026 PoW Coins - Integration Notes

Created: 2026-03-22

## Supported Coin

| Symbol | Name | Algorithm | Stratum Port | RPC Port | Config File |
|--------|------|-----------|--------------|----------|-------------|
| QNTM | Quantum Token | scrypt | 17337 | 17335 | qntm.conf |

## Files Created

- `sql/2026-03-22-add-new-2026-coins.sql` - Database migration
- `stratum/config.sample/qntm.conf` - QNTM stratum config
- `sql/2026-03-30-remove-nonrpc-coins.sql` - Cleanup migration for unsupported coins

## Setup Steps

### 1. Import SQL Migration

```bash
mysql yaamp < /home/kp/repos/yiimp/sql/2026-03-22-add-new-2026-coins.sql
```

### 2. Deploy Coin Wallets

Download and install the `QNTM` wallet from:

- **QNTM**: https://quantum-token.net/ (Windows/Linux wallets available)

### 3. Configure Coin Daemons

Create daemon config files in `/etc/` (or wallet directory):

**QNTM** (`qntm.conf`):
```ini
rpcuser=qntm
rpcpassword=Qn7mT0k3nRpcW8x3pLv1
rpcport=17335
rpcallowip=127.0.0.1
rpcbind=127.0.0.1
server=1
daemon=0
listen=1
txindex=1
addnode=77.105.161.72
```

### 4. Copy Stratum Configs

```bash
sudo install -D -m 0644 /home/kp/repos/yiimp/stratum/config.sample/silvr.conf /etc/yiimp/stratum/silvr.conf
sudo install -D -m 0644 /home/kp/repos/yiimp/stratum/config.sample/brics.conf /etc/yiimp/stratum/brics.conf
sudo install -D -m 0644 /home/kp/repos/yiimp/stratum/config.sample/qntm.conf /etc/yiimp/stratum/scrypt-qntm.conf
```

### 5. Install Process Units

The current host uses `systemd`, not `supervisord`. Install unit files under `/etc/systemd/system/` and point them at `/etc/yiimp/stratum/*.conf`.

```ini
[Unit]
Description=YiiMP Stratum Scrypt-QNTM dedicated
After=network.target
Wants=network.target

[Service]
ExecStart=/usr/local/bin/stratum /etc/yiimp/stratum/scrypt-qntm.conf
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

### 6. Enable Mining (After Wallet Sync)

```sql
UPDATE coins
SET enable=1, visible=1, installed=1, auto_ready=1, dedicatedport=17337
WHERE symbol = 'QNTM';
```

### 7. Restart Services

```bash
sudo systemctl daemon-reload
sudo systemctl enable qntmd stratum-scrypt-qntm
sudo systemctl restart qntmd stratum-scrypt-qntm
```

## ANN Thread Reference

- QNTM: https://bitcointalk.org/index.php?topic=5576829.0

## Addnodes Summary

- **QNTM**: `addnode=77.105.161.72`

## Existing Pools

- **QNTM**: ANN references external pool support. Validate protocol compatibility locally before enabling.
