#!/bin/bash
set -e
cd "$(dirname ${BASH_SOURCE[0]})"

# Upgrade dependencies
composer install --no-interaction --no-ansi --no-dev

# Run database migrations
php artisan migrate --force

# Restart the queue workers
php artisan queue:restart
