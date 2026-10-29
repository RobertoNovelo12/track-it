<?php

use App\Models\Hotel;
use App\Services\SystemCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

/*
|--------------------------------------------------------------------------
| Administración › Ubicaciones
|--------------------------------------------------------------------------
|
| Lugares concretos de cada hotel donde hay equipos (Cocina caliente,
| Cuarto de comunicaciones, Kids Club…). Cada una tiene:
| - hotel (o ninguno = disponible en cualquier hotel),
| - tipo de ubicación (lista editable: tipos_ubicacion),
| - departamentos que trabajan ahí (departamento_ubicacion; ninguno =
|   todos los departamentos de su hotel). Es lo que filtra la lista de
|   ubicaciones en Asignar / Reasignar (UbicacionOrganizacionalService).
|
| Agregar / editar una ubicación y los tipos de ubicación tienen su
| propia página (⚡ubicacion-editor, ⚡tipos-ubicacion).
|
*/

new class extends Component
{
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;
    use WithPagination;

    public string $search = '';
    public string $estado = 'todos';
    public string $hotelId = '';
    public string $tipoId = '';

    public int $perPage = 10;
    // Por defecto lo más reciente primero (último registro arriba).
    public string $sortField = 'recientes';
    public string $sortDirection = 'desc';

    private const ORDENABLES = [
        'recientes' => 'ub.id_ubicacion',
        'nombre' => 'ub.nombre',
        'tipo' => 'tu.nombre',
        'hotel' => 'u.nombre',
        'activo' => 'ub.activo',
    ];

    public function mount(): void
    {
        // Solo Admin modifica la organización (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');
    }

    public function placeholder(array $params = [])
    {
        return view('livewire.placeholders.administracion-seccion', [
            'columnas' => [
                ['label' => 'Ubicación', 'width' => '24%'],
                ['label' => 'Tipo', 'width' => '15%'],
                ['label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'], 'width' => '18%'],
                ['label' => 'Departamentos', 'width' => '28%'],
                ['label' => 'Estado', 'width' => '12%', 'type' => 'status'],
            ],
            'anchoMinimo' => '900px',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros y orden
    |--------------------------------------------------------------------------
    */

    public function updated(string $propiedad): void
    {
        if ($propiedad === 'perPage' && ! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        if (in_array($propiedad, ['search', 'estado', 'hotelId', 'tipoId', 'perPage', 'sortDirection'], true)) {
            $this->resetPage(pageName: 'ubicacionesPage');
        }
    }

    public function sortBy(string $campo): void
    {
        if (! array_key_exists($campo, self::ORDENABLES)) {
            return;
        }

        if ($this->sortField === $campo) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $campo;
            $this->sortDirection = 'asc';
        }

        $this->resetPage(pageName: 'ubicacionesPage');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = 'todos';
        $this->hotelId = '';
        $this->tipoId = '';

        $this->resetPage(pageName: 'ubicacionesPage');
    }

    /** ¿Ya se corrió la migración de tipos de ubicación? */
    public function listo(): bool
    {
        return \App\Support\Esquema::tieneTabla('tipos_ubicacion')
            && \App\Support\Esquema::tieneColumna('ubicaciones', 'id_tipo_ubicacion')
            && \App\Support\Esquema::tieneTabla('departamento_ubicacion');
    }

    /*
    |--------------------------------------------------------------------------
    | Datos
    |--------------------------------------------------------------------------
    */

    public function ubicaciones()
    {
        $search = trim($this->search);

        $pagina = DB::table('ubicaciones as ub')
            ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'ub.id_unidad')
            ->leftJoin('tipos_ubicacion as tu', 'tu.id_tipo_ubicacion', '=', 'ub.id_tipo_ubicacion')
            ->when($search !== '', fn ($query) => $query->where(function ($filtro) use ($search) {
                $filtro->where('ub.nombre', 'ilike', "%{$search}%")
                    ->orWhere('ub.descripcion', 'ilike', "%{$search}%");
            }))
            ->when(in_array($this->estado, ['activos', 'inactivos'], true), fn ($query) => $query->where('ub.activo', $this->estado === 'activos'))
            ->when($this->hotelId === 'general', fn ($query) => $query->whereNull('ub.id_unidad'))
            ->when(ctype_digit($this->hotelId), fn ($query) => $query->where('ub.id_unidad', (int) $this->hotelId))
            ->when($this->tipoId === 'sin', fn ($query) => $query->whereNull('ub.id_tipo_ubicacion'))
            ->when(ctype_digit($this->tipoId), fn ($query) => $query->where('ub.id_tipo_ubicacion', (int) $this->tipoId))
            ->orderByRaw(
                (self::ORDENABLES[$this->sortField] ?? 'ub.nombre')
                .' '.($this->sortDirection === 'desc' ? 'desc' : 'asc')
                .' NULLS LAST'
            )
            ->orderBy('ub.id_ubicacion')
            ->select([
                'ub.id_ubicacion',
                'ub.nombre',
                'ub.descripcion',
                'ub.activo',
                'ub.id_unidad',
                'ub.id_tipo_ubicacion',
                'u.nombre as hotel',
                'tu.nombre as tipo',
            ])
            ->paginate(perPage: $this->perPage, pageName: 'ubicacionesPage');

        // Departamentos de las ubicaciones de esta página (una sola consulta).
        $departamentos = DB::table('departamento_ubicacion as du')
            ->join('departamentos as d', 'd.id_departamento', '=', 'du.id_departamento')
            ->whereIn('du.id_ubicacion', $pagina->getCollection()->pluck('id_ubicacion')->all())
            ->orderBy('d.nombre')
            ->get(['du.id_ubicacion', 'd.id_departamento', 'd.nombre'])
            ->groupBy('id_ubicacion');

        $pagina->getCollection()->transform(function ($ubicacion) use ($departamentos) {
            $propios = $departamentos->get($ubicacion->id_ubicacion, collect());

            $ubicacion->departamentos = $propios->pluck('nombre')->all();
            $ubicacion->departamento_ids = $propios->pluck('id_departamento')->map(fn ($id) => (string) $id)->all();

            return $ubicacion;
        });

        return $pagina;
    }

    public function metricas(): array
    {
        $fila = DB::table('ubicaciones')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE activo) AS activas')
            ->selectRaw('COUNT(*) FILTER (WHERE id_tipo_ubicacion IS NULL) AS sin_tipo')
            ->first();

        return [
            'total' => (int) ($fila->total ?? 0),
            'activas' => (int) ($fila->activas ?? 0),
            'sin_tipo' => (int) ($fila->sin_tipo ?? 0),
        ];
    }

    public function hoteles()
    {
        return Hotel::query()->orderBy('nombre')->get(['id_unidad', 'nombre', 'activo']);
    }

    public function tipos(): array
    {
        return DB::table('tipos_ubicacion')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id_tipo_ubicacion', 'nombre', 'activo'])
            ->map(fn ($tipo) => [
                'id' => (int) $tipo->id_tipo_ubicacion,
                'nombre' => (string) $tipo->nombre,
                'activo' => (bool) $tipo->activo,
            ])
            ->all();
    }

    public function alternarUbicacion(int $id): void
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        DB::table('ubicaciones')->where('id_ubicacion', $id)->update(['activo' => DB::raw('NOT activo')]);

        $this->refrescarCaches();
    }

    private function refrescarCaches(): void
    {
        $cache = app(SystemCacheService::class);
        $cache->refreshModule('catalogos');
        $cache->refreshModule('asignaciones');
    }
};
?>

<div class="min-w-0 space-y-5">
    @if (! $this->listo())
        <div class="rounded-xl border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
            Falta ejecutar <code class="font-mono">php artisan migrate</code> para administrar ubicaciones y sus tipos.
        </div>
    @else
        @php
            $ubicaciones = $this->ubicaciones();
            $hoteles = $this->hoteles();
            $tipos = $this->tipos();
            $metricas = $this->metricas();
            $etiquetasPlantilla = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
            $unidadMinuscula = mb_strtolower($etiquetasPlantilla['unidad_singular']);

            $opcionesHotel = $hoteles->map(fn ($h) => [
                'value' => (string) $h->id_unidad,
                'label' => $h->nombre.($h->activo ? '' : ' (inactivo)'),
            ])->values()->all();

        @endphp

        <div class="space-y-5">
            {{-- Buscador, filtros y acciones (el título está en Administración) --}}
            <x-admin.filter-bar>
                <x-admin.search
                    id="ubicaciones-search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Buscar ubicación..."
                    label="Buscar ubicación"
                />

                <x-admin.select
                    model="hotelId"
                    :placeholder="$etiquetasPlantilla['unidad_singular']"
                    :clear-label="$etiquetasPlantilla['todas_unidades']"
                    :options="array_merge([['value' => 'general', 'label' => 'Cualquier '.$unidadMinuscula]], $opcionesHotel)"
                />

                <x-admin.select
                    model="tipoId"
                    placeholder="Tipo"
                    clear-label="Todos los tipos"
                    :options="array_merge(
                        collect($tipos)->map(fn ($t) => ['value' => (string) $t['id'], 'label' => $t['nombre'].($t['activo'] ? '' : ' (inactivo)')])->all(),
                        [['value' => 'sin', 'label' => 'Sin tipo']]
                    )"
                />

                <x-admin.select
                    model="estado"
                    placeholder="Estado"
                    :options="[
                        ['value' => 'todos', 'label' => 'Todos los estados'],
                        ['value' => 'activos', 'label' => 'Activas'],
                        ['value' => 'inactivos', 'label' => 'Inactivas'],
                    ]"
                />

                <x-slot:actions>
                    <a
                        href="{{ route('catalogos.tipos-ubicacion.index') }}"
                        wire:navigate.hover
                        class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2.5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                    >
                        Tipos de ubicación
                    </a>

                    <x-admin.action-button :href="route('catalogos.ubicaciones.create')">
                        Añadir ubicación
                    </x-admin.action-button>
                </x-slot:actions>

                <x-slot:footer>
                    @if ($search !== '' || $estado !== 'todos' || $hotelId !== '' || $tipoId !== '')
                        <button type="button" wire:click="limpiarFiltros" class="text-xs font-medium text-[var(--theme-primary)] hover:underline">
                            Limpiar filtros
                        </button>
                    @endif
                </x-slot:footer>
            </x-admin.filter-bar>

            <x-admin.metrics :items="[
                ['label' => 'Total de ubicaciones', 'value' => $metricas['total']],
                ['label' => 'Ubicaciones activas', 'value' => $metricas['activas']],
                ['label' => 'Sin tipo asignado', 'value' => $metricas['sin_tipo']],
            ]" />

            <x-data-table
                :rows="$ubicaciones"
                row-key="id_ubicacion"
                key-prefix="ubicacion"
                bulk-trash="ubicacion"

                :sort-field="$sortField"
                :sort-direction="$sortDirection"
                sort-action="sortBy"

                per-page-model="perPage"
                sort-model="sortDirection"
                refresh-action="$refresh"

                page-name="ubicacionesPage"
                result-singular="ubicación encontrada"
                result-plural="ubicaciones encontradas"
                empty-message="No se encontraron ubicaciones. Prueba con otro nombre o cambia los filtros."
                min-width="900px"

                :columns="[
                    ['key' => 'nombre', 'label' => 'Ubicación', 'width' => '24%', 'sort' => 'nombre', 'strong' => true],
                    ['key' => 'tipo', 'label' => 'Tipo', 'width' => '15%', 'sort' => 'tipo', 'default' => 'Sin tipo'],
                    ['key' => 'hotel', 'label' => $etiquetasPlantilla['unidad_singular'], 'width' => '18%', 'sort' => 'hotel', 'default' => 'Cualquiera'],
                    [
                        'key' => 'departamentos',
                        'label' => 'Departamentos',
                        'width' => '28%',
                        'type' => 'list',
                        'value' => fn ($u) => $u->departamentos,
                        'default' => 'Todos los del '.$unidadMinuscula,
                    ],
                    [
                        'key' => 'activo',
                        'label' => 'Estado',
                        'width' => '12%',
                        'sort' => 'activo',
                        'type' => 'status',
                        'value' => fn ($u) => $u->activo ? 'Activo' : 'Inactivo',
                    ],
                ]"

                :actions="fn ($u) => [
                    'edit' => route('catalogos.ubicaciones.edit', ['ubicacion' => $u->id_ubicacion]),
                    'menu' => array_values(array_filter([
                        ['label' => 'Editar', 'href' => route('catalogos.ubicaciones.edit', ['ubicacion' => $u->id_ubicacion])],
                        [
                            'label' => $u->activo ? 'Desactivar' : 'Activar',
                            'click' => 'alternarUbicacion(' . (int) $u->id_ubicacion . ')',
                            'danger' => (bool) $u->activo,
                        ],
                        \App\Support\PapeleraEntidades::accion('ubicacion', $u->id_ubicacion),
                    ])),
                ]"
            />
        </div>
    @endif
</div>
