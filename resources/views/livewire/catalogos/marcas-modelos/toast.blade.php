{{-- ============================================================
    TOAST
============================================================ --}}
<div
    x-show="toastOpen"
    x-cloak

    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"

    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"

    class="
        fixed
        top-5
        right-5
        z-[120]

        w-[calc(100vw-2.5rem)]
        sm:w-auto
        sm:min-w-[320px]
        sm:max-w-md

        px-4
        py-3

        rounded-xl

        border
        border-[var(--theme-border)]

        bg-[var(--theme-surface)]

        theme-shadow-xl
    "
>
    <div class="flex items-start gap-3">

        <div
            class="
                w-8
                h-8
                shrink-0

                rounded-full

                flex
                items-center
                justify-center

                bg-[var(--theme-primary-soft)]
                text-[var(--theme-primary)]
            "
        >
            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            ><use href="{{ \App\Support\Icono::url('i-42997ad') }}"></use></svg>
        </div>


        <div class="min-w-0 flex-1">

            <p
                class="
                    text-xs
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Catálogo actualizado
            </p>

            <p
                x-text="toastMessage"

                class="
                    mt-0.5

                    text-[11px]
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            ></p>

        </div>


        <button
            type="button"

            @click="
                toastOpen = false
            "

            class="
                w-7
                h-7
                shrink-0

                rounded-md

                flex
                items-center
                justify-center

                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface-soft)]
                hover:text-[var(--theme-text-strong)]

                transition-colors
            "

            aria-label="Cerrar notificación"
        >
            <svg
                class="w-3.5 h-3.5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            ><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
        </button>

    </div>
</div>