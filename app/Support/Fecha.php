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

    /*
     * Solo el día (d/m/Y) en la zona horaria del usuario. Para fechas con
     * hora guardadas en UTC: de noche en México ya serían «mañana».
     */
    public static function dia(mixed $valor, ?string $vacio = null): ?string
    {
        $completo = self::conHora($valor, '');

        return $completo === '' ? $vacio : substr($completo, 0, 10);
    }

    public static function hora(mixed $valor, string $vacio = '—'): string
    {
        return app(PreferenciasUsuarioService::class)->hora($valor, $vacio);
    }
}
