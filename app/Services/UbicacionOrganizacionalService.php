<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Hotel → Departamento → Ubicación
|--------------------------------------------------------------------------
|
| Fuente única para los formularios de asignación y reasignación.
|
| Reglas:
| - Hotel: unidades_organizativas activas.
| - Departamento: principales y activos que atienden todas las unidades
|   o están vinculados al hotel (departamento_unidad).
| - Ubicación: activa, del hotel elegido (o sin hotel = cualquiera) y,
|   si tiene departamentos configurados (departamento_ubicacion), sólo
|   para esos departamentos. Sin departamentos = todos los del hotel.
|
| Las listas se envían completas al navegador para que el filtrado en
| cascada no haga peticiones a Livewire.
|
*/

class UbicacionOrganizacionalService
{
    private ?bool $estructuraNueva = null;

    private static ?bool $equiposConUbicacion = null;

    /*
     * ¿Ya existe equipos.id_ubicacion? (migración 2026_10_24_000400).
     * Mientras no, los equipos se guardan sin ubicación en vez de fallar.
     */
    public static function equiposTienenUbicacion(): bool
    {
        return self::$equiposConUbicacion ??= \App\Support\Esquema::tieneColumna('equipos', 'id_ubicacion');
    }

    /*
    |--------------------------------------------------------------------------
    | Datos para el navegador
    |--------------------------------------------------------------------------
    */

    public function datosFormulario(): array
    {
        return [
            'unidades' => $this->unidades(),
            'departamentos' => $this->departamentos(),
            'ubicaciones' => $this->ubicaciones(),
        ];
    }

    public function unidades(): array
    {
        return DB::table('unidades_organizativas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_unidad', 'nombre'])
            ->map(fn ($unidad): array => [
                'value' => (string) $unidad->id_unidad,
                'label' => (string) $unidad->nombre,
            ])
            ->values()
            ->all();
    }

    /*
     * [{ value, label, todas: bool, unidades: ['1','3'] }]
     */
    public function departamentos(): array
    {
        $departamentos = DB::table('departamentos')
            ->whereNull('id_departamento_principal')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_departamento', 'nombre', 'atiende_todas_unidades']);

        $vinculos = DB::table('departamento_unidad')
            ->whereIn('id_departamento', $departamentos->pluck('id_departamento'))
            ->get(['id_departamento', 'id_unidad'])
            ->groupBy('id_departamento');

        return $departamentos
            ->map(fn ($departamento): array => [
                'value' => (string) $departamento->id_departamento,
                'label' => (string) $departamento->nombre,
                'todas' => (bool) $departamento->atiende_todas_unidades,
                'unidades' => $vinculos
                    ->get($departamento->id_departamento, collect())
                    ->map(fn ($vinculo): string => (string) $vinculo->id_unidad)
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /*
     * [{ value, label, unidad: '' | '3', departamentos: ['5','8'] }]
     */
    public function ubicaciones(): array
    {
        if (! $this->tieneEstructuraNueva()) {
            return DB::table('ubicaciones')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id_ubicacion', 'nombre'])
                ->map(fn ($ubicacion): array => [
                    'value' => (string) $ubicacion->id_ubicacion,
                    'label' => (string) $ubicacion->nombre,
                    'unidad' => '',
                    'departamentos' => [],
                ])
                ->values()
                ->all();
        }

        $ubicaciones = DB::table('ubicaciones')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_ubicacion', 'id_unidad', 'nombre']);

        $vinculos = DB::table('departamento_ubicacion')
            ->get(['id_departamento', 'id_ubicacion'])
            ->groupBy('id_ubicacion');

        return $ubicaciones
            ->map(fn ($ubicacion): array => [
                'value' => (string) $ubicacion->id_ubicacion,
                'label' => (string) $ubicacion->nombre,
                'unidad' => $ubicacion->id_unidad !== null
                    ? (string) $ubicacion->id_unidad
                    : '',
                'departamentos' => $vinculos
                    ->get($ubicacion->id_ubicacion, collect())
                    ->map(fn ($vinculo): string => (string) $vinculo->id_departamento)
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Validación al guardar
    |--------------------------------------------------------------------------
    |
    | $campos: nombre del campo del formulario para cada nivel, así los
    | mensajes aparecen debajo del select correcto.
    |
    | Devuelve las columnas listas para asignaciones.
    |
    */

    public function validar(
        int|string|null $idUnidad,
        int|string|null $idDepartamento,
        int|string|null $idUbicacion,
        array $campos
    ): array {
        $unidadId = (int) $idUnidad;
        $departamentoId = (int) $idDepartamento;
        $ubicacionId = filled($idUbicacion) ? (int) $idUbicacion : null;

        $unidad = $unidadId > 0
            ? DB::table('unidades_organizativas')
                ->where('id_unidad', $unidadId)
                ->where('activo', true)
                ->first(['id_unidad'])
            : null;

        if (! $unidad) {
            throw ValidationException::withMessages([
                $campos['unidad'] => 'Selecciona un hotel válido.',
            ]);
        }

        $departamentoValido = $departamentoId > 0
            && DB::table('departamentos as d')
                ->where('d.id_departamento', $departamentoId)
                ->whereNull('d.id_departamento_principal')
                ->where('d.activo', true)
                ->where(function ($query) use ($unidadId) {
                    $query
                        ->where('d.atiende_todas_unidades', true)
                        ->orWhereExists(function ($sub) use ($unidadId) {
                            $sub->selectRaw('1')
                                ->from('departamento_unidad as du')
                                ->whereColumn('du.id_departamento', 'd.id_departamento')
                                ->where('du.id_unidad', $unidadId);
                        });
                })
                ->exists();

        if (! $departamentoValido) {
            throw ValidationException::withMessages([
                $campos['departamento'] => 'El departamento no está disponible en el hotel seleccionado.',
            ]);
        }

        if ($ubicacionId !== null && ! $this->ubicacionPermitida($ubicacionId, $unidadId, $departamentoId)) {
            throw ValidationException::withMessages([
                $campos['ubicacion'] => 'La ubicación no corresponde al hotel o al departamento seleccionado.',
            ]);
        }

        return [
            'id_unidad' => $unidadId,
            'id_departamento' => $departamentoId,
            'id_ubicacion' => $ubicacionId,
        ];
    }

    /*
     * Ubicación del equipo (campo «Ubicación» de Campos disponibles).
     * Es opcional y el departamento también: sin departamento sólo se
     * revisa que la ubicación sea del hotel. Devuelve el id o null.
     */
    public function validarUbicacionEquipo(
        int|string|null $idUbicacion,
        int $unidadId,
        ?int $departamentoId,
        string $campo
    ): ?int {
        if (! filled($idUbicacion)) {
            return null;
        }

        $ubicacionId = (int) $idUbicacion;

        if ($ubicacionId < 1 || ! $this->ubicacionPermitida($ubicacionId, $unidadId, $departamentoId)) {
            throw ValidationException::withMessages([
                $campo => 'La ubicación no corresponde a la unidad o al departamento seleccionado.',
            ]);
        }

        return $ubicacionId;
    }

    private function ubicacionPermitida(int $ubicacionId, int $unidadId, ?int $departamentoId): bool
    {
        if (! $this->tieneEstructuraNueva()) {
            return DB::table('ubicaciones')
                ->where('id_ubicacion', $ubicacionId)
                ->where('activo', true)
                ->exists();
        }

        return DB::table('ubicaciones as ub')
            ->where('ub.id_ubicacion', $ubicacionId)
            ->where('ub.activo', true)
            ->where(function ($query) use ($unidadId) {
                $query->whereNull('ub.id_unidad')
                    ->orWhere('ub.id_unidad', $unidadId);
            })
            ->when($departamentoId !== null, fn ($consulta) => $consulta->where(function ($query) use ($departamentoId) {
                $query
                    ->whereNotExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('departamento_ubicacion as dub')
                            ->whereColumn('dub.id_ubicacion', 'ub.id_ubicacion');
                    })
                    ->orWhereExists(function ($sub) use ($departamentoId) {
                        $sub->selectRaw('1')
                            ->from('departamento_ubicacion as dub')
                            ->whereColumn('dub.id_ubicacion', 'ub.id_ubicacion')
                            ->where('dub.id_departamento', $departamentoId);
                    });
            }))
            ->exists();
    }

    /*
     * Mientras no se ejecute la migración, los formularios siguen
     * funcionando (sin ubicaciones filtradas) en lugar de fallar.
     */
    private function tieneEstructuraNueva(): bool
    {
        return $this->estructuraNueva ??= \App\Support\Esquema::tieneColumna('ubicaciones', 'id_unidad')
            && \App\Support\Esquema::tieneTabla('departamento_ubicacion');
    }
}
