#!/bin/bash
set -eo pipefail

# Backup database
php artisan backup:run --no-interaction --no-ansi

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan view:clear

# Optimize caches
php artisan config:cache
php artisan route:cache

# Restart the queue workers
php artisan queue:restart
