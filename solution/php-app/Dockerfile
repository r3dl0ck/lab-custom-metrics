FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-progress

FROM php:8.3-apache
# rozszerzenie phpredis - klient Redisa (storage dla metryk)
RUN pecl install redis >/dev/null \
 && docker-php-ext-enable redis \
 && a2enmod rewrite
COPY apache.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY composer.json ./
COPY public ./public
COPY src ./src
