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
        @class([
            'grid',
            'grid-cols-1',
            'gap-3',

            'md:grid-cols-[minmax(0,1fr)_160px]' =>
                $vista === 'marcas',

            'md:grid-cols-[minmax(0,1fr)_160px_190px]' =>
                $vista !== 'marcas',
        ])
    >

        {{-- ====================================================
            BUSCADOR
        ==================================================== --}}
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
                    stroke-linecap="round"
                    stroke-linejoin="round"
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

                @if ($vista === 'marcas')
                    placeholder="Buscar por marca o descripción..."
                @else
                    placeholder="Buscar por modelo, marca, tipo o descripción..."
                @endif

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


        {{-- ====================================================
            ESTADO
        ==================================================== --}}
        <div class="min-w-0">

            <x-searchable-select
                x-model="estado"
                x-options="estadoOptions"

                placeholder="Buscar estado..."

                :show-clear="false"

                compact
            />

        </div>


        {{-- ====================================================
            TIPO DE EQUIPO
            Sólo aplica a Modelos
        ==================================================== --}}
        @if ($vista !== 'marcas')

            <div class="min-w-0">

                <x-searchable-select
                    x-model="tipo"
                    x-options="tipoOptions"

                    placeholder="Buscar tipo..."

                    :show-clear="false"

                    compact
                />

            </div>

        @endif

    </div>
</section>