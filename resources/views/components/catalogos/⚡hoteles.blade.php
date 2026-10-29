<?php

use App\Models\Hotel;
use App\Models\Departamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;

    use WithPagination;

    /*
     * Vista mientras la sección se carga en Administración.
     */
    public function placeholder(array $params = [])
    {
        return view('livewire.placeholders.administracion-seccion', [
            'columnas' => [
                ['label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'], 'width' => '32%'],
                ['label' => 'Sede', 'width' => '24%'],
                ['label' => 'Código', 'width' => '16%'],
                ['label' => 'Estado', 'width' => '16%', 'type' => 'status'],
            ],
            'anchoMinimo' => '760px',
        ]);
    }

    #[Locked]
    public bool $paginaEdicion = false;

    #[Locked]
    public ?int $editingId = null;

    public string $search = '';
    public string $estado = 'todos';

    /*
     * Tabla: registros por página y ordenamiento.
     */
    public int $perPage = 10;
    // Por defecto lo más reciente primero (último registro arriba).
    public string $sortField = 'recientes';
    public string $sortDirection = 'desc';

    private const ORDENABLES = ['recientes', 'nombre', 'sede', 'codigo', 'activo'];
    public bool $formOpen = false;
    public string $message = '';

    public string $idSede = '';
    public string $codigo = '';
    public string $nombre = '';
    public string $responsable = '';
    public string $correo = '';
    public string $extension_telefono = '';
    public string $descripcion = '';
    public bool $activo = true;
    public array $idDepartamentos = [];

    public function mount(?int $hotelId = null): void
    {
        // Solo Admin modifica la organización (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        if ($hotelId !== null) {
            $this->editHotel($hotelId);
            $this->paginaEdicion = true;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage(pageName: 'hotelesPage');
    }

    public function updatedEstado(): void
    {
        $this->resetPage(pageName: 'hotelesPage');
    }

    public function updatedPerPage(): void
    {
        if (! in_array((int) $this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        $this->resetPage(pageName: 'hotelesPage');
    }

    public function updatedSortDirection(): void
    {
        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }

        $this->resetPage(pageName: 'hotelesPage');
    }

    public function sortBy(string $campo): void
    {
        if (! in_array($campo, self::ORDENABLES, true)) {
            return;
        }

        if ($this->sortField === $campo) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $campo;
            $this->sortDirection = 'asc';
        }

        $this->resetPage(pageName: 'hotelesPage');
    }

    public function createHotel(): void
    {
        $this->resetForm();
        $this->formOpen = true;
    }

    public function editHotel(int $id): void
    {
        $hotel = Hotel::findOrFail($id);

        $this->resetForm();

        $this->editingId = (int) $hotel->id_unidad;
        $this->idSede = (string) $hotel->id_sede;

        foreach ([
            'codigo',
            'nombre',
            'responsable',
            'correo',
            'extension_telefono',
            'descripcion',
        ] as $field) {
            $this->{$field} = (string) ($hotel->{$field} ?? '');
        }

        $this->activo = (bool) $hotel->activo;
        $this->idDepartamentos = $hotel->departamentos()->principales()
            ->where('atiende_todas_unidades', false)
            ->pluck('departamentos.id_departamento')->map(fn ($id) => (string) $id)->all();
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        if ($this->paginaEdicion) {
            $this->redirectRoute(
                'catalogos.index',
                ['section' => 'hoteles']
            );

            return;
        }

        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->reset([
            'editingId',
            'idDepartamentos',
            'idSede',
            'codigo',
            'nombre',
            'responsable',
            'correo',
            'extension_telefono',
            'descripcion',
            'activo',
            'formOpen',
        ]);

        $this->message = '';
    }

    public function saveHotel(): void
    {
        abort_unless(auth()->check(), 403);

        $this->message = '';
        $unidad = mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']);

        foreach ([
            'codigo',
            'nombre',
            'responsable',
            'correo',
            'extension_telefono',
            'descripcion',
        ] as $field) {
            $this->{$field} = trim($this->{$field});
        }

        $validated = $this->validate([
            'idDepartamentos' => ['array'],
            'idDepartamentos.*' => ['integer', 'distinct',
                Rule::exists('departamentos', 'id_departamento')->whereNull('id_departamento_principal')],
            'idSede' => [
                'required',
                'integer',
                Rule::exists('sedes', 'id_sede'),
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unidades_organizativas', 'nombre')
                    ->where('id_sede', $this->idSede)
                    ->ignore($this->editingId, 'id_unidad'),
            ],
            'codigo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'responsable' => [
                'nullable',
                'string',
                'max:255',
            ],
            'correo' => [
                'nullable',
                'email',
                'max:255',
            ],
            'extension_telefono' => [
                'nullable',
                'string',
                'max:255',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'activo' => [
                'boolean',
            ],
        ], [
            'idDepartamentos.*.exists' => 'Un departamento ya no está disponible. Recarga la página.',
            'idDepartamentos.*.distinct' => 'No repitas departamentos.',
            'idSede.required' => 'Selecciona una sede.',
            'idSede.exists' => 'La sede seleccionada ya no existe.',
            'nombre.required' => 'Escribe el nombre del '.$unidad.'.',
            'nombre.unique' => 'Ya existe un '.$unidad.' con ese nombre en esta sede.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'correo.email' => 'Escribe un correo válido.',
        ]);

        $data = [
            'id_sede' => (int) $validated['idSede'],
            'nombre' => $validated['nombre'],
            'activo' => $validated['activo'],
        ];

        foreach ([
            'codigo',
            'responsable',
            'correo',
            'extension_telefono',
            'descripcion',
        ] as $field) {
            $data[$field] = ($validated[$field] ?? '') !== ''
                ? $validated[$field]
                : null;
        }

        DB::transaction(function () use ($data, $validated) {
            $departamentos = Departamento::principales()->orderBy('id_departamento')->lockForUpdate()->get();
            $hotel = $this->editingId ? Hotel::lockForUpdate()->findOrFail($this->editingId) : new Hotel;
            $hotel->fill($data)->save();
            // La cobertura global no necesita asociaciones individuales.
            $seleccionados = $departamentos->where('atiende_todas_unidades', false)
                ->whereIn('id_departamento', $validated['idDepartamentos'])->pluck('id_departamento')->all();
            $quitar = $departamentos->pluck('id_departamento')->diff($seleccionados)->all();
            $hotel->departamentos()->detach($quitar);
            $hotel->departamentos()->syncWithoutDetaching($seleccionados);
        });
        app(\App\Services\SystemCacheService::class)->refreshModule('catalogos');

        if ($this->paginaEdicion) {
            $this->resetValidation();
            // Confirmación con el aviso global (components/ui/toast-region).
        $this->dispatch('toast', type: 'success', message: ucfirst($unidad).' actualizado correctamente.');

            return;
        }

        $this->resetForm();

        $this->search = '';
        $this->estado = 'todos';

        $this->resetPage(pageName: 'hotelesPage');

        // Confirmación con el aviso global (components/ui/toast-region).
        $this->dispatch('toast', type: 'success', message: ucfirst($unidad).' guardado correctamente.');
    }

    /*
     * Totales para las tarjetas de métricas.
     */
    public function metricas(): array
    {
        $fila = Hotel::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE activo) AS activos')
            ->first();

        $total = (int) ($fila->total ?? 0);
        $activos = (int) ($fila->activos ?? 0);

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $total - $activos,
        ];
    }

    public function hoteles()
    {
        $search = trim($this->search);

        return Hotel::query()
            ->with('sede')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nombre', 'ilike', '%'.$search.'%')
                        ->orWhere('codigo', 'ilike', '%'.$search.'%')
                        ->orWhereHas(
                            'sede',
                            fn ($sede) => $sede->where(
                                'nombre',
                                'ilike',
                                '%'.$search.'%'
                            )
                        );
                });
            })
            ->when(
                in_array($this->estado, ['activos', 'inactivos'], true),
                fn ($query) => $query->where(
                    'activo',
                    $this->estado === 'activos'
                )
            )
            ->when(
                $this->sortField === 'sede',
                fn ($query) => $query->orderBy(
                    \App\Models\Sede::select('nombre')
                        ->whereColumn('sedes.id_sede', 'unidades_organizativas.id_sede')
                        ->limit(1),
                    $this->sortDirection
                ),
                fn ($query) => $query->orderBy(
                    match (true) {
                        $this->sortField === 'recientes' => 'id_unidad',
                        in_array($this->sortField, self::ORDENABLES, true) => $this->sortField,
                        default => 'id_unidad',
                    },
                    $this->sortDirection === 'desc' ? 'desc' : 'asc'
                )
            )
            ->orderBy('id_unidad')
            ->paginate($this->perPage, ['*'], 'hotelesPage');
    }
};
?>

<div class="min-w-0 space-y-5">
    @php
        $hoteles = $paginaEdicion ? null : $this->hoteles();
        $metricas = $paginaEdicion ? [] : $this->metricas();
        $etiquetasUnidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
        $unidad = mb_strtolower($etiquetasUnidad['unidad_singular']);
        $unidades = mb_strtolower($etiquetasUnidad['unidad_plural']);
    @endphp

    {{-- Buscador, filtros y acción (el título está en Administración) --}}
    @if (! $paginaEdicion)
        <x-admin.filter-bar>
            <x-admin.search
                id="hoteles-search"
                wire:model.live.debounce.350ms="search"
                placeholder="Buscar por nombre, código o sede..."
                :label="'Buscar '.$unidades"
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
                <x-admin.action-button wire:click="createHotel" wire:loading.attr="disabled">
                    Añadir {{ $unidad }}
                </x-admin.action-button>
            </x-slot:actions>
        </x-admin.filter-bar>

        <x-admin.metrics :items="[
            ['label' => 'Total de '.$unidades, 'value' => $metricas['total']],
            ['label' => ucfirst($unidades).' activos', 'value' => $metricas['activos']],
            ['label' => ucfirst($unidades).' inactivos', 'value' => $metricas['inactivos']],
        ]" />
    @endif

    {{-- Confirmación --}}
    @if ($message)
        <div
            role="status"
            class="rounded-lg border border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] px-4 py-3 text-sm text-[var(--theme-text-strong)]"
        >
            {{ $message }}
        </div>
    @endif

    {{-- Formulario de alta o edición --}}
    @if ($formOpen)
        <form
            wire:submit="saveHotel"
            wire:key="hotel-form-{{ $editingId ?? 'new' }}"
            class="space-y-5"
        >
            {{-- Mismo diseño que Nueva Alta de equipo --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                {{ $paginaEdicion ? 'Información del '.$unidad : 'Nuevo '.$unidad }}
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Sede *
                    </label>

                    <x-searchable-select
                        wire-model="idSede"
                        :empty-as-null="false"
                        placeholder="Selecciona una sede"
                        :show-clear="false"
                        :options="\App\Models\Sede::orderBy('nombre')->get()->map(fn ($sede) => [
                            'value' => (string) $sede->id_sede,
                            'label' => $sede->nombre.($sede->activo ? '' : ' (inactiva)'),
                        ])->values()->all()"
                    />

                    @error('idSede')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                @foreach ([
                    'nombre' => 'Nombre *',
                    'codigo' => 'Código',
                    'responsable' => 'Responsable',
                    'correo' => 'Correo',
                    'extension_telefono' => 'Extensión / teléfono',
                ] as $field => $label)
                    <div>
                        <label
                            for="hotel-{{ $field }}"
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            {{ $label }}
                        </label>

                        <input
                            id="hotel-{{ $field }}"
                            wire:model="{{ $field }}"
                            type="{{ $field === 'correo' ? 'email' : 'text' }}"
                            maxlength="255"
                            @required($field === 'nombre')
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                        >

                        @error($field)
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach

                <div class="md:col-span-2 xl:col-span-3">
                    <label
                        for="hotel-descripcion"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Descripción
                    </label>

                    <textarea
                        id="hotel-descripcion"
                        wire:model="descripcion"
                        rows="3"
                        maxlength="10000"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] min-h-24 resize-y"
                    ></textarea>

                    @error('descripcion')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <label class="mt-4 inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                <input
                    type="checkbox"
                    wire:model="activo"
                    class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                >

                {{ $etiquetasUnidad['unidad_singular'] }} activo
            </label>

            @error('activo')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
            @enderror
            </section>

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Departamentos</h2>
                <p class="mt-1 mb-4 text-xs text-[var(--theme-text-muted)]">
                    Selecciona los departamentos que atienden esta unidad. Los de cobertura global se incluyen automáticamente, también en las nuevas unidades.
                    Los inactivos conservan su asignación, pero no estarán disponibles al registrar equipos.
                </p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @forelse (\App\Models\Departamento::principales()->orderBy('nombre')->get() as $departamento)
                        <label wire:key="hotel-departamento-{{ $departamento->id_departamento }}" class="flex items-start gap-3 rounded-lg border border-[var(--theme-border)] p-3 text-sm text-[var(--theme-text)]">
                            @if ($departamento->atiende_todas_unidades)
                                <input type="checkbox" checked disabled class="mt-1 h-4 w-4 accent-[var(--theme-primary)]">
                            @else
                                <input type="checkbox" wire:model="idDepartamentos" value="{{ $departamento->id_departamento }}" class="mt-1 h-4 w-4 accent-[var(--theme-primary)]">
                            @endif
                            <span>
                                {{ $departamento->nombre }}
                                @if (! $departamento->activo)
                                    <span class="text-xs text-[var(--theme-text-muted)]">(Inactivo)</span>
                                @endif
                                @if ($departamento->atiende_todas_unidades)
                                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">{{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['atiende_todas_unidades'] }} · Automático</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-[var(--theme-text-muted)]">No hay departamentos registrados.</p>
                    @endforelse
                </div>
                @error('idDepartamentos')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror
                @error('idDepartamentos.*')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    wire:click="closeForm"
                    wire:loading.attr="disabled"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                >
                    Cancelar
                </button>

                <x-ui.button
                    type="submit"
                    target="saveHotel"
                    size="lg"
                    loading-text="Guardando…"
                    class="w-full sm:w-auto"
                >
                    {{ $paginaEdicion ? 'Guardar cambios' : 'Guardar '.$unidad }}
                </x-ui.button>
            </div>
        </form>
    @endif

    {{-- Listado: solo en Administración --}}
    @if (! $paginaEdicion)
        {{-- Listado: componente reutilizable <x-data-table> --}}
        <x-data-table
            :rows="$hoteles"
            row-key="id_unidad"
            key-prefix="hotel"
            bulk-trash="hotel"

            :sort-field="$sortField"
            :sort-direction="$sortDirection"
            sort-action="sortBy"

            per-page-model="perPage"
            sort-model="sortDirection"
            refresh-action="$refresh"

            page-name="hotelesPage"
            :result-singular="$unidad.' encontrado'"
            :result-plural="$unidades.' encontrados'"
            :empty-message="'No se encontraron '.$unidades.' con estos filtros.'"
            min-width="760px"

            :columns="[
                [
                    'key' => 'nombre',
                    'label' => $etiquetasUnidad['unidad_singular'],
                    'width' => '32%',
                    'sort' => 'nombre',
                    'strong' => true,
                ],
                [
                    'key' => 'sede',
                    'label' => 'Sede',
                    'width' => '24%',
                    'sort' => 'sede',
                    'value' => fn ($hotel) => $hotel->sede?->nombre,
                    'default' => 'Sin sede',
                ],
                [
                    'key' => 'codigo',
                    'label' => 'Código',
                    'width' => '16%',
                    'sort' => 'codigo',
                ],
                [
                    'key' => 'activo',
                    'label' => 'Estado',
                    'width' => '16%',
                    'sort' => 'activo',
                    'type' => 'status',
                    'value' => fn ($hotel) => $hotel->activo ? 'Activo' : 'Inactivo',
                ],
            ]"

            :actions="fn ($hotel) => [
                'edit' => route('catalogos.hoteles.edit', ['hotel' => $hotel->id_unidad]),
                'menu' => array_values(array_filter([
                    [
                        'label' => 'Editar',
                        'href' => route('catalogos.hoteles.edit', ['hotel' => $hotel->id_unidad]),
                    ],
                    \App\Support\PapeleraEntidades::accion('hotel', $hotel->id_unidad),
                ])),
            ]"
        />
    @endif
</div>