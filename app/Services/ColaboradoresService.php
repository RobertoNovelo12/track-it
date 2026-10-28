<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Colaboradores de las asignaciones
|--------------------------------------------------------------------------
|
| Cada asignación puede apuntar a una cuenta (asignaciones.id_usuario).
| Si al asignar se escribe un nombre que no tiene cuenta, la asignación
| se guarda igual (id_usuario = NULL) y el colaborador aparece en
| Seguridad y roles › «Colaboradores sin cuenta», donde un administrador
| lo vincula con un usuario existente o le crea una cuenta.
|
| En los formularios, el selector (x-searchable-select con allow-custom)
| entrega el id del usuario elegido o «texto:Nombre escrito».
|
*/

class ColaboradoresService
{
    public const PREFIJO_TEXTO = 'texto:';

    private const CACHE_OPCIONES = 'trackit.colaboradores.opciones.v1';

    /*
    |--------------------------------------------------------------------------
    | Opciones del selector
    |--------------------------------------------------------------------------
    */

    public function opcionesUsuarios(): array
    {
        return Cache::remember(
            self::CACHE_OPCIONES,
            now()->addMinutes(10),
            fn (): array => DB::table('usuarios')
                ->select([
                    'id_usuario',
                    'nombres',
                    'apellido_paterno',
                    'apellido_materno',
                    'numero_colaborador',
                    'username',
                    'correo',
                ])
                ->orderBy('nombres')
                ->orderBy('apellido_paterno')
                ->get()
                ->map(function ($usuario): array {
                    $nombre = $this->nombreCompleto($usuario);
                    $numero = trim((string) ($usuario->numero_colaborador ?? ''));

                    return [
                        'value' => (string) $usuario->id_usuario,
                        'label' => $numero !== '' ? $nombre . ' · ' . $numero : $nombre,
                        'nombre' => $nombre,
                        'numero' => $numero,
                        'search' => implode(' ', array_filter([
                            $numero,
                            $usuario->username,
                            $usuario->correo,
                        ])),
                    ];
                })
                ->all()
        );
    }

    /*
     * ¿Ya se corrió la migración de asignaciones.id_usuario? Solo se
     * guarda el «sí» (para siempre); mientras falte se vuelve a revisar.
     */
    public function disponible(): bool
    {
        if (Cache::get('trackit.asignaciones.id_usuario.v1') === true) {
            return true;
        }

        $existe = \App\Support\Esquema::tieneColumna('asignaciones', 'id_usuario');

        if ($existe) {
            Cache::forever('trackit.asignaciones.id_usuario.v1', true);
        }

        return $existe;
    }

    /*
     * Columnas a guardar en una asignación (sin id_usuario si la
     * migración aún no se ha corrido).
     */
    public function columnasAsignacion(array $resuelto): array
    {
        $columnas = [
            'nombre_colaborador' => $resuelto['nombre'],
            'numero_colaborador' => $resuelto['numero'],
        ];

        if ($this->disponible()) {
            $columnas['id_usuario'] = $resuelto['id_usuario'];
        }

        return $columnas;
    }

    public function olvidarOpciones(): void
    {
        Cache::forget(self::CACHE_OPCIONES);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver lo capturado en un formulario
    |--------------------------------------------------------------------------
    |
    | $seleccion: id de usuario («15») o «texto:Nombre escrito».
    | $nombreRespaldo: nombre escrito en formularios antiguos (sin selector).
    |
    | Devuelve ['id_usuario' => ?int, 'nombre' => string, 'numero' => ?string,
    |           'sin_cuenta' => bool]
    |
    */

    public function resolver(?string $seleccion, ?string $nombreRespaldo = null, ?string $numeroEscrito = null): array
    {
        $seleccion = trim((string) $seleccion);
        $numeroEscrito = $this->textoONulo($numeroEscrito);

        if ($seleccion !== '' && ctype_digit($seleccion)) {
            $usuario = DB::table('usuarios')
                ->where('id_usuario', (int) $seleccion)
                ->first(['id_usuario', 'nombres', 'apellido_paterno', 'apellido_materno', 'numero_colaborador']);

            if ($usuario) {
                return [
                    'id_usuario' => (int) $usuario->id_usuario,
                    'nombre' => $this->nombreCompleto($usuario),
                    'numero' => $this->textoONulo($usuario->numero_colaborador) ?? $numeroEscrito,
                    'sin_cuenta' => false,
                ];
            }
        }

        $nombre = str_starts_with($seleccion, self::PREFIJO_TEXTO)
            ? substr($seleccion, strlen(self::PREFIJO_TEXTO))
            : (string) $nombreRespaldo;

        return [
            'id_usuario' => null,
            'nombre' => $this->normalizarEspacios($nombre),
            'numero' => $numeroEscrito,
            'sin_cuenta' => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Colaboradores sin cuenta (Seguridad y roles)
    |--------------------------------------------------------------------------
    |
    | Asignaciones vigentes (sin fecha_fin) sin cuenta, agrupadas por el
    | nombre escrito (sin distinguir mayúsculas ni espacios extra).
    |
    */

    public function pendientes(): array
    {
        if (! $this->disponible()) {
            return [];
        }

        $filas = DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'a.id_unidad')
            ->leftJoin('departamentos as d', 'd.id_departamento', '=', 'a.id_departamento')
            ->whereNull('a.id_usuario')
            ->whereNull('a.fecha_fin')
            ->whereRaw("TRIM(COALESCE(a.nombre_colaborador, '')) <> ''")
            ->orderBy('a.nombre_colaborador')
            ->orderBy('e.codigo_inventario')
            ->get([
                'a.id_asignacion',
                'a.nombre_colaborador',
                'a.numero_colaborador',
                'a.fecha_asignacion',
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'u.nombre as unidad',
                'd.nombre as departamento',
            ]);

        return $filas
            ->groupBy(fn ($fila): string => $this->clave((string) $fila->nombre_colaborador))
            ->map(function ($grupo, string $clave): array {
                $primera = $grupo->first();

                return [
                    'clave' => $clave,
                    'nombre' => $this->normalizarEspacios((string) $primera->nombre_colaborador),
                    'numero' => $grupo->pluck('numero_colaborador')->filter()->first(),
                    'equipos' => $grupo->map(fn ($fila): array => [
                        'id_equipo' => (int) $fila->id_equipo,
                        'codigo' => (string) $fila->codigo_inventario,
                        'nombre' => (string) $fila->nombre_equipo,
                        'ubicacion' => implode(' / ', array_filter([$fila->unidad, $fila->departamento])),
                        'fecha' => $fila->fecha_asignacion,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function totalPendientes(): int
    {
        return count($this->pendientes());
    }

    /*
    |--------------------------------------------------------------------------
    | Vincular con una cuenta
    |--------------------------------------------------------------------------
    |
    | Todas las asignaciones sin cuenta con ese nombre (vigentes y del
    | historial) pasan a la cuenta y toman su nombre y número.
    | Devuelve cuántas asignaciones se actualizaron.
    |
    */

    public function vincular(string $clave, int $idUsuario): int
    {
        $usuario = DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->first(['id_usuario', 'nombres', 'apellido_paterno', 'apellido_materno', 'numero_colaborador']);

        if (! $usuario || trim($clave) === '') {
            return 0;
        }

        $cambios = [
            'id_usuario' => (int) $usuario->id_usuario,
            'nombre_colaborador' => $this->nombreCompleto($usuario),
        ];

        $numero = $this->textoONulo($usuario->numero_colaborador);

        if ($numero !== null) {
            $cambios['numero_colaborador'] = $numero;
        }

        $actualizadas = DB::table('asignaciones')
            ->whereNull('id_usuario')
            ->whereRaw("LOWER(REGEXP_REPLACE(TRIM(nombre_colaborador), '\\s+', ' ', 'g')) = ?", [$clave])
            ->update($cambios);

        // Nombres visibles en Asignaciones, Equipos y la Vista general.
        $cache = app(SystemCacheService::class);
        $cache->refreshModule('asignaciones');
        $cache->refreshModule('equipos');
        $cache->refreshModule('dashboard');

        return $actualizadas;
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    /*
     * Misma normalización que la consulta de vincular():
     * minúsculas y espacios simples.
     */
    public function clave(string $nombre): string
    {
        return mb_strtolower($this->normalizarEspacios($nombre));
    }

    public function nombreCompleto(object $usuario): string
    {
        return $this->normalizarEspacios(implode(' ', array_filter([
            $usuario->nombres ?? null,
            $usuario->apellido_paterno ?? null,
            $usuario->apellido_materno ?? null,
        ])));
    }

    private function normalizarEspacios(string $texto): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $texto));
    }

    private function textoONulo(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
