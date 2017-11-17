FROM php:7.0-apache
RUN a2enmod rewrite
COPY ping.html /var/www/html/ping.html
COPY . /var/www/html/
