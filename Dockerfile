FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y \
    nginx \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

COPY nginx.conf /etc/nginx/sites-available/default

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable

EXPOSE 80

CMD service nginx start && php-fpm
