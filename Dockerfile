FROM php:8.3-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    zip \
    oniguruma-dev \
    mysql-client

RUN docker-php-ext-install pdo_mysql mbstring bcmath gd zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy all application files
COPY . .

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions for storage & cache
RUN chmod -R 777 storage bootstrap/cache

# Fix script line endings & permissions for Linux
RUN dos2unix docker/start.sh 2>/dev/null || true && chmod +x docker/start.sh

ENV PORT=10000
EXPOSE 10000

CMD ["docker/start.sh"]
