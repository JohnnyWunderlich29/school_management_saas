FROM php:8.2-fpm

# Instalar dependências do sistema e extensões PHP necessárias para Laravel e Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# CRIAR PASTAS E DAR PERMISSÕES (Isso resolve o erro do cache path)
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Instalar dependências sem rodar scripts automáticos que travam o build
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Comando para iniciar o servidor interno do PHP (ideal para testes rápidos)
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
