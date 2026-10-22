<?php

use App\Models\Hotel;
use App\Services\SystemCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Alta / edición de una ubicación (página propia)
|--------------------------------------------------------------------------
|
| ubicacionId = 0 crea una nueva (catalogos.ubicaciones.create).
| Mismo diseño que el formulario de departamentos.
|
*/

new class extends Component
{
    #[Locked]
    public int $ubicacionId = 0;

    public string $nombre = '';
    public string $descripcion = '';
    public string $hotel = '';
    public string $tipo = '';
    public bool $activo = true;

    /*
     * Departamentos que trabajan en la ubicación (departamento_ubicacion).
     * Ninguno = todos los departamentos de su hotel.
     */
    public array $departamentoIds = [];

    public function mount(int $ubicacionId = 0): void
    {
        // Solo Admin modifica la organización (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        if ($ubicacionId === 0) {
            return;
        }

        $ubicacion = DB::table('ubicaciones')->where('id_ubicacion', $ubicacionId)->first();

        abort_unless($ubicacion, 404);

        $this->ubicacionId = (int) $ubicacion->id_ubicacion;
        $this->nombre = (string) $ubicacion->nombre;
        $this->descripcion = (string) ($ubicacion->descripcion ?? '');
        $this->hotel = $ubicacion->id_unidad !== null ? (string) $ubicacion->id_unidad : '';
        $this->tipo = ($ubicacion->id_tipo_ubicacion ?? null) !== null ? (string) $ubicacion->id_tipo_ubicacion : '';
        $this->activo = (bool) $ubicacion->activo;

        $this->departamentoIds = DB::table('departamento_ubicacion')
            ->where('id_ubicacion', $this->ubicacionId)
            ->pluck('id_departamento')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function hoteles(): array
    {
        return Hotel::query()
            ->orderBy('nombre')
            ->get(['id_unidad', 'nombre', 'activo'])
            ->map(fn ($hotel) => [
                'value' => (string) $hotel->id_unidad,
                'label' => $hotel->nombre.($hotel->activo ? '' : ' (inactivo)'),
            ])
            ->all();
    }

    /** Tipos activos (y el actual aunque esté inactivo). */
    public function tipos(): array
    {
        return DB::table('tipos_ubicacion')
            ->where(fn ($query) => $query->where('activo', true)
                ->when(ctype_digit($this->tipo), fn ($q) => $q->orWhere('id_tipo_ubicacion', (int) $this->tipo)))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id_tipo_ubicacion', 'nombre', 'activo'])
            ->map(fn ($tipo) => [
                'value' => (string) $tipo->id_tipo_ubicacion,
                'label' => $tipo->nombre.($tipo->activo ? '' : ' (inactivo)'),
            ])
            ->all();
    }

    /** Departamentos activos con los hoteles que atienden. */
    public function departamentos(): array
    {
        $vinculos = DB::table('departamento_unidad')
            ->get(['id_departamento', 'id_unidad'])
            ->groupBy('id_departamento');

        return DB::table('departamentos')
            ->whereNull('id_departamento_principal')
            ->where(fn ($query) => $query->where('activo', true)
                ->orWhereIn('id_departamento', array_map('intval', $this->departamentoIds)))
            ->orderBy('nombre')
            ->get(['id_departamento', 'nombre', 'atiende_todas_unidades'])
            ->map(fn ($departamento) => [
                'id' => (string) $departamento->id_departamento,
                'nombre' => (string) $departamento->nombre,
                'todos' => (bool) $departamento->atiende_todas_unidades,
                'hoteles' => $vinculos->get($departamento->id_departamento, collect())
                    ->pluck('id_unidad')
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    public function save(): void
    {
        $this->nombre = trim($this->nombre);
        $this->descripcion = trim($this->descripcion);

        $hotel = ctype_digit($this->hotel) ? (int) $this->hotel : null;

        $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                // Mismo nombre en el mismo hotel (sin distinguir mayúsculas).
                function (string $atributo, mixed $valor, \Closure $fallar) use ($hotel) {
                    $repetida = DB::table('ubicaciones')
                        ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower((string) $valor)])
                        ->when($hotel === null, fn ($query) => $query->whereNull('id_unidad'), fn ($query) => $query->where('id_unidad', $hotel))
                        ->when($this->ubicacionId > 0, fn ($query) => $query->where('id_ubicacion', '<>', $this->ubicacionId))
                        ->exists();

                    if ($repetida) {
                        $fallar('Ya existe una ubicación con ese nombre en ese hotel.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'hotel' => ['nullable', Rule::exists('unidades_organizativas', 'id_unidad')],
            'tipo' => ['nullable', Rule::exists('tipos_ubicacion', 'id_tipo_ubicacion')],
            'activo' => ['boolean'],
            'departamentoIds' => ['array'],
            'departamentoIds.*' => ['integer', 'distinct', Rule::exists('departamentos', 'id_departamento')],
        ], [
            'nombre.required' => 'Escribe el nombre de la ubicación.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'hotel.exists' => 'El hotel seleccionado ya no existe.',
            'tipo.exists' => 'El tipo seleccionado ya no existe.',
            'departamentoIds.*.exists' => 'Uno de los departamentos seleccionados ya no existe.',
        ]);

        // Solo departamentos que atienden el hotel elegido.
        $validos = collect($this->departamentos())
            ->filter(fn ($d) => $hotel === null || $d['todos'] || in_array((string) $hotel, $d['hoteles'], true))
            ->pluck('id')
            ->all();

        $departamentos = array_values(array_unique(array_filter(
            array_map('strval', $this->departamentoIds),
            fn ($id) => in_array($id, $validos, true)
        )));

        $unidad = $hotel !== null
            ? DB::table('unidades_organizativas')->where('id_unidad', $hotel)->first(['id_sede', 'id_area_origen'])
            : null;

        $fila = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'id_unidad' => $hotel,
            'id_tipo_ubicacion' => ctype_digit($this->tipo) ? (int) $this->tipo : null,
            'activo' => $this->activo,
            // Columnas anteriores: se llenan desde el hotel por compatibilidad.
            'id_sede' => $unidad->id_sede ?? null,
            'id_area' => $unidad->id_area_origen ?? null,
        ];

        $esNueva = $this->ubicacionId === 0;

        DB::transaction(function () use ($fila, $departamentos) {
            if ($this->ubicacionId === 0) {
                $this->ubicacionId = (int) DB::table('ubicaciones')->insertGetId($fila, 'id_ubicacion');
            } else {
                DB::table('ubicaciones')->where('id_ubicacion', $this->ubicacionId)->update($fila);
            }

            DB::table('departamento_ubicacion')->where('id_ubicacion', $this->ubicacionId)->delete();

            if ($departamentos !== []) {
                DB::table('departamento_ubicacion')->insert(array_map(
                    fn ($id) => ['id_departamento' => (int) $id, 'id_ubicacion' => $this->ubicacionId],
                    $departamentos
                ));
            }
        });

        $cache = app(SystemCacheService::class);
        $cache->refreshModule('catalogos');
        $cache->refreshModule('asignaciones');

        session()->flash('toast', [
            'type' => 'success',
            'message' => $esNueva ? 'Ubicación «'.$this->nombre.'» agregada.' : 'Ubicación actualizada correctamente.',
        ]);

        $this->redirectRoute('catalogos.index', ['section' => 'ubicaciones'], navigate: true);
    }
};
?>

<div>
    @php
        $hoteles = $this->hoteles();
        $tipos = $this->tipos();
        $departamentos = $this->departamentos();
        $unidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'];
        $unidadMinuscula = mb_strtolower($unidad);
        $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';
    @endphp

    <form
        wire:submit="save"
        class="space-y-5"
        x-data="{ hotel: $wire.entangle('hotel'), tipo: $wire.entangle('tipo') }"
    >
        {{-- DATOS DE LA UBICACIÓN --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Información de la ubicación
            </h2>

            <p class="mb-5 mt-1 text-xs text-[var(--theme-text-muted)]">
                Lugar del {{ $unidadMinuscula }} donde hay equipos, p. ej. Cocina caliente, Cuarto de comunicaciones o Kids Club.
            </p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="ubicacion-nombre" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                        Nombre *
                    </label>

                    <input
                        id="ubicacion-nombre"
                        type="text"
                        wire:model="nombre"
                        maxlength="150"
                        placeholder="Ej. Cocina caliente"
                        class="{{ $campo }}"
                    >

                    @error('nombre')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-searchable-select
                        x-model="tipo"
                        :options="$tipos"
                        label="Tipo de ubicación"
                        placeholder="Sin tipo"
                        clear-label="Sin tipo"
                        :empty-as-null="false"
                    />

                    @error('tipo')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-searchable-select
                        x-model="hotel"
                        :options="$hoteles"
                        :label="$unidad"
                        :placeholder="'Cualquier '.$unidadMinuscula"
                        :clear-label="'Cualquier '.$unidadMinuscula"
                        :empty-as-null="false"
                    />

                    @error('hotel')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label for="ubicacion-descripcion" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                        Descripción
                    </label>

                    <textarea
                        id="ubicacion-descripcion"
                        wire:model="descripcion"
                        rows="3"
                        maxlength="500"
                        placeholder="Referencia para encontrarla: piso, edificio…"
                        class="{{ $campo }}"
                    ></textarea>

                    @error('descripcion')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                        <input
                            type="checkbox"
                            wire:model="activo"
                            class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                        >

                        Ubicación activa
                    </label>

                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        Solo las activas aparecen al asignar equipos.
                    </p>
                </div>
            </div>
        </section>

        {{-- DEPARTAMENTOS --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Departamentos que la usan
            </h2>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Sin ninguno marcado, la ubicación está disponible para todos los departamentos del {{ $unidadMinuscula }}.
                Solo se muestran los departamentos que atienden el {{ $unidadMinuscula }} elegido.
            </p>

            <div class="mt-5 grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($departamentos as $departamento)
                    <label
                        wire:key="ubicacion-departamento-{{ $departamento['id'] }}"
                        x-show="hotel === '' || hotel === null || @js($departamento['todos']) || @js($departamento['hoteles']).includes(String(hotel))"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border border-[var(--theme-border)] px-3 py-2.5 hover:border-[var(--theme-primary)]"
                    >
                        <input
                            type="checkbox"
                            wire:model="departamentoIds"
                            value="{{ $departamento['id'] }}"
                            class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                        >

                        <span class="min-w-0 text-sm text-[var(--theme-text)]">
                            {{ $departamento['nombre'] }}
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-[var(--theme-text-muted)]">
                        Todavía no hay departamentos activos.
                    </p>
                @endforelse
            </div>

            @error('departamentoIds.*')
                <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
            @enderror
        </section>

        {{-- ACCIONES --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.index', ['section' => 'ubicaciones']) }}"
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
                {{ $ubicacionId === 0 ? 'Agregar ubicación' : 'Guardar cambios' }}
            </x-ui.button>
        </div>
    </form>
</div>
