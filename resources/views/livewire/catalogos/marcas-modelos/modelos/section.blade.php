{{-- ============================================================
    SECCIÓN - MODELOS
============================================================ --}}
<div class="space-y-3">

    {{-- ========================================================
        TÍTULO
    ======================================================== --}}
    <div>
        <h2
            class="
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Modelos
        </h2>

        <p
            class="
                mt-1

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            Gestión de modelos registrados
        </p>
    </div>


    {{-- ========================================================
        RESULTADOS
    ======================================================== --}}
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

        {{-- ====================================================
            BARRA SUPERIOR
        ==================================================== --}}
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
                            filteredModelos.length
                        )
                    "
                ></span>

                <span
                    x-text="
                        filteredModelos.length === 1
                            ? 'modelo encontrado'
                            : 'modelos encontrados'
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
                            x-model="perPageModelos"
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
                        x-model="sortModelos"
                        x-options="sortOptions"

                        :show-clear="false"

                        compact
                    />
                </div>

            </div>
        </div>


        {{-- ====================================================
            TABLA ESCRITORIO
        ==================================================== --}}
        @include(
            'livewire.catalogos.marcas-modelos.modelos.table'
        )


        {{-- ====================================================
            TARJETAS MÓVIL
        ==================================================== --}}
        @include(
            'livewire.catalogos.marcas-modelos.modelos.mobile'
        )


        {{-- ====================================================
            PAGINACIÓN
        ==================================================== --}}
        @include(
            'livewire.catalogos.marcas-modelos.modelos.pagination'
        )

    </div>

</div>