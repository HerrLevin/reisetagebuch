#!/bin/sh

# Exit script on error
set -e

echo "Starting Reisetagebuch..."

if [ ! -f /var/www/html/.env ]; then
  echo "Environment file not found. Creating a new one..."
  touch /var/www/html/.env

  [ -z "$APP_ENV" ]   && echo "APP_ENV=production" >> /var/www/html/.env
  [ -z "$APP_DEBUG" ] && echo "APP_DEBUG=false" >> /var/www/html/.env
  [ -z "$APP_KEY" ]   && echo "APP_KEY=base64:$(openssl rand -base64 32)" >> /var/www/html/.env
fi

# Set correct permissions (may fail when running as non-root, which is fine
# since the Dockerfile already sets correct ownership)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start Supervisor in the background
supervisord -c /etc/supervisord.conf &

# Run database migrations and other artisan commands
echo "Running Laravel artisan commands..."

# echo "Running migrations..."
php artisan migrate --force

echo "Clearing caches..."
php artisan optimize

echo "Generating Passport keys (skipped if they already exist)..."
php artisan passport:keys || true

echo "Restart queue workers..."
php artisan queue:restart

# Keep the container running
wait
