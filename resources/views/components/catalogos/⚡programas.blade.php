<?php

use App\Services\ProgramasService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/*
|--------------------------------------------------------------------------
| Administración › Programas
|--------------------------------------------------------------------------
|
| Catálogo de software que se instala en los equipos (Office, antivirus,
| navegadores…). Agregar / editar tiene su propia página
| (⚡programa-editor). Los tipos de equipo con «Incluye programas» los
| ofrecen al dar de alta y editar sus equipos.
|
*/

new class extends Component
{
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;
    use WithPagination;

    public string $search = '';
    public string $estado = 'todos';
    public string $categoria = '';
    public string $plataforma = '';

    public int $perPage = 10;
    // Por defecto lo más reciente primero (último registro arriba).
    public string $sortField = 'recientes';
    public string $sortDirection = 'desc';

    private const ORDENABLES = [
        'recientes' => 'p.id_programa',
        'nombre' => 'p.nombre',
        'empresa' => 'p.empresa',
        'plataforma' => 'p.plataforma',
        'categoria' => 'p.categoria',
        'peso' => 'p.peso_mb',
        'equipos' => 'equipos',
        'activo' => 'p.activo',
    ];

    public function mount(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');
    }

    public function placeholder(array $params = [])
    {
        return view('livewire.placeholders.administracion-seccion', [
            'columnas' => [
                ['label' => 'Programa', 'width' => '22%'],
                ['label' => 'Empresa', 'width' => '15%'],
                ['label' => 'Plataforma', 'width' => '12%'],
                ['label' => 'Categoría', 'width' => '13%'],
                ['label' => 'Licencia', 'width' => '11%'],
                ['label' => 'Peso', 'width' => '9%'],
                ['label' => 'Estado', 'width' => '10%', 'type' => 'status'],
            ],
            'anchoMinimo' => '1000px',
        ]);
    }

    public function updated(string $propiedad): void
    {
        if ($propiedad === 'perPage' && ! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        if (in_array($propiedad, ['search', 'estado', 'categoria', 'plataforma', 'perPage', 'sortDirection'], true)) {
            $this->resetPage(pageName: 'programasPage');
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

        $this->resetPage(pageName: 'programasPage');
    }

    public function limpiarFiltros(): void
    {
        $this->reset('search', 'estado', 'categoria', 'plataforma');
        $this->resetPage(pageName: 'programasPage');
    }

    public function listo(): bool
    {
        return app(ProgramasService::class)->disponible();
    }

    public function programas()
    {
        $equipos = DB::table('equipo_programa')
            ->selectRaw('id_programa, COUNT(*) AS total')
            ->groupBy('id_programa');

        $consulta = DB::table('programas as p')
            ->leftJoinSub($equipos, 'ep', 'ep.id_programa', '=', 'p.id_programa')
            ->select('p.*')
            ->selectRaw('COALESCE(ep.total, 0) AS equipos')
            ->when(in_array($this->estado, ['activos', 'inactivos'], true), fn ($query) => $query->where('p.activo', $this->estado === 'activos'))
            ->when($this->categoria !== '', fn ($query) => $query->where('p.categoria', $this->categoria))
            ->when($this->plataforma !== '', fn ($query) => $query->where('p.plataforma', $this->plataforma));

        \App\Support\BusquedaTolerante::aplicar($consulta, $this->search, ['p.nombre', 'p.empresa', 'p.version', 'p.categoria', 'p.descripcion']);

        return $consulta
            ->orderByRaw(
                (self::ORDENABLES[$this->sortField] ?? 'p.nombre')
                .' '.($this->sortDirection === 'desc' ? 'desc' : 'asc')
                .' NULLS LAST'
            )
            ->orderBy('p.id_programa')
            ->paginate(perPage: $this->perPage, pageName: 'programasPage');
    }

    public function metricas(): array
    {
        $fila = DB::table('programas')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE activo) AS activos')
            ->selectRaw("COUNT(*) FILTER (WHERE licencia <> 'gratuita') AS con_licencia")
            ->first();

        return [
            'total' => (int) ($fila->total ?? 0),
            'activos' => (int) ($fila->activos ?? 0),
            'con_licencia' => (int) ($fila->con_licencia ?? 0),
        ];
    }

    public function alternarPrograma(int $id): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        DB::table('programas')->where('id_programa', $id)->update([
            'activo' => DB::raw('NOT activo'),
            'fecha_actualizacion' => now(),
        ]);

        app(ProgramasService::class)->refrescar();
    }
};
?>

<div class="min-w-0 space-y-5">
    @if (! $this->listo())
        <div class="rounded-xl border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
            Falta ejecutar <code class="font-mono">php artisan migrate</code> para administrar los programas.
        </div>
    @else
        @php
            $programas = $this->programas();
            $metricas = $this->metricas();
            $servicio = app(\App\Services\ProgramasService::class);
        @endphp

        <div class="space-y-5">
            <x-admin.filter-bar>
                <x-admin.search
                    id="programas-search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Buscar programa o empresa..."
                    label="Buscar programa"
                />

                <x-admin.select
                    model="categoria"
                    placeholder="Categoría"
                    clear-label="Todas las categorías"
                    :options="collect(\App\Services\ProgramasService::CATEGORIAS)->map(fn ($c) => ['value' => $c, 'label' => $c])->all()"
                />

                <x-admin.select
                    model="plataforma"
                    placeholder="Plataforma"
                    clear-label="Todas las plataformas"
                    :options="collect(\App\Services\ProgramasService::PLATAFORMAS)->map(fn ($p) => ['value' => $p, 'label' => $p])->all()"
                />

                <x-admin.select
                    model="estado"
                    placeholder="Estado"
                    :options="[
                        ['value' => 'todos', 'label' => 'Todos los estados'],
                        ['value' => 'activos', 'label' => 'Activos'],
                        ['value' => 'inactivos', 'label' => 'Inactivos'],
                    ]"
                />

                <x-slot:actions>
                    <x-admin.action-button :href="route('catalogos.programas.create')">
                        Añadir programa
                    </x-admin.action-button>
                </x-slot:actions>

                <x-slot:footer>
                    @if ($search !== '' || $estado !== 'todos' || $categoria !== '' || $plataforma !== '')
                        <button type="button" wire:click="limpiarFiltros" class="text-xs font-medium text-[var(--theme-primary)] hover:underline">
                            Limpiar filtros
                        </button>
                    @endif
                </x-slot:footer>
            </x-admin.filter-bar>

            <x-admin.metrics :items="[
                ['label' => 'Total de programas', 'value' => $metricas['total']],
                ['label' => 'Programas activos', 'value' => $metricas['activos']],
                ['label' => 'De pago o suscripción', 'value' => $metricas['con_licencia']],
            ]" />

            <x-data-table
                :rows="$programas"
                row-key="id_programa"
                key-prefix="programa"
                bulk-trash="programa"

                :sort-field="$sortField"
                :sort-direction="$sortDirection"
                sort-action="sortBy"

                per-page-model="perPage"
                sort-model="sortDirection"
                refresh-action="$refresh"

                page-name="programasPage"
                result-singular="programa encontrado"
                result-plural="programas encontrados"
                empty-message="No hay programas que coincidan. Agrega el primero con «Añadir programa»."
                min-width="1100px"

                :mobile-subtitle="fn ($p) => trim(implode(' · ', array_filter([$p->empresa, $p->version ? 'v'.ltrim($p->version, 'vV') : null])))"

                :columns="[
                    ['key' => 'nombre', 'label' => 'Programa', 'width' => '20%', 'sort' => 'nombre', 'strong' => true],
                    ['key' => 'empresa', 'label' => 'Empresa', 'width' => '14%', 'sort' => 'empresa'],
                    ['key' => 'version', 'label' => 'Versión', 'width' => '9%', 'mobile' => 'filled'],
                    ['key' => 'plataforma', 'label' => 'Plataforma', 'width' => '11%', 'sort' => 'plataforma'],
                    ['key' => 'categoria', 'label' => 'Categoría', 'width' => '12%', 'sort' => 'categoria'],
                    [
                        'key' => 'licencia',
                        'label' => 'Licencia',
                        'width' => '11%',
                        'value' => fn ($p) => (\App\Services\ProgramasService::LICENCIAS[$p->licencia] ?? 'Gratuita').($p->requiere_clave ? ' · clave' : ''),
                    ],
                    ['key' => 'peso', 'label' => 'Peso', 'width' => '8%', 'sort' => 'peso', 'value' => fn ($p) => $servicio->pesoLegible($p->peso_mb), 'mobile' => 'filled'],
                    ['key' => 'equipos', 'label' => 'Equipos', 'width' => '7%', 'sort' => 'equipos', 'value' => fn ($p) => (string) (int) $p->equipos],
                    [
                        'key' => 'activo',
                        'label' => 'Estado',
                        'width' => '8%',
                        'sort' => 'activo',
                        'type' => 'status',
                        'value' => fn ($p) => $p->activo ? 'Activo' : 'Inactivo',
                    ],
                ]"

                :actions="fn ($p) => [
                    'edit' => route('catalogos.programas.edit', ['programa' => $p->id_programa]),
                    'menu' => array_values(array_filter([
                        ['label' => 'Editar', 'href' => route('catalogos.programas.edit', ['programa' => $p->id_programa])],
                        // Inventario filtrado por «Programa instalado».
                        (int) $p->equipos > 0 ? ['label' => 'Ver equipos (' . (int) $p->equipos . ')', 'href' => route('equipos.index', ['programa' => $p->id_programa])] : null,
                        filled($p->enlace_descarga) ? ['label' => 'Abrir enlace de descarga', 'js' => 'window.open('.\Illuminate\Support\Js::from($p->enlace_descarga).', \'_blank\', \'noopener\')'] : null,
                        [
                            'label' => $p->activo ? 'Desactivar' : 'Activar',
                            'click' => 'alternarPrograma(' . (int) $p->id_programa . ')',
                            'danger' => (bool) $p->activo,
                        ],
                        \App\Support\PapeleraEntidades::accion('programa', $p->id_programa),
                    ])),
                ]"
            />
        </div>
    @endif
</div>
