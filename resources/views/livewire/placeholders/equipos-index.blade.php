<div>

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


        {{-- ========================================================
            RESULTADOS MÓVIL
        ======================================================== --}}
        <div class="animate-pulse space-y-3">

            @for ($i = 0; $i < 5; $i++)
                <article
                    class="
                        bg-[var(--theme-surface)]

                        border
                        border-[var(--theme-border)]

                        rounded-xl

                        p-4

                        shadow-sm
                    "
                >

                    {{-- Cabecera --}}
                    <div class="flex items-start gap-3">

                        {{-- Selector --}}
                        <div class="shrink-0 pt-0.5">
                            <div
                                class="
                                    w-5
                                    h-5

                                    rounded-full

                                    border
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface)]
                                "
                            ></div>
                        </div>


                        {{-- Información principal --}}
                        <div class="flex-1 min-w-0">

                            <div
                                class="
                                    h-4
                                    w-3/4

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-20

                                    mt-2

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>


                        {{-- Menú --}}
                        <div
                            class="
                                shrink-0

                                flex
                                flex-col
                                items-center

                                gap-1

                                pt-1
                            "
                        >
                            <div class="w-1 h-1 rounded-full bg-[var(--theme-border-strong)]"></div>
                            <div class="w-1 h-1 rounded-full bg-[var(--theme-border-strong)]"></div>
                            <div class="w-1 h-1 rounded-full bg-[var(--theme-border-strong)]"></div>
                        </div>

                    </div>


                    {{-- Información --}}
                    <div class="mt-4 space-y-3">

                        {{-- SN --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="
                                    h-3
                                    w-7

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-24

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>


                        {{-- Service Tag --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="
                                    h-3
                                    w-20

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-20

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>


                        {{-- IP --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="
                                    h-3
                                    w-6

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-24

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>


                        {{-- Estado --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="
                                    h-3
                                    w-12

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-5
                                    w-20

                                    rounded-full

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>


                        {{-- Área --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="
                                    h-3
                                    w-10

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-28

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>

                    </div>

                </article>
            @endfor

        </div>


        {{-- ========================================================
            PAGINACIÓN MÓVIL
        ======================================================== --}}
        <div
            class="
                animate-pulse

                flex
                items-center
                justify-between

                gap-3

                mt-4

                px-1
                py-3
            "
        >
            <div
                class="
                    h-9
                    w-20

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]
                "
            ></div>

            <div
                class="
                    h-3
                    w-20

                    rounded

                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-9
                    w-20

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]
                "
            ></div>
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
                        Área / Departamento
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
                            placeholder="Buscar área..."
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


        {{-- ========================================================
            RESULTADOS
        ======================================================== --}}
        <div
            class="
                animate-pulse

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                overflow-hidden
            "
        >

            {{-- ====================================================
                BARRA SUPERIOR
            ==================================================== --}}
            <div
                class="
                    flex
                    items-center
                    justify-between

                    px-5
                    py-4

                    border-b
                    border-[var(--theme-border)]
                "
            >

                {{-- Resultados --}}
                <div class="flex items-center gap-2">
                    <span
                        class="
                            text-sm
                            text-[var(--theme-text)]
                        "
                    >
                        Resultados:
                    </span>

                    <div
                        class="
                            h-4
                            w-8

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <span
                        class="
                            text-sm
                            text-[var(--theme-text)]
                        "
                    >
                        Equipos encontrados
                    </span>
                </div>


                {{-- Controles --}}
                <div class="flex items-center gap-4">

                    {{-- Por página --}}
                    <div class="flex items-center gap-2">
                        <span
                            class="
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Mostrar
                        </span>

                        <div
                            class="
                                h-7
                                w-12

                                rounded-md

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <span
                            class="
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Por página
                        </span>
                    </div>


                    {{-- Orden --}}
                    <div
                        class="
                            h-7
                            w-16

                            rounded-md

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>

            </div>


            {{-- ====================================================
                TABLA
            ==================================================== --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">

                    {{-- Encabezados --}}
                    <thead>
                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]
                            "
                        >
                            <th class="px-5 py-3 w-10">
                                <div
                                    class="
                                        w-4
                                        h-4

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-16

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-24

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-14

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-16

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-24

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-20

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-20

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-14

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-28

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-16

                                        ml-auto

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>
                        </tr>
                    </thead>


                    {{-- Filas --}}
                    <tbody>
                        @for ($row = 0; $row < 8; $row++)
                            <tr
                                class="
                                    border-b
                                    border-[var(--theme-border)]
                                "
                            >
                                {{-- Checkbox --}}
                                <td class="px-5 py-3">
                                    <div
                                        class="
                                            w-4
                                            h-4

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Código --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-20

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Tipo --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-24

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Marca --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-16

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Modelo --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-20

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Serie --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-24

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Service Tag --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-20

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- IP --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-20

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Estado --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-5
                                            w-20

                                            rounded-full

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Área --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            h-3
                                            w-28

                                            rounded

                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>
                                </td>


                                {{-- Acciones --}}
                                <td class="px-3 py-3">
                                    <div
                                        class="
                                            flex
                                            items-center
                                            justify-end

                                            gap-2
                                        "
                                    >
                                        <div
                                            class="
                                                w-4
                                                h-4

                                                rounded

                                                bg-[var(--theme-surface-soft)]
                                            "
                                        ></div>

                                        <div
                                            class="
                                                w-4
                                                h-4

                                                rounded

                                                bg-[var(--theme-surface-soft)]
                                            "
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>

                </table>
            </div>


            {{-- ====================================================
                PAGINACIÓN
            ==================================================== --}}
            <div
                class="
                    flex
                    items-center
                    justify-end

                    gap-1

                    px-5
                    py-4

                    border-t
                    border-[var(--theme-border)]
                "
            >
                @for ($i = 0; $i < 7; $i++)
                    <div
                        class="
                            w-8
                            h-8

                            rounded-full

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                @endfor
            </div>

        </div>

    </div>

</div>