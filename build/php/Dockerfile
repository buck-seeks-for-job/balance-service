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

COPY build/php/git_ssh /usr/bin/git_ssh
ENV GIT_SSH /usr/bin/git_ssh

RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer global require hirak/prestissimo

COPY build/php/php.ini /usr/local/etc/php/php.ini
