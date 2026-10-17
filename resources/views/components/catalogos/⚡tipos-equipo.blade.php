<?php

use App\Models\TipoEquipo;
use App\Services\SystemCacheService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public string $estado = 'todos';

    public int $perPage = 10;


    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    public bool $modalOpen = false;

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
        $validated =
            $this->validate(
                [
                    'nombre' => [
                        'required',
                        'string',
                        'max:100',

                        Rule::unique(
                            'tipos_equipo',
                            'nombre'
                        )->ignore(
                            $this->editingTipoId,
                            'id_tipo_equipo'
                        ),
                    ],

                    'descripcion' => [
                        'nullable',
                        'string',
                    ],

                    'activo' => [
                        'boolean',
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


        if (
            $this->formMode === 'edit'
            &&
            $this->editingTipoId
        ) {
            $tipo =
                TipoEquipo::query()
                    ->find(
                        $this->editingTipoId
                    );

            if (! $tipo) {
                $this->closeModal();

                return;
            }

            $tipo->update([
                'nombre' =>
                    trim(
                        $validated['nombre']
                    ),

                'descripcion' =>
                    $this->normalizeNullableText(
                        $validated['descripcion']
                        ?? null
                    ),

                'activo' =>
                    (bool) $validated['activo'],
            ]);

            $message =
                'Tipo de equipo actualizado correctamente.';
        } else {
            TipoEquipo::query()
                ->create([
                    'nombre' =>
                        trim(
                            $validated['nombre']
                        ),

                    'descripcion' =>
                        $this->normalizeNullableText(
                            $validated['descripcion']
                            ?? null
                        ),

                    'activo' =>
                        (bool) $validated['activo'],
                ]);

            $message =
                'Tipo de equipo creado correctamente.';
        }


        $this->refreshCatalogCache();

        $this->closeModal();

        $this->resetPage(
            pageName: 'tiposPage'
        );


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


        return $query
            ->orderBy(
                'nombre'
            )
            ->paginate(
                perPage:
                    $this->perPage,

                pageName:
                    'tiposPage'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Métricas
    |--------------------------------------------------------------------------
    */

    public function metricas(): array
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
        app(
            SystemCacheService::class
        )->refreshModule(
            'catalogos'
        );
    }
};

?>


@php
    $tipos =
        $this->tipos();

    $metricas =
        $this->metricas();
@endphp


<div
    x-data="{
        toastOpen: false,
        toastMessage: '',
        toastTimer: null,

        showToast(message) {
            this.toastMessage =
                message
                ?? 'Cambios guardados correctamente.';

            this.toastOpen =
                true;

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

    class="min-w-0 space-y-5 p-4 sm:p-5"
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
                >
                    <path d="M5 12l4 4L19 6"/>
                </svg>
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
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>

        </div>
    </div>


    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div
        class="
            flex
            flex-col

            lg:flex-row
            lg:items-end
            lg:justify-between

            gap-4
        "
    >
        <div>

            <h1
                class="
                    text-xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Tipos de equipo
            </h1>

            <p
                class="
                    mt-1

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Administra los tipos de equipo disponibles para el inventario tecnológico.
            </p>

        </div>


        <button
            type="button"

            wire:click="createTipo"

            class="
                h-9
                px-4

                inline-flex
                items-center
                justify-center
                gap-2

                rounded-md

                bg-[var(--theme-primary)]
                text-white

                text-xs
                font-medium

                hover:bg-[var(--theme-primary-hover)]

                transition-colors
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
            >
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>

            Añadir tipo
        </button>
    </div>


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <section
        class="
            p-4

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]
        "
    >
        <div
            class="
                grid
                grid-cols-1

                md:grid-cols-[minmax(0,1fr)_180px_140px]

                gap-3
            "
        >

            {{-- BUSCADOR --}}
            <div class="relative">

                <div
                    class="
                        pointer-events-none

                        absolute
                        inset-y-0
                        left-0

                        w-10

                        flex
                        items-center
                        justify-center

                        text-[var(--theme-text-muted)]
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="7"
                        />

                        <path d="M20 20l-3.5-3.5"/>
                    </svg>
                </div>


                <input
                    type="search"

                    wire:model.live.debounce.300ms="search"

                    placeholder="Buscar por nombre o descripción..."

                    class="
                        w-full
                        h-10

                        pl-10
                        pr-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface-soft)]

                        text-xs
                        text-[var(--theme-text)]

                        placeholder:text-[var(--theme-text-muted)]

                        focus:outline-none
                        focus:border-[var(--theme-primary)]

                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]

                        transition-colors
                    "
                >

            </div>


            {{-- ESTADO --}}
            <select
                wire:model.live="estado"

                class="
                    w-full
                    h-10

                    px-3

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface-soft)]

                    text-xs
                    text-[var(--theme-text)]

                    focus:outline-none
                    focus:border-[var(--theme-primary)]

                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]
                "
            >
                <option value="todos">
                    Todos los estados
                </option>

                <option value="activos">
                    Activos
                </option>

                <option value="inactivos">
                    Inactivos
                </option>
            </select>


            {{-- REGISTROS --}}
            <select
                wire:model.live="perPage"

                class="
                    w-full
                    h-10

                    px-3

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface-soft)]

                    text-xs
                    text-[var(--theme-text)]

                    focus:outline-none
                    focus:border-[var(--theme-primary)]

                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]
                "
            >
                <option value="10">
                    10
                </option>

                <option value="25">
                    25
                </option>

                <option value="50">
                    50
                </option>

                <option value="100">
                    100
                </option>
            </select>

        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-3

            gap-3
        "
    >

        {{-- TOTAL --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-primary)]

                bg-[var(--theme-surface)]
            "
        >
            <p
                class="
                    text-2xl
                    font-semibold
                    text-[var(--theme-primary)]
                "
            >
                {{ number_format($metricas['total']) }}
            </p>

            <p
                class="
                    mt-0.5

                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Total de tipos
            </p>
        </div>


        {{-- ACTIVOS --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]
            "
        >
            <p
                class="
                    text-2xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                {{ number_format($metricas['activos']) }}
            </p>

            <p
                class="
                    mt-0.5

                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Tipos activos
            </p>
        </div>


        {{-- INACTIVOS --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]
            "
        >
            <p
                class="
                    text-2xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                {{ number_format($metricas['inactivos']) }}
            </p>

            <p
                class="
                    mt-0.5

                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Tipos inactivos
            </p>
        </div>

    </div>


    {{-- ============================================================
        TABLA
    ============================================================ --}}
    <section
        class="
            overflow-hidden

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]
        "
    >

        {{-- ENCABEZADO --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <div
                class="
                    flex
                    items-center
                    justify-between
                    gap-3
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
                        Tipos registrados
                    </h2>

                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ number_format($tipos->total()) }}
                        resultados encontrados
                    </p>

                </div>
            </div>
        </div>


        {{-- ESCRITORIO --}}
        <div
            class="
                hidden
                md:block

                overflow-x-auto
            "
        >
            <table
                class="
                    w-full

                    text-left
                "
            >
                <thead
                    class="
                        bg-[var(--theme-surface-soft)]

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <tr>

                        <th
                            class="
                                px-5
                                py-3

                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            ID
                        </th>

                        <th
                            class="
                                px-5
                                py-3

                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Nombre
                        </th>

                        <th
                            class="
                                px-5
                                py-3

                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Descripción
                        </th>

                        <th
                            class="
                                px-5
                                py-3

                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Estado
                        </th>

                        <th
                            class="
                                px-5
                                py-3

                                text-right
                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Acciones
                        </th>

                    </tr>
                </thead>


                <tbody
                    class="
                        divide-y
                        divide-[var(--theme-border)]
                    "
                >
                    @forelse ($tipos as $tipo)

                        <tr
                            wire:key="tipo-equipo-{{ $tipo->id_tipo_equipo }}"

                            class="
                                hover:bg-[var(--theme-surface-soft)]

                                transition-colors
                            "
                        >

                            <td
                                class="
                                    px-5
                                    py-3.5

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $tipo->id_tipo_equipo }}
                            </td>


                            <td
                                class="
                                    px-5
                                    py-3.5

                                    text-xs
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ $tipo->nombre }}
                            </td>


                            <td
                                class="
                                    px-5
                                    py-3.5

                                    max-w-[440px]

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $tipo->descripcion ?: 'Sin descripción' }}
                            </td>


                            <td
                                class="
                                    px-5
                                    py-3.5
                                "
                            >
                                @if ($tipo->activo)

                                    <span
                                        class="
                                            inline-flex
                                            items-center

                                            px-2.5
                                            py-1

                                            rounded-full

                                            bg-[var(--theme-success-soft)]
                                            text-[var(--theme-success)]

                                            text-[10px]
                                            font-semibold
                                        "
                                    >
                                        Activo
                                    </span>

                                @else

                                    <span
                                        class="
                                            inline-flex
                                            items-center

                                            px-2.5
                                            py-1

                                            rounded-full

                                            bg-[var(--theme-surface-soft)]
                                            text-[var(--theme-text-muted)]

                                            text-[10px]
                                            font-semibold
                                        "
                                    >
                                        Inactivo
                                    </span>

                                @endif
                            </td>


                            <td
                                class="
                                    px-5
                                    py-3.5
                                "
                            >
                                <div
                                    class="
                                        flex
                                        items-center
                                        justify-end
                                        gap-1
                                    "
                                >

                                    {{-- EDITAR --}}
                                    <a
                                        href="{{ route(
                                            'catalogos.tipos-equipo.edit',
                                            [
                                                'tipoEquipo' =>
                                                    $tipo->id_tipo_equipo,
                                            ]
                                        ) }}"

                                        wire:navigate.hover

                                        class="
                                            w-8
                                            h-8

                                            rounded-md

                                            flex
                                            items-center
                                            justify-center

                                            text-[var(--theme-text-muted)]

                                            hover:bg-[var(--theme-primary-soft)]
                                            hover:text-[var(--theme-primary)]

                                            transition-colors
                                        "

                                        title="Editar"
                                        aria-label="Editar tipo de equipo"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4z"/>
                                        </svg>
                                    </a>


                                    {{-- ESTADO --}}
                                    <button
                                        type="button"

                                        wire:click="
                                            toggleTipoStatus(
                                                {{ $tipo->id_tipo_equipo }}
                                            )
                                        "

                                        class="
                                            w-8
                                            h-8

                                            rounded-md

                                            flex
                                            items-center
                                            justify-center

                                            text-[var(--theme-text-muted)]

                                            hover:bg-[var(--theme-surface-soft)]
                                            hover:text-[var(--theme-text-strong)]

                                            transition-colors
                                        "

                                        title="{{ $tipo->activo ? 'Desactivar' : 'Activar' }}"
                                        aria-label="{{ $tipo->activo ? 'Desactivar tipo' : 'Activar tipo' }}"
                                    >
                                        @if ($tipo->activo)

                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                />

                                                <path d="M8 8l8 8"/>
                                            </svg>

                                        @else

                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                />

                                                <path d="M8 12l2.5 2.5L16 9"/>
                                            </svg>

                                        @endif
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="5"

                                class="
                                    px-5
                                    py-12

                                    text-center

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                No se encontraron tipos de equipo.
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- MÓVIL --}}
        <div
            class="
                md:hidden

                divide-y
                divide-[var(--theme-border)]
            "
        >
            @forelse ($tipos as $tipo)

                <div
                    wire:key="tipo-equipo-mobile-{{ $tipo->id_tipo_equipo }}"

                    class="
                        p-4

                        space-y-3
                    "
                >
                    <div
                        class="
                            flex
                            items-start
                            justify-between
                            gap-3
                        "
                    >
                        <div
                            class="
                                min-w-0
                                flex-1
                            "
                        >
                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ $tipo->nombre }}
                            </p>

                            <p
                                class="
                                    mt-0.5

                                    text-[10px]
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                ID #{{ $tipo->id_tipo_equipo }}
                            </p>
                        </div>


                        @if ($tipo->activo)

                            <span
                                class="
                                    shrink-0

                                    px-2
                                    py-1

                                    rounded-full

                                    bg-[var(--theme-success-soft)]
                                    text-[var(--theme-success)]

                                    text-[9px]
                                    font-semibold
                                "
                            >
                                Activo
                            </span>

                        @else

                            <span
                                class="
                                    shrink-0

                                    px-2
                                    py-1

                                    rounded-full

                                    bg-[var(--theme-surface-soft)]
                                    text-[var(--theme-text-muted)]

                                    text-[9px]
                                    font-semibold
                                "
                            >
                                Inactivo
                            </span>

                        @endif
                    </div>


                    <p
                        class="
                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ $tipo->descripcion ?: 'Sin descripción' }}
                    </p>


                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <button
                            type="button"

                            wire:click="
                                editTipo(
                                    {{ $tipo->id_tipo_equipo }}
                                )
                            "

                            class="
                                h-8

                                px-3

                                inline-flex
                                items-center
                                justify-center
                                gap-1.5

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                text-[10px]
                                font-medium
                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]

                                transition-colors
                            "
                        >
                            Editar
                        </button>


                        <button
                            type="button"

                            wire:click="
                                toggleTipoStatus(
                                    {{ $tipo->id_tipo_equipo }}
                                )
                            "

                            class="
                                h-8

                                px-3

                                inline-flex
                                items-center
                                justify-center

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                text-[10px]
                                font-medium
                                text-[var(--theme-text-muted)]

                                hover:bg-[var(--theme-surface-soft)]

                                transition-colors
                            "
                        >
                            {{ $tipo->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>

                </div>

            @empty

                <div
                    class="
                        px-4
                        py-12

                        text-center

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    No se encontraron tipos de equipo.
                </div>

            @endforelse
        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($tipos->hasPages())

            <div
                class="
                    px-4
                    sm:px-5
                    py-3

                    border-t
                    border-[var(--theme-border)]

                    flex
                    items-center
                    justify-between
                    gap-3
                "
            >
                <p
                    class="
                        hidden
                        sm:block

                        text-[10px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Página
                    {{ $tipos->currentPage() }}
                    de
                    {{ $tipos->lastPage() }}
                </p>


                <div
                    class="
                        flex
                        items-center
                        gap-2

                        sm:ml-auto
                    "
                >
                    <button
                        type="button"

                        wire:click="
                            previousPage(
                                'tiposPage'
                            )
                        "

                        @disabled($tipos->onFirstPage())

                        class="
                            h-8

                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            text-[10px]
                            font-medium
                            text-[var(--theme-text)]

                            hover:bg-[var(--theme-surface-soft)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed

                            transition-colors
                        "
                    >
                        Anterior
                    </button>


                    <button
                        type="button"

                        wire:click="
                            nextPage(
                                'tiposPage'
                            )
                        "

                        @disabled(! $tipos->hasMorePages())

                        class="
                            h-8

                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            text-[10px]
                            font-medium
                            text-[var(--theme-text)]

                            hover:bg-[var(--theme-surface-soft)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed

                            transition-colors
                        "
                    >
                        Siguiente
                    </button>
                </div>
            </div>

        @endif

    </section>


    {{-- ============================================================
        MODAL CREAR / EDITAR
    ============================================================ --}}
    @if ($modalOpen)

        <div
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

                wire:click="closeModal"

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
                            {{ $formMode === 'edit'
                                ? 'Editar tipo de equipo'
                                : 'Nuevo tipo de equipo'
                            }}
                        </h2>

                        <p
                            class="
                                mt-1

                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $formMode === 'edit'
                                ? 'Estos son los datos almacenados para este tipo de equipo.'
                                : 'Registra un nuevo tipo de equipo.'
                            }}
                        </p>

                    </div>


                    <button
                        type="button"

                        wire:click="closeModal"

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
                        >
                            <path d="M6 6l12 12"/>
                            <path d="M18 6L6 18"/>
                        </svg>
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
                        @if ($formMode === 'edit')

                            <div>
                                <label
                                    class="
                                        block

                                        mb-1.5

                                        text-[11px]
                                        font-medium
                                        text-[var(--theme-text)]
                                    "
                                >
                                    ID del tipo
                                </label>

                                <input
                                    type="text"

                                    value="{{ $editingTipoId }}"

                                    disabled

                                    class="
                                        w-full
                                        h-10

                                        px-3

                                        rounded-md

                                        border
                                        border-[var(--theme-border)]

                                        bg-[var(--theme-surface-soft)]

                                        text-xs
                                        text-[var(--theme-text-muted)]

                                        cursor-not-allowed
                                    "
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

                        @endif


                        {{-- NOMBRE --}}
                        <div>
                            <label
                                for="tipo-equipo-nombre"

                                class="
                                    block

                                    mb-1.5

                                    text-[11px]
                                    font-medium
                                    text-[var(--theme-text)]
                                "
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
                                    w-full
                                    h-10

                                    px-3

                                    rounded-md

                                    border

                                    @error('nombre')
                                        border-[var(--theme-danger)]
                                    @else
                                        border-[var(--theme-border-strong)]
                                    @enderror

                                    bg-[var(--theme-surface-soft)]

                                    text-xs
                                    text-[var(--theme-text)]

                                    placeholder:text-[var(--theme-text-muted)]

                                    focus:outline-none
                                    focus:border-[var(--theme-primary)]

                                    focus:ring-2
                                    focus:ring-[var(--theme-primary-soft)]
                                "
                            >

                            @error('nombre')

                                <p
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

                                class="
                                    block

                                    mb-1.5

                                    text-[11px]
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                Descripción
                            </label>

                            <textarea
                                id="tipo-equipo-descripcion"

                                wire:model="descripcion"

                                rows="4"

                                placeholder="Describe brevemente este tipo de equipo..."

                                class="
                                    w-full

                                    px-3
                                    py-2.5

                                    rounded-md

                                    border
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface-soft)]

                                    text-xs
                                    text-[var(--theme-text)]

                                    placeholder:text-[var(--theme-text-muted)]

                                    resize-none

                                    focus:outline-none
                                    focus:border-[var(--theme-primary)]

                                    focus:ring-2
                                    focus:ring-[var(--theme-primary-soft)]
                                "
                            ></textarea>

                            @error('descripcion')

                                <p
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


                        {{-- ESTADO --}}
                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4

                                p-3.5

                                rounded-lg

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        >
                            <div>
                                <p
                                    class="
                                        text-[11px]
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    Tipo activo
                                </p>

                                <p
                                    class="
                                        mt-0.5

                                        text-[10px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Los tipos inactivos permanecen registrados pero no deben utilizarse en nuevos registros.
                                </p>
                            </div>


                            <label
                                class="
                                    relative

                                    inline-flex
                                    items-center

                                    shrink-0

                                    cursor-pointer
                                "
                            >
                                <input
                                    type="checkbox"

                                    wire:model="activo"

                                    class="
                                        peer
                                        sr-only
                                    "
                                >

                                <span
                                    class="
                                        w-10
                                        h-5

                                        rounded-full

                                        bg-[var(--theme-border-strong)]

                                        peer-checked:bg-[var(--theme-primary)]

                                        transition-colors

                                        after:content-['']

                                        after:absolute
                                        after:top-0.5
                                        after:left-0.5

                                        after:w-4
                                        after:h-4

                                        after:rounded-full

                                        after:bg-white

                                        after:transition-transform

                                        peer-checked:after:translate-x-5
                                    "
                                ></span>
                            </label>
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

                            wire:click="closeModal"

                            class="
                                h-9

                                px-4

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface)]

                                text-xs
                                font-medium
                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]

                                transition-colors
                            "
                        >
                            Cancelar
                        </button>


                        <button
                            type="submit"

                            wire:loading.attr="disabled"
                            wire:target="saveTipo"

                            class="
                                min-w-[118px]
                                h-9

                                px-4

                                rounded-md

                                inline-flex
                                items-center
                                justify-center
                                gap-2

                                bg-[var(--theme-primary)]
                                text-white

                                text-xs
                                font-medium

                                hover:bg-[var(--theme-primary-hover)]

                                disabled:opacity-60
                                disabled:cursor-not-allowed

                                transition-colors
                            "
                        >
                            <svg
                                wire:loading
                                wire:target="saveTipo"

                                class="
                                    w-4
                                    h-4

                                    animate-spin
                                "

                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    opacity=".25"
                                />

                                <path
                                    d="M21 12a9 9 0 00-9-9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>


                            <span
                                wire:loading.remove
                                wire:target="saveTipo"
                            >
                                {{ $formMode === 'edit'
                                    ? 'Guardar cambios'
                                    : 'Crear tipo'
                                }}
                            </span>


                            <span
                                wire:loading
                                wire:target="saveTipo"
                            >
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    @endif

</div>