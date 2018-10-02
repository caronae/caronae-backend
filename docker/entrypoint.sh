#!/bin/sh

set -e

command=$1

export LOG_CHANNEL=stdout
export LOG_STREAM=/tmp/stdout-${command}
if [ ! -p ${LOG_STREAM} ]; then
    mkfifo -m 666 ${LOG_STREAM}
fi

case $command in
    server)
        echo 'Starting application'
        php-fpm -D | tail -f ${LOG_STREAM}
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
