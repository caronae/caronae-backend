FROM caronae/php:latest

ENV LOG_STREAM="/tmp/backend.log"
RUN mkfifo $LOG_STREAM && chmod 777 $LOG_STREAM
CMD ["/bin/sh", "-c", "php-fpm -D | tail -f $LOG_STREAM"]

# Copy application files
COPY composer.json ./
COPY composer.lock ./
COPY artisan ./
COPY app ./app/
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY vendor ./vendor
COPY scripts/update_laravel.sh ./scripts/
COPY docker/entrypoint.sh /caronae-entrypoint.sh

RUN mkdir -p storage/app
RUN mkdir -p storage/logs
RUN mkdir -p storage/framework/cache
RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views

RUN chown -R www-data:www-data bootstrap/cache 
RUN chown -R www-data:www-data storage 

# Install dependencies
RUN composer install --no-interaction --no-ansi --no-dev

VOLUME /var/www

ENTRYPOINT ["/caronae-entrypoint.sh"]
CMD ["server"]
