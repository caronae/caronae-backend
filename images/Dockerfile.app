FROM php:7.1-fpm-alpine

WORKDIR /var/www

RUN set -ex \
  && apk --repository http://dl-2.alpinelinux.org/alpine/edge/community/ --no-cache add \
    postgresql-dev \
    libxml2-dev \
    curl-dev \
    shadow

RUN docker-php-ext-install pdo pdo_pgsql pgsql zip xml curl mbstring

COPY images/php.logs.ini /usr/local/etc/php/conf.d/logs.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

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
COPY storage ./storage
COPY vendor ./vendor

RUN chown -R www-data:www-data storage 
RUN ls -la .

# RUN rm storage/logs/*


VOLUME /var/www

# drwxr-xr-x   18 root     root          4096 Aug 31 14:22 app
# -rw-r--r--    1 root     root          1646 Mar  7 14:06 artisan
# drwxr-xr-x    3 root     root          4096 Jun  7 05:06 bootstrap
# -rw-r--r--    1 root     root          1067 Aug 23 01:48 circle.yml
# -rw-r--r--    1 root     root           777 Mar 31 20:35 circle.yml~
# -rw-r--r--    1 root     root          1867 Jul  3 01:05 composer.json
# -rw-r--r--    1 root     root        215926 Jul  3 01:05 composer.lock
# drwxr-xr-x    4 root     root          4096 Jul  3 01:05 config
# drwxr-xr-x    5 root     root          4096 Mar  7 14:06 database
# drwxr-xr-x    2 root     root          4096 Sep  1 20:39 html
# drwxr-xr-x    2 root     root          4096 Sep  2 02:18 images
# -rw-r--r--    1 root     root          1006 Jul  3 01:05 phpunit.xml
# drwxr-xr-x    7 root     root          4096 Jul  4 01:49 public
# drwxr-xr-x    4 root     root          4096 Mar  7 14:06 resources
# drwxr-xr-x    2 root     root          4096 Aug 31 14:22 routes
# -rw-r--r--    1 root     root           567 Mar  7 14:06 server.php
# drwxr-xr-x    5 root     root          4096 Apr 21 05:18 storage
# drwxr-xr-x    7 root     root          4096 Jul  8 23:46 tests
# -rwxr-xr-x    1 root     root           309 Jul  3 01:05 upgrade.sh
# drwxr-xr-x   54 root     root          4096 Aug 31 13:55 vendor