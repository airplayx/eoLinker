FROM php:7.4-fpm

WORKDIR /var/www/html

COPY . .

RUN pecl install redis
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libhiredis-dev \
        wget \
        cron \
    && docker-php-ext-install pdo pdo_mysql zip pcntl fileinfo \
    && docker-php-ext-enable redis

EXPOSE 9000
CMD ["php-fpm"]