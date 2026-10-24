<div class="space-y-5">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section>

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

                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Consulta las asignaciones, reasignaciones y movimientos registrados de los equipos.
                </p>
            </div>

            {{-- «Volver» lo agrega el layout (layouts/partials/boton-volver). --}}
        </div>
    </section>


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <form
        data-filtros {{-- Filtros: no cambian con «Columnas de los formularios» --}}
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
                        placeholder="Equipo, colaborador, host, {{ mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']) }}..."
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

                <x-searchable-select
                    wire-model="tipo"
                    :empty-as-null="false"
                    placeholder="Selecciona..."
                    :show-clear="false"
                    :options="[
                        ...collect($tiposFiltro)->map(fn ($opcion) => ['value' => $opcion['value'], 'label' => $opcion['label']])->all(),
                    ]"
                />
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
        RESULTADOS: tabla común (components/data-table)
        Misma barra de resultados, indicador de actualización,
        tarjetas en móvil, estado vacío y paginación que el resto.
    ============================================================ --}}
    @php
        $colaboradorHistorial = function ($registro): string {
            $nuevo = trim((string) ($registro->nombre_colaborador ?? ''));
            $anterior = trim((string) ($registro->colaborador_anterior ?? ''));

            if ($anterior !== '' && $nuevo !== '' && $anterior !== $nuevo) {
                return $anterior . ' → ' . $nuevo;
            }

            return $nuevo !== '' ? $nuevo : $anterior;
        };
    @endphp

    <x-data-table
        :rows="$historial"
        key-prefix="historial"
        page-name="historyPage"
        per-page-model="perPage"
        refresh-action="$refresh"
        min-width="1350px"
        result-singular="movimiento encontrado"
        result-plural="movimientos encontrados"
        empty-message="No hay movimientos con los filtros seleccionados."
        :row-key="fn ($registro) => $registro->tipo_registro . '-' . $registro->id"
        :mobile-title="fn ($registro) => $registro->codigo_inventario ?: 'Sin código'"
        :mobile-subtitle="fn ($registro) => \App\Support\Fecha::conHora($registro->fecha)"
        :columns="[
            [
                'key' => 'fecha',
                'label' => 'Fecha',
                'width' => '150px',
                'value' => fn ($registro) => \App\Support\Fecha::conHora($registro->fecha),
                'mobile' => false,
            ],
            [
                'key' => 'equipo',
                'label' => 'Equipo',
                'width' => '250px',
                'strong' => true,
                'value' => fn ($registro) => collect([
                    $registro->codigo_inventario,
                    $registro->nombre_equipo,
                    $registro->host,
                ])->filter()->implode(' · '),
                'mobile' => false,
            ],
            [
                'key' => 'movimiento',
                'label' => 'Movimiento',
                'width' => '140px',
                'type' => 'status',
            ],
            [
                'key' => 'colaborador',
                'label' => 'Colaborador',
                'width' => '220px',
                'value' => $colaboradorHistorial,
            ],
            [
                'key' => 'detalle',
                'label' => 'Tipo / motivo',
                'width' => '200px',
                'value' => fn ($registro) => collect([$registro->tipo_asignacion, $registro->motivo])->filter()->implode(' · '),
                'mobile' => 'filled',
            ],
            [
                'key' => 'ubicacion_texto',
                'label' => 'Ubicación',
                'width' => '260px',
                'value' => fn ($registro) => collect([$registro->area, $registro->departamento, $registro->ubicacion])->filter()->implode(' · '),
                'default' => 'Sin ubicación',
            ],
            [
                'key' => 'usuario',
                'label' => 'Realizado por',
                'width' => '160px',
            ],
        ]"
        :actions="fn ($registro) => [
            'detail' => $registro->id_equipo ? route('equipos.show', $registro->id_equipo) : null,
        ]"
    />

</div>
