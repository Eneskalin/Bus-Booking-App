
FROM composer:2 AS composer_install

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction


FROM php:8.2-apache 


RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_sqlite

RUN a2enmod rewrite

COPY . /var/www/html/

COPY --from=composer_install /app/vendor /var/www/html/vendor



EXPOSE 80