<div class="space-y-5">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section>
        <div
            class="
                flex items-center flex-wrap gap-2
                mb-2
                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            <a
                href="{{ route('dashboard') }}"
                wire:navigate.hover
                class="
                    hover:text-[var(--theme-primary)]
                    transition-colors
                "
            >
                Vista General
            </a>

            <svg
                class="w-3 h-3"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>

            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
                class="
                    hover:text-[var(--theme-primary)]
                    transition-colors
                "
            >
                Asignación y Movimientos
            </a>

            <svg
                class="w-3 h-3"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>

            <span>
                Historial
            </span>
        </div>

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
                <h1
                    class="
                        text-xl
                        font-semibold
                        leading-tight
                        text-[var(--theme-text-strong)]
                    "
                >
                    Historial de Asignaciones y Movimientos
                </h1>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Consulta las asignaciones, reasignaciones y movimientos registrados de los equipos.
                </p>
            </div>

            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
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

                    text-sm
                    font-medium
                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:border-[var(--theme-primary-border)]

                    transition-colors
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M15 6l-6 6 6 6"/>
                </svg>

                Volver
            </a>
        </div>
    </section>


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <section
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
            <div>
                <label
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
                        type="search"
                        disabled
                        placeholder="Equipo, colaborador, host, sede..."
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

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >
                </div>
            </div>


            {{-- TIPO --}}
            <div>
                <label
                    class="
                        block
                        mb-1.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Tipo
                </label>

                <select
                    disabled
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

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
                    <option>
                        Todos los movimientos
                    </option>
                </select>
            </div>


            {{-- DESDE --}}
            <div>
                <label
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
                    type="date"
                    disabled
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

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
            </div>


            {{-- HASTA --}}
            <div>
                <label
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
                    type="date"
                    disabled
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

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                >
            </div>

        </div>


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
                disabled
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

                    disabled:opacity-70
                    disabled:cursor-default
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
                type="button"
                disabled
                class="
                    h-9
                    px-4

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    bg-[var(--theme-primary)]

                    text-xs
                    font-medium
                    text-white

                    disabled:opacity-70
                    disabled:cursor-default
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>

                Aplicar filtros
            </button>
        </div>
    </section>


    {{-- ============================================================
        RESULTADOS: misma forma que la tabla común
    ============================================================ --}}
    <x-ui.skeleton-table
        :columns="[
            ['label' => 'Fecha', 'width' => '150px'],
            ['label' => 'Equipo', 'width' => '250px'],
            ['label' => 'Movimiento', 'width' => '140px', 'type' => 'status'],
            ['label' => 'Colaborador', 'width' => '220px'],
            ['label' => 'Tipo / motivo', 'width' => '200px'],
            ['label' => 'Ubicación', 'width' => '260px'],
            ['label' => 'Realizado por', 'width' => '160px'],
        ]"
        :rows="8"
    />

</div>