FROM php:7.1-fpm-alpine

WORKDIR /var/www

RUN set -ex \
  && apk --repository http://dl-2.alpinelinux.org/alpine/edge/community/ --no-cache add \
    postgresql-dev \
    libxml2-dev \
    curl-dev

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
COPY vendor ./vendor

RUN mkdir -p storage/app
RUN mkdir -p storage/logs
RUN mkdir -p storage/framework/cache
RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views

RUN chown -R www-data:www-data bootstrap/cache 
RUN chown -R www-data:www-data storage 

VOLUME /var/www