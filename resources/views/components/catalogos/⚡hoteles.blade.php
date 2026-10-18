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
    use WithPagination;

    #[Locked]
    public bool $paginaEdicion = false;

    #[Locked]
    public ?int $editingId = null;

    public string $search = '';
    public string $estado = 'todos';
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
            $this->message = ucfirst($unidad).' actualizado correctamente.';

            return;
        }

        $this->resetForm();

        $this->search = '';
        $this->estado = 'todos';

        $this->resetPage(pageName: 'hotelesPage');

        $this->message = ucfirst($unidad).' guardado correctamente.';
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
            ->orderBy('nombre')
            ->orderBy('id_unidad')
            ->paginate(15, ['*'], 'hotelesPage');
    }
};
?>

<div class="min-w-0 space-y-5 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">
    @php
        $hoteles = $paginaEdicion ? null : $this->hoteles();
        $etiquetasUnidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
        $unidad = mb_strtolower($etiquetasUnidad['unidad_singular']);
        $unidades = mb_strtolower($etiquetasUnidad['unidad_plural']);
    @endphp

    {{-- Encabezado del listado --}}
    @if (! $paginaEdicion)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-[var(--theme-text-strong)]">
                    {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_plural'] }}
                </h2>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Administra los {{ $unidades }} y sus datos de contacto.
                </p>
            </div>

            <button
                type="button"
                wire:click="createHotel"
                wire:loading.attr="disabled"
                class="rounded-lg bg-[var(--theme-primary)] px-4 py-2.5 text-xs font-semibold text-white disabled:opacity-50"
            >
                + Añadir {{ $unidad }}
            </button>
        </div>
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
            class="space-y-5 rounded-xl border border-[var(--theme-border)] p-4 sm:p-5"
        >
            <div>
                <h3 class="font-semibold text-[var(--theme-text-strong)]">
                    {{ $paginaEdicion ? 'Datos del '.$unidad : 'Nuevo '.$unidad }}
                </h3>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Los campos con * son obligatorios.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label
                        for="hotel-sede"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Sede *
                    </label>

                    <select
                        id="hotel-sede"
                        wire:model="idSede"
                        required
                        class="w-full rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)]"
                    >
                        <option value="">Selecciona una sede</option>

                        @foreach (\App\Models\Sede::orderBy('nombre')->get() as $sede)
                            <option value="{{ $sede->id_sede }}">
                                {{ $sede->nombre }}{{ $sede->activo ? '' : ' (inactiva)' }}
                            </option>
                        @endforeach
                    </select>

                    @error('idSede')
                        <p class="mt-1 text-xs text-red-500">
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
                            class="w-full rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)]"
                        >

                        @error($field)
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach

                <div class="md:col-span-2">
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
                        class="min-h-24 w-full resize-y rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)]"
                    ></textarea>

                    @error('descripcion')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-[var(--theme-text)]">
                <input
                    type="checkbox"
                    wire:model="activo"
                    class="h-4 w-4 accent-[var(--theme-primary)]"
                >

                {{ $etiquetasUnidad['unidad_singular'] }} activo
            </label>

            @error('activo')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            <fieldset class="space-y-3 border-t border-[var(--theme-border)] pt-5">
                <legend class="px-1 text-sm font-semibold text-[var(--theme-text-strong)]">Departamentos</legend>
                <p class="text-xs text-[var(--theme-text-muted)]">
                    Selecciona los departamentos que atienden esta unidad. Los de cobertura global se incluyen automáticamente, también en las nuevas unidades.
                    Los inactivos conservan su asignación, pero no estarán disponibles al registrar equipos.
                </p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
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
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                @error('idDepartamentos.*')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3 border-t border-[var(--theme-border)] pt-4">
                <button
                    type="button"
                    wire:click="closeForm"
                    wire:loading.attr="disabled"
                    class="rounded-lg border border-[var(--theme-border)] px-4 py-2.5 text-xs text-[var(--theme-text)] disabled:opacity-50"
                >
                    {{ $paginaEdicion ? 'Volver a '.$unidades : 'Cancelar' }}
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-[var(--theme-primary)] px-4 py-2.5 text-xs font-semibold text-white disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="saveHotel">
                        {{ $paginaEdicion ? 'Guardar cambios' : 'Guardar '.$unidad }}
                    </span>

                    <span wire:loading wire:target="saveHotel">
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    @endif

    {{-- Filtros y listado: solo en Administración --}}
    @if (! $paginaEdicion)
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="hoteles-search" class="sr-only">
                    Buscar {{ $unidades }}
                </label>

                <input
                    id="hoteles-search"
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Buscar por nombre, código o sede..."
                    class="w-full rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)]"
                >
            </div>

            <div>
                <label for="hoteles-estado" class="sr-only">
                    Estado del {{ $unidad }}
                </label>

                <select
                    id="hoteles-estado"
                    wire:model.live="estado"
                    class="w-full rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] sm:w-44"
                >
                    <option value="todos">Todos los estados</option>
                    <option value="activos">Activos</option>
                    <option value="inactivos">Inactivos</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-[var(--theme-border)]">
            <div class="border-b border-[var(--theme-border)] px-4 py-4 sm:px-5">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    {{ $etiquetasUnidad['unidad_plural'] }} registrados
                </h3>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    {{ $hoteles->total() }} resultados encontrados
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 sm:px-5">
                                {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] }}
                            </th>

                            <th scope="col" class="px-4 py-3">
                                Sede
                            </th>

                            <th scope="col" class="px-4 py-3">
                                Código
                            </th>

                            <th scope="col" class="px-4 py-3">
                                Estado
                            </th>

                            <th scope="col" class="px-4 py-3 text-right sm:px-5">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @forelse ($hoteles as $hotel)
                            <tr wire:key="hotel-row-{{ $hotel->id_unidad }}">
                                <td class="px-4 py-4 font-medium text-[var(--theme-text-strong)] sm:px-5">
                                    {{ $hotel->nombre }}
                                </td>

                                <td class="px-4 py-4 text-[var(--theme-text-muted)]">
                                    {{ $hotel->sede?->nombre ?? 'Sin sede' }}
                                </td>

                                <td class="px-4 py-4 text-[var(--theme-text-muted)]">
                                    {{ $hotel->codigo ?: '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-500/10 text-emerald-500' => $hotel->activo,
                                        'bg-amber-500/10 text-amber-500' => ! $hotel->activo,
                                    ])>
                                        {{ $hotel->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right sm:px-5">
                                    <a
                                        href="{{ route('catalogos.hoteles.edit', ['hotel' => $hotel->id_unidad]) }}"
                                        aria-label="Editar {{ $hotel->nombre }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-[var(--theme-border)] px-3 py-2 text-xs text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                                    >
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-5 py-10 text-center text-sm text-[var(--theme-text-muted)]"
                                >
                                    No se encontraron {{ $unidades }} con estos filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($hoteles->hasPages())
                <div class="border-t border-[var(--theme-border)] px-4 py-4 sm:px-5">
                    {{ $hoteles->links() }}
                </div>
            @endif
        </div>
    @endif
</div>