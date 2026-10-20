{{-- Mismo x-data que la raíz de livewire/equipos-index: al reemplazar este
     placeholder por el componente (carga diferida), el estado ya existe y
     Alpine no evalúa «filtersOpen» antes de tiempo. --}}
<div
    x-data="{
        filtersOpen: false
    }"
>

    {{-- ============================================================
        PLACEHOLDER MÓVIL
    ============================================================ --}}
    <div class="md:hidden">

        {{-- ========================================================
            BUSCADOR GENERAL
        ======================================================== --}}
        <div class="relative mb-3">
            <svg
                class="
                    absolute
                    left-4
                    top-1/2
                    -translate-y-1/2

                    w-5
                    h-5

                    text-[var(--theme-text-muted)]

                    pointer-events-none
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7"/>
                <path d="M20 20l-4-4"/>
            </svg>

            <input
                type="text"
                disabled
                placeholder="Buscar equipos..."
                class="
                    w-full
                    h-12

                    pl-12
                    pr-10

                    rounded-lg

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
        </div>


        {{-- ========================================================
            FILTRAR / ORDENAR
        ======================================================== --}}
        <div class="flex items-center gap-3 mb-5">

            {{-- Filtros --}}
            <button
                type="button"
                disabled
                class="
                    h-11

                    flex
                    items-center
                    gap-2

                    px-4

                    rounded-lg

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text)]

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M4 5h16l-6 7v5l-4 2v-7z"/>
                </svg>

                <span>
                    Filtros
                </span>
            </button>


            {{-- Ordenar --}}
            <button
                type="button"
                disabled
                class="
                    h-11

                    flex
                    items-center
                    gap-2

                    px-4

                    rounded-lg

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text)]

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M8 4v16"/>
                    <path d="M5 7l3-3 3 3"/>

                    <path d="M16 20V4"/>
                    <path d="M13 17l3 3 3-3"/>
                </svg>

                <span>
                    Ordenar
                </span>
            </button>

        </div>


    </div>


    {{-- ============================================================
        PLACEHOLDER ESCRITORIO
    ============================================================ --}}
    <div class="hidden md:block">

        {{-- ========================================================
            FILTROS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                p-6
                mb-6
            "
        >

            {{-- Encabezado --}}
            <div class="flex items-center gap-2 mb-5">
                <svg
                    class="
                        w-4
                        h-4

                        text-[var(--theme-text-muted)]
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path d="M4 6h16"/>
                    <path d="M7 6v2a2 2 0 002 2h6a2 2 0 002-2V6"/>
                    <path d="M4 18h16"/>
                    <path d="M9 18v-2a2 2 0 012-2h2a2 2 0 012 2v2"/>
                </svg>

                <span
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text)]
                    "
                >
                    Filtros de búsqueda
                </span>
            </div>


            {{-- ====================================================
                PRIMERA FILA
            ==================================================== --}}
            <div
                class="
                    grid
                    grid-cols-1

                    sm:grid-cols-2
                    lg:grid-cols-4

                    gap-4
                    mb-4
                "
            >

                {{-- Tipo de activo --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Tipo de activo
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled
                            placeholder="Buscar tipo..."
                            class="
                                w-full
                                min-w-0

                                border-0
                                bg-transparent

                                pl-3
                                pr-10
                                py-2

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-0

                                disabled:cursor-default
                                disabled:opacity-100
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-2.5

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>


                {{-- Marca --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Marca
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled
                            placeholder="Buscar marca..."
                            class="
                                w-full
                                min-w-0

                                border-0
                                bg-transparent

                                pl-3
                                pr-10
                                py-2

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-0

                                disabled:cursor-default
                                disabled:opacity-100
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-2.5

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>


                {{-- Modelo --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Modelo
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled
                            placeholder="Buscar modelo..."
                            class="
                                w-full
                                min-w-0

                                border-0
                                bg-transparent

                                pl-3
                                pr-10
                                py-2

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-0

                                disabled:cursor-default
                                disabled:opacity-100
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-2.5

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>


                {{-- Número de serie --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Número de serie
                    </label>

                    <input
                        type="text"
                        disabled
                        placeholder="Buscar..."
                        class="
                            w-full

                            text-sm
                            text-[var(--theme-text)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2

                            bg-[var(--theme-surface)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:cursor-default
                            disabled:opacity-100
                        "
                    >
                </div>

            </div>


            {{-- ====================================================
                SEGUNDA FILA
            ==================================================== --}}
            <div
                class="
                    grid
                    grid-cols-1

                    sm:grid-cols-2
                    lg:grid-cols-4

                    gap-4
                "
            >

                {{-- Service Tag --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Service Tag
                    </label>

                    <input
                        type="text"
                        disabled
                        placeholder="Buscar..."
                        class="
                            w-full

                            text-sm
                            text-[var(--theme-text)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2

                            bg-[var(--theme-surface)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:cursor-default
                            disabled:opacity-100
                        "
                    >
                </div>


                {{-- Dirección IP --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Dirección IP
                    </label>

                    <input
                        type="text"
                        disabled
                        placeholder="Buscar..."
                        class="
                            w-full

                            text-sm
                            text-[var(--theme-text)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2

                            bg-[var(--theme-surface)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:cursor-default
                            disabled:opacity-100
                        "
                    >
                </div>


                {{-- Estado --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Estado
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled
                            placeholder="Buscar estado..."
                            class="
                                w-full
                                min-w-0

                                border-0
                                bg-transparent

                                pl-3
                                pr-10
                                py-2

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-0

                                disabled:cursor-default
                                disabled:opacity-100
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-2.5

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>


                {{-- Área --}}
                <div>
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] }} / Departamento
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled
                            placeholder="{{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['buscar_unidad'] }}"
                            class="
                                w-full
                                min-w-0

                                border-0
                                bg-transparent

                                pl-3
                                pr-10
                                py-2

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-0

                                disabled:cursor-default
                                disabled:opacity-100
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-2.5

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>

            </div>


            {{-- ====================================================
                BOTONES
            ==================================================== --}}
            <div class="flex items-center justify-end gap-3 mt-5">

                <button
                    type="button"
                    disabled
                    class="
                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-4
                        py-2

                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]

                        bg-[var(--theme-surface)]

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    Limpiar filtros
                </button>


                <button
                    type="button"
                    disabled
                    class="
                        flex
                        items-center
                        gap-2

                        bg-[var(--theme-primary)]

                        rounded-md

                        px-4
                        py-2

                        text-sm
                        font-medium
                        text-white

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-4-4"/>
                    </svg>

                    Aplicar filtros
                </button>

            </div>

        </div>

    </div>


    {{-- ============================================================
        RESULTADOS (móvil y escritorio)
        Mismas columnas y anchos que livewire/equipos/results/section.
        Las filas siguen la cantidad por página (máximo 8).
    ============================================================ --}}
    @php
        $placeholderPerPage = (int) request(
            'per_page',
            app(\App\Services\PreferenciasUsuarioService::class)->registrosPorPagina()
        );
    @endphp

    <x-ui.skeleton-table
        selectable
        min-width="1500px"
        :rows="max(5, min($placeholderPerPage, 8))"
        :columns="[
            ['label' => 'ID Equipo', 'width' => '140px'],
            ['label' => 'Nombre de equipo', 'width' => '210px'],
            ['label' => 'Tipo de equipo', 'width' => '145px'],
            ['label' => 'Marca', 'width' => '110px'],
            ['label' => 'Modelo', 'width' => '165px'],
            ['label' => 'Número de serie', 'width' => '175px'],
            ['label' => 'Dirección IP', 'width' => '125px'],
            ['label' => 'Estado', 'width' => '125px', 'type' => 'status'],
            ['label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] . ' / Departamento', 'width' => '225px'],
        ]"
    />

</div>
