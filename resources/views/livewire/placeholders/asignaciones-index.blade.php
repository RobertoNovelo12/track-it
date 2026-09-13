<div class="animate-pulse">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section
        class="
            flex
            flex-col
            xl:flex-row
            xl:items-end
            xl:justify-between
            gap-5
            mb-6
        "
    >

        <div class="min-w-0 flex-1">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 mb-2">

                <div
                    class="
                        h-3
                        w-20
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-3
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-36
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

            </div>


            {{-- Título --}}
            <div
                class="
                    h-6
                    w-64
                    max-w-full
                    rounded
                    bg-[#50514F]/10
                "
            ></div>


            {{-- Descripción --}}
            <div
                class="
                    mt-2
                    h-3
                    w-[520px]
                    max-w-full
                    rounded
                    bg-[#50514F]/10
                "
            ></div>

        </div>


        {{-- Acciones --}}
        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
                xl:justify-end
            "
        >

            <div
                class="
                    h-9
                    w-24
                    rounded-md
                    bg-[#247BA0]/15
                "
            ></div>

            <div
                class="
                    h-9
                    w-28
                    rounded-md
                    bg-[#50514F]/10
                "
            ></div>

            <div
                class="
                    h-9
                    w-24
                    rounded-md
                    bg-[#50514F]/10
                "
            ></div>

        </div>

    </section>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <section
        class="
            grid
            grid-cols-1
            md:grid-cols-3
            gap-3
            sm:gap-4
            mb-5
        "
    >

        @for ($i = 0; $i < 3; $i++)

            <div
                class="
                    bg-white
                    border
                    border-[#50514F]/10
                    rounded-xl

                    px-4
                    sm:px-5
                    py-4

                    flex
                    items-center
                    gap-4
                "
            >

                {{-- Icono --}}
                <div
                    class="
                        shrink-0
                        w-14
                        h-14
                        rounded-xl
                        bg-[#50514F]/10
                    "
                ></div>


                {{-- Texto --}}
                <div class="flex-1 min-w-0">

                    <div
                        class="
                            h-3
                            w-32
                            rounded
                            bg-[#50514F]/10
                        "
                    ></div>

                    <div
                        class="
                            mt-2
                            h-6
                            w-10
                            rounded
                            bg-[#50514F]/10
                        "
                    ></div>

                    <div
                        class="
                            mt-2
                            h-2.5
                            w-24
                            rounded
                            bg-[#50514F]/10
                        "
                    ></div>

                </div>


                {{-- Flecha --}}
                <div
                    class="
                        shrink-0
                        w-4
                        h-4
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

            </div>

        @endfor

    </section>


    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[1.05fr_1fr]
            gap-4
        "
    >

        {{-- ========================================================
            GRÁFICA
        ======================================================== --}}
        <section
            class="
                bg-white
                border
                border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-5
            "
        >

            {{-- Header --}}
            <div
                class="
                    flex
                    items-start
                    justify-between
                    gap-4
                "
            >

                <div class="flex items-start gap-3">

                    <div
                        class="
                            w-5
                            h-5
                            rounded
                            bg-[#50514F]/10
                        "
                    ></div>


                    <div>

                        <div
                            class="
                                h-4
                                w-44
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                        <div
                            class="
                                mt-2
                                h-3
                                w-64
                                max-w-full
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                    </div>

                </div>


                <div
                    class="
                        hidden
                        sm:block
                        h-9
                        w-32
                        rounded-lg
                        bg-[#50514F]/10
                    "
                ></div>

            </div>


            {{-- Gráfica --}}
            <div
                class="
                    min-h-[330px]

                    flex
                    flex-col
                    sm:flex-row

                    items-center
                    justify-center

                    gap-8

                    mt-5
                "
            >

                {{-- Donut --}}
                <div
                    class="
                        relative
                        shrink-0

                        w-56
                        h-56

                        rounded-full

                        border-[42px]
                        border-[#247BA0]/10
                    "
                >

                    <div
                        class="
                            absolute
                            inset-0

                            flex
                            flex-col
                            items-center
                            justify-center
                        "
                    >

                        <div
                            class="
                                h-7
                                w-12
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                        <div
                            class="
                                mt-2
                                h-3
                                w-20
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                    </div>

                </div>


                {{-- Leyenda --}}
                <div
                    class="
                        w-full
                        max-w-xs
                        space-y-3
                    "
                >

                    @for ($i = 0; $i < 5; $i++)

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >

                            <div
                                class="
                                    shrink-0
                                    w-2.5
                                    h-2.5
                                    rounded-full
                                    bg-[#50514F]/10
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    flex-1
                                    rounded
                                    bg-[#50514F]/10
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-6
                                    rounded
                                    bg-[#50514F]/10
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </div>

        </section>


        {{-- ========================================================
            RESUMEN DE ACTIVIDAD
        ======================================================== --}}
        <section
            class="
                bg-white
                border
                border-[#50514F]/10
                rounded-xl
                overflow-hidden
            "
        >

            <div class="p-4 sm:p-5">

                {{-- Cabecera --}}
                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >

                    <div class="flex items-start gap-3">

                        <div
                            class="
                                w-5
                                h-5
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                        <div>

                            <div
                                class="
                                    h-4
                                    w-40
                                    rounded
                                    bg-[#50514F]/10
                                "
                            ></div>

                            <div
                                class="
                                    mt-2
                                    h-3
                                    w-56
                                    max-w-full
                                    rounded
                                    bg-[#50514F]/10
                                "
                            ></div>

                        </div>

                    </div>

                </div>


                {{-- Mini métricas --}}
                <div
                    class="
                        grid
                        grid-cols-2
                        sm:grid-cols-4
                        gap-2
                        mt-5
                    "
                >

                    @for ($i = 0; $i < 4; $i++)

                        <div
                            class="
                                border
                                border-[#50514F]/10
                                rounded-xl
                                p-3
                            "
                        >

                            <div class="flex items-center gap-2">

                                <div
                                    class="
                                        w-7
                                        h-7
                                        rounded-lg
                                        bg-[#50514F]/10
                                    "
                                ></div>

                                <div
                                    class="
                                        h-5
                                        w-7
                                        rounded
                                        bg-[#50514F]/10
                                    "
                                ></div>

                            </div>

                            <div
                                class="
                                    mt-2
                                    h-2.5
                                    w-20
                                    rounded
                                    bg-[#50514F]/10
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </div>


            {{-- Actividad --}}
            <div
                class="
                    px-4
                    sm:px-5
                    py-3

                    border-y
                    border-[#50514F]/10

                    flex
                    justify-between
                    items-center
                "
            >

                <div
                    class="
                        h-4
                        w-32
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-24
                        rounded
                        bg-[#50514F]/10
                    "
                ></div>

            </div>


            {{-- Filas --}}
            @for ($i = 0; $i < 5; $i++)

                <div
                    class="
                        grid
                        grid-cols-[36px_1fr_60px]

                        gap-3
                        items-center

                        px-4
                        sm:px-5
                        py-3

                        border-b
                        border-[#50514F]/10

                        last:border-b-0
                    "
                >

                    <div
                        class="
                            w-8
                            h-7
                            rounded-lg
                            bg-[#50514F]/10
                        "
                    ></div>


                    <div>

                        <div
                            class="
                                h-3
                                w-3/4
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                        <div
                            class="
                                mt-2
                                h-2
                                w-24
                                rounded
                                bg-[#50514F]/10
                            "
                        ></div>

                    </div>


                    <div
                        class="
                            h-2.5
                            w-full
                            rounded
                            bg-[#50514F]/10
                        "
                    ></div>

                </div>

            @endfor

        </section>

    </div>

</div>