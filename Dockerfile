FROM php:7.1-apache

ENV HOME /var/www/html
ENV TZ Asia/Shanghai
ENV APACHE_DOCUMENT_ROOT $HOME

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!/var/www/html/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && apt-get update && apt-get install -y \
              libfreetype6-dev \
              libjpeg62-turbo-dev \
              libpng-dev \
    && docker-php-ext-install pdo pdo_mysql zip pcntl fileinfo \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY . $HOME

RUN a2enmod rewrite \
    && chmod -R 0755 $HOME \
    && chown -R www-data:www-data $HOME

EXPOSE 80
