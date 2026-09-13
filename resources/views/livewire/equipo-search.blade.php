<div>

    {{-- ============================================================
        BUSCADOR
    ============================================================ --}}
    <section
        class="
            bg-white
            border border-[#50514F]/10
            rounded-xl
            p-4
            sm:p-5
            mb-4
            sm:mb-6
        "
    >

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            {{-- Campo de búsqueda --}}
            <div
                class="
                    relative
                    w-full
                    sm:max-w-xl
                "
            >

                {{-- Lupa --}}
                <svg
                    class="
                        absolute
                        left-4
                        top-1/2
                        -translate-y-1/2
                        w-5
                        h-5
                        text-[#50514F]/40
                        pointer-events-none
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>


                <input
                    type="text"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Buscar por nombre, ID, código, marca o modelo..."
                    autocomplete="off"
                    class="
                        w-full
                        h-12
                        pl-12
                        pr-11

                        border
                        border-[#50514F]/15
                        rounded-lg

                        bg-white

                        text-sm
                        text-[#50514F]

                        placeholder:text-[#50514F]/40

                        focus:ring-1
                        focus:ring-[#247BA0]
                        focus:border-[#247BA0]
                    "
                >


                {{-- Limpiar --}}
                @if ($search !== '')

                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="
                            absolute
                            right-3
                            top-1/2
                            -translate-y-1/2

                            w-7
                            h-7

                            flex
                            items-center
                            justify-center

                            rounded-full

                            text-[#50514F]/40

                            hover:text-[#50514F]/70
                            hover:bg-[#50514F]/5

                            transition-colors
                        "
                        aria-label="Limpiar búsqueda"
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 6l12 12"/>
                            <path d="M18 6L6 18"/>
                        </svg>
                    </button>

                @endif


                {{-- Indicador de carga --}}
                <div
                    wire:loading.flex
                    wire:target="search"
                    class="
                        absolute
                        right-11
                        top-1/2
                        -translate-y-1/2

                        items-center
                        justify-center
                    "
                >
                    <svg
                        class="
                            w-4
                            h-4
                            animate-spin
                            text-[#247BA0]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="2"
                            opacity="0.2"
                        />

                        <path
                            d="M21 12a9 9 0 0 0-9-9"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>

            </div>


            {{-- Volver al inventario --}}
            <a
                href="{{ route('equipos.index') }}"
                class="
                    shrink-0
                    h-10

                    flex
                    items-center
                    justify-center
                    gap-2

                    border
                    border-[#50514F]/20
                    rounded-lg

                    px-4

                    bg-white

                    text-sm
                    font-medium
                    text-[#50514F]/65

                    hover:bg-[#50514F]/5

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

    </section>


    {{-- ============================================================
        SIN TEXTO DE BÚSQUEDA
    ============================================================ --}}
    @if (trim($search) === '')

        <section
            class="
                bg-white
                border border-[#50514F]/10
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

                    bg-[#247BA0]/10
                    text-[#247BA0]
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
                    text-[#50514F]
                "
            >
                Buscar equipos
            </h2>


            <p
                class="
                    mt-2
                    text-sm
                    leading-relaxed
                    text-[#50514F]/50
                "
            >
                Escribe un nombre, ID, código de inventario, número de serie,
                marca o modelo.
            </p>

        </section>


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    @else

        {{-- Cabecera de resultados --}}
        <div
            class="
                flex
                items-center
                justify-between
                gap-3
                mb-4
            "
        >

            <div>

                <h2
                    class="
                        text-base
                        font-semibold
                        text-[#50514F]
                    "
                >
                    Resultados de búsqueda
                </h2>


                <p
                    class="
                        mt-1
                        text-xs
                        text-[#50514F]/50
                    "
                >
                    {{ number_format($equipos->total()) }}

                    {{ $equipos->total() === 1 ? 'resultado' : 'resultados' }}

                    para

                    <span
                        class="
                            font-medium
                            text-[#50514F]/75
                        "
                    >
                        “{{ $search }}”
                    </span>
                </p>

            </div>

        </div>


        @if ($equipos->count() > 0)

            {{-- ====================================================
                TARJETAS
            ==================================================== --}}
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
                            bg-white
                            border
                            border-[#50514F]/10
                            rounded-xl

                            p-4
                            sm:p-5

                            hover:border-[#247BA0]/25
                            hover:shadow-sm

                            transition-all
                        "
                    >

                        {{-- Cabecera --}}
                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-3
                            "
                        >

                            <div class="min-w-0 flex-1">

                                {{-- Nombre --}}
                                <h3
                                    class="
                                        text-sm
                                        sm:text-base

                                        font-semibold
                                        leading-snug

                                        text-[#25344A]

                                        break-words
                                    "
                                >
                                    {{ $nombre }}
                                </h3>


                                {{-- Nombre registrado --}}
                                @if (
                                    !empty($equipo->nombre_equipo) &&
                                    $equipo->nombre_equipo !== $nombre
                                )

                                    <p
                                        class="
                                            mt-1
                                            text-xs
                                            text-[#50514F]/50
                                        "
                                    >
                                        {{ $equipo->nombre_equipo }}
                                    </p>

                                @endif

                            </div>


                            {{-- Estado --}}
                            <div class="shrink-0">

                                <x-status-badge
                                    :status="$equipo->estado ?? 'Sin estado'"
                                />

                            </div>

                        </div>


                        {{-- Datos principales --}}
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

                            {{-- Código --}}
                            <div>

                                <p class="text-[11px] text-[#50514F]/40">
                                    Código de inventario
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
                                    "
                                >
                                    {{ $equipo->codigo_inventario ?? '—' }}
                                </p>

                            </div>


                            {{-- ID --}}
                            <div>

                                <p class="text-[11px] text-[#50514F]/40">
                                    ID interno
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
                                    "
                                >
                                    {{ $equipo->id_equipo }}
                                </p>

                            </div>


                            {{-- Número de serie --}}
                            <div>

                                <p class="text-[11px] text-[#50514F]/40">
                                    Número de serie
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
                                        break-all
                                    "
                                >
                                    {{ $equipo->numero_serie ?? '—' }}
                                </p>

                            </div>


                            {{-- Host --}}
                            <div>

                                <p class="text-[11px] text-[#50514F]/40">
                                    Host
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
                                    "
                                >
                                    {{ $equipo->host ?? '—' }}
                                </p>

                            </div>


                            {{-- Marca --}}
                            <div>

                                <p class="text-[11px] text-[#50514F]/40">
                                    Marca / Modelo
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
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

                                <p class="text-[11px] text-[#50514F]/40">
                                    Ubicación
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[#50514F]/75
                                    "
                                >
                                    {{ $equipo->ubicacion ?? '—' }}
                                </p>

                            </div>

                        </div>


                        {{-- Acciones --}}
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
                                border-[#50514F]/10
                            "
                        >

                            {{-- Ver detalle --}}
                            <a
                                href="{{ route(
                                    'equipos.show',
                                    $equipo->id_equipo
                                ) }}"
                                class="
                                    h-9
                                    px-4

                                    flex
                                    items-center
                                    justify-center
                                    gap-2

                                    border
                                    border-[#50514F]/15
                                    rounded-lg

                                    bg-white

                                    text-xs
                                    font-medium
                                    text-[#50514F]/70

                                    hover:bg-[#50514F]/5

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
                                href="{{ route(
                                    'equipos.edit',
                                    $equipo->id_equipo
                                ) }}"
                                class="
                                    h-9
                                    px-4

                                    flex
                                    items-center
                                    justify-center
                                    gap-2

                                    rounded-lg

                                    bg-[#247BA0]
                                    hover:bg-[#1d6688]

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

                <div
                    class="
                        mt-5
                        sm:mt-6
                    "
                >

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

                        @if ($equipos->onFirstPage())

                            <span
                                class="
                                    h-10
                                    px-4

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[#50514F]/10
                                    rounded-lg

                                    bg-white

                                    text-xs
                                    text-[#50514F]/30
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
                                    border-[#50514F]/15
                                    rounded-lg

                                    bg-white

                                    text-xs
                                    text-[#50514F]/70
                                "
                            >
                                Anterior
                            </button>

                        @endif


                        <span
                            class="
                                text-xs
                                text-[#50514F]/50
                            "
                        >
                            {{ $equipos->currentPage() }}
                            /
                            {{ $equipos->lastPage() }}
                        </span>


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
                                    border-[#50514F]/15
                                    rounded-lg

                                    bg-white

                                    text-xs
                                    text-[#50514F]/70
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
                                    border-[#50514F]/10
                                    rounded-lg

                                    bg-white

                                    text-xs
                                    text-[#50514F]/30
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
                    bg-white
                    border border-[#50514F]/10
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

                        bg-[#50514F]/5
                        text-[#50514F]/30
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
                        text-[#50514F]
                    "
                >
                    No encontramos equipos
                </h3>


                <p
                    class="
                        mt-2
                        text-sm
                        text-[#50514F]/50
                    "
                >
                    No hay resultados para
                    <span class="font-medium">
                        “{{ $search }}”
                    </span>.
                </p>


                <button
                    type="button"
                    wire:click="clearSearch"
                    class="
                        mt-5
                        h-10
                        px-4

                        inline-flex
                        items-center
                        justify-center

                        border
                        border-[#50514F]/15
                        rounded-lg

                        text-sm
                        font-medium
                        text-[#50514F]/65

                        hover:bg-[#50514F]/5

                        transition-colors
                    "
                >
                    Limpiar búsqueda
                </button>

            </section>

        @endif

    @endif

</div>