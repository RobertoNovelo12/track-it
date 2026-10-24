<?php

use App\Services\ProgramasService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Administración › Programas › Nuevo / Editar
|--------------------------------------------------------------------------
|
| programaId = 0 crea uno nuevo (catalogos.programas.create).
| El peso se captura en MB o GB y se guarda en MB (peso_mb).
|
*/

new class extends Component
{
    #[Locked]
    public int $programaId = 0;

    public string $nombre = '';
    public string $empresa = '';
    public string $version = '';
    public ?string $plataforma = null;
    public ?string $categoria = null;
    public ?string $licencia = 'gratuita';
    public bool $requiereClave = false;
    public string $enlaceDescarga = '';
    public string $peso = '';
    public ?string $unidadPeso = 'MB';
    public string $descripcion = '';
    public bool $activo = true;

    /** Equipos que ya tienen el programa (aviso al desactivar). */
    #[Locked]
    public int $equiposConPrograma = 0;

    public function mount(int $programaId = 0): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        abort_unless(app(ProgramasService::class)->disponible(), 404);

        if ($programaId === 0) {
            return;
        }

        $programa = DB::table('programas')->where('id_programa', $programaId)->first();

        abort_unless($programa, 404);

        $this->programaId = (int) $programa->id_programa;
        $this->nombre = (string) $programa->nombre;
        $this->empresa = (string) ($programa->empresa ?? '');
        $this->version = (string) ($programa->version ?? '');
        $this->plataforma = $programa->plataforma ?: null;
        $this->categoria = $programa->categoria ?: null;
        $this->licencia = $programa->licencia ?: 'gratuita';
        $this->requiereClave = (bool) $programa->requiere_clave;
        $this->enlaceDescarga = (string) ($programa->enlace_descarga ?? '');
        $this->descripcion = (string) ($programa->descripcion ?? '');
        $this->activo = (bool) $programa->activo;

        // Peso: se muestra en GB si llega a 1 GB.
        if ($programa->peso_mb !== null) {
            $mb = (float) $programa->peso_mb;

            if ($mb >= 1024) {
                $this->peso = rtrim(rtrim(number_format($mb / 1024, 2, '.', ''), '0'), '.');
                $this->unidadPeso = 'GB';
            } else {
                $this->peso = rtrim(rtrim(number_format($mb, 2, '.', ''), '0'), '.');
            }
        }

        $this->equiposConPrograma = (int) DB::table('equipo_programa')->where('id_programa', $this->programaId)->count();
    }

    public function save(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        foreach (['nombre', 'empresa', 'version', 'enlaceDescarga', 'descripcion'] as $campo) {
            $this->{$campo} = trim($this->{$campo});
        }

        $this->peso = str_replace(',', '.', trim($this->peso));

        // Enlace sin protocolo («www.…»): se completa con https://.
        if ($this->enlaceDescarga !== '' && ! preg_match('#^https?://#i', $this->enlaceDescarga)) {
            $this->enlaceDescarga = 'https://'.$this->enlaceDescarga;
        }

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'version' => ['nullable', 'string', 'max:50'],
            'plataforma' => ['nullable', 'in:'.implode(',', ProgramasService::PLATAFORMAS)],
            'categoria' => ['nullable', 'in:'.implode(',', ProgramasService::CATEGORIAS)],
            'licencia' => ['required', 'in:'.implode(',', array_keys(ProgramasService::LICENCIAS))],
            'requiereClave' => ['boolean'],
            'enlaceDescarga' => ['nullable', 'url:http,https', 'max:500'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'unidadPeso' => ['required', 'in:MB,GB'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'activo' => ['boolean'],
        ], [
            'nombre.required' => 'Escribe el nombre del programa.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'empresa.max' => 'La empresa no puede superar los 150 caracteres.',
            'version.max' => 'La versión no puede superar los 50 caracteres.',
            'plataforma.in' => 'Elige una plataforma de la lista.',
            'categoria.in' => 'Elige una categoría de la lista.',
            'licencia.required' => 'Elige el tipo de licencia.',
            'licencia.in' => 'Elige el tipo de licencia.',
            'enlaceDescarga.url' => 'Escribe un enlace válido, por ejemplo https://www.empresa.com/descarga.',
            'enlaceDescarga.max' => 'El enlace no puede superar los 500 caracteres.',
            'peso.numeric' => 'Escribe el peso solo con números, por ejemplo 350 o 1.5.',
            'peso.min' => 'El peso no puede ser negativo.',
            'descripcion.max' => 'Las notas no pueden superar los 2000 caracteres.',
        ]);

        // Mismo nombre y misma plataforma = mismo programa.
        $duplicado = DB::table('programas')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($validated['nombre'])])
            ->where(fn ($query) => $validated['plataforma']
                ? $query->where('plataforma', $validated['plataforma'])
                : $query->whereNull('plataforma'))
            ->where('id_programa', '<>', $this->programaId)
            ->exists();

        if ($duplicado) {
            $this->addError('nombre', 'Ya existe un programa con este nombre'.($validated['plataforma'] ? ' para '.$validated['plataforma'] : '').'.');

            return;
        }

        $pesoMb = $validated['peso'] === '' || $validated['peso'] === null
            ? null
            : round((float) $validated['peso'] * ($validated['unidadPeso'] === 'GB' ? 1024 : 1), 2);

        $datos = [
            'nombre' => $validated['nombre'],
            'empresa' => $validated['empresa'] ?: null,
            'version' => $validated['version'] ? ltrim($validated['version'], 'vV ') : null,
            'plataforma' => $validated['plataforma'] ?: null,
            'categoria' => $validated['categoria'] ?: null,
            'licencia' => $validated['licencia'],
            'requiere_clave' => (bool) $validated['requiereClave'],
            'enlace_descarga' => $validated['enlaceDescarga'] ?: null,
            'peso_mb' => $pesoMb,
            'descripcion' => $validated['descripcion'] ?: null,
            'activo' => (bool) $validated['activo'],
        ];

        $esNuevo = $this->programaId === 0;

        if ($esNuevo) {
            $this->programaId = (int) DB::table('programas')->insertGetId($datos + ['fecha_registro' => now()], 'id_programa');
        } else {
            DB::table('programas')->where('id_programa', $this->programaId)->update($datos + ['fecha_actualizacion' => now()]);
        }

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => $esNuevo ? 'PROGRAMA_CREADO' : 'PROGRAMA_ACTUALIZADO',
            'modulo' => 'CATALOGOS',
            'descripcion' => ($esNuevo ? 'Se registró el programa ' : 'Se actualizó el programa ').$datos['nombre'].'.',
            'fecha_hora' => now(),
        ]);

        app(ProgramasService::class)->refrescar();

        session()->flash('toast', [
            'type' => 'success',
            'message' => $esNuevo
                ? 'Programa «'.$datos['nombre'].'» agregado.'
                : 'Programa «'.$datos['nombre'].'» actualizado.',
        ]);

        $this->redirectRoute('catalogos.index', ['section' => 'programas'], navigate: true);
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

        {{-- DATOS DEL PROGRAMA --}}
        <section class="{{ $seccion }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Información del programa</h2>
            <p class="mt-1 mb-5 text-xs text-[var(--theme-text-muted)]">Los tipos de equipo con «Incluye programas» lo ofrecerán al dar de alta sus equipos.</p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="programa-nombre" class="{{ $etiqueta }}">Nombre de la app *</label>
                    <input id="programa-nombre" type="text" wire:model="nombre" maxlength="150" placeholder="Ej. Microsoft Office" class="{{ $campo }}">
                    @error('nombre') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="programa-empresa" class="{{ $etiqueta }}">Empresa</label>
                    <input id="programa-empresa" type="text" wire:model="empresa" maxlength="150" placeholder="Ej. Microsoft" class="{{ $campo }}">
                    @error('empresa') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="programa-version" class="{{ $etiqueta }}">Versión</label>
                    <input id="programa-version" type="text" wire:model="version" maxlength="50" placeholder="Ej. 2021 o 16.0.1" class="{{ $campo }}">
                    @error('version') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-searchable-select
                        label="Plataforma"
                        wire-model="plataforma"
                        :options="collect(\App\Services\ProgramasService::PLATAFORMAS)->map(fn ($p) => ['value' => $p, 'label' => $p])->all()"
                        placeholder="Buscar plataforma..."
                        clear-label="Sin especificar"
                    />
                    @error('plataforma') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-searchable-select
                        label="Categoría"
                        wire-model="categoria"
                        :options="collect(\App\Services\ProgramasService::CATEGORIAS)->map(fn ($c) => ['value' => $c, 'label' => $c])->all()"
                        placeholder="Buscar categoría..."
                        clear-label="Sin categoría"
                    />
                    @error('categoria') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center md:pt-5">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                        <input type="checkbox" wire:model="activo" class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                        Programa activo
                    </label>
                </div>
            </div>

            @if ($programaId !== 0 && $equiposConPrograma > 0)
                <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                    Instalado en {{ $equiposConPrograma }} {{ $equiposConPrograma === 1 ? 'equipo' : 'equipos' }}.
                    Si lo desactivas, ya no se ofrecerá a equipos nuevos, pero los que lo tienen lo conservan.
                </p>
            @endif
        </section>

        {{-- DESCARGA Y LICENCIA --}}
        <section class="{{ $seccion }}">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">Descarga y licencia</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="md:col-span-2">
                    <label for="programa-enlace" class="{{ $etiqueta }}">Enlace de descarga</label>
                    <input id="programa-enlace" type="url" inputmode="url" wire:model="enlaceDescarga" maxlength="500" placeholder="https://www.empresa.com/descarga" class="{{ $campo }}">
                    @error('enlaceDescarga') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="programa-peso" class="{{ $etiqueta }}">Peso del instalador</label>
                    <div class="flex gap-2">
                        <input id="programa-peso" type="text" inputmode="decimal" wire:model="peso" placeholder="Ej. 350" class="{{ $campo }} min-w-0 flex-1">
                        <div class="w-24 shrink-0">
                            <x-searchable-select
                                wire-model="unidadPeso"
                                :options="[['value' => 'MB', 'label' => 'MB'], ['value' => 'GB', 'label' => 'GB']]"
                                :show-clear="false"
                                placeholder="MB"
                            />
                        </div>
                    </div>
                    @error('peso') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-searchable-select
                        label="Licencia *"
                        wire-model="licencia"
                        :options="collect(\App\Services\ProgramasService::LICENCIAS)->map(fn ($nombre, $clave) => ['value' => $clave, 'label' => $nombre])->values()->all()"
                        :show-clear="false"
                        placeholder="Buscar licencia..."
                    />
                    @error('licencia') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center md:pt-5 xl:col-span-2">
                    <label class="inline-flex items-start gap-2 text-sm text-[var(--theme-text)]">
                        <input type="checkbox" wire:model="requiereClave" class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                        <span>
                            Requiere clave o activación
                            <span class="block text-xs text-[var(--theme-text-muted)]">Por ejemplo, número de serie o cuenta de la empresa.</span>
                        </span>
                    </label>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label for="programa-descripcion" class="{{ $etiqueta }}">Notas de instalación</label>
                    <textarea id="programa-descripcion" wire:model="descripcion" rows="3" maxlength="2000" placeholder="Pasos, configuración o a quién pedir la clave." class="{{ $campo }}"></textarea>
                    @error('descripcion') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ACCIONES --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('catalogos.index', ['section' => 'programas']) }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <x-ui.button type="submit" target="save" size="lg" loading-text="Guardando…" class="w-full sm:w-auto">
                {{ $programaId === 0 ? 'Agregar programa' : 'Guardar cambios' }}
            </x-ui.button>
        </div>
    </form>
</div>
