FROM caronae/php:latest

COPY composer.lock composer.json ./
COPY database ./database

RUN composer install --no-interaction --no-ansi --no-dev --no-scripts

COPY . ./

RUN composer dump-autoload --no-interaction --no-ansi --no-dev

RUN chown -R www-data:www-data \
        /var/www/storage \
        /var/www/bootstrap/cache

COPY docker/entrypoint.sh /root/entrypoint.sh
RUN mv docker/entrypoint.sh /root/entrypoint.sh && rm -rf ./docker

VOLUME /var/www

ENTRYPOINT ["/root/entrypoint.sh"]
CMD ["server"]
