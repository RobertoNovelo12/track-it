{{-- ============================================================
    RESULTADOS - MARCAS
============================================================ --}}
<div
    class="
        relative

        bg-transparent
        border-0
        rounded-none
        overflow-visible

        md:bg-[var(--theme-surface)]
        md:border
        md:border-[var(--theme-border)]
        md:rounded-lg
        md:overflow-hidden
    "
>

    {{-- ========================================================
        BARRA SUPERIOR
    ======================================================== --}}
    <div
        class="
            hidden
            md:flex

            items-center
            justify-between

            px-5
            py-4

            border-b
            border-[var(--theme-border)]
        "
    >
        <p
            class="
                text-sm
                text-[var(--theme-text)]
            "
        >
            Resultados:

            <span
                class="font-medium"

                x-text="
                    formatNumber(
                        filteredMarcas.length
                    )
                "
            ></span>

            <span
                x-text="
                    filteredMarcas.length === 1
                        ? 'marca encontrada'
                        : 'marcas encontradas'
                "
            ></span>
        </p>


        <div
            class="
                flex
                items-center

                gap-4
            "
        >

            {{-- CANTIDAD POR PÁGINA --}}
            <div
                class="
                    flex
                    items-center

                    gap-2
                "
            >
                <span
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Mostrar
                </span>


                <div
                    class="
                        w-20
                        shrink-0
                    "
                >
                    <x-searchable-select
                        x-model="perPageMarcas"
                        x-options="perPageOptions"

                        :show-clear="false"

                        compact
                    />
                </div>


                <span
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Por página
                </span>
            </div>


            {{-- ORDEN --}}
            <div
                class="
                    w-24
                    shrink-0
                "
            >
                <x-searchable-select
                    x-model="sortMarcas"
                    x-options="sortOptions"

                    :show-clear="false"

                    compact
                />
            </div>

        </div>
    </div>


    {{-- ========================================================
        TABLA ESCRITORIO
    ======================================================== --}}
    @include(
        'livewire.catalogos.marcas-modelos.marcas.table'
    )


    {{-- ========================================================
        TARJETAS MÓVIL
    ======================================================== --}}
    @include(
        'livewire.catalogos.marcas-modelos.marcas.mobile'
    )


    {{-- ========================================================
        PAGINACIÓN
    ======================================================== --}}
    @include(
        'livewire.catalogos.marcas-modelos.marcas.pagination'
    )

</div>