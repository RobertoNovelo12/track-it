@php
    $isMarcas =
        $vista === 'marcas';

    $isModelos =
        $vista === 'modelos';

    $title =
        $isMarcas
            ? 'Marcas'
            : (
                $isModelos
                    ? 'Modelos'
                    : 'Marcas y modelos'
            );

    $description =
        $isMarcas
            ? 'Administra las marcas disponibles para los tipos de equipo.'
            : (
                $isModelos
                    ? 'Administra los modelos disponibles y su relación con marcas y tipos de equipo.'
                    : 'Administra las marcas y modelos disponibles.'
            );

    $buttonLabel =
        $isMarcas
            ? 'Añadir marca'
            : (
                $isModelos
                    ? 'Añadir modelo'
                    : 'Añadir'
            );
@endphp


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
    {{-- ========================================================
        TÍTULO / RUTA
    ======================================================== --}}
    <div>

        <h2
            class="
                text-xl
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            {{ $title }}
        </h2>

        <p
            class="
                mt-1

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            {{ $description }}
        </p>

    </div>


    {{-- ========================================================
        ACCIÓN
    ======================================================== --}}
    <a
        href="{{ route('catalogos.create') }}"

        class="
            h-9
            px-4

            inline-flex
            items-center
            justify-center

            gap-2

            rounded-md

            bg-[var(--theme-primary)]
            text-white

            text-xs
            font-medium

            hover:bg-[var(--theme-primary-hover)]

            transition-colors
        "
    >
        <svg
            class="w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M12 5v14"/>
            <path d="M5 12h14"/>
        </svg>

        {{ $buttonLabel }}
    </a>
</div>