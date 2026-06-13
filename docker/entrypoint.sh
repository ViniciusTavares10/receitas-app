#!/usr/bin/env sh
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader ${COMPOSER_INSTALL_FLAGS:---no-dev}
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
