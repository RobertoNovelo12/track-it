<div class="space-y-5">

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
                Inicio

                <span class="mx-1">
                    &gt;
                </span>

                Catálogo
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

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
            </div>


            {{-- ESTADO --}}
            <div
                class="
                    h-10
                    px-3

                    flex
                    items-center
                    justify-between

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface-soft)]

                    text-xs
                    text-[var(--theme-text)]
                "
            >
                <span>Todos los estados</span>

                <svg
                    class="w-4 h-4 text-[var(--theme-text-muted)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M7 10l5 5 5-5"/>
                </svg>
            </div>


            {{-- TIPO --}}
            <div
                class="
                    h-10
                    px-3

                    flex
                    items-center
                    justify-between

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface-soft)]

                    text-xs
                    text-[var(--theme-text)]
                "
            >
                <span>Todos los tipos</span>

                <svg
                    class="w-4 h-4 text-[var(--theme-text-muted)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M7 10l5 5 5-5"/>
                </svg>
            </div>
        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    @php
        $metricLabels = [
            'Total de marcas',
            'Total de modelos',
            'Marcas inactivas',
            'Modelos en uso',
        ];
    @endphp

    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-2
            xl:grid-cols-4

            gap-3
        "
    >
        @foreach ($metricLabels as $metricLabel)
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
                    <div class="min-w-0 flex-1">
                        {{-- DATO DINÁMICO --}}
                        <div
                            class="
                                h-7
                                w-12

                                rounded

                                bg-[var(--theme-surface-soft)]

                                animate-pulse
                            "
                        ></div>

                        <p
                            class="
                                mt-1

                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $metricLabel }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
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
        {{-- BARRA SUPERIOR --}}
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
            <p class="text-sm text-[var(--theme-text)]">
                Resultados:

                <span
                    class="
                        inline-block
                        align-middle

                        ml-1

                        h-4
                        w-8

                        rounded

                        bg-[var(--theme-surface-soft)]

                        animate-pulse
                    "
                ></span>

                <span class="ml-1">
                    marcas
                </span>
            </p>


            <div class="flex items-center gap-4">
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
                            h-8
                            min-w-14
                            px-2

                            flex
                            items-center
                            justify-between

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface-soft)]

                            text-xs
                            text-[var(--theme-text)]
                        "
                    >
                        <span>10</span>

                        <svg
                            class="w-3.5 h-3.5 text-[var(--theme-text-muted)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path d="M7 10l5 5 5-5"/>
                        </svg>
                    </div>

                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        por página
                    </span>
                </div>


                <div
                    class="
                        h-8
                        min-w-16
                        px-2

                        flex
                        items-center
                        justify-between

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface-soft)]

                        text-xs
                        text-[var(--theme-text)]
                    "
                >
                    <span>ASC</span>

                    <svg
                        class="w-3.5 h-3.5 text-[var(--theme-text-muted)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M7 10l5 5 5-5"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- TABLA ESCRITORIO --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
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

                                    border
                                    border-[var(--theme-border-strong)]
                                "
                            ></div>
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
                                px-3
                                py-3

                                font-medium
                                text-[var(--theme-text-muted)]
                                text-right
                            "
                        >
                            Acciones
                        </th>
                    </tr>
                </thead>


                <tbody class="animate-pulse">
                    @for ($row = 0; $row < 10; $row++)
                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]
                            "
                        >
                            <td class="px-5 py-3">
                                <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-36 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>


        {{-- TARJETAS MÓVIL --}}
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
                                <div class="h-4 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                                <div class="h-3 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                        </div>

                        <div class="h-3 w-full rounded bg-[var(--theme-surface-soft)]"></div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                        </div>
                    </div>
                </article>
            @endfor
        </div>


        {{-- PAGINACIÓN DEPENDIENTE DE DATOS --}}
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

                animate-pulse
            "
        >
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
        {{-- BARRA SUPERIOR --}}
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
            <p class="text-sm text-[var(--theme-text)]">
                Resultados:

                <span
                    class="
                        inline-block
                        align-middle

                        ml-1

                        h-4
                        w-8

                        rounded

                        bg-[var(--theme-surface-soft)]

                        animate-pulse
                    "
                ></span>

                <span class="ml-1">
                    modelos
                </span>
            </p>


            <div class="flex items-center gap-4">
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
                            h-8
                            min-w-14
                            px-2

                            flex
                            items-center
                            justify-between

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface-soft)]

                            text-xs
                            text-[var(--theme-text)]
                        "
                    >
                        <span>10</span>

                        <svg
                            class="w-3.5 h-3.5 text-[var(--theme-text-muted)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path d="M7 10l5 5 5-5"/>
                        </svg>
                    </div>

                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        por página
                    </span>
                </div>


                <div
                    class="
                        h-8
                        min-w-16
                        px-2

                        flex
                        items-center
                        justify-between

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface-soft)]

                        text-xs
                        text-[var(--theme-text)]
                    "
                >
                    <span>ASC</span>

                    <svg
                        class="w-3.5 h-3.5 text-[var(--theme-text-muted)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M7 10l5 5 5-5"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- TABLA ESCRITORIO --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
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

                                    border
                                    border-[var(--theme-border-strong)]
                                "
                            ></div>
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
                            Tipo
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
                                px-3
                                py-3

                                font-medium
                                text-[var(--theme-text-muted)]
                                text-right
                            "
                        >
                            Acciones
                        </th>
                    </tr>
                </thead>


                <tbody class="animate-pulse">
                    @for ($row = 0; $row < 10; $row++)
                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]
                            "
                        >
                            <td class="px-5 py-3">
                                <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>


        {{-- TARJETAS MÓVIL --}}
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
                                <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                                <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                        </div>

                        <div class="h-3 w-full rounded bg-[var(--theme-surface-soft)]"></div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                        </div>
                    </div>
                </article>
            @endfor
        </div>


        {{-- PAGINACIÓN DEPENDIENTE DE DATOS --}}
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

                animate-pulse
            "
        >
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
    </section>

</div>