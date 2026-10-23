<?php

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Alta / edición de un tipo de ubicación (página propia)
|--------------------------------------------------------------------------
|
| tipoId = 0 crea uno nuevo (catalogos.tipos-ubicacion.create).
|
*/

new class extends Component
{
    #[Locked]
    public int $tipoId = 0;

    public string $nombre = '';
    public bool $activo = true;

    public function mount(int $tipoId = 0): void
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        if ($tipoId === 0) {
            return;
        }

        $tipo = DB::table('tipos_ubicacion')->where('id_tipo_ubicacion', $tipoId)->first();

        abort_unless($tipo, 404);

        $this->tipoId = (int) $tipo->id_tipo_ubicacion;
        $this->nombre = (string) $tipo->nombre;
        $this->activo = (bool) $tipo->activo;
    }

    public function save(): void
    {
        $this->nombre = trim($this->nombre);

        $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function (string $atributo, mixed $valor, \Closure $fallar) {
                    $repetido = DB::table('tipos_ubicacion')
                        ->whereRaw('LOWER(nombre) = ?', [mb_strtolower((string) $valor)])
                        ->when($this->tipoId > 0, fn ($query) => $query->where('id_tipo_ubicacion', '<>', $this->tipoId))
                        ->exists();

                    if ($repetido) {
                        $fallar('Ya existe un tipo con ese nombre.');
                    }
                },
            ],
            'activo' => ['boolean'],
        ], [
            'nombre.required' => 'Escribe el nombre del tipo.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $esNuevo = $this->tipoId === 0;

        if ($esNuevo) {
            DB::table('tipos_ubicacion')->insert([
                'nombre' => $this->nombre,
                'orden' => (int) DB::table('tipos_ubicacion')->max('orden') + 10,
                'activo' => $this->activo,
            ]);
        } else {
            DB::table('tipos_ubicacion')
                ->where('id_tipo_ubicacion', $this->tipoId)
                ->update(['nombre' => $this->nombre, 'activo' => $this->activo]);
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => $esNuevo ? 'Tipo «'.$this->nombre.'» agregado.' : 'Tipo de ubicación actualizado.',
        ]);

        $this->redirectRoute('catalogos.tipos-ubicacion.index', navigate: true);
    }
};
?>

<div>
    <form
            data-avisar-cambios wire:submit="save" class="space-y-5">
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Información del tipo
            </h2>

            <p class="mb-5 mt-1 text-xs text-[var(--theme-text-muted)]">
                Clasifica las ubicaciones, p. ej. Cocina, Club o Cuarto de comunicaciones.
            </p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="tipo-ubicacion-nombre" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                        Nombre *
                    </label>

                    <input
                        id="tipo-ubicacion-nombre"
                        type="text"
                        wire:model="nombre"
                        maxlength="100"
                        placeholder="Ej. Spa"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    >

                    @error('nombre')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:pt-5">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                        <input
                            type="checkbox"
                            wire:model="activo"
                            class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                        >

                        Tipo activo
                    </label>

                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        Un tipo inactivo ya no se ofrece en ubicaciones nuevas.
                    </p>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.tipos-ubicacion.index') }}"
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
                {{ $tipoId === 0 ? 'Agregar tipo' : 'Guardar cambios' }}
            </x-ui.button>
        </div>
    </form>
</div>
