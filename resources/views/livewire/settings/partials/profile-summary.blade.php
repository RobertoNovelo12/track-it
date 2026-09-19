{{-- ========================================================
    RESUMEN DE PERFIL
======================================================== --}}
<aside
    class="
        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border)]

        rounded-xl

        p-5
    "
>

    <div
        class="
            flex
            flex-col
            items-center

            text-center
        "
    >

        {{-- Avatar --}}
        <div
            class="
                w-20
                h-20

                rounded-full

                flex
                items-center
                justify-center

                bg-[var(--theme-primary-soft)]
                text-[var(--theme-primary)]

                text-2xl
                font-semibold
                uppercase
            "
        >
            {{ mb_substr($usuario->nombres ?? '?', 0, 1) }}
            {{ mb_substr($usuario->apellido_paterno ?? '', 0, 1) }}
        </div>


        {{-- Nombre --}}
        <h2
            class="
                mt-4

                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            {{ $nombreCompleto }}
        </h2>


        {{-- Usuario --}}
        <p
            class="
                mt-1

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            {{ '@' . $usuario->username }}
        </p>


        {{-- Estado --}}
        @php
            $estadoClave = strtoupper(
                $usuario->estado_clave ?? ''
            );

            $estadoClasses = match ($estadoClave) {
                'ACTIVO' =>
                    'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                'PENDIENTE' =>
                    'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                'INACTIVO', 'BAJA' =>
                    'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                default =>
                    'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
            };
        @endphp


        <span
            class="
                mt-3

                inline-flex
                items-center

                px-2.5
                py-1

                rounded-full

                text-[10px]
                font-medium

                {{ $estadoClasses }}
            "
        >
            {{ $usuario->estado_nombre ?? 'Sin estado' }}
        </span>

    </div>

</aside>