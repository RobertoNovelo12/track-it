{{-- ============================================================
    FILTROS ACTIVOS - MÓVIL
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

    if ($nombreEquipo) {
        $activeFilters[] = [
            'property' => 'nombreEquipo',
            'label' => 'Nombre: ' . $nombreEquipo,
        ];
    }

    if ($numeroFactura) {
        $activeFilters[] = [
            'property' => 'numeroFactura',
            'label' => 'Factura: ' . $numeroFactura,
        ];
    }

    /*
     * Filtros específicos del tipo (se quitan uno por uno).
     */
    foreach ($camposTipoFiltros ?? [] as $campoTipo) {
        $valorCampo = trim((string) data_get($camposTipo, $campoTipo['id'], ''));

        if ($valorCampo === '') {
            continue;
        }

        $textoValor = collect($campoTipo['opciones'])->firstWhere('value', $valorCampo)['label'] ?? $valorCampo;

        $activeFilters[] = [
            'property' => 'camposTipo.' . $campoTipo['id'],
            'label' => $campoTipo['etiqueta'] . ': ' . $textoValor,
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
                (string) $item['id_unidad'] === (string) $area
        );

        $activeFilters[] = [
            'property' => 'area',
            'label' => $item['nombre'] ?? app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'],
        ];
    }

    if ($situacion) {
        $item = collect($situaciones)->firstWhere('value', $situacion);

        $activeFilters[] = [
            'property' => 'situacion',
            'label' => $item['label'] ?? 'Situación',
        ];
    }

    if (filled($programa ?? null)) {
        $item = collect($programasFiltro ?? [])->firstWhere('value', (string) $programa);

        $activeFilters[] = [
            'property' => 'programa',
            'label' => 'Con ' . ($item['label'] ?? 'programa'),
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

                text-[var(--theme-text-muted)]

                pointer-events-none
            "
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        ><use href="{{ \App\Support\Icono::url('i-8bdcd83') }}"></use></svg>


        <input
            type="text"

            wire:model.live.debounce.400ms="search"

            placeholder="Buscar equipos..."
            autocomplete="off"

            class="
                w-full
                h-12

                pl-12
                pr-10

                rounded-lg

                border
                border-[var(--theme-border-strong)]

                bg-[var(--theme-surface)]

                text-sm
                text-[var(--theme-text)]

                placeholder:text-[var(--theme-text-muted)]

                focus:border-[var(--theme-primary)]
                focus:ring-1
                focus:ring-[var(--theme-primary)]
            "
        >


        @if ($search)

            <button
                type="button"

                wire:click="$set('search', null)"

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

                    text-[var(--theme-text-muted)]

                    hover:text-[var(--theme-text-strong)]
                    hover:bg-[var(--theme-surface-soft)]

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
                ><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
            </button>

        @endif

    </div>


    {{-- ========================================================
        FILTRAR / ORDENAR
    ======================================================== --}}
    <div class="flex items-center gap-3">

        {{-- FILTROS --}}
        <button
            type="button"

            @click="
                filtersOpen = true
            "

            class="
                h-11

                flex
                items-center
                gap-2

                px-4

                rounded-lg

                border

                text-sm
                font-medium

                transition-colors

                {{ count($activeFilters) > 0
                    ? 'border-[var(--theme-primary)] text-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]'
                    : 'border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]'
                }}
            "
        >

            <svg
                class="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-0e09bcb') }}"></use></svg>


            <span>
                Filtros
            </span>


            @if (count($activeFilters) > 0)

                <span>
                    · {{ count($activeFilters) }}
                </span>

            @endif

        </button>


        {{-- ORDENAR --}}
        <button
            type="button"

            wire:click="
                $set(
                    'sort',
                    '{{ $sort === 'asc' ? 'desc' : 'asc' }}'
                )
            "

            class="
                h-11

                flex
                items-center
                gap-2

                px-4

                rounded-lg

                border
                border-[var(--theme-border-strong)]

                bg-[var(--theme-surface)]

                text-sm
                font-medium
                text-[var(--theme-text)]

                hover:bg-[var(--theme-surface-soft)]

                transition-colors
            "
        >

            <svg
                class="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-de0bcf2') }}"></use></svg>


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
                flex
                items-center
                gap-2

                overflow-x-auto

                mt-4
                pb-1
            "
        >

            @foreach ($activeFilters as $filter)

                <button
                    type="button"

                    wire:click="
                        $set(
                            '{{ $filter['property'] }}',
                            null
                        )
                    "

                    class="
                        shrink-0

                        flex
                        items-center
                        gap-1.5

                        px-3
                        py-1.5

                        rounded-full

                        bg-[var(--theme-primary-soft)]

                        text-[var(--theme-primary)]

                        text-xs
                        font-medium
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
                    ><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>

                </button>

            @endforeach


            <button
                type="button"

                wire:click="resetFilters"

                class="
                    shrink-0

                    px-2
                    py-1.5

                    text-xs
                    font-medium
                    text-[var(--theme-primary)]
                "
            >
                Limpiar
            </button>

        </div>

    @endif

</div>