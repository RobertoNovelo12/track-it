<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Throwable;

/*
|--------------------------------------------------------------------------
| Preferencias del usuario (Ajustes › Preferencias)
|--------------------------------------------------------------------------
|
| Tabla preferencias_usuario. Un usuario sin fila (o antes de ejecutar la
| migración) usa los valores predeterminados.
|
| Se leen una vez por petición y se guardan 10 minutos en caché.
|
*/

class PreferenciasUsuarioService
{
    public const TEMAS = [
        'system' => 'Según el sistema',
        'light' => 'Claro',
        'dark' => 'Oscuro',
    ];

    public const DENSIDADES = [
        'comoda' => 'Cómoda',
        'compacta' => 'Compacta',
    ];

    /*
     * Pantalla al iniciar sesión: ruta y permiso necesario.
     */
    public const PANTALLAS_INICIO = [
        'dashboard' => ['label' => 'Vista general', 'route' => 'dashboard', 'permiso' => null],
        'equipos' => ['label' => 'Inventario', 'route' => 'equipos.index', 'permiso' => null],
        'asignaciones' => ['label' => 'Asignaciones y movimientos', 'route' => 'asignaciones.index', 'permiso' => 'gestionar'],
    ];

    public const REGISTROS_POR_PAGINA = [10, 25, 50, 100];

    public const ZONAS_HORARIAS = [
        'America/Mexico_City' => 'Centro de México (Ciudad de México)',
        'America/Cancun' => 'Sureste (Cancún, Quintana Roo)',
        'America/Monterrey' => 'Noreste (Monterrey)',
        'America/Chihuahua' => 'Chihuahua',
        'America/Mazatlan' => 'Pacífico (Mazatlán, Los Cabos)',
        'America/Tijuana' => 'Noroeste (Tijuana)',
        'America/Bogota' => 'Colombia (Bogotá)',
        'America/Santo_Domingo' => 'República Dominicana',
        'America/Jamaica' => 'Jamaica',
        'America/Sao_Paulo' => 'Brasil (São Paulo)',
        'America/New_York' => 'EE. UU. Este (Nueva York)',
        'America/Los_Angeles' => 'EE. UU. Pacífico (Los Ángeles)',
        'Europe/Madrid' => 'España (Madrid)',
        'UTC' => 'UTC',
    ];

    public const FORMATOS_HORA = [
        '24' => '24 horas (14:30)',
        '12' => '12 horas (2:30 p. m.)',
    ];

    public const PREDETERMINADAS = [
        'tema' => 'system',
        'densidad' => 'comoda',
        'pantalla_inicio' => 'dashboard',
        'registros_por_pagina' => 10,
        'id_unidad_predeterminada' => null,
        'zona_horaria' => 'America/Mexico_City',
        'formato_hora' => '24',
    ];

    private array $memoria = [];

    private ?bool $tablaDisponible = null;

    /*
    |--------------------------------------------------------------------------
    | Lectura
    |--------------------------------------------------------------------------
    */

    public function obtener(?int $idUsuario = null): array
    {
        $idUsuario ??= auth()->id();

        if (! $idUsuario) {
            return self::PREDETERMINADAS;
        }

        if (isset($this->memoria[$idUsuario])) {
            return $this->memoria[$idUsuario];
        }

        if (! $this->tablaDisponible()) {
            return $this->memoria[$idUsuario] = self::PREDETERMINADAS;
        }

        try {
            $fila = Cache::remember(
                $this->claveCache($idUsuario),
                now()->addMinutes(10),
                fn () => (array) (DB::table('preferencias_usuario')
                    ->where('id_usuario', $idUsuario)
                    ->first() ?? [])
            );
        } catch (Throwable $exception) {
            report($exception);
            $fila = [];
        }

        return $this->memoria[$idUsuario] = $this->normalizar($fila);
    }

    public function valor(string $clave, ?int $idUsuario = null): mixed
    {
        return $this->obtener($idUsuario)[$clave] ?? self::PREDETERMINADAS[$clave] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Guardado
    |--------------------------------------------------------------------------
    */

    public function guardar(int $idUsuario, array $cambios): array
    {
        $actuales = $this->obtener($idUsuario);

        $datos = $this->normalizar(array_merge($actuales, $cambios));

        DB::table('preferencias_usuario')->updateOrInsert(
            ['id_usuario' => $idUsuario],
            $datos + ['fecha_actualizacion' => now()]
        );

        Cache::forget($this->claveCache($idUsuario));

        unset($this->memoria[$idUsuario]);

        return $datos;
    }

    /*
    |--------------------------------------------------------------------------
    | Apoyo
    |--------------------------------------------------------------------------
    */

    /*
     * URL de la pantalla de inicio permitida para el rol del usuario.
     */
    public function urlInicio(): string
    {
        $pantalla = self::PANTALLAS_INICIO[$this->valor('pantalla_inicio')]
            ?? self::PANTALLAS_INICIO['dashboard'];

        if ($pantalla['permiso'] && Gate::denies($pantalla['permiso'])) {
            $pantalla = self::PANTALLAS_INICIO['dashboard'];
        }

        return route($pantalla['route']);
    }

    public function registrosPorPagina(): int
    {
        return (int) $this->valor('registros_por_pagina');
    }

    /*
     * Fecha y hora de la base de datos (guardada en la zona de la
     * aplicación) en la zona horaria y formato del usuario.
     */
    public function fechaHora(mixed $valor, string $vacio = '—'): string
    {
        if (blank($valor)) {
            return $vacio;
        }

        try {
            $fecha = $valor instanceof \DateTimeInterface
                ? Carbon::instance($valor)
                : Carbon::parse($valor, config('app.timezone'));
        } catch (Throwable) {
            return (string) $valor;
        }

        $formato = $this->valor('formato_hora') === '12'
            ? 'd/m/Y h:i a'
            : 'd/m/Y H:i';

        return $fecha
            ->setTimezone($this->valor('zona_horaria'))
            ->format($formato);
    }

    public function hora(mixed $valor, string $vacio = '—'): string
    {
        $completo = $this->fechaHora($valor, $vacio);

        return $completo === $vacio ? $vacio : trim(substr($completo, 11));
    }

    private function normalizar(array $fila): array
    {
        $datos = array_merge(self::PREDETERMINADAS, array_intersect_key($fila, self::PREDETERMINADAS));

        if (! array_key_exists($datos['tema'], self::TEMAS)) {
            $datos['tema'] = self::PREDETERMINADAS['tema'];
        }

        if (! array_key_exists($datos['densidad'], self::DENSIDADES)) {
            $datos['densidad'] = self::PREDETERMINADAS['densidad'];
        }

        if (! array_key_exists($datos['pantalla_inicio'], self::PANTALLAS_INICIO)) {
            $datos['pantalla_inicio'] = self::PREDETERMINADAS['pantalla_inicio'];
        }

        $datos['registros_por_pagina'] = (int) $datos['registros_por_pagina'];

        if (! in_array($datos['registros_por_pagina'], self::REGISTROS_POR_PAGINA, true)) {
            $datos['registros_por_pagina'] = self::PREDETERMINADAS['registros_por_pagina'];
        }

        $datos['id_unidad_predeterminada'] = filled($datos['id_unidad_predeterminada'])
            ? (int) $datos['id_unidad_predeterminada']
            : null;

        if (! in_array($datos['zona_horaria'], timezone_identifiers_list(), true)) {
            $datos['zona_horaria'] = self::PREDETERMINADAS['zona_horaria'];
        }

        $datos['formato_hora'] = (string) $datos['formato_hora'] === '12' ? '12' : '24';

        return $datos;
    }

    private function tablaDisponible(): bool
    {
        /*
         * La consulta al catálogo de la base es lenta: el resultado se
         * guarda en caché (10 min si existe, 1 min si aún no se migra).
         */
        if ($this->tablaDisponible !== null) {
            return $this->tablaDisponible;
        }

        $existe = Cache::get('preferencias_usuario.tabla');

        if ($existe === null) {
            $existe = Schema::hasTable('preferencias_usuario');

            Cache::put('preferencias_usuario.tabla', $existe, $existe ? now()->addMinutes(10) : now()->addMinute());
        }

        return $this->tablaDisponible = (bool) $existe;
    }

    private function claveCache(int $idUsuario): string
    {
        return 'preferencias_usuario.' . $idUsuario;
    }
}
