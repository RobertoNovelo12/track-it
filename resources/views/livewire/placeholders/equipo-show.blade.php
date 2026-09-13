<div class="animate-pulse">

    {{-- ============================================================
        RESUMEN DEL EQUIPO
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]
            border border-[var(--theme-border)]
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
                gap-4
                sm:flex-row
                sm:items-start
                sm:justify-between
            "
        >

            {{-- Información principal --}}
            <div class="flex items-start gap-3 flex-1 min-w-0">

                {{-- Icono --}}
                <div
                    class="
                        shrink-0
                        w-12 h-12
                        rounded-xl
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div class="flex-1 min-w-0">

                    {{-- Título --}}
                    <div
                        class="
                            h-5
                            w-3/4
                            sm:w-64
                            bg-[var(--theme-surface-soft)]
                            rounded
                        "
                    ></div>


                    {{-- Nombre secundario --}}
                    <div
                        class="
                            h-3
                            w-40
                            bg-[var(--theme-surface-soft)]
                            rounded
                            mt-2
                        "
                    ></div>


                    {{-- Código / serie --}}
                    <div class="flex gap-3 mt-3">

                        <div
                            class="
                                h-3
                                w-24
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                        <div
                            class="
                                h-3
                                w-28
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                    </div>

                </div>

            </div>


            {{-- Exportar --}}
            <div
                class="
                    h-10
                    w-full
                    sm:w-28
                    rounded-lg
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

        </div>


        {{-- Resumen inferior --}}
        <div
            class="
                grid
                grid-cols-2
                lg:grid-cols-4
                gap-4
                mt-5
                pt-4
                border-t border-[var(--theme-border)]
            "
        >

            @for ($i = 0; $i < 4; $i++)

                <div>

                    <div
                        class="
                            h-2.5
                            w-12
                            bg-[var(--theme-surface-soft)]
                            rounded
                            mb-2
                        "
                    ></div>

                    <div
                        class="
                            h-3
                            {{ $i === 3 ? 'w-28' : 'w-20' }}
                            bg-[var(--theme-surface-soft)]
                            rounded
                        "
                    ></div>

                </div>

            @endfor

        </div>

    </section>


    {{-- ============================================================
        TARJETAS PRINCIPALES
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-3
            gap-4
            sm:gap-5
            mb-4
            sm:mb-6
        "
    >

        {{-- Información general --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-5
            "
        >

            {{-- Título --}}
            <div class="flex items-center gap-2 mb-5">

                <div
                    class="
                        w-5 h-5
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4
                        w-36
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            {{-- Filas --}}
            <div class="space-y-4">

                @for ($i = 0; $i < 11; $i++)

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-4
                        "
                    >

                        <div
                            class="
                                h-3
                                w-24
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                        <div
                            class="
                                h-3
                                {{ $i % 3 === 0 ? 'w-28' : 'w-20' }}
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- Especificaciones --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-5
            "
        >

            <div class="flex items-center gap-2 mb-5">

                <div
                    class="
                        w-5 h-5
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4
                        w-44
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            <div class="space-y-4">

                @for ($i = 0; $i < 6; $i++)

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-4
                        "
                    >

                        <div
                            class="
                                h-3
                                w-28
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                        <div
                            class="
                                h-3
                                w-20
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- Asignación --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-5
            "
        >

            <div class="flex items-center gap-2 mb-5">

                <div
                    class="
                        w-5 h-5
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4
                        w-32
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            <div class="space-y-4">

                @for ($i = 0; $i < 4; $i++)

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-4
                        "
                    >

                        <div
                            class="
                                h-3
                                w-24
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                        <div
                            class="
                                h-3
                                w-28
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>

    </div>


    {{-- ============================================================
        HISTORIALES
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-2
            gap-4
            sm:gap-5
        "
    >

        {{-- Movimientos --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                overflow-hidden
            "
        >

            <div
                class="
                    flex
                    items-center
                    gap-2
                    p-4
                    sm:p-5
                    border-b border-[var(--theme-border)]
                "
            >

                <div
                    class="
                        w-5 h-5
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4
                        w-40
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            <div class="p-4 sm:p-5 space-y-4">

                @for ($i = 0; $i < 4; $i++)

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-4
                        "
                    >

                        <div class="space-y-2">

                            <div
                                class="
                                    h-3
                                    w-32
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                            <div
                                class="
                                    h-2.5
                                    w-24
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                        </div>


                        <div
                            class="
                                h-3
                                w-16
                                bg-[var(--theme-surface-soft)]
                                rounded
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- Mantenimiento --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                overflow-hidden
            "
        >

            <div
                class="
                    flex
                    items-center
                    gap-2
                    p-4
                    sm:p-5
                    border-b border-[var(--theme-border)]
                "
            >

                <div
                    class="
                        w-5 h-5
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4
                        w-48
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            <div
                class="
                    flex
                    flex-col
                    items-center
                    justify-center
                    px-5
                    py-10
                "
            >

                <div
                    class="
                        w-10
                        h-10
                        rounded-full
                        bg-[var(--theme-surface-soft)]
                        mb-3
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-44
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-2.5
                        w-52
                        max-w-full
                        bg-[var(--theme-surface-soft)]
                        rounded
                        mt-2
                    "
                ></div>

            </div>

        </section>

    </div>

</div>