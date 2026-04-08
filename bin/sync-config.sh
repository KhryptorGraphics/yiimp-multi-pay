#!/bin/bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

copy_file() {
	local src="$1"
	local dst="$2"

	if [ ! -f "$src" ]; then
		echo "Missing source: $src" >&2
		exit 1
	fi

	echo "Syncing $src -> $dst"
	sudo install -D -m 0644 "$src" "$dst"
}

copy_file "$REPO_ROOT/web/yaamp/config.php" /var/www/yaamp/config.php
copy_file "$REPO_ROOT/config/stratum/scrypt.conf" /etc/yiimp/stratum/scrypt.conf
copy_file "$REPO_ROOT/config/stratum/spacescrypt.conf" /etc/yiimp/stratum/spacescrypt.conf
copy_file "$REPO_ROOT/config/stratum/sha256.conf" /etc/yiimp/stratum/sha256.conf
copy_file "$REPO_ROOT/config/stratum/sha256d.conf" /etc/yiimp/stratum/sha256d.conf
copy_file "$REPO_ROOT/config/stratum/lyra2v2.conf" /etc/yiimp/stratum/lyra2v2.conf

copy_file "$REPO_ROOT/config/systemd/yiimp-main.service" /etc/systemd/system/yiimp-main.service
copy_file "$REPO_ROOT/config/systemd/yiimp-loop2.service" /etc/systemd/system/yiimp-loop2.service
copy_file "$REPO_ROOT/config/systemd/yiimp-blocks.service" /etc/systemd/system/yiimp-blocks.service
copy_file "$REPO_ROOT/config/systemd/stratum-scrypt.service" /etc/systemd/system/stratum-scrypt.service
copy_file "$REPO_ROOT/config/systemd/stratum-spacescrypt.service" /etc/systemd/system/stratum-spacescrypt.service
copy_file "$REPO_ROOT/config/systemd/stratum-sha256.service" /etc/systemd/system/stratum-sha256.service
copy_file "$REPO_ROOT/config/systemd/stratum-sha256d.service" /etc/systemd/system/stratum-sha256d.service
copy_file "$REPO_ROOT/config/systemd/stratum-lyra2v2.service" /etc/systemd/system/stratum-lyra2v2.service

sudo systemctl daemon-reload
sudo systemctl enable yiimp-main yiimp-loop2 yiimp-blocks stratum-scrypt stratum-spacescrypt stratum-sha256 stratum-sha256d stratum-lyra2v2

echo
echo "Runtime config synced."
echo "Start services with:"
echo "  sudo systemctl restart yiimp-main yiimp-loop2 yiimp-blocks stratum-scrypt stratum-spacescrypt stratum-sha256 stratum-sha256d stratum-lyra2v2"
