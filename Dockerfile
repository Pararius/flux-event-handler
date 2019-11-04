FROM php:7.3-cli-alpine AS build

WORKDIR /app

ADD composer.json /app
ADD composer.lock /app

COPY --from=composer:1.9 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

FROM php:7.3-cli-alpine

WORKDIR /app

ENV DEBUG 0

COPY server.php /app
COPY src /app/src
COPY --from=build /app/vendor /app/vendor

EXPOSE 80

CMD ["php", "/app/server.php"]
