<?php

use App\Models\Sede;
use App\Models\UnidadOrganizativa;
use App\Services\OrganizacionService;
use App\Services\PlantillaInstalacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/*
|--------------------------------------------------------------------------
| Administración › Organización
|--------------------------------------------------------------------------
|
| - Identidad: nombre, logotipo, tamaño y apariencia en modo oscuro.
| - Correo: dominios corporativos permitidos en el registro.
| - Registro: obligatoriedad de los campos configurables.
| - Sedes: nombres, estado y hoteles que pertenecen a cada una.
|
*/

new class extends Component
{
    use WithFileUploads;

    /*
     * Vista mientras la sección se carga en Administración.
     */
    public function placeholder(array $params = [])
    {
        return view('livewire.placeholders.administracion-seccion');
    }

    private const PESTANAS = [
        'identidad',
        'correo',
        'registro',
        'sedes',
    ];

    public string $tab = 'identidad';

    public string $message = '';

    /*
     * Identidad
     */
    public string $nombreOrganizacion = '';

    public $logo = null;

    public int $logoEscala = 100;

    public string $logoModoOscuro = 'blanco';

    /*
     * Correo
     */
    public bool $restringirDominio = false;

    public array $dominios = [];

    public string $nuevoDominio = '';

    /*
     * Registro
     */
    public array $camposRegistro = [];

    /*
     * Sedes
     */
    /*
     * La edición de una sede se abre en su propia página
     * (catalogos.sedes.edit) para no cargar la pantalla principal.
     */
    #[Locked]
    public bool $paginaEdicion = false;

    public bool $sedeFormOpen = false;

    #[Locked]
    public ?int $sedeEditingId = null;

    public string $sedeNombre = '';

    public string $sedeCodigo = '';

    public string $sedeDescripcion = '';

    public bool $sedeActivo = true;

    public array $sedeUnidades = [];

    public function mount(?int $sedeId = null, bool $nuevaSede = false): void
    {
        $tab = (string) request()->query('tab', 'identidad');

        $this->tab = in_array($tab, self::PESTANAS, true)
            ? $tab
            : 'identidad';

        $this->message = (string) session('organizacion_mensaje', '');

        $this->cargarConfiguracion();

        if ($sedeId !== null) {
            $this->tab = 'sedes';
            $this->editarSede($sedeId);
            $this->paginaEdicion = true;
        } elseif ($nuevaSede) {
            $this->tab = 'sedes';
            $this->crearSede();
            $this->paginaEdicion = true;
        }
    }

    private function organizacion(): OrganizacionService
    {
        return app(OrganizacionService::class);
    }

    private function cargarConfiguracion(): void
    {
        $config = $this->organizacion()->configuracion();

        $this->nombreOrganizacion = (string) ($config['nombre_organizacion'] ?? '');
        $this->logoEscala = (int) $config['logo_escala'];
        $this->logoModoOscuro = $config['logo_modo_oscuro'];

        $this->restringirDominio = (bool) $config['restringir_dominio_correo'];
        $this->dominios = $config['dominios_correo'];

        $this->camposRegistro = $config['campos_registro'];
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, self::PESTANAS, true)) {
            return;
        }

        $this->tab = $tab;
        $this->message = '';
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Identidad y logotipo
    |--------------------------------------------------------------------------
    */

    public function updatedLogo(): void
    {
        $this->validateOnly('logo', $this->reglasLogo(), $this->mensajesLogo());
    }

    private function reglasLogo(): array
    {
        return [
            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:'.OrganizacionService::LOGO_TAMANO_MAXIMO_KB,
                'dimensions:min_width=40,min_height=20,max_width=4000,max_height=4000',
            ],
        ];
    }

    private function mensajesLogo(): array
    {
        return [
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'Utiliza una imagen PNG, JPG o WEBP.',
            'logo.max' => 'El logotipo no puede superar 1 MB.',
            'logo.dimensions' => 'La imagen debe medir entre 40×20 y 4000×4000 píxeles.',
        ];
    }

    public function guardarIdentidad(): void
    {
        abort_unless(auth()->check(), 403);

        $this->message = '';
        $this->nombreOrganizacion = trim($this->nombreOrganizacion);

        $validated = $this->validate(
            $this->reglasLogo() + [
                'nombreOrganizacion' => ['nullable', 'string', 'max:150'],
                'logoEscala' => [
                    'required',
                    'integer',
                    'between:'.OrganizacionService::ESCALA_MINIMA.','.OrganizacionService::ESCALA_MAXIMA,
                ],
                'logoModoOscuro' => [
                    'required',
                    Rule::in(array_keys(OrganizacionService::MODOS_OSCURO)),
                ],
            ],
            $this->mensajesLogo() + [
                'nombreOrganizacion.max' => 'El nombre no puede superar 150 caracteres.',
                'logoEscala.between' => 'El tamaño debe estar entre '.OrganizacionService::ESCALA_MINIMA.' % y '.OrganizacionService::ESCALA_MAXIMA.' %.',
                'logoModoOscuro.in' => 'Selecciona una apariencia válida.',
            ]
        );

        $cambios = [
            'nombre_organizacion' => $validated['nombreOrganizacion'] !== ''
                ? $validated['nombreOrganizacion']
                : null,
            'logo_escala' => (int) $validated['logoEscala'],
            'logo_modo_oscuro' => $validated['logoModoOscuro'],
        ];

        if ($this->logo) {
            $contenido = file_get_contents($this->logo->getRealPath());
            $info = $contenido !== false ? @getimagesizefromstring($contenido) : false;

            if (
                $info === false
                || ! in_array($info['mime'], OrganizacionService::LOGO_MIMES, true)
            ) {
                throw ValidationException::withMessages([
                    'logo' => 'No se pudo leer la imagen. Utiliza un PNG, JPG o WEBP válido.',
                ]);
            }

            $cambios += [
                'logo_base64' => base64_encode($contenido),
                'logo_mime' => $info['mime'],
                'logo_nombre_original' => Str::limit($this->logo->getClientOriginalName(), 250, ''),
                'logo_version' => substr(sha1($contenido), 0, 20),
            ];
        }

        $this->organizacion()->actualizar($cambios);

        $this->registrarBitacora(
            $this->logo
                ? 'Se actualizó la identidad visual y se reemplazó el logotipo.'
                : 'Se actualizó la identidad visual.'
        );

        // El logotipo del menú se conserva entre navegaciones: recargar.
        $this->recargar('identidad', 'Identidad visual guardada correctamente.');
    }

    public function restaurarLogo(): void
    {
        abort_unless(auth()->check(), 403);

        $this->organizacion()->actualizar([
            'logo_base64' => null,
            'logo_mime' => null,
            'logo_nombre_original' => null,
            'logo_version' => null,
        ]);

        $this->registrarBitacora('Se restauró el logotipo predeterminado.');

        $this->recargar('identidad', 'Se restauró el logotipo predeterminado.');
    }

    public function descartarLogo(): void
    {
        $this->logo = null;
        $this->resetValidation('logo');
    }

    /*
    |--------------------------------------------------------------------------
    | Dominio de correo
    |--------------------------------------------------------------------------
    */

    public function agregarDominio(): void
    {
        $this->resetValidation('nuevoDominio');

        $dominio = OrganizacionService::normalizarDominio($this->nuevoDominio);

        if ($dominio === null) {
            $this->addError('nuevoDominio', 'Escribe un dominio válido, por ejemplo empresa.com.');

            return;
        }

        if (in_array($dominio, $this->dominios, true)) {
            $this->addError('nuevoDominio', 'Ese dominio ya está en la lista.');

            return;
        }

        if (count($this->dominios) >= 20) {
            $this->addError('nuevoDominio', 'Puedes registrar hasta 20 dominios.');

            return;
        }

        $this->dominios[] = $dominio;
        $this->nuevoDominio = '';
    }

    public function quitarDominio(int $indice): void
    {
        unset($this->dominios[$indice]);

        $this->dominios = array_values($this->dominios);
    }

    public function guardarCorreo(): void
    {
        abort_unless(auth()->check(), 403);

        $this->message = '';
        $this->resetValidation();

        $dominios = collect($this->dominios)
            ->map(fn ($dominio) => is_string($dominio) ? OrganizacionService::normalizarDominio($dominio) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($this->restringirDominio && $dominios === []) {
            $this->addError('dominios', 'Agrega al menos un dominio para restringir el registro.');

            return;
        }

        $this->organizacion()->actualizar([
            'restringir_dominio_correo' => $this->restringirDominio,
            'dominios_correo' => $dominios,
        ]);

        $this->dominios = $dominios;

        $this->registrarBitacora(
            $this->restringirDominio
                ? 'Registro restringido a los dominios: '.implode(', ', $dominios).'.'
                : 'Registro sin restricción de dominio de correo.'
        );

        $this->message = 'Configuración de correo guardada correctamente.';
    }

    /*
    |--------------------------------------------------------------------------
    | Datos de registro
    |--------------------------------------------------------------------------
    */

    public function guardarRegistro(): void
    {
        abort_unless(auth()->check(), 403);

        $this->message = '';

        $reglas = [];

        foreach (array_keys(OrganizacionService::CAMPOS_REGISTRO) as $campo) {
            $reglas['camposRegistro.'.$campo] = [
                'required',
                Rule::in(array_keys(OrganizacionService::MODOS_CAMPO)),
            ];
        }

        $validated = $this->validate($reglas, [
            'camposRegistro.*.in' => 'Selecciona una opción válida.',
            'camposRegistro.*.required' => 'Selecciona una opción.',
        ]);

        $campos = array_intersect_key(
            $validated['camposRegistro'],
            OrganizacionService::CAMPOS_REGISTRO
        );

        $this->organizacion()->actualizar([
            'campos_registro' => $campos,
        ]);

        $this->registrarBitacora('Se actualizaron los datos solicitados en el registro de usuarios.');

        $this->message = 'Datos de registro guardados correctamente.';
    }

    /*
    |--------------------------------------------------------------------------
    | Sedes
    |--------------------------------------------------------------------------
    */

    public function crearSede(): void
    {
        $this->resetSede();
        $this->message = '';
        $this->sedeFormOpen = true;
    }

    public function editarSede(int $id): void
    {
        $sede = Sede::findOrFail($id);

        $this->resetSede();
        $this->message = '';

        $this->sedeEditingId = (int) $sede->id_sede;
        $this->sedeNombre = (string) $sede->nombre;
        $this->sedeCodigo = (string) ($sede->codigo ?? '');
        $this->sedeDescripcion = (string) ($sede->descripcion ?? '');
        $this->sedeActivo = (bool) $sede->activo;

        $this->sedeUnidades = UnidadOrganizativa::where('id_sede', $sede->id_sede)
            ->pluck('id_unidad')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->sedeFormOpen = true;
    }

    public function cerrarSede(): void
    {
        if ($this->paginaEdicion) {
            $this->redirectRoute('catalogos.index', [
                'section' => 'organizacion',
                'tab' => 'sedes',
            ]);

            return;
        }

        $this->resetSede();
    }

    private function resetSede(): void
    {
        $this->resetValidation();

        $this->reset([
            'sedeFormOpen',
            'sedeEditingId',
            'sedeNombre',
            'sedeCodigo',
            'sedeDescripcion',
            'sedeActivo',
            'sedeUnidades',
        ]);
    }

    public function guardarSede(): void
    {
        abort_unless(auth()->check(), 403);

        $this->message = '';

        $this->sedeNombre = trim($this->sedeNombre);
        $this->sedeCodigo = trim($this->sedeCodigo);
        $this->sedeDescripcion = trim($this->sedeDescripcion);

        $unidad = mb_strtolower($this->etiquetas()['unidad_singular']);

        $validated = $this->validate([
            'sedeNombre' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) {
                    $existe = Sede::query()
                        ->whereRaw('lower(trim(nombre)) = ?', [mb_strtolower($value)])
                        ->when($this->sedeEditingId, fn ($q) => $q->where('id_sede', '!=', $this->sedeEditingId))
                        ->exists();

                    if ($existe) {
                        $fail('Ya existe una sede con ese nombre.');
                    }
                },
            ],
            'sedeCodigo' => ['nullable', 'string', 'max:50'],
            'sedeDescripcion' => ['nullable', 'string', 'max:2000'],
            'sedeActivo' => ['boolean'],
            'sedeUnidades' => ['array'],
            'sedeUnidades.*' => [
                'integer',
                'distinct',
                Rule::exists('unidades_organizativas', 'id_unidad'),
            ],
        ], [
            'sedeNombre.required' => 'Escribe el nombre de la sede.',
            'sedeNombre.max' => 'El nombre no puede superar 255 caracteres.',
            'sedeCodigo.max' => 'El código no puede superar 50 caracteres.',
            'sedeUnidades.*.exists' => 'Un '.$unidad.' ya no existe. Recarga la página.',
        ]);

        $esNueva = $this->sedeEditingId === null;

        $seleccion = collect($validated['sedeUnidades'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($validated, $seleccion, $unidad) {
            $sede = $this->sedeEditingId
                ? Sede::lockForUpdate()->findOrFail($this->sedeEditingId)
                : new Sede;

            $sede->fill([
                'nombre' => $validated['sedeNombre'],
                'codigo' => $validated['sedeCodigo'] !== '' ? $validated['sedeCodigo'] : null,
                'descripcion' => $validated['sedeDescripcion'] !== '' ? $validated['sedeDescripcion'] : null,
                'activo' => $validated['sedeActivo'],
            ])->save();

            /*
             * Cada hotel pertenece a una sola sede (id_sede es obligatorio).
             * Los hoteles marcados se trasladan a esta sede; los que ya
             * pertenecen a ella no pueden quitarse aquí, solo moverse
             * a otra sede.
             */
            $actuales = UnidadOrganizativa::where('id_sede', $sede->id_sede)
                ->lockForUpdate()
                ->pluck('id_unidad')
                ->map(fn ($id) => (int) $id);

            $faltantes = $actuales->diff($seleccion);

            if ($faltantes->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'sedeUnidades' => 'Para quitar un '.$unidad.' de esta sede, asígnalo desde la sede a la que debe pertenecer.',
                ]);
            }

            $trasladar = UnidadOrganizativa::whereIn('id_unidad', $seleccion->diff($actuales)->all())
                ->lockForUpdate()
                ->get();

            foreach ($trasladar as $registro) {
                $duplicado = UnidadOrganizativa::where('id_sede', $sede->id_sede)
                    ->where('id_unidad', '!=', $registro->id_unidad)
                    ->whereRaw('lower(nombre) = ?', [mb_strtolower($registro->nombre)])
                    ->exists();

                if ($duplicado) {
                    throw ValidationException::withMessages([
                        'sedeUnidades' => 'La sede ya tiene un '.$unidad.' llamado "'.$registro->nombre.'".',
                    ]);
                }

                $registro->id_sede = $sede->id_sede;
                $registro->save();
            }

            $this->sedeEditingId = (int) $sede->id_sede;
        });

        app(\App\Services\SystemCacheService::class)->refreshModule('catalogos');

        $this->registrarBitacora('Se guardó la sede "'.$validated['sedeNombre'].'".');

        if ($this->paginaEdicion && $esNueva) {
            session()->flash('organizacion_mensaje', 'Sede guardada correctamente.');

            $this->redirectRoute('catalogos.index', [
                'section' => 'organizacion',
                'tab' => 'sedes',
            ]);

            return;
        }

        if ($this->paginaEdicion) {
            $this->editarSede((int) $this->sedeEditingId);
            $this->message = 'Sede actualizada correctamente.';

            return;
        }

        $this->resetSede();

        $this->message = 'Sede guardada correctamente.';
    }

    /*
    |--------------------------------------------------------------------------
    | Apoyo
    |--------------------------------------------------------------------------
    */

    private function etiquetas(): array
    {
        return app(PlantillaInstalacionService::class)->etiquetas();
    }

    private function recargar(string $tab, string $mensaje): void
    {
        session()->flash('organizacion_mensaje', $mensaje);

        $this->redirect(
            route('catalogos.index', [
                'section' => 'organizacion',
                'tab' => $tab,
            ])
        );
    }

    private function registrarBitacora(string $descripcion): void
    {
        try {
            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => auth()->id(),
                'accion' => 'ORGANIZACION_ACTUALIZADA',
                'modulo' => 'ADMINISTRACION',
                'descripcion' => $descripcion,
                'fecha_hora' => now(),
            ]);
        } catch (\Throwable $e) {
            // La bitácora no debe impedir guardar la configuración.
            report($e);
        }
    }
};
?>

@php
    $org = app(\App\Services\OrganizacionService::class);
    $etiquetas = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
    $unidad = mb_strtolower($etiquetas['unidad_singular']);
    $unidades = mb_strtolower($etiquetas['unidad_plural']);

    $pestanas = [
        'identidad' => 'Identidad visual',
        'correo' => 'Dominio de correo',
        'registro' => 'Datos de registro',
        'sedes' => 'Sedes',
    ];

    $logoActual = $org->logoUrl();
    $logoPrevio = null;

    if ($logo && ! $errors->has('logo')) {
        try {
            $logoPrevio = $logo->temporaryUrl();
        } catch (\Throwable) {
            $logoPrevio = null;
        }
    }

    $inputClass = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';
@endphp

{{-- En las páginas de crear/editar sede el formulario va sin tarjeta exterior --}}
<div @class([
    'min-w-0 space-y-5',
    'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6' => ! $paginaEdicion,
])>

    @unless ($paginaEdicion)
    {{-- Pestañas internas (el título está en Administración) --}}
    <div
        role="tablist"
        aria-label="Configuración de organización"
        class="flex gap-2 overflow-x-auto border-b border-[var(--theme-border)] pb-px"
    >
        @foreach ($pestanas as $clave => $etiqueta)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $tab === $clave ? 'true' : 'false' }}"
                wire:click="setTab('{{ $clave }}')"
                @class([
                    'shrink-0 border-b-2 px-3 py-2.5 text-xs font-medium transition-colors',
                    'border-[var(--theme-primary)] text-[var(--theme-primary)]' => $tab === $clave,
                    'border-transparent text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]' => $tab !== $clave,
                ])
            >
                {{ $etiqueta }}
            </button>
        @endforeach
    </div>

    @endunless

    {{-- Confirmación --}}
    @if ($message)
        <div
            role="status"
            class="rounded-lg border border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] px-4 py-3 text-sm text-[var(--theme-text-strong)]"
        >
            {{ $message }}
        </div>
    @endif

    {{-- ============================================================
        IDENTIDAD VISUAL
    ============================================================ --}}
    @if ($tab === 'identidad')
        <form
            wire:submit="guardarIdentidad"
            class="space-y-6"
            x-data="{ escala: $wire.entangle('logoEscala'), modo: $wire.entangle('logoModoOscuro') }"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="org-nombre" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                        Nombre de la organización
                    </label>

                    <input
                        id="org-nombre"
                        type="text"
                        wire:model="nombreOrganizacion"
                        maxlength="150"
                        placeholder="{{ \App\Services\OrganizacionService::NOMBRE_PREDETERMINADO }}"
                        class="{{ $inputClass }}"
                    >

                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        Se utiliza como texto alternativo del logotipo.
                    </p>

                    @error('nombreOrganizacion')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Logotipo --}}
            <fieldset class="space-y-4 border-t border-[var(--theme-border)] pt-5">
                <legend class="px-1 text-sm font-semibold text-[var(--theme-text-strong)]">
                    Logotipo principal
                </legend>

                <p class="text-xs text-[var(--theme-text-muted)]">
                    Se muestra en el menú lateral y en las pantallas de inicio de sesión y registro.
                    PNG, JPG o WEBP de hasta 1 MB. Se recomienda un PNG con fondo transparente.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <label
                        for="org-logo"
                        class="cursor-pointer rounded-lg border border-[var(--theme-border)] px-4 py-2.5 text-xs text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                    >
                        {{ $logo ? 'Elegir otra imagen' : 'Reemplazar logotipo' }}
                    </label>

                    <input
                        id="org-logo"
                        type="file"
                        wire:model="logo"
                        accept="image/png,image/jpeg,image/webp"
                        class="sr-only"
                    >

                    <span wire:loading wire:target="logo" class="text-xs text-[var(--theme-text-muted)]">
                        Cargando imagen...
                    </span>

                    @if ($logo)
                        <button
                            type="button"
                            wire:click="descartarLogo"
                            class="text-xs text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] hover:underline"
                        >
                            Descartar imagen nueva
                        </button>
                    @elseif ($org->logoPersonalizado())
                        <button
                            type="button"
                            wire:click="restaurarLogo"
                            wire:confirm="¿Restaurar el logotipo predeterminado?"
                            wire:loading.attr="disabled"
                            class="text-xs text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] hover:underline"
                        >
                            Restaurar logotipo predeterminado
                        </button>
                    @endif
                </div>

                @error('logo')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror

                {{-- Tamaño --}}
                <div class="max-w-xl">
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="org-escala" class="text-xs text-[var(--theme-text-muted)]">
                            Tamaño
                        </label>

                        <span class="text-xs font-medium text-[var(--theme-text-strong)]" x-text="escala + ' %'"></span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            aria-label="Hacer más pequeño"
                            x-on:click="escala = Math.max({{ \App\Services\OrganizacionService::ESCALA_MINIMA }}, Number(escala) - 5)"
                            class="h-8 w-8 shrink-0 rounded-lg border border-[var(--theme-border)] text-sm text-[var(--theme-text)]"
                        >−</button>

                        <input
                            id="org-escala"
                            type="range"
                            min="{{ \App\Services\OrganizacionService::ESCALA_MINIMA }}"
                            max="{{ \App\Services\OrganizacionService::ESCALA_MAXIMA }}"
                            step="5"
                            x-model.number="escala"
                            class="w-full accent-[var(--theme-primary)]"
                        >

                        <button
                            type="button"
                            aria-label="Hacer más grande"
                            x-on:click="escala = Math.min({{ \App\Services\OrganizacionService::ESCALA_MAXIMA }}, Number(escala) + 5)"
                            class="h-8 w-8 shrink-0 rounded-lg border border-[var(--theme-border)] text-sm text-[var(--theme-text)]"
                        >+</button>

                        <button
                            type="button"
                            x-on:click="escala = 100"
                            class="shrink-0 text-xs text-[var(--theme-text-muted)] hover:underline"
                        >100 %</button>
                    </div>

                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        En el menú lateral el ancho se limita a 170 px para no cubrir el botón de colapsar.
                    </p>

                    @error('logoEscala')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modo oscuro --}}
                <div>
                    <p class="mb-2 text-xs text-[var(--theme-text-muted)]">Apariencia en modo oscuro</p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach (\App\Services\OrganizacionService::MODOS_OSCURO as $clave => $modo)
                            <label
                                wire:key="org-modo-{{ $clave }}"
                                class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 text-sm text-[var(--theme-text)]"
                                x-bind:class="modo === '{{ $clave }}' ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)]' : 'border-[var(--theme-border)]'"
                            >
                                <input
                                    type="radio"
                                    value="{{ $clave }}"
                                    x-model="modo"
                                    class="mt-1 h-4 w-4 accent-[var(--theme-primary)]"
                                >

                                <span>
                                    {{ $modo['etiqueta'] }}
                                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">{{ $modo['descripcion'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('logoModoOscuro')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Vista previa --}}
                <div>
                    <p class="mb-2 text-xs text-[var(--theme-text-muted)]">
                        Vista previa en el menú lateral
                        @if ($logoPrevio)
                            · <span class="text-[var(--theme-primary)]">imagen nueva sin guardar</span>
                        @endif
                    </p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex h-28 items-center rounded-lg border border-[var(--theme-border)] bg-white px-5">
                            <img
                                src="{{ $logoPrevio ?? $logoActual }}"
                                alt="Vista previa en modo claro"
                                class="h-auto max-h-[4.5rem] object-contain"
                                x-bind:style="'width: ' + Math.min(Math.round(135 * escala / 100), 170) + 'px'"
                            >
                        </div>

                        <div class="flex h-28 items-center rounded-lg border border-[var(--theme-border)] bg-[#111827] px-5">
                            <img
                                src="{{ $logoPrevio ?? $logoActual }}"
                                alt="Vista previa en modo oscuro"
                                class="org-logo-preview-dark h-auto max-h-[4.5rem] object-contain"
                                x-bind:data-dark-mode="modo"
                                x-bind:style="'width: ' + Math.min(Math.round(135 * escala / 100), 170) + 'px'"
                            >
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3 border-t border-[var(--theme-border)] pt-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="guardarIdentidad,logo"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="guardarIdentidad">Guardar identidad visual</span>
                    <span wire:loading wire:target="guardarIdentidad">Guardando...</span>
                </button>
            </div>
        </form>
    @endif

    {{-- ============================================================
        DOMINIO DE CORREO
    ============================================================ --}}
    @if ($tab === 'correo')
        <form wire:submit="guardarCorreo" class="space-y-5">
            <label class="flex items-start gap-3 rounded-lg border border-[var(--theme-border)] p-4 text-sm text-[var(--theme-text)]">
                <input
                    type="checkbox"
                    wire:model.live="restringirDominio"
                    class="mt-1 h-4 w-4 accent-[var(--theme-primary)]"
                >

                <span>
                    Restringir el registro a correos corporativos
                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                        Solo podrán crear una cuenta quienes utilicen uno de los dominios de la lista.
                        Las cuentas existentes no se modifican.
                    </span>
                </span>
            </label>

            <div class="max-w-xl space-y-3">
                <label for="org-dominio" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                    Dominios permitidos
                </label>

                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-[var(--theme-text-muted)]">@</span>

                        <input
                            id="org-dominio"
                            type="text"
                            wire:model="nuevoDominio"
                            wire:keydown.enter.prevent="agregarDominio"
                            placeholder="empresa.com"
                            maxlength="253"
                            autocomplete="off"
                            class="{{ $inputClass }} pl-7"
                        >
                    </div>

                    <button
                        type="button"
                        wire:click="agregarDominio"
                        class="shrink-0 rounded-lg border border-[var(--theme-border)] px-4 py-2.5 text-xs text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                    >
                        Agregar
                    </button>
                </div>

                @error('nuevoDominio')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror

                @if ($dominios === [])
                    <p class="text-sm text-[var(--theme-text-muted)]">No hay dominios registrados.</p>
                @else
                    <ul class="divide-y divide-[var(--theme-border)] rounded-lg border border-[var(--theme-border)]">
                        @foreach ($dominios as $indice => $dominio)
                            <li wire:key="org-dominio-{{ $dominio }}" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                <span class="text-[var(--theme-text-strong)]">@<span>{{ $dominio }}</span></span>

                                <button
                                    type="button"
                                    wire:click="quitarDominio({{ $indice }})"
                                    aria-label="Quitar {{ $dominio }}"
                                    class="text-xs text-[var(--theme-text-muted)] hover:text-[var(--theme-danger)]"
                                >
                                    Quitar
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @error('dominios')
                    <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror

                @if ($restringirDominio && $dominios !== [])
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Ejemplo aceptado: usuario@{{ $dominios[0] }}
                    </p>
                @endif
            </div>

            <div class="flex justify-end border-t border-[var(--theme-border)] pt-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="guardarCorreo">Guardar configuración de correo</span>
                    <span wire:loading wire:target="guardarCorreo">Guardando...</span>
                </button>
            </div>
        </form>
    @endif

    {{-- ============================================================
        DATOS DE REGISTRO
    ============================================================ --}}
    @if ($tab === 'registro')
        <form wire:submit="guardarRegistro" class="space-y-5">
            <p class="text-xs text-[var(--theme-text-muted)]">
                Define qué datos se solicitan a los usuarios nuevos. Nombre, apellido paterno, correo,
                usuario, sede, {{ $unidad }}, departamento y contraseña siempre son obligatorios.
            </p>

            <div class="overflow-x-auto rounded-xl border border-[var(--theme-border)]">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 sm:px-5">Campo</th>

                            @foreach (\App\Services\OrganizacionService::MODOS_CAMPO as $modo => $etiqueta)
                                <th scope="col" class="px-4 py-3 text-center">{{ $etiqueta }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @foreach (\App\Services\OrganizacionService::CAMPOS_REGISTRO as $campo => $definicion)
                            <tr wire:key="org-campo-{{ $campo }}">
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="font-medium text-[var(--theme-text-strong)]">{{ $definicion['etiqueta'] }}</span>
                                    <span class="block text-xs text-[var(--theme-text-muted)]">
                                        {{ $definicion['grupo'] }}@if ($definicion['ayuda']) · {{ $definicion['ayuda'] }}@endif
                                    </span>

                                    @error('camposRegistro.'.$campo)
                                        <span class="mt-1 block text-xs text-[var(--theme-danger)]">{{ $message }}</span>
                                    @enderror
                                </td>

                                @foreach (\App\Services\OrganizacionService::MODOS_CAMPO as $modo => $etiqueta)
                                    <td class="px-4 py-3 text-center">
                                        <input
                                            type="radio"
                                            wire:model="camposRegistro.{{ $campo }}"
                                            value="{{ $modo }}"
                                            aria-label="{{ $definicion['etiqueta'] }}: {{ $etiqueta }}"
                                            class="h-4 w-4 accent-[var(--theme-primary)]"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-[var(--theme-border)] pt-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="guardarRegistro">Guardar datos de registro</span>
                    <span wire:loading wire:target="guardarRegistro">Guardando...</span>
                </button>
            </div>
        </form>
    @endif

    {{-- ============================================================
        SEDES
    ============================================================ --}}
    @if ($tab === 'sedes')
        @unless ($paginaEdicion)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-[var(--theme-text-muted)]">
                Cada {{ $unidad }} pertenece a una sede. Las sedes inactivas no se ofrecen en el registro de usuarios.
            </p>

            <a
                href="{{ route('catalogos.sedes.create') }}"
                class="shrink-0 rounded-lg bg-[var(--theme-primary)] px-4 py-2.5 text-center text-xs font-semibold text-white"
            >
                + Añadir sede
            </a>
        </div>
        @endunless

        @if ($paginaEdicion && $sedeFormOpen)
            @php
                $todasUnidades = \App\Models\UnidadOrganizativa::with('sede')->orderBy('nombre')->get();
            @endphp

            <form
                wire:submit="guardarSede"
                wire:key="sede-form-{{ $sedeEditingId ?? 'new' }}"
                class="space-y-5"
            >
                {{-- Mismo diseño que Nueva Alta de equipo --}}
                <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                    Información de la sede
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="sede-nombre" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Nombre *</label>
                        <input id="sede-nombre" type="text" wire:model="sedeNombre" maxlength="255" required class="{{ $inputClass }}">
                        @error('sedeNombre')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sede-codigo" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Código</label>
                        <input id="sede-codigo" type="text" wire:model="sedeCodigo" maxlength="50" class="{{ $inputClass }}">
                        @error('sedeCodigo')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 xl:col-span-3">
                        <label for="sede-descripcion" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Descripción</label>
                        <textarea id="sede-descripcion" wire:model="sedeDescripcion" rows="2" maxlength="2000" class="min-h-20 resize-y {{ $inputClass }}"></textarea>
                        @error('sedeDescripcion')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <label class="mt-4 inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                    <input type="checkbox" wire:model="sedeActivo" class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                    Sede activa
                </label>
                </section>

                <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        {{ $etiquetas['unidad_plural'] }} de esta sede
                    </h2>

                    <p class="mt-1 mb-4 text-xs text-[var(--theme-text-muted)]">
                        Marca los {{ $unidades }} que pertenecen a esta sede. Si un {{ $unidad }} está en otra sede,
                        se trasladará al guardar. Para quitar uno de esta sede, asígnalo desde la sede correspondiente.
                    </p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @forelse ($todasUnidades as $registro)
                            @php
                                $enEstaSede = $sedeEditingId && (int) $registro->id_sede === (int) $sedeEditingId;
                            @endphp

                            <label
                                wire:key="sede-unidad-{{ $registro->id_unidad }}"
                                class="flex items-start gap-3 rounded-lg border border-[var(--theme-border)] p-3 text-sm text-[var(--theme-text)]"
                            >
                                <input
                                    type="checkbox"
                                    wire:model="sedeUnidades"
                                    value="{{ $registro->id_unidad }}"
                                    @disabled($enEstaSede)
                                    class="mt-1 h-4 w-4 accent-[var(--theme-primary)]"
                                >

                                <span>
                                    {{ $registro->nombre }}
                                    @unless ($registro->activo)
                                        <span class="text-xs text-[var(--theme-text-muted)]">(Inactivo)</span>
                                    @endunless

                                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                                        @if ($enEstaSede)
                                            Pertenece a esta sede
                                        @else
                                            Actualmente en: {{ $registro->sede?->nombre ?? 'Sin sede' }}
                                        @endif
                                    </span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-[var(--theme-text-muted)]">No hay {{ $unidades }} registrados.</p>
                        @endforelse
                    </div>

                    @error('sedeUnidades')
                        <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                    @error('sedeUnidades.*')
                        <p class="mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                    @enderror
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="cerrarSede"
                        wire:loading.attr="disabled"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="guardarSede">{{ $sedeEditingId ? 'Guardar cambios' : 'Guardar sede' }}</span>
                        <span wire:loading wire:target="guardarSede">Guardando...</span>
                    </button>
                </div>
            </form>
        @endif

        @unless ($paginaEdicion)
        @php
            $sedes = \App\Models\Sede::withCount('unidades')->orderBy('nombre')->get();
        @endphp

        <div class="overflow-hidden rounded-xl border border-[var(--theme-border)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 sm:px-5">Sede</th>
                            <th scope="col" class="px-4 py-3">Código</th>
                            <th scope="col" class="px-4 py-3">{{ $etiquetas['unidad_plural'] }}</th>
                            <th scope="col" class="px-4 py-3">Estado</th>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @forelse ($sedes as $sede)
                            <tr wire:key="sede-row-{{ $sede->id_sede }}">
                                <td class="px-4 py-4 font-medium text-[var(--theme-text-strong)] sm:px-5">{{ $sede->nombre }}</td>
                                <td class="px-4 py-4 text-[var(--theme-text-muted)]">{{ $sede->codigo ?: '—' }}</td>
                                <td class="px-4 py-4 text-[var(--theme-text-muted)]">{{ $sede->unidades_count }}</td>
                                <td class="px-4 py-4">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-500/10 text-emerald-500' => $sede->activo,
                                        'bg-amber-500/10 text-amber-500' => ! $sede->activo,
                                    ])>
                                        {{ $sede->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right sm:px-5">
                                    <a
                                        href="{{ route('catalogos.sedes.edit', ['sede' => $sede->id_sede]) }}"
                                        aria-label="Editar {{ $sede->nombre }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-[var(--theme-border)] px-3 py-2 text-xs text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                                    >
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-[var(--theme-text-muted)]">
                                    No hay sedes registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endunless
    @endif
</div>
