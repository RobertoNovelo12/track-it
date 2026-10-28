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
                ><use href="{{ \App\Support\Icono::url('i-7eb8154') }}"></use></svg>
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
                ><use href="{{ \App\Support\Icono::url('i-0fd8210') }}"></use></svg>
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
                ><use href="{{ \App\Support\Icono::url('i-90f5a0e') }}"></use></svg>
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
                ><use href="{{ \App\Support\Icono::url('i-fcd4d5c') }}"></use></svg>
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