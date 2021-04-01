FROM php:8.0-cli-alpine AS build

WORKDIR /app

ADD composer.json /app
ADD composer.lock /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

FROM php:8.0-cli-alpine

WORKDIR /app

ENV DEBUG 0

COPY server.php /app
COPY src /app/src
COPY --from=build /app/vendor /app/vendor

EXPOSE 80

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

CMD ["php", "/app/server.php"]
