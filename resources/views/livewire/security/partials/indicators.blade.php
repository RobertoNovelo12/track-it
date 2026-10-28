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

    {{-- Registrados este mes --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-primary-border)]

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

                flex
                items-center
                justify-center

                bg-[var(--theme-primary-soft)]
                text-[var(--theme-primary)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            ><use href="{{ \App\Support\Icono::url('i-a66f8d6') }}"></use></svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-primary)]
                "
            >
                Nuevos usuarios
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-primary)]
                "
            >
                {{ number_format($stats['registrados_mes']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Registrados este mes
            </p>

        </div>

    </div>


    {{-- Activos --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-success-border)]

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

                flex
                items-center
                justify-center

                bg-[var(--theme-success-soft)]
                text-[var(--theme-success)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            ><use href="{{ \App\Support\Icono::url('i-d0eabec') }}"></use></svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-success)]
                "
            >
                Usuarios activos
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-success)]
                "
            >
                {{ number_format($stats['activos']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Cuentas habilitadas
            </p>

        </div>

    </div>


    {{-- Pendientes --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-warning-border)]

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

                flex
                items-center
                justify-center

                bg-[var(--theme-warning-soft)]
                text-[var(--theme-warning)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            ><use href="{{ \App\Support\Icono::url('i-2361b46') }}"></use></svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-warning)]
                "
            >
                Pendientes
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-warning)]
                "
            >
                {{ number_format($stats['pendientes']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Requieren aprobación
            </p>

        </div>

    </div>


    {{-- Roles --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border-strong)]

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

                flex
                items-center
                justify-center

                bg-[var(--theme-surface-soft)]
                text-[var(--theme-text-strong)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            ><use href="{{ \App\Support\Icono::url('i-7ad5c3f') }}"></use></svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                Roles configurados
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-text-strong)]
                "
            >
                {{ number_format($stats['roles']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                {{ number_format($stats['total']) }} usuarios totales
            </p>

        </div>

    </div>

</div>
