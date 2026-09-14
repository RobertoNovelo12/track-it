<div
    x-data="{
        toastVisible: false,
        toastMessage: '',

        showToast(message) {
            this.toastMessage = message;
            this.toastVisible = true;

            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        }
    }"
    @asignacion-guardada.window="showToast($event.detail.message)"
>

    @php

        /*
        |--------------------------------------------------------------------------
        | Gráfica por área
        |--------------------------------------------------------------------------
        */

        $totalPorArea = collect($equiposPorArea)->sum('total');

        $chartColors = [
            '#247BA0',
            '#70C1B3',
            '#B2DB8A',
            '#F4A261',
            '#F28482',
            '#9B8AFB',
            '#E76F51',
            '#90CAF9',
        ];

        $gradientParts = [];

        $start = 0;

        foreach ($equiposPorArea as $index => $item) {

            $percentage = $totalPorArea > 0
                ? ((int) $item->total / $totalPorArea) * 100
                : 0;

            $end = $start + $percentage;

            $color = $chartColors[
                $index % count($chartColors)
            ];

            $gradientParts[] =
                $color
                . ' '
                . round($start, 2)
                . '% '
                . round($end, 2)
                . '%';

            $start = $end;
        }

        $chartGradient = count($gradientParts)
            ? implode(', ', $gradientParts)
            : 'var(--theme-surface-soft) 0% 100%';

    @endphp


    {{-- ============================================================
        TOAST
    ============================================================ --}}
    <div
        x-show="toastVisible"
        x-cloak
        x-transition
        class="
            fixed
            top-5
            right-4
            sm:right-6

            z-[100]

            w-[calc(100%_-_2rem)]
            max-w-sm

            bg-[var(--theme-surface)]

            border
            border-emerald-500/30

            rounded-xl
            shadow-lg

            px-4
            py-3

            flex
            items-start
            gap-3
        "
    >

        <div
            class="
                shrink-0

                w-8
                h-8

                rounded-full

                bg-emerald-500/10
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
                stroke-width="2"
            >
                <path d="M5 12l4 4L19 6"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Asignación realizada
            </p>

            <p
                class="
                    mt-0.5
                    text-xs
                    text-[var(--theme-text-muted)]
                "
                x-text="toastMessage"
            ></p>

        </div>

    </div>


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

        {{-- Texto --}}
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

            {{-- ASIGNAR --}}
            <button
                type="button"
                class="
                    h-9
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    bg-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-hover)]

                    text-sm
                    font-medium
                    text-white

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
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                Asignar
            </button>


            {{-- Reasignar --}}
            <button
                type="button"
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
                    <rect
                        x="3"
                        y="4"
                        width="12"
                        height="14"
                        rx="2"
                    />

                    <rect
                        x="15"
                        y="8"
                        width="6"
                        height="12"
                        rx="1"
                    />

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

                <p
                    class="
                        mt-0.5
                        text-2xl
                        font-bold
                        text-emerald-500
                        leading-none
                    "
                >
                    {{ number_format($equiposAsignados) }}
                </p>

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

                <p
                    class="
                        mt-0.5
                        text-2xl
                        font-bold
                        text-red-500
                        leading-none
                    "
                >
                    {{ number_format($equiposNoAsignados) }}
                </p>

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

                <p
                    class="
                        mt-0.5
                        text-2xl
                        font-bold
                        text-[var(--theme-primary)]
                        leading-none
                    "
                >
                    {{ number_format($movimientosRecientes) }}
                </p>

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

            {{-- Cabecera --}}
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


                    <div>

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


                {{-- Visual por ahora --}}
                <div
                    class="
                        shrink-0

                        h-9

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

                </div>

            </div>


            @if ($totalPorArea > 0)

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

                    {{-- ====================================================
                        DONUT
                    ==================================================== --}}
                    <div
                        class="
                            relative
                            shrink-0

                            w-56
                            h-56

                            sm:w-64
                            sm:h-64
                        "
                    >

                        <div
                            class="
                                absolute
                                inset-0
                                rounded-full
                            "
                            style="
                                background:
                                    conic-gradient(
                                        {{ $chartGradient }}
                                    );
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

                                text-center
                            "
                        >

                            <span
                                class="
                                    text-2xl
                                    sm:text-3xl

                                    font-bold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ number_format($totalPorArea) }}
                            </span>

                            <span
                                class="
                                    mt-1

                                    text-[10px]
                                    sm:text-xs

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Equipos asignados
                            </span>

                        </div>

                    </div>


                    {{-- ====================================================
                        LEYENDA
                    ==================================================== --}}
                    <div
                        class="
                            w-full
                            max-w-xs
                            space-y-2.5
                        "
                    >

                        @foreach ($equiposPorArea as $index => $item)

                            @php
                                $color =
                                    $chartColors[
                                        $index % count($chartColors)
                                    ];
                            @endphp


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
                                    "
                                    style="background-color: {{ $color }};"
                                ></span>


                                <span
                                    class="
                                        min-w-0
                                        flex-1
                                        truncate
                                        text-xs
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ $item->area }}
                                </span>


                                <span
                                    class="
                                        text-xs
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ number_format($item->total) }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                </div>

            @else

                <div
                    class="
                        min-h-72

                        flex
                        flex-col
                        items-center
                        justify-center

                        text-center
                    "
                >

                    <div
                        class="
                            w-14
                            h-14

                            rounded-full

                            bg-[var(--theme-primary-soft-subtle)]
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
                            stroke-width="1.6"
                        >
                            <circle cx="12" cy="12" r="8"/>
                            <path d="M12 4v8h8"/>
                        </svg>
                    </div>

                    <p
                        class="
                            mt-3
                            text-sm
                            font-medium
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Sin equipos asignados
                    </p>

                </div>

            @endif

        </section>


        {{-- ========================================================
            RESUMEN DE ACTIVIDAD
        ======================================================== --}}
        <section
            id="actividad-reciente"
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


                        <div>

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


                    {{-- Visual --}}
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

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-emerald-500
                                "
                            >
                                {{ number_format($asignacionesHoy) }}
                            </span>

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

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-[var(--theme-primary)]
                                "
                            >
                                {{ number_format($reasignacionesHoy) }}
                            </span>

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


                    {{-- Asignaciones mes --}}
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

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-amber-500
                                "
                            >
                                {{ number_format($asignacionesMes) }}
                            </span>

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


                    {{-- Reasignaciones mes --}}
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

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-violet-500
                                "
                            >
                                {{ number_format($reasignacionesMes) }}
                            </span>

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
                ACTIVIDAD
            ==================================================== --}}
            <div>

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
                            <circle cx="3.5" cy="6" r=".5" fill="currentColor"/>
                            <circle cx="3.5" cy="12" r=".5" fill="currentColor"/>
                            <circle cx="3.5" cy="18" r=".5" fill="currentColor"/>
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


                {{-- Encabezado tabla escritorio --}}
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
                    <div>Acción</div>
                    <div>Detalle</div>
                    <div class="text-right">Hora</div>
                </div>


                @forelse ($actividad as $item)

                    @php

                        $fechaActividad =
                            \Illuminate\Support\Carbon::parse(
                                $item->fecha
                            );

                        $horaActividad =
                            $fechaActividad->format('h:i A');

                        $fechaTexto =
                            $fechaActividad->isToday()
                                ? 'Hoy'
                                : $fechaActividad->format('d/m/Y');

                        $codigo =
                            $item->codigo_inventario
                            ?? ('Equipo #' . $item->id_equipo);

                    @endphp


                    <div
                        wire:key="
                            actividad-
                            {{ $item->tipo }}-
                            {{ $item->id }}
                        "
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

                            <span
                                class="
                                    w-8
                                    h-7

                                    rounded-lg

                                    inline-flex
                                    items-center
                                    justify-center

                                    {{ $item->tipo === 'asignacion'
                                        ? 'bg-emerald-500/15 text-emerald-500'
                                        : 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                    }}
                                "
                            >

                                @if ($item->tipo === 'asignacion')

                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path d="M4 7h14"/>
                                        <path d="M15 4l3 3-3 3"/>
                                        <path d="M20 17H6"/>
                                    </svg>

                                @else

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

                                @endif

                            </span>

                        </div>


                        {{-- Detalle --}}
                        <div class="min-w-0">

                            <p
                                class="
                                    text-xs
                                    leading-relaxed
                                    text-[var(--theme-text)]
                                "
                            >

                                @if ($item->tipo === 'asignacion')

                                    Se asignó el equipo

                                @elseif ($item->tipo === 'reasignacion')

                                    Se reasignó el equipo

                                @else

                                    Se registró movimiento del equipo

                                @endif


                                <span
                                    class="
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ $codigo }}
                                </span>


                                @if (!empty($item->nombre_colaborador))

                                    <span class="hidden md:inline">
                                        a
                                        {{ $item->nombre_colaborador }}
                                    </span>

                                @endif

                            </p>


                            @if (!empty($item->usuario))

                                <p
                                    class="
                                        mt-0.5
                                        text-[9px]
                                        text-[var(--theme-text-muted)]
                                        truncate
                                    "
                                >
                                    {{ $item->usuario }}
                                </p>

                            @endif

                        </div>


                        {{-- Fecha --}}
                        <div class="text-right">

                            <p
                                class="
                                    text-[10px]
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $horaActividad }}
                            </p>

                            <p
                                class="
                                    sm:hidden
                                    mt-0.5
                                    text-[9px]
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $fechaTexto }}
                            </p>

                        </div>

                    </div>

                @empty

                    <div
                        class="
                            min-h-40

                            flex
                            items-center
                            justify-center

                            px-5
                            py-10

                            text-center
                        "
                    >
                        <p
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Todavía no hay actividad registrada.
                        </p>
                    </div>

                @endforelse

            </div>

        </section>

    </div>

</div>