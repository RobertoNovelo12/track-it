<div>

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section
        class="
            flex
            flex-col
            xl:flex-row
            xl:items-end
            xl:justify-between
            gap-5
            mb-6
        "
    >
        <div class="min-w-0">

            {{-- Breadcrumb --}}
            <div
                class="
                    flex
                    items-center
                    flex-wrap
                    gap-2
                    mb-2
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                <a
                    href="{{ route('dashboard') }}"
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

                <span>
                    Asignación y Movimientos
                </span>
            </div>

            {{-- Título --}}
            <h1
                class="
                    text-xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-text-strong)]
                "
            >
                Asignación y Movimientos
            </h1>
        </div>


        {{-- ========================================================
            ACCIONES SUPERIORES
        ======================================================== --}}
        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
                xl:justify-end
            "
        >

            {{-- Asignar --}}
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
                    text-sm
                    font-medium
                    text-white
                    disabled:opacity-100
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
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                Asignar
            </button>


            {{-- Reasignar --}}
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
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border-strong)]
                    text-sm
                    font-medium
                    text-[var(--theme-text)]
                    disabled:opacity-100
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
                    <path d="M4 7h15"/>
                    <path d="M16 4l3 3-3 3"/>
                    <path d="M20 17H5"/>
                    <path d="M8 14l-3 3 3 3"/>
                </svg>

                Reasignar
            </button>


            {{-- Historial --}}
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
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border-strong)]
                    text-sm
                    font-medium
                    text-[var(--theme-text)]
                    disabled:opacity-100
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
                    <circle cx="12" cy="12" r="8"/>
                    <path d="M12 7v5l3 2"/>
                </svg>

                Historial
            </button>
        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS PRINCIPALES
    ============================================================ --}}
    <section
        class="
            grid
            grid-cols-1
            md:grid-cols-3
            gap-3
            sm:gap-4
            mb-5
        "
    >

        {{-- ========================================================
            EQUIPOS ASIGNADOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-emerald-500/10
                    text-emerald-500
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <rect x="3" y="4" width="12" height="14" rx="2"/>
                    <rect x="15" y="8" width="6" height="12" rx="1"/>
                    <path d="M6 8h6"/>
                    <path d="M18 12h.01"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos Asignados
                </p>

                {{-- Solo el dato es desconocido --}}
                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
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
                    Equipos en uso
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>
        </div>


        {{-- ========================================================
            EQUIPOS NO ASIGNADOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-red-500/25
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-red-500/10
                    text-red-500
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"/>
                    <path d="M4 7.5l8 4.5 8-4.5"/>
                    <path d="M12 12v9"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos No Asignados
                </p>

                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
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
                    Equipos disponibles
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>
        </div>


        {{-- ========================================================
            MOVIMIENTOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-primary-border)]
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M4 7h15"/>
                    <path d="M16 4l3 3-3 3"/>
                    <path d="M20 17H5"/>
                    <path d="M8 14l-3 3 3 3"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Movimientos recientes
                </p>

                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
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
                    Últimos 7 días
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>
        </div>

    </section>


    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[1.05fr_1fr]
            gap-4
        "
    >

        {{-- ========================================================
            EQUIPOS ASIGNADOS POR ÁREA
        ======================================================== --}}
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

            {{-- Cabecera conocida --}}
            <div
                class="
                    flex
                    flex-col
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
                    gap-3
                "
            >
                <div
                    class="
                        flex
                        items-start
                        gap-3
                        min-w-0
                    "
                >
                    <svg
                        class="
                            shrink-0
                            w-5
                            h-5
                            mt-0.5
                            text-[var(--theme-text-strong)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M5 20V10"/>
                        <path d="M12 20V4"/>
                        <path d="M19 20v-7"/>
                    </svg>

                    <div class="min-w-0">
                        <h2
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Equipos asignados por área
                        </h2>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Distribución de equipos en uso por área de la organización.
                        </p>
                    </div>
                </div>


                {{-- Sabemos qué control existe, solo aún no hay datos --}}
                <button
                    type="button"
                    disabled
                    class="
                        shrink-0
                        h-9
                        w-full
                        sm:w-auto
                        px-3
                        inline-flex
                        items-center
                        justify-between
                        gap-4
                        border
                        border-[var(--theme-border-strong)]
                        rounded-lg
                        bg-[var(--theme-surface)]
                        text-xs
                        text-[var(--theme-text-muted)]
                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    Todas las áreas

                    <svg
                        class="w-3.5 h-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
            </div>


            {{-- ====================================================
                DATOS DEL GRÁFICO
            ==================================================== --}}
            <div
                class="
                    mt-7
                    flex
                    flex-col
                    lg:flex-row
                    items-center
                    justify-center
                    gap-8
                "
            >

                {{-- Donut desconocido --}}
                <div
                    class="
                        relative
                        shrink-0
                        w-56
                        h-56
                        sm:w-64
                        sm:h-64
                        animate-pulse
                    "
                >
                    <div
                        class="
                            absolute
                            inset-0
                            rounded-full
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            absolute
                            inset-[25%]
                            rounded-full
                            bg-[var(--theme-surface)]
                            flex
                            flex-col
                            items-center
                            justify-center
                            gap-2
                        "
                    >
                        <div
                            class="
                                h-7
                                w-16
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <span
                            class="
                                text-[10px]
                                sm:text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Equipos asignados
                        </span>
                    </div>
                </div>


                {{-- Leyenda --}}
                <div
                    class="
                        w-full
                        max-w-xs
                        space-y-2.5
                        animate-pulse
                    "
                >
                    @for ($i = 0; $i < 5; $i++)
                        <div
                            class="
                                flex
                                items-center
                                gap-2.5
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-2.5
                                    h-2.5
                                    rounded-full
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></span>

                            <div
                                class="
                                    h-2.5
                                    flex-1
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-2.5
                                    w-6
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>
                    @endfor
                </div>

            </div>
        </section>


        {{-- ========================================================
            RESUMEN DE ACTIVIDAD
        ======================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                overflow-hidden
            "
        >

            {{-- Cabecera --}}
            <div
                class="
                    px-4
                    sm:px-5
                    pt-4
                    sm:pt-5
                "
            >
                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >
                    <div
                        class="
                            flex
                            items-start
                            gap-3
                            min-w-0
                        "
                    >
                        <svg
                            class="
                                shrink-0
                                w-5
                                h-5
                                mt-0.5
                                text-[var(--theme-text-strong)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M3 12h4l2-7 4 14 2-7h6"/>
                        </svg>

                        <div class="min-w-0">
                            <h2
                                class="
                                    text-sm
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Resumen de actividad
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Información general de movimientos y asignaciones.
                            </p>
                        </div>
                    </div>


                    <span
                        class="
                            hidden
                            sm:inline-flex
                            items-center
                            gap-1
                            text-xs
                            font-medium
                            text-[var(--theme-primary)]
                        "
                    >
                        Ver reporte completo

                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M5 12h14"/>
                            <path d="M15 8l4 4-4 4"/>
                        </svg>
                    </span>
                </div>


                {{-- ====================================================
                    MINI MÉTRICAS
                ==================================================== --}}
                <div
                    class="
                        grid
                        grid-cols-2
                        sm:grid-cols-4
                        gap-2
                        mt-5
                        mb-5
                    "
                >

                    {{-- Asignaciones hoy --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-emerald-500/20
                            bg-emerald-500/10
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-emerald-500/15
                                    text-emerald-500
                                    flex
                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M6 3h10l3 3v15H6z"/>
                                    <path d="M9 11h6"/>
                                    <path d="M9 15h6"/>
                                </svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-emerald-500/20
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Asignaciones hoy
                        </p>
                    </div>


                    {{-- Reasignaciones hoy --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-[var(--theme-primary-border)]
                            bg-[var(--theme-primary-soft-subtle)]
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-[var(--theme-primary-soft)]
                                    text-[var(--theme-primary)]
                                    flex
                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M4 7h15"/>
                                    <path d="M16 4l3 3-3 3"/>
                                    <path d="M20 17H5"/>
                                    <path d="M8 14l-3 3 3 3"/>
                                </svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-primary-soft)]
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Reasignaciones hoy
                        </p>
                    </div>


                    {{-- Asignaciones este mes --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-amber-500/20
                            bg-amber-500/10
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-amber-500/15
                                    text-amber-500
                                    flex
                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <circle cx="12" cy="12" r="8"/>
                                    <path d="M12 7v5l3 2"/>
                                </svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-amber-500/20
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Asignaciones este mes
                        </p>
                    </div>


                    {{-- Reasignaciones este mes --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-violet-500/20
                            bg-violet-500/10
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-violet-500/15
                                    text-violet-500
                                    flex
                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M5 20V10"/>
                                    <path d="M12 20V4"/>
                                    <path d="M19 20v-7"/>
                                </svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-violet-500/20
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Reasignaciones este mes
                        </p>
                    </div>

                </div>
            </div>


            {{-- ====================================================
                ACTIVIDAD RECIENTE
            ==================================================== --}}
            <div>

                {{-- Título conocido --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3
                        border-y
                        border-[var(--theme-border)]
                        flex
                        items-center
                        justify-between
                        gap-3
                    "
                >
                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <svg
                            class="
                                w-4
                                h-4
                                text-[var(--theme-text-strong)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path d="M8 6h13"/>
                            <path d="M8 12h13"/>
                            <path d="M8 18h13"/>

                            <circle
                                cx="3.5"
                                cy="6"
                                r=".5"
                                fill="currentColor"
                            />

                            <circle
                                cx="3.5"
                                cy="12"
                                r=".5"
                                fill="currentColor"
                            />

                            <circle
                                cx="3.5"
                                cy="18"
                                r=".5"
                                fill="currentColor"
                            />
                        </svg>

                        <h3
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Actividad reciente
                        </h3>
                    </div>

                    <span
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-primary)]
                        "
                    >
                        Últimos movimientos
                    </span>
                </div>


                {{-- Encabezado escritorio conocido --}}
                <div
                    class="
                        hidden
                        sm:grid
                        grid-cols-[80px_1fr_90px]
                        gap-3
                        px-5
                        py-2
                        bg-[var(--theme-surface-soft)]
                        border-b
                        border-[var(--theme-border)]
                        text-[9px]
                        font-medium
                        uppercase
                        tracking-wide
                        text-[var(--theme-text-muted)]
                    "
                >
                    <div>
                        Acción
                    </div>

                    <div>
                        Detalle
                    </div>

                    <div class="text-right">
                        Hora
                    </div>
                </div>


                {{-- ====================================================
                    FILAS DESCONOCIDAS
                ==================================================== --}}
                <div class="animate-pulse">

                    @for ($i = 0; $i < 5; $i++)
                        <div
                            class="
                                grid
                                grid-cols-[36px_1fr_auto]
                                sm:grid-cols-[80px_1fr_90px]
                                items-center
                                gap-3
                                px-4
                                sm:px-5
                                py-2.5
                                border-b
                                border-[var(--theme-border)]
                                last:border-b-0
                            "
                        >

                            {{-- Acción --}}
                            <div>
                                <div
                                    class="
                                        w-8
                                        h-7
                                        rounded-lg
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </div>


                            {{-- Detalle --}}
                            <div class="min-w-0">
                                <div
                                    class="
                                        h-2.5
                                        w-full
                                        max-w-56
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>

                                <div
                                    class="
                                        mt-2
                                        h-2
                                        w-24
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </div>


                            {{-- Hora --}}
                            <div
                                class="
                                    justify-self-end
                                    h-2.5
                                    w-12
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>
                    @endfor

                </div>

            </div>

        </section>

    </div>

</div>