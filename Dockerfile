FROM php:8.5-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite
RUN apt-get update && \
	apt-get install -y \
	libpng-dev \
	libwebp-dev \
	libjpeg62-turbo-dev \
	libpng-dev libxpm-dev \
	libfreetype6-dev
RUN docker-php-ext-configure gd \
    --with-webp \
    --with-jpeg \
    --with-xpm \
    --with-freetype
RUN docker-php-ext-install gd

# Apache prefork config
RUN a2enmod mpm_prefork
COPY Docker/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

COPY . /var/www/html/
