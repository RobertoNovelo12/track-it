<?php

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/*
|--------------------------------------------------------------------------
| Administración › Proveedores
|--------------------------------------------------------------------------
|
| Empresas que venden o dan servicio a los equipos. Se eligen al dar de
| alta un equipo, en los mantenimientos en garantía y como proveedor
| sugerido de marcas y modelos. Agregar / editar tiene su propia página
| (⚡proveedor-editor).
|
*/

new class extends Component
{
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;
    use WithPagination;

    public string $search = '';
    public string $estado = 'todos';

    public int $perPage = 10;
    // Por defecto lo más reciente primero (último registro arriba).
    public string $sortField = 'recientes';
    public string $sortDirection = 'desc';

    private const ORDENABLES = [
        'recientes' => 'p.id_proveedor',
        'nombre' => 'p.nombre',
        'contacto' => 'p.contacto',
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
                ['label' => 'Proveedor', 'width' => '24%'],
                ['label' => 'Contacto', 'width' => '18%'],
                ['label' => 'Teléfono', 'width' => '14%'],
                ['label' => 'Correo', 'width' => '22%'],
                ['label' => 'Equipos', 'width' => '9%'],
                ['label' => 'Estado', 'width' => '10%', 'type' => 'status'],
            ],
            'anchoMinimo' => '960px',
        ]);
    }

    public function updated(string $propiedad): void
    {
        if ($propiedad === 'perPage' && ! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        if (in_array($propiedad, ['search', 'estado', 'perPage', 'sortDirection'], true)) {
            $this->resetPage(pageName: 'proveedoresPage');
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

        $this->resetPage(pageName: 'proveedoresPage');
    }

    public function limpiarFiltros(): void
    {
        $this->reset('search', 'estado');
        $this->resetPage(pageName: 'proveedoresPage');
    }

    public function proveedores()
    {
        $equipos = DB::table('equipos')
            ->selectRaw('id_proveedor, COUNT(*) AS total')
            ->whereNotNull('id_proveedor')
            ->groupBy('id_proveedor');

        $consulta = DB::table('proveedores as p')
            ->leftJoinSub($equipos, 'ep', 'ep.id_proveedor', '=', 'p.id_proveedor')
            ->select('p.id_proveedor', 'p.nombre', 'p.contacto', 'p.telefono', 'p.correo', 'p.activo')
            ->selectRaw('COALESCE(ep.total, 0) AS equipos')
            ->when(in_array($this->estado, ['activos', 'inactivos'], true), fn ($query) => $query->where('p.activo', $this->estado === 'activos'));

        \App\Support\BusquedaTolerante::aplicar($consulta, $this->search, ['p.nombre', 'p.contacto', 'p.correo', 'p.telefono']);

        return $consulta
            ->orderByRaw(
                (self::ORDENABLES[$this->sortField] ?? 'p.nombre')
                .' '.($this->sortDirection === 'desc' ? 'desc' : 'asc')
                .' NULLS LAST'
            )
            ->orderBy('p.id_proveedor')
            ->paginate(perPage: $this->perPage, pageName: 'proveedoresPage');
    }

    public function metricas(): array
    {
        $fila = DB::table('proveedores')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE activo) AS activos')
            ->first();

        return [
            'total' => (int) ($fila->total ?? 0),
            'activos' => (int) ($fila->activos ?? 0),
            'con_equipos' => (int) DB::table('equipos')->whereNotNull('id_proveedor')->distinct()->count('id_proveedor'),
        ];
    }

    public function alternarProveedor(int $id): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        DB::table('proveedores')->where('id_proveedor', $id)->update([
            'activo' => DB::raw('NOT activo'),
        ]);

        app(\App\Services\SystemCacheService::class)->refreshModule('catalogos');
    }
};
?>

<div class="min-w-0 space-y-5">
    @php
        $proveedores = $this->proveedores();
        $metricas = $this->metricas();
    @endphp

    <x-admin.filter-bar>
        <x-admin.search
            id="proveedores-search"
            wire:model.live.debounce.350ms="search"
            placeholder="Buscar proveedor, contacto o correo..."
            label="Buscar proveedor"
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
            <x-admin.action-button :href="route('catalogos.proveedores.create')">
                Añadir proveedor
            </x-admin.action-button>
        </x-slot:actions>

        <x-slot:footer>
            @if ($search !== '' || $estado !== 'todos')
                <button type="button" wire:click="limpiarFiltros" class="text-xs font-medium text-[var(--theme-primary)] hover:underline">
                    Limpiar filtros
                </button>
            @endif
        </x-slot:footer>
    </x-admin.filter-bar>

    <x-admin.metrics :items="[
        ['label' => 'Total de proveedores', 'value' => $metricas['total']],
        ['label' => 'Proveedores activos', 'value' => $metricas['activos']],
        ['label' => 'Con equipos registrados', 'value' => $metricas['con_equipos']],
    ]" />

    <x-data-table
        :rows="$proveedores"
        row-key="id_proveedor"
        key-prefix="proveedor"
        bulk-trash="proveedor"

        :sort-field="$sortField"
        :sort-direction="$sortDirection"
        sort-action="sortBy"

        per-page-model="perPage"
        sort-model="sortDirection"
        refresh-action="$refresh"

        page-name="proveedoresPage"
        result-singular="proveedor encontrado"
        result-plural="proveedores encontrados"
        empty-message="No hay proveedores que coincidan. Agrega el primero con «Añadir proveedor»."
        min-width="960px"

        :mobile-subtitle="fn ($p) => trim(implode(' · ', array_filter([$p->contacto, $p->telefono])))"

        :columns="[
            ['key' => 'nombre', 'label' => 'Proveedor', 'width' => '24%', 'sort' => 'nombre', 'strong' => true],
            ['key' => 'contacto', 'label' => 'Contacto', 'width' => '18%', 'sort' => 'contacto', 'mobile' => 'filled'],
            ['key' => 'telefono', 'label' => 'Teléfono', 'width' => '14%', 'mobile' => 'filled'],
            ['key' => 'correo', 'label' => 'Correo', 'width' => '22%', 'mobile' => 'filled'],
            ['key' => 'equipos', 'label' => 'Equipos', 'width' => '9%', 'sort' => 'equipos', 'value' => fn ($p) => (string) (int) $p->equipos],
            [
                'key' => 'activo',
                'label' => 'Estado',
                'width' => '10%',
                'sort' => 'activo',
                'type' => 'status',
                'value' => fn ($p) => $p->activo ? 'Activo' : 'Inactivo',
            ],
        ]"

        :actions="fn ($p) => [
            'edit' => route('catalogos.proveedores.edit', ['proveedor' => $p->id_proveedor]),
            'menu' => array_values(array_filter([
                ['label' => 'Editar', 'href' => route('catalogos.proveedores.edit', ['proveedor' => $p->id_proveedor])],
                [
                    'label' => $p->activo ? 'Desactivar' : 'Activar',
                    'click' => 'alternarProveedor(' . (int) $p->id_proveedor . ')',
                    'danger' => (bool) $p->activo,
                ],
                \App\Support\PapeleraEntidades::accion('proveedor', $p->id_proveedor),
            ])),
        ]"
    />
</div>
