#!/bin/sh
set -e

# Render inyecta el puerto real en la variable $PORT (normalmente no es 80).
# Apache por defecto escucha en 80, así que lo ajustamos al arrancar.
PORT="${PORT:-10000}"

sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Cachea config/rutas/vistas para arranque más rápido en cada request.
# Si tu .env aún no está 100% listo en el contenedor, puedes comentar
# estas 3 líneas temporalmente.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground