{{-- ============================================================
    PAGINACIÓN - MARCAS
============================================================ --}}
<div
    x-show="
        lastMarcaPage > 1
    "

    class="
        flex
        items-center
        justify-between

        sm:justify-end

        gap-2

        px-1
        sm:px-5
        py-4

        md:border-t
        md:border-[var(--theme-border)]
    "
>
    <button
        type="button"

        @click="
            previousMarcaPage()
        "

        :disabled="
            currentMarcaPage <= 1
        "

        class="
            h-9
            px-3

            sm:w-8
            sm:px-0

            flex
            items-center
            justify-center

            rounded-md

            border
            border-[var(--theme-border-strong)]

            sm:border-0

            bg-[var(--theme-surface)]

            text-xs
            text-[var(--theme-text-muted)]

            disabled:opacity-40
            disabled:cursor-not-allowed

            hover:bg-[var(--theme-surface-soft)]
        "
    >
        <svg
            class="
                hidden
                sm:block

                w-4
                h-4
            "

            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
        ><use href="{{ \App\Support\Icono::url('i-d889af1') }}"></use></svg>

        <span class="sm:hidden">
            Anterior
        </span>
    </button>


    <div
        class="
            hidden
            sm:flex

            items-center

            gap-1
        "
    >
        <template
            x-for="
                item
                in marcaPaginationItems
            "

            :key="
                item.key
            "
        >
            <button
                type="button"

                @click="
                    if (item.page !== null) {
                        goMarcaPage(
                            item.page
                        );
                    }
                "

                :disabled="
                    item.page === null
                "

                class="
                    w-8
                    h-8

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-xs
                    font-medium

                    transition-colors
                "

                :class="
                    item.page === null
                        ? 'text-[var(--theme-text-muted)] cursor-default'
                        : (
                            item.page === currentMarcaPage
                                ? 'bg-[var(--theme-primary)] text-white'
                                : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                        )
                "

                x-text="
                    item.page === null
                        ? '...'
                        : item.page
                "
            ></button>
        </template>
    </div>


    <span
        class="
            sm:hidden

            text-xs
            text-[var(--theme-text-muted)]
        "
    >
        Página

        <span
            class="
                font-medium
                text-[var(--theme-text)]
            "

            x-text="
                currentMarcaPage
            "
        ></span>

        de

        <span
            class="
                font-medium
                text-[var(--theme-text)]
            "

            x-text="
                lastMarcaPage
            "
        ></span>
    </span>


    <button
        type="button"

        @click="
            nextMarcaPage()
        "

        :disabled="
            currentMarcaPage
            >= lastMarcaPage
        "

        class="
            h-9
            px-3

            sm:w-8
            sm:px-0

            flex
            items-center
            justify-center

            rounded-md

            border
            border-[var(--theme-border-strong)]

            sm:border-0

            bg-[var(--theme-surface)]

            text-xs
            text-[var(--theme-text-muted)]

            disabled:opacity-40
            disabled:cursor-not-allowed

            hover:bg-[var(--theme-surface-soft)]
        "
    >
        <svg
            class="
                hidden
                sm:block

                w-4
                h-4
            "

            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
        ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>

        <span class="sm:hidden">
            Siguiente
        </span>
    </button>

</div>