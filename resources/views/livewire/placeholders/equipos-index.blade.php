{{-- Comparte todo el estado Alpine con el inventario: Livewire lo conserva durante la carga diferida. --}}
<div
    x-data="@include('livewire.equipos.estado-alpine')"
>

    {{-- ============================================================
        PLACEHOLDER MÓVIL
    ============================================================ --}}
    <div class="md:hidden">

        {{-- ========================================================
            BUSCADOR GENERAL
        ======================================================== --}}
        <div class="relative mb-3">
            <svg
                class="
                    absolute
                    left-4
                    top-1/2
                    -translate-y-1/2

                    w-5
                    h-5

                    text-[var(--theme-text-muted)]

                    pointer-events-none
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7"/>
                <path d="M20 20l-4-4"/>
            </svg>

            <input
                type="text"
                disabled
                placeholder="Buscar equipos..."
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

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
        </div>


        {{-- ========================================================
            FILTRAR / ORDENAR
        ======================================================== --}}
        <div class="flex items-center gap-3 mb-5">

            {{-- Filtros --}}
            <button
                type="button"
                disabled
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

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M4 5h16l-6 7v5l-4 2v-7z"/>
                </svg>

                <span>
                    Filtros
                </span>
            </button>


            {{-- Ordenar --}}
            <button
                type="button"
                disabled
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

                    disabled:cursor-default
                    disabled:opacity-100
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
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


    </div>


    {{-- ============================================================
        PLACEHOLDER ESCRITORIO
    ============================================================ --}}
    <div class="hidden md:block">

        {{-- ========================================================
            FILTROS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg
                px-6
                py-3
                mb-6
            "
        >

            {{-- Encabezado (mismo alto que el real) --}}
            <div class="-mx-2 flex items-center gap-2 rounded-md px-2 py-0.5">
                <svg
                    class="
                        w-4
                        h-4

                        text-[var(--theme-text-muted)]
                    "
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

                {{-- Flecha de plegar (gira si los filtros están plegados) --}}
                <span class="ml-auto inline-flex h-8 w-8 items-center justify-center text-[var(--theme-text-muted)]" aria-hidden="true">
                    <svg data-filtros-equipos-flecha class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 15l6-6 6 6"/>
                    </svg>
                </span>
            </div>

            {{-- Cuerpo: se oculta si el usuario dejó los filtros plegados --}}
            <div data-filtros-equipos-cuerpo class="pt-4 pb-3">

            {{-- Campos base (mismo orden que los filtros reales) --}}
            @php
                $unidadFiltro = app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'];

                $camposFiltroSkeleton = [
                    ['Tipo de equipo', 'Buscar tipo...', true],
                    ['Nombre de equipo', 'Buscar nombre...', false],
                    ['Marca', 'Buscar marca...', true],
                    ['Modelo', 'Buscar modelo...', true],
                    ['Número de serie', 'Buscar...', false],
                    ['Número de factura', 'Buscar...', false],
                    ['Estado', 'Buscar estado...', true],
                    [$unidadFiltro, 'Buscar...', true],
                    ['Situación', 'Buscar situación...', true],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($camposFiltroSkeleton as [$etiqueta, $marcador, $esLista])
                    <div>
                        <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                            {{ $etiqueta }}
                        </label>

                        <div class="flex h-[38px] w-full items-center justify-between rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text-muted)]">
                            <span class="truncate">{{ $marcador }}</span>

                            @if ($esLista)
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>


            {{-- ====================================================
                BOTONES
            ==================================================== --}}
            <div class="flex items-center justify-end gap-3 mt-5">

                <button
                    type="button"
                    disabled
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

                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    Limpiar filtros
                </button>


                <button
                    type="button"
                    disabled
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

                        disabled:cursor-default
                        disabled:opacity-100
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

            </div>

        </div>

    </div>


    {{-- ============================================================
        RESULTADOS (móvil y escritorio)
        Mismas columnas y anchos que livewire/equipos/results/section.
        Las filas siguen la cantidad por página (máximo 8).
    ============================================================ --}}
    @php
        $placeholderPerPage = (int) request(
            'per_page',
            app(\App\Services\PreferenciasUsuarioService::class)->registrosPorPagina()
        );
    @endphp

    <x-ui.skeleton-table
        selectable
        min-width="1375px"
        :rows="max(5, min($placeholderPerPage, 8))"
        :columns="[
            ['label' => 'ID Equipo', 'width' => '140px'],
            ['label' => 'Nombre de equipo', 'width' => '210px'],
            ['label' => 'Tipo de equipo', 'width' => '145px'],
            ['label' => 'Marca', 'width' => '110px'],
            ['label' => 'Modelo', 'width' => '165px'],
            ['label' => 'Número de serie', 'width' => '175px'],
            ['label' => 'Estado', 'width' => '125px', 'type' => 'status'],
            ['label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] . ' / Departamento', 'width' => '225px'],
        ]"
    />

</div>
