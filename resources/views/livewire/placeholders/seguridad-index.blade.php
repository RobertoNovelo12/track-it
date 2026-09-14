<div class="animate-pulse">

    {{-- ============================================================
        INDICADORES
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-2
            xl:grid-cols-4
            gap-4
            mb-6
        "
    >

        @for ($i = 0; $i < 4; $i++)

            <div
                class="
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-lg
                    p-4
                    flex
                    items-center
                    gap-4
                "
            >

                <div
                    class="
                        w-12
                        h-12
                        shrink-0
                        rounded-lg
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div class="flex-1 min-w-0">

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
                            mt-2
                            h-7
                            w-12
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            mt-2
                            h-2.5
                            w-28
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>

            </div>

        @endfor

    </div>


    {{-- ============================================================
        GRID PRINCIPAL
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[minmax(0,1fr)_320px]
            gap-5
        "
    >

        {{-- COLUMNA IZQUIERDA --}}
        <div class="space-y-5">

            {{-- Gestión de usuarios --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-lg
                    overflow-hidden
                "
            >

                {{-- Encabezado --}}
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
                            flex
                            flex-col
                            lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-4
                        "
                    >

                        <div>

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


                        <div class="flex gap-2">

                            <div
                                class="
                                    h-9
                                    w-52
                                    rounded-md
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-9
                                    w-32
                                    rounded-md
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>

                    </div>

                </div>


                {{-- Tabla --}}
                <div>

                    {{-- Header --}}
                    <div
                        class="
                            hidden
                            lg:grid
                            grid-cols-7
                            gap-4
                            px-4
                            py-3
                            bg-[var(--theme-surface-soft)]
                        "
                    >

                        @for ($i = 0; $i < 7; $i++)

                            <div
                                class="
                                    h-3
                                    rounded
                                    bg-[var(--theme-border-strong)]
                                "
                            ></div>

                        @endfor

                    </div>


                    {{-- Filas --}}
                    @for ($fila = 0; $fila < 6; $fila++)

                        <div
                            class="
                                px-4
                                py-4
                                border-t
                                border-[var(--theme-border)]

                                lg:grid
                                lg:grid-cols-7
                                lg:items-center
                                lg:gap-4
                            "
                        >

                            {{-- Usuario --}}
                            <div
                                class="
                                    flex
                                    items-center
                                    gap-3
                                "
                            >

                                <div
                                    class="
                                        w-8
                                        h-8
                                        shrink-0
                                        rounded-full
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>

                                <div class="flex-1">

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
                                            mt-1.5
                                            h-2
                                            w-16
                                            rounded
                                            bg-[var(--theme-surface-soft)]
                                        "
                                    ></div>

                                </div>

                            </div>


                            @for ($col = 0; $col < 6; $col++)

                                <div
                                    class="
                                        hidden
                                        lg:block
                                        h-3
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>

                            @endfor

                        </div>

                    @endfor

                </div>

            </section>


            {{-- Bitácora --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-lg
                    overflow-hidden
                "
            >

                <div
                    class="
                        px-5
                        py-4
                        border-b
                        border-[var(--theme-border)]
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

                </div>


                @for ($i = 0; $i < 4; $i++)

                    <div
                        class="
                            grid
                            grid-cols-5
                            gap-4
                            px-5
                            py-4
                            border-t
                            border-[var(--theme-border)]
                        "
                    >

                        @for ($j = 0; $j < 5; $j++)

                            <div
                                class="
                                    h-3
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        @endfor

                    </div>

                @endfor

            </section>

        </div>


        {{-- COLUMNA DERECHA --}}
        <aside class="space-y-5">

            {{-- Resumen semanal --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-lg
                    p-4
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
                        mt-6
                        h-44
                        flex
                        items-end
                        justify-between
                        gap-2
                    "
                >

                    @foreach ([45, 70, 35, 85, 60, 50, 75] as $altura)

                        <div
                            class="
                                flex-1
                                flex
                                items-end
                                justify-center
                                gap-1
                            "
                        >

                            <div
                                class="
                                    w-2.5
                                    rounded-t
                                    bg-[var(--theme-surface-soft)]
                                "
                                style="height: {{ $altura }}%"
                            ></div>

                            <div
                                class="
                                    w-2.5
                                    rounded-t
                                    bg-[var(--theme-surface-soft)]
                                "
                                style="height: {{ max(20, $altura - 20) }}%"
                            ></div>

                        </div>

                    @endforeach

                </div>

            </section>


            {{-- Alertas --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-lg
                    p-4
                "
            >

                <div
                    class="
                        h-4
                        w-32
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div class="mt-4 space-y-3">

                    @for ($i = 0; $i < 3; $i++)

                        <div
                            class="
                                flex
                                items-center
                                gap-3
                                p-3
                                rounded-lg
                                border
                                border-[var(--theme-border)]
                            "
                        >

                            <div
                                class="
                                    w-8
                                    h-8
                                    shrink-0
                                    rounded-full
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    flex-1
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>

                    @endfor

                </div>

            </section>

        </aside>

    </div>

</div>