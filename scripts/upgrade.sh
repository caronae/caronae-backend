#!/bin/bash
set -eo pipefail
cd "$(dirname ${BASH_SOURCE[0]})"

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
