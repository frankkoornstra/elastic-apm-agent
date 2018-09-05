FROM php:7.2.2-cli

RUN pecl install xdebug-2.6.0 \
    && docker-php-ext-enable xdebug

WORKDIR /project
