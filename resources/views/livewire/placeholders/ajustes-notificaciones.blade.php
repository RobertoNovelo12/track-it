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
        ACTIVIDAD OPERATIVA
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
                        w-36

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


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
            </div>
        </div>


        {{-- ========================================================
            ASIGNACIONES Y MOVIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-52
                        max-w-[75%]

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        mt-2

                        h-3
                        w-[430px]
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
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
            MANTENIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-36

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


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
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
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
            CAMBIOS EN EQUIPOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-[420px]
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>

    </section>


    {{-- ============================================================
        ADMINISTRACIÓN
    ============================================================ --}}
    <section
        class="
            mt-5

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
                        w-28

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


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
            </div>
        </div>


        {{-- ========================================================
            USUARIOS Y ACCESOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-[430px]
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
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
            REPORTES
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-24

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        mt-2

                        h-3
                        w-80
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>

    </section>


    {{-- ============================================================
        CENTRO DE NOTIFICACIONES
    ============================================================ --}}
    <section
        class="
            mt-5

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
                        w-44

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


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
            </div>
        </div>


        {{-- ========================================================
            SOLO NO LEÍDAS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-64
                        max-w-[75%]

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        mt-2

                        h-3
                        w-80
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
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
            MANTENER HISTORIAL
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between

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
                        w-36

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div
                    class="
                        mt-2

                        h-3
                        w-[430px]
                        max-w-full

                        rounded

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>
            </div>


            {{-- Switch --}}
            <div
                class="
                    w-11
                    h-6
                    shrink-0

                    rounded-full

                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>


        {{-- ========================================================
            PIE / GUARDAR CAMBIOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-t
                border-[var(--theme-border)]

                bg-[var(--theme-surface-soft)]

                flex
                justify-end
            "
        >
            <div
                class="
                    h-9
                    w-full
                    sm:w-36

                    rounded-md

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]
                "
            ></div>
        </div>

    </section>

</div>