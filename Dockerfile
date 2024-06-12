FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo pdo_mysql \
    && a2enmod rewrite \
    && apt-get update \
    && apt-get install unzip
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer