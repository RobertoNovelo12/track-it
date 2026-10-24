<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Programas (software) de los equipos
|--------------------------------------------------------------------------
|
| Catálogo en Administración › Programas. Un tipo de equipo con
| «Incluye programas» muestra la sección al dar de alta / editar sus
| equipos, con sus programas predeterminados ya marcados.
|
*/
class ProgramasService
{
    public const PLATAFORMAS = ['Windows', 'macOS', 'Linux', 'Android', 'iOS', 'Web', 'Multiplataforma'];

    public const CATEGORIAS = [
        'Ofimática',
        'Seguridad',
        'Comunicación',
        'Navegador',
        'Diseño',
        'Desarrollo',
        'Utilerías',
        'Sistema',
        'Administrativo',
        'Otra',
    ];

    public const LICENCIAS = [
        'gratuita' => 'Gratuita',
        'pago' => 'De pago',
        'suscripcion' => 'Suscripción',
    ];

    /** ¿Ya se corrió la migración? (solo se guarda el «sí»). */
    public function disponible(): bool
    {
        if (Cache::get('trackit.programas.v1') === true) {
            return true;
        }

        $existe = Schema::hasTable('programas')
            && Schema::hasTable('equipo_programa')
            && Schema::hasTable('tipo_equipo_programa')
            && Schema::hasColumn('tipos_equipo', 'incluye_programas');

        if ($existe) {
            Cache::forever('trackit.programas.v1', true);
        }

        return $existe;
    }

    /** Refresca las cachés después de un cambio en el catálogo o en un tipo. */
    public function refrescar(): void
    {
        app(SystemCacheService::class)->refreshModule('programas');
        app(SystemCacheService::class)->refreshModule('catalogos');
    }

    /**
     * Programas activos para elegir: [{value, label, detalle, categoria}].
     * $incluirIds: también estos aunque estén inactivos (ya instalados).
     */
    public function opciones(array $incluirIds = []): array
    {
        if (! $this->disponible()) {
            return [];
        }

        $activos = app(SystemCacheService::class)->rememberSimple(
            'programas',
            'opciones-activas',
            fn (): array => DB::table('programas')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->map(fn ($programa) => $this->aOpcion($programa))
                ->all(),
            12
        );

        $faltantes = array_diff(
            array_map('intval', $incluirIds),
            array_map(fn ($opcion) => (int) $opcion['value'], $activos)
        );

        if ($faltantes === []) {
            return $activos;
        }

        $extra = DB::table('programas')
            ->whereIn('id_programa', $faltantes)
            ->get()
            ->map(fn ($programa) => $this->aOpcion($programa))
            ->all();

        return collect([...$activos, ...$extra])->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    private function aOpcion(object $programa): array
    {
        return [
            'value' => (string) $programa->id_programa,
            'label' => (string) $programa->nombre,
            'detalle' => trim(implode(' · ', array_filter([
                $programa->empresa,
                $programa->version ? 'v'.ltrim((string) $programa->version, 'vV') : null,
                $programa->plataforma,
            ]))),
            'categoria' => (string) ($programa->categoria ?? ''),
            'activo' => (bool) $programa->activo,
        ];
    }

    /**
     * Por tipo de equipo: id => ['incluye' => bool, 'predeterminados' => [ids]].
     */
    public function porTipo(): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return app(SystemCacheService::class)->rememberSimple(
            'programas',
            'por-tipo',
            function (): array {
                $predeterminados = DB::table('tipo_equipo_programa as tp')
                    ->join('programas as p', 'p.id_programa', '=', 'tp.id_programa')
                    ->where('p.activo', true)
                    ->get(['tp.id_tipo_equipo', 'tp.id_programa'])
                    ->groupBy('id_tipo_equipo');

                return DB::table('tipos_equipo')
                    ->get(['id_tipo_equipo', 'incluye_programas'])
                    ->mapWithKeys(fn ($tipo) => [
                        (string) $tipo->id_tipo_equipo => [
                            'incluye' => (bool) $tipo->incluye_programas,
                            'predeterminados' => $predeterminados->get($tipo->id_tipo_equipo, collect())
                                ->pluck('id_programa')
                                ->map(fn ($id) => (string) $id)
                                ->values()
                                ->all(),
                        ],
                    ])
                    ->all();
            },
            12
        );
    }

    /**
     * Editor del tipo de equipo: casilla, predeterminados y programas para elegir.
     */
    public function configuracionTipo(int $idTipo): array
    {
        if (! $this->disponible()) {
            return ['disponible' => false, 'incluye' => false, 'seleccion' => [], 'opciones' => []];
        }

        $seleccion = DB::table('tipo_equipo_programa')
            ->where('id_tipo_equipo', $idTipo)
            ->pluck('id_programa')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        return [
            'disponible' => true,
            'incluye' => (bool) DB::table('tipos_equipo')->where('id_tipo_equipo', $idTipo)->value('incluye_programas'),
            'seleccion' => $seleccion,
            // Activos + los ya elegidos aunque se hayan desactivado.
            'opciones' => $this->opciones($seleccion),
            'url_catalogo' => route('catalogos.index', ['section' => 'programas']),
            'url_nuevo' => route('catalogos.programas.create'),
        ];
    }

    /**
     * Guarda «Incluye programas» y los programas predeterminados del tipo.
     * Desactivar la casilla conserva los predeterminados (por si se vuelve
     * a activar) y no toca los programas ya registrados en los equipos.
     */
    public function guardarConfiguracionTipo(int $idTipo, bool $incluye, array $seleccion): void
    {
        $ids = collect($seleccion)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $validos = $ids === []
            ? []
            : DB::table('programas')->whereIn('id_programa', $ids)->pluck('id_programa')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($idTipo, $incluye, $validos): void {
            DB::table('tipos_equipo')
                ->where('id_tipo_equipo', $idTipo)
                ->update(['incluye_programas' => $incluye]);

            DB::table('tipo_equipo_programa')
                ->where('id_tipo_equipo', $idTipo)
                ->whereNotIn('id_programa', $validos ?: [0])
                ->delete();

            $existentes = DB::table('tipo_equipo_programa')
                ->where('id_tipo_equipo', $idTipo)
                ->pluck('id_programa')
                ->map(fn ($id) => (int) $id)
                ->all();

            $nuevos = array_values(array_diff($validos, $existentes));

            if ($nuevos !== []) {
                DB::table('tipo_equipo_programa')->insert(array_map(
                    fn (int $id) => ['id_tipo_equipo' => $idTipo, 'id_programa' => $id],
                    $nuevos
                ));
            }
        });

        $this->refrescar();
    }

    public function tipoIncluye(int $idTipo): bool
    {
        return (bool) ($this->porTipo()[(string) $idTipo]['incluye'] ?? false);
    }

    /** Ids de los programas del equipo. */
    public function idsDelEquipo(int $idEquipo): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return DB::table('equipo_programa')
            ->where('id_equipo', $idEquipo)
            ->pluck('id_programa')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /** Programas del equipo con sus datos (detalle del equipo). */
    public function delEquipo(int $idEquipo): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return DB::table('equipo_programa as ep')
            ->join('programas as p', 'p.id_programa', '=', 'ep.id_programa')
            ->where('ep.id_equipo', $idEquipo)
            ->orderBy('p.nombre')
            ->get(['p.*'])
            ->map(fn ($programa) => [
                'id' => (int) $programa->id_programa,
                'nombre' => (string) $programa->nombre,
                'empresa' => (string) ($programa->empresa ?? ''),
                'version' => (string) ($programa->version ?? ''),
                'plataforma' => (string) ($programa->plataforma ?? ''),
                'categoria' => (string) ($programa->categoria ?? ''),
                'licencia' => self::LICENCIAS[$programa->licencia] ?? 'Gratuita',
                'requiere_clave' => (bool) $programa->requiere_clave,
                'enlace' => (string) ($programa->enlace_descarga ?? ''),
                'peso' => $this->pesoLegible($programa->peso_mb),
                'activo' => (bool) $programa->activo,
            ])
            ->all();
    }

    /**
     * Valida la selección enviada desde el formulario.
     * Devuelve los ids (enteros) o null si no hay que tocar los programas
     * (tipo sin programas, o el formulario no los envió).
     * Acepta programas inactivos solo si el equipo ya los tenía.
     */
    public function validarSeleccion(mixed $seleccion, int $idTipo, ?int $idEquipo = null): ?array
    {
        if (! $this->disponible() || ! is_array($seleccion) || ! $this->tipoIncluye($idTipo)) {
            return null;
        }

        $ids = collect($seleccion)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        $actuales = $idEquipo ? array_map('intval', $this->idsDelEquipo($idEquipo)) : [];

        $validos = DB::table('programas')
            ->whereIn('id_programa', $ids)
            ->where(fn ($query) => $query->where('activo', true)->orWhereIn('id_programa', $actuales ?: [0]))
            ->pluck('id_programa')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validos) !== count($ids)) {
            throw ValidationException::withMessages([
                'programas' => 'Uno de los programas elegidos ya no está disponible. Recarga la página.',
            ]);
        }

        return $ids;
    }

    /** Reemplaza los programas del equipo. */
    public function sincronizarEquipo(int $idEquipo, array $ids): void
    {
        DB::table('equipo_programa')->where('id_equipo', $idEquipo)->whereNotIn('id_programa', $ids ?: [0])->delete();

        $existentes = array_map('intval', $this->idsDelEquipo($idEquipo));

        $nuevos = array_values(array_diff($ids, $existentes));

        if ($nuevos !== []) {
            DB::table('equipo_programa')->insert(array_map(
                fn (int $id) => ['id_equipo' => $idEquipo, 'id_programa' => $id, 'fecha_registro' => now()],
                $nuevos
            ));
        }
    }

    public function pesoLegible(mixed $pesoMb): string
    {
        if ($pesoMb === null || $pesoMb === '') {
            return '';
        }

        $mb = (float) $pesoMb;

        return $mb >= 1024
            ? rtrim(rtrim(number_format($mb / 1024, 2, '.', ','), '0'), '.').' GB'
            : rtrim(rtrim(number_format($mb, 2, '.', ','), '0'), '.').' MB';
    }
}
