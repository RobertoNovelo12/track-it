<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Comprobaciones de esquema en caché
|--------------------------------------------------------------------------
|
| En Postgres (Supabase) cada Schema::hasTable / hasColumn es una consulta
| al catálogo que tarda entre 0.5 y 2 s con la base remota. Varias por
| petición hacían que exportar unos pocos equipos tardara 20–30 s.
|
| El resultado se guarda en caché: «sí existe» por 30 días, «no existe»
| por 5 minutos (para que una migración nueva se note pronto). Al correr
| php artisan migrate se olvida todo (AppServiceProvider).
|
*/

class Esquema
{
    private static array $memoria = [];

    private static ?int $version = null;

    public static function tieneTabla(string $tabla): bool
    {
        return self::recordar('t:'.$tabla, fn (): bool => Schema::hasTable($tabla));
    }

    public static function tieneColumna(string $tabla, string $columna): bool
    {
        return self::recordar('c:'.$tabla.'.'.$columna, fn (): bool => Schema::hasColumn($tabla, $columna));
    }

    public static function olvidar(): void
    {
        self::$memoria = [];
        self::$version = time();

        Cache::forever('esquema:version', self::$version);
    }

    private static function recordar(string $clave, callable $consultar): bool
    {
        if (array_key_exists($clave, self::$memoria)) {
            return self::$memoria[$clave];
        }

        self::$version ??= (int) Cache::get('esquema:version', 1);
        $claveCache = 'esquema:'.self::$version.':'.$clave;

        $valor = Cache::get($claveCache);

        if ($valor === null) {
            $valor = (bool) $consultar();
            Cache::put($claveCache, $valor, $valor ? now()->addDays(30) : now()->addMinutes(5));
        }

        return self::$memoria[$clave] = (bool) $valor;
    }
}
