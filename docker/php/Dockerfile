FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
