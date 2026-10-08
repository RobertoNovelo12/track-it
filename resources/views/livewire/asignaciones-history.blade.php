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
    <form
        wire:submit="buscar"
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
            <div class="min-w-0">
                <label
                    for="history-search"
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
                            pointer-events-none
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
                        id="history-search"
                        type="search"
                        wire:model="search"
                        placeholder="Equipo, colaborador, host, sede..."
                        autocomplete="off"
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

                            outline-none

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]

                            transition-colors
                        "
                    >
                </div>
            </div>


            {{-- TIPO --}}
            <div class="min-w-0">
                <label
                    for="history-type"
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
                    id="history-type"
                    wire:model="tipo"
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

                        outline-none

                        focus:ring-1
                        focus:ring-[var(--theme-primary)]
                        focus:border-[var(--theme-primary)]

                        transition-colors
                    "
                >
                    @foreach ($tiposFiltro as $opcion)
                        <option value="{{ $opcion['value'] }}">
                            {{ $opcion['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- DESDE --}}
            <div class="min-w-0">
                <label
                    for="history-date-from"
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
                    id="history-date-from"
                    type="date"
                    wire:model="fechaDesde"
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

                        outline-none

                        focus:ring-1
                        focus:ring-[var(--theme-primary)]
                        focus:border-[var(--theme-primary)]

                        transition-colors
                    "
                >
            </div>


            {{-- HASTA --}}
            <div class="min-w-0">
                <label
                    for="history-date-to"
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
                    id="history-date-to"
                    type="date"
                    wire:model="fechaHasta"
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

                        outline-none

                        focus:ring-1
                        focus:ring-[var(--theme-primary)]
                        focus:border-[var(--theme-primary)]

                        transition-colors
                    "
                >
            </div>

        </div>


        {{-- ACCIONES FILTROS --}}
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
                wire:click="clearFilters"
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

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text)]

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
                    <path d="M4 4l16 16"/>
                    <path d="M20 4L4 20"/>
                </svg>

                Limpiar filtros
            </button>

            <button
                type="submit"
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

                    text-xs
                    font-medium
                    text-white

                    transition-colors
                "
            >
                <svg
                    wire:loading.remove
                    wire:target="buscar"
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>

                <svg
                    wire:loading
                    wire:target="buscar"
                    class="w-4 h-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-width="2"
                        opacity="0.25"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>

                Aplicar filtros
            </button>
        </div>
    </form>


    {{-- ============================================================
        RESULTADOS
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

        {{-- LOADING --}}
        <div
            wire:loading.delay
            wire:target="
                buscar,
                clearFilters,
                perPage,
                previousPage,
                nextPage,
                gotoPage
            "
            class="
                absolute
                inset-0
                z-30

                bg-[var(--theme-surface)]/70
                backdrop-blur-[1px]

                items-center
                justify-center
            "
        >
            <div
                class="
                    flex
                    items-center
                    gap-2

                    px-3
                    py-2

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-xs
                    text-[var(--theme-text-muted)]

                    shadow-sm
                "
            >
                <svg
                    class="w-4 h-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-width="2"
                        opacity="0.25"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>

                Actualizando...
            </div>
        </div>


        {{-- ========================================================
            CABECERA RESULTADOS
        ======================================================== --}}
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

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    {{ $historial->total() }}
                    resultado{{ $historial->total() === 1 ? '' : 's' }}
                </p>
            </div>

            <div
                class="
                    flex
                    items-center
                    gap-2
                "
            >
                <label
                    for="history-per-page"
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Mostrar
                </label>

                <select
                    id="history-per-page"
                    wire:model.live="perPage"
                    class="
                        h-9
                        px-2

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        text-[var(--theme-text)]

                        outline-none
                    "
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>


        {{-- ========================================================
            TABLA - ESCRITORIO
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
                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                                whitespace-nowrap
                            "
                        >
                            Fecha
                        </th>

                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                            "
                        >
                            Equipo
                        </th>

                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                                whitespace-nowrap
                            "
                        >
                            Movimiento
                        </th>

                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                            "
                        >
                            Colaborador
                        </th>

                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                            "
                        >
                            Ubicación
                        </th>

                        <th
                            class="
                                px-4
                                py-3

                                text-left
                                font-medium
                            "
                        >
                            Realizado por
                        </th>
                    </tr>
                </thead>


                <tbody>
                    @forelse ($historial as $registro)

                        @php
                            $esAsignacion =
                                $registro->tipo_registro === 'asignacion';

                            $esReasignacion =
                                $registro->tipo_registro === 'reasignacion';

                            $badgeClasses =
                                $esAsignacion
                                    ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                    : (
                                        $esReasignacion
                                            ? 'bg-amber-500/10 text-amber-500'
                                            : 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]'
                                    );

                            $ubicacionPartes = array_filter([
                                $registro->sede ?? null,
                                $registro->area ?? null,
                                $registro->departamento ?? null,
                                $registro->ubicacion ?? null,
                            ]);

                            $ubicacionTexto =
                                ! empty($ubicacionPartes)
                                    ? implode(' · ', $ubicacionPartes)
                                    : 'Sin ubicación';

                            $fechaFormateada =
                                $registro->fecha
                                    ? \Illuminate\Support\Carbon::parse(
                                        $registro->fecha
                                    )->format('d/m/Y H:i')
                                    : '—';

                            $codigo =
                                $registro->codigo_inventario
                                    ?: 'Sin código';

                            $nombreEquipo =
                                $registro->nombre_equipo
                                    ?: 'Equipo';

                            $colaboradorNuevo =
                                trim(
                                    (string) (
                                        $registro->nombre_colaborador
                                        ?? ''
                                    )
                                );

                            $colaboradorAnterior =
                                trim(
                                    (string) (
                                        $registro->colaborador_anterior
                                        ?? ''
                                    )
                                );
                        @endphp

                        <tr
                            wire:key="
                                history-desktop-
                                {{ $registro->tipo_registro }}-
                                {{ $registro->id }}
                            "
                            class="
                                border-t
                                border-[var(--theme-border)]

                                hover:bg-[var(--theme-primary-soft-subtle)]

                                transition-colors
                            "
                        >

                            {{-- FECHA --}}
                            <td
                                class="
                                    px-4
                                    py-3

                                    whitespace-nowrap

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $fechaFormateada }}
                            </td>


                            {{-- EQUIPO --}}
                            <td class="px-4 py-3">
                                <div class="min-w-[170px]">
                                    <p
                                        class="
                                            font-medium
                                            text-[var(--theme-text-strong)]
                                        "
                                    >
                                        {{ $codigo }}
                                    </p>

                                    <p
                                        class="
                                            mt-0.5
                                            text-[11px]
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ $nombreEquipo }}

                                        @if (! empty($registro->host))
                                            · {{ $registro->host }}
                                        @endif
                                    </p>
                                </div>
                            </td>


                            {{-- MOVIMIENTO --}}
                            <td class="px-4 py-3">
                                <div class="min-w-[120px]">
                                    <span
                                        class="
                                            inline-flex
                                            items-center

                                            px-2
                                            py-1

                                            rounded-full

                                            text-[10px]
                                            font-medium

                                            {{ $badgeClasses }}
                                        "
                                    >
                                        {{ $registro->movimiento }}
                                    </span>

                                    @if (! empty($registro->tipo_asignacion))
                                        <p
                                            class="
                                                mt-1
                                                text-[10px]
                                                text-[var(--theme-text-muted)]
                                            "
                                        >
                                            {{ $registro->tipo_asignacion }}
                                        </p>
                                    @endif
                                </div>
                            </td>


                            {{-- COLABORADOR --}}
                            <td class="px-4 py-3">
                                <div class="min-w-[180px]">

                                    @if (
                                        $esReasignacion
                                        && $colaboradorAnterior !== ''
                                        && $colaboradorNuevo !== ''
                                    )
                                        <div
                                            class="
                                                flex
                                                items-center
                                                gap-1.5

                                                text-[var(--theme-text)]
                                            "
                                        >
                                            <span>
                                                {{ $colaboradorAnterior }}
                                            </span>

                                            <svg
                                                class="
                                                    w-3
                                                    h-3
                                                    shrink-0
                                                    text-[var(--theme-text-muted)]
                                                "
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M5 12h14"/>
                                                <path d="M14 7l5 5-5 5"/>
                                            </svg>

                                            <span
                                                class="
                                                    font-medium
                                                    text-[var(--theme-text-strong)]
                                                "
                                            >
                                                {{ $colaboradorNuevo }}
                                            </span>
                                        </div>
                                    @else
                                        <p
                                            class="
                                                font-medium
                                                text-[var(--theme-text-strong)]
                                            "
                                        >
                                            {{ $colaboradorNuevo !== ''
                                                ? $colaboradorNuevo
                                                : '—'
                                            }}
                                        </p>
                                    @endif

                                    @if (! empty($registro->numero_colaborador))
                                        <p
                                            class="
                                                mt-0.5
                                                text-[10px]
                                                text-[var(--theme-text-muted)]
                                            "
                                        >
                                            {{ $registro->numero_colaborador }}
                                        </p>
                                    @endif

                                    @if (! empty($registro->motivo))
                                        <p
                                            class="
                                                mt-1
                                                text-[10px]
                                                text-[var(--theme-text-muted)]
                                            "
                                        >
                                            {{ $registro->motivo }}
                                        </p>
                                    @endif
                                </div>
                            </td>


                            {{-- UBICACIÓN --}}
                            <td class="px-4 py-3">
                                <p
                                    class="
                                        min-w-[180px]
                                        leading-relaxed
                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $ubicacionTexto }}
                                </p>
                            </td>


                            {{-- USUARIO --}}
                            <td class="px-4 py-3">
                                <p
                                    class="
                                        min-w-[130px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ ! empty($registro->usuario)
                                        ? $registro->usuario
                                        : '—'
                                    }}
                                </p>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="
                                    px-5
                                    py-12

                                    text-center
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                No se encontraron registros con los filtros seleccionados.
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

            @forelse ($historial as $registro)

                @php
                    $esAsignacion =
                        $registro->tipo_registro === 'asignacion';

                    $esReasignacion =
                        $registro->tipo_registro === 'reasignacion';

                    $badgeClasses =
                        $esAsignacion
                            ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                            : (
                                $esReasignacion
                                    ? 'bg-amber-500/10 text-amber-500'
                                    : 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]'
                            );

                    $ubicacionPartes = array_filter([
                        $registro->sede ?? null,
                        $registro->area ?? null,
                        $registro->departamento ?? null,
                        $registro->ubicacion ?? null,
                    ]);

                    $ubicacionTexto =
                        ! empty($ubicacionPartes)
                            ? implode(' · ', $ubicacionPartes)
                            : 'Sin ubicación';

                    $fechaFormateada =
                        $registro->fecha
                            ? \Illuminate\Support\Carbon::parse(
                                $registro->fecha
                            )->format('d/m/Y H:i')
                            : '—';

                    $colaboradorNuevo =
                        trim(
                            (string) (
                                $registro->nombre_colaborador
                                ?? ''
                            )
                        );

                    $colaboradorAnterior =
                        trim(
                            (string) (
                                $registro->colaborador_anterior
                                ?? ''
                            )
                        );
                @endphp

                <article
                    wire:key="
                        history-mobile-
                        {{ $registro->tipo_registro }}-
                        {{ $registro->id }}
                    "
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        shadow-sm
                    "
                >

                    {{-- CABECERA --}}
                    <div
                        class="
                            flex
                            items-start
                            justify-between
                            gap-3
                        "
                    >
                        <div class="min-w-0">
                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ $registro->codigo_inventario ?: 'Sin código' }}
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ $registro->nombre_equipo ?: 'Equipo' }}
                            </p>
                        </div>

                        <span
                            class="
                                shrink-0

                                inline-flex
                                items-center

                                px-2
                                py-1

                                rounded-full

                                text-[10px]
                                font-medium

                                {{ $badgeClasses }}
                            "
                        >
                            {{ $registro->movimiento }}
                        </span>
                    </div>


                    <div
                        class="
                            mt-4

                            grid
                            grid-cols-2
                            gap-x-4
                            gap-y-3
                        "
                    >

                        {{-- FECHA --}}
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

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $fechaFormateada }}
                            </p>
                        </div>


                        {{-- HOST --}}
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

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ ! empty($registro->host)
                                    ? $registro->host
                                    : '—'
                                }}
                            </p>
                        </div>


                        {{-- COLABORADOR --}}
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

                            @if (
                                $esReasignacion
                                && $colaboradorAnterior !== ''
                                && $colaboradorNuevo !== ''
                            )
                                <div
                                    class="
                                        mt-1

                                        flex
                                        items-center
                                        flex-wrap
                                        gap-1.5

                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    <span>
                                        {{ $colaboradorAnterior }}
                                    </span>

                                    <svg
                                        class="
                                            w-3
                                            h-3
                                            text-[var(--theme-text-muted)]
                                        "
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M5 12h14"/>
                                        <path d="M14 7l5 5-5 5"/>
                                    </svg>

                                    <span
                                        class="
                                            font-medium
                                            text-[var(--theme-text-strong)]
                                        "
                                    >
                                        {{ $colaboradorNuevo }}
                                    </span>
                                </div>
                            @else
                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $colaboradorNuevo !== ''
                                        ? $colaboradorNuevo
                                        : '—'
                                    }}
                                </p>
                            @endif
                        </div>


                        {{-- UBICACIÓN --}}
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

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    leading-relaxed
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $ubicacionTexto }}
                            </p>
                        </div>


                        @if (! empty($registro->motivo))
                            <div class="col-span-2">
                                <p
                                    class="
                                        text-[10px]
                                        uppercase
                                        tracking-wide
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Motivo
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $registro->motivo }}
                                </p>
                            </div>
                        @endif


                        {{-- REALIZADO POR --}}
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

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ ! empty($registro->usuario)
                                    ? $registro->usuario
                                    : '—'
                                }}
                            </p>
                        </div>

                    </div>

                </article>

            @empty

                <div
                    class="
                        px-5
                        py-12

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        text-center
                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >
                    No se encontraron registros con los filtros seleccionados.
                </div>

            @endforelse

        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($historial->hasPages())
            <div
                class="
                    mt-3
                    md:mt-0

                    px-4
                    py-3

                    md:border-t
                    md:border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    rounded-lg
                    md:rounded-none

                    border
                    md:border-x-0
                    md:border-b-0
                    border-[var(--theme-border)]

                    flex
                    flex-col
                    sm:flex-row
                    sm:items-center
                    sm:justify-between

                    gap-3
                "
            >
                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Mostrando

                    <span
                        class="
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $historial->firstItem() }}
                    </span>

                    a

                    <span
                        class="
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $historial->lastItem() }}
                    </span>

                    de

                    <span
                        class="
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $historial->total() }}
                    </span>
                </p>


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        sm:justify-end

                        gap-2
                    "
                >
                    <button
                        type="button"
                        wire:click="previousPage('historyPage')"
                        @disabled($historial->onFirstPage())
                        class="
                            h-9
                            px-3

                            rounded-lg

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-xs
                            font-medium
                            text-[var(--theme-text-muted)]

                            hover:bg-[var(--theme-surface-soft)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed

                            transition-colors
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

                        <span
                            class="
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $historial->currentPage() }}
                        </span>

                        de

                        <span
                            class="
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ max(1, $historial->lastPage()) }}
                        </span>
                    </span>


                    <button
                        type="button"
                        wire:click="nextPage('historyPage')"
                        @disabled(! $historial->hasMorePages())
                        class="
                            h-9
                            px-3

                            rounded-lg

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-xs
                            font-medium
                            text-[var(--theme-text-muted)]

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

</div>