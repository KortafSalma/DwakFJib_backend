FROM php:8.3-fpm-alpine AS builder

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    mysql-dev \
    oniguruma-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    && apk del --no-cache \
    libpng-dev \
    libxml2-dev \
    mysql-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative

RUN php artisan package:discover --ansi

RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/logs \
    bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    mysql-client \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

COPY --from=builder /var/www/html .

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --retries=3 --start-period=40s \
  CMD wget -qO- http://localhost:8000/api/medications || exit 1

CMD ["sh", "-c", "php artisan migrate --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=8000"]
