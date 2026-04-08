# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

<!-- gitnexus:start -->
# GitNexus MCP

This project is indexed by GitNexus as **yiimp** (21677 symbols, 45869 relationships, 300 execution flows).

## Always Start Here

1. **Read `gitnexus://repo/{name}/context`** — codebase overview + check index freshness
2. **Match your task to a skill below** and **read that skill file**
3. **Follow the skill's workflow and checklist**

> If step 1 warns the index is stale, run `npx gitnexus analyze` in the terminal first.

## Skills

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |

<!-- gitnexus:end -->

---

## Project Overview

Yiimp is a cryptocurrency mining pool platform (fork of Yaamp). It runs multiple algorithms simultaneously, accepts miner connections via stratum, and manages payouts, coin tracking, and exchange integration.

## Build Commands

### Container (primary workflow)

```bash
make build          # Build production Docker/Podman image
make build-devel    # Build development image (mounts web/ and yiimp2/ as volumes)
make run            # Start production container
make run-devel      # Start development container (live code reload via volume mounts)
```

### Stratum (C++)

```bash
cd stratum
make                # Full build (fetches git submodules, builds secp256k1, algos, sha3, iniparser)
make buildonly      # Build without submodule init
make install        # Build + strip + copy to /usr/local/bin/ and ../bin/
make clean          # Remove build artifacts
```

Dependencies: `gcc`, `libmysqlclient`, `libcurl`, `libssl`, `libsodium`, `libgmp`, `libstdc++`

### blocknotify (C++)

```bash
cd blocknotify && make
```

### blocknotify-dcr (Go — Decred only)

```bash
cd blocknotify-dcr && go build
```

## Architecture

### Three-layer system

1. **Stratum layer** (`stratum/`) — C++ daemon. One process per algorithm. Accepts miner TCP connections, validates shares, talks to coin daemons via RPC, stores shares to MySQL. Started via `stratum/run.sh <algo>`. Config files in `config/stratum/` and `stratum/config.sample/`.

2. **PHP backend** (`web/`) — Yii 1.x MVC application. Three shell loops run continuously:
   - `web/main.sh` — Runs `php runconsole.php cronjob/run` every 90 seconds (payments, coin updates, market data)
   - `web/loop2.sh` — Secondary maintenance loop
   - `web/blocks.sh` — Block monitoring loop

   Backend logic lives in `web/yaamp/core/` (functions/, backend/, exchange/, rpc/, trading/).

3. **Web frontend** (`web/` + `yiimp2/`) — Two apps:
   - `web/` — Main public pool site (Yii 1.x). Modules: `admin`, `api`, `site`, `coin`, `stats`, `market`, `explorer`, `bench`, `nicehash`, `renting`, `trading`.
   - `yiimp2/` — Modern admin panel (Yii 2.x, at a separate Apache vhost). Controllers: `Admin`, `Explorer`, `Site`, `Stats`.

### Block notification flow

Coin daemon → `blocknotify` binary → stratum TCP port → stratum fetches new block template → broadcasts to miners

### Configuration

- `/etc/yiimp/serverconfig.php` — Main runtime config (DB credentials, site URL, fees, admin access). Use `web/serverconfig.sample.php` as template.
- `/etc/yiimp/keys.php` — Exchange API keys (separate file for security).
- `config/supervisord.conf` — Controls all processes inside the container (stratum instances, PHP loops, Apache).

## Database

MySQL with database name `yaamp`. Requires two DB users: one for PHP web/backend, one for stratum (set in algo `.conf` files).

**Schema setup:** Import `sql/2024-03-06-complete_export.sql.gz`, then apply all `sql/YYYY-MM-DD-*.sql` files in date order.

**Migrations:** New features add SQL files to `sql/`. Always create a migration file when adding algorithms or schema changes.

## Adding a New Algorithm

Three changes required:
1. **Stratum**: Add algo implementation in `stratum/algos/` (C source), register in `stratum/algos/Makefile`.
2. **SQL migration**: Add entry to `algos` table (see existing `sql/2025-*.sql` files as patterns).
3. **Web config**: Add algo config sample to `stratum/config.sample/`.

## Development Workflow

The development container (`make run-devel`) mounts `web/` and `yiimp2/` as live volumes — PHP changes take effect immediately without rebuilding.

For stratum changes, rebuild the binary (`cd stratum && make`) and restart the stratum process via supervisorctl:

```bash
supervisorctl -u yiimp -p supervisor -s http://127.0.0.1:8900 restart stratum-<algo>
```

Admin UI: `http://localhost:8900/` (supervisord web interface).

## Key File Locations (inside container)

| Path | Purpose |
|------|---------|
| `/var/www/` | Web root (Yii 1.x app) |
| `/var/yiimp2/` | Yii 2.x admin panel |
| `/etc/yiimp/serverconfig.php` | Runtime config |
| `/var/www/log/` | PHP application logs |
| `/var/log/stratum/debug.log` | Stratum debug log |
| `/usr/local/bin/stratum` | Compiled stratum binary |
