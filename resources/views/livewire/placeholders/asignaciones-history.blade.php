<div class="space-y-5">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section>
        <div
            class="
                flex items-center flex-wrap gap-2
                mb-2
                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            <a
                href="{{ route('dashboard') }}"
                wire:navigate.hover
                class="
                    hover:text-[var(--theme-primary)]
                    transition-colors
                "
            >
                Vista General
            </a>

            <svg
                class="w-3 h-3"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>

            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
                class="
                    hover:text-[var(--theme-primary)]
                    transition-colors
                "
            >
                Asignación y Movimientos
            </a>

            <svg
                class="w-3 h-3"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>

            <span>
                Historial
            </span>
        </div>

        <div
            class="
                flex flex-col
                sm:flex-row
                sm:items-start
                sm:justify-between
                gap-4
            "
        >
            <div>
                <h1
                    class="
                        text-xl
                        font-semibold
                        leading-tight
                        text-[var(--theme-text-strong)]
                    "
                >
                    Historial de Asignaciones y Movimientos
                </h1>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Consulta las asignaciones, reasignaciones y movimientos registrados de los equipos.
                </p>
            </div>

            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
                class="
                    h-9
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:border-[var(--theme-primary-border)]

                    transition-colors
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M15 6l-6 6 6 6"/>
                </svg>

                Volver
            </a>
        </div>
    </section>


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            p-4
            sm:p-5
        "
    >
        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-2
                xl:grid-cols-[minmax(280px,1.4fr)_minmax(180px,.7fr)_minmax(160px,.55fr)_minmax(160px,.55fr)]
                gap-4
            "
        >

            {{-- BUSCAR --}}
            <div>
                <label
                    class="
                        block
                        mb-1.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Buscar
                </label>

                <div class="relative">
                    <svg
                        class="
                            absolute
                            left-3
                            top-1/2
                            -translate-y-1/2

                            w-4
                            h-4

                            text-[var(--theme-text-muted)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-4-4"/>
                    </svg>

                    <input
                        type="search"
                        disabled
                        placeholder="Equipo, colaborador, host, sede..."
                        class="
                            w-full
                            h-10

                            pl-9
                            pr-3

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >
                </div>
            </div>


            {{-- TIPO --}}
            <div>
                <label
                    class="
                        block
                        mb-1.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Tipo
                </label>

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

                        text-sm
                        text-[var(--theme-text)]

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
                    <option>
                        Todos los movimientos
                    </option>
                </select>
            </div>


            {{-- DESDE --}}
            <div>
                <label
                    class="
                        block
                        mb-1.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Desde
                </label>

                <input
                    type="date"
                    disabled
                    class="
                        w-full
                        h-10

                        px-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-sm
                        text-[var(--theme-text)]

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
            </div>


            {{-- HASTA --}}
            <div>
                <label
                    class="
                        block
                        mb-1.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Hasta
                </label>

                <input
                    type="date"
                    disabled
                    class="
                        w-full
                        h-10

                        px-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-sm
                        text-[var(--theme-text)]

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
            </div>

        </div>


        <div
            class="
                mt-4

                flex
                flex-col-reverse
                sm:flex-row
                sm:items-center
                sm:justify-end

                gap-2
            "
        >
            <button
                type="button"
                disabled
                class="
                    h-9
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-xs
                    font-medium
                    text-[var(--theme-text-muted)]

                    disabled:opacity-70
                    disabled:cursor-default
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M4 4l16 16"/>
                    <path d="M20 4L4 20"/>
                </svg>

                Limpiar filtros
            </button>

            <button
                type="button"
                disabled
                class="
                    h-9
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    bg-[var(--theme-primary)]

                    text-xs
                    font-medium
                    text-white

                    disabled:opacity-70
                    disabled:cursor-default
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
                    <path d="M20 20l-4-4"/>
                </svg>

                Aplicar filtros
            </button>
        </div>
    </section>


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    <section
        class="
            bg-transparent
            border-0

            md:bg-[var(--theme-surface)]
            md:border
            md:border-[var(--theme-border)]
            md:rounded-lg
            md:overflow-hidden
        "
    >

        {{-- CABECERA --}}
        <div
            class="
                mb-3
                md:mb-0

                px-5
                md:px-6

                py-4

                md:border-b
                md:border-[var(--theme-border)]

                flex
                flex-col
                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-3
            "
        >
            <div>
                <h2
                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Historial
                </h2>

                <div
                    class="
                        mt-2
                        h-3
                        w-20
                        rounded
                        bg-[var(--theme-surface-soft)]
                        animate-pulse
                    "
                ></div>
            </div>

            <div
                class="
                    flex
                    items-center
                    gap-2
                "
            >
                <span
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Mostrar
                </span>

                <select
                    disabled
                    class="
                        h-9
                        px-2

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        text-[var(--theme-text)]

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
                    <option>10</option>
                </select>
            </div>
        </div>


        {{-- ========================================================
            TABLA ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-xs">

                <thead
                    class="
                        bg-[var(--theme-surface-soft)]
                        text-[var(--theme-text-muted)]
                    "
                >
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Fecha
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Equipo
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Movimiento
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Colaborador
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Ubicación
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Realizado por
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @for ($i = 0; $i < 6; $i++)

                        <tr
                            class="
                                border-t
                                border-[var(--theme-border)]
                            "
                        >

                            {{-- FECHA --}}
                            <td class="px-4 py-4">
                                <div
                                    class="
                                        h-3
                                        w-24
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                        animate-pulse
                                    "
                                ></div>
                            </td>


                            {{-- EQUIPO --}}
                            <td class="px-4 py-4">
                                <div class="space-y-2 min-w-[160px]">
                                    <div
                                        class="
                                            h-3
                                            w-24
                                            rounded
                                            bg-[var(--theme-surface-soft)]
                                            animate-pulse
                                        "
                                    ></div>

                                    <div
                                        class="
                                            h-2.5
                                            w-32
                                            rounded
                                            bg-[var(--theme-surface-soft)]
                                            animate-pulse
                                        "
                                    ></div>
                                </div>
                            </td>


                            {{-- MOVIMIENTO --}}
                            <td class="px-4 py-4">
                                <div
                                    class="
                                        h-6
                                        w-20
                                        rounded-full
                                        bg-[var(--theme-surface-soft)]
                                        animate-pulse
                                    "
                                ></div>
                            </td>


                            {{-- COLABORADOR --}}
                            <td class="px-4 py-4">
                                <div class="space-y-2 min-w-[150px]">
                                    <div
                                        class="
                                            h-3
                                            w-32
                                            rounded
                                            bg-[var(--theme-surface-soft)]
                                            animate-pulse
                                        "
                                    ></div>

                                    <div
                                        class="
                                            h-2.5
                                            w-20
                                            rounded
                                            bg-[var(--theme-surface-soft)]
                                            animate-pulse
                                        "
                                    ></div>
                                </div>
                            </td>


                            {{-- UBICACIÓN --}}
                            <td class="px-4 py-4">
                                <div
                                    class="
                                        h-3
                                        w-36
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                        animate-pulse
                                    "
                                ></div>
                            </td>


                            {{-- USUARIO --}}
                            <td class="px-4 py-4">
                                <div
                                    class="
                                        h-3
                                        w-28
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                        animate-pulse
                                    "
                                ></div>
                            </td>

                        </tr>

                    @endfor

                </tbody>

            </table>
        </div>


        {{-- ========================================================
            TARJETAS MÓVIL
        ======================================================== --}}
        <div class="md:hidden space-y-3">

            @for ($i = 0; $i < 4; $i++)

                <article
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        shadow-sm
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
                        <div class="space-y-2">
                            <div
                                class="
                                    h-3
                                    w-24
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>

                            <div
                                class="
                                    h-2.5
                                    w-32
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>

                        <div
                            class="
                                h-6
                                w-20
                                rounded-full
                                bg-[var(--theme-surface-soft)]
                                animate-pulse
                            "
                        ></div>
                    </div>


                    <div
                        class="
                            mt-4

                            grid
                            grid-cols-2

                            gap-x-4
                            gap-y-4
                        "
                    >
                        <div>
                            <p
                                class="
                                    text-[10px]
                                    uppercase
                                    tracking-wide
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Fecha
                            </p>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-24
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>


                        <div>
                            <p
                                class="
                                    text-[10px]
                                    uppercase
                                    tracking-wide
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Host
                            </p>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-20
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>


                        <div class="col-span-2">
                            <p
                                class="
                                    text-[10px]
                                    uppercase
                                    tracking-wide
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Colaborador
                            </p>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-36
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>


                        <div class="col-span-2">
                            <p
                                class="
                                    text-[10px]
                                    uppercase
                                    tracking-wide
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Ubicación
                            </p>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-44
                                    max-w-full
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>


                        <div class="col-span-2">
                            <p
                                class="
                                    text-[10px]
                                    uppercase
                                    tracking-wide
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Realizado por
                            </p>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-28
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                    animate-pulse
                                "
                            ></div>
                        </div>
                    </div>
                </article>

            @endfor

        </div>

    </section>

</div>