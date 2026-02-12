# Multi-stage build for Laravel CTF Challenge
# Using PHP 8.0 for Laravel 8 + vulnerable facade/ignition compatibility
FROM php:8.0-cli-alpine AS composer

# Install composer 2.3.x (before audit feature was enforced)
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.3.10 --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy composer files first for better caching
COPY composer.json ./

# Install dependencies without scripts
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# Copy application code
COPY . .

# Generate autoloader and disable platform check
RUN composer dump-autoload --optimize --no-scripts && \
    echo "<?php" > vendor/composer/platform_check.php

# Production image
FROM php:8.0-fpm-alpine

# Install system dependencies
RUN apk add --no-cache nginx supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql opcache

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Configure PHP-FPM
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create nginx temp directories
RUN mkdir -p /tmp/nginx-client-body /tmp/nginx-proxy /tmp/nginx-fastcgi /tmp/nginx-uwsgi /tmp/nginx-scgi \
    && chmod 777 /tmp/nginx-*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --from=composer /app ./

# Create directories and set permissions
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/secret \
    && mkdir -p bootstrap/cache \
    && touch storage/logs/laravel.log \
    && chown -R nobody:nobody storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Create the flag file at root - readable and writable for CVE-2021-3129 exploit
RUN echo "BUETCTF{1gn1710n_51gn47ur3_ch41n_pwn3d_O_o}" > /flag.txt \
    && chown nobody:nobody /flag.txt \
    && chmod 666 /flag.txt

# Bake in the environment
COPY .env.docker .env

# Clear caches
RUN php artisan config:clear 2>/dev/null || true \
    && php artisan view:clear 2>/dev/null || true

# Remove sensitive files
RUN rm -rf .git .gitignore .env.example tests phpunit.xml \
    SOLUTION.md README-DEPLOY.md exploit scripts Makefile

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD wget -q --spider http://127.0.0.1/dashboard || exit 1

# Run supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
