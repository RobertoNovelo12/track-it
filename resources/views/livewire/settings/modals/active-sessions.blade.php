{{-- ============================================================
    MODAL: DISPOSITIVOS ACTIVOS

    Apertura/cierre instantáneos en navegador.
    La consulta sigue siendo procesada por Livewire.
============================================================ --}}

<div
    id="active-sessions-modal"

    wire:key="active-sessions-modal"
    wire:ignore.self

    aria-hidden="true"

    class="
        hidden

        fixed
        inset-0
        z-[100]

        flex
        items-center
        justify-center

        px-4
        py-6
    "

    onclick="
        if (event.target === this) {
            window.closeActiveSessionsModalFast();
        }
    "
>

    {{-- ========================================================
        FONDO
    ======================================================== --}}
    <div
        id="active-sessions-backdrop"

        wire:ignore.self

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "

        style="
            opacity: 0;
            transition: opacity 180ms ease-out;
        "

        onclick="window.closeActiveSessionsModalFast()"
    ></div>


    {{-- ========================================================
        PANEL
    ======================================================== --}}
    <div
        id="active-sessions-panel"

        wire:ignore.self

        class="
            relative
            z-10

            w-full
            max-w-lg

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl

            overflow-hidden
        "

        style="
            opacity: 0;
            transform: translateY(10px) scale(0.975);

            transition:
                opacity 180ms ease-out,
                transform 180ms cubic-bezier(0.22, 1, 0.36, 1);

            will-change:
                opacity,
                transform;
        "

        role="dialog"
        aria-modal="true"
        aria-labelledby="active-sessions-modal-title"
    >

        {{-- ====================================================
            ENCABEZADO
        ==================================================== --}}
        <div
            class="
                px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                justify-between
                gap-4
            "
        >
            <div class="min-w-0">

                <h3
                    id="active-sessions-modal-title"

                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Dispositivos activos
                </h3>

                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Revisa las sesiones abiertas actualmente con tu cuenta.
                </p>

            </div>


            <button
                id="active-sessions-close-button"

                type="button"

                onclick="window.closeActiveSessionsModalFast()"

                class="
                    w-8
                    h-8
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

                aria-label="Cerrar"
            >
                <svg
                    class="w-4 h-4"

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>

        </div>


        {{-- ====================================================
            CONTENIDO
        ==================================================== --}}
        <div
            class="
                max-h-[60vh]
                overflow-y-auto
            "
        >

            {{-- =================================================
                CARGANDO
            ================================================= --}}
            <div
                wire:loading.flex
                wire:target="loadActiveSessions"

                class="
                    min-h-48

                    items-center
                    justify-center

                    px-5
                    py-10
                "
            >
                <svg
                    class="
                        w-6
                        h-6

                        animate-spin

                        text-[var(--theme-primary)]
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"

                        stroke="currentColor"
                        stroke-width="2"

                        opacity="0.25"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"

                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>

                <span class="sr-only">
                    Cargando dispositivos...
                </span>
            </div>


            {{-- =================================================
                RESULTADOS
            ================================================= --}}
            <div
                wire:loading.remove
                wire:target="loadActiveSessions"

                class="
                    divide-y
                    divide-[var(--theme-border)]
                "
            >

                @forelse ($activeSessions as $activeSession)

                    <div
                        wire:key="active-session-{{ $loop->index }}"

                        class="
                            px-5
                            py-4

                            flex
                            items-start
                            gap-3
                        "
                    >

                        {{-- Icono --}}
                        <div
                            class="
                                w-9
                                h-9
                                shrink-0

                                rounded-lg

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-surface-soft)]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            <svg
                                class="w-4.5 h-4.5"

                                viewBox="0 0 24 24"

                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="13"
                                    rx="2"
                                />

                                <path d="M8 21h8"/>
                                <path d="M12 17v4"/>
                            </svg>
                        </div>


                        {{-- Información --}}
                        <div class="min-w-0 flex-1">

                            <div
                                class="
                                    flex
                                    flex-wrap
                                    items-center
                                    gap-2
                                "
                            >
                                <p
                                    class="
                                        text-sm
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ $activeSession['device'] }}
                                </p>


                                @if ($activeSession['is_current'])

                                    <span
                                        class="
                                            inline-flex
                                            items-center
                                            gap-1.5

                                            px-2
                                            py-0.5

                                            rounded-full

                                            bg-[var(--theme-success-soft)]
                                            text-[var(--theme-success)]

                                            text-[10px]
                                            font-medium
                                        "
                                    >
                                        <span
                                            class="
                                                w-1.5
                                                h-1.5

                                                rounded-full

                                                bg-current
                                            "
                                        ></span>

                                        Esta sesión
                                    </span>

                                @endif
                            </div>


                            <p
                                class="
                                    mt-1

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                IP:
                                {{ $activeSession['ip_address'] }}
                            </p>


                            <div
                                class="
                                    mt-1.5

                                    flex
                                    items-center
                                    gap-2

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                @if ($activeSession['is_current'])

                                    <span
                                        class="
                                            w-1.5
                                            h-1.5
                                            shrink-0

                                            rounded-full

                                            bg-emerald-500
                                        "
                                    ></span>

                                @endif


                                <span>
                                    {{ $activeSession['last_activity'] }}
                                </span>
                            </div>

                        </div>
                    </div>

                @empty

                    <div
                        class="
                            px-5
                            py-10

                            text-center
                        "
                    >
                        <div
                            class="
                                w-10
                                h-10

                                mx-auto

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-surface-soft)]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            <svg
                                class="w-5 h-5"

                                viewBox="0 0 24 24"

                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="13"
                                    rx="2"
                                />

                                <path d="M8 21h8"/>
                                <path d="M12 17v4"/>
                            </svg>
                        </div>


                        <p
                            class="
                                mt-3

                                text-sm
                                font-medium
                                text-[var(--theme-text-strong)]
                            "
                        >
                            No hay sesiones activas
                        </p>

                        <p
                            class="
                                mt-1

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            No se encontraron sesiones para mostrar.
                        </p>
                    </div>

                @endforelse

            </div>
        </div>


        {{-- ====================================================
            ACCIONES
        ==================================================== --}}
        <div
            class="
                px-5
                py-4

                border-t
                border-[var(--theme-border)]

                flex
                items-center
                justify-end

                bg-[var(--theme-surface-soft)]
            "
        >
            <button
                type="button"

                onclick="window.closeActiveSessionsModalFast()"

                class="
                    h-9
                    px-4

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-xs
                    font-medium
                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface-soft)]

                    transition-colors
                "
            >
                Cerrar
            </button>
        </div>

    </div>

</div>


@script
<script>

(() => {
    /*
    |--------------------------------------------------------------------------
    | Abrir modal inmediatamente
    |--------------------------------------------------------------------------
    */

    window.openActiveSessionsModalFast =
        () => {

            const modal =
                document.getElementById(
                    'active-sessions-modal'
                );

            if (!modal) {
                return;
            }


            const backdrop =
                document.getElementById(
                    'active-sessions-backdrop'
                );

            const panel =
                document.getElementById(
                    'active-sessions-panel'
                );

            const reduceMotion =
                window.matchMedia?.(
                    '(prefers-reduced-motion: reduce)'
                )?.matches ?? false;


            if (
                window.__activeSessionsCloseTimer
            ) {
                clearTimeout(
                    window.__activeSessionsCloseTimer
                );

                window.__activeSessionsCloseTimer =
                    null;
            }


            /*
             * Apertura inmediata.
             */
            modal.classList.remove(
                'hidden'
            );

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style.overflow =
                'hidden';


            if (backdrop) {

                backdrop.style.opacity =
                    '0';

                backdrop.style.transitionDuration =
                    reduceMotion
                        ? '0ms'
                        : '180ms';
            }


            if (panel) {

                panel.style.opacity =
                    '0';

                panel.style.transform =
                    'translateY(10px) scale(0.975)';

                panel.style.transitionDuration =
                    reduceMotion
                        ? '0ms'
                        : '180ms';
            }


            requestAnimationFrame(() => {

                requestAnimationFrame(() => {

                    if (backdrop) {
                        backdrop.style.opacity =
                            '1';
                    }


                    if (panel) {

                        panel.style.opacity =
                            '1';

                        panel.style.transform =
                            'translateY(0) scale(1)';
                    }


                    document
                        .getElementById(
                            'active-sessions-close-button'
                        )
                        ?.focus({
                            preventScroll: true
                        });

                });

            });


            /*
             * La modal ya está visible.
             *
             * Ahora cargamos las sesiones.
             */
            $wire.loadActiveSessions();

        };


    /*
    |--------------------------------------------------------------------------
    | Cerrar modal inmediatamente
    |--------------------------------------------------------------------------
    */

    window.closeActiveSessionsModalFast =
        () => {

            const modal =
                document.getElementById(
                    'active-sessions-modal'
                );

            const backdrop =
                document.getElementById(
                    'active-sessions-backdrop'
                );

            const panel =
                document.getElementById(
                    'active-sessions-panel'
                );


            if (
                !modal ||
                modal.classList.contains(
                    'hidden'
                )
            ) {
                return;
            }


            const reduceMotion =
                window.matchMedia?.(
                    '(prefers-reduced-motion: reduce)'
                )?.matches ?? false;

            const duration =
                reduceMotion
                    ? 0
                    : 160;


            if (backdrop) {
                backdrop.style.opacity =
                    '0';
            }


            if (panel) {

                panel.style.opacity =
                    '0';

                panel.style.transform =
                    'translateY(8px) scale(0.985)';
            }


            modal.setAttribute(
                'aria-hidden',
                'true'
            );


            if (
                window.__activeSessionsCloseTimer
            ) {
                clearTimeout(
                    window.__activeSessionsCloseTimer
                );
            }


            window.__activeSessionsCloseTimer =
                window.setTimeout(
                    () => {

                        modal.classList.add(
                            'hidden'
                        );


                        document.body.style.overflow =
                            '';

                        window.__activeSessionsCloseTimer =
                            null;

                    },
                    duration
                );

        };


    /*
    |--------------------------------------------------------------------------
    | Escape
    |--------------------------------------------------------------------------
    */

    if (
        !window.__activeSessionsModalListenersInstalled
    ) {

        window.__activeSessionsModalListenersInstalled =
            true;


        window.addEventListener(
            'keydown',
            (event) => {

                if (
                    event.key !== 'Escape'
                ) {
                    return;
                }


                const modal =
                    document.getElementById(
                        'active-sessions-modal'
                    );


                if (
                    modal &&
                    !modal.classList.contains(
                        'hidden'
                    )
                ) {
                    window
                        .closeActiveSessionsModalFast();
                }

            }
        );

    }

})();

</script>
@endscript