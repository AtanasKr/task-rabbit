# Simple PHP CLI image (enough for artisan serve)
FROM php:8.3-cli-alpine

# Install system deps
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    bash \
    icu-dev \
    mysql-client

# PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
