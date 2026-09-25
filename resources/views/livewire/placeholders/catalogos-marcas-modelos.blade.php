<div
    x-data="{
        toastOpen: false,
        toastMessage: '',
        toastTimer: null,

        showToast(message) {
            this.toastMessage = message ?? 'Cambios guardados correctamente.';
            this.toastOpen = true;

            if (this.toastTimer) {
                clearTimeout(this.toastTimer);
            }

            this.toastTimer = setTimeout(() => {
                this.toastOpen = false;
            }, 3200);
        }
    }"
    @catalog-item-saved.window="showToast($event.detail.message)"
    @catalog-status-updated.window="showToast($event.detail.message)"
    class="animate-pulse space-y-5"
>

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div
        class="
            flex
            flex-col
            lg:flex-row
            lg:items-end
            lg:justify-between
            gap-4
        "
    >
        <div class="space-y-2">

            <div
                class="
                    h-6
                    w-64
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-3
                    w-80
                    max-w-full
                    rounded
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

        </div>


        <div
            class="
                h-9
                w-28
                rounded-md
                bg-[var(--theme-surface-soft)]
            "
        ></div>
    </div>


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <section
        class="
            p-4

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]
        "
    >
        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-[minmax(0,1fr)_160px_190px]
                gap-3
            "
        >
            <div
                class="
                    h-10
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-10
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

            <div
                class="
                    h-10
                    rounded-md
                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-2
            xl:grid-cols-4
            gap-3
        "
    >
        @for ($i = 0; $i < 4; $i++)

            <div
                class="
                    p-4

                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]
                "
            >
                <div class="flex items-center gap-3">

                    <div
                        class="
                            w-10
                            h-10
                            shrink-0

                            rounded-lg

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>


                    <div class="space-y-2 flex-1">

                        <div
                            class="
                                h-6
                                w-12
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

                </div>
            </div>

        @endfor
    </div>


    {{-- ============================================================
        TABLA DE MARCAS
    ============================================================ --}}
    <section
        class="
            relative

            bg-transparent
            border-0
            rounded-none
            overflow-visible

            md:bg-[var(--theme-surface)]
            md:border
            md:border-[var(--theme-border)]
            md:rounded-lg
            md:overflow-hidden
        "
    >
        {{-- BARRA SUPERIOR --}}
        <div
            class="
                hidden
                md:flex

                items-center
                justify-between

                px-5
                py-4

                border-b
                border-[var(--theme-border)]
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


            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">

                    <div
                        class="
                            h-3
                            w-12
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            h-8
                            w-14
                            rounded-md
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            h-3
                            w-16
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                <div
                    class="
                        h-8
                        w-16
                        rounded-md
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>
        </div>


        {{-- TABLA ESCRITORIO --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b
                            border-[var(--theme-border)]
                        "
                    >
                        <th class="px-5 py-3 w-10">
                            <div
                                class="
                                    w-4
                                    h-4
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </th>

                        @foreach ([
                            'w-16',
                            'w-24',
                            'w-28',
                            'w-16',
                            'w-16',
                            'w-16',
                            'w-20',
                            'w-16',
                        ] as $width)

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        {{ $width }}
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                        @endforeach
                    </tr>

                </thead>


                <tbody>

                    @for ($row = 0; $row < 10; $row++)

                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]
                            "
                        >
                            <td class="px-5 py-3">
                                <div
                                    class="
                                        w-4
                                        h-4
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-36 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            </td>
                        </tr>

                    @endfor

                </tbody>
            </table>
        </div>


        {{-- TARJETAS MÓVIL --}}
        <div class="md:hidden space-y-3">

            @for ($row = 0; $row < 4; $row++)

                <article
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]
                    "
                >
                    <div class="space-y-3">

                        <div class="flex items-center justify-between gap-3">

                            <div class="space-y-2">
                                <div class="h-4 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                                <div class="h-3 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>

                        </div>


                        <div class="h-3 w-full rounded bg-[var(--theme-surface-soft)]"></div>

                        <div class="grid grid-cols-2 gap-3">

                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>

                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>

                        </div>

                    </div>
                </article>

            @endfor
        </div>


        {{-- PAGINACIÓN --}}
        <div
            class="
                hidden
                sm:flex

                items-center
                justify-end
                gap-1

                px-5
                py-4

                border-t
                border-[var(--theme-border)]
            "
        >
            @for ($i = 0; $i < 6; $i++)

                <div
                    class="
                        w-8
                        h-8

                        rounded-full

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            @endfor
        </div>
    </section>


    {{-- ============================================================
        TABLA DE MODELOS
    ============================================================ --}}
    <section
        class="
            relative

            bg-transparent
            border-0
            rounded-none
            overflow-visible

            md:bg-[var(--theme-surface)]
            md:border
            md:border-[var(--theme-border)]
            md:rounded-lg
            md:overflow-hidden
        "
    >
        {{-- BARRA SUPERIOR --}}
        <div
            class="
                hidden
                md:flex

                items-center
                justify-between

                px-5
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <div
                class="
                    h-4
                    w-48
                    rounded
                    bg-[var(--theme-surface-soft)]
                "
            ></div>


            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">

                    <div
                        class="
                            h-3
                            w-12
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            h-8
                            w-14
                            rounded-md
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            h-3
                            w-16
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                <div
                    class="
                        h-8
                        w-16
                        rounded-md
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>
        </div>


        {{-- TABLA ESCRITORIO --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b
                            border-[var(--theme-border)]
                        "
                    >
                        <th class="px-5 py-3 w-10">
                            <div
                                class="
                                    w-4
                                    h-4
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </th>

                        @foreach ([
                            'w-16',
                            'w-24',
                            'w-20',
                            'w-24',
                            'w-28',
                            'w-16',
                            'w-16',
                            'w-20',
                            'w-16',
                        ] as $width)

                            <th class="px-3 py-3">
                                <div
                                    class="
                                        h-3
                                        {{ $width }}
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </th>

                        @endforeach
                    </tr>

                </thead>


                <tbody>

                    @for ($row = 0; $row < 10; $row++)

                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]
                            "
                        >
                            <td class="px-5 py-3">
                                <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-8 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="h-4 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="w-4 h-4 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            </td>
                        </tr>

                    @endfor

                </tbody>
            </table>
        </div>


        {{-- TARJETAS MÓVIL --}}
        <div class="md:hidden space-y-3">

            @for ($row = 0; $row < 4; $row++)

                <article
                    class="
                        p-4

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]
                    "
                >
                    <div class="space-y-3">

                        <div class="flex items-center justify-between gap-3">

                            <div class="space-y-2">
                                <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                                <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>

                        </div>


                        <div class="h-3 w-full rounded bg-[var(--theme-surface-soft)]"></div>

                        <div class="grid grid-cols-2 gap-3">

                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>

                            <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>

                        </div>

                    </div>
                </article>

            @endfor
        </div>


        {{-- PAGINACIÓN --}}
        <div
            class="
                hidden
                sm:flex

                items-center
                justify-end
                gap-1

                px-5
                py-4

                border-t
                border-[var(--theme-border)]
            "
        >
            @for ($i = 0; $i < 6; $i++)

                <div
                    class="
                        w-8
                        h-8

                        rounded-full

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            @endfor
        </div>
    </section>

</div>