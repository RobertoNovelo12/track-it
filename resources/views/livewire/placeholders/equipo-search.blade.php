<div class="animate-pulse">

    {{-- ============================================================
        ACCIONES
    ============================================================ --}}
    <div
        class="
            flex
            justify-end

            mb-4
            sm:mb-6
        "
    >

        {{-- Botón Inventario --}}
        <div
            class="
                h-10
                w-28

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>

    </div>


    {{-- ============================================================
        CABECERA DE RESULTADOS
    ============================================================ --}}
    <div
        class="
            flex
            items-center
            justify-between

            gap-3

            mb-4
        "
    >

        <div>

            {{-- Título --}}
            <div
                class="
                    h-4
                    w-40

                    rounded

                    bg-[var(--theme-surface-soft)]
                "
            ></div>


            {{-- Cantidad de resultados --}}
            <div
                class="
                    h-3
                    w-52
                    max-w-full

                    rounded

                    bg-[var(--theme-surface-soft)]

                    mt-2
                "
            ></div>

        </div>

    </div>


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            lg:grid-cols-2

            gap-3
            sm:gap-4
        "
    >

        @for ($i = 0; $i < 6; $i++)

            <article
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    p-4
                    sm:p-5
                "
            >

                {{-- ====================================================
                    CABECERA
                ==================================================== --}}
                <div
                    class="
                        flex
                        items-start
                        justify-between

                        gap-3
                    "
                >

                    <div class="min-w-0 flex-1">

                        {{-- Nombre principal --}}
                        <div
                            class="
                                h-4
                                w-3/4

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>


                        {{-- Nombre registrado --}}
                        <div
                            class="
                                h-3
                                w-40

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mt-2
                            "
                        ></div>

                    </div>


                    {{-- Estado --}}
                    <div
                        class="
                            shrink-0

                            h-6
                            w-16

                            rounded-full

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- ====================================================
                    DATOS
                ==================================================== --}}
                <div
                    class="
                        grid
                        grid-cols-1
                        sm:grid-cols-2

                        gap-x-5
                        gap-y-4

                        mt-5
                    "
                >

                    @for ($j = 0; $j < 6; $j++)

                        <div>

                            {{-- Label --}}
                            <div
                                class="
                                    h-2.5

                                    {{ $j % 2 === 0 ? 'w-24' : 'w-16' }}

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>


                            {{-- Valor --}}
                            <div
                                class="
                                    h-3

                                    {{ $j % 3 === 0 ? 'w-28' : 'w-20' }}

                                    rounded

                                    bg-[var(--theme-surface-soft)]

                                    mt-2
                                "
                            ></div>

                        </div>

                    @endfor

                </div>


                {{-- ====================================================
                    ACCIONES
                ==================================================== --}}
                <div
                    class="
                        flex
                        flex-col

                        sm:flex-row
                        sm:items-center
                        sm:justify-end

                        gap-2

                        mt-5
                        pt-4

                        border-t
                        border-[var(--theme-border)]
                    "
                >

                    {{-- Ver detalle --}}
                    <div
                        class="
                            h-9
                            w-full
                            sm:w-28

                            rounded-lg

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>


                    {{-- Editar --}}
                    <div
                        class="
                            h-9
                            w-full
                            sm:w-24

                            rounded-lg

                            bg-[var(--theme-primary-soft)]
                        "
                    ></div>

                </div>

            </article>

        @endfor

    </div>


    {{-- ============================================================
        PAGINACIÓN
    ============================================================ --}}
    <div
        class="
            flex
            items-center
            justify-between

            md:justify-end

            gap-2

            mt-5
            sm:mt-6
        "
    >

        {{-- Anterior --}}
        <div
            class="
                h-9
                w-20

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>


        {{-- Página --}}
        <div
            class="
                h-9
                w-9

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>


        {{-- Página --}}
        <div
            class="
                h-9
                w-9

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>


        {{-- Siguiente --}}
        <div
            class="
                h-9
                w-20

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>

    </div>

</div>