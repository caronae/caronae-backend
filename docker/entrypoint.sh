#!/bin/sh
set -eo pipefail

function run_migrations() {
    if [ "$APP_ENV" == "production" ] || [ "$APP_ENV" == "staging" ]; then
        php artisan backup:run --no-interaction --no-ansi
    fi

    php artisan migrate --force
}

function optimize_laravel() {
    if [ "$APP_ENV" == "production" ] || [ "$APP_ENV" == "staging" ]; then
        php artisan cache:clear
        php artisan view:clear
        php artisan config:cache
        php artisan route:cache
    fi
}

command=$1

cd /var/www

case ${command} in
    server)
        echo 'Starting application'
        export LOG_CHANNEL=stdout
        export LOG_STREAM=/tmp/stdout
        if [ ! -p ${LOG_STREAM} ]; then
            mkfifo -m 666 ${LOG_STREAM}
        fi

        run_migrations
        optimize_laravel

        php-fpm -D | tail -f ${LOG_STREAM}
    ;;
    queue)
        echo 'Starting queue processor'
        php artisan queue:work --sleep=2 --tries=3
    ;;
    scheduler)
        echo 'Starting task scheduler'
        while [ true ]
        do
            php artisan schedule:run --verbose --no-interaction &
            sleep 60
        done
    ;;
    test)
        echo 'Installing dependencies'
        composer install --no-interaction --no-ansi
        echo 'Starting tests'
        ./vendor/bin/phpunit --debug --log-junit reports/phpunit/junit.xml
    ;;
    *)
        echo 'Invalid operation'
        exit 1
        ;;
esac
