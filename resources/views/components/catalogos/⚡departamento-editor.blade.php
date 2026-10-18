<?php

use App\Models\Departamento;
use App\Models\Hotel;
use App\Services\SystemCacheService;
use Illuminate\Support\Facades\DB;
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

    public string $message = '';

    public function mount(int $departamentoId): void
    {
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
            ]
        );

        /*
         * Cuando atiende a todos, la bandera define la cobertura.
         * No hace falta crear vínculos para cada hotel.
         */
        $hotelIds = $validated['atiendeTodos']
            ? []
            : array_map('intval', $validated['hotelIds']);

        DB::transaction(function () use ($validated, $hotelIds): void {
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
        });

        app(SystemCacheService::class)
            ->refreshModule('catalogos');

        $this->hotelIds = array_map('strval', $hotelIds);

        $this->message =
            'Departamento y hoteles asignados actualizados correctamente.';
    }
};
?>

<div>
    @php
        $hoteles = $this->hoteles();
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

        {{-- ACCIONES --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.index', ['section' => 'departamentos']) }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
            >
                <span wire:loading.remove wire:target="save">
                    Guardar cambios
                </span>

                <span wire:loading wire:target="save">
                    Guardando...
                </span>
            </button>
        </div>
    </form>
</div>