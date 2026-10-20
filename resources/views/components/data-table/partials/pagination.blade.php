{{-- ============================================================
    PAGINACIÓN
    ------------------------------------------------------------
    Usa los métodos de WithPagination con el nombre de página
    del listado (page-name), para admitir varias tablas.
============================================================ --}}
@if ($rows->hasPages() && $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)

    {{-- ========================================================
        PAGINACIÓN - ESCRITORIO
    ======================================================== --}}
    <div
        class="
            hidden
            sm:flex

            items-center
            justify-end

            gap-1

            px-5
            py-4

            border-t
            border-[var(--theme-border)]
        "
    >

        {{-- ANTERIOR --}}
        <button
            type="button"

            wire:click="previousPage('{{ $pageName }}')"
            wire:loading.attr="disabled"

            @disabled(
                $rows->onFirstPage()
            )

            class="
                w-8
                h-8

                flex
                items-center
                justify-center

                rounded-md

                text-[var(--theme-text-muted)]

                {{
                    $rows->onFirstPage()
                        ? 'opacity-40'
                        : 'hover:bg-[var(--theme-surface-soft)]'
                }}
            "
        >
            <svg
                class="w-4 h-4"

                viewBox="0 0 24 24"

                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M15 6l-6 6 6 6"/>
            </svg>
        </button>


        {{-- ====================================================
            DATOS DE PAGINACIÓN
        ==================================================== --}}
        @php
            $current =
                $rows->currentPage();

            $last =
                $rows->lastPage();

            $window = 2;
        @endphp


        {{-- ====================================================
            PRIMERAS PÁGINAS
        ==================================================== --}}
        @for (
            $page = 1;
            $page <= min(2, $last);
            $page++
        )

            <button
                type="button"

                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
            wire:loading.attr="disabled"

                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-xs
                    font-medium

                    {{
                        $page === $current
                            ? 'bg-[var(--theme-primary)] text-white'
                            : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                    }}
                "
            >
                {{ $page }}
            </button>

        @endfor


        {{-- ====================================================
            SEPARADOR IZQUIERDO
        ==================================================== --}}
        @if ($current > 4)

            <span
                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                ...
            </span>

        @endif


        {{-- ====================================================
            VENTANA CENTRAL
        ==================================================== --}}
        @for (
            $page = max(
                3,
                $current - $window
            );
            $page <= min(
                $last - 2,
                $current + $window
            );
            $page++
        )

            <button
                type="button"

                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
            wire:loading.attr="disabled"

                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-xs
                    font-medium

                    {{
                        $page === $current
                            ? 'bg-[var(--theme-primary)] text-white'
                            : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                    }}
                "
            >
                {{ $page }}
            </button>

        @endfor


        {{-- ====================================================
            SEPARADOR DERECHO
        ==================================================== --}}
        @if ($current < $last - 3)

            <span
                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                ...
            </span>

        @endif


        {{-- ====================================================
            ÚLTIMAS PÁGINAS
        ==================================================== --}}
        @for (
            $page = max(
                $last - 1,
                3
            );
            $page <= $last;
            $page++
        )

            <button
                type="button"

                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
            wire:loading.attr="disabled"

                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-xs
                    font-medium

                    {{
                        $page === $current
                            ? 'bg-[var(--theme-primary)] text-white'
                            : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                    }}
                "
            >
                {{ $page }}
            </button>

        @endfor


        {{-- SIGUIENTE --}}
        <button
            type="button"

            wire:click="nextPage('{{ $pageName }}')"
            wire:loading.attr="disabled"

            @disabled(
                ! $rows->hasMorePages()
            )

            class="
                w-8
                h-8

                flex
                items-center
                justify-center

                rounded-md

                text-[var(--theme-text-muted)]

                {{
                    $rows->hasMorePages()
                        ? 'hover:bg-[var(--theme-surface-soft)]'
                        : 'opacity-40'
                }}
            "
        >
            <svg
                class="w-4 h-4"

                viewBox="0 0 24 24"

                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M9 6l6 6-6 6"/>
            </svg>
        </button>

    </div>


    {{-- ========================================================
        PAGINACIÓN - MÓVIL
    ======================================================== --}}
    <div
        class="
            flex
            sm:hidden

            items-center
            justify-between

            gap-3

            mt-4

            px-1
            py-3
        "
    >

        {{-- ANTERIOR --}}
        <button
            type="button"

            wire:click="previousPage('{{ $pageName }}')"
            wire:loading.attr="disabled"

            @disabled(
                $rows->onFirstPage()
            )

            class="
                h-9

                px-3

                rounded-lg

                border
                border-[var(--theme-border-strong)]

                bg-[var(--theme-surface)]

                text-xs
                font-medium

                {{
                    $rows->onFirstPage()
                        ? 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                        : 'text-[var(--theme-text-muted)]'
                }}
            "
        >
            Anterior
        </button>


        {{-- PÁGINA ACTUAL --}}
        <span
            class="
                text-xs
                text-[var(--theme-text-muted)]

                text-center
            "
        >
            Página

            <span
                class="
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                {{ $rows->currentPage() }}
            </span>

            de

            <span
                class="
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                {{ $rows->lastPage() }}
            </span>
        </span>


        {{-- SIGUIENTE --}}
        <button
            type="button"

            wire:click="nextPage('{{ $pageName }}')"
            wire:loading.attr="disabled"

            @disabled(
                ! $rows->hasMorePages()
            )

            class="
                h-9

                px-3

                rounded-lg

                border
                border-[var(--theme-border-strong)]

                bg-[var(--theme-surface)]

                text-xs
                font-medium

                {{
                    $rows->hasMorePages()
                        ? 'text-[var(--theme-text-muted)]'
                        : 'opacity-40 cursor-not-allowed text-[var(--theme-text-muted)]'
                }}
            "
        >
            Siguiente
        </button>

    </div>

@endif