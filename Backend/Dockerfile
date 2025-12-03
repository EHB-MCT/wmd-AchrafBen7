FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    zip \
    unzip \
    libpq \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev


RUN docker-php-ext-install pdo pdo_pgsql intl zip


COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html


COPY . .

RUN composer install --no-interaction --prefer-dist

RUN if [ -f ".env" ]; then php artisan key:generate; fi

CMD ["php-fpm"]

EXPOSE 9000
