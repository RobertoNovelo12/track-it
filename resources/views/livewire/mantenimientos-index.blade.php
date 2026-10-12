<div>

    <section

        class="

            rounded-xl

            border

            border-[var(--theme-border)]

            bg-[var(--theme-surface)]

            p-5

        "

    >

        {{-- Encabezado --}}

        <div class="mb-5">

            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">

                Filtros de búsqueda

            </h2>

        </div>



        {{-- Filtros --}}

        <div

            class="

                grid

                grid-cols-1

                md:grid-cols-2

                xl:grid-cols-4

                gap-4

            "

        >

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

                <label

                    class="

                        mb-1.5

                        block

                        text-sm

                        font-medium

                        text-[var(--theme-text)]

                    "

                >

                    Equipo

                </label>



                <input

                    type="text"

                    wire:model="equipo"

                    placeholder="Buscar..."

                    class="

                        w-full

                        rounded-lg

                        border

                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-3

                        py-2

                        text-sm

                        text-[var(--theme-text)]

                        outline-none

                        placeholder:text-[var(--theme-text-muted)]

                    "

                >

            </div>



            {{-- Fecha desde --}}

            <div>

                <label

                    class="

                        mb-1.5

                        block

                        text-sm

                        font-medium

                        text-[var(--theme-text)]

                    "

                >

                    Fecha desde

                </label>



                <input

    type="date"

    wire:model="fechaDesde"

    class="

        w-full

        rounded-lg

        border

        border-[var(--theme-border)]

        bg-[var(--theme-surface)]

        px-3

        py-2

        text-sm

        text-[var(--theme-text)]

        outline-none

    "

>

            </div>



            {{-- Fecha hasta --}}

            <div>

                <label

                    class="

                        mb-1.5

                        block

                        text-sm

                        font-medium

                        text-[var(--theme-text)]

                    "

                >

                    Fecha hasta

                </label>



                <input

    type="date"

    wire:model="fechaHasta"

    class="

        w-full

        rounded-lg

        border

        border-[var(--theme-border)]

        bg-[var(--theme-surface)]

        px-3

        py-2

        text-sm

        text-[var(--theme-text)]

        outline-none

    "

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

                <label

                    class="

                        mb-1.5

                        block

                        text-sm

                        font-medium

                        text-[var(--theme-text)]

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

                        rounded-lg

                        border

                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-3

                        py-2

                        text-sm

                        text-[var(--theme-text)]

                        outline-none

                        placeholder:text-[var(--theme-text-muted)]

                    "

                >

            </div>

        </div>



        {{-- Acciones --}}

        <div class="mt-5 flex justify-end gap-3">

            <button

    type="button"

    wire:click="limpiarFiltros"

    wire:loading.attr="disabled"

    wire:target="limpiarFiltros"

    class="

        rounded-lg

        border

        border-[var(--theme-border)]

        px-4

        py-2

        text-sm

        font-medium

        text-[var(--theme-text)]

        disabled:opacity-50

    "

>

    Limpiar filtros

</button>







            <button

                type="button"

                class="

                    rounded-lg

                    bg-[var(--theme-primary)]

                    px-4

                    py-2

                    text-sm

                    font-medium

                    text-white

                "

            >

                Aplicar filtros

            </button>

        </div>

        {{-- ============================================================
    RESULTADOS
============================================================ --}}

<div

    class="

        relative

        mt-5

        overflow-hidden

        rounded-xl

        border

        border-[var(--theme-border)]

        bg-[var(--theme-surface)]

    "

>

    {{-- Barra superior --}}

    <div

        class="

            flex

            flex-col

            gap-3

            border-b

            border-[var(--theme-border)]

            px-5

            py-4

            md:flex-row

            md:items-center

            md:justify-between

        "

    >

        <div>

            <p class="text-sm font-medium text-[var(--theme-text-strong)]">

                Resultados

            </p>



            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">

                {{ $mantenimientos->total() }}

                {{ $mantenimientos->total() === 1

                    ? 'mantenimiento encontrado'

                    : 'mantenimientos encontrados'

                }}

            </p>

        </div>



        <div class="flex items-center gap-3">

            {{-- Registros por página --}}

            <div class="flex items-center gap-2">

                <span class="text-xs text-[var(--theme-text-muted)]">

                    Mostrar

                </span>



                <select

                    wire:model.live="perPage"

                    class="

                        rounded-md

                        border

                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-2

                        py-1.5

                        text-xs

                        text-[var(--theme-text)]

                        outline-none

                    "

                >

                    <option value="10">10</option>

                    <option value="25">25</option>

                    <option value="50">50</option>

                    <option value="100">100</option>

                </select>



                <span class="text-xs text-[var(--theme-text-muted)]">

                    por página

                </span>

            </div>



            {{-- Orden --}}

            <select

                wire:model.live="sort"

                class="

                    rounded-md

                    border

                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    px-2

                    py-1.5

                    text-xs

                    text-[var(--theme-text)]

                    outline-none

                "

            >

                <option value="desc">DESC</option>

                <option value="asc">ASC</option>

            </select>

        </div>

    </div>



    {{-- Estado de carga --}}

    <div

        wire:loading.delay

        wire:target="

            aplicarFiltros,

            limpiarFiltros,

            perPage,

            sort,

            previousPage,

            nextPage,

            gotoPage

        "

        class="

            absolute

            inset-0

            z-20

            bg-[var(--theme-surface)]

            opacity-50

        "

    ></div>



    {{-- Tabla --}}

    <div class="overflow-x-auto">

        <table class="w-full min-w-[1400px] text-sm">



            <thead>

                <tr

                    class="

                        border-b

                        border-[var(--theme-border)]

                        text-left

                    "

                >

                    <th class="w-10 px-4 py-3">

                        <input

                            type="checkbox"

                            class="

                                rounded

                                border-[var(--theme-border)]

                            "

                        >

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        ID Mantenimiento

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Tipo

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Marca

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Modelo

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Número de serie

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Intervención

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Equipo

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Fecha

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Técnico responsable

                    </th>



                    <th class="px-3 py-3 font-medium text-[var(--theme-text-muted)]">

                        Estado

                    </th>



                    <th

                        class="

                            px-4

                            py-3

                            text-right

                            font-medium

                            text-[var(--theme-text-muted)]

                        "

                    >

                        Acciones

                    </th>

                </tr>

            </thead>



            <tbody>

                @forelse ($mantenimientos as $mantenimiento)



                    <tr

                        wire:key="mantenimiento-{{ $mantenimiento->id_mantenimiento }}"

                        class="

                            border-b

                            border-[var(--theme-border)]

                            transition

                            last:border-b-0

                        "

                    >

                        {{-- Checkbox --}}

                        <td class="px-4 py-3">

                            <input

                                type="checkbox"

                                class="

                                    rounded

                                    border-[var(--theme-border)]

                                "

                            >

                        </td>



                        {{-- ID / Folio --}}

                        <td

                            class="

                                whitespace-nowrap

                                px-3

                                py-3

                                font-medium

                                text-[var(--theme-primary)]

                            "

                        >

                            {{ $mantenimiento->folio }}

                        </td>



                        {{-- Tipo --}}

                        <td class="whitespace-nowrap px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->tipo_mantenimiento ?? '—' }}

                        </td>



                        {{-- Marca --}}

                        <td class="px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->marca ?? '—' }}

                        </td>



                        {{-- Modelo --}}

                        <td class="px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->modelo ?? '—' }}

                        </td>



                        {{-- Número de serie --}}

                        <td class="whitespace-nowrap px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->numero_serie ?? '—' }}

                        </td>



                        {{-- Intervención --}}

                        <td

                            class="

                                max-w-[220px]

                                truncate

                                px-3

                                py-3

                                text-[var(--theme-text)]

                            "

                            title="{{ $mantenimiento->intervencion_realizada ?: $mantenimiento->tipo_intervencion }}"

                        >

                            {{

                                $mantenimiento->intervencion_realizada

                                    ?: ($mantenimiento->tipo_intervencion ?? '—')

                            }}

                        </td>



                        {{-- Equipo --}}

                        <td class="px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->nombre_equipo

                                ?: ($mantenimiento->tipo_equipo ?? '—')

                            }}

                        </td>



                        {{-- Fecha --}}

                        <td class="whitespace-nowrap px-3 py-3 text-[var(--theme-text)]">

                            {{

                                $mantenimiento->fecha_intervencion

                                    ? \Illuminate\Support\Carbon::parse(

                                        $mantenimiento->fecha_intervencion

                                    )->format('d/m/Y')

                                    : '—'

                            }}

                        </td>



                        {{-- Técnico --}}

                        <td class="px-3 py-3 text-[var(--theme-text)]">

                            {{ $mantenimiento->tecnico_responsable ?: '—' }}

                        </td>



                        {{-- Estado --}}

                        <td class="px-3 py-3">

                            <span

                                class="

                                    inline-flex

                                    whitespace-nowrap

                                    rounded-md

                                    border

                                    border-[var(--theme-border)]

                                    px-2

                                    py-1

                                    text-xs

                                    font-medium

                                    text-[var(--theme-text)]

                                "

                            >

                                {{ $mantenimiento->estado_mantenimiento ?? '—' }}

                            </span>

                        </td>



                        {{-- Acciones --}}

                        <td class="px-4 py-3">

                            <div

                                class="

                                    flex

                                    items-center

                                    justify-end

                                    gap-3

                                    whitespace-nowrap

                                "

                            >

                                <a

                                    href="{{ route(

                                        'mantenimientos.show',

                                        $mantenimiento->id_mantenimiento

                                    ) }}"

                                    wire:navigate

                                    class="

                                        text-sm

                                        font-medium

                                        text-[var(--theme-primary)]

                                    "

                                >

                                    Ver

                                </a>



                                <a

                                    href="{{ route(

                                        'mantenimientos.edit',

                                        $mantenimiento->id_mantenimiento

                                    ) }}"

                                    wire:navigate

                                    class="

                                        text-sm

                                        font-medium

                                        text-[var(--theme-text-muted)]

                                    "

                                >

                                    Editar

                                </a>

                            </div>

                        </td>

                    </tr>



                @empty



                    <tr>

                        <td

                            colspan="12"

                            class="

                                px-5

                                py-12

                                text-center

                                text-sm

                                text-[var(--theme-text-muted)]

                            "

                        >

                            No se encontraron mantenimientos.

                        </td>

                    </tr>



                @endforelse

            </tbody>

        </table>

    </div>



    {{-- Paginación --}}

    @if ($mantenimientos->hasPages())

        <div

            class="

                flex

                items-center

                justify-between

                border-t

                border-[var(--theme-border)]

                px-5

                py-4

            "

        >

            <button

                type="button"

                wire:click="previousPage"

                @disabled($mantenimientos->onFirstPage())

                class="

                    rounded-md

                    border

                    border-[var(--theme-border)]

                    px-3

                    py-1.5

                    text-xs

                    text-[var(--theme-text)]

                    disabled:cursor-not-allowed

                    disabled:opacity-40

                "

            >

                Anterior

            </button>



            <p class="text-xs text-[var(--theme-text-muted)]">

                Página



                <span class="font-medium text-[var(--theme-text)]">

                    {{ $mantenimientos->currentPage() }}

                </span>



                de



                <span class="font-medium text-[var(--theme-text)]">

                    {{ $mantenimientos->lastPage() }}

                </span>

            </p>



            <button

                type="button"

                wire:click="nextPage"

                @disabled(! $mantenimientos->hasMorePages())

                class="

                    rounded-md

                    border

                    border-[var(--theme-border)]

                    px-3

                    py-1.5

                    text-xs

                    text-[var(--theme-text)]

                    disabled:cursor-not-allowed

                    disabled:opacity-40

                "

            >

                Siguiente

            </button>

        </div>

    @endif

</div>

    </section>

</div>
