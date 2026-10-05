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

        {{-- BUSCADOR --}}
        <div class="relative">

            <div
                class="
                    pointer-events-none

                    absolute
                    inset-y-0
                    left-0

                    w-10

                    flex
                    items-center
                    justify-center

                    text-[var(--theme-text-muted)]
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    />

                    <path d="M20 20l-3.5-3.5"/>
                </svg>
            </div>


            <input
                type="search"

                x-model="search"

                placeholder="Buscar por marca, modelo, tipo o descripción..."

                class="
                    w-full
                    h-10

                    pl-10
                    pr-3

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface-soft)]

                    text-xs
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    focus:outline-none
                    focus:border-[var(--theme-primary)]

                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]

                    transition-colors
                "
            >

        </div>


        {{-- ESTADO --}}
        <div class="min-w-0">

            <x-searchable-select
                x-model="estado"
                x-options="estadoOptions"

                placeholder="Buscar estado..."

                :show-clear="false"

                compact
            />

        </div>


        {{-- TIPO --}}
        <div class="min-w-0">

            <x-searchable-select
                x-model="tipo"
                x-options="tipoOptions"

                placeholder="Buscar tipo..."

                :show-clear="false"

                compact
            />

        </div>

    </div>
</section>