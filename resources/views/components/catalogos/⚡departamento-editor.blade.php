<?php

use App\Models\Departamento;
use App\Models\Hotel;
use App\Services\SystemCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $departamentoId;

    public string $nombre = '';
    public string $descripcion = '';

    public bool $activo = true;
    public bool $atiendeTodos = false;

    public array $hotelIds = [];

    /*
     * Ubicaciones donde trabaja el departamento
     * (tabla departamento_ubicacion).
     */
    public array $ubicacionIds = [];

    public string $message = '';

    public function mount(int $departamentoId): void
    {
        // Solo Admin modifica la organización (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $departamento = Departamento::query()
            ->principales()
            ->with('hoteles')
            ->findOrFail($departamentoId);

        $this->departamentoId = (int) $departamento->id_departamento;
        $this->nombre = (string) $departamento->nombre;
        $this->descripcion = (string) ($departamento->descripcion ?? '');
        $this->activo = (bool) $departamento->activo;
        $this->atiendeTodos = (bool) $departamento->atiende_todas_unidades;

        $this->hotelIds = $departamento->hoteles
            ->map(fn ($hotel) => (string) $hotel->id_unidad)
            ->all();

        if ($this->gestionaUbicaciones()) {
            $this->ubicacionIds = DB::table('departamento_ubicacion')
                ->where('id_departamento', $this->departamentoId)
                ->pluck('id_ubicacion')
                ->map(fn ($id) => (string) $id)
                ->all();
        }
    }

    /*
     * La sección de ubicaciones aparece cuando ya se ejecutó la
     * migración que crea departamento_ubicacion.
     */
    public function gestionaUbicaciones(): bool
    {
        return Schema::hasTable('departamento_ubicacion')
            && Schema::hasColumn('ubicaciones', 'id_unidad');
    }

    /*
     * Ubicaciones activas agrupadas por hotel. Cada una indica con
     * qué otros departamentos se comparte.
     */
    public function ubicacionesPorHotel(): array
    {
        if (! $this->gestionaUbicaciones()) {
            return [];
        }

        $ubicaciones = DB::table('ubicaciones as ub')
            ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'ub.id_unidad')
            ->where('ub.activo', true)
            ->orderByRaw('u.nombre IS NULL, u.nombre')
            ->orderBy('ub.nombre')
            ->get([
                'ub.id_ubicacion',
                'ub.nombre',
                'u.nombre as hotel',
            ]);

        $otros = DB::table('departamento_ubicacion as du')
            ->join('departamentos as d', 'd.id_departamento', '=', 'du.id_departamento')
            ->where('du.id_departamento', '<>', $this->departamentoId)
            ->orderBy('d.nombre')
            ->get(['du.id_ubicacion', 'd.nombre'])
            ->groupBy('id_ubicacion');

        return $ubicaciones
            ->groupBy(fn ($ubicacion) => $ubicacion->hotel ?? 'Generales (cualquier hotel)')
            ->map(fn ($grupo) => $grupo->map(fn ($ubicacion) => [
                'id' => (string) $ubicacion->id_ubicacion,
                'nombre' => (string) $ubicacion->nombre,
                'compartida' => $otros
                    ->get($ubicacion->id_ubicacion, collect())
                    ->pluck('nombre')
                    ->all(),
            ])->all())
            ->all();
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

    public function updated(): void
    {
        $this->message = '';
    }

    public function save(): void
    {
        $this->message = '';

        $this->nombre = trim($this->nombre);
        $this->descripcion = trim($this->descripcion);

        $validated = $this->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique('departamentos', 'nombre')
                        ->whereNull('id_departamento_principal')
                        ->ignore(
                            $this->departamentoId,
                            'id_departamento'
                        ),
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                ],

                'activo' => [
                    'boolean',
                ],

                'atiendeTodos' => [
                    'boolean',
                ],

                'hotelIds' => [
                    'array',
                ],

                'hotelIds.*' => [
                    'integer',
                    'distinct',
                    Rule::exists('unidades_organizativas', 'id_unidad'),
                ],

                'ubicacionIds' => [
                    'array',
                ],

                'ubicacionIds.*' => [
                    'integer',
                    'distinct',
                    Rule::exists('ubicaciones', 'id_ubicacion'),
                ],
            ],
            [
                'nombre.required' =>
                    'Escribe el nombre del departamento.',

                'nombre.max' =>
                    'El nombre no puede superar los 100 caracteres.',

                'nombre.unique' =>
                    'Ya existe otro departamento con este nombre.',

                'hotelIds.array' =>
                    'La selección de hoteles no es válida.',

                'hotelIds.*.integer' =>
                    'Selecciona hoteles válidos.',

                'hotelIds.*.distinct' =>
                    'Hay hoteles repetidos en la selección.',

                'hotelIds.*.exists' =>
                    'Uno de los hoteles seleccionados ya no existe.',

                'ubicacionIds.*.exists' =>
                    'Una de las ubicaciones seleccionadas ya no existe.',
            ]
        );

        /*
         * Cuando atiende a todos, la bandera define la cobertura.
         * No hace falta crear vínculos para cada hotel.
         */
        $hotelIds = $validated['atiendeTodos']
            ? []
            : array_map('intval', $validated['hotelIds']);

        $ubicacionIds = array_values(array_unique(
            array_map('intval', $validated['ubicacionIds'] ?? [])
        ));

        $gestionaUbicaciones = $this->gestionaUbicaciones();

        DB::transaction(function () use ($validated, $hotelIds, $ubicacionIds, $gestionaUbicaciones): void {
            $departamento = Departamento::query()
                ->principales()
                ->where('id_departamento', $this->departamentoId)
                ->lockForUpdate()
                ->firstOrFail();

            $departamento->update([
                'nombre' => $validated['nombre'],

                'descripcion' => $validated['descripcion'] !== ''
                    ? $validated['descripcion']
                    : null,

                'activo' => (bool) $validated['activo'],

                'atiende_todas_unidades' =>
                    (bool) $validated['atiendeTodos'],
            ]);

            /*
             * Agrega los hoteles seleccionados y retira los desmarcados.
             * Una selección vacía deja al departamento sin hoteles.
             */
            $departamento->hoteles()->sync($hotelIds);

            /*
             * Ubicaciones del departamento: se reemplaza la selección.
             */
            if ($gestionaUbicaciones) {
                DB::table('departamento_ubicacion')
                    ->where('id_departamento', $this->departamentoId)
                    ->delete();

                if ($ubicacionIds !== []) {
                    DB::table('departamento_ubicacion')->insert(
                        array_map(fn (int $id): array => [
                            'id_departamento' => $this->departamentoId,
                            'id_ubicacion' => $id,
                        ], $ubicacionIds)
                    );
                }
            }
        });

        app(SystemCacheService::class)
            ->refreshModule('catalogos');

        $this->hotelIds = array_map('strval', $hotelIds);
        $this->ubicacionIds = array_map('strval', $ubicacionIds);

        // Confirmación con el aviso global (components/ui/toast-region).
        $this->dispatch('toast', type: 'success', message: 'Departamento, hoteles y ubicaciones actualizados correctamente.');
    }
};
?>

<div>
    @php
        $hoteles = $this->hoteles();
        $ubicacionesPorHotel = $this->ubicacionesPorHotel();
    @endphp

    @if ($message !== '')
        <div
            role="status"
            class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-500"
        >
            {{ $message }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        {{-- DATOS DEL DEPARTAMENTO --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                Información del departamento
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label
                        for="department-name"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Nombre *
                    </label>

                    <input
                        id="department-name"
                        type="text"
                        wire:model="nombre"
                        maxlength="100"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    >

                    @error('nombre')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center md:pt-5">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                        <input
                            type="checkbox"
                            wire:model="activo"
                            class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                        >

                        Departamento activo
                    </label>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label
                        for="department-description"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Descripción
                    </label>

                    <textarea
                        id="department-description"
                        wire:model="descripcion"
                        rows="3"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    ></textarea>

                    @error('descripcion')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- COBERTURA --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['cobertura'] }}
            </h2>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Marca los hoteles para asignarlos o desmárcalos para retirar la relación.
            </p>

            <label class="mt-5 flex items-start gap-3 rounded-lg border border-[var(--theme-border)] p-4">
                <input
                    type="checkbox"
                    wire:model.live="atiendeTodos"
                    class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                >

                <span>
                    <span class="block text-sm font-semibold text-[var(--theme-text-strong)]">
                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['atiende_todas_unidades'] }}
                    </span>

                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                        Incluye automáticamente los hoteles que se creen después.
                    </span>
                </span>
            </label>

            @if ($atiendeTodos)
                <div class="mt-4 rounded-lg bg-[var(--theme-primary-soft)] p-4 text-sm text-[var(--theme-primary)]">
                    Este departamento atenderá todos los hoteles.
                    Para elegir hoteles específicos, desmarca la opción anterior.
                </div>
            @else
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @forelse ($hoteles as $hotel)
                        <label
                            wire:key="department-hotel-{{ $hotel->id_unidad }}"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-[var(--theme-border)] p-4 hover:border-[var(--theme-primary)]"
                        >
                            <input
                                type="checkbox"
                                wire:model="hotelIds"
                                value="{{ $hotel->id_unidad }}"
                                class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                            >

                            <span class="text-sm text-[var(--theme-text)]">
                                {{ $hotel->nombre }}

                                @unless ($hotel->activo)
                                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                                        Hotel inactivo
                                    </span>
                                @endunless
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-[var(--theme-text-muted)] md:col-span-2">
                            Todavía no hay hoteles registrados.
                        </p>
                    @endforelse
                </div>

                <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                    Si no seleccionas ninguno, el departamento quedará sin hoteles asignados.
                </p>
            @endif

            @error('hotelIds')
                <p class="mt-3 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

            @error('hotelIds.*')
                <p class="mt-3 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

            <p class="mt-5 text-xs text-[var(--theme-text-muted)]">
                Estos cambios actualizan la disponibilidad del departamento.
                Los equipos y las asignaciones existentes conservan sus referencias.
            </p>
        </section>

        {{-- UBICACIONES --}}
        @if ($this->gestionaUbicaciones())
            <section
                class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
                x-data="{ buscar: '' }"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                            Ubicaciones
                        </h2>

                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                            Marca dónde trabaja este departamento. Al asignar un equipo solo se ofrecerán estas ubicaciones.
                            Una ubicación que no esté marcada en ningún departamento queda disponible para todos los de su hotel.
                        </p>
                    </div>

                    <div class="w-full sm:w-64 shrink-0">
                        <input
                            type="search"
                            x-model="buscar"
                            placeholder="Buscar ubicación..."
                            autocomplete="off"
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                        >
                    </div>
                </div>

                <p class="mt-4 text-xs text-[var(--theme-text-muted)]">
                    Seleccionadas:
                    <span class="font-medium text-[var(--theme-text)]" x-text="($wire.ubicacionIds ?? []).length">{{ count($ubicacionIds) }}</span>
                </p>

                @forelse ($ubicacionesPorHotel as $hotelNombre => $ubicaciones)
                    <div
                        wire:key="department-locations-{{ md5($hotelNombre) }}"
                        class="mt-5"
                        x-show="! buscar || $el.textContent.toLowerCase().includes(buscar.toLowerCase())"
                    >
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-[var(--theme-text-muted)]">
                            {{ $hotelNombre }}
                        </h3>

                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($ubicaciones as $ubicacion)
                                <label
                                    wire:key="department-location-{{ $ubicacion['id'] }}"
                                    x-show="! buscar || @js(mb_strtolower($ubicacion['nombre'] . ' ' . $hotelNombre)).includes(buscar.toLowerCase())"
                                    class="flex cursor-pointer items-start gap-3 rounded-lg border border-[var(--theme-border)] px-3 py-2.5 hover:border-[var(--theme-primary)]"
                                >
                                    <input
                                        type="checkbox"
                                        wire:model="ubicacionIds"
                                        value="{{ $ubicacion['id'] }}"
                                        class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                                    >

                                    <span class="min-w-0 text-sm text-[var(--theme-text)]">
                                        {{ $ubicacion['nombre'] }}

                                        @if ($ubicacion['compartida'] !== [])
                                            <span class="mt-0.5 block truncate text-xs text-[var(--theme-text-muted)]" title="{{ implode(', ', $ubicacion['compartida']) }}">
                                                También: {{ implode(', ', $ubicacion['compartida']) }}
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="mt-4 text-sm text-[var(--theme-text-muted)]">
                        Todavía no hay ubicaciones registradas.
                    </p>
                @endforelse

                @error('ubicacionIds.*')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror
            </section>
        @endif

        {{-- ACCIONES --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.index', ['section' => 'departamentos']) }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <x-ui.button
                type="submit"
                target="save"
                size="lg"
                loading-text="Guardando…"
                class="w-full sm:w-auto"
            >
                Guardar cambios
            </x-ui.button>
        </div>
    </form>
</div>