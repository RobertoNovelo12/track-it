# ---- Stage 1: build de assets con Node/Vite ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: imagen final de PHP ----
FROM php:8.4-cli

# Dependencias del sistema + extensiones de PHP que Laravel necesita
RUN apt-get update && apt-get install -y \
        git unzip libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath xml \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Traemos los assets ya compilados desde el stage de Node
COPY --from=assets /app/public/build /var/www/html/public/build

# Instala dependencias de PHP en el propio build
RUN composer install --no-dev --optimize-autoloader --no-interaction

ENV APP_ENV=production
ENV APP_DEBUG=false

# Render inyecta la variable PORT; el servidor debe escuchar ahí.
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]