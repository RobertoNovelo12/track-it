<div class="space-y-5">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-[var(--theme-text-strong)]">
                Gestión de marcas y modelos
            </h1>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Inicio

                <span class="mx-1">
                    &gt;
                </span>

                Catalogo
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
                    disabled
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

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
            </div>


            {{-- ESTADO --}}
            <div>
                <select
                    disabled
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

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    <option>
                        Todos los estados
                    </option>

                    <option>
                        Activos
                    </option>

                    <option>
                        Inactivos
                    </option>
                </select>
            </div>


            {{-- TIPO --}}
            <div>
                <select
                    disabled
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

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    <option>
                        Todos los tipos
                    </option>
                </select>
            </div>

        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS
        Solo cargan los números.
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
                    <div class="animate-pulse">
                        <div
                            class="
                                h-7
                                w-12

                                rounded

                                bg-[var(--theme-primary-soft)]
                            "
                        ></div>
                    </div>

                    <p
                        class="
                            mt-1

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
                    <div class="animate-pulse">
                        <div
                            class="
                                h-7
                                w-12

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>
                    </div>

                    <p
                        class="
                            mt-1

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
                    <div class="animate-pulse">
                        <div
                            class="
                                h-7
                                w-12

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>
                    </div>

                    <p
                        class="
                            mt-1

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
                    <div class="animate-pulse">
                        <div
                            class="
                                h-7
                                w-12

                                rounded

                                bg-[var(--theme-primary-soft)]
                            "
                        ></div>
                    </div>

                    <p
                        class="
                            mt-1

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
    ============================================================ --}}
    <section
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

        {{-- ========================================================
            BARRA SUPERIOR - ESCRITORIO
        ======================================================== --}}
        <div
            class="
                hidden
                md:flex

                items-center
                justify-between

                px-5
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <div class="flex items-center gap-1 text-sm text-[var(--theme-text)]">
                <span>
                    Resultados:
                </span>

                <div class="animate-pulse">
                    <div
                        class="
                            h-4
                            w-7

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

                <span>
                    marcas encontradas
                </span>
            </div>


            <div class="flex items-center gap-4">

                {{-- POR PÁGINA --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Mostrar
                    </span>

                    <select
                        disabled
                        class="
                            text-xs

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-2
                            py-1.5

                            bg-[var(--theme-surface)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >
                        <option>
                            10
                        </option>
                    </select>

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Por página
                    </span>
                </div>


                {{-- ORDEN --}}
                <select
                    disabled
                    class="
                        text-xs

                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-2
                        py-1.5

                        bg-[var(--theme-surface)]

                        uppercase

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
                    <option>
                        ASC
                    </option>
                </select>

            </div>
        </div>


        {{-- ========================================================
            TABLA MARCAS - ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">

                {{-- Encabezados conocidos --}}
                <thead>
                    <tr
                        class="
                            border-b
                            border-[var(--theme-border)]

                            text-left
                        "
                    >
                        <th class="px-5 py-3 w-10">
                            <input
                                type="checkbox"
                                disabled
                                class="
                                    rounded
                                    border-[var(--theme-border-strong)]
                                    disabled:opacity-100
                                "
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

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)] text-right">
                            Acciones
                        </th>
                    </tr>
                </thead>


                {{-- Solo los registros son desconocidos --}}
                <tbody class="animate-pulse">
                    @for ($row = 0; $row < 8; $row++)
                        <tr class="border-b border-[var(--theme-border)]">

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

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-8

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

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

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-36
                                        max-w-full

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-5
                                        w-14

                                        rounded-full

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-8

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-8

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

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

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
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


        {{-- ========================================================
            MARCAS - MÓVIL
        ======================================================== --}}
        <div class="md:hidden space-y-3 animate-pulse">

            @for ($row = 0; $row < 4; $row++)
                <article
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]
                    "
                >
                    <div class="space-y-3">

                        <div class="flex items-center justify-between gap-3">
                            <div class="space-y-2">
                                <div
                                    class="
                                        h-4
                                        w-28

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>

                                <div
                                    class="
                                        h-3
                                        w-16

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </div>

                            <div
                                class="
                                    h-6
                                    w-14

                                    rounded-full

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>

                        <div
                            class="
                                h-3
                                w-full

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div class="grid grid-cols-2 gap-3">
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

                    </div>
                </article>
            @endfor

        </div>


        {{-- ========================================================
            PAGINACIÓN MARCAS
        ======================================================== --}}
        <div
            class="
                hidden
                sm:flex

                items-center
                justify-end

                gap-1

                px-5
                py-4

                border-t
                border-[var(--theme-border)]
            "
        >
            <div class="animate-pulse flex items-center gap-1">
                @for ($i = 0; $i < 6; $i++)
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

    </section>


    {{-- ============================================================
        RESULTADOS - MODELOS
    ============================================================ --}}
    <section
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

        {{-- ========================================================
            BARRA SUPERIOR
        ======================================================== --}}
        <div
            class="
                hidden
                md:flex

                items-center
                justify-between

                px-5
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <div class="flex items-center gap-1 text-sm text-[var(--theme-text)]">
                <span>
                    Resultados:
                </span>

                <div class="animate-pulse">
                    <div
                        class="
                            h-4
                            w-7

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

                <span>
                    modelos encontrados
                </span>
            </div>


            <div class="flex items-center gap-4">

                {{-- POR PÁGINA --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Mostrar
                    </span>

                    <select
                        disabled
                        class="
                            text-xs

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-2
                            py-1.5

                            bg-[var(--theme-surface)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >
                        <option>
                            10
                        </option>
                    </select>

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Por página
                    </span>
                </div>


                {{-- ORDEN --}}
                <select
                    disabled
                    class="
                        text-xs

                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-2
                        py-1.5

                        bg-[var(--theme-surface)]

                        uppercase

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
                    <option>
                        ASC
                    </option>
                </select>

            </div>
        </div>


        {{-- ========================================================
            TABLA MODELOS - ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">

                {{-- Encabezados conocidos --}}
                <thead>
                    <tr
                        class="
                            border-b
                            border-[var(--theme-border)]

                            text-left
                        "
                    >
                        <th class="px-5 py-3 w-10">
                            <input
                                type="checkbox"
                                disabled
                                class="
                                    rounded
                                    border-[var(--theme-border-strong)]
                                    disabled:opacity-100
                                "
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

                        <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)] text-right">
                            Acciones
                        </th>
                    </tr>
                </thead>


                {{-- Registros --}}
                <tbody class="animate-pulse">
                    @for ($row = 0; $row < 8; $row++)
                        <tr class="border-b border-[var(--theme-border)]">

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

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-8

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

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

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-32
                                        max-w-full

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-5
                                        w-14

                                        rounded-full

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        w-8

                                        rounded

                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

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

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
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


        {{-- ========================================================
            MODELOS - MÓVIL
        ======================================================== --}}
        <div class="md:hidden space-y-3 animate-pulse">

            @for ($row = 0; $row < 4; $row++)
                <article
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]
                    "
                >
                    <div class="space-y-3">

                        <div class="flex items-center justify-between gap-3">
                            <div class="space-y-2">
                                <div
                                    class="
                                        h-4
                                        w-32

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

                            <div
                                class="
                                    h-6
                                    w-14

                                    rounded-full

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>

                        <div
                            class="
                                h-3
                                w-full

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div class="grid grid-cols-2 gap-3">
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

                    </div>
                </article>
            @endfor

        </div>


        {{-- ========================================================
            PAGINACIÓN MODELOS
        ======================================================== --}}
        <div
            class="
                hidden
                sm:flex

                items-center
                justify-end

                gap-1

                px-5
                py-4

                border-t
                border-[var(--theme-border)]
            "
        >
            <div class="animate-pulse flex items-center gap-1">
                @for ($i = 0; $i < 6; $i++)
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

    </section>

</div>