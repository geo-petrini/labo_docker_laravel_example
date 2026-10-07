#!/bin/sh
set -e

cd /var/www/app

php artisan key:generate --force
php artisan migrate --force

exec apache2-foreground