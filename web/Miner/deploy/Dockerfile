FROM php:5.6-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY src/ /var/www/html/
COPY flag.txt /flag.txt
RUN chmod 444 /flag.txt
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
