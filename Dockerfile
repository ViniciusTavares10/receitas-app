FROM php:8.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN COMPOSER_MEMORY_LIMIT=-1 COMPOSER_MAX_PARALLEL_HTTP=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY docker/entrypoint.sh /usr/local/bin/receitas-entrypoint

RUN chmod +x /usr/local/bin/receitas-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["receitas-entrypoint"]
CMD ["php-fpm"]