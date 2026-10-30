<?php

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Administración › Proveedores › Nuevo / Editar
|--------------------------------------------------------------------------
|
| proveedorId = 0 crea uno nuevo (catalogos.proveedores.create).
| Columnas de la tabla proveedores: nombre, contacto, telefono, correo,
| activo.
|
*/

new class extends Component
{
    #[Locked]
    public int $proveedorId = 0;

    public string $nombre = '';
    public string $contacto = '';
    public string $telefono = '';
    public string $correo = '';
    public bool $activo = true;

    /** Equipos con este proveedor (aviso al desactivar). */
    #[Locked]
    public int $equiposConProveedor = 0;

    public function mount(int $proveedorId = 0): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        if ($proveedorId === 0) {
            return;
        }

        $proveedor = DB::table('proveedores')->where('id_proveedor', $proveedorId)->first();

        abort_unless($proveedor, 404);

        $this->proveedorId = (int) $proveedor->id_proveedor;
        $this->nombre = (string) $proveedor->nombre;
        $this->contacto = (string) ($proveedor->contacto ?? '');
        $this->telefono = (string) ($proveedor->telefono ?? '');
        $this->correo = (string) ($proveedor->correo ?? '');
        $this->activo = (bool) $proveedor->activo;

        $this->equiposConProveedor = (int) DB::table('equipos')->where('id_proveedor', $this->proveedorId)->count();
    }

    public function save(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        foreach (['nombre', 'contacto', 'telefono', 'correo'] as $campo) {
            $this->{$campo} = trim($this->{$campo});
        }

        $this->correo = mb_strtolower($this->correo);

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'contacto' => ['nullable', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s.]+(\s*(ext\.?|x)\s*[0-9]+)?$/i'],
            'correo' => ['nullable', 'email', 'max:150'],
            'activo' => ['boolean'],
        ], [
            'nombre.required' => 'Escribe el nombre del proveedor.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'contacto.max' => 'El nombre del contacto no puede superar los 150 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 30 caracteres.',
            'telefono.regex' => 'Escribe el teléfono solo con números, espacios, guiones o paréntesis. Ej. 999 123 4567 ext. 12.',
            'correo.email' => 'Escribe un correo válido, por ejemplo ventas@empresa.com.',
            'correo.max' => 'El correo no puede superar los 150 caracteres.',
        ]);

        $duplicado = DB::table('proveedores')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($validated['nombre'])])
            ->where('id_proveedor', '<>', $this->proveedorId)
            ->exists();

        if ($duplicado) {
            $this->addError('nombre', 'Ya existe un proveedor con este nombre.');

            return;
        }

        $datos = [
            'nombre' => $validated['nombre'],
            'contacto' => $validated['contacto'] ?: null,
            'telefono' => $validated['telefono'] ?: null,
            'correo' => $validated['correo'] ?: null,
            'activo' => (bool) $validated['activo'],
        ];

        $esNuevo = $this->proveedorId === 0;

        if ($esNuevo) {
            $this->proveedorId = (int) DB::table('proveedores')->insertGetId($datos, 'id_proveedor');
        } else {
            DB::table('proveedores')->where('id_proveedor', $this->proveedorId)->update($datos);
        }

        try {
            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => auth()->id(),
                'accion' => $esNuevo ? 'PROVEEDOR_CREADO' : 'PROVEEDOR_ACTUALIZADO',
                'modulo' => 'CATALOGOS',
                'descripcion' => ($esNuevo ? 'Se registró el proveedor ' : 'Se actualizó el proveedor ').$datos['nombre'].'.',
                'fecha_hora' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        app(\App\Services\SystemCacheService::class)->refreshModule('catalogos');

        // Notificación (campana y push). No interrumpe el guardado.
        try {
            $notificaciones = app(\App\Services\NotificacionService::class);

            $notificaciones->notificar(
                'equipos',
                $esNuevo ? 'Proveedor agregado' : 'Proveedor actualizado',
                $notificaciones->nombreActor().($esNuevo ? ' agregó el proveedor «' : ' actualizó el proveedor «').$datos['nombre'].'».',
                route('catalogos.index', ['section' => 'proveedores'], false)
            );

            $this->dispatch('notificaciones-actualizadas');
        } catch (\Throwable $e) {
            report($e);
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => $esNuevo
                ? 'Proveedor «'.$datos['nombre'].'» agregado.'
                : 'Proveedor «'.$datos['nombre'].'» actualizado.',
        ]);

        $this->redirectRoute('catalogos.index', ['section' => 'proveedores'], navigate: true);
    }
};
?>

<div>
    @php
        $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]';
        $etiqueta = 'mb-1.5 block text-xs text-[var(--theme-text-muted)]';
        $seccion = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    @endphp

    <form data-avisar-cambios wire:submit="save" class="space-y-5">

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] px-4 py-3 text-sm text-[var(--theme-danger)]">
                Revisa los campos marcados antes de guardar.
            </div>
        @endif

        <section class="{{ $seccion }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Información del proveedor</h2>
            <p class="mt-1 mb-5 text-xs text-[var(--theme-text-muted)]">Se elige al dar de alta un equipo, en los mantenimientos en garantía y como proveedor sugerido de marcas y modelos.</p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="proveedor-nombre" class="{{ $etiqueta }}">Nombre del proveedor *</label>
                    <input id="proveedor-nombre" type="text" wire:model="nombre" maxlength="150" placeholder="Ej. CompuMérida S.A. de C.V." class="{{ $campo }}">
                    @error('nombre') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="proveedor-contacto" class="{{ $etiqueta }}">Persona de contacto</label>
                    <input id="proveedor-contacto" type="text" wire:model="contacto" maxlength="150" placeholder="Ej. Ana Pérez" autocomplete="off" class="{{ $campo }}">
                    @error('contacto') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="proveedor-telefono" class="{{ $etiqueta }}">Teléfono</label>
                    <input id="proveedor-telefono" type="tel" inputmode="tel" wire:model="telefono" maxlength="30" placeholder="Ej. 999 123 4567 ext. 12" autocomplete="off" class="{{ $campo }}">
                    @error('telefono') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="proveedor-correo" class="{{ $etiqueta }}">Correo</label>
                    <input id="proveedor-correo" type="email" inputmode="email" wire:model="correo" maxlength="150" placeholder="Ej. ventas@empresa.com" autocomplete="off" class="{{ $campo }}">
                    @error('correo') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                        <input type="checkbox" wire:model="activo" class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                        Proveedor activo
                    </label>
                </div>
            </div>

            @if ($proveedorId !== 0 && $equiposConProveedor > 0)
                <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                    Tiene {{ $equiposConProveedor }} {{ $equiposConProveedor === 1 ? 'equipo' : 'equipos' }}.
                    Si lo desactivas, ya no se ofrecerá en equipos nuevos, pero los que lo tienen lo conservan.
                </p>
            @endif
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.index', ['section' => 'proveedores']) }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <x-ui.button type="submit" target="save" size="lg" loading-text="Guardando…" class="w-full sm:w-auto">
                {{ $proveedorId === 0 ? 'Agregar proveedor' : 'Guardar cambios' }}
            </x-ui.button>
        </div>
    </form>
</div>
