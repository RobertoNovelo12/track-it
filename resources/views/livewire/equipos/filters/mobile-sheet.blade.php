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

            rounded-t-[24px]

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

                    {{-- =========================================
                        TIPO DE ACTIVO
                    ========================================= --}}
                    <x-searchable-select
                        label="Tipo de activo"

                        wire-model="tipoActivo"

                        :options="
                            collect($tiposActivo)->map(
                                fn ($t) => [
                                    'value' => $t['id_tipo_equipo'],
                                    'label' => $t['nombre'],
                                ]
                            )
                        "

                        placeholder="Seleccionar tipo..."
                    />


                    {{-- =========================================
                        MARCA
                    ========================================= --}}
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


                    {{-- =========================================
                        MODELO
                    ========================================= --}}
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


                    {{-- =========================================
                        NÚMERO DE SERIE
                    ========================================= --}}
                    <div>

                        <label
                            class="
                                block

                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
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

                                px-3
                                py-2

                                text-sm

                                border
                                border-[var(--theme-border-strong)]

                                rounded-md

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]
                            "
                        >

                    </div>


                    {{-- =========================================
                        SERVICE TAG
                    ========================================= --}}
                    <div>

                        <label
                            class="
                                block

                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
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

                                px-3
                                py-2

                                text-sm

                                border
                                border-[var(--theme-border-strong)]

                                rounded-md

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]
                            "
                        >

                    </div>


                    {{-- =========================================
                        DIRECCIÓN IP
                    ========================================= --}}
                    <div>

                        <label
                            class="
                                block

                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
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

                                px-3
                                py-2

                                text-sm

                                border
                                border-[var(--theme-border-strong)]

                                rounded-md

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]
                            "
                        >

                    </div>


                    {{-- =========================================
                        ESTADO
                    ========================================= --}}
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


                    {{-- =========================================
                        ÁREA / DEPARTAMENTO
                    ========================================= --}}
                    <x-searchable-select
                        label="Área / Departamento"

                        wire-model="area"

                        :options="
                            collect($areas)->map(
                                fn ($a) => [
                                    'value' => $a['id_area'],
                                    'label' => $a['nombre'],
                                ]
                            )
                        "

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