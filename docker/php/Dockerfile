ARG PHP_VERSION=8.3

# ─── Базовый образ ───────────────────────────────────────────────────────────
FROM php:${PHP_VERSION}-fpm-alpine AS base

RUN apk add --no-cache \
        autoconf \
        g++ \
        make \
        file \
        re2c \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        linux-headers \
        supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear \
    && apk del autoconf g++ make file re2c

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

# ─── Development ─────────────────────────────────────────────────────────────
FROM base AS dev

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/php.ini         /usr/local/etc/php/conf.d/app.ini
COPY docker/php/php-fpm.conf    /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm"]

# ─── Builder — устанавливаем зависимости ─────────────────────────────────────
FROM base AS builder

COPY backend/composer.json backend/composer.lock ./

# Устанавливаем без dev зависимостей для prod
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --optimize-autoloader

COPY backend/ .

RUN composer dump-autoload --optimize --no-dev \
    && php bin/console cache:warmup --env=prod

# ─── Production ──────────────────────────────────────────────────────────────
FROM base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY docker/php/php.ini              /usr/local/etc/php/conf.d/app.ini
COPY docker/php/php-fpm.conf         /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/supervisord.conf     /etc/supervisord.conf

# Копируем только артефакты из builder — не весь контекст
COPY --from=builder /var/www/app /var/www/app

RUN chown -R www-data:www-data /var/www/app/var

# supervisord запускает php-fpm + messenger:consume
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

EXPOSE 9000