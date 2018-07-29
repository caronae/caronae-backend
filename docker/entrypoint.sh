#!/bin/sh

set -e

command=$1

case $command in
    server)
        echo 'Starting application'
        php-fpm -D | tail -f $LOG_STREAM
    ;;
    queue)
        echo 'Starting queue processor'
        php /var/www/artisan queue:work --sleep=2 --tries=3
    ;;
    scheduler)
        echo 'Starting task scheduler'
        while [ true ]
        do
            php /var/www/artisan schedule:run --verbose --no-interaction &
            sleep 60
        done
    ;;
    *)
        echo 'Invalid operation'
        exit 1
        ;;
esac
