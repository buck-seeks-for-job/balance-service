FROM php:7.2-cli-alpine

WORKDIR /var/balance-service

RUN apk upgrade --update && apk add icu-dev bash git openssh-client postgresql-dev pcre-dev
RUN docker-php-ext-install opcache intl bcmath pgsql pdo pdo_pgsql

RUN apk add --no-cache --virtual .build-deps g++ autoconf make \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug --ini-name=xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

# I know that copy-paste is not a good idea and the better way is to extract the base Dockerfile but I'm short on time