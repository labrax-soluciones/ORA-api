#!/usr/bin/env sh
set -e

# Asegura directorios y permisos en cada arranque (por si el bind-mount los pisa)
mkdir -p /var/www/storage/logs /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
find /var/www/storage /var/www/bootstrap/cache -type d -exec chmod 775 {} \; || true
find /var/www/storage /var/www/bootstrap/cache -type f -exec chmod 664 {} \; || true

# Genera APP_KEY si falta
if [ -f /var/www/.env ]; then
  if ! grep -q '^APP_KEY=' /var/www/.env || [ -z "$(grep '^APP_KEY=' /var/www/.env | cut -d= -f2)" ]; then
    php artisan key:generate --force || true
  fi
fi

# Limpia caches por si cambiaste rutas/config
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true

exec "$@"
