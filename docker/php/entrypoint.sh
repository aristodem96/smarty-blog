#!/bin/sh
# Runs on every container start, so every step here must be idempotent.
set -e

# No-op when vendor/ already matches composer.lock.
composer install --no-interaction --prefer-dist --no-progress

# Smarty writes compiled templates into var/; PHP-FPM workers run as www-data.
mkdir -p var
chown -R www-data:www-data var

# Hand over to the official image entrypoint (starts php-fpm by default).
exec docker-php-entrypoint "$@"
