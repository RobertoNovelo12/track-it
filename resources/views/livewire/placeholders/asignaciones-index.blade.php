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

        <div class="min-w-0">

            {{-- Breadcrumb --}}
            <div
                class="
                    flex
                    items-center
                    gap-2
                    mb-3
                "
            >
                <div
                    class="
                        h-2.5
                        w-20
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div
                    class="
                        h-2.5
                        w-2
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div
                    class="
                        h-2.5
                        w-32
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Título --}}
            <div
                class="
                    h-6
                    w-56
                    max-w-full
                    rounded
                    bg-[var(--theme-surface-soft)]
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
            "
        >

            <div
                class="
                    h-9
                    w-24
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-9
                    w-28
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-9
                    w-24
                    rounded-md
                    bg-[var(--theme-surface-soft)]
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
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-xl
                    px-4
                    sm:px-5
                    py-4
                    flex
                    items-center
                    gap-4
                "
            >

                <div
                    class="
                        shrink-0
                        w-14
                        h-14
                        rounded-xl
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div class="flex-1 min-w-0">

                    <div
                        class="
                            h-3
                            w-36
                            max-w-full
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            mt-2
                            h-6
                            w-12
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            mt-2
                            h-2.5
                            w-24
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                <div
                    class="
                        shrink-0
                        w-5
                        h-5
                        rounded
                        bg-[var(--theme-surface-soft)]
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
            EQUIPOS ASIGNADOS POR ÁREA
        ======================================================== --}}
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

            {{-- Cabecera --}}
            <div
                class="
                    flex
                    flex-col
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
                    gap-3
                "
            >

                <div
                    class="
                        flex
                        items-start
                        gap-3
                        min-w-0
                    "
                >

                    <div
                        class="
                            shrink-0
                            w-5
                            h-5
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>


                    <div class="min-w-0 flex-1">

                        <div
                            class="
                                h-3.5
                                w-40
                                max-w-full
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div
                            class="
                                mt-2
                                h-2.5
                                w-64
                                max-w-full
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                </div>


                <div
                    class="
                        h-9
                        w-full
                        sm:w-32
                        rounded-lg
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>


            {{-- Donut y leyenda --}}
            <div
                class="
                    mt-7
                    flex
                    flex-col
                    lg:flex-row
                    items-center
                    justify-center
                    gap-8
                "
            >

                {{-- Donut --}}
                <div
                    class="
                        relative
                        shrink-0
                        w-48
                        h-48
                        sm:w-56
                        sm:h-56
                        rounded-full
                        bg-[var(--theme-surface-soft)]
                    "
                >

                    <div
                        class="
                            absolute
                            inset-[28%]
                            rounded-full
                            bg-[var(--theme-surface)]
                        "
                    ></div>

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
                                gap-2.5
                            "
                        >

                            <div
                                class="
                                    shrink-0
                                    w-2.5
                                    h-2.5
                                    rounded-full
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>


                            <div
                                class="
                                    h-2.5
                                    flex-1
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>


                            <div
                                class="
                                    h-2.5
                                    w-6
                                    rounded
                                    bg-[var(--theme-surface-soft)]
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
                    pt-4
                    sm:pt-5
                "
            >

                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            gap-3
                            min-w-0
                        "
                    >

                        <div
                            class="
                                shrink-0
                                w-5
                                h-5
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>


                        <div class="min-w-0">

                            <div
                                class="
                                    h-3.5
                                    w-36
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    mt-2
                                    h-2.5
                                    w-56
                                    max-w-full
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>

                    </div>


                    <div
                        class="
                            hidden
                            sm:block
                            h-3
                            w-28
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Mini métricas --}}
                <div
                    class="
                        grid
                        grid-cols-2
                        sm:grid-cols-4
                        gap-2
                        mt-5
                        mb-5
                    "
                >

                    @for ($i = 0; $i < 4; $i++)

                        <div
                            class="
                                rounded-xl
                                border
                                border-[var(--theme-border)]
                                bg-[var(--theme-surface-soft)]
                                p-3
                            "
                        >

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
                                        w-7
                                        h-7
                                        rounded-lg
                                        bg-[var(--theme-border)]
                                    "
                                ></div>


                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-border)]
                                    "
                                ></div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    h-2.5
                                    w-20
                                    max-w-full
                                    rounded
                                    bg-[var(--theme-border)]
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </div>


            {{-- Actividad reciente --}}
            <div>

                <div
                    class="
                        px-4
                        sm:px-5
                        py-3
                        border-y
                        border-[var(--theme-border)]
                        flex
                        items-center
                        justify-between
                        gap-3
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >

                        <div
                            class="
                                w-4
                                h-4
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <div
                            class="
                                h-3
                                w-28
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>


                    <div
                        class="
                            h-2.5
                            w-20
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- En móvil son filas compactas, no tabla --}}
                @for ($i = 0; $i < 5; $i++)

                    <div
                        class="
                            grid
                            grid-cols-[36px_1fr_auto]
                            sm:grid-cols-[80px_1fr_90px]
                            items-center
                            gap-3
                            px-4
                            sm:px-5
                            py-3
                            border-b
                            border-[var(--theme-border)]
                            last:border-b-0
                        "
                    >

                        <div
                            class="
                                w-8
                                h-7
                                rounded-lg
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>


                        <div class="min-w-0">

                            <div
                                class="
                                    h-2.5
                                    w-full
                                    max-w-44
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    mt-2
                                    h-2
                                    w-20
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>


                        <div
                            class="
                                justify-self-end
                                h-2.5
                                w-12
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