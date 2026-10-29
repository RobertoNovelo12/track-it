<?php

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/*
|--------------------------------------------------------------------------
| Administración › Ubicaciones › Tipos de ubicación (listado)
|--------------------------------------------------------------------------
|
| Lista editable (Cocina, Club, Cuarto de comunicaciones…). Agregar y
| editar se hacen en su propia página (⚡tipo-ubicacion-editor). Un tipo
| inactivo ya no se ofrece al crear ubicaciones; las que lo tienen lo
| conservan.
|
*/

new class extends Component
{
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;
    use WithPagination;

    public string $search = '';
    public string $estado = 'todos';

    public int $perPage = 10;
    public string $sortField = 'orden';
    public string $sortDirection = 'asc';

    private const ORDENABLES = [
        'orden' => 'tu.orden',
        'nombre' => 'tu.nombre',
        'ubicaciones' => 'ubicaciones',
        'activo' => 'tu.activo',
    ];

    public function mount(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');
    }

    public function updated(string $propiedad): void
    {
        if ($propiedad === 'perPage' && ! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        if (in_array($propiedad, ['search', 'estado', 'perPage', 'sortDirection'], true)) {
            $this->resetPage(pageName: 'tiposUbicacionPage');
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

        $this->resetPage(pageName: 'tiposUbicacionPage');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = 'todos';

        $this->resetPage(pageName: 'tiposUbicacionPage');
    }

    public function tipos()
    {
        $search = trim($this->search);

        return DB::table('tipos_ubicacion as tu')
            ->leftJoin('ubicaciones as ub', 'ub.id_tipo_ubicacion', '=', 'tu.id_tipo_ubicacion')
            ->when($search !== '', fn ($query) => $query->where('tu.nombre', 'ilike', "%{$search}%"))
            ->when(in_array($this->estado, ['activos', 'inactivos'], true), fn ($query) => $query->where('tu.activo', $this->estado === 'activos'))
            ->groupBy('tu.id_tipo_ubicacion', 'tu.nombre', 'tu.orden', 'tu.activo')
            ->select(['tu.id_tipo_ubicacion', 'tu.nombre', 'tu.orden', 'tu.activo'])
            ->selectRaw('COUNT(ub.id_ubicacion) AS ubicaciones')
            ->orderByRaw(
                (self::ORDENABLES[$this->sortField] ?? 'tu.orden')
                .' '.($this->sortDirection === 'desc' ? 'desc' : 'asc')
            )
            ->orderBy('tu.nombre')
            ->paginate(perPage: $this->perPage, pageName: 'tiposUbicacionPage');
    }

    public function alternar(int $id): void
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        DB::table('tipos_ubicacion')->where('id_tipo_ubicacion', $id)->update(['activo' => DB::raw('NOT activo')]);
    }
};
?>

<div class="min-w-0 space-y-5">
    @php
        $tipos = $this->tipos();
    @endphp

    <x-admin.filter-bar>
        <x-admin.search
            id="tipos-ubicacion-search"
            wire:model.live.debounce.350ms="search"
            placeholder="Buscar tipo..."
            label="Buscar tipo de ubicación"
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
            <x-admin.action-button :href="route('catalogos.tipos-ubicacion.create')">
                Añadir tipo
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

    <x-data-table
        :rows="$tipos"
        row-key="id_tipo_ubicacion"
        key-prefix="tipo-ubicacion"

        :sort-field="$sortField"
        :sort-direction="$sortDirection"
        sort-action="sortBy"

        per-page-model="perPage"
        sort-model="sortDirection"
        refresh-action="$refresh"

        page-name="tiposUbicacionPage"
        result-singular="tipo encontrado"
        result-plural="tipos encontrados"
        empty-message="No se encontraron tipos de ubicación."
        min-width="640px"

        :columns="[
            ['key' => 'nombre', 'label' => 'Tipo', 'width' => '45%', 'sort' => 'nombre', 'strong' => true],
            [
                'key' => 'ubicaciones',
                'label' => 'Ubicaciones',
                'width' => '20%',
                'sort' => 'ubicaciones',
                'value' => fn ($t) => number_format((int) $t->ubicaciones),
            ],
            [
                'key' => 'activo',
                'label' => 'Estado',
                'width' => '20%',
                'sort' => 'activo',
                'type' => 'status',
                'value' => fn ($t) => $t->activo ? 'Activo' : 'Inactivo',
            ],
        ]"

        :actions="fn ($t) => [
            'edit' => route('catalogos.tipos-ubicacion.edit', ['tipo' => $t->id_tipo_ubicacion]),
            'menu' => array_values(array_filter([
                ['label' => 'Editar', 'href' => route('catalogos.tipos-ubicacion.edit', ['tipo' => $t->id_tipo_ubicacion])],
                [
                    'label' => $t->activo ? 'Desactivar' : 'Activar',
                    'click' => 'alternar(' . (int) $t->id_tipo_ubicacion . ')',
                    'danger' => (bool) $t->activo,
                ],
                \App\Support\PapeleraEntidades::accion('tipo_ubicacion', $t->id_tipo_ubicacion),
            ])),
        ]"
    />
</div>
