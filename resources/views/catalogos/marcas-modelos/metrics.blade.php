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

    {{-- TOTAL MARCAS --}}
    <div
        class="
            p-4

            rounded-xl

            border
            border-[var(--theme-primary)]

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

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M20 12l-8 8-8-8V4h8l8 8z"/>
                    <circle cx="8.5" cy="8.5" r="1"/>
                </svg>
            </div>


            <div>

                <p
                    x-text="
                        formatNumber(
                            totalMarcas
                        )
                    "

                    class="
                        text-2xl
                        font-semibold
                        text-[var(--theme-primary)]
                    "
                ></p>

                <p
                    class="
                        mt-0.5

                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Total de marcas
                </p>

            </div>

        </div>
    </div>


    {{-- TOTAL MODELOS --}}
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

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <rect
                        x="4"
                        y="5"
                        width="16"
                        height="14"
                        rx="2"
                    />

                    <path d="M8 9h8"/>
                    <path d="M8 13h5"/>
                </svg>
            </div>


            <div>

                <p
                    x-text="
                        formatNumber(
                            totalModelos
                        )
                    "

                    class="
                        text-2xl
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                ></p>

                <p
                    class="
                        mt-0.5

                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Total de modelos
                </p>

            </div>

        </div>
    </div>


    {{-- MARCAS INACTIVAS --}}
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

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M12 8v5"/>
                    <path d="M12 17h.01"/>
                    <path d="M10.3 4.7L2.9 17.5A2 2 0 004.6 20h14.8a2 2 0 001.7-2.5L13.7 4.7a2 2 0 00-3.4 0z"/>
                </svg>
            </div>


            <div>

                <p
                    x-text="
                        formatNumber(
                            marcasInactivas
                        )
                    "

                    class="
                        text-2xl
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                ></p>

                <p
                    class="
                        mt-0.5

                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Marcas inactivas
                </p>

            </div>

        </div>
    </div>


    {{-- MODELOS EN USO --}}
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

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <rect
                        x="5"
                        y="5"
                        width="14"
                        height="14"
                        rx="2"
                    />

                    <path d="M9 9h6v6H9z"/>
                    <path d="M2 9h3"/>
                    <path d="M2 15h3"/>
                    <path d="M19 9h3"/>
                    <path d="M19 15h3"/>
                </svg>
            </div>


            <div>

                <p
                    x-text="
                        formatNumber(
                            modelosEnUso
                        )
                    "

                    class="
                        text-2xl
                        font-semibold
                        text-[var(--theme-primary)]
                    "
                ></p>

                <p
                    class="
                        mt-0.5

                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Modelos en uso
                </p>

            </div>

        </div>
    </div>

</div>  