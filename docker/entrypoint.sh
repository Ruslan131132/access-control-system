#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -d vendor/laravel ] || [ ! -f vendor/autoload.php ] || [ ! -d vendor/nunomaduro/collision ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

rm -f bootstrap/cache/packages.php bootstrap/cache/services.php 2>/dev/null || true

if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
  php artisan key:generate --force --no-interaction
fi

echo "Waiting for MySQL..."
i=0
until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
  i=$((i + 1))
  if [ "$i" -gt 60 ]; then
    echo "MySQL is not available"
    exit 1
  fi
  sleep 2
done

echo "Waiting for RabbitMQ..."
i=0
until php -r "
\$fp = @fsockopen(getenv('RABBITMQ_HOST') ?: 'rabbitmq', (int)(getenv('RABBITMQ_PORT') ?: 5672), \$e, \$s, 2);
if (!\$fp) { exit(1); }
fclose(\$fp);
" 2>/dev/null; do
  i=$((i + 1))
  if [ "$i" -gt 60 ]; then
    echo "RabbitMQ is not available"
    exit 1
  fi
  sleep 2
done

if [ "${SKIP_DB_SETUP:-0}" != "1" ]; then
  php artisan migrate --force --no-interaction
  php artisan db:seed --force --no-interaction
  php artisan storage:link --force --no-interaction 2>/dev/null || true
fi

exec "$@"
