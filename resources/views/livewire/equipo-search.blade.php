<div>

    {{-- ============================================================
        SIN TEXTO DE BÚSQUEDA
    ============================================================ --}}
    @if (trim($search) === '')

        {{-- Volver al inventario --}}
        <div class="flex justify-end mb-4 sm:mb-6">

            <a
                href="{{ route('equipos.index') }}"
                class="
                    h-10
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    border
                    border-[var(--theme-border-strong)]

                    rounded-lg

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text)]
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

                Inventario
            </a>

        </div>


        {{-- Estado inicial --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                px-5
                py-14

                text-center
            "
        >

            <div
                class="
                    mx-auto

                    w-14
                    h-14

                    rounded-full

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>
            </div>


            <h2
                class="
                    mt-4

                    text-base
                    font-semibold

                    text-[var(--theme-text-strong)]
                "
            >
                Buscar equipos
            </h2>


            <p
                class="
                    mt-2

                    text-sm
                    leading-relaxed

                    text-[var(--theme-text-muted)]
                "
            >
                Utiliza el buscador superior para localizar un equipo por nombre,
                ID, código de inventario, número de serie, marca o modelo.
            </p>

        </section>


    {{-- ============================================================
        HAY TEXTO DE BÚSQUEDA
    ============================================================ --}}
    @else

        {{-- ========================================================
            CABECERA DE RESULTADOS
        ======================================================== --}}
        <div
            class="
                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-3

                mb-4
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
                    Resultados de búsqueda
                </h2>


                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    {{ number_format($equipos->total()) }}

                    {{ $equipos->total() === 1 ? 'resultado' : 'resultados' }}

                    para

                    <span
                        class="
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        “{{ $search }}”
                    </span>
                </p>

            </div>


            {{-- Volver al inventario --}}
            <a
                href="{{ route('equipos.index') }}"
                class="
                    shrink-0

                    h-10
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    border
                    border-[var(--theme-border-strong)]

                    rounded-lg

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text)]
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

                Inventario
            </a>

        </div>


        {{-- ========================================================
            RESULTADOS ENCONTRADOS
        ======================================================== --}}
        @if ($equipos->count() > 0)

            <div
                class="
                    grid
                    grid-cols-1

                    lg:grid-cols-2

                    gap-3
                    sm:gap-4
                "
            >

                @foreach ($equipos as $equipo)

                    @php
                        $nombre = trim(
                            ($equipo->tipo_equipo ?? '') . ' ' .
                            ($equipo->marca ?? '') . ' ' .
                            ($equipo->modelo ?? '')
                        );

                        if ($nombre === '') {
                            $nombre = $equipo->nombre_equipo ?? 'Equipo';
                        }
                    @endphp


                    <article
                        wire:key="search-equipo-{{ $equipo->id_equipo }}"
                        class="
                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border)]

                            rounded-xl

                            p-4
                            sm:p-5

                            hover:border-[var(--theme-primary-border)]
                            hover:shadow-sm

                            transition-all
                        "
                    >

                        {{-- ====================================================
                            CABECERA DE TARJETA
                        ==================================================== --}}
                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-3
                            "
                        >

                            <div class="min-w-0 flex-1">

                                <h3
                                    class="
                                        text-sm
                                        sm:text-base

                                        font-semibold
                                        leading-snug

                                        text-[var(--theme-text-strong)]

                                        break-words
                                    "
                                >
                                    {{ $nombre }}
                                </h3>


                                @if (
                                    !empty($equipo->nombre_equipo) &&
                                    $equipo->nombre_equipo !== $nombre
                                )

                                    <p
                                        class="
                                            mt-1
                                            text-xs
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ $equipo->nombre_equipo }}
                                    </p>

                                @endif

                            </div>


                            <div class="shrink-0">

                                <x-status-badge
                                    :status="$equipo->estado ?? 'Sin estado'"
                                />

                            </div>

                        </div>


                        {{-- ====================================================
                            DATOS
                        ==================================================== --}}
                        <div
                            class="
                                grid
                                grid-cols-1
                                sm:grid-cols-2

                                gap-x-5
                                gap-y-3

                                mt-5
                            "
                        >

                            {{-- Código de inventario --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Código de inventario
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $equipo->codigo_inventario ?? '—' }}
                                </p>

                            </div>


                            {{-- ID --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    ID interno
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $equipo->id_equipo }}
                                </p>

                            </div>


                            {{-- Número de serie --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Número de serie
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]

                                        break-all
                                    "
                                >
                                    {{ $equipo->numero_serie ?? '—' }}
                                </p>

                            </div>


                            {{-- Host --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Host
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $equipo->host ?? '—' }}
                                </p>

                            </div>


                            {{-- Marca / Modelo --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Marca / Modelo
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $equipo->marca ?? '—' }}

                                    @if (!empty($equipo->modelo))
                                        / {{ $equipo->modelo }}
                                    @endif
                                </p>

                            </div>


                            {{-- Ubicación --}}
                            <div>

                                <p
                                    class="
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Ubicación
                                </p>

                                <p
                                    class="
                                        mt-1

                                        text-xs
                                        font-medium

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $equipo->ubicacion ?? '—' }}
                                </p>

                            </div>

                        </div>


                        {{-- ====================================================
                            ACCIONES
                        ==================================================== --}}
                        <div
                            class="
                                flex
                                flex-col

                                sm:flex-row
                                sm:items-center
                                sm:justify-end

                                gap-2

                                mt-5
                                pt-4

                                border-t
                                border-[var(--theme-border)]
                            "
                        >

                            {{-- Ver detalle --}}
                            <a
                                href="{{ route('equipos.show', $equipo->id_equipo) }}"
                                class="
                                    h-9
                                    px-4

                                    flex
                                    items-center
                                    justify-center
                                    gap-2

                                    border
                                    border-[var(--theme-border-strong)]

                                    rounded-lg

                                    bg-[var(--theme-surface)]

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text-muted)]

                                    hover:bg-[var(--theme-surface-soft)]
                                    hover:text-[var(--theme-text)]
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
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                Ver detalle
                            </a>


                            {{-- Editar --}}
                            <a
                                href="{{ route('equipos.edit', $equipo->id_equipo) }}"
                                class="
                                    h-9
                                    px-4

                                    flex
                                    items-center
                                    justify-center
                                    gap-2

                                    rounded-lg

                                    bg-[var(--theme-primary)]
                                    hover:bg-[var(--theme-primary-hover)]

                                    text-xs
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
                                    stroke-width="1.6"
                                >
                                    <path d="M4 20h4l11-11a2.8 2.8 0 00-4-4L4 16v4z"/>
                                    <path d="M13.5 6.5l4 4"/>
                                </svg>

                                Editar
                            </a>

                        </div>

                    </article>

                @endforeach

            </div>


            {{-- ====================================================
                PAGINACIÓN
            ==================================================== --}}
            @if ($equipos->hasPages())

                <div class="mt-5 sm:mt-6">

                    {{-- Escritorio --}}
                    <div class="hidden md:block">
                        {{ $equipos->links() }}
                    </div>


                    {{-- Móvil --}}
                    <div
                        class="
                            md:hidden

                            flex
                            items-center
                            justify-between

                            gap-3
                        "
                    >

                        {{-- Anterior --}}
                        @if ($equipos->onFirstPage())

                            <span
                                class="
                                    h-10
                                    px-4

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[var(--theme-border)]

                                    rounded-lg

                                    bg-[var(--theme-surface-soft)]

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    opacity-50
                                "
                            >
                                Anterior
                            </span>

                        @else

                            <button
                                type="button"
                                wire:click="previousPage"
                                class="
                                    h-10
                                    px-4

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[var(--theme-border-strong)]

                                    rounded-lg

                                    bg-[var(--theme-surface)]

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    hover:bg-[var(--theme-surface-soft)]
                                    hover:text-[var(--theme-text)]

                                    transition-colors
                                "
                            >
                                Anterior
                            </button>

                        @endif


                        {{-- Página --}}
                        <span
                            class="
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $equipos->currentPage() }}
                            /
                            {{ $equipos->lastPage() }}
                        </span>


                        {{-- Siguiente --}}
                        @if ($equipos->hasMorePages())

                            <button
                                type="button"
                                wire:click="nextPage"
                                class="
                                    h-10
                                    px-4

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[var(--theme-border-strong)]

                                    rounded-lg

                                    bg-[var(--theme-surface)]

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    hover:bg-[var(--theme-surface-soft)]
                                    hover:text-[var(--theme-text)]

                                    transition-colors
                                "
                            >
                                Siguiente
                            </button>

                        @else

                            <span
                                class="
                                    h-10
                                    px-4

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[var(--theme-border)]

                                    rounded-lg

                                    bg-[var(--theme-surface-soft)]

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    opacity-50
                                "
                            >
                                Siguiente
                            </span>

                        @endif

                    </div>

                </div>

            @endif


        {{-- ========================================================
            SIN RESULTADOS
        ======================================================== --}}
        @else

            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    px-5
                    py-14

                    text-center
                "
            >

                <div
                    class="
                        mx-auto

                        w-14
                        h-14

                        rounded-full

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]
                        text-[var(--theme-text-muted)]
                    "
                >
                    <svg
                        class="w-7 h-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-4-4"/>
                    </svg>
                </div>


                <h3
                    class="
                        mt-4

                        text-base
                        font-semibold

                        text-[var(--theme-text-strong)]
                    "
                >
                    No encontramos equipos
                </h3>


                <p
                    class="
                        mt-2

                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >

                    No hay resultados para

                    <span
                        class="
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        “{{ $search }}”
                    </span>.

                </p>


                <a
                    href="{{ route('equipos.search') }}"
                    class="
                        mt-5

                        h-10
                        px-4

                        inline-flex
                        items-center
                        justify-center

                        border
                        border-[var(--theme-border-strong)]

                        rounded-lg

                        bg-[var(--theme-surface)]

                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]

                        hover:bg-[var(--theme-surface-soft)]
                        hover:text-[var(--theme-text)]
                        hover:border-[var(--theme-primary-border)]

                        transition-colors
                    "
                >
                    Limpiar búsqueda
                </a>

            </section>

        @endif

    @endif

</div>