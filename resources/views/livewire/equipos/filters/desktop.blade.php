{{-- ============================================================
    FILTROS DE ESCRITORIO
============================================================ --}}
@php
    /*
     * Filtros activos (se muestran junto al título cuando el panel
     * está plegado, para recordar que la tabla está filtrada).
     */
    $filtrosActivosEscritorio = collect([
        $tipoActivo, $nombreEquipo, $marca, $modelo, $numeroSerie,
        $numeroFactura, $estado, $area, $situacion, $serviceTag, $direccionIp,
    ])->filter(fn ($valor) => filled($valor))->count()
        + collect($camposTipo ?? [])->filter(fn ($valor) => filled($valor) && ! is_array($valor))->count();
@endphp

<form
    wire:submit.prevent="$refresh"

    {{-- Plegable: el estado se recuerda en el navegador --}}
    x-data="{
        abierto: true,

        init() {
            // La preferencia guardada manda (no el estado del DOM restaurado).
            this.abierto = ! (window.trackitFiltrosPlegados?.() ?? false);
            document.documentElement.classList.toggle('filtros-equipos-plegados', ! this.abierto);
        },

        alternar() {
            this.abierto = ! this.abierto;

            document.documentElement.classList.toggle('filtros-equipos-plegados', ! this.abierto);

            try {
                localStorage.setItem('trackit_filtros_equipos', this.abierto ? 'abierto' : 'plegado');
            } catch (error) {
                //
            }
        },
    }"

    class="
        {{ ($filtrosTodosLosTamanos ?? false) ? 'block' : 'hidden md:block' }}

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border)]

        rounded-lg

        px-6
        py-3
        mb-6
    "
>

    {{-- ========================================================
        ENCABEZADO
    ======================================================== --}}
    {{-- Todo el encabezado pliega / despliega (no solo la flecha) --}}
    <div
        class="-mx-2 flex cursor-pointer select-none items-center gap-2 rounded-md px-2 py-0.5"
        x-on:click="alternar()"
    >

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
                numeroFactura,
                camposTipo,
                serviceTag,
                direccionIp,
                estado,
                area,
                situacion,
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

        {{-- Filtros activos (solo con el panel plegado) --}}
        @if ($filtrosActivosEscritorio > 0)
            <span
                x-show="! abierto"
                x-transition.opacity
                x-cloak
                class="rounded-full bg-[var(--theme-primary-soft)] px-2 py-0.5 text-[11px] font-medium text-[var(--theme-primary)]"
            >
                {{ $filtrosActivosEscritorio }} {{ $filtrosActivosEscritorio === 1 ? 'activo' : 'activos' }}
            </span>
        @endif

        {{-- Plegar / desplegar --}}
        <button
            type="button"
            x-on:click.stop="alternar()"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            x-bind:title="abierto ? 'Ocultar filtros' : 'Mostrar filtros'"
            x-bind:aria-label="abierto ? 'Ocultar filtros' : 'Mostrar filtros'"
            aria-controls="filtros-equipos-contenido"
            class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors duration-[var(--motion-fast)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--theme-primary)]"
        >
            <svg
                class="h-4 w-4 transition-transform duration-[var(--motion-base)]"
                x-bind:class="abierto ? '' : '-rotate-180'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M6 15l6-6 6 6"/>
            </svg>
        </button>

    </div>

    {{--
        Contenido plegable. x-collapse anima la altura, así la tabla de
        abajo sube y baja junto con el panel. wire:ignore.self evita que
        Livewire restablezca la altura/visibilidad que maneja Alpine.
    --}}
    <div
        id="filtros-equipos-contenido"
        x-show="abierto"
        x-collapse.duration.250ms
        wire:ignore.self
    >
    <div class="pt-4 pb-3">


    @php
        $claseInputFiltro = 'w-full px-3 py-2 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors';

        $tipoNombre = data_get(
            collect($tiposActivo)->first(fn ($t) => (string) data_get($t, 'id_tipo_equipo') === (string) $tipoActivo),
            'nombre'
        );
    @endphp

    {{-- ========================================================
        PRIMERA FILA: tipo, nombre, marca y modelo
    ======================================================== --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

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
            placeholder="Buscar tipo..."
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
                class="{{ $claseInputFiltro }}"
            >
        </div>

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
            placeholder="Buscar marca..."
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
            placeholder="Buscar modelo..."
        />
    </div>


    {{-- ========================================================
        SEGUNDA FILA: serie, factura, estado y hotel
    ======================================================== --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- NÚMERO DE SERIE --}}
        <div>
            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                Número de serie
            </label>

            <input
                type="text"
                wire:model="numeroSerie"
                placeholder="Buscar..."
                autocomplete="off"
                class="{{ $claseInputFiltro }}"
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
                placeholder="Buscar..."
                autocomplete="off"
                class="{{ $claseInputFiltro }}"
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
            placeholder="Buscar estado..."
        />

        {{-- HOTEL (unidad organizativa) --}}
        <x-searchable-select
            :label="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']"
            wire-model="area"
            :options="
                collect($areas)->map(
                    fn ($a) => [
                        'value' => $a['id_area'],
                        'label' => $a['nombre'],
                    ]
                )
            "
            :placeholder="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['buscar_unidad']"
        />
    </div>


    {{-- ========================================================
        FILTROS ESPECÍFICOS DEL TIPO SELECCIONADO
    ======================================================== --}}
    @include('livewire.equipos.filters.campos-tipo', ['variante' => 'escritorio'])


    {{-- ========================================================
        ÚLTIMA FILA: situación y botones
    ======================================================== --}}
    <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- SITUACIÓN: también se llena desde los indicadores de la Vista general --}}
        <x-searchable-select
            label="Situación"
            wire-model="situacion"
            :options="$situaciones"
            placeholder="Buscar situación..."
        />


        {{-- ====================================================
            BOTONES

            En escritorio ocupan las tres columnas que deja
            Situación, alineados a la derecha.
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

    </div>
    </div>

</form>
