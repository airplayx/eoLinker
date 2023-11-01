FROM php:7.1-apache

WORKDIR /var/www/html

COPY . .
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip pcntl fileinfo

RUN chmod -R 777 /var/www/html

EXPOSE 80