#!/usr/bin/env bash
# ======================================================================
# Track IT · Exportación de la base de entrega (se ejecuta DENTRO de un
# contenedor postgres:17; lo lanzan exportar.ps1 / exportar.sh).
#
# 1. Levanta un Postgres temporal dentro del contenedor.
# 2. Copia ahí el esquema public de Supabase (solo lectura en Supabase).
# 3. Aplica limpieza.sql en la copia.
# 4. Genera salida/track-it-entrega.sql.
# ======================================================================
set -euo pipefail

export PGDATA=/tmp/pgdata
mkdir -p "$PGDATA"
chown postgres "$PGDATA"
su postgres -c "initdb -D $PGDATA -A trust -U postgres >/dev/null"
su postgres -c "pg_ctl -D $PGDATA -o \"-c listen_addresses=''\" -w start >/dev/null"

LOCAL=(psql -X -q -v ON_ERROR_STOP=1 -U postgres -h /var/run/postgresql)

"${LOCAL[@]}" -d postgres -c "CREATE DATABASE entrega"
"${LOCAL[@]}" -d entrega -c 'CREATE SCHEMA IF NOT EXISTS extensions; CREATE EXTENSION IF NOT EXISTS "uuid-ossp" SCHEMA extensions; CREATE EXTENSION IF NOT EXISTS pgcrypto SCHEMA extensions;'

echo "1/3  Copiando la base de Supabase (no se modifica)…"
ORIGEN=(-h "$ORIGEN_HOST" -p "${ORIGEN_PORT:-5432}" -U "$ORIGEN_USER" -d "${ORIGEN_DB:-postgres}")
if [ -n "${ORIGEN_URL:-}" ]; then ORIGEN=(-d "$ORIGEN_URL"); fi

PGPASSWORD="${ORIGEN_PASSWORD:-}" PGSSLMODE="${PGSSLMODE:-require}" \
  pg_dump "${ORIGEN[@]}" --schema=public --no-owner --no-privileges --no-comments \
          --no-publications --no-subscriptions \
  | grep -v '^CREATE SCHEMA public;' \
  | "${LOCAL[@]}" -d entrega >/dev/null

echo "2/3  Quitando usuarios, inventario, movimientos y bitácora de la copia…"
sed 's/\r$//' /trabajo/limpieza.sql \
  | "${LOCAL[@]}" -d entrega -v admin_password="$ADMIN_PASSWORD" -P pager=off -f -

echo "3/3  Generando el script…"
SALIDA=/salida/track-it-entrega.sql
{
  echo "-- ===================================================================="
  echo "-- Track IT · Base de entrega ($(date '+%d/%m/%Y %H:%M'))"
  echo "-- Incluye: catálogos, tipos de equipo y formularios, marcas, modelos y"
  echo "-- organización (sedes, hoteles, departamentos, ubicaciones)."
  echo "-- Sin usuarios, inventario, movimientos, mantenimientos, bajas,"
  echo "-- bitácora ni notificaciones."
  echo "--"
  echo "-- Acceso inicial: usuario «admin» con la contraseña temporal que se"
  echo "-- indicó al exportar. Al entrar pide cambiarla y activar la"
  echo "-- verificación en dos pasos."
  echo "--"
  echo "-- Restaurar en una base vacía:  psql \"<cadena de conexión>\" -f track-it-entrega.sql"
  echo "-- ===================================================================="
  echo
  echo 'CREATE SCHEMA IF NOT EXISTS extensions;'
  echo 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp" SCHEMA extensions;'
  echo 'CREATE EXTENSION IF NOT EXISTS pgcrypto SCHEMA extensions;'
  echo
  pg_dump -U postgres -h /var/run/postgresql -d entrega --schema=public --no-owner --no-privileges --no-comments \
    | grep -v '^CREATE SCHEMA public;'
} > "$SALIDA"

echo "Listo: database/entrega/salida/track-it-entrega.sql ($(du -h "$SALIDA" | cut -f1))"
