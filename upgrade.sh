#!/bin/bash
set -e
cd "$(dirname ${BASH_SOURCE[0]})"

# Upgrade dependencies
composer install --no-interaction --no-ansi --no-dev

# Backup database
php artisan backup:run --no-interaction --no-ansi

# Run database migrations
php artisan migrate --force

# Restart the queue workers
php artisan queue:restart
