<div
    x-data="{
        toastVisible: false,
        toastMessage: '',

        showToast(message) {
            this.toastMessage = message;
            window.trackitToast?.(this.toastType === 'error' ? 'error' : 'success', this.toastMessage); /* aviso global */

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
        | Gráfica por hotel
        |--------------------------------------------------------------------------
        */

        $totalPorHotel = collect($equiposPorHotel)->sum('total');

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

        foreach ($equiposPorHotel as $index => $item) {

            $percentage = $totalPorHotel > 0
                ? ((int) $item->total / $totalPorHotel) * 100
                : 0;

            $end = $start + $percentage;

            $color = $chartColors[
                ((int) $index) % count($chartColors)
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
            ><use href="{{ \App\Support\Icono::url('i-42997ad') }}"></use></svg>
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
            md:flex-row md:items-center md:justify-between

            gap-5

            mb-6
        "
    >


        {{-- ========================================================
            ACCIONES SUPERIORES («Volver» a la izquierda, en escritorio)
        ======================================================== --}}
        @include('layouts.partials.boton-volver', ['destino' => url('/')])

        {{--
            NUEVO MOVIMIENTO: un solo botón con Asignar, Reasignar y
            Devolver. El Historial se abre desde la tarjeta «Movimientos
            recientes».
        --}}
        {{-- El menú usa posición fija sobre toda la página (como los menús ⋮
             de las tablas): así ningún bloque de abajo lo tapa. --}}
        {{-- wire:key: al cargar, Livewire lo crea nuevo en lugar de reutilizar
             el botón de la pantalla de carga (si no, el menú quedaba sin
             sus variables: «abierto is not defined»). --}}
        <div
            wire:key="nuevo-movimiento-menu"
            x-data="{
                abierto: false,
                arriba: 0,
                izquierda: 0,
                ancho: 320,
                alternar(boton) {
                    if (this.abierto) { this.abierto = false; return; }
                    const r = boton.getBoundingClientRect();
                    const movil = window.innerWidth < 768;
                    this.ancho = movil ? r.width : 320;
                    this.izquierda = movil ? r.left : Math.max(8, r.right - this.ancho);
                    this.arriba = r.bottom + 8;
                    this.abierto = true;
                },
            }"
            x-on:click.outside="abierto = false"
            x-on:keydown.escape.window="abierto = false"
            @trackit-cerrar-menus.window="abierto = false"
            @scroll.window.passive="abierto = false"
            @resize.window="abierto = false"
            class="relative w-full md:w-auto"
        >
            <button
                type="button"
                x-on:click="alternar($el)"
                :aria-expanded="abierto.toString()"
                aria-haspopup="menu"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] md:w-auto"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>
                Nuevo movimiento
                <svg class="h-4 w-4 transition-transform" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div
                x-show="abierto"
                x-cloak
                x-transition.opacity.duration.100ms
                role="menu"
                :style="{ position: 'fixed', top: arriba + 'px', left: izquierda + 'px', width: ancho + 'px', zIndex: 130 }"
                class="overflow-hidden rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] text-left shadow-lg shadow-[var(--theme-shadow)]"
            >
                @foreach ([
                    ['ruta' => 'asignaciones.create', 'icono' => 'i-6aed230', 'titulo' => 'Asignar', 'texto' => 'Entregar un equipo disponible a una persona u hotel'],
                    ['ruta' => 'asignaciones.reassign', 'icono' => 'i-e5c8e79', 'titulo' => 'Reasignar', 'texto' => 'Pasar un equipo asignado a otra persona u hotel'],
                    ['ruta' => 'asignaciones.devolver', 'icono' => 'i-3179dca', 'titulo' => 'Devolver', 'texto' => 'Recibir un equipo y dejarlo disponible'],
                ] as $opcion)
                    <a
                        href="{{ route($opcion['ruta']) }}"
                        wire:navigate.hover
                        role="menuitem"
                        class="flex items-start gap-3 border-b border-[var(--theme-border)] px-4 py-3 transition-colors last:border-b-0 hover:bg-[var(--theme-surface-soft)]"
                    >
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url($opcion['icono']) }}"></use></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-[var(--theme-text-strong)]">{{ $opcion['titulo'] }}</span>
                            <span class="mt-0.5 block text-xs text-[var(--theme-text-muted)]">{{ $opcion['texto'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

            </section> {{-- CIERRA ENCABEZADO --}}


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
        <a
            href="{{ route('equipos.index', ['situacion' => 'asignados']) }}"
            wire:navigate.hover
            title="Ver equipos asignados en el inventario"
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

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    shrink-0

                    w-14
                    h-14

                    rounded-xl

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]

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
                ><use href="{{ \App\Support\Icono::url('i-af649ce') }}"></use></svg>
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
                        text-[var(--theme-text-strong)]
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
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>

        </a>


        {{-- ========================================================
            EQUIPOS NO ASIGNADOS
        ======================================================== --}}
        <a
            href="{{ route('equipos.index', ['situacion' => 'sin_asignar']) }}"
            wire:navigate.hover
            title="Ver equipos sin asignar en el inventario"
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

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
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
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-37cc6c3') }}"></use></svg>
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
                        text-[var(--theme-text-strong)]
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
                    Listos para asignar
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
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>

        </a>


        {{-- ========================================================
            MOVIMIENTOS
        ======================================================== --}}
        <a
            href="{{ route('asignaciones.history') }}"
            wire:navigate.hover
            title="Ver el historial de movimientos"
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

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    shrink-0

                    w-14
                    h-14

                    rounded-xl

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]

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
                ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>
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
                        text-[var(--theme-text-strong)]
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
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>

        </a>

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

        {{--
            Columna izquierda: gráfica + equipos disponibles. La lista
            ocupa el alto restante para igualar la columna de actividad.
        --}}
        <div class="flex min-w-0 flex-col gap-4">

        {{-- ========================================================
            EQUIPOS ASIGNADOS POR HOTEL
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
                    ><use href="{{ \App\Support\Icono::url('i-1f0e5a3') }}"></use></svg>


                    <div>

                        <h2
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Equipos asignados por {{ mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']) }}
                        </h2>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Distribución de equipos en uso por {{ mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']) }}.
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
                    Todos los {{ mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_plural']) }}

                    <svg
                        class="w-3.5 h-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>

                </div>

            </div>


            @if ($totalPorHotel > 0)

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
                                {{ number_format($totalPorHotel) }}
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

                        @foreach ($equiposPorHotel as $index => $item)

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
                                    {{ $item->hotel }}
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
                        ><use href="{{ \App\Support\Icono::url('i-dbb4cf3') }}"></use></svg>
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
            DISPONIBLES PARA ASIGNAR
        ======================================================== --}}
        <section class="flex flex-1 flex-col rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-[var(--theme-text-strong)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-b41fc0d') }}"></use></svg>

                    <div>
                        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Disponibles para asignar</h3>
                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Los que llevan más tiempo sin responsable.</p>
                    </div>
                </div>

                @if ($equiposNoAsignados > 0)
                    <a
                        href="{{ route('equipos.index', ['situacion' => 'sin_asignar']) }}"
                        wire:navigate.hover
                        class="shrink-0 text-xs font-medium text-[var(--theme-primary)] hover:underline"
                    >
                        Ver {{ $equiposNoAsignados === 1 ? 'el disponible' : 'los ' . number_format($equiposNoAsignados) }} →
                    </a>
                @endif
            </div>

            @if ($disponiblesParaAsignar === [])
                <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
                    <p class="text-sm font-medium text-[var(--theme-text-strong)]">Todos los equipos tienen responsable</p>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Cuando haya equipos sin asignar aparecerán aquí.</p>
                </div>
            @else
                <ul class="mt-4 divide-y divide-[var(--theme-border)]">
                    @foreach ($disponiblesParaAsignar as $disponible)
                        @php
                            $desdeDisponible = $disponible['desde']
                                ? \Illuminate\Support\Carbon::parse($disponible['desde'])->locale('es')->diffForHumans(['parts' => 1])
                                : null;
                        @endphp

                        <li wire:key="disponible-{{ $disponible['id'] }}" class="flex items-center gap-3 py-2.5">
                            <div class="min-w-0 flex-1">
                                <a
                                    href="{{ route('equipos.show', $disponible['id']) }}"
                                    wire:navigate.hover
                                    class="block truncate text-sm font-medium text-[var(--theme-text-strong)] hover:text-[var(--theme-primary)]"
                                >
                                    {{ $disponible['nombre'] !== '' ? $disponible['nombre'] : $disponible['codigo'] }}
                                </a>

                                <p class="mt-0.5 truncate text-[11px] text-[var(--theme-text-muted)]">
                                    {{ implode(' · ', array_filter([$disponible['codigo'], $disponible['tipo'], $disponible['hotel']])) }}
                                    @if ($desdeDisponible)
                                        · Disponible desde {{ $desdeDisponible }}
                                    @endif
                                </p>
                            </div>

                            <a
                                href="{{ route('asignaciones.create', ['equipo' => $disponible['id']]) }}"
                                wire:navigate.hover
                                class="inline-flex h-8 shrink-0 items-center gap-1.5 rounded-md border border-[var(--theme-primary-border)] px-3 text-xs font-medium text-[var(--theme-primary)] transition-colors hover:bg-[var(--theme-primary-soft)]"
                                aria-label="Asignar {{ $disponible['codigo'] }}"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-90887f4') }}"></use></svg>
                                Asignar
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        </div>



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
                        ><use href="{{ \App\Support\Icono::url('i-e9c3e4a') }}"></use></svg>


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
                        ><use href="{{ \App\Support\Icono::url('i-ab28778') }}"></use></svg>
                    </span>

                </div>


                {{-- ====================================================
                    MINI MÉTRICAS (neutras: conteos informativos; se distinguen
        por su icono y su texto, no por colores distintos)
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
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]

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

                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]

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
                                ><use href="{{ \App\Support\Icono::url('i-16d6c5f') }}"></use></svg>
                            </span>

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-[var(--theme-text-strong)]
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
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]

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

                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]

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
                                ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>
                            </span>

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-[var(--theme-text-strong)]
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
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]

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

                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]

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
                                ><use href="{{ \App\Support\Icono::url('i-172bef0') }}"></use></svg>
                            </span>

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-[var(--theme-text-strong)]
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
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]

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

                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]

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
                                ><use href="{{ \App\Support\Icono::url('i-1f0e5a3') }}"></use></svg>
                            </span>

                            <span
                                class="
                                    text-xl
                                    font-bold
                                    text-[var(--theme-text-strong)]
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
                        ><use href="{{ \App\Support\Icono::url('i-a5f1f42') }}"></use></svg>

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

                        // Zona horaria y formato de hora del usuario.
                        $preferenciasFecha =
                            app(\App\Services\PreferenciasUsuarioService::class);

                        $fechaActividad =
                            \Illuminate\Support\Carbon::parse(
                                $item->fecha,
                                config('app.timezone')
                            )->setTimezone(
                                $preferenciasFecha->valor('zona_horaria')
                            );

                        $horaActividad =
                            $preferenciasFecha->hora($item->fecha);

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

                                    {{ match ($item->tipo) {
                                        'asignacion' => 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',
                                        'devolucion' => 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
                                        default => 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',
                                    } }}
                                "
                            >

                                @if ($item->tipo === 'devolucion')

                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-3179dca') }}"></use></svg>

                                @elseif ($item->tipo === 'asignacion')

                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    ><use href="{{ \App\Support\Icono::url('i-0be254e') }}"></use></svg>

                                @else

                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>

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

                                @elseif ($item->tipo === 'devolucion')

                                    Se devolvió el equipo

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
                                        {{ $item->tipo === 'devolucion' ? 'que tenía' : 'a' }}
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

                    <x-ui.empty-state
                        compact
                        title="Todavía no hay actividad"
                        description="Las asignaciones, reasignaciones y movimientos aparecerán aquí."
                    />

                @endforelse

            </div>

        </section>

    </div>

</div>
