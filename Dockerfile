FROM php:8.3-cli

# Dependencias del sistema + extensiones de PHP que Laravel necesita
# (pdo_pgsql es la que importa para conectar a Supabase).
RUN apt-get update && apt-get install -y \
        git unzip libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath xml \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Instala dependencias de PHP en el propio build (no en el arranque del contenedor,
# así vemos cualquier error de Composer directo en el log de build de Render).
RUN composer install --no-dev --optimize-autoloader --no-interaction

ENV APP_ENV=production
ENV APP_DEBUG=false

# Render inyecta la variable PORT; el servidor debe escuchar ahí.
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]