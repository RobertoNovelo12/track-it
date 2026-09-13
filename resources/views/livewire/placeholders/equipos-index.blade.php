<div>

    {{-- ============================================================
        PLACEHOLDER MÓVIL
    ============================================================ --}}
    <div class="md:hidden animate-pulse">

        {{-- Buscador --}}
        <div class="relative mb-3">

            <div
                class="
                    w-full
                    h-12
                    rounded-lg
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            ></div>

            <div
                class="
                    absolute
                    left-4 top-1/2
                    -translate-y-1/2
                    w-5 h-5
                    rounded-full
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

        </div>


        {{-- Botones filtros / ordenar --}}
        <div class="flex items-center gap-3 mb-5">

            <div
                class="
                    h-11 w-28
                    rounded-lg
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            ></div>

            <div
                class="
                    h-11 w-28
                    rounded-lg
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            ></div>

        </div>


        {{-- ========================================================
            TARJETAS MÓVIL
        ======================================================== --}}
        <div class="space-y-3">

            @for ($i = 0; $i < 5; $i++)

                <div
                    class="
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border)]
                        rounded-xl
                        p-4
                        shadow-sm
                    "
                >


                    {{-- Cabecera --}}
                    <div class="flex items-start gap-3">

                        {{-- Selector visual --}}
                        <div class="shrink-0 pt-0.5">
                            <div
                                class="
                                    w-5 h-5
                                    rounded-full
                                    border border-[var(--theme-border-strong)]
                                    bg-[var(--theme-surface)]
                                "
                            ></div>
                        </div>


                        {{-- Información principal --}}
                        <div class="flex-1 min-w-0">

                            {{-- Nombre del equipo --}}
                            <div
                                class="
                                    h-4
                                    w-3/4
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                            {{-- Código --}}
                            <div
                                class="
                                    h-3
                                    w-20
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                    mt-2
                                "
                            ></div>

                        </div>


                        {{-- Menú visual --}}
                        <div class="shrink-0 flex flex-col items-center gap-1 pt-1">

                            <div class="w-1 h-1 bg-[var(--theme-border-strong)] rounded-full"></div>
                            <div class="w-1 h-1 bg-[var(--theme-border-strong)] rounded-full"></div>
                            <div class="w-1 h-1 bg-[var(--theme-border-strong)] rounded-full"></div>

                        </div>

                    </div>              


                    {{-- Información --}}
                    <div class="mt-4 space-y-3">

                        {{-- SN --}}
                        <div class="flex items-center gap-2">

                            <div
                                class="
                                    h-3
                                    w-7
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                            <div
                                class="
                                    h-3
                                    w-24
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                        </div>


                        {{-- Service Tag --}}
                        <div class="flex items-center gap-2">

                            <div
                                class="
                                    h-3
                                    w-20
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


                        {{-- Estado --}}
                        <div class="flex items-center gap-2">

                            <div
                                class="
                                    h-3
                                    w-12
                                    bg-[var(--theme-surface-soft)]
                                    rounded
                                "
                            ></div>

                            <div
                                class="
                                    h-5
                                    w-20
                                    bg-[var(--theme-surface-soft)]
                                    rounded-full
                                "
                            ></div>

                        </div>


                        {{-- Área --}}
                        <div class="flex items-center gap-2">

                            <div
                                class="
                                    h-3
                                    w-10
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

            @endfor

        </div>


        {{-- Paginación móvil --}}
        <div
            class="
                flex items-center justify-between
                gap-3
                mt-4
                px-1 py-3
            "
        >

            <div
                class="
                    h-9 w-20
                    rounded-lg
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            ></div>

            <div
                class="
                    h-3 w-20
                    bg-[var(--theme-surface-soft)]
                    rounded
                "
            ></div>

            <div
                class="
                    h-9 w-20
                    rounded-lg
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            ></div>

        </div>

    </div>


    {{-- ============================================================
        PLACEHOLDER ESCRITORIO
    ============================================================ --}}
    <div class="hidden md:block">

        {{-- ========================================================
            FILTROS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-lg
                p-6 mb-6
                animate-pulse
            "
        >

            {{-- Título filtros --}}
            <div class="flex items-center gap-2 mb-5">

                <div
                    class="
                        w-4 h-4
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

                <div
                    class="
                        h-4 w-36
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>

            </div>


            {{-- Primera fila --}}
            <div
                class="
                    grid
                    grid-cols-1
                    sm:grid-cols-2
                    lg:grid-cols-4
                    gap-4
                    mb-4
                "
            >

                @for ($i = 0; $i < 4; $i++)

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
                                h-9
                                w-full
                                bg-[var(--theme-surface-soft)]
                                rounded-md
                            "
                        ></div>

                    </div>

                @endfor

            </div>


            {{-- Segunda fila --}}
            <div
                class="
                    grid
                    grid-cols-1
                    sm:grid-cols-2
                    lg:grid-cols-4
                    gap-4
                "
            >

                @for ($i = 0; $i < 4; $i++)

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
                                h-9
                                w-full
                                bg-[var(--theme-surface-soft)]
                                rounded-md
                            "
                        ></div>

                    </div>

                @endfor

            </div>


            {{-- Botones --}}
            <div
                class="
                    flex
                    items-center
                    justify-end
                    gap-3
                    mt-5
                "
            >

                <div
                    class="
                        h-9 w-28
                        bg-[var(--theme-surface-soft)]
                        rounded-md
                    "
                ></div>

                <div
                    class="
                        h-9 w-32
                        bg-[var(--theme-surface-soft)]
                        rounded-md
                    "
                ></div>

            </div>

        </div>


        {{-- ========================================================
            RESULTADOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border border-[var(--theme-border)]
                rounded-lg
                overflow-hidden
                animate-pulse
            "
        >

            {{-- Barra superior --}}
            <div
                class="
                    flex
                    items-center
                    justify-between
                    px-5 py-4
                    border-b border-[var(--theme-border)]
                "
            >

                <div
                    class="
                        h-4 w-48
                        bg-[var(--theme-surface-soft)]
                        rounded
                    "
                ></div>


                <div class="flex items-center gap-4">

                    <div
                        class="
                            h-7 w-32
                            bg-[var(--theme-surface-soft)]
                            rounded-md
                        "
                    ></div>

                    <div
                        class="
                            h-7 w-16
                            bg-[var(--theme-surface-soft)]
                            rounded-md
                        "
                    ></div>

                </div>

            </div>


            {{-- Tabla --}}
            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>

                        <tr class="border-b border-[var(--theme-border)]">

                            @for ($i = 0; $i < 11; $i++)

                                <th class="px-3 py-3">

                                    <div
                                        class="
                                            h-3
                                            {{ $i === 0 ? 'w-4' : 'w-16' }}
                                            bg-[var(--theme-surface-soft)]
                                            rounded
                                        "
                                    ></div>

                                </th>

                            @endfor

                        </tr>

                    </thead>


                    <tbody>

                        @for ($row = 0; $row < 8; $row++)

                            <tr class="border-b border-[var(--theme-border)]">

                                @for ($col = 0; $col < 11; $col++)

                                    <td class="px-3 py-3">

                                        @if ($col === 0)

                                            <div
                                                class="
                                                    w-4 h-4
                                                    bg-[var(--theme-surface-soft)]
                                                    rounded
                                                "
                                            ></div>

                                        @elseif ($col === 8)

                                            <div
                                                class="
                                                    h-5 w-20
                                                    bg-[var(--theme-surface-soft)]
                                                    rounded-full
                                                "
                                            ></div>

                                        @elseif ($col === 10)

                                            <div
                                                class="
                                                    flex
                                                    items-center
                                                    justify-end
                                                    gap-2
                                                "
                                            >

                                                <div
                                                    class="
                                                        w-4 h-4
                                                        bg-[var(--theme-surface-soft)]
                                                        rounded
                                                    "
                                                ></div>

                                                <div
                                                    class="
                                                        w-4 h-4
                                                        bg-[var(--theme-surface-soft)]
                                                        rounded
                                                    "
                                                ></div>

                                            </div>

                                        @else

                                            <div
                                                class="
                                                    h-3 w-16
                                                    bg-[var(--theme-surface-soft)]
                                                    rounded
                                                "
                                            ></div>

                                        @endif

                                    </td>

                                @endfor

                            </tr>

                        @endfor

                    </tbody>

                </table>

            </div>


            {{-- Paginación escritorio --}}
            <div
                class="
                    flex
                    items-center
                    justify-end
                    gap-1
                    px-5 py-4
                    border-t border-[var(--theme-border)]
                "
            >

                @for ($i = 0; $i < 7; $i++)

                    <div
                        class="
                            w-8 h-8
                            bg-[var(--theme-surface-soft)]
                            rounded-full
                        "
                    ></div>

                @endfor

            </div>

        </div>

    </div>

</div>