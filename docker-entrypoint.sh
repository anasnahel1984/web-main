#!/bin/sh
set -eu

# Populate /var/www/html dari /app-src jika belum ada index.php
# Ini terjadi saat named volume baru pertama kali di-mount
if [ ! -f /var/www/html/public/index.php ]; then
    echo "[entrypoint] First run: populating /var/www/html from /app-src..."
    cp -a /app-src/. /var/www/html/
    chown -R www-data:www-data /var/www/html
    chmod -R 775 /var/www/html/writable
    echo "[entrypoint] Done."
else
    echo "[entrypoint] /var/www/html already populated, skipping copy."
fi

exec "$@"
