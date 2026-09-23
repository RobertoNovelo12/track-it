<div
    x-data="{
        toastOpen: false,
        toastMessage: '',
        toastTimer: null,

        showToast(message) {
            this.toastMessage = message ?? 'Cambios guardados correctamente.';
            this.toastOpen = true;

            if (this.toastTimer) {
                clearTimeout(this.toastTimer);
            }

            this.toastTimer = setTimeout(() => {
                this.toastOpen = false;
            }, 3200);
        }
    }"
    @catalog-item-saved.window="showToast($event.detail.message)"
    @catalog-status-updated.window="showToast($event.detail.message)"
    class="space-y-5"
>

    {{-- ============================================================
        TOAST LOCAL
    ============================================================ --}}
    <div
        x-show="toastOpen"
        x-cloak

        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"

        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"

        class="
            fixed
            top-5
            right-5
            z-[120]

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
        <div class="flex items-start gap-3">

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
                >
                    <path d="M5 12l4 4L19 6"/>
                </svg>
            </div>


            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Catálogo actualizado
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
                @click="toastOpen = false"

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
                Gestión de marcas y modelos
            </h1>

            <p
                class="
                    mt-1

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Administra las marcas y modelos disponibles para el inventario tecnológico.
            </p>

        </div>


        <a
            href="{{ route('catalogos.create') }}"

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
            >
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>

            Añadir
        </a>
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
                md:grid-cols-[minmax(0,1fr)_160px_190px]

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
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-3.5-3.5"/>
                    </svg>
                </div>


                <input
                    type="search"

                    wire:model.live.debounce.300ms="search"

                    placeholder="Buscar por marca, modelo, tipo o descripción..."

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
            <div>
                <select
                    wire:model.live="estado"

                    class="
                        w-full
                        h-10

                        px-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

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
            </div>


            {{-- TIPO --}}
            <div>
                <select
                    wire:model.live="tipo"

                    class="
                        w-full
                        h-10

                        px-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        text-[var(--theme-text)]

                        focus:outline-none
                        focus:border-[var(--theme-primary)]
                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]
                    "
                >
                    <option value="todos">
                        Todos los tipos
                    </option>

                    @foreach ($tiposEquipo as $tipoEquipo)

                        <option
                            value="{{ $tipoEquipo->id_tipo_equipo }}"
                        >
                            {{ $tipoEquipo->nombre }}
                        </option>

                    @endforeach
                </select>
            </div>
        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-2
            xl:grid-cols-4

            gap-3
        "
    >
        {{-- TOTAL MARCAS --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-primary)]

                bg-[var(--theme-surface)]
            "
        >
            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10
                        h-10
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-primary-soft)]
                        text-[var(--theme-primary)]
                    "
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M20 12l-8 8-8-8V4h8l8 8z"/>
                        <circle cx="8.5" cy="8.5" r="1"/>
                    </svg>
                </div>


                <div>
                    <p
                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        {{ $totalMarcas }}
                    </p>

                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Total de marcas
                    </p>
                </div>

            </div>
        </div>


        {{-- TOTAL MODELOS --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]
            "
        >
            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10
                        h-10
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]
                        text-[var(--theme-text)]
                    "
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <rect x="4" y="5" width="16" height="14" rx="2"/>
                        <path d="M8 9h8"/>
                        <path d="M8 13h5"/>
                    </svg>
                </div>


                <div>
                    <p
                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ $totalModelos }}
                    </p>

                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Total de modelos
                    </p>
                </div>

            </div>
        </div>


        {{-- MARCAS INACTIVAS --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]
            "
        >
            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10
                        h-10
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]
                        text-[var(--theme-text-muted)]
                    "
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M12 8v5"/>
                        <path d="M12 17h.01"/>
                        <path d="M10.3 4.7L2.9 17.5A2 2 0 004.6 20h14.8a2 2 0 001.7-2.5L13.7 4.7a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>


                <div>
                    <p
                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ $marcasInactivas }}
                    </p>

                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Marcas inactivas
                    </p>
                </div>

            </div>
        </div>


        {{-- MODELOS EN USO --}}
        <div
            class="
                p-4

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]
            "
        >
            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10
                        h-10
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-primary-soft)]
                        text-[var(--theme-primary)]
                    "
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <rect x="5" y="5" width="14" height="14" rx="2"/>
                        <path d="M9 9h6v6H9z"/>
                        <path d="M2 9h3"/>
                        <path d="M2 15h3"/>
                        <path d="M19 9h3"/>
                        <path d="M19 15h3"/>
                    </svg>
                </div>


                <div>
                    <p
                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        {{ $modelosEnUso }}
                    </p>

                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Modelos en uso
                    </p>
                </div>

            </div>
        </div>
    </div>


    {{-- ============================================================
        RESULTADOS - MARCAS
        Basado directamente en equipos-index.blade.php
    ============================================================ --}}
    <div
        class="
            relative

            bg-transparent
            border-0
            rounded-none
            overflow-visible

            md:bg-[var(--theme-surface)]
            md:border
            md:border-[var(--theme-border)]
            md:rounded-lg
            md:overflow-hidden
        "
    >
        {{-- Overlay mientras Livewire actualiza --}}
        <div
            wire:loading.delay
            wire:target="
                search,
                estado,
                tipo,
                perPageMarcas,
                sortMarcas,
                previousPage,
                nextPage,
                gotoPage,
                toggleMarcaStatus,
                editMarca
            "
            class="
                absolute inset-0
                bg-[var(--theme-surface)] opacity-50
                z-10
                pointer-events-none
            "
        ></div>


        {{-- ========================================================
            BARRA DE RESULTADOS - ESCRITORIO
        ======================================================== --}}
        <div
            class="
                hidden md:flex
                items-center justify-between
                px-5 py-4
                border-b border-[var(--theme-border)]
            "
        >
            <p class="text-sm text-[var(--theme-text)]">

                Resultados:

                <span class="font-medium">
                    {{ number_format($marcas->total()) }}
                </span>

                {{ $marcas->total() === 1 ? 'marca encontrada' : 'marcas encontradas' }}

            </p>


            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Mostrar
                    </span>

                    <select
                        wire:model.live="perPageMarcas"
                        class="
                            text-xs
                            border border-[var(--theme-border-strong)]
                            rounded-md
                            px-2 py-1.5
                            bg-[var(--theme-surface)]
                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                        "
                    >
                        @foreach ([10, 25, 50, 100] as $option)

                            <option value="{{ $option }}">
                                {{ $option }}
                            </option>

                        @endforeach
                    </select>

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Por página
                    </span>

                </div>


                <select
                    wire:model.live="sortMarcas"
                    class="
                        text-xs
                        border border-[var(--theme-border-strong)]
                        rounded-md
                        px-2 py-1.5
                        bg-[var(--theme-surface)]
                        uppercase
                        focus:ring-1
                        focus:ring-[var(--theme-primary)]
                    "
                >
                    <option value="asc">
                        ASC
                    </option>

                    <option value="desc">
                        DESC
                    </option>
                </select>

            </div>
        </div>


        {{-- ========================================================
            TABLA - ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b border-[var(--theme-border)]
                            text-left
                        "
                    >
                        <th class="px-5 py-3 w-10">

                            <input
                                type="checkbox"
                                onclick="
                                    this.closest('table')
                                        .querySelectorAll('tbody input[type=checkbox]')
                                        .forEach((checkbox) => checkbox.checked = this.checked)
                                "
                                class="rounded border-[var(--theme-border-strong)]"
                            >

                        </th>


                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            ID Marca
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Marca
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Descripción
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Estado
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Modelos
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Equipos
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Registro
                        </th>

                        <th
                            class="
                                px-3 py-3
                                font-medium
                                text-[var(--theme-text-muted)]
                                text-right
                            "
                        >
                            Acciones
                        </th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($marcas as $marca)

                        <tr
                            wire:key="marca-{{ $marca->id_marca }}"
                            class="
                                border-b border-[var(--theme-border)]
                                hover:bg-[var(--theme-primary-soft-subtle)]
                                transition-colors
                            "
                        >
                            <td class="px-5 py-3">

                                <input
                                    type="checkbox"
                                    value="{{ $marca->id_marca }}"
                                    class="
                                        rounded
                                        border-[var(--theme-border-strong)]
                                    "
                                >

                            </td>


                            {{-- ID --}}
                            <td
                                class="
                                    px-3 py-3
                                    font-medium
                                    text-[var(--theme-text)]
                                    whitespace-nowrap
                                "
                            >
                                {{ $marca->id_marca }}
                            </td>


                            {{-- Marca --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $marca->nombre }}
                            </td>


                            {{-- Descripción --}}
                            <td
                                class="
                                    px-3 py-3
                                    max-w-[340px]
                                    text-[var(--theme-text)]
                                "
                            >
                                <span
                                    class="block truncate"
                                    title="{{ $marca->descripcion ?: 'Sin descripción' }}"
                                >
                                    {{ $marca->descripcion ?: 'Sin descripción' }}
                                </span>
                            </td>


                            {{-- Estado --}}
                            <td class="px-3 py-3">

                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        px-2 py-1
                                        rounded-full
                                        text-[10px]
                                        font-medium

                                        {{ $marca->activo
                                            ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                            : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                                        }}
                                    "
                                >
                                    {{ $marca->activo ? 'Activa' : 'Inactiva' }}
                                </span>

                            </td>


                            {{-- Modelos --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $marca->total_modelos }}
                            </td>


                            {{-- Equipos --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $marca->total_equipos }}
                            </td>


                            {{-- Registro --}}
                            <td
                                class="
                                    px-3 py-3
                                    whitespace-nowrap
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $marca->fecha_creacion
                                    ? \Illuminate\Support\Carbon::parse($marca->fecha_creacion)->format('d/m/Y')
                                    : '—'
                                }}
                            </td>


                            {{-- Acciones --}}
                            <td class="px-3 py-3">

                                <div
                                    class="
                                        flex items-center justify-end
                                        gap-2
                                    "
                                >
                                    {{-- Editar --}}
                                    <a
                                        href="{{ route(
                                            'catalogos.edit',
                                            [
                                                'tipo' => 'marca',
                                                'registro' => $marca->id_marca,
                                            ]
                                        ) }}"
                                        class="
                                            text-[var(--theme-primary)]
                                            hover:text-[var(--theme-primary-hover)]
                                            transition-colors
                                        "
                                        title="Editar marca"
                                        aria-label="Editar marca {{ $marca->nombre }}"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path d="M4 20h4l11-11-4-4L4 16v4z"/>
                                            <path d="M13.5 6.5l4 4"/>
                                        </svg>
                                    </a>


                                    {{-- Más acciones --}}
                                    <div
                                        x-data="{ open: false }"
                                        @click.outside="open = false"
                                        class="relative"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="
                                                text-[var(--theme-text-muted)]
                                                hover:text-[var(--theme-text-strong)]
                                                transition-colors
                                            "
                                            title="Más acciones"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>


                                        <div
                                            x-show="open"
                                            x-cloak
                                            x-transition.opacity.duration.100ms
                                            class="
                                                absolute
                                                right-0 top-6
                                                w-40
                                                bg-[var(--theme-surface)]
                                                border border-[var(--theme-border)]
                                                rounded-md
                                                shadow-md
                                                z-20
                                                text-left
                                                overflow-hidden
                                            "
                                        >
                                            <button
                                                type="button"
                                                @click="
                                                    open = false;
                                                    $wire.toggleMarcaStatus({{ $marca->id_marca }});
                                                "

                                                @if ($marca->activo)
                                                    wire:confirm="¿Desactivar la marca {{ addslashes($marca->nombre) }}? Los registros existentes conservarán su relación."
                                                @endif

                                                class="
                                                    w-full
                                                    block
                                                    text-left
                                                    px-4 py-2
                                                    text-xs

                                                    {{ $marca->activo
                                                        ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                                        : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                                    }}
                                                "
                                            >
                                                {{ $marca->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </div>
                                    </div>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="
                                    px-5 py-12
                                    text-center
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                No se encontraron marcas con los filtros seleccionados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ========================================================
            TARJETAS - MÓVIL
        ======================================================== --}}
        <div class="md:hidden space-y-3">

            @forelse ($marcas as $marca)

                <article
                    wire:key="marca-card-{{ $marca->id_marca }}"
                    class="
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border)]
                        rounded-xl
                        p-4
                        shadow-sm
                    "
                >
                    {{-- Cabecera --}}
                    <div class="flex items-start gap-3">

                        <div class="min-w-0 flex-1">

                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    leading-snug
                                    text-[var(--theme-text-strong)]
                                    break-words
                                "
                            >
                                {{ $marca->nombre }}
                            </p>

                            <p class="text-xs text-[var(--theme-text-muted)] mt-1">
                                ID {{ $marca->id_marca }}
                            </p>

                        </div>


                        <div
                            x-data="{ open: false }"
                            @click.outside="open = false"
                            class="relative shrink-0"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                class="
                                    w-8 h-8
                                    flex items-center justify-center
                                    rounded-md
                                    text-[var(--theme-text)]
                                    hover:bg-[var(--theme-surface-soft)]
                                    transition-colors
                                "
                                aria-label="Más acciones"
                            >
                                <svg
                                    class="w-5 h-5"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>


                            <div
                                x-show="open"
                                x-cloak
                                class="
                                    absolute
                                    right-0 top-9
                                    w-40
                                    bg-[var(--theme-surface)]
                                    border border-[var(--theme-border)]
                                    rounded-lg
                                    shadow-lg
                                    z-20
                                    overflow-hidden
                                    text-left
                                "
                            >
                                <a
                                    href="{{ route(
                                        'catalogos.edit',
                                        [
                                            'tipo' => 'marca',
                                            'registro' => $marca->id_marca,
                                        ]
                                    ) }}"
                                    @click="open = false"
                                    class="
                                        block
                                        px-4 py-2.5
                                        text-xs
                                        text-[var(--theme-text)]
                                        hover:bg-[var(--theme-surface-soft)]
                                    "
                                >
                                    Editar
                                </a>


                                <button
                                    type="button"
                                    @click="
                                        open = false;
                                        $wire.toggleMarcaStatus({{ $marca->id_marca }});
                                    "

                                    @if ($marca->activo)
                                        wire:confirm="¿Desactivar la marca {{ addslashes($marca->nombre) }}? Los registros existentes conservarán su relación."
                                    @endif

                                    class="
                                        w-full
                                        block
                                        text-left
                                        px-4 py-2.5
                                        text-xs

                                        {{ $marca->activo
                                            ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                            : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                        }}
                                    "
                                >
                                    {{ $marca->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </div>

                    </div>


                    {{-- Información --}}
                    <div class="mt-3 space-y-2">

                        <p class="text-xs text-[var(--theme-text-muted)]">

                            <span>
                                Descripción:
                            </span>

                            <span class="text-[var(--theme-text)]">
                                {{ $marca->descripcion ?: 'Sin descripción' }}
                            </span>

                        </p>


                        <div class="grid grid-cols-2 gap-3">

                            <p class="text-xs text-[var(--theme-text-muted)]">
                                Modelos:
                                <span class="text-[var(--theme-text)]">
                                    {{ $marca->total_modelos }}
                                </span>
                            </p>

                            <p class="text-xs text-[var(--theme-text-muted)]">
                                Equipos:
                                <span class="text-[var(--theme-text)]">
                                    {{ $marca->total_equipos }}
                                </span>
                            </p>

                        </div>


                        <div class="flex items-center justify-between gap-3">

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    px-2 py-1
                                    rounded-full
                                    text-[10px]
                                    font-medium

                                    {{ $marca->activo
                                        ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                        : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                                    }}
                                "
                            >
                                {{ $marca->activo ? 'Activa' : 'Inactiva' }}
                            </span>


                            <span class="text-xs text-[var(--theme-text-muted)]">
                                {{ $marca->fecha_creacion
                                    ? \Illuminate\Support\Carbon::parse($marca->fecha_creacion)->format('d/m/Y')
                                    : '—'
                                }}
                            </span>

                        </div>

                    </div>
                </article>

            @empty

                <div
                    class="
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border)]
                        rounded-xl
                        px-5 py-12
                        text-center
                    "
                >
                    <p class="text-sm text-[var(--theme-text-muted)]">
                        No se encontraron marcas
                    </p>
                </div>

            @endforelse

        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($marcas->hasPages())

            {{-- Escritorio --}}
            <div
                class="
                    hidden sm:flex
                    items-center justify-end
                    gap-1
                    px-5 py-4
                    border-t border-[var(--theme-border)]
                "
            >
                <button
                    type="button"
                    wire:click="previousPage('marcasPage')"
                    @disabled($marcas->onFirstPage())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[var(--theme-text-muted)]

                        {{ $marcas->onFirstPage()
                            ? 'opacity-40'
                            : 'hover:bg-[var(--theme-surface-soft)]'
                        }}
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M15 6l-6 6 6 6"/>
                    </svg>
                </button>


                @php
                    $currentMarcaPage = $marcas->currentPage();
                    $lastMarcaPage = $marcas->lastPage();
                    $marcaWindow = 2;
                @endphp


                @for ($page = 1; $page <= min(2, $lastMarcaPage); $page++)

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'marcasPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentMarcaPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($currentMarcaPage > 4)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ...
                    </span>

                @endif


                @for (
                    $page = max(3, $currentMarcaPage - $marcaWindow);
                    $page <= min($lastMarcaPage - 2, $currentMarcaPage + $marcaWindow);
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'marcasPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentMarcaPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($currentMarcaPage < $lastMarcaPage - 3)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ...
                    </span>

                @endif


                @for (
                    $page = max($lastMarcaPage - 1, 3);
                    $page <= $lastMarcaPage;
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'marcasPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentMarcaPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                <button
                    type="button"
                    wire:click="nextPage('marcasPage')"
                    @disabled(! $marcas->hasMorePages())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[var(--theme-text-muted)]

                        {{ $marcas->hasMorePages()
                            ? 'hover:bg-[var(--theme-surface-soft)]'
                            : 'opacity-40'
                        }}
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M9 6l6 6-6 6"/>
                    </svg>
                </button>

            </div>


            {{-- Móvil --}}
            <div
                class="
                    flex sm:hidden
                    items-center justify-between
                    gap-3
                    mt-4
                    px-1 py-3
                "
            >
                <button
                    type="button"
                    wire:click="previousPage('marcasPage')"
                    @disabled($marcas->onFirstPage())
                    class="
                        h-9 px-3
                        rounded-lg
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-xs font-medium

                        {{ $marcas->onFirstPage()
                            ? 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                            : 'text-[var(--theme-text-muted)]'
                        }}
                    "
                >
                    Anterior
                </button>


                <span
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                        text-center
                    "
                >
                    Página

                    <span class="font-medium text-[var(--theme-text)]">
                        {{ $marcas->currentPage() }}
                    </span>

                    de

                    <span class="font-medium text-[var(--theme-text)]">
                        {{ $marcas->lastPage() }}
                    </span>
                </span>


                <button
                    type="button"
                    wire:click="nextPage('marcasPage')"
                    @disabled(! $marcas->hasMorePages())
                    class="
                        h-9 px-3
                        rounded-lg
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-xs font-medium

                        {{ $marcas->hasMorePages()
                            ? 'text-[var(--theme-text-muted)]'
                            : 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                        }}
                    "
                >
                    Siguiente
                </button>
            </div>

        @endif

    </div>


    {{-- ============================================================
        RESULTADOS - MODELOS
        Basado directamente en equipos-index.blade.php
    ============================================================ --}}
    <div
        class="
            relative

            bg-transparent
            border-0
            rounded-none
            overflow-visible

            md:bg-[var(--theme-surface)]
            md:border
            md:border-[var(--theme-border)]
            md:rounded-lg
            md:overflow-hidden
        "
    >
        {{-- Overlay mientras Livewire actualiza --}}
        <div
            wire:loading.delay
            wire:target="
                search,
                estado,
                tipo,
                perPageModelos,
                sortModelos,
                previousPage,
                nextPage,
                gotoPage,
                toggleModeloStatus,
                editModelo
            "
            class="
                absolute inset-0
                bg-[var(--theme-surface)] opacity-50
                z-10
                pointer-events-none
            "
        ></div>


        {{-- ========================================================
            BARRA DE RESULTADOS - ESCRITORIO
        ======================================================== --}}
        <div
            class="
                hidden md:flex
                items-center justify-between
                px-5 py-4
                border-b border-[var(--theme-border)]
            "
        >
            <p class="text-sm text-[var(--theme-text)]">

                Resultados:

                <span class="font-medium">
                    {{ number_format($modelos->total()) }}
                </span>

                {{ $modelos->total() === 1 ? 'modelo encontrado' : 'modelos encontrados' }}

            </p>


            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Mostrar
                    </span>

                    <select
                        wire:model.live="perPageModelos"
                        class="
                            text-xs
                            border border-[var(--theme-border-strong)]
                            rounded-md
                            px-2 py-1.5
                            bg-[var(--theme-surface)]
                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                        "
                    >
                        @foreach ([10, 25, 50, 100] as $option)

                            <option value="{{ $option }}">
                                {{ $option }}
                            </option>

                        @endforeach
                    </select>

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Por página
                    </span>

                </div>


                <select
                    wire:model.live="sortModelos"
                    class="
                        text-xs
                        border border-[var(--theme-border-strong)]
                        rounded-md
                        px-2 py-1.5
                        bg-[var(--theme-surface)]
                        uppercase
                        focus:ring-1
                        focus:ring-[var(--theme-primary)]
                    "
                >
                    <option value="asc">
                        ASC
                    </option>

                    <option value="desc">
                        DESC
                    </option>
                </select>

            </div>
        </div>


        {{-- ========================================================
            TABLA - ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b border-[var(--theme-border)]
                            text-left
                        "
                    >
                        <th class="px-5 py-3 w-10">

                            <input
                                type="checkbox"
                                onclick="
                                    this.closest('table')
                                        .querySelectorAll('tbody input[type=checkbox]')
                                        .forEach((checkbox) => checkbox.checked = this.checked)
                                "
                                class="rounded border-[var(--theme-border-strong)]"
                            >

                        </th>


                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            ID Modelo
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Modelo
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Marca
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Tipo de Equipo
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Descripción
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Estado
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Equipos
                        </th>

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">
                            Registro
                        </th>

                        <th
                            class="
                                px-3 py-3
                                font-medium
                                text-[var(--theme-text-muted)]
                                text-right
                            "
                        >
                            Acciones
                        </th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($modelos as $modelo)

                        <tr
                            wire:key="modelo-{{ $modelo->id_modelo }}"
                            class="
                                border-b border-[var(--theme-border)]
                                hover:bg-[var(--theme-primary-soft-subtle)]
                                transition-colors
                            "
                        >
                            <td class="px-5 py-3">

                                <input
                                    type="checkbox"
                                    value="{{ $modelo->id_modelo }}"
                                    class="
                                        rounded
                                        border-[var(--theme-border-strong)]
                                    "
                                >

                            </td>


                            {{-- ID --}}
                            <td
                                class="
                                    px-3 py-3
                                    font-medium
                                    text-[var(--theme-text)]
                                    whitespace-nowrap
                                "
                            >
                                {{ $modelo->id_modelo }}
                            </td>


                            {{-- Modelo --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $modelo->nombre }}
                            </td>


                            {{-- Marca --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $modelo->marca_nombre }}
                            </td>


                            {{-- Tipo --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $modelo->tipo_equipo_nombre }}
                            </td>


                            {{-- Descripción --}}
                            <td
                                class="
                                    px-3 py-3
                                    max-w-[300px]
                                    text-[var(--theme-text)]
                                "
                            >
                                <span
                                    class="block truncate"
                                    title="{{ $modelo->descripcion ?: 'Sin descripción' }}"
                                >
                                    {{ $modelo->descripcion ?: 'Sin descripción' }}
                                </span>
                            </td>


                            {{-- Estado --}}
                            <td class="px-3 py-3">

                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        px-2 py-1
                                        rounded-full
                                        text-[10px]
                                        font-medium

                                        {{ $modelo->activo
                                            ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                            : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                                        }}
                                    "
                                >
                                    {{ $modelo->activo ? 'Activo' : 'Inactivo' }}
                                </span>

                            </td>


                            {{-- Equipos --}}
                            <td class="px-3 py-3 text-[var(--theme-text)]">
                                {{ $modelo->total_equipos }}
                            </td>


                            {{-- Registro --}}
                            <td
                                class="
                                    px-3 py-3
                                    whitespace-nowrap
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $modelo->fecha_creacion
                                    ? \Illuminate\Support\Carbon::parse($modelo->fecha_creacion)->format('d/m/Y')
                                    : '—'
                                }}
                            </td>


                            {{-- Acciones --}}
                            <td class="px-3 py-3">

                                <div
                                    class="
                                        flex items-center justify-end
                                        gap-2
                                    "
                                >
                                    {{-- Editar --}}
                                    <a
                                        href="{{ route(
                                            'catalogos.edit',
                                            [
                                                'tipo' => 'modelo',
                                                'registro' => $modelo->id_modelo,
                                            ]
                                        ) }}"
                                        class="
                                            text-[var(--theme-primary)]
                                            hover:text-[var(--theme-primary-hover)]
                                            transition-colors
                                        "
                                        title="Editar modelo"
                                        aria-label="Editar modelo {{ $modelo->nombre }}"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path d="M4 20h4l11-11-4-4L4 16v4z"/>
                                            <path d="M13.5 6.5l4 4"/>
                                        </svg>
                                    </a>


                                    {{-- Más acciones --}}
                                    <div
                                        x-data="{ open: false }"
                                        @click.outside="open = false"
                                        class="relative"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="
                                                text-[var(--theme-text-muted)]
                                                hover:text-[var(--theme-text-strong)]
                                                transition-colors
                                            "
                                            title="Más acciones"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>


                                        <div
                                            x-show="open"
                                            x-cloak
                                            x-transition.opacity.duration.100ms
                                            class="
                                                absolute
                                                right-0 top-6
                                                w-40
                                                bg-[var(--theme-surface)]
                                                border border-[var(--theme-border)]
                                                rounded-md
                                                shadow-md
                                                z-20
                                                text-left
                                                overflow-hidden
                                            "
                                        >
                                            <button
                                                type="button"
                                                @click="
                                                    open = false;
                                                    $wire.toggleModeloStatus({{ $modelo->id_modelo }});
                                                "

                                                @if ($modelo->activo)
                                                    wire:confirm="¿Desactivar el modelo {{ addslashes($modelo->nombre) }}? Los equipos existentes conservarán su relación."
                                                @endif

                                                class="
                                                    w-full
                                                    block
                                                    text-left
                                                    px-4 py-2
                                                    text-xs

                                                    {{ $modelo->activo
                                                        ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                                        : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                                    }}
                                                "
                                            >
                                                {{ $modelo->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </div>
                                    </div>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="10"
                                class="
                                    px-5 py-12
                                    text-center
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                No se encontraron modelos con los filtros seleccionados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ========================================================
            TARJETAS - MÓVIL
        ======================================================== --}}
        <div class="md:hidden space-y-3">

            @forelse ($modelos as $modelo)

                <article
                    wire:key="modelo-card-{{ $modelo->id_modelo }}"
                    class="
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border)]
                        rounded-xl
                        p-4
                        shadow-sm
                    "
                >
                    {{-- Cabecera --}}
                    <div class="flex items-start gap-3">

                        <div class="min-w-0 flex-1">

                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    leading-snug
                                    text-[var(--theme-text-strong)]
                                    break-words
                                "
                            >
                                {{ $modelo->nombre }}
                            </p>

                            <p class="text-xs text-[var(--theme-text-muted)] mt-1">
                                {{ $modelo->marca_nombre }}
                                · ID {{ $modelo->id_modelo }}
                            </p>

                        </div>


                        <div
                            x-data="{ open: false }"
                            @click.outside="open = false"
                            class="relative shrink-0"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                class="
                                    w-8 h-8
                                    flex items-center justify-center
                                    rounded-md
                                    text-[var(--theme-text)]
                                    hover:bg-[var(--theme-surface-soft)]
                                    transition-colors
                                "
                                aria-label="Más acciones"
                            >
                                <svg
                                    class="w-5 h-5"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>


                            <div
                                x-show="open"
                                x-cloak
                                class="
                                    absolute
                                    right-0 top-9
                                    w-40
                                    bg-[var(--theme-surface)]
                                    border border-[var(--theme-border)]
                                    rounded-lg
                                    shadow-lg
                                    z-20
                                    overflow-hidden
                                    text-left
                                "
                            >
                                <a
                                    href="{{ route(
                                        'catalogos.edit',
                                        [
                                            'tipo' => 'modelo',
                                            'registro' => $modelo->id_modelo,
                                        ]
                                    ) }}"
                                    @click="open = false"
                                    class="
                                        block
                                        px-4 py-2.5
                                        text-xs
                                        text-[var(--theme-text)]
                                        hover:bg-[var(--theme-surface-soft)]
                                    "
                                >
                                    Editar
                                </a>


                                <button
                                    type="button"
                                    @click="
                                        open = false;
                                        $wire.toggleModeloStatus({{ $modelo->id_modelo }});
                                    "

                                    @if ($modelo->activo)
                                        wire:confirm="¿Desactivar el modelo {{ addslashes($modelo->nombre) }}? Los equipos existentes conservarán su relación."
                                    @endif

                                    class="
                                        w-full
                                        block
                                        text-left
                                        px-4 py-2.5
                                        text-xs

                                        {{ $modelo->activo
                                            ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                            : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                        }}
                                    "
                                >
                                    {{ $modelo->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </div>

                    </div>


                    {{-- Información --}}
                    <div class="mt-3 space-y-2">

                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Tipo:
                            <span class="text-[var(--theme-text)]">
                                {{ $modelo->tipo_equipo_nombre }}
                            </span>
                        </p>


                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Descripción:
                            <span class="text-[var(--theme-text)]">
                                {{ $modelo->descripcion ?: 'Sin descripción' }}
                            </span>
                        </p>


                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Equipos:
                            <span class="text-[var(--theme-text)]">
                                {{ $modelo->total_equipos }}
                            </span>
                        </p>


                        <div class="flex items-center justify-between gap-3">

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    px-2 py-1
                                    rounded-full
                                    text-[10px]
                                    font-medium

                                    {{ $modelo->activo
                                        ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                        : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                                    }}
                                "
                            >
                                {{ $modelo->activo ? 'Activo' : 'Inactivo' }}
                            </span>


                            <span class="text-xs text-[var(--theme-text-muted)]">
                                {{ $modelo->fecha_creacion
                                    ? \Illuminate\Support\Carbon::parse($modelo->fecha_creacion)->format('d/m/Y')
                                    : '—'
                                }}
                            </span>

                        </div>

                    </div>
                </article>

            @empty

                <div
                    class="
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border)]
                        rounded-xl
                        px-5 py-12
                        text-center
                    "
                >
                    <p class="text-sm text-[var(--theme-text-muted)]">
                        No se encontraron modelos
                    </p>
                </div>

            @endforelse

        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($modelos->hasPages())

            {{-- Escritorio --}}
            <div
                class="
                    hidden sm:flex
                    items-center justify-end
                    gap-1
                    px-5 py-4
                    border-t border-[var(--theme-border)]
                "
            >
                <button
                    type="button"
                    wire:click="previousPage('modelosPage')"
                    @disabled($modelos->onFirstPage())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[var(--theme-text-muted)]

                        {{ $modelos->onFirstPage()
                            ? 'opacity-40'
                            : 'hover:bg-[var(--theme-surface-soft)]'
                        }}
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M15 6l-6 6 6 6"/>
                    </svg>
                </button>


                @php
                    $currentModeloPage = $modelos->currentPage();
                    $lastModeloPage = $modelos->lastPage();
                    $modeloWindow = 2;
                @endphp


                @for ($page = 1; $page <= min(2, $lastModeloPage); $page++)

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'modelosPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentModeloPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($currentModeloPage > 4)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ...
                    </span>

                @endif


                @for (
                    $page = max(3, $currentModeloPage - $modeloWindow);
                    $page <= min($lastModeloPage - 2, $currentModeloPage + $modeloWindow);
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'modelosPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentModeloPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($currentModeloPage < $lastModeloPage - 3)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ...
                    </span>

                @endif


                @for (
                    $page = max($lastModeloPage - 1, 3);
                    $page <= $lastModeloPage;
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }}, 'modelosPage')"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $currentModeloPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                <button
                    type="button"
                    wire:click="nextPage('modelosPage')"
                    @disabled(! $modelos->hasMorePages())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[var(--theme-text-muted)]

                        {{ $modelos->hasMorePages()
                            ? 'hover:bg-[var(--theme-surface-soft)]'
                            : 'opacity-40'
                        }}
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M9 6l6 6-6 6"/>
                    </svg>
                </button>

            </div>


            {{-- Móvil --}}
            <div
                class="
                    flex sm:hidden
                    items-center justify-between
                    gap-3
                    mt-4
                    px-1 py-3
                "
            >
                <button
                    type="button"
                    wire:click="previousPage('modelosPage')"
                    @disabled($modelos->onFirstPage())
                    class="
                        h-9 px-3
                        rounded-lg
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-xs font-medium

                        {{ $modelos->onFirstPage()
                            ? 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                            : 'text-[var(--theme-text-muted)]'
                        }}
                    "
                >
                    Anterior
                </button>


                <span
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                        text-center
                    "
                >
                    Página

                    <span class="font-medium text-[var(--theme-text)]">
                        {{ $modelos->currentPage() }}
                    </span>

                    de

                    <span class="font-medium text-[var(--theme-text)]">
                        {{ $modelos->lastPage() }}
                    </span>
                </span>


                <button
                    type="button"
                    wire:click="nextPage('modelosPage')"
                    @disabled(! $modelos->hasMorePages())
                    class="
                        h-9 px-3
                        rounded-lg
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-xs font-medium

                        {{ $modelos->hasMorePages()
                            ? 'text-[var(--theme-text-muted)]'
                            : 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                        }}
                    "
                >
                    Siguiente
                </button>
            </div>

        @endif

    </div>


</div>