#!/bin/sh
set -eu

if [ -n "${DB_SSL_CA_CONTENT:-}" ]; then
    ca_path="${DB_SSL_CA:-/etc/ssl/certs/aiven-ca.pem}"
    printf '%s' "$DB_SSL_CA_CONTENT" > "$ca_path"
    chmod 0644 "$ca_path"
fi

exec "$@"
