{{-- ============================================================
    TOAST DEL CONSTRUCTOR
============================================================ --}}
<div
    x-show="
        toastVisible
    "

    x-cloak

    x-transition:enter="
        transition
        ease-out
        duration-200
    "

    x-transition:enter-start="
        opacity-0
        translate-y-2
    "

    x-transition:enter-end="
        opacity-100
        translate-y-0
    "

    x-transition:leave="
        transition
        ease-in
        duration-150
    "

    x-transition:leave-start="
        opacity-100
        translate-y-0
    "

    x-transition:leave-end="
        opacity-0
        translate-y-2
    "

    class="
        fixed
        top-5
        right-5
        z-[170]

        w-[calc(100vw-2.5rem)]

        sm:w-auto
        sm:min-w-[340px]
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
    <div
        class="
            flex
            items-start
            gap-3
        "
    >

        {{-- ========================================================
            ICONO
        ======================================================== --}}
        <div
            :class="{
                'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]':
                    toastType === 'success'
                    || toastType === 'info',

                'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]':
                    toastType === 'warning',

                'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]':
                    toastType === 'error'
            }"

            class="
                w-9
                h-9

                shrink-0

                rounded-full

                flex
                items-center
                justify-center
            "
        >

            {{-- ÉXITO / INFO --}}
            <svg
                x-show="
                    toastType === 'success'
                    || toastType === 'info'
                "

                x-cloak

                class="
                    w-4
                    h-4
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M5 12l4 4L19 6"/>
            </svg>


            {{-- WARNING --}}
            <svg
                x-show="
                    toastType === 'warning'
                "

                x-cloak

                class="
                    w-4
                    h-4
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
                <path d="M10.3 4.4L2.8 17.3a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.4a2 2 0 00-3.4 0z"/>
            </svg>


            {{-- ERROR --}}
            <svg
                x-show="
                    toastType === 'error'
                "

                x-cloak

                class="
                    w-4
                    h-4
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                />

                <path d="M9 9l6 6"/>
                <path d="M15 9l-6 6"/>
            </svg>

        </div>


        {{-- ========================================================
            TEXTO
        ======================================================== --}}
        <div
            class="
                min-w-0
                flex-1
            "
        >
            <p
                x-text="
                    toastType === 'error'
                        ? 'No se pudo completar la acción'
                        : (
                            toastType === 'warning'
                                ? 'Revisa la configuración'
                                : 'Configuración actualizada'
                        )
                "

                class="
                    text-xs
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            ></p>


            <p
                x-text="
                    toastMessage
                "

                class="
                    mt-0.5

                    text-[11px]
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            ></p>
        </div>


        {{-- ========================================================
            CERRAR
        ======================================================== --}}
        <button
            type="button"

            @click="
                toastVisible = false
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
                class="
                    w-3.5
                    h-3.5
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
            >
                <path d="M6 6l12 12"/>
                <path d="M18 6L6 18"/>
            </svg>
        </button>

    </div>
</div>