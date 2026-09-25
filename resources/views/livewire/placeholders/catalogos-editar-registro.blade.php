<div
    class="animate-pulse"
>

    {{-- ============================================================
        CABECERA
    ============================================================ --}}
    <div
        class="
            flex
            flex-col

            lg:flex-row
            lg:items-start
            lg:justify-between

            gap-4

            mb-5
        "
    >
        <div class="space-y-2">

            {{-- TÍTULO --}}
            <div
                class="
                    h-6
                    w-40

                    rounded-md

                    bg-[var(--theme-surface-soft)]
                "
            ></div>


            {{-- BREADCRUMB --}}
            <div
                class="
                    flex
                    items-center
                    gap-2
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
                        w-3

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

                <div
                    class="
                        h-3
                        w-3

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-12

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>

        </div>


        {{-- ========================================================
            ACCIONES SUPERIORES
        ======================================================== --}}
        <div
            class="
                flex
                flex-col

                sm:flex-row
                sm:items-center

                gap-2
            "
        >
            {{-- VOLVER --}}
            <div
                class="
                    h-10
                    w-full
                    sm:w-24

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]
                "
            ></div>


            {{-- ACTIVAR / DESACTIVAR --}}
            <div
                class="
                    h-10
                    w-full
                    sm:w-28

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>

    </div>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <div
        class="
            space-y-4
            sm:space-y-6
        "
    >

        {{-- ========================================================
            INFORMACIÓN GENERAL
        ======================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4
                sm:p-6
            "
        >
            {{-- ENCABEZADO DE SECCIÓN --}}
            <div
                class="
                    flex
                    flex-col

                    gap-2

                    sm:flex-row
                    sm:items-center
                    sm:justify-between

                    mb-5
                "
            >
                {{-- TÍTULO --}}
                <div
                    class="
                        h-4
                        w-36

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                {{-- CÓDIGO INTERNO / IDENTIFICADOR --}}
                <div
                    class="
                        flex
                        items-center
                        gap-2
                    "
                >
                    <div
                        class="
                            h-3
                            w-20

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            h-7
                            w-24

                            rounded-md

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

            </div>


            {{-- CAMPOS --}}
            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4
                "
            >
                @for ($i = 0; $i < 9; $i++)

                    <div class="min-w-0">

                        {{-- LABEL --}}
                        <div
                            class="
                                h-3

                                {{ $i % 3 === 0
                                    ? 'w-24'
                                    : (
                                        $i % 3 === 1
                                            ? 'w-20'
                                            : 'w-28'
                                    )
                                }}

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mb-1.5
                            "
                        ></div>


                        {{-- INPUT --}}
                        <div
                            class="
                                h-[42px]
                                w-full

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                @endfor


                {{-- ====================================================
                    DESCRIPCIÓN
                ==================================================== --}}
                <div
                    class="
                        min-w-0
                        xl:col-span-3
                    "
                >
                    <div
                        class="
                            h-3
                            w-20

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

                    <div
                        class="
                            h-24
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>

            </div>

        </section>


        {{-- ========================================================
            INFORMACIÓN ADICIONAL
        ======================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4
                sm:p-6
            "
        >
            {{-- TÍTULO --}}
            <div
                class="
                    h-4
                    w-40

                    rounded

                    bg-[var(--theme-surface-soft)]

                    mb-5
                "
            ></div>


            {{-- CAMPOS --}}
            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4
                "
            >
                @for ($i = 0; $i < 5; $i++)

                    <div class="min-w-0">

                        {{-- LABEL --}}
                        <div
                            class="
                                h-3

                                {{ $i % 2 === 0
                                    ? 'w-28'
                                    : 'w-24'
                                }}

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mb-1.5
                            "
                        ></div>


                        {{-- INPUT --}}
                        <div
                            class="
                                h-[42px]
                                w-full

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                @endfor


                {{-- ====================================================
                    COMENTARIOS
                ==================================================== --}}
                <div
                    class="
                        min-w-0

                        md:col-span-2
                        xl:col-span-3
                    "
                >
                    <div
                        class="
                            h-3
                            w-24

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

                    <div
                        class="
                            h-28
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>

            </div>

        </section>


        {{-- ========================================================
            ACCIONES INFERIORES
        ======================================================== --}}
        <div
            class="
                flex
                flex-col-reverse

                sm:flex-row
                sm:items-center
                sm:justify-end

                gap-3

                pt-1
                pb-2
            "
        >
            {{-- CANCELAR --}}
            <div
                class="
                    h-11

                    w-full
                    sm:w-24

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]
                "
            ></div>


            {{-- GUARDAR CAMBIOS --}}
            <div
                class="
                    h-11

                    w-full
                    sm:w-36

                    rounded-lg

                    bg-[var(--theme-surface-soft)]
                "
            ></div>

        </div>

    </div>

</div>