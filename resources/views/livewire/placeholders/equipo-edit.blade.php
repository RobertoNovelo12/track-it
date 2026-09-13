<div class="animate-pulse">

    {{-- ============================================================
        ACCIONES SUPERIORES
    ============================================================ --}}
    <div
        class="
            flex
            flex-col

            sm:flex-row
            sm:items-center
            sm:justify-end

            gap-3

            mb-4
        "
    >

        <div
            class="
                h-10

                w-full
                sm:w-32

                rounded-lg

                bg-[var(--theme-surface-soft)]
            "
        ></div>


        <div
            class="
                h-10

                w-full
                sm:w-28

                rounded-lg

                bg-[var(--theme-danger-soft)]
            "
        ></div>

    </div>


    <div class="space-y-4 sm:space-y-6">

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

            {{-- Encabezado --}}
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

                <div
                    class="
                        h-4
                        w-36

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div class="flex items-center gap-2">

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
                            h-6
                            w-24

                            rounded-md

                            bg-[var(--theme-primary-soft)]
                        "
                    ></div>

                </div>

            </div>


            {{-- Campos --}}
            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4
                "
            >

                @for ($i = 0; $i < 13; $i++)

                    <div class="min-w-0">

                        <div
                            class="
                                h-3

                                {{ $i % 3 === 0
                                    ? 'w-32'
                                    : 'w-24'
                                }}

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mb-2
                            "
                        ></div>


                        <div
                            class="
                                h-10
                                w-full

                                rounded-md

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- ========================================================
            CAMPOS DINÁMICOS
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

            <div
                class="
                    h-4
                    w-40

                    rounded

                    bg-[var(--theme-surface-soft)]

                    mb-5
                "
            ></div>


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

                        <div
                            class="
                                h-3
                                w-28

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mb-2
                            "
                        ></div>


                        <div
                            class="
                                h-10
                                w-full

                                rounded-md

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                    </div>

                @endfor

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

            <div
                class="
                    h-4
                    w-40

                    rounded

                    bg-[var(--theme-surface-soft)]

                    mb-5
                "
            ></div>


            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4

                    mb-5
                "
            >

                @for ($i = 0; $i < 4; $i++)

                    <div class="min-w-0">

                        <div
                            class="
                                h-3

                                {{ $i === 0
                                    ? 'w-36'
                                    : 'w-24'
                                }}

                                rounded

                                bg-[var(--theme-surface-soft)]

                                mb-2
                            "
                        ></div>


                        <div
                            class="
                                h-10
                                w-full

                                rounded-md

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>


                        @if ($i === 0)

                            <div
                                class="
                                    h-2.5
                                    w-3/4

                                    rounded

                                    bg-[var(--theme-surface-soft)]

                                    mt-2
                                "
                            ></div>

                        @endif

                    </div>

                @endfor

            </div>


            {{-- Comentarios --}}
            <div>

                <div
                    class="
                        h-3
                        w-20

                        rounded

                        bg-[var(--theme-surface-soft)]

                        mb-2
                    "
                ></div>


                <div
                    class="
                        h-24
                        w-full

                        rounded-md

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>

        </section>


        {{-- ========================================================
            BOTONES
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

            {{-- Cancelar --}}
            <div
                class="
                    h-11

                    w-full
                    sm:w-24

                    rounded-lg

                    bg-[var(--theme-surface-soft)]
                "
            ></div>


            {{-- Guardar --}}
            <div
                class="
                    h-11

                    w-full
                    sm:w-36

                    rounded-lg

                    bg-[var(--theme-primary-soft)]
                "
            ></div>

        </div>

    </div>

</div>