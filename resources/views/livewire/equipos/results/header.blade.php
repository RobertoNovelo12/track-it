{{-- ============================================================
    BARRA DE RESULTADOS - ESCRITORIO
============================================================ --}}
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

    {{-- ========================================================
        RESULTADOS
    ======================================================== --}}
    <p class="text-sm text-[var(--theme-text)]">

        Resultados:

        <span class="font-medium">
            {{ number_format($equipos->total()) }}
        </span>

        {{ $equipos->total() === 1
            ? 'Equipo encontrado'
            : 'Equipos encontrados'
        }}

    </p>


    {{-- ========================================================
        CONTROLES
    ======================================================== --}}
    <div class="flex items-center gap-4">

        {{-- ====================================================
            CANTIDAD POR PÁGINA
        ==================================================== --}}
        <div class="flex items-center gap-2">

            <span
                class="
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Mostrar
            </span>


            <select
                wire:model.live="perPage"

                class="
                    text-xs

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    px-2
                    py-1.5

                    bg-[var(--theme-surface)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                "
            >

                @foreach ([10, 25, 50, 100] as $option)

                    <option value="{{ $option }}">
                        {{ $option }}
                    </option>

                @endforeach

            </select>


            <span
                class="
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Por página
            </span>

        </div>


        {{-- ====================================================
            DIRECCIÓN DEL ORDENAMIENTO
        ==================================================== --}}
        <select
            wire:model.live="sort"

            class="
                text-xs

                border
                border-[var(--theme-border-strong)]

                rounded-md

                px-2
                py-1.5

                bg-[var(--theme-surface)]

                uppercase

                focus:ring-1
                focus:ring-[var(--theme-primary)]
            "
        >

            <option value="asc">
                ASC
            </option>

            <option value="desc">
                DESC
            </option>

        </select>


        {{-- ====================================================
            ACTUALIZAR TABLA
        ==================================================== --}}
        <button
            type="button"

            wire:click="refreshTable"

            wire:loading.attr="disabled"
            wire:target="refreshTable"

            title="Actualizar tabla"
            aria-label="Actualizar tabla"

            class="
                w-8
                h-8

                shrink-0

                flex
                items-center
                justify-center

                rounded-md

                border
                border-[var(--theme-border-strong)]

                bg-[var(--theme-surface)]

                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface-soft)]
                hover:text-[var(--theme-primary)]
                hover:border-[var(--theme-primary-border)]

                disabled:opacity-60
                disabled:cursor-not-allowed

                transition-colors
            "
        >

            <svg
                wire:loading.class="animate-spin"
                wire:target="refreshTable"

                class="
                    w-4
                    h-4
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"

                aria-hidden="true"
            >
                <path
                    d="
                        M20 11
                        a8 8 0 1 0-2.3 5.7
                    "
                />

                <path
                    d="
                        M20 4
                        v7
                        h-7
                    "
                />
            </svg>

        </button>

    </div>

</div>