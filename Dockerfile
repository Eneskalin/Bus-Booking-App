FROM composer:2 AS composer_install

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction


FROM php:8.2-apache 


RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    git \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite gd mbstring

RUN a2enmod rewrite

COPY . /var/www/html/

COPY --from=composer_install /app/vendor /var/www/html/vendor


RUN mkdir -p /var/www/html/storage/tickets && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80