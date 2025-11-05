FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip curl libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml ctype

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www


