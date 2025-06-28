# Multi-stage build for production optimization
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    mysql-client \
    redis \
    supervisor \
    nginx \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    sockets

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Development stage
FROM base AS development
RUN docker-php-ext-install xdebug
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini

# Production stage
FROM base AS production

# Configure PHP for production
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Create necessary directories and set permissions
RUN mkdir -p storage/logs storage/cache storage/sessions storage/uploads \
    && mkdir -p public/attachments \
    && chown -R www-data:www-data storage public/attachments \
    && chmod -R 775 storage public/attachments

# Copy supervisor configuration
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create health check script
RUN echo '<?php echo "OK"; ?>' > /var/www/html/health.php

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:9000/health.php || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
