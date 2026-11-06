#!/usr/bin/env bash
# Track IT · Exportar la base de entrega (Linux / macOS). Requiere Docker.
# Uso: bash database/entrega/exportar.sh ["Contraseña temporal del admin"]
set -euo pipefail
AQUI="$(cd "$(dirname "$0")" && pwd)"
RAIZ="$(cd "$AQUI/../.." && pwd)"
leer() { grep -E "^\s*$1\s*=" "$RAIZ/.env" | tail -1 | cut -d= -f2- | sed -E "s/^\s*['\"]?//; s/['\"]?\s*$//"; }
mkdir -p "$AQUI/salida"
CONTRASENA="${1:-TrackIT-Entrega2026!}"
docker run --rm \
  -e ORIGEN_URL="$(leer DB_URL || true)" -e ORIGEN_HOST="$(leer DB_HOST || true)" -e ORIGEN_PORT="$(leer DB_PORT || true)" \
  -e ORIGEN_DB="$(leer DB_DATABASE || true)" -e ORIGEN_USER="$(leer DB_USERNAME || true)" -e ORIGEN_PASSWORD="$(leer DB_PASSWORD || true)" \
  -e ADMIN_PASSWORD="$CONTRASENA" \
  -v "$AQUI:/trabajo:ro" -v "$AQUI/salida:/salida" \
  postgres:17 bash -c "sed 's/\r$//' /trabajo/contenedor.sh | bash"
echo "Usuario inicial: admin   Contraseña temporal: $CONTRASENA"
