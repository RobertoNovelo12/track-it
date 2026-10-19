<?php

use App\Models\Departamento;
use App\Models\Hotel;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    public function mount(): void
    {
        // Solo Admin modifica la organización (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');
    }

    use WithPagination;

    /*
     * Vista mientras la sección se carga en Administración.
     */
    public function placeholder(array $params = [])
    {
        return view('livewire.placeholders.administracion-seccion');
    }

    public string $search = '';
    public string $estado = 'todos';
    public string $hotelId = '';

    /*
     * Tabla: registros por página y ordenamiento.
     */
    public int $perPage = 10;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    private const ORDENABLES = [
        'nombre' => 'departamentos.nombre',
        'codigo' => 'departamentos.codigo',
        'responsable' => 'departamentos.responsable',
        'cobertura' => 'departamentos.atiende_todas_unidades',
        'activo' => 'departamentos.activo',
    ];

    public function updatedPerPage(): void
    {
        if (! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedSortDirection(): void
    {
        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }

        $this->resetPage(pageName: 'departamentosPage');
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

        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedEstado(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedHotelId(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = 'todos';
        $this->hotelId = '';

        $this->resetPage(pageName: 'departamentosPage');
    }

    public function departamentos()
    {
        $search = trim($this->search);

        return Departamento::query()
            ->principales()
            ->with([
                'hoteles' => fn ($query) => $query
                    ->orderBy('unidades_organizativas.nombre'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter
                        ->where('departamentos.nombre', 'ilike', "%{$search}%")
                        ->orWhere('departamentos.codigo', 'ilike', "%{$search}%")
                        ->orWhere('departamentos.responsable', 'ilike', "%{$search}%");
                });
            })
            ->when(
                in_array($this->estado, ['activos', 'inactivos'], true),
                function ($query) {
                    $query->where(
                        'departamentos.activo',
                        $this->estado === 'activos'
                    );
                }
            )
            ->when($this->hotelId !== '', function ($query) {
                $hotelId = (int) $this->hotelId;

                $query->where(function ($coverage) use ($hotelId) {
                    $coverage
                        ->where('departamentos.atiende_todas_unidades', true)
                        ->orWhereHas('hoteles', function ($hoteles) use ($hotelId) {
                            $hoteles->where('unidades_organizativas.id_unidad', $hotelId);
                        });
                });
            })
            ->orderByRaw(
                (self::ORDENABLES[$this->sortField] ?? self::ORDENABLES['nombre'])
                .' '.($this->sortDirection === 'desc' ? 'desc' : 'asc')
                .' NULLS LAST'
            )
            ->orderBy('departamentos.id_departamento')
            ->paginate(
                perPage: $this->perPage,
                pageName: 'departamentosPage'
            );
    }

    /*
     * Totales para las tarjetas de métricas.
     */
    public function metricas(): array
    {
        $fila = Departamento::query()
            ->principales()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE departamentos.activo) AS activos')
            ->selectRaw('COUNT(*) FILTER (WHERE departamentos.atiende_todas_unidades) AS globales')
            ->first();

        return [
            'total' => (int) ($fila->total ?? 0),
            'activos' => (int) ($fila->activos ?? 0),
            'globales' => (int) ($fila->globales ?? 0),
        ];
    }

    public function hoteles()
    {
        return Hotel::query()
            ->orderBy('nombre')
            ->get([
                'id_unidad',
                'nombre',
                'activo',
            ]);
    }
};
?>

<div class="min-w-0 space-y-5">
    @php
        $departamentos = $this->departamentos();
        $hoteles = $this->hoteles();
        $metricas = $this->metricas();
        $etiquetasPlantilla = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
    @endphp

    {{-- Buscador y filtros (el título está en Administración) --}}
    <x-admin.filter-bar>
        <x-admin.search
            id="departamentos-search"
            wire:model.live.debounce.350ms="search"
            placeholder="Buscar por nombre, código o responsable..."
            label="Buscar departamento"
        />

        <x-admin.select
            model="hotelId"
            :placeholder="$etiquetasPlantilla['unidad_singular']"
            :clear-label="$etiquetasPlantilla['todas_unidades']"
            :options="$hoteles->map(fn ($hotel) => [
                'value' => (string) $hotel->id_unidad,
                'label' => $hotel->nombre.($hotel->activo ? '' : ' (inactivo)'),
            ])->values()->all()"
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

        <x-slot:footer>
            @if ($search !== '' || $estado !== 'todos' || $hotelId !== '')
                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="text-xs font-medium text-[var(--theme-primary)] hover:underline"
                >
                    Limpiar filtros
                </button>
            @endif
        </x-slot:footer>
    </x-admin.filter-bar>

    {{-- Métricas --}}
    <x-admin.metrics :items="[
        ['label' => 'Total de departamentos', 'value' => $metricas['total']],
        ['label' => 'Departamentos activos', 'value' => $metricas['activos']],
        ['label' => $etiquetasPlantilla['atiende_todas_unidades'], 'value' => $metricas['globales']],
    ]" />

    {{-- LISTADO: componente reutilizable <x-data-table> --}}
    <x-data-table

        :rows="$departamentos"
        row-key="id_departamento"
        key-prefix="departamento"

        :sort-field="$sortField"
        :sort-direction="$sortDirection"
        sort-action="sortBy"

        per-page-model="perPage"
        sort-model="sortDirection"
        refresh-action="$refresh"

        page-name="departamentosPage"
        result-singular="departamento encontrado"
        result-plural="departamentos encontrados"
        empty-message="No se encontraron departamentos. Prueba con otro nombre o cambia los filtros."
        min-width="980px"

        :columns="[
            [
                'key' => 'nombre',
                'label' => 'Departamento',
                'width' => '22%',
                'sort' => 'nombre',
                'strong' => true,
            ],
            [
                'key' => 'codigo',
                'label' => 'Código',
                'width' => '11%',
                'sort' => 'codigo',
                'mobile' => 'filled',
            ],
            [
                'key' => 'cobertura',
                'label' => $etiquetasPlantilla['cobertura'],
                'width' => '27%',
                'sort' => 'cobertura',
                'type' => 'list',
                'value' => fn ($d) => $d->atiende_todas_unidades
                    ? [$etiquetasPlantilla['todas_unidades']]
                    : $d->hoteles->map(fn ($h) => $h->nombre.($h->activo ? '' : ' (inactivo)'))->all(),
                'default' => $etiquetasPlantilla['sin_unidades'],
            ],
            [
                'key' => 'responsable',
                'label' => 'Responsable',
                'width' => '17%',
                'sort' => 'responsable',
                'default' => 'Sin responsable',
            ],
            [
                'key' => 'correo',
                'label' => 'Correo',
                'desktop' => false,
                'mobile' => 'filled',
            ],
            [
                'key' => 'extension_telefono',
                'label' => 'Extensión',
                'desktop' => false,
                'mobile' => 'filled',
            ],
            [
                'key' => 'activo',
                'label' => 'Estado',
                'width' => '12%',
                'sort' => 'activo',
                'type' => 'status',
                'value' => fn ($d) => $d->activo ? 'Activo' : 'Inactivo',
            ],
        ]"

        :actions="fn ($d) => [
            'edit' => route('catalogos.departamentos.edit', ['departamento' => $d->id_departamento]),
            'menu' => [
                [
                    'label' => 'Editar',
                    'href' => route('catalogos.departamentos.edit', ['departamento' => $d->id_departamento]),
                ],
            ],
        ]"
    />
</div>