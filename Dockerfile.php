FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libicu-dev \
    zip unzip git curl \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        opcache \
        intl \
    && rm -rf /var/lib/apt/lists/*

# Override www.conf: gunakan www-data bukan root
RUN { \
    echo '[www]'; \
    echo 'user = www-data'; \
    echo 'group = www-data'; \
    echo 'listen = 9000'; \
    echo 'listen.owner = www-data'; \
    echo 'listen.group = www-data'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 20'; \
    echo 'pm.start_servers = 4'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 10'; \
    echo 'pm.max_requests = 500'; \
    echo 'catch_workers_output = yes'; \
} > /usr/local/etc/php-fpm.d/www.conf

# Copy source ke image di /app-src (akan di-copy ke volume saat startup)
WORKDIR /app-src
COPY . .

RUN chown -R www-data:www-data /app-src \
    && find /app-src -type d -exec chmod 755 {} \; \
    && find /app-src -type f -exec chmod 644 {} \; \
    && chmod -R 775 /app-src/writable

# Entrypoint: populate /var/www/html dari /app-src jika belum ada
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

HEALTHCHECK --interval=10s --timeout=5s --retries=5 --start-period=30s \
    CMD php-fpm -t || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
