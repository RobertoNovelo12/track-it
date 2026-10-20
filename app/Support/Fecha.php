<?php

namespace App\Support;

use App\Services\PreferenciasUsuarioService;

/*
|--------------------------------------------------------------------------
| Fechas en la zona horaria y formato del usuario
|--------------------------------------------------------------------------
|
| Las fechas se guardan en la zona de la aplicación (config app.timezone).
| Para mostrarlas:
|
|   {{ \App\Support\Fecha::conHora($registro->fecha_hora) }}
|       -> 19/10/2026 14:30   ó   19/10/2026 02:30 pm
|
|   {{ \App\Support\Fecha::hora($valor) }} -> 14:30
|
*/

class Fecha
{
    public static function conHora(mixed $valor, string $vacio = '—'): string
    {
        return app(PreferenciasUsuarioService::class)->fechaHora($valor, $vacio);
    }

    public static function hora(mixed $valor, string $vacio = '—'): string
    {
        return app(PreferenciasUsuarioService::class)->hora($valor, $vacio);
    }
}
