FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY apache.conf /etc/apache2/sites-available/000-default.conf
