# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --no-progress --prefer-dist

COPY . .
RUN composer dump-autoload --no-dev --optimize


FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev \
    && docker-php-ext-install pdo_mysql intl opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=prod \
    APP_DEBUG=0

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/staffwise-entrypoint
RUN chmod +x /usr/local/bin/staffwise-entrypoint \
    && mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/www/html/var/share \
    && chown -R www-data:www-data /var/www/html/var

WORKDIR /var/www/html

ENTRYPOINT ["staffwise-entrypoint"]
CMD ["apache2-foreground"]
