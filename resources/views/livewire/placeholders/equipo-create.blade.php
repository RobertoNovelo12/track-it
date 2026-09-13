<div class="animate-pulse">

    <form class="space-y-4 sm:space-y-6">

        {{-- ============================================================
            INFORMACIÓN GENERAL
        ============================================================ --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-6
            "
        >

            {{-- Título --}}
            <div
                class="
                    h-4
                    w-36
                    bg-[var(--theme-surface-soft)]
                    rounded
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

                @for ($i = 0; $i < 11; $i++)

                    <div class="min-w-0">

                        {{-- Label --}}
                        <div
                            class="
                                h-3
                                {{ $i % 3 === 0 ? 'w-32' : 'w-24' }}
                                bg-[var(--theme-surface-soft)]
                                rounded
                                mb-2
                            "
                        ></div>

                        {{-- Campo --}}
                        <div
                            class="
                                h-10
                                w-full
                                bg-[var(--theme-surface-soft)]
                                rounded-md
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- ============================================================
            CAMPOS DINÁMICOS
            Simulamos una sección para evitar salto visual cuando exista.
        ============================================================ --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-6
            "
        >

            {{-- Título --}}
            <div
                class="
                    h-4
                    w-40
                    bg-[var(--theme-surface-soft)]
                    rounded
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

                @for ($i = 0; $i < 3; $i++)

                    <div class="min-w-0">

                        <div
                            class="
                                h-3
                                w-28
                                bg-[var(--theme-surface-soft)]
                                rounded
                                mb-2
                            "
                        ></div>

                        <div
                            class="
                                h-10
                                w-full
                                bg-[var(--theme-surface-soft)]
                                rounded-md
                            "
                        ></div>

                    </div>

                @endfor

            </div>

        </section>


        {{-- ============================================================
            INFORMACIÓN ADICIONAL
        ============================================================ --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-6
            "
        >

            {{-- Título --}}
            <div
                class="
                    h-4
                    w-40
                    bg-[var(--theme-surface-soft)]
                    rounded
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

                @for ($i = 0; $i < 5; $i++)

                    <div class="min-w-0">

                        {{-- Label --}}
                        <div
                            class="
                                h-3
                                {{ $i === 0 ? 'w-36' : 'w-24' }}
                                bg-[var(--theme-surface-soft)]
                                rounded
                                mb-2
                            "
                        ></div>


                        {{-- Campo --}}
                        <div
                            class="
                                h-10
                                w-full
                                bg-[var(--theme-surface-soft)]
                                rounded-md
                            "
                        ></div>


                        {{-- Texto auxiliar del propietario --}}
                        @if ($i === 0)

                            <div
                                class="
                                    h-2.5
                                    w-3/4
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                    mt-2
                                "
                            ></div>

                            <div
                                class="
                                    h-2.5
                                    w-1/2
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                    mt-1
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
                        bg-[var(--theme-surface-soft)]
                        rounded
                        mb-2
                    "
                ></div>

                <div
                    class="
                        h-24
                        w-full
                        bg-[var(--theme-surface-soft)]
                        rounded-md
                    "
                ></div>

            </div>

        </section>


        {{-- ============================================================
            BOTONES
        ============================================================ --}}
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
                    bg-[var(--theme-surface-soft)]
                    rounded-lg
                "
            ></div>

            {{-- Guardar --}}
            <div
                class="
                    h-11
                    w-full
                    sm:w-36
                    bg-[var(--theme-primary-soft)]
                    rounded-lg
                "
            ></div>

        </div>

    </form>

</div>