FROM php:8.4-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev curl \
    && docker-php-ext-install pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org | php -- --install-dir=/usr/local/bin --filename=composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

RUN useradd -m -s /bin/bash -u 1000 dev
RUN chown -R dev:dev /var/www/app

CMD ["php-fpm"]

