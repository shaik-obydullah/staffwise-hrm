#!/bin/sh
set -e

cd /var/www/html

mkdir -p var/cache var/log var/share
chown -R www-data:www-data var

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    echo "Clearing Symfony cache..."
    su -s /bin/sh www-data -c "php bin/console cache:clear --no-warmup"

    echo "Running database migrations..."
    su -s /bin/sh www-data -c "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration"

    echo "Seeding default UK tax rates (only when none exist)..."
    su -s /bin/sh www-data -c "php bin/console app:seed-rates"
fi

exec "$@"
