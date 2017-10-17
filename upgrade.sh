#!/bin/bash
set -e
cd "$(dirname ${BASH_SOURCE[0]})"

# Upgrade dependencies
composer install --no-interaction --no-ansi --no-dev

# Backup database
php artisan backup:run --no-interaction --no-ansi

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan config:cache
php artisan cache:clear
php artisan view:clear

# Restart the queue workers
php artisan queue:restart
