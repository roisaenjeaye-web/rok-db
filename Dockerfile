FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    zip \
    oniguruma-dev \
    mysql-client \
    postgresql-dev \
    ca-certificates

RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring bcmath gd zip opcache

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

ENV PORT=7860
EXPOSE 7860

CMD ["docker/start.sh"]
