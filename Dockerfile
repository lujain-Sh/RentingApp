FROM php:8.2-cli

# System deps
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    nodejs npm \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# PHP deps
RUN composer install --no-dev --optimize-autoloader

# FRONTEND ASSETS (this is what you were missing)
RUN npm install
RUN npm run build

EXPOSE 10000

CMD php artisan config:clear \
 && php artisan cache:clear \
 && php artisan migrate --force \
 && php artisan serve --host=0.0.0.0 --port=10000
