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
    <div>

        <h1
            class="
                text-xl
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Gestión de marcas y modelos
        </h1>

        <p
            class="
                mt-1

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            Inicio

            <span class="mx-1">
                &gt;
            </span>

            Catálogo
        </p>

    </div>


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
        >
            <path d="M12 5v14"/>
            <path d="M5 12h14"/>
        </svg>

        Añadir
    </a>
</div>