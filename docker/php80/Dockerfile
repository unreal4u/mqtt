FROM php:8.0-rc-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update
RUN apt-get install -y \
        libzip-dev \
        zip
RUN docker-php-ext-install zip