<div class="animate-pulse">

    {{-- ============================================================
        NAVEGACIÓN
    ============================================================ --}}

    {{-- Móvil --}}
    <div
        class="
            sm:hidden
            mb-5
        "
    >
        <div
            class="
                h-11
                w-full

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]
            "
        >
            <div
                class="
                    h-full

                    px-3

                    flex
                    items-center
                    gap-3
                "
            >

                <div
                    class="
                        w-7
                        h-7

                        rounded-lg

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-20

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>
        </div>
    </div>


    {{-- Escritorio --}}
    <div
        class="
            hidden
            sm:flex

            gap-2
            mb-5
        "
    >
        @for ($i = 0; $i < 4; $i++)

            <div
                class="
                    h-9
                    w-28

                    rounded-md

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]
                "
            ></div>

        @endfor
    </div>



    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[280px_minmax(0,1fr)]

            gap-5
        "
    >

        {{-- ========================================================
            PERFIL
        ======================================================== --}}
        <aside
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-5
            "
        >

            <div
                class="
                    flex
                    flex-col
                    items-center
                "
            >

                {{-- Avatar --}}
                <div
                    class="
                        w-20
                        h-20

                        rounded-full

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                {{-- Nombre --}}
                <div
                    class="
                        mt-4

                        h-4
                        w-40

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                {{-- Usuario --}}
                <div
                    class="
                        mt-2

                        h-3
                        w-32

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                {{-- Estado --}}
                <div
                    class="
                        mt-3

                        h-6
                        w-14

                        rounded-full

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>


            <div
                class="
                    mt-5
                    pt-5

                    border-t
                    border-[var(--theme-border)]

                    space-y-5
                "
            >

                @for ($i = 0; $i < 3; $i++)

                    <div>

                        <div
                            class="
                                h-2
                                w-20

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div
                            class="
                                mt-2

                                h-3
                                w-32
                                max-w-full

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </aside>



        {{-- ========================================================
            COLUMNA DERECHA
        ======================================================== --}}
        <div class="space-y-5">

            {{-- ====================================================
                INFORMACIÓN PERSONAL
            ==================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >

                {{-- Cabecera --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-4

                        border-b
                        border-[var(--theme-border)]

                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >

                    <div class="min-w-0">

                        <div
                            class="
                                h-4
                                w-36

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div
                            class="
                                mt-2

                                h-3
                                w-56
                                max-w-full

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>


                    <div
                        class="
                            shrink-0

                            w-16
                            h-8

                            rounded-md

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Filas --}}
                <div
                    class="
                        divide-y
                        divide-[var(--theme-border)]
                    "
                >

                    @for ($i = 0; $i < 4; $i++)

                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-2
                                sm:gap-4
                            "
                        >

                            <div
                                class="
                                    h-3
                                    w-28

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>


                            <div
                                class="
                                    h-3
                                    w-44
                                    max-w-full

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </section>



            {{-- ====================================================
                INFORMACIÓN ORGANIZACIONAL
            ==================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >

                <div
                    class="
                        px-4
                        sm:px-5
                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >

                    <div
                        class="
                            h-4
                            w-44

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            mt-2

                            h-3
                            w-64
                            max-w-full

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                <div
                    class="
                        divide-y
                        divide-[var(--theme-border)]
                    "
                >

                    @for ($i = 0; $i < 6; $i++)

                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-2
                                sm:gap-4
                            "
                        >

                            <div
                                class="
                                    h-3
                                    w-24

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>


                            <div
                                class="
                                    h-3
                                    w-36
                                    max-w-full

                                    rounded

                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </section>

        </div>

    </div>

</div>