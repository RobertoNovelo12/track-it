{{-- ============================================================
    MÉTRICAS
============================================================ --}}
<div
    @class([
        'grid',
        'grid-cols-1',
        'sm:grid-cols-2',
        'gap-3',

        'xl:grid-cols-4' =>
            $vista === 'ambos',
    ])
>

    {{-- ========================================================
        TOTAL MARCAS
    ======================================================== --}}
    @if ($vista !== 'modelos')

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
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    ><use href="{{ \App\Support\Icono::url('i-7eb8154') }}"></use></svg>
                </div>


                <div>

                    <p

                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        {{ number_format((int) ($metricas['totalMarcas'] ?? 0)) }}
                    </p>

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

    @endif


    {{-- ========================================================
        MARCAS INACTIVAS
    ======================================================== --}}
    @if ($vista !== 'modelos')

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
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    ><use href="{{ \App\Support\Icono::url('i-90f5a0e') }}"></use></svg>
                </div>


                <div>

                    <p

                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ number_format((int) ($metricas['marcasInactivas'] ?? 0)) }}
                    </p>

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

    @endif


    {{-- ========================================================
        TOTAL MODELOS
    ======================================================== --}}
    @if ($vista !== 'marcas')

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
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    ><use href="{{ \App\Support\Icono::url('i-0fd8210') }}"></use></svg>
                </div>


                <div>

                    <p

                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        {{ number_format((int) ($metricas['totalModelos'] ?? 0)) }}
                    </p>

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

    @endif


    {{-- ========================================================
        MODELOS EN USO
    ======================================================== --}}
    @if ($vista !== 'marcas')

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
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    ><use href="{{ \App\Support\Icono::url('i-fcd4d5c') }}"></use></svg>
                </div>


                <div>

                    <p

                        class="
                            text-2xl
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ number_format((int) ($metricas['modelosEnUso'] ?? 0)) }}
                    </p>

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

    @endif

</div>