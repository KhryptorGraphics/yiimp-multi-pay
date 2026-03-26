#!/usr/bin/env bash

set -euo pipefail

DOMAIN="giggahash.com"
ALT_DOMAIN="www.giggahash.com"
DOCROOT="/var/www"
HTTP_VHOST="/etc/apache2/sites-available/giggahash.com.conf"
SSL_VHOST="/etc/apache2/sites-available/giggahash-ssl.conf"
EMAIL="${LE_EMAIL:-admin@giggahash.com}"
CERT_DIR="/etc/letsencrypt/live/${DOMAIN}"

log() {
  printf '[fix-giggahash-ssl] %s\n' "$*"
}

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || {
    printf 'Missing required command: %s\n' "$1" >&2
    exit 1
  }
}

if [[ "$(id -u)" -ne 0 ]]; then
  printf 'This script must be run as root (use sudo).\n' >&2
  exit 1
fi

require_cmd apache2ctl
require_cmd a2ensite
require_cmd a2dissite
require_cmd certbot
require_cmd systemctl
require_cmd openssl

if [[ ! -f "$HTTP_VHOST" ]]; then
  printf 'Expected HTTP vhost not found: %s\n' "$HTTP_VHOST" >&2
  exit 1
fi

log "Checking existing Apache vhost map"
apache2ctl -S >/dev/null

if [[ ! -f "${CERT_DIR}/fullchain.pem" || ! -f "${CERT_DIR}/privkey.pem" ]]; then
  log "Issuing or renewing certificate for ${DOMAIN} and ${ALT_DOMAIN}"
  mkdir -p "${DOCROOT}/.well-known/acme-challenge"
  certbot certonly \
    --webroot \
    -w "$DOCROOT" \
    --keep-until-expiring \
    --non-interactive \
    --agree-tos \
    --email "$EMAIL" \
    -d "$DOMAIN" \
    -d "$ALT_DOMAIN"
else
  log "Existing certificate found at ${CERT_DIR}; skipping issuance"
fi

if [[ ! -f /etc/letsencrypt/options-ssl-apache.conf ]]; then
  printf 'Missing /etc/letsencrypt/options-ssl-apache.conf\n' >&2
  exit 1
fi

tmpfile="$(mktemp)"
cleanup_tmp() {
  rm -f "$tmpfile"
}
trap cleanup_tmp EXIT

cat >"$tmpfile" <<EOF
<VirtualHost *:443>
    ServerName ${DOMAIN}
    ServerAlias ${ALT_DOMAIN}
    DocumentRoot ${DOCROOT}

    SSLEngine on

    <Directory "${DOCROOT}/">
        Options -Indexes
        Require all granted
        AllowOverride all
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-ssl-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-ssl-access.log combined

    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile ${CERT_DIR}/fullchain.pem
    SSLCertificateKeyFile ${CERT_DIR}/privkey.pem
</VirtualHost>
EOF

if [[ -f "$SSL_VHOST" ]] && ! cmp -s "$tmpfile" "$SSL_VHOST"; then
  backup="${SSL_VHOST}.bak.$(date +%Y%m%d%H%M%S)"
  cp "$SSL_VHOST" "$backup"
  log "Backed up existing SSL vhost to ${backup}"
fi

install -m 0644 "$tmpfile" "$SSL_VHOST"
log "Installed ${SSL_VHOST}"

site_enabled_now=0
if [[ ! -L "/etc/apache2/sites-enabled/$(basename "$SSL_VHOST")" ]]; then
  a2ensite "$(basename "$SSL_VHOST")"
  site_enabled_now=1
fi

log "Validating Apache configuration"
if ! apache2ctl configtest; then
  if [[ "$site_enabled_now" -eq 1 ]]; then
    a2dissite "$(basename "$SSL_VHOST")" >/dev/null
  fi
  printf 'Apache configtest failed; SSL vhost was not reloaded.\n' >&2
  exit 1
fi

log "Reloading Apache"
systemctl reload apache2

log "Verifying local SNI certificate"
echo | openssl s_client -connect 127.0.0.1:443 -servername "$DOMAIN" 2>/dev/null \
  | openssl x509 -noout -subject -ext subjectAltName

log "Done. Recommended external checks:"
printf '  curl -I https://%s\n' "$DOMAIN"
printf '  curl -I https://giggatrade.com\n'
