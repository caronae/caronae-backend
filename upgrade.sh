#!/bin/bash
set -e
cd "$(dirname ${BASH_SOURCE[0]})"

# Upgrade dependencies
composer install --no-interaction --no-ansi --no-dev

# Run database migrations
php artisan migrate --force

# Restart the queue worker
if hash supervisorctl 2>/dev/null; then
  supervisorctl restart laravel-worker:
fi
