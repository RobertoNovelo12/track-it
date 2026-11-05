<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class SystemCacheService
{
    /*
     * Pantallas que se recalculan solas tras un cambio: módulo invalidado
     * => componentes Livewire con un método precalentar(). Movimientos usa
     * también la versión de Equipos en sus claves.
     */
    private const PRECALENTAR = [
        'dashboard' => ['dashboard-overview'],
        'asignaciones' => ['asignaciones-index'],
        'equipos' => ['asignaciones-index'],
    ];

    // Componentes por recalcular en esta petición (una vez cada uno).
    private static array $porPrecalentar = [];

    /*
     * Tras invalidar un módulo, sus pantallas se recalculan al terminar la
     * petición (después de enviar la respuesta): la siguiente visita ya las
     * encuentra listas y nadie espera las consultas.
     */
    private function precalentar(string $module): void
    {
        if (app()->runningInConsole() || ! isset(self::PRECALENTAR[$module])) {
            return;
        }

        $primero = self::$porPrecalentar === [];

        foreach (self::PRECALENTAR[$module] as $componente) {
            self::$porPrecalentar[$componente] = true;
        }

        if (! $primero) {
            return;
        }

        app()->terminating(function (): void {
            $componentes = array_keys(self::$porPrecalentar);
            self::$porPrecalentar = [];

            @set_time_limit(90);

            foreach ($componentes as $nombre) {
                try {
                    $componente = \Livewire\Livewire::new($nombre);

                    if (method_exists($componente, 'precalentar')) {
                        $componente->precalentar();
                    }
                } catch (\Throwable $error) {
                    report($error);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    private const GLOBAL_VERSION_KEY =
        'trackit.cache.global.version';

    private const MODULE_VERSION_PREFIX =
        'trackit.cache.module.';

    private const DEFAULT_TTL_HOURS =
        12;

    /*
    |--------------------------------------------------------------------------
    | VERSIÓN GLOBAL
    |--------------------------------------------------------------------------
    */

    public function globalVersion(): int
    {
        return (int) Cache::rememberForever(
            self::GLOBAL_VERSION_KEY,
            fn (): int => 1
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VERSIÓN DE MÓDULO
    |--------------------------------------------------------------------------
    */

    public function moduleVersion(
        string $module
    ): int {
        return (int) Cache::rememberForever(
            $this->moduleVersionKey($module),
            fn (): int => 1
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDAR TODO EL SISTEMA
    |--------------------------------------------------------------------------
    */

    public function refreshAll(): int
    {
        $version = $this->globalVersion() + 1;

        Cache::forever(
            self::GLOBAL_VERSION_KEY,
            $version
        );

        return $version;
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDAR UN MÓDULO
    |--------------------------------------------------------------------------
    */

    public function refreshModule(
        string $module
    ): int {
        $version = $this->moduleVersion($module) + 1;

        Cache::forever(
            $this->moduleVersionKey($module),
            $version
        );

        $this->precalentar($module);

        return $version;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR CLAVE
    |--------------------------------------------------------------------------
    */

    public function key(
        string $module,
        string $resource,
        array $state = []
    ): string {
        $normalizedModule = $this->normalize($module);
        $normalizedResource = $this->normalize($resource);

        $stateHash = hash(
            'sha256',
            serialize($state)
        );

        return implode('.', [
            'trackit',
            'v' . $this->globalVersion(),
            $normalizedModule,
            'v' . $this->moduleVersion($normalizedModule),
            $normalizedResource,
            $stateHash,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RECORDAR DATOS
    |--------------------------------------------------------------------------
    */

    public function remember(
        string $module,
        string $resource,
        array $state,
        Closure $callback,
        ?int $hours = null
    ): mixed {
        $ttl = now()->addHours(
            $hours ?? self::DEFAULT_TTL_HOURS
        );

        return Cache::remember(
            $this->key(
                $module,
                $resource,
                $state
            ),
            $ttl,
            $callback
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RECORDAR DATOS SIN ESTADO
    |--------------------------------------------------------------------------
    */

    public function rememberSimple(
        string $module,
        string $resource,
        Closure $callback,
        ?int $hours = null
    ): mixed {
        return $this->remember(
            $module,
            $resource,
            [],
            $callback,
            $hours
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLAVE DE VERSIÓN DEL MÓDULO
    |--------------------------------------------------------------------------
    */

    private function moduleVersionKey(
        string $module
    ): string {
        return self::MODULE_VERSION_PREFIX
            . $this->normalize($module)
            . '.version';
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR
    |--------------------------------------------------------------------------
    */

    private function normalize(
        string $value
    ): string {
        $value = strtolower(trim($value));

        $value = preg_replace(
            '/[^a-z0-9._-]+/',
            '-',
            $value
        );

        return trim(
            (string) $value,
            '-'
        );
    }
}
