{{-- ============================================================
    FILTROS DE ESCRITORIO
============================================================ --}}
<form
    wire:submit.prevent="$refresh"

    class="
        hidden
        md:block

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border)]

        rounded-lg

        p-6
        mb-6
    "
>

    {{-- ========================================================
        ENCABEZADO
    ======================================================== --}}
    <div class="flex items-center gap-2 mb-5">

        <svg
            class="w-4 h-4 text-[var(--theme-text-muted)]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            aria-hidden="true"
        >
            <path d="M4 6h16"/>
            <path d="M7 6v2a2 2 0 002 2h6a2 2 0 002-2V6"/>
            <path d="M4 18h16"/>
            <path d="M9 18v-2a2 2 0 012-2h2a2 2 0 012 2v2"/>
        </svg>


        <span
            class="
                text-sm
                font-semibold
                text-[var(--theme-text)]
            "
        >
            Filtros de búsqueda
        </span>


        {{-- ====================================================
            SPINNER
        ==================================================== --}}
        <svg
            wire:loading

            wire:target="
                search,
                nombreEquipo,
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
                w-4
                h-4

                animate-spin

                text-[var(--theme-primary)]

                ml-1
            "

            viewBox="0 0 24 24"
            fill="none"

            aria-hidden="true"
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

    </div>


    {{-- ========================================================
        PRIMERA FILA
    ======================================================== --}}
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

        {{-- ====================================================
            NOMBRE DE EQUIPO
        ==================================================== --}}
        <div>

            <label
                class="
                    block

                    mb-1.5

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Nombre de equipo
            </label>


            <input
                type="text"

                wire:model="nombreEquipo"

                placeholder="Buscar nombre..."

                autocomplete="off"

                class="
                    w-full

                    px-3
                    py-2

                    text-sm
                    text-[var(--theme-text)]

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    placeholder:text-[var(--theme-text-muted)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                    focus:border-[var(--theme-primary)]

                    outline-none

                    transition-colors
                "
            >

        </div>


        {{-- ====================================================
            TIPO DE ACTIVO
        ==================================================== --}}
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

            placeholder="Buscar tipo..."
        />


        {{-- ====================================================
            MARCA
        ==================================================== --}}
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

            placeholder="Buscar marca..."
        />


        {{-- ====================================================
            MODELO
        ==================================================== --}}
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

            placeholder="Buscar modelo..."
        />

    </div>


    {{-- ========================================================
        SEGUNDA FILA
    ======================================================== --}}
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

        {{-- ====================================================
            NÚMERO DE SERIE
        ==================================================== --}}
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

                placeholder="Buscar..."

                autocomplete="off"

                class="
                    w-full

                    px-3
                    py-2

                    text-sm
                    text-[var(--theme-text)]

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    placeholder:text-[var(--theme-text-muted)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                    focus:border-[var(--theme-primary)]

                    outline-none

                    transition-colors
                "
            >

        </div>


        {{-- ====================================================
            DIRECCIÓN IP
        ==================================================== --}}
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

                placeholder="Buscar..."

                autocomplete="off"

                class="
                    w-full

                    px-3
                    py-2

                    text-sm
                    text-[var(--theme-text)]

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    placeholder:text-[var(--theme-text-muted)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                    focus:border-[var(--theme-primary)]

                    outline-none

                    transition-colors
                "
            >

        </div>


        {{-- ====================================================
            ESTADO
        ==================================================== --}}
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

            placeholder="Buscar estado..."
        />


        {{-- ====================================================
            ÁREA / DEPARTAMENTO
        ==================================================== --}}
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

            placeholder="Buscar área..."
        />

    </div>


    {{-- ========================================================
        TERCERA FILA

        Service Tag queda disponible como filtro,
        pero ya no ocupa una columna de la tabla.
    ======================================================== --}}
    <div
        class="
            grid
            grid-cols-1

            sm:grid-cols-2
            lg:grid-cols-4

            gap-4

            items-end
        "
    >

        {{-- ====================================================
            SERVICE TAG
        ==================================================== --}}
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

                placeholder="Buscar..."

                autocomplete="off"

                class="
                    w-full

                    px-3
                    py-2

                    text-sm
                    text-[var(--theme-text)]

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    placeholder:text-[var(--theme-text-muted)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                    focus:border-[var(--theme-primary)]

                    outline-none

                    transition-colors
                "
            >

        </div>


        {{-- ====================================================
            BOTONES

            En escritorio ocupan las tres columnas restantes,
            así Service Tag no deja la tarjeta desequilibrada.
        ==================================================== --}}
        <div
            class="
                flex
                items-center
                justify-end

                gap-3

                sm:col-span-1
                lg:col-span-3
            "
        >

            {{-- ================================================
                LIMPIAR
            ================================================= --}}
            <button
                type="button"

                wire:click="resetFilters"

                wire:loading.attr="disabled"
                wire:target="resetFilters"

                class="
                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    px-4
                    py-2

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    bg-[var(--theme-surface)]

                    hover:bg-[var(--theme-surface-soft)]

                    disabled:opacity-60
                    disabled:cursor-not-allowed

                    transition-colors
                "
            >
                Limpiar filtros
            </button>


            {{-- ================================================
                APLICAR
            ================================================= --}}
            <button
                type="submit"

                class="
                    flex
                    items-center

                    gap-2

                    bg-[var(--theme-primary)]

                    rounded-md

                    px-4
                    py-2

                    text-sm
                    font-medium
                    text-white

                    hover:bg-[var(--theme-primary-hover)]

                    transition-colors
                "
            >

                <svg
                    class="w-4 h-4"

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"

                    aria-hidden="true"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    />

                    <path d="M20 20l-4-4"/>
                </svg>


                Aplicar filtros

            </button>

        </div>

    </div>

</form>