<div
    x-data="{ filtersOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', filtersOpen)"
    @keydown.escape.window="filtersOpen = false"
    @resize.window="if (window.innerWidth >= 768) filtersOpen = false"
>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>


    {{-- ============================================================
        FILTROS ACTIVOS
    ============================================================ --}}
    @php
        $activeFilters = [];

        if ($tipoActivo) {
            $item = collect($tiposActivo)->first(
                fn ($item) =>
                    (string) $item['id_tipo_equipo'] === (string) $tipoActivo
            );

            $activeFilters[] = [
                'property' => 'tipoActivo',
                'label' => $item['nombre'] ?? 'Tipo de activo',
            ];
        }

        if ($marca) {
            $item = collect($marcas)->first(
                fn ($item) =>
                    (string) $item['id_marca'] === (string) $marca
            );

            $activeFilters[] = [
                'property' => 'marca',
                'label' => $item['nombre'] ?? 'Marca',
            ];
        }

        if ($modelo) {
            $item = collect($modelos)->first(
                fn ($item) =>
                    (string) $item['id_modelo'] === (string) $modelo
            );

            $activeFilters[] = [
                'property' => 'modelo',
                'label' => $item['nombre'] ?? 'Modelo',
            ];
        }

        if ($numeroSerie) {
            $activeFilters[] = [
                'property' => 'numeroSerie',
                'label' => 'SN: ' . $numeroSerie,
            ];
        }

        if ($serviceTag) {
            $activeFilters[] = [
                'property' => 'serviceTag',
                'label' => 'Tag: ' . $serviceTag,
            ];
        }

        if ($direccionIp) {
            $activeFilters[] = [
                'property' => 'direccionIp',
                'label' => 'IP: ' . $direccionIp,
            ];
        }

        if ($estado) {
            $item = collect($estados)->first(
                fn ($item) =>
                    (string) $item['id_valor'] === (string) $estado
            );

            $activeFilters[] = [
                'property' => 'estado',
                'label' => $item['nombre'] ?? 'Estado',
            ];
        }

        if ($area) {
            $item = collect($areas)->first(
                fn ($item) =>
                    (string) $item['id_area'] === (string) $area
            );

            $activeFilters[] = [
                'property' => 'area',
                'label' => $item['nombre'] ?? 'Área',
            ];
        }
    @endphp


    {{-- ============================================================
        CONTROLES MÓVILES
    ============================================================ --}}
    <div class="md:hidden mb-5">

        {{-- ========================================================
            BUSCADOR GENERAL
        ======================================================== --}}
        <div class="relative mb-3">

            <svg
                class="
                    absolute
                    left-4 top-1/2
                    -translate-y-1/2
                    w-5 h-5
                    text-[#50514F]/45
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
                wire:model.live.debounce.400ms="search"
                placeholder="Buscar equipos..."
                autocomplete="off"
                class="
                    w-full
                    h-12
                    pl-12 pr-10
                    rounded-lg
                    border border-[#50514F]/15
                    bg-white
                    text-sm
                    text-[#50514F]
                    placeholder:text-[#50514F]/40
                    focus:border-[#247BA0]
                    focus:ring-1
                    focus:ring-[#247BA0]
                "
            >


            @if ($search)

                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="
                        absolute
                        right-3 top-1/2
                        -translate-y-1/2
                        w-7 h-7
                        flex items-center justify-center
                        rounded-full
                        text-[#50514F]/45
                        hover:text-[#50514F]
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

        </div>


        {{-- ========================================================
            FILTRAR / ORDENAR
        ======================================================== --}}
        <div class="flex items-center gap-3">

            {{-- Filtros --}}
            <button
                type="button"
                @click="filtersOpen = true"
                class="
                    h-11
                    flex items-center gap-2
                    px-4
                    rounded-lg
                    border
                    text-sm font-medium
                    transition-colors

                    {{ count($activeFilters) > 0
                        ? 'border-[#247BA0] text-[#247BA0] bg-[#247BA0]/5'
                        : 'border-[#50514F]/15 bg-white text-[#50514F]/80 hover:bg-[#50514F]/5'
                    }}
                "
            >

                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M4 5h16l-6 7v5l-4 2v-7z"/>
                </svg>

                <span>
                    Filtros
                </span>


                @if (count($activeFilters) > 0)

                    <span>
                        · {{ count($activeFilters) }}
                    </span>

                @endif

            </button>


            {{-- Ordenar --}}
            <button
                type="button"
                wire:click="$set('sort', '{{ $sort === 'asc' ? 'desc' : 'asc' }}')"
                class="
                    h-11
                    flex items-center gap-2
                    px-4
                    rounded-lg
                    border border-[#50514F]/15
                    bg-white
                    text-sm font-medium
                    text-[#50514F]/80
                    hover:bg-[#50514F]/5
                    transition-colors
                "
            >

                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
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
            CHIPS DE FILTROS ACTIVOS
        ======================================================== --}}
        @if (count($activeFilters) > 0)

            <div
                class="
                    flex items-center gap-2
                    overflow-x-auto
                    mt-4 pb-1
                "
            >

                @foreach ($activeFilters as $filter)

                    <button
                        type="button"
                        wire:click="$set('{{ $filter['property'] }}', '')"
                        class="
                            shrink-0
                            flex items-center gap-1.5
                            px-3 py-1.5
                            rounded-full
                            bg-[#247BA0]/10
                            text-[#247BA0]
                            text-xs font-medium
                        "
                    >

                        <span>
                            {{ $filter['label'] }}
                        </span>

                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M6 6l12 12"/>
                            <path d="M18 6L6 18"/>
                        </svg>

                    </button>

                @endforeach


                <button
                    type="button"
                    wire:click="resetFilters"
                    class="
                        shrink-0
                        px-2 py-1.5
                        text-xs font-medium
                        text-[#247BA0]
                    "
                >
                    Limpiar
                </button>

            </div>

        @endif

    </div>


    {{-- ============================================================
        BOTTOM SHEET DE FILTROS - MÓVIL
    ============================================================ --}}
    <div
        x-show="filtersOpen"
        x-cloak
        class="fixed inset-0 z-[100] md:hidden"
    >

        {{-- Overlay --}}
        <div
            x-show="filtersOpen"
            x-transition.opacity.duration.200ms
            @click="filtersOpen = false"
            class="
                absolute inset-0
                bg-[#25344A]/40
                backdrop-blur-[1px]
            "
        ></div>


        {{-- Panel --}}
        <div
            x-show="filtersOpen"

            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"

            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"

            class="
                absolute
                left-0 right-0 bottom-0
                max-h-[90vh]
                bg-white
                rounded-t-[24px]
                shadow-2xl
                flex flex-col
                overflow-hidden
            "
        >

            {{-- Barra superior --}}
            <div class="shrink-0 flex justify-center pt-3">

                <div
                    class="
                        w-12 h-1
                        rounded-full
                        bg-[#50514F]/25
                    "
                ></div>

            </div>


            {{-- Cabecera --}}
            <div
                class="
                    shrink-0
                    flex items-center justify-between
                    px-5 py-4
                "
            >

                <h2 class="text-xl font-semibold text-[#25344A]">
                    Filtros de búsqueda
                </h2>


                <button
                    type="button"
                    @click="filtersOpen = false"
                    class="
                        w-9 h-9
                        flex items-center justify-center
                        rounded-full
                        text-[#25344A]
                        hover:bg-[#50514F]/5
                        transition-colors
                    "
                    aria-label="Cerrar filtros"
                >

                    <svg
                        class="w-6 h-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M6 6l12 12"/>
                        <path d="M18 6L6 18"/>
                    </svg>

                </button>

            </div>


            {{-- Formulario --}}
            <form
                wire:submit.prevent="$refresh"
                @submit="filtersOpen = false"
                class="flex flex-col min-h-0 flex-1"
            >

                {{-- =================================================
                    CAMPOS
                ================================================= --}}
                <div
                    class="
                        flex-1
                        overflow-y-auto
                        overscroll-contain
                        px-5 pb-6
                    "
                >

                    <div class="space-y-5">

                        {{-- Tipo de activo --}}
                        <x-searchable-select
                            label="Tipo de activo"
                            wire-model="tipoActivo"
                            :options="collect($tiposActivo)->map(fn ($t) => [
                                'value' => $t['id_tipo_equipo'],
                                'label' => $t['nombre']
                            ])"
                            placeholder="Seleccionar tipo..."
                        />


                        {{-- Marca --}}
                        <x-searchable-select
                            label="Marca"
                            wire-model="marca"
                            :options="collect($marcas)->map(fn ($m) => [
                                'value' => $m['id_marca'],
                                'label' => $m['nombre']
                            ])"
                            placeholder="Seleccionar marca..."
                        />


                        {{-- Modelo --}}
                        <x-searchable-select
                            label="Modelo"
                            wire-model="modelo"
                            :options="collect($modelos)->map(fn ($m) => [
                                'value' => $m['id_modelo'],
                                'label' => $m['nombre']
                            ])"
                            placeholder="Seleccionar modelo..."
                        />


                        {{-- Número de serie --}}
                        <div>

                            <label
                                class="
                                    block
                                    text-xs
                                    text-[#50514F]/60
                                    mb-1.5
                                "
                            >
                                Número de serie
                            </label>

                            <input
                                type="text"
                                wire:model="numeroSerie"
                                placeholder="Buscar número de serie..."
                                autocomplete="off"
                                class="
                                    w-full
                                    text-sm
                                    border border-[#50514F]/15
                                    rounded-md
                                    px-3 py-2
                                    placeholder:text-[#50514F]/40
                                    focus:ring-1
                                    focus:ring-[#247BA0]
                                    focus:border-[#247BA0]
                                "
                            >

                        </div>


                        {{-- Service Tag --}}
                        <div>

                            <label
                                class="
                                    block
                                    text-xs
                                    text-[#50514F]/60
                                    mb-1.5
                                "
                            >
                                Service Tag
                            </label>

                            <input
                                type="text"
                                wire:model="serviceTag"
                                placeholder="Buscar Service Tag..."
                                autocomplete="off"
                                class="
                                    w-full
                                    text-sm
                                    border border-[#50514F]/15
                                    rounded-md
                                    px-3 py-2
                                    placeholder:text-[#50514F]/40
                                    focus:ring-1
                                    focus:ring-[#247BA0]
                                    focus:border-[#247BA0]
                                "
                            >

                        </div>


                        {{-- Dirección IP --}}
                        <div>

                            <label
                                class="
                                    block
                                    text-xs
                                    text-[#50514F]/60
                                    mb-1.5
                                "
                            >
                                Dirección IP
                            </label>

                            <input
                                type="text"
                                wire:model="direccionIp"
                                placeholder="Buscar dirección IP..."
                                autocomplete="off"
                                class="
                                    w-full
                                    text-sm
                                    border border-[#50514F]/15
                                    rounded-md
                                    px-3 py-2
                                    placeholder:text-[#50514F]/40
                                    focus:ring-1
                                    focus:ring-[#247BA0]
                                    focus:border-[#247BA0]
                                "
                            >

                        </div>


                        {{-- Estado --}}
                        <x-searchable-select
                            label="Estado"
                            wire-model="estado"
                            :options="collect($estados)->map(fn ($e) => [
                                'value' => $e['id_valor'],
                                'label' => $e['nombre']
                            ])"
                            placeholder="Seleccionar estado..."
                        />


                        {{-- Área --}}
                        <x-searchable-select
                            label="Área / Departamento"
                            wire-model="area"
                            :options="collect($areas)->map(fn ($a) => [
                                'value' => $a['id_area'],
                                'label' => $a['nombre']
                            ])"
                            placeholder="Seleccionar área..."
                        />

                    </div>

                </div>


                {{-- =================================================
                    BOTONES
                ================================================= --}}
                <div
                    class="
                        shrink-0
                        grid grid-cols-2
                        gap-3
                        p-5
                        bg-white
                        border-t border-[#50514F]/10
                    "
                >

                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="
                            h-12
                            rounded-lg
                            border border-[#247BA0]/40
                            text-sm font-medium
                            text-[#247BA0]
                            hover:bg-[#247BA0]/5
                            transition-colors
                        "
                    >
                        Limpiar filtros
                    </button>


                    <button
                        type="submit"
                        class="
                            h-12
                            rounded-lg
                            bg-[#247BA0]
                            text-sm font-medium
                            text-white
                            hover:bg-[#1d6688]
                            transition-colors
                        "
                    >
                        Aplicar filtros
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
        FILTROS DE ESCRITORIO
    ============================================================ --}}
    <form
        wire:submit.prevent="$refresh"
        class="
            hidden md:block
            bg-white
            border border-[#50514F]/10
            rounded-lg
            p-6 mb-6
        "
    >

        {{-- Encabezado --}}
        <div class="flex items-center gap-2 mb-5">

            <svg
                class="w-4 h-4 text-[#50514F]/70"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M4 6h16"/>
                <path d="M7 6v2a2 2 0 002 2h6a2 2 0 002-2V6"/>
                <path d="M4 18h16"/>
                <path d="M9 18v-2a2 2 0 012-2h2a2 2 0 012 2v2"/>
            </svg>

            <span class="text-sm font-semibold text-[#50514F]">
                Filtros de búsqueda
            </span>


            <svg
                wire:loading
                wire:target="
                    search,
                    tipoActivo,
                    marca,
                    modelo,
                    numeroSerie,
                    serviceTag,
                    direccionIp,
                    estado,
                    area,
                    resetFilters
                "
                class="
                    w-4 h-4
                    animate-spin
                    text-[#247BA0]
                    ml-1
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
                    opacity="0.25"
                />

                <path
                    d="M21 12a9 9 0 0 0-9-9"
                    stroke="currentColor"
                    stroke-width="2"
                />
            </svg>

        </div>


        {{-- Primera fila --}}
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

            <x-searchable-select
                label="Tipo de activo"
                wire-model="tipoActivo"
                :options="collect($tiposActivo)->map(fn ($t) => [
                    'value' => $t['id_tipo_equipo'],
                    'label' => $t['nombre']
                ])"
                placeholder="Buscar tipo..."
            />


            <x-searchable-select
                label="Marca"
                wire-model="marca"
                :options="collect($marcas)->map(fn ($m) => [
                    'value' => $m['id_marca'],
                    'label' => $m['nombre']
                ])"
                placeholder="Buscar marca..."
            />


            <x-searchable-select
                label="Modelo"
                wire-model="modelo"
                :options="collect($modelos)->map(fn ($m) => [
                    'value' => $m['id_modelo'],
                    'label' => $m['nombre']
                ])"
                placeholder="Buscar modelo..."
            />


            <div>

                <label
                    class="
                        block
                        text-xs
                        text-[#50514F]/60
                        mb-1.5
                    "
                >
                    Número de serie
                </label>

                <input
                    type="text"
                    wire:model="numeroSerie"
                    placeholder="Buscar..."
                    class="
                        w-full
                        text-sm
                        border border-[#50514F]/15
                        rounded-md
                        px-3 py-2
                        placeholder:text-[#50514F]/40
                        focus:ring-1
                        focus:ring-[#247BA0]
                        focus:border-[#247BA0]
                    "
                >

            </div>

        </div>


        {{-- Segunda fila --}}
        <div
            class="
                grid
                grid-cols-1
                sm:grid-cols-2
                lg:grid-cols-4
                gap-4
            "
        >

            <div>

                <label
                    class="
                        block
                        text-xs
                        text-[#50514F]/60
                        mb-1.5
                    "
                >
                    Service Tag
                </label>

                <input
                    type="text"
                    wire:model="serviceTag"
                    placeholder="Buscar..."
                    class="
                        w-full
                        text-sm
                        border border-[#50514F]/15
                        rounded-md
                        px-3 py-2
                        placeholder:text-[#50514F]/40
                        focus:ring-1
                        focus:ring-[#247BA0]
                        focus:border-[#247BA0]
                    "
                >

            </div>


            <div>

                <label
                    class="
                        block
                        text-xs
                        text-[#50514F]/60
                        mb-1.5
                    "
                >
                    Dirección IP
                </label>

                <input
                    type="text"
                    wire:model="direccionIp"
                    placeholder="Buscar..."
                    class="
                        w-full
                        text-sm
                        border border-[#50514F]/15
                        rounded-md
                        px-3 py-2
                        placeholder:text-[#50514F]/40
                        focus:ring-1
                        focus:ring-[#247BA0]
                        focus:border-[#247BA0]
                    "
                >

            </div>


            <x-searchable-select
                label="Estado"
                wire-model="estado"
                :options="collect($estados)->map(fn ($e) => [
                    'value' => $e['id_valor'],
                    'label' => $e['nombre']
                ])"
                placeholder="Buscar estado..."
            />


            <x-searchable-select
                label="Área / Departamento"
                wire-model="area"
                :options="collect($areas)->map(fn ($a) => [
                    'value' => $a['id_area'],
                    'label' => $a['nombre']
                ])"
                placeholder="Buscar área..."
            />

        </div>


        {{-- Botones --}}
        <div class="flex items-center justify-end gap-3 mt-5">

            <button
                type="button"
                wire:click="resetFilters"
                class="
                    border border-[#50514F]/20
                    rounded-md
                    px-4 py-2
                    text-sm font-medium
                    text-[#50514F]/70
                    hover:bg-[#50514F]/5
                    transition-colors
                "
            >
                Limpiar filtros
            </button>


            <button
                type="submit"
                class="
                    flex items-center gap-2
                    bg-[#247BA0]
                    rounded-md
                    px-4 py-2
                    text-sm font-medium
                    text-white
                    hover:bg-[#1d6688]
                    transition-colors
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

    </form>


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    <div
        class="
            relative

            bg-transparent
            border-0
            rounded-none
            overflow-visible

            md:bg-white
            md:border
            md:border-[#50514F]/10
            md:rounded-lg
            md:overflow-hidden
        "
    >

        {{-- Overlay mientras Livewire actualiza --}}
        <div
            wire:loading.delay
            class="
                absolute inset-0
                bg-white/50
                z-10
                pointer-events-none
            "
        ></div>


        {{-- ========================================================
            BARRA DE RESULTADOS - ESCRITORIO
        ======================================================== --}}
        <div
            class="
                hidden md:flex
                items-center justify-between
                px-5 py-4
                border-b border-[#50514F]/10
            "
        >

            <p class="text-sm text-[#50514F]/80">

                Resultados:

                <span class="font-medium">
                    {{ number_format($equipos->total()) }}
                </span>

                Equipos encontrados

            </p>


            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">

                    <span class="text-xs text-[#50514F]/60">
                        Mostrar
                    </span>

                    <select
                        wire:model.live="perPage"
                        class="
                            text-xs
                            border border-[#50514F]/15
                            rounded-md
                            px-2 py-1.5
                            bg-white
                            focus:ring-1
                            focus:ring-[#247BA0]
                        "
                    >

                        @foreach ([10, 25, 50, 100] as $option)

                            <option value="{{ $option }}">
                                {{ $option }}
                            </option>

                        @endforeach

                    </select>

                    <span class="text-xs text-[#50514F]/60">
                        Por página
                    </span>

                </div>


                <select
                    wire:model.live="sort"
                    class="
                        text-xs
                        border border-[#50514F]/15
                        rounded-md
                        px-2 py-1.5
                        bg-white
                        uppercase
                        focus:ring-1
                        focus:ring-[#247BA0]
                    "
                >
                    <option value="asc">
                        ASC
                    </option>

                    <option value="desc">
                        DESC
                    </option>
                </select>

            </div>

        </div>


        {{-- ========================================================
            TABLA - ESCRITORIO
        ======================================================== --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b border-[#50514F]/10
                            text-left
                        "
                    >

                        <th class="px-5 py-3 w-10">

                            <input
                                type="checkbox"
                                onclick="toggleAllRows(this)"
                                class="rounded border-[#50514F]/30"
                            >

                        </th>


                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            ID Equipo
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Tipo de Equipo
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Marca
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Modelo
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Número de serie
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Service Tag
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Dirección IP
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Estado
                        </th>

                        <th class="px-3 py-3 font-medium text-[#50514F]/70">
                            Área / Departamento
                        </th>

                        <th
                            class="
                                px-3 py-3
                                font-medium
                                text-[#50514F]/70
                                text-right
                            "
                        >
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse ($equipos as $equipo)

                        <tr
                            wire:key="equipo-{{ $equipo->id_equipo }}"
                            class="
                                border-b border-[#50514F]/5
                                hover:bg-[#247BA0]/5
                                transition-colors
                            "
                        >

                            <td class="px-5 py-3">

                                <input
                                    type="checkbox"
                                    name="ids[]"
                                    value="{{ $equipo->id_equipo }}"
                                    class="
                                        row-checkbox
                                        rounded
                                        border-[#50514F]/30
                                    "
                                >

                            </td>


                            <td
                                class="
                                    px-3 py-3
                                    font-medium
                                    text-[#50514F]
                                "
                            >
                                {{ $equipo->codigo_inventario }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->tipo_equipo_nombre ?? '—' }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->marca_nombre ?? '—' }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->modelo_nombre ?? 'N/A' }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->numero_serie ?? 'NA' }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->service_tag ?? 'NA' }}
                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->direccion_ip ?? 'NA' }}
                            </td>


                            <td class="px-3 py-3">

                                <x-status-badge
                                    :status="$equipo->estado_nombre ?? 'Sin estado'"
                                />

                            </td>


                            <td class="px-3 py-3 text-[#50514F]/80">
                                {{ $equipo->ubicacion_organizacional ?? '—' }}
                            </td>


                            <td class="px-3 py-3">

                                <div
                                    class="
                                        flex items-center justify-end
                                        gap-2
                                        relative
                                    "
                                >

                                    {{-- Ver detalle --}}
                                    <a
                                        href="{{ Route::has('equipos.show')
                                            ? route(
                                                'equipos.show',
                                                $equipo->id_equipo
                                            )
                                            : '#'
                                        }}"
                                        class="
                                            text-[#247BA0]
                                            hover:text-[#1d6688]
                                        "
                                        title="Ver detalle"
                                    >

                                        <svg
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"
                                            />
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>

                                    </a>


                                    {{-- Más acciones --}}
                                    <button
                                        type="button"
                                        onclick="toggleRowMenu(
                                            event,
                                            'menu-{{ $equipo->id_equipo }}'
                                        )"
                                        class="
                                            text-[#50514F]/60
                                            hover:text-[#50514F]
                                        "
                                        title="Más acciones"
                                    >

                                        <svg
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <circle cx="12" cy="5" r="1.5"/>
                                            <circle cx="12" cy="12" r="1.5"/>
                                            <circle cx="12" cy="19" r="1.5"/>
                                        </svg>

                                    </button>


                                    {{-- Menú --}}
                                    <div
                                        id="menu-{{ $equipo->id_equipo }}"
                                        class="
                                            row-menu
                                            hidden
                                            absolute
                                            right-0 top-6
                                            w-40
                                            bg-white
                                            border border-[#50514F]/10
                                            rounded-md
                                            shadow-md
                                            z-20
                                            text-left
                                        "
                                    >

                                        <a
                                            href="{{ Route::has('equipos.edit')
                                                ? route(
                                                    'equipos.edit',
                                                    $equipo->id_equipo
                                                )
                                                : '#'
                                            }}"
                                            class="
                                                block
                                                px-4 py-2
                                                text-xs
                                                text-[#50514F]/80
                                                hover:bg-[#50514F]/5
                                            "
                                        >
                                            Editar
                                        </a>


                                        <button
                                            type="button"
                                            onclick="confirmarBaja(
                                                '{{ $equipo->id_equipo }}'
                                            )"
                                            class="
                                                w-full
                                                block
                                                text-left
                                                px-4 py-2
                                                text-xs
                                                text-red-600
                                                hover:bg-red-50
                                            "
                                        >
                                            Dar de baja
                                        </button>

                                    </div>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="11"
                                class="
                                    px-5 py-12
                                    text-center
                                    text-sm
                                    text-[#50514F]/50
                                "
                            >
                                No se encontraron equipos con los filtros seleccionados.
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

            @forelse ($equipos as $equipo)

                <article
                    wire:key="equipo-card-{{ $equipo->id_equipo }}"
                    class="
                        bg-white
                        border border-[#50514F]/10
                        rounded-xl
                        p-4
                        shadow-sm
                    "
                >

                    {{-- Cabecera --}}
                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0 flex-1">

                            {{-- Nombre del equipo --}}
                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    leading-snug
                                    text-[#25344A]
                                    break-words
                                "
                            >

                                {{ $equipo->tipo_equipo_nombre ?? 'Equipo' }}

                                @if ($equipo->marca_nombre)
                                    {{ $equipo->marca_nombre }}
                                @endif

                                @if ($equipo->modelo_nombre)
                                    {{ $equipo->modelo_nombre }}
                                @endif

                            </p>


                            {{-- Código inventario --}}
                            <p class="text-xs text-[#50514F]/50 mt-1">
                                {{ $equipo->codigo_inventario }}
                            </p>

                        </div>


                        {{-- Más acciones --}}
                        <div class="relative shrink-0">

                            <button
                                type="button"
                                onclick="toggleRowMenu(
                                    event,
                                    'menu-m-{{ $equipo->id_equipo }}'
                                )"
                                class="
                                    w-8 h-8
                                    flex items-center justify-center
                                    rounded-md
                                    text-[#25344A]/80
                                    hover:bg-[#50514F]/5
                                    transition-colors
                                "
                                aria-label="Más acciones"
                            >

                                <svg
                                    class="w-5 h-5"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>

                            </button>


                            <div
                                id="menu-m-{{ $equipo->id_equipo }}"
                                class="
                                    row-menu
                                    hidden
                                    absolute
                                    right-0 top-9
                                    w-40
                                    bg-white
                                    border border-[#50514F]/10
                                    rounded-lg
                                    shadow-lg
                                    z-20
                                    overflow-hidden
                                    text-left
                                "
                            >

                                <a
                                    href="{{ Route::has('equipos.show')
                                        ? route(
                                            'equipos.show',
                                            $equipo->id_equipo
                                        )
                                        : '#'
                                    }}"
                                    class="
                                        block
                                        px-4 py-2.5
                                        text-xs
                                        text-[#50514F]/80
                                        hover:bg-[#50514F]/5
                                    "
                                >
                                    Ver detalle
                                </a>


                                <a
                                    href="{{ Route::has('equipos.edit')
                                        ? route(
                                            'equipos.edit',
                                            $equipo->id_equipo
                                        )
                                        : '#'
                                    }}"
                                    class="
                                        block
                                        px-4 py-2.5
                                        text-xs
                                        text-[#50514F]/80
                                        hover:bg-[#50514F]/5
                                    "
                                >
                                    Editar
                                </a>


                                <button
                                    type="button"
                                    onclick="confirmarBaja(
                                        '{{ $equipo->id_equipo }}'
                                    )"
                                    class="
                                        w-full
                                        block
                                        text-left
                                        px-4 py-2.5
                                        text-xs
                                        text-red-600
                                        hover:bg-red-50
                                    "
                                >
                                    Dar de baja
                                </button>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        INFORMACIÓN
                    ================================================= --}}
                    <div class="mt-3 space-y-2">

                        {{-- Número de serie --}}
                        <p class="text-xs text-[#50514F]/70">

                            <span class="text-[#50514F]/45">
                                SN:
                            </span>

                            <span class="break-all">
                                {{ $equipo->numero_serie ?? 'NA' }}
                            </span>

                        </p>


                        {{-- Service Tag --}}
                        <p class="text-xs text-[#50514F]/70">

                            <span class="text-[#50514F]/45">
                                Service Tag:
                            </span>

                            <span class="break-all">
                                {{ $equipo->service_tag ?? 'NA' }}
                            </span>

                        </p>


                        {{-- IP --}}
                        @if ($equipo->direccion_ip)

                            <p class="text-xs text-[#50514F]/70">

                                <span class="text-[#50514F]/45">
                                    IP:
                                </span>

                                {{ $equipo->direccion_ip }}

                            </p>

                        @endif


                        {{-- Estado --}}
                        <div class="flex items-center gap-2">

                            <span class="text-xs text-[#50514F]/45">
                                Estado:
                            </span>

                            <x-status-badge
                                :status="$equipo->estado_nombre ?? 'Sin estado'"
                            />

                        </div>


                        {{-- Área --}}
                        <p class="text-xs text-[#50514F]/70">

                            <span class="text-[#50514F]/45">
                                Área:
                            </span>

                            {{ $equipo->ubicacion_organizacional ?? '—' }}

                        </p>

                    </div>

                </article>

            @empty

                {{-- Sin resultados --}}
                <div
                    class="
                        bg-white
                        border border-[#50514F]/10
                        rounded-xl
                        px-5 py-12
                        text-center
                    "
                >

                    <svg
                        class="
                            w-9 h-9
                            mx-auto mb-3
                            text-[#50514F]/35
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M3 6h12"/>
                        <path d="M3 11h9"/>
                        <path d="M3 16h6"/>
                        <circle cx="17" cy="16" r="3"/>
                        <path d="M19.5 18.5L22 21"/>
                    </svg>


                    <p class="text-sm text-[#50514F]/50">
                        No se encontraron resultados
                    </p>

                </div>

            @endforelse

        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($equipos->hasPages())

            {{-- ====================================================
                PAGINACIÓN ESCRITORIO
            ==================================================== --}}
            <div
                class="
                    hidden sm:flex
                    items-center justify-end
                    gap-1
                    px-5 py-4
                    border-t border-[#50514F]/10
                "
            >

                {{-- Anterior --}}
                <button
                    type="button"
                    wire:click="previousPage"
                    @disabled($equipos->onFirstPage())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[#50514F]/50

                        {{ $equipos->onFirstPage()
                            ? 'opacity-40'
                            : 'hover:bg-[#50514F]/5'
                        }}
                    "
                >

                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M15 6l-6 6 6 6"/>
                    </svg>

                </button>


                @php
                    $current = $equipos->currentPage();
                    $last = $equipos->lastPage();
                    $window = 2;
                @endphp


                {{-- Primeras páginas --}}
                @for ($page = 1; $page <= min(2, $last); $page++)

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }})"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $current
                                ? 'bg-[#247BA0] text-white'
                                : 'text-[#50514F]/70 hover:bg-[#50514F]/5'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($current > 4)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[#50514F]/40
                        "
                    >
                        ...
                    </span>

                @endif


                {{-- Ventana central --}}
                @for (
                    $page = max(3, $current - $window);
                    $page <= min($last - 2, $current + $window);
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }})"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $current
                                ? 'bg-[#247BA0] text-white'
                                : 'text-[#50514F]/70 hover:bg-[#50514F]/5'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                @if ($current < $last - 3)

                    <span
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            text-xs
                            text-[#50514F]/40
                        "
                    >
                        ...
                    </span>

                @endif


                {{-- Últimas páginas --}}
                @for (
                    $page = max($last - 1, 3);
                    $page <= $last;
                    $page++
                )

                    <button
                        type="button"
                        wire:click="gotoPage({{ $page }})"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            rounded-full
                            text-xs font-medium

                            {{ $page === $current
                                ? 'bg-[#247BA0] text-white'
                                : 'text-[#50514F]/70 hover:bg-[#50514F]/5'
                            }}
                        "
                    >
                        {{ $page }}
                    </button>

                @endfor


                {{-- Siguiente --}}
                <button
                    type="button"
                    wire:click="nextPage"
                    @disabled(! $equipos->hasMorePages())
                    class="
                        w-8 h-8
                        flex items-center justify-center
                        rounded-md
                        text-[#50514F]/50

                        {{ $equipos->hasMorePages()
                            ? 'hover:bg-[#50514F]/5'
                            : 'opacity-40'
                        }}
                    "
                >

                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M9 6l6 6-6 6"/>
                    </svg>

                </button>

            </div>


            {{-- ====================================================
                PAGINACIÓN MÓVIL
            ==================================================== --}}
            <div
                class="
                    flex sm:hidden
                    items-center justify-between
                    gap-3
                    mt-4
                    px-1 py-3
                "
            >

                <button
                    type="button"
                    wire:click="previousPage"
                    @disabled($equipos->onFirstPage())
                    class="
                        h-9
                        px-3
                        rounded-lg
                        border border-[#50514F]/15
                        bg-white
                        text-xs font-medium

                        {{ $equipos->onFirstPage()
                            ? 'opacity-40 cursor-not-allowed text-[#50514F]/50'
                            : 'text-[#50514F]/70'
                        }}
                    "
                >
                    Anterior
                </button>


                <span
                    class="
                        text-xs
                        text-[#50514F]/55
                        text-center
                    "
                >

                    Página

                    <span class="font-medium text-[#50514F]/80">
                        {{ $equipos->currentPage() }}
                    </span>

                    de

                    <span class="font-medium text-[#50514F]/80">
                        {{ $equipos->lastPage() }}
                    </span>

                </span>


                <button
                    type="button"
                    wire:click="nextPage"
                    @disabled(! $equipos->hasMorePages())
                    class="
                        h-9
                        px-3
                        rounded-lg
                        border border-[#50514F]/15
                        bg-white
                        text-xs font-medium

                        {{ $equipos->hasMorePages()
                            ? 'text-[#50514F]/70'
                            : 'opacity-40 cursor-not-allowed text-[#50514F]/50'
                        }}
                    "
                >
                    Siguiente
                </button>

            </div>

        @endif

    </div>

</div>