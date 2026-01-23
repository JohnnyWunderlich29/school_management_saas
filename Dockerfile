FROM php:8.2-fpm

# Instalar dependências do sistema e extensões PHP necessárias para Laravel e Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip intl

# Instalar Node.js (necessário para compilar assets do Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# CRIAR PASTAS E DAR PERMISSÕES
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Instalar dependências PHP
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Instalar dependências Node e compilar assets
RUN npm install && npm run build

# Comando para iniciar o servidor (Railway injeta a variável PORT automaticamente)
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
