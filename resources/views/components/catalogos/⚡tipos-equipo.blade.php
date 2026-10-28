<?php

use App\Models\TipoEquipo;
use App\Services\SystemCacheService;
use Illuminate\Validation\Rule;
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
                ['label' => 'ID', 'width' => '80px'],
                ['label' => 'Tipo de equipo', 'width' => '26%'],
                ['label' => 'Descripción', 'width' => '44%'],
                ['label' => 'Estado', 'width' => '120px', 'type' => 'status'],
            ],
            'anchoMinimo' => '760px',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public string $estado = 'todos';

    public int $perPage = 10;

    /*
     * Ordenamiento de la tabla.
     */
    public string $sortField = 'id';

    // Por defecto lo más reciente primero (último tipo creado arriba).
    public string $sortDirection = 'desc';

    private const ORDENABLES = [
        'id' => 'id_tipo_equipo',
        'nombre' => 'nombre',
        'descripcion' => 'descripcion',
        'activo' => 'activo',
    ];

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

        $this->resetPage(pageName: 'tiposPage');
    }

    public function updatedSortDirection(): void
    {
        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }

        $this->resetPage(pageName: 'tiposPage');
    }


    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    public bool $modalOpen = false;

    /*
     * Cambia en cada apertura para que el modal se cree de nuevo
     * (el cierre se hace en el navegador sin petición).
     */
    public string $modalToken = '';

    public string $formMode = 'create';

    public ?int $editingTipoId = null;


    /*
    |--------------------------------------------------------------------------
    | Formulario
    |--------------------------------------------------------------------------
    */

    public string $nombre = '';

    public string $descripcion = '';

    public bool $activo = true;


    /*
    |--------------------------------------------------------------------------
    | Reiniciar paginación
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        $this->resetPage(
            pageName: 'tiposPage'
        );
    }


    public function updatedEstado(): void
    {
        $this->resetPage(
            pageName: 'tiposPage'
        );
    }


    public function updatedPerPage(): void
    {
        if (
            ! in_array(
                $this->perPage,
                [
                    10,
                    25,
                    50,
                    100,
                ],
                true
            )
        ) {
            $this->perPage = 10;
        }

        $this->resetPage(
            pageName: 'tiposPage'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function createTipo(): void
    {
        $this->resetForm();

        $this->formMode =
            'create';

        $this->activo =
            true;

        $this->modalToken =
            uniqid('tipo-modal-');

        $this->modalOpen =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function editTipo(
        int $idTipoEquipo
    ): void {
        $tipo =
            TipoEquipo::query()
                ->find(
                    $idTipoEquipo
                );

        if (! $tipo) {
            return;
        }

        $this->resetValidation();

        $this->formMode =
            'edit';

        $this->editingTipoId =
            (int) $tipo->id_tipo_equipo;

        $this->nombre =
            (string) $tipo->nombre;

        $this->descripcion =
            (string) (
                $tipo->descripcion
                ?? ''
            );

        $this->activo =
            (bool) $tipo->activo;

        $this->modalToken =
            uniqid('tipo-modal-');

        $this->modalOpen =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    public function saveTipo(): void
    {
        $validated = $this->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique('tipos_equipo', 'nombre')
                        ->ignore(
                            $this->editingTipoId,
                            'id_tipo_equipo'
                        ),
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                ],
            ],
            [
                'nombre.required' =>
                    'El nombre del tipo de equipo es obligatorio.',

                'nombre.max' =>
                    'El nombre no puede superar los 100 caracteres.',

                'nombre.unique' =>
                    'Ya existe un tipo de equipo con ese nombre.',
            ]
        );

        $attributes = [
            'nombre' => trim($validated['nombre']),

            'descripcion' => $this->normalizeNullableText(
                $validated['descripcion'] ?? null
            ),
        ];

        if (
            $this->formMode === 'edit'
            && $this->editingTipoId
        ) {
            $tipo = TipoEquipo::query()
                ->find($this->editingTipoId);

            if (!$tipo) {
                $this->closeModal();
                return;
            }

            \Illuminate\Support\Facades\DB::transaction(
                function () use ($tipo, $attributes): void {
                    $tipo->update($attributes);

                    app(
                        \App\Services\TipoEquipoFormBuilderService::class
                    )->ensureRequiredFields(
                        (int) $tipo->id_tipo_equipo
                    );
                }
            );

            $message = 'Tipo de equipo actualizado correctamente.';
        } else {
            \Illuminate\Support\Facades\DB::transaction(
                function () use ($attributes): void {
                    // Los tipos nuevos siempre se crean activos.
                    $tipo = TipoEquipo::query()->create($attributes + ['activo' => true]);

                    app(
                        \App\Services\TipoEquipoFormBuilderService::class
                    )->ensureRequiredFields(
                        (int) $tipo->id_tipo_equipo
                    );
                }
            );

            $message = 'Tipo de equipo creado correctamente.';
        }

        $this->refreshCatalogCache();
        $this->closeModal();
        $this->resetPage(pageName: 'tiposPage');

        $this->dispatch(
            'tipo-equipo-guardado',
            message: $message
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activar / desactivar
    |--------------------------------------------------------------------------
    */

    public function toggleTipoStatus(
        int $idTipoEquipo
    ): void {
        $tipo =
            TipoEquipo::query()
                ->find(
                    $idTipoEquipo
                );

        if (! $tipo) {
            return;
        }

        $nuevoEstado =
            ! (bool) $tipo->activo;

        $tipo->update([
            'activo' =>
                $nuevoEstado,
        ]);


        $this->refreshCatalogCache();


        $this->dispatch(
            'tipo-equipo-guardado',
            message:
                $nuevoEstado
                    ? 'Tipo de equipo activado correctamente.'
                    : 'Tipo de equipo desactivado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar modal
    |--------------------------------------------------------------------------
    */

    public function closeModal(): void
    {
        $this->modalOpen =
            false;

        $this->resetForm();
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar formulario
    |--------------------------------------------------------------------------
    */

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->formMode =
            'create';

        $this->editingTipoId =
            null;

        $this->nombre =
            '';

        $this->descripcion =
            '';

        $this->activo =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | Consulta principal
    |--------------------------------------------------------------------------
    */

    public function tipos()
    {
        $search =
            trim(
                $this->search
            );


        $query =
            TipoEquipo::query()
                ->select([
                    'id_tipo_equipo',
                    'nombre',
                    'descripcion',
                    'activo',
                ]);


        if ($search !== '') {
            $query->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'nombre',
                            'ILIKE',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'descripcion',
                            'ILIKE',
                            '%' . $search . '%'
                        );
                }
            );
        }


        if (
            $this->estado === 'activos'
        ) {
            $query->where(
                'activo',
                true
            );
        }


        if (
            $this->estado === 'inactivos'
        ) {
            $query->where(
                'activo',
                false
            );
        }


        /*
         * Página en caché (módulo «catalogos»): refreshCatalogCache() la
         * renueva al crear, editar o activar/desactivar un tipo. Antes cada
         * carga hacía 3 consultas (~160 ms cada una contra Supabase).
         */
        $perPage = max(1, (int) $this->perPage);
        $page = max(1, (int) $this->getPage('tiposPage'));
        $sortColumn = self::ORDENABLES[$this->sortField] ?? 'nombre';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $datos = app(SystemCacheService::class)->remember(
            'catalogos',
            'administracion.tipos_equipo.pagina.v1',
            [
                'search' => $search,
                'estado' => $this->estado,
                'sort' => $sortColumn,
                'direction' => $sortDirection,
                'per_page' => $perPage,
                'page' => $page,
            ],
            function () use ($query, $search, $perPage, $page, $sortColumn, $sortDirection): array {
                /*
                 * Sin búsqueda, el total ya lo calculan las métricas:
                 * se evita el COUNT(*) extra de la paginación.
                 */
                if ($search === '') {
                    $metricas = $this->metricas();

                    $total = match ($this->estado) {
                        'activos' => $metricas['activos'],
                        'inactivos' => $metricas['inactivos'],
                        default => $metricas['total'],
                    };
                } else {
                    $total = (clone $query)->count();
                }

                $items = $query
                    ->orderByRaw($sortColumn . ' ' . $sortDirection . ' NULLS LAST')
                    ->orderBy('id_tipo_equipo')
                    ->forPage($page, $perPage)
                    ->get()
                    ->map(fn ($tipo): array => [
                        'id_tipo_equipo' => (int) $tipo->id_tipo_equipo,
                        'nombre' => (string) $tipo->nombre,
                        'descripcion' => $tipo->descripcion,
                        'activo' => (bool) $tipo->activo,
                    ])
                    ->all();

                return [
                    'items' => $items,
                    'total' => (int) $total,
                ];
            }
        );

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_map(fn (array $item): object => (object) $item, $datos['items']),
            $datos['total'],
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'tiposPage',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Métricas
    |--------------------------------------------------------------------------
    */

    public function metricas(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'administracion.tipos_equipo.metricas.v1',
            fn (): array => $this->leerMetricas()
        );
    }

    private function leerMetricas(): array
    {
        $row =
            TipoEquipo::query()
                ->selectRaw(
                    '
                        COUNT(*) AS total,
                        COUNT(*) FILTER (
                            WHERE activo = true
                        ) AS activos,
                        COUNT(*) FILTER (
                            WHERE activo = false
                        ) AS inactivos
                    '
                )
                ->first();


        return [
            'total' =>
                (int) (
                    $row->total
                    ?? 0
                ),

            'activos' =>
                (int) (
                    $row->activos
                    ?? 0
                ),

            'inactivos' =>
                (int) (
                    $row->inactivos
                    ?? 0
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Texto nullable
    |--------------------------------------------------------------------------
    */

    private function normalizeNullableText(
        ?string $value
    ): ?string {
        $value =
            trim(
                (string) $value
            );

        return $value === ''
            ? null
            : $value;
    }


    /*
    |--------------------------------------------------------------------------
    | Invalidar caché
    |--------------------------------------------------------------------------
    */

    private function refreshCatalogCache(): void
    {
        // Actualizar la lista utilizada por los formularios de equipos.
        \Illuminate\Support\Facades\Cache::forget(
            'form.tipos_equipo.v2'
        );

        // Actualizar el catálogo de Administración.
        app(
            \App\Services\SystemCacheService::class
        )->refreshModule('catalogos');
    }
};

?>


@php
    $tipos =
        $this->tipos();

    $metricas =
        $this->metricas();
@endphp


{{--
    Contenedor raíz sin Alpine: el x-data va en el elemento interno
    para que se inicialice también cuando la sección se carga en
    diferido (lazy) desde Administración.

    El wire:key evita que Livewire reutilice un elemento del
    placeholder para este contenedor: así Alpine lo trata como
    elemento nuevo y ejecuta su x-data.
--}}
<div>
<div
    wire:key="tipos-equipo-contenido"
    x-data="{
        toastOpen: false,
        toastMessage: '',
        toastTimer: null,

        showToast(message) {
            this.toastMessage =
                message
                ?? 'Cambios guardados correctamente.';

            // Aviso global (components/ui/toast-region).
            window.trackitToast?.('success', this.toastMessage);

            if (this.toastTimer) {
                clearTimeout(
                    this.toastTimer
                );
            }

            this.toastTimer =
                setTimeout(
                    () => {
                        this.toastOpen =
                            false;
                    },
                    3200
                );
        }
    }"

    @tipo-equipo-guardado.window="
        showToast(
            $event.detail.message
        )
    "

    class="min-w-0 space-y-5"
>

    {{-- ============================================================
        TOAST
    ============================================================ --}}
    <div
        x-show="toastOpen"
        x-cloak

        x-transition:enter="
            transition
            ease-out
            duration-200
        "

        x-transition:enter-start="
            opacity-0
            translate-y-2
        "

        x-transition:enter-end="
            opacity-100
            translate-y-0
        "

        x-transition:leave="
            transition
            ease-in
            duration-150
        "

        x-transition:leave-start="
            opacity-100
            translate-y-0
        "

        x-transition:leave-end="
            opacity-0
            translate-y-2
        "

        class="
            fixed
            top-5
            right-5
            z-[150]

            w-[calc(100vw-2.5rem)]
            sm:w-auto
            sm:min-w-[320px]
            sm:max-w-md

            px-4
            py-3

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]

            theme-shadow-xl
        "
    >
        <div
            class="
                flex
                items-start
                gap-3
            "
        >

            <div
                class="
                    w-8
                    h-8
                    shrink-0

                    rounded-full

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                ><use href="{{ \App\Support\Icono::url('i-42997ad') }}"></use></svg>
            </div>


            <div
                class="
                    min-w-0
                    flex-1
                "
            >
                <p
                    class="
                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Tipo de equipo actualizado
                </p>

                <p
                    x-text="toastMessage"

                    class="
                        mt-0.5

                        text-[11px]
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                ></p>
            </div>


            <button
                type="button"

                @click="
                    toastOpen = false
                "

                class="
                    w-7
                    h-7
                    shrink-0

                    rounded-md

                    flex
                    items-center
                    justify-center

                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text-strong)]

                    transition-colors
                "

                aria-label="Cerrar notificación"
            >
                <svg
                    class="w-3.5 h-3.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                ><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
            </button>

        </div>
    </div>


    {{-- ============================================================
        BUSCADOR, FILTROS Y ACCIÓN
        (El título está en Administración; aquí no se repite.)
    ============================================================ --}}
    <x-admin.filter-bar>
        <x-admin.search
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o descripción..."
            label="Buscar tipo de equipo"
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
            <x-admin.action-button x-on:click="$dispatch('abrir-tipo-equipo', {})">
                Añadir tipo
            </x-admin.action-button>
        </x-slot:actions>
    </x-admin.filter-bar>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <x-admin.metrics :items="[
        ['label' => 'Total de tipos', 'value' => $metricas['total'] ?? 0],
        ['label' => 'Tipos activos', 'value' => $metricas['activos'] ?? 0],
        ['label' => 'Tipos inactivos', 'value' => $metricas['inactivos'] ?? 0],
    ]" />


    {{-- ============================================================
        TABLA: componente reutilizable <x-data-table>
    ============================================================ --}}
    <x-data-table
        :rows="$tipos"
        row-key="id_tipo_equipo"
        key-prefix="tipo-equipo"

        :sort-field="$sortField"
        :sort-direction="$sortDirection"
        sort-action="sortBy"

        per-page-model="perPage"
        sort-model="sortDirection"
        refresh-action="$refresh"

        page-name="tiposPage"
        result-singular="tipo de equipo encontrado"
        result-plural="tipos de equipo encontrados"
        empty-message="No se encontraron tipos de equipo con estos filtros."
        min-width="760px"

        :columns="[
            ['key' => 'id_tipo_equipo', 'label' => 'ID', 'width' => '80px', 'sort' => 'id', 'mobile' => false],
            ['key' => 'nombre', 'label' => 'Tipo de equipo', 'width' => '26%', 'sort' => 'nombre', 'strong' => true],
            ['key' => 'descripcion', 'label' => 'Descripción', 'width' => '44%', 'sort' => 'descripcion', 'default' => 'Sin descripción', 'mobile' => 'filled'],
            ['key' => 'activo', 'label' => 'Estado', 'width' => '120px', 'sort' => 'activo', 'type' => 'status', 'value' => fn ($t) => $t->activo ? 'Activo' : 'Inactivo'],
        ]"

        :actions="fn ($tipo) => [
            'edit' => route('catalogos.tipos-equipo.edit', ['tipoEquipo' => $tipo->id_tipo_equipo]),
            'menu' => [
                [
                    'label' => 'Editar formulario',
                    'href' => route('catalogos.tipos-equipo.edit', ['tipoEquipo' => $tipo->id_tipo_equipo]),
                ],
                [
                    'label' => 'Editar nombre',
                    'js' => '$dispatch(\'abrir-tipo-equipo\', '.\Illuminate\Support\Js::from([
                        'id' => $tipo->id_tipo_equipo,
                        'nombre' => $tipo->nombre,
                        'descripcion' => $tipo->descripcion ?? '',
                    ]).')',
                ],
            ],
        ]"
    />


    {{-- ============================================================
        MODAL CREAR / EDITAR
    ============================================================ --}}
    {{--
        El modal se abre y se cierra en el navegador con los datos que ya
        tiene la tabla: no hay consulta extra al abrir ni al cancelar.
        Solo "Guardar" va al servidor (saveTipo), que valida y avisa con
        el evento tipo-equipo-guardado para cerrarlo.
    --}}
        <div
            wire:ignore.self
            x-data="{
                visible: false,
                modo: 'create',
                id: null,

                abrir(datos = {}) {
                    this.modo = datos.id ? 'edit' : 'create';
                    this.id = datos.id ?? null;

                    this.$wire.$set('formMode', this.modo, false);
                    this.$wire.$set('editingTipoId', this.id, false);
                    this.$wire.$set('nombre', datos.nombre ?? '', false);
                    this.$wire.$set('descripcion', datos.descripcion ?? '', false);

                    /* Quitar errores de un intento anterior. */
                    this.$root.querySelectorAll('[data-error]').forEach(error => error.remove());

                    this.visible = true;

                    this.$nextTick(() => document.getElementById('tipo-equipo-nombre')?.focus());
                },

                cerrar() {
                    this.visible = false;
                },
            }"
            x-show="visible"
            x-cloak
            @abrir-tipo-equipo.window="abrir($event.detail ?? {})"
            @tipo-equipo-guardado.window="cerrar()"
            @keydown.escape.window="cerrar()"

            class="
                fixed
                inset-0
                z-[140]

                flex
                items-center
                justify-center

                p-4
            "
        >
            {{-- FONDO --}}
            <button
                type="button"

                @click="cerrar()"

                class="
                    absolute
                    inset-0

                    w-full
                    h-full

                    bg-[var(--theme-overlay)]
                "

                aria-label="Cerrar modal"
            ></button>


            {{-- MODAL --}}
            <div
                class="
                    relative
                    z-10

                    w-full
                    max-w-xl

                    overflow-hidden

                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    theme-shadow-xl
                "
            >

                {{-- CABECERA --}}
                <div
                    class="
                        px-5
                        py-4

                        border-b
                        border-[var(--theme-border)]

                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >
                    <div>

                        <h2
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            <span x-text="modo === 'edit' ? 'Editar tipo de equipo' : 'Nuevo tipo de equipo'"></span>
                        </h2>

                        <p
                            class="
                                mt-1

                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            <span x-text="modo === 'edit' ? 'Estos son los datos almacenados para este tipo de equipo.' : 'Registra un nuevo tipo de equipo.'"></span>
                        </p>

                    </div>


                    <button
                        type="button"

                        @click="cerrar()"

                        class="
                            w-8
                            h-8
                            shrink-0

                            rounded-md

                            flex
                            items-center
                            justify-center

                            text-[var(--theme-text-muted)]

                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "

                        aria-label="Cerrar"
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        ><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
                    </button>

                </div>


                {{-- FORMULARIO --}}
                <form
                    wire:submit="saveTipo"
                >
                    <div
                        class="
                            p-5

                            space-y-5
                        "
                    >

                        {{-- ID --}}
                            <div x-show="modo === 'edit'">
                                <label
                                    class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                                >
                                    ID del tipo
                                </label>

                                <input
                                    type="text"

                                    :value="id"

                                    disabled

                                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] cursor-not-allowed"
                                >

                                <p
                                    class="
                                        mt-1

                                        text-[10px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Este identificador es generado por el sistema y no puede modificarse.
                                </p>
                            </div>


                        {{-- NOMBRE --}}
                        <div>
                            <label
                                for="tipo-equipo-nombre"

                                class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                            >
                                Nombre
                                <span
                                    class="
                                        text-[var(--theme-danger)]
                                    "
                                >
                                    *
                                </span>
                            </label>

                            <input
                                id="tipo-equipo-nombre"

                                type="text"

                                wire:model="nombre"

                                maxlength="100"

                                autocomplete="off"

                                placeholder="Ej. Laptop"

                                class="
                                    w-full rounded-md border bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]
                                    @error('nombre') border-[var(--theme-danger)] @else border-[var(--theme-border-strong)] @enderror
                                "
                            >

                            @error('nombre')

                                <p
                                    data-error
                                    class="
                                        mt-1.5

                                        text-[10px]
                                        text-[var(--theme-danger)]
                                    "
                                >
                                    {{ $message }}
                                </p>

                            @enderror
                        </div>


                        {{-- DESCRIPCIÓN --}}
                        <div>
                            <label
                                for="tipo-equipo-descripcion"

                                class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                            >
                                Descripción
                            </label>

                            <textarea
                                id="tipo-equipo-descripcion"

                                wire:model="descripcion"

                                rows="4"

                                placeholder="Describe brevemente este tipo de equipo..."

                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
                            ></textarea>

                            @error('descripcion')

                                <p
                                    data-error
                                    class="
                                        mt-1.5

                                        text-[10px]
                                        text-[var(--theme-danger)]
                                    "
                                >
                                    {{ $message }}
                                </p>

                            @enderror
                        </div>


                    </div>


                    {{-- ACCIONES --}}
                    <div
                        class="
                            px-5
                            py-4

                            border-t
                            border-[var(--theme-border)]

                            flex
                            items-center
                            justify-end
                            gap-2
                        "
                    >
                        <button
                            type="button"

                            @click="cerrar()"

                            class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                        >
                            Cancelar
                        </button>


                        <x-ui.button
                            type="submit"
                            target="saveTipo"
                            size="lg"
                            loading-text="Guardando…"
                            class="w-full sm:w-auto"
                        >
                            <span x-text="modo === 'edit' ? 'Guardar cambios' : 'Crear tipo'"></span>
                        </x-ui.button>
                    </div>
                </form>

            </div>
        </div>

</div>

</div>
