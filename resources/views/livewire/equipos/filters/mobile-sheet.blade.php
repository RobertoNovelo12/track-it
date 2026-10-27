{{-- ============================================================
    BOTTOM SHEET DE FILTROS - MÓVIL
============================================================ --}}
<div
    x-show="filtersOpen"
    x-cloak
    class="fixed inset-0 z-[100] md:hidden"
>

    {{-- ========================================================
        OVERLAY
    ======================================================== --}}
    <div
        x-show="filtersOpen"

        x-transition.opacity.duration.200ms

        @click="
            filtersOpen = false
        "

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]

            backdrop-blur-[1px]
        "
    ></div>


    {{-- ========================================================
        PANEL
    ======================================================== --}}
    <div
        x-show="filtersOpen"

        x-transition:enter="
            transition
            ease-out
            duration-300
        "

        x-transition:enter-start="
            translate-y-full
        "

        x-transition:enter-end="
            translate-y-0
        "

        x-transition:leave="
            transition
            ease-in
            duration-200
        "

        x-transition:leave-start="
            translate-y-0
        "

        x-transition:leave-end="
            translate-y-full
        "

        class="
            absolute

            left-0
            right-0
            bottom-0

            max-h-[90vh]

            bg-[var(--theme-surface)]

            rounded-t-2xl

            shadow-2xl

            flex
            flex-col

            overflow-hidden
        "
    >

        {{-- ====================================================
            BARRA SUPERIOR
        ==================================================== --}}
        <div
            class="
                shrink-0

                flex
                justify-center

                pt-3
            "
        >
            <div
                class="
                    w-12
                    h-1

                    rounded-full

                    bg-[var(--theme-border-strong)]
                "
            ></div>
        </div>


        {{-- ====================================================
            CABECERA
        ==================================================== --}}
        <div
            class="
                shrink-0

                flex
                items-center
                justify-between

                px-5
                py-4
            "
        >

            <h2
                class="
                    text-xl
                    font-semibold

                    text-[var(--theme-text-strong)]
                "
            >
                Filtros de búsqueda
            </h2>


            <button
                type="button"

                @click="
                    filtersOpen = false
                "

                class="
                    w-9
                    h-9

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-[var(--theme-text-strong)]

                    hover:bg-[var(--theme-surface-soft)]

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


        {{-- ====================================================
            FORMULARIO
        ==================================================== --}}
        <form
            data-filtros {{-- Filtros: no cambian con «Columnas de los formularios» --}}
            wire:submit.prevent="$refresh"

            @submit="
                filtersOpen = false
            "

            class="
                flex
                flex-col

                min-h-0
                flex-1
            "
        >

            {{-- =================================================
                CAMPOS
            ================================================= --}}
            <div
                class="
                    flex-1

                    overflow-y-auto
                    overscroll-contain

                    px-5
                    pb-6
                "
            >

                <div class="space-y-5">

                    @php
                        $claseInputMovil = 'h-10 w-full px-3 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none';

                        $tipoNombre = data_get(
                            collect($tiposActivo)->first(fn ($t) => (string) data_get($t, 'id_tipo_equipo') === (string) $tipoActivo),
                            'nombre'
                        );
                    @endphp

                    {{-- TIPO DE EQUIPO (al elegirlo aparecen sus filtros específicos) --}}
                    <x-searchable-select
                        label="Tipo de equipo"
                        wire-model="tipoActivo"
                        :options="
                            collect($tiposActivo)->map(
                                fn ($t) => [
                                    'value' => data_get($t, 'id_tipo_equipo'),
                                    'label' => data_get($t, 'nombre'),
                                ]
                            )
                        "
                        placeholder="Seleccionar tipo..."
                    />

                    {{-- NOMBRE --}}
                    <div>
                        <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                            Nombre de equipo
                        </label>

                        <input
                            type="text"
                            wire:model="nombreEquipo"
                            placeholder="Buscar nombre..."
                            autocomplete="off"
                            class="{{ $claseInputMovil }}"
                        >
                    </div>

                    {{-- ESTADO --}}
                    <x-searchable-select
                        label="Estado"
                        wire-model="estado"
                        :options="
                            collect($estados)->map(
                                fn ($e) => [
                                    'value' => $e['id_valor'],
                                    'label' => $e['nombre'],
                                ]
                            )
                        "
                        placeholder="Seleccionar estado..."
                    />

                    {{-- MARCA --}}
                    <x-searchable-select
                        label="Marca"
                        wire-model="marca"
                        :options="
                            collect($marcas)->map(
                                fn ($m) => [
                                    'value' => $m['id_marca'],
                                    'label' => $m['nombre'],
                                ]
                            )
                        "
                        placeholder="Seleccionar marca..."
                    />

                    {{-- MODELO --}}
                    <x-searchable-select
                        label="Modelo"
                        wire-model="modelo"
                        :options="
                            collect($modelos)->map(
                                fn ($m) => [
                                    'value' => $m['id_modelo'],
                                    'label' => $m['nombre'],
                                ]
                            )
                        "
                        placeholder="Seleccionar modelo..."
                    />

                    {{-- NÚMERO DE SERIE --}}
                    <div>
                        <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                            Número de serie
                        </label>

                        <input
                            type="text"
                            wire:model="numeroSerie"
                            placeholder="Buscar número de serie..."
                            autocomplete="off"
                            class="{{ $claseInputMovil }}"
                        >
                    </div>

                    {{-- NÚMERO DE FACTURA --}}
                    <div>
                        <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                            Número de factura
                        </label>

                        <input
                            type="text"
                            wire:model="numeroFactura"
                            placeholder="Buscar número de factura..."
                            autocomplete="off"
                            class="{{ $claseInputMovil }}"
                        >
                    </div>

                    {{-- HOTEL (unidad organizativa) --}}
                    <x-searchable-select
                        :label="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']"
                        wire-model="area"
                        :options="
                            collect($areas)->map(
                                fn ($a) => [
                                    'value' => $a['id_unidad'],
                                    'label' => $a['nombre'],
                                ]
                            )
                        "
                        :placeholder="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['buscar_unidad']"
                    />

                    {{-- SITUACIÓN: también se llena desde la Vista general --}}
                    <x-searchable-select
                        label="Situación"
                        wire-model="situacion"
                        :options="$situaciones"
                        placeholder="Seleccionar situación..."
                    />

                    {{-- PROGRAMA INSTALADO --}}
                    @if (! empty($programasFiltro ?? []))
                        <x-searchable-select
                            label="Programa instalado"
                            wire-model="programa"
                            :options="collect($programasFiltro)->map(fn ($p) => ['value' => $p['value'], 'label' => $p['label']])->all()"
                            placeholder="Seleccionar programa..."
                        />
                    @endif

                    {{-- FILTROS ESPECÍFICOS DEL TIPO SELECCIONADO --}}
                    @include('livewire.equipos.filters.campos-tipo', ['variante' => 'movil'])

                </div>

            </div>


            {{-- =================================================
                BOTONES
            ================================================= --}}
            <div
                class="
                    shrink-0

                    grid
                    grid-cols-2

                    gap-3

                    p-5

                    bg-[var(--theme-surface)]

                    border-t
                    border-[var(--theme-border)]
                "
            >

                {{-- LIMPIAR --}}
                <button
                    type="button"

                    wire:click="resetFilters"

                    class="
                        h-12

                        rounded-lg

                        border
                        border-[var(--theme-primary-border)]

                        text-sm
                        font-medium
                        text-[var(--theme-primary)]

                        hover:bg-[var(--theme-primary-soft-subtle)]

                        transition-colors
                    "
                >
                    Limpiar filtros
                </button>


                {{-- APLICAR --}}
                <button
                    type="submit"

                    class="
                        h-12

                        rounded-lg

                        bg-[var(--theme-primary)]

                        text-sm
                        font-medium
                        text-white

                        hover:bg-[var(--theme-primary-hover)]

                        transition-colors
                    "
                >
                    Aplicar filtros
                </button>

            </div>

        </form>

    </div>

</div>