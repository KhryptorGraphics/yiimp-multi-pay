#!/usr/bin/env bash

set -euo pipefail

KEYRING_DIR=/var/lib/mysql-keyring
PLUGIN_DIR=/usr/lib/mysql/plugin
MYSQLD_MANIFEST=/usr/sbin/mysqld.my
PLUGIN_RECOVERY_CNF=/etc/mysql/conf.d/z-yiimp-keyring-recovery.cnf
COMPONENT_CNF="${PLUGIN_DIR}/component_keyring_file.cnf"
MYSQL_ERROR_LOG=/var/log/mysql/error.log

require_root() {
  if [[ "${EUID}" -ne 0 ]]; then
    printf 'Run this script as root.\n' >&2
    exit 1
  fi
}

backup_file() {
  local path="$1"
  if [[ -e "${path}" ]]; then
    cp -a "${path}" "${path}.bak.$(date +%Y%m%d%H%M%S)"
  fi
}

pick_component_key_file() {
  find "${KEYRING_DIR}" -maxdepth 1 -type f \
    ! -name '*.backup' \
    ! -name '*.cnf' \
    -name 'component_keyring_file*' \
    | sort \
    | head -n 1
}

pick_plugin_key_file() {
  find "${KEYRING_DIR}" -maxdepth 1 -type f \
    ! -name '*.backup' \
    ! -name '*.cnf' \
    \( -name 'keyring' -o -name 'keyring_*' -o -name '*.keyring' \) \
    | sort \
    | head -n 1
}

configure_component_keyring() {
  local data_file="$1"

  backup_file "${MYSQLD_MANIFEST}"
  backup_file "${COMPONENT_CNF}"
  rm -f "${PLUGIN_RECOVERY_CNF}"

  install -d -m 700 -o mysql -g mysql "${KEYRING_DIR}"

  cat > "${MYSQLD_MANIFEST}" <<EOF
{
  "components": "file://component_keyring_file"
}
EOF

  cat > "${COMPONENT_CNF}" <<EOF
{
  "path": "${data_file}",
  "read_only": false
}
EOF

  chown root:mysql "${MYSQLD_MANIFEST}" "${COMPONENT_CNF}"
  chmod 640 "${MYSQLD_MANIFEST}" "${COMPONENT_CNF}"

  printf 'Configured MySQL keyring component with %s\n' "${data_file}"
}

configure_plugin_keyring() {
  local data_file="$1"

  backup_file "${PLUGIN_RECOVERY_CNF}"
  rm -f "${MYSQLD_MANIFEST}" "${COMPONENT_CNF}"

  cat > "${PLUGIN_RECOVERY_CNF}" <<EOF
[mysqld]
early-plugin-load=keyring_file.so
keyring_file_data=${data_file}
EOF

  chown root:root "${PLUGIN_RECOVERY_CNF}"
  chmod 644 "${PLUGIN_RECOVERY_CNF}"

  printf 'Configured MySQL keyring plugin with %s\n' "${data_file}"
}

restart_mysql() {
  systemctl restart mysql
  systemctl is-active --quiet mysql
}

show_failure_context() {
  printf '\nMySQL restart failed. Recent log output:\n' >&2
  tail -n 80 "${MYSQL_ERROR_LOG}" 2>/dev/null || true
}

main() {
  local component_file
  local plugin_file

  require_root

  if [[ ! -d "${KEYRING_DIR}" ]]; then
    printf 'Missing keyring directory: %s\n' "${KEYRING_DIR}" >&2
    exit 1
  fi

  component_file="$(pick_component_key_file || true)"
  plugin_file="$(pick_plugin_key_file || true)"

  if [[ -n "${component_file}" ]]; then
    configure_component_keyring "${component_file}"
  elif [[ -n "${plugin_file}" ]]; then
    configure_plugin_keyring "${plugin_file}"
  else
    configure_component_keyring "${KEYRING_DIR}/component_keyring_file"
  fi

  if ! restart_mysql; then
    show_failure_context
    exit 1
  fi

  printf '\nMySQL restarted successfully.\n'
  printf 'Verify with:\n'
  printf '  mysql -e "SHOW DATABASES;"\n'
  printf '  curl -I https://giggahash.com/\n'
}

main "$@"
