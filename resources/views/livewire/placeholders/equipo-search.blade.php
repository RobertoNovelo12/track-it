<div class="animate-pulse">

    {{-- ============================================================
        BUSCADOR
    ============================================================ --}}
    <section
        class="
            bg-white
            border border-[#50514F]/10
            rounded-xl
            p-4
            sm:p-5
            mb-4
            sm:mb-6
        "
    >

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            {{-- Campo de búsqueda --}}
            <div
                class="
                    relative
                    w-full
                    sm:max-w-xl
                "
            >

                <div
                    class="
                        h-12
                        w-full
                        rounded-lg
                        bg-[#50514F]/10
                    "
                ></div>

                {{-- Lupa --}}
                <div
                    class="
                        absolute
                        left-4
                        top-1/2
                        -translate-y-1/2

                        w-5
                        h-5

                        rounded-full
                        bg-[#50514F]/10
                    "
                ></div>

            </div>


            {{-- Botón inventario --}}
            <div
                class="
                    h-10
                    w-full
                    sm:w-28
                    rounded-lg
                    bg-[#50514F]/10
                "
            ></div>

        </div>

    </section>


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
                    h-3
                    w-52
                    max-w-full
                    rounded
                    bg-[#50514F]/10
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
                    bg-white
                    border border-[#50514F]/10
                    rounded-xl
                    p-4
                    sm:p-5
                "
            >

                {{-- Cabecera --}}
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
                                bg-[#50514F]/10
                            "
                        ></div>


                        {{-- Nombre registrado --}}
                        <div
                            class="
                                h-3
                                w-40
                                rounded
                                bg-[#50514F]/10
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
                            bg-[#50514F]/10
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
                                    bg-[#50514F]/10
                                "
                            ></div>


                            {{-- Valor --}}
                            <div
                                class="
                                    h-3
                                    {{ $j % 3 === 0 ? 'w-28' : 'w-20' }}
                                    rounded
                                    bg-[#50514F]/10
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
                        border-[#50514F]/10
                    "
                >

                    <div
                        class="
                            h-9
                            w-full
                            sm:w-28
                            rounded-lg
                            bg-[#50514F]/10
                        "
                    ></div>


                    <div
                        class="
                            h-9
                            w-full
                            sm:w-24
                            rounded-lg
                            bg-[#247BA0]/15
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

        <div
            class="
                h-9
                w-20
                rounded-lg
                bg-[#50514F]/10
            "
        ></div>


        <div
            class="
                h-9
                w-9
                rounded-lg
                bg-[#50514F]/10
            "
        ></div>


        <div
            class="
                h-9
                w-9
                rounded-lg
                bg-[#50514F]/10
            "
        ></div>


        <div
            class="
                h-9
                w-20
                rounded-lg
                bg-[#50514F]/10
            "
        ></div>

    </div>

</div>