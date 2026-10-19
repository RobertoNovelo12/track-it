<div class="animate-pulse">

    {{-- ============================================================
        NAVEGACIÓN
    ============================================================ --}}

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
        Misma estructura que settings/account y settings/operation.
    ============================================================ --}}
    <div class="space-y-5">

        {{-- ========================================================
            INFORMACIÓN PERSONAL
        ======================================================== --}}
        <section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">

            {{-- Encabezado --}}
            <div class="flex flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 shrink-0 rounded-full bg-[var(--theme-surface-soft)]"></div>

                    <div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-44 rounded bg-[var(--theme-surface-soft)]"></div>
                            <div class="h-4 w-12 rounded-full bg-[var(--theme-surface-soft)]"></div>
                        </div>

                        <div class="mt-2 h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                    </div>
                </div>

                <div class="h-9 w-28 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)]"></div>
            </div>

            {{-- Datos --}}
            <div class="border-t border-[var(--theme-border)] px-4 py-2 sm:px-5">
                <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10">
                    @for ($columna = 0; $columna < 2; $columna++)
                        <div>
                            @for ($fila = 0; $fila < 2; $fila++)
                                <div @class([
                                    'flex items-center gap-4 py-3 border-[var(--theme-border)]',
                                    'border-b' => ! ($columna === 1 && $fila === 1),
                                    'md:border-b-0' => $fila === 1,
                                ])>
                                    <div class="h-3 w-40 shrink-0 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="h-4 w-36 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </section>


        {{-- ========================================================
            INFORMACIÓN ORGANIZACIONAL
        ======================================================== --}}
        <section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">

            {{-- Encabezado --}}
            <div class="flex flex-col gap-4 px-4 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-5">
                <div>
                    <div class="h-4 w-48 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="mt-2 h-3 w-72 max-w-full rounded bg-[var(--theme-surface-soft)]"></div>
                </div>

                <div class="h-9 w-36 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)]"></div>
            </div>

            {{-- Datos --}}
            <div class="border-t border-[var(--theme-border)] px-4 py-4 sm:px-5">
                <div class="grid grid-cols-1 gap-y-5 md:grid-cols-2 md:gap-x-10">
                    @for ($grupo = 0; $grupo < 2; $grupo++)
                        <div>
                            <div class="mb-1 h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>

                            @for ($fila = 0; $fila < 4; $fila++)
                                <div @class([
                                    'flex items-center gap-4 py-3 border-[var(--theme-border)]',
                                    'border-b' => $fila < 3,
                                ])>
                                    <div class="h-3 w-40 shrink-0 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </section>

    </div>

</div>
