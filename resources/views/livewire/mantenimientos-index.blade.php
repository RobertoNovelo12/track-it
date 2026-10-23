{{--
    Filtros en su propia tarjeta y la tabla debajo, fuera de ella
    (antes la tabla quedaba dentro de la sección de filtros).
--}}
<div class="space-y-5">
    <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">

        {{-- ============================================================
            FILTROS DE BÚSQUEDA
        ============================================================ --}}
        <div class="mb-5">
            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                Filtros de búsqueda
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            {{-- Tipo de mantenimiento --}}
            <x-searchable-select
                label="Tipo de mantenimiento"
                wire-model="tipo"
                :options="collect($tiposMantenimiento)->map(fn ($item) => [
                    'value' => $item->id_valor,
                    'label' => $item->nombre,
                ])->values()->all()"
                placeholder="Buscar tipo..."
                :show-all-on-open="true"
            />

            {{-- Estado --}}
            <x-searchable-select
                label="Estado"
                wire-model="estado"
                :options="collect($estadosMantenimiento)->map(fn ($item) => [
                    'value' => $item->id_valor,
                    'label' => $item->nombre,
                ])->values()->all()"
                placeholder="Buscar estado..."
                :show-all-on-open="true"
            />

            {{-- Técnico responsable --}}
            <x-searchable-select
                label="Técnico responsable"
                wire-model="tecnico"
                :options="collect($tecnicos)->map(fn ($item) => [
                    'value' => $item->id_usuario,
                    'label' => trim(
                        $item->nombres . ' ' .
                        $item->apellido_paterno . ' ' .
                        ($item->apellido_materno ?? '')
                    ),
                ])->values()->all()"
                placeholder="Buscar técnico..."
                :show-all-on-open="true"
            />

            {{-- Equipo --}}
            <div>
                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                    Equipo
                </label>
                <input
                    type="text"
                    wire:model="equipo"
                    placeholder="Buscar..."
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                >
            </div>

            {{-- Fecha desde --}}
            <div>
                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                    Fecha desde
                </label>
                <input
                    type="date"
                    wire:model="fechaDesde"
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                >
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                    Fecha hasta
                </label>
                <input
                    type="date"
                    wire:model="fechaHasta"
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                >
            </div>

            {{-- Departamento --}}
            <x-searchable-select
                label="Departamento"
                wire-model="areaDepartamento"
                :options="collect($departamentos)->map(fn ($item) => [
                    'value' => 'departamento:' . $item->id_departamento,
                    'label' => $item->nombre,
                ])->values()->all()"
                placeholder="Buscar departamento..."
                :show-all-on-open="true"
            />

            {{-- Número de serie --}}
            <div>
                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                    Número de serie
                </label>
                <input
                    type="text"
                    wire:model="numeroSerie"
                    placeholder="Buscar..."
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                >
            </div>
        </div>

        {{-- Acciones de filtros --}}
        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                wire:click="limpiarFiltros"
                wire:loading.attr="disabled"
                wire:target="limpiarFiltros"
                class="rounded-lg border border-[var(--theme-border)] px-4 py-2 text-sm font-medium text-[var(--theme-text)] disabled:opacity-50"
            >
                Limpiar filtros
            </button>

            <button
                type="button"
                wire:click="aplicarFiltros"
                wire:loading.attr="disabled"
                wire:target="aplicarFiltros"
                class="rounded-lg bg-[var(--theme-primary)] px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                Aplicar filtros
            </button>
        </div>
    </section>

        {{-- ============================================================
            RESULTADOS
            Componente reutilizable <x-data-table>.
        ============================================================ --}}
        {{-- «Actualizando…» solo con acciones que cambian la tabla: elegir un filtro no la cambia hasta «Aplicar filtros». --}}
        <x-data-table

            :rows="$mantenimientos"
            row-key="id_mantenimiento"
            key-prefix="mantenimiento"
            selectable

            :sort-field="$sortField"
            :sort-direction="$sort"
            sort-action="sortBy"

            per-page-model="perPage"
            sort-model="sort"
            refresh-action="$refresh"
            loading-target="aplicarFiltros, limpiarFiltros, sortBy, perPage, sort, gotoPage, nextPage, previousPage, setPage, $refresh"

            result-singular="mantenimiento encontrado"
            result-plural="mantenimientos encontrados"
            empty-message="No se encontraron mantenimientos."
            min-width="1300px"

            :columns="[
                [
                    'key' => 'folio',
                    'label' => 'ID Mantenimiento',
                    'width' => '150px',
                    'sort' => 'folio',
                    'strong' => true,
                    'mobile' => false,
                ],
                [
                    'key' => 'tipo_mantenimiento',
                    'label' => 'Tipo',
                    'width' => '120px',
                    'sort' => 'tipo',
                ],
                [
                    'key' => 'marca',
                    'label' => 'Marca',
                    'width' => '110px',
                    'sort' => 'marca',
                ],
                [
                    'key' => 'modelo',
                    'label' => 'Modelo',
                    'width' => '150px',
                    'sort' => 'modelo',
                ],
                [
                    'key' => 'numero_serie',
                    'label' => 'Número de serie',
                    'width' => '150px',
                    'sort' => 'numero_serie',
                    'mobileLabel' => 'SN',
                ],
                [
                    'key' => 'intervencion',
                    'label' => 'Intervención',
                    'width' => '220px',
                    'sort' => 'intervencion',
                    'value' => fn ($m) => $m->intervencion_realizada ?: $m->tipo_intervencion,
                ],
                [
                    'key' => 'equipo',
                    'label' => 'Equipo',
                    'width' => '180px',
                    'sort' => 'equipo',
                    'value' => fn ($m) => $m->nombre_equipo ?: $m->tipo_equipo,
                    'mobile' => false,
                ],
                [
                    'key' => 'fecha_intervencion',
                    'label' => 'Fecha',
                    'width' => '110px',
                    'sort' => 'fecha',
                    'value' => fn ($m) => $m->fecha_intervencion
                        ? \Illuminate\Support\Carbon::parse($m->fecha_intervencion)->format('d/m/Y')
                        : null,
                ],
                [
                    'key' => 'tecnico_responsable',
                    'label' => 'Técnico responsable',
                    'width' => '190px',
                    'sort' => 'tecnico',
                    'mobileLabel' => 'Técnico',
                ],
                [
                    'key' => 'estado_mantenimiento',
                    'label' => 'Estado',
                    'width' => '130px',
                    'sort' => 'estado',
                    'type' => 'status',
                ],
            ]"

            :mobile-title="fn ($m) => $m->nombre_equipo ?: ($m->tipo_equipo ?? 'Mantenimiento')"
            :mobile-subtitle="fn ($m) => $m->folio"

            :actions="fn ($m) => [
                'edit' => route('mantenimientos.edit', $m->id_mantenimiento),
                'detail' => route('equipos.show', $m->id_equipo),
                'menu' => [
                    [
                        'label' => 'Editar',
                        'href' => route('mantenimientos.edit', $m->id_mantenimiento),
                    ],
                    [
                        'label' => 'Ver equipo',
                        'href' => route('equipos.show', $m->id_equipo),
                    ],
                ],
            ]"
        />
</div>