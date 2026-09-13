# ---- Stage 1: build de assets con Node/Vite ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: imagen final de PHP + Apache ----
FROM php:8.4-apache

# Dependencias del sistema + extensiones de PHP que Laravel necesita
RUN apt-get update && apt-get install -y \
        git unzip libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath xml \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Traemos los assets ya compilados desde el stage de Node
COPY --from=assets /app/public/build /var/www/html/public/build

# Instala dependencias de PHP en el propio build
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --------------------------------------------------------------
# Apache debe servir desde /public (ahí vive index.php de Laravel),
# no desde la raíz del proyecto. Por defecto Apache sirve /var/www/html.
# --------------------------------------------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permite que el .htaccess de Laravel funcione (rutas amigables, sin index.php en la URL)
RUN printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Permisos correctos para que Laravel pueda escribir logs, cache y sesiones
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false

# Script que ajusta el puerto dinámico de Render y arranca Apache
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]