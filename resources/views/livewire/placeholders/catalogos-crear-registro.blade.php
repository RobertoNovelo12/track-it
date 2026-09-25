<div
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,

        showSuccess(message) {
            this.successMessage = message ?? 'El registro se creó correctamente.';
            this.successVisible = true;

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 3200);
        }
    }"

    @catalog-record-created.window="
        showSuccess(
            $event.detail.message ?? 'El registro se creó correctamente.'
        )
    "

    class="animate-pulse"
>

    {{-- ============================================================
        CABECERA
    ============================================================ --}}
    <div class="mb-5">

        {{-- Título --}}
        <div
            class="
                h-6
                w-72
                max-w-full

                rounded-md

                bg-[var(--theme-surface-soft)]
            "
        ></div>


        {{-- Breadcrumb --}}
        <div
            class="
                flex
                items-center
                gap-2

                mt-2
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
                    w-24

                    rounded

                    bg-[var(--theme-surface-soft)]
                "
            ></div>
        </div>

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
            <div
                class="
                    h-4
                    w-36

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
                {{-- Tipo de registro --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Código interno --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Estado inicial --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            flex
                            items-center

                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    >
                        <div
                            class="
                                h-6
                                w-14

                                rounded-full

                                bg-[var(--theme-border)]
                            "
                        ></div>
                    </div>

                </div>


                {{-- Nombre --}}
                <div class="min-w-0">

                    <div
                        class="
                            h-3
                            w-28

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

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


                {{-- Campo dinámico 1 --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Campo dinámico 2 --}}
                <div class="min-w-0">

                    <div
                        class="
                            h-3
                            w-28

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

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


                {{-- Sitio web --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- País / dato heredado --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Espacio estructural --}}
                <div class="hidden xl:block"></div>


                {{-- Descripción --}}
                <div class="min-w-0 xl:col-span-3">

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
                {{-- Proveedor --}}
                <div class="min-w-0">

                    <div
                        class="
                            h-3
                            w-28

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

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


                {{-- Garantía --}}
                <div class="min-w-0">

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
                            h-[42px]
                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>


                {{-- Contacto proveedor --}}
                <div class="min-w-0">

                    <div
                        class="
                            h-3
                            w-32

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

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


                {{-- Área opcional --}}
                <div class="min-w-0">

                    <div
                        class="
                            h-3
                            w-28

                            rounded

                            bg-[var(--theme-surface-soft)]

                            mb-1.5
                        "
                    ></div>

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


                {{-- Comentarios --}}
                <div
                    class="
                        min-w-0

                        md:col-span-2
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
            ACCIONES
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