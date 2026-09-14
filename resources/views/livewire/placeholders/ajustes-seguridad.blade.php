<div class="animate-pulse">

    {{-- ============================================================
        NAVEGACIÓN - ESCRITORIO
    ============================================================ --}}
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
        ACCESO
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            {{-- Icono --}}
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    bg-[var(--theme-surface-soft)]
                "
            ></div>


            {{-- Textos --}}
            <div
                class="
                    min-w-0
                    flex-1
                "
            >
                <div
                    class="
                        h-4
                        w-20

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        mt-2

                        h-3
                        w-72
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>
        </div>


        {{-- ========================================================
            CAMBIAR CONTRASEÑA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-4
            "
        >
            <div
                class="
                    min-w-0
                    flex-1
                "
            >
                <div
                    class="
                        h-4
                        w-40

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
                    h-9
                    w-full
                    sm:w-44
                    shrink-0

                    rounded-md

                    bg-[var(--theme-surface-soft)]

                    border
                    border-[var(--theme-border)]
                "
            ></div>
        </div>


        {{-- Separador --}}
        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            AUTENTICACIÓN EN DOS PASOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-4
            "
        >
            <div
                class="
                    min-w-0
                    flex-1

                    flex
                    items-start
                    gap-3
                "
            >
                {{-- Icono --}}
                <div
                    class="
                        w-9
                        h-9
                        shrink-0

                        rounded-lg

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        min-w-0
                        flex-1
                    "
                >
                    {{-- Título + estado --}}
                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <div
                            class="
                                h-4
                                w-48
                                max-w-[65%]

                                rounded

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>


                        <div
                            class="
                                h-5
                                w-14

                                rounded-full

                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>
                    </div>


                    {{-- Descripción --}}
                    <div
                        class="
                            mt-2

                            h-3
                            w-96
                            max-w-full

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>


                    <div
                        class="
                            mt-2

                            sm:hidden

                            h-3
                            w-56
                            max-w-full

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>
            </div>


            {{-- Botón --}}
            <div
                class="
                    h-9
                    w-full
                    sm:w-32
                    shrink-0

                    rounded-md

                    bg-[var(--theme-surface-soft)]

                    border
                    border-[var(--theme-border)]
                "
            ></div>
        </div>
    </section>

</div>