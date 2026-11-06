<#
  Track IT · Exportar la base de entrega (Windows, PowerShell)

  Requisitos: Docker Desktop abierto. Lee la conexión de Supabase del .env
  del proyecto (DB_URL o DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD).
  Supabase NO se modifica: todo se limpia en una copia temporal.

  Uso (desde la raíz del proyecto):
    powershell -ExecutionPolicy Bypass -File database\entrega\exportar.ps1
    powershell -ExecutionPolicy Bypass -File database\entrega\exportar.ps1 -Contrasena "OtraClave#2026"

  Resultado: database\entrega\salida\track-it-entrega.sql
#>
param(
    [string]$Contrasena = 'TrackIT-Entrega2026!'
)

$ErrorActionPreference = 'Stop'
$aqui = $PSScriptRoot
$raiz = (Resolve-Path (Join-Path $aqui '..\..')).Path
$archivoEnv = Join-Path $raiz '.env'

if (-not (Test-Path $archivoEnv)) { throw "No encontré $archivoEnv" }

$cfg = @{}
foreach ($linea in Get-Content $archivoEnv) {
    if ($linea -match '^\s*(DB_[A-Z_]+)\s*=\s*(.*)$') {
        $cfg[$Matches[1]] = $Matches[2].Trim().Trim('"').Trim("'")
    }
}

if (-not $cfg['DB_URL'] -and -not $cfg['DB_HOST']) { throw 'El .env no tiene DB_URL ni DB_HOST.' }

docker info *> $null
if ($LASTEXITCODE -ne 0) { throw 'Docker no está disponible. Abre Docker Desktop y vuelve a intentarlo.' }

$salida = Join-Path $aqui 'salida'
New-Item -ItemType Directory -Force -Path $salida | Out-Null

docker run --rm `
    -e ORIGEN_URL="$($cfg['DB_URL'])" `
    -e ORIGEN_HOST="$($cfg['DB_HOST'])" `
    -e ORIGEN_PORT="$($cfg['DB_PORT'])" `
    -e ORIGEN_DB="$($cfg['DB_DATABASE'])" `
    -e ORIGEN_USER="$($cfg['DB_USERNAME'])" `
    -e ORIGEN_PASSWORD="$($cfg['DB_PASSWORD'])" `
    -e ADMIN_PASSWORD="$Contrasena" `
    -v "${aqui}:/trabajo:ro" `
    -v "${salida}:/salida" `
    postgres:17 bash -c "sed 's/\r$//' /trabajo/contenedor.sh | bash"

if ($LASTEXITCODE -ne 0) { throw 'La exportación falló; revisa el mensaje de arriba. Supabase no se modificó.' }

Write-Host ''
Write-Host "Usuario inicial: admin   Contraseña temporal: $Contrasena" -ForegroundColor Green
