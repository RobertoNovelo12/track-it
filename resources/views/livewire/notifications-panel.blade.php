<div>

    {{-- ============================================================
        ESTADO CLIENTE
        Livewire actualizará este valor después de la primera carga.
    ============================================================ --}}
    <span
        id="notifications-panel-loaded-state"
        data-loaded="{{ $notificationsLoaded ? '1' : '0' }}"
        class="hidden"
        aria-hidden="true"
    ></span>


    {{-- ============================================================
        PANEL FLOTANTE DE NOTIFICACIONES
    ============================================================ --}}
    <div
        id="notifications-panel-root"

        wire:ignore.self

        aria-hidden="true"

        class="
            hidden

            fixed
            inset-0
            z-[100]
        "
    >

        {{-- ========================================================
            ÁREA EXTERIOR PARA CERRAR
        ======================================================== --}}
        <div
            id="notifications-panel-backdrop"

            wire:ignore.self

            class="
                absolute
                inset-0

                bg-transparent
            "

            onclick="window.closeNotificationsPanelFast()"
        ></div>


        {{-- ========================================================
            PANEL
        ======================================================== --}}
        <div
            id="notifications-panel-card"

            wire:ignore.self

            class="
                absolute

                top-20
                right-4

                sm:right-6

                w-[calc(100vw-2rem)]
                sm:w-[390px]
                max-w-[390px]

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                theme-shadow-xl

                overflow-hidden
            "

            style="
                opacity: 0;
                transform: translateY(-8px) scale(0.985);

                transition:
                    opacity 160ms ease-out,
                    transform 180ms cubic-bezier(0.22, 1, 0.36, 1);

                will-change:
                    opacity,
                    transform;
            "

            role="dialog"
            aria-modal="false"
            aria-labelledby="notifications-panel-title"

            onclick="event.stopPropagation()"
        >

            {{-- ====================================================
                CABECERA
            ==================================================== --}}
            <div
                class="
                    px-4
                    py-3.5

                    border-b
                    border-[var(--theme-border)]

                    flex
                    items-start
                    justify-between
                    gap-3
                "
            >
                <div class="min-w-0">

                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            gap-2
                        "
                    >
                        <h2
                            id="notifications-panel-title"

                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Notificaciones
                        </h2>


                        @if ($notificationsLoaded && $unreadCount > 0)

                            <span
                                class="
                                    min-w-5
                                    h-5

                                    px-1.5

                                    inline-flex
                                    items-center
                                    justify-center

                                    rounded-full

                                    bg-[var(--theme-danger)]
                                    text-white

                                    text-[10px]
                                    font-semibold
                                "
                            >
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>

                        @endif
                    </div>


                    <p
                        class="
                            mt-0.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Actividad reciente de tu cuenta.
                    </p>

                </div>


                <div
                    class="
                        flex
                        items-center
                        gap-1
                    "
                >

                    {{-- =============================================
                        MARCAR TODAS COMO LEÍDAS
                    ============================================== --}}
                    @if ($notificationsLoaded && $unreadCount > 0)

                        <button
                            type="button"

                            wire:click="markAllAsRead"

                            wire:loading.attr="disabled"
                            wire:target="markAllAsRead"

                            class="
                                relative

                                h-8
                                px-2.5
                                shrink-0

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface)]

                                text-[10px]
                                font-medium
                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]
                                hover:text-[var(--theme-primary)]

                                disabled:opacity-60
                                disabled:cursor-not-allowed

                                transition-colors
                            "
                        >
                            <span
                                wire:loading.class="invisible"
                                wire:target="markAllAsRead"

                                class="
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-1.5
                                "
                            >
                                <svg
                                    class="w-3.5 h-3.5"

                                    viewBox="0 0 24 24"

                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M5 12l4 4L19 6"/>
                                </svg>

                                Marcar todas
                            </span>


                            <span
                                wire:loading.flex
                                wire:target="markAllAsRead"

                                class="
                                    absolute
                                    inset-0

                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4 animate-spin"

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
                                    Marcando notificaciones como leídas...
                                </span>
                            </span>
                        </button>

                    @endif


                    {{-- =============================================
                        ELIMINAR TODAS
                    ============================================== --}}
                    @if ($notificationsLoaded && count($notifications) > 0)

                        <button
                            type="button"

                            wire:click="deleteAllNotifications"
                            wire:confirm="¿Eliminar todas las notificaciones? Esta acción no se puede deshacer."

                            wire:loading.attr="disabled"
                            wire:target="deleteAllNotifications"

                            class="
                                relative

                                w-8
                                h-8
                                shrink-0

                                rounded-md

                                flex
                                items-center
                                justify-center

                                text-[var(--theme-text-muted)]

                                hover:bg-[var(--theme-danger-soft)]
                                hover:text-[var(--theme-danger)]

                                disabled:opacity-60
                                disabled:cursor-not-allowed

                                transition-colors
                            "

                            title="Eliminar todas las notificaciones"
                            aria-label="Eliminar todas las notificaciones"
                        >
                            <span
                                wire:loading.class="invisible"
                                wire:target="deleteAllNotifications"

                                class="
                                    flex
                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"

                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M4 7h16"/>
                                    <path d="M9 7V4h6v3"/>
                                    <path d="M7 7l1 13h8l1-13"/>
                                    <path d="M10 11v5"/>
                                    <path d="M14 11v5"/>
                                </svg>
                            </span>


                            <span
                                wire:loading.flex
                                wire:target="deleteAllNotifications"

                                class="
                                    absolute
                                    inset-0

                                    items-center
                                    justify-center
                                "
                            >
                                <svg
                                    class="w-4 h-4 animate-spin"

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
                                    Eliminando notificaciones...
                                </span>
                            </span>
                        </button>

                    @endif


                    {{-- =============================================
                        ACTUALIZAR
                    ============================================== --}}
                    <button
                        type="button"

                        wire:click="refreshNotifications"

                        wire:loading.attr="disabled"
                        wire:target="refreshNotifications"

                        class="
                            relative

                            w-8
                            h-8
                            shrink-0

                            rounded-md

                            flex
                            items-center
                            justify-center

                            text-[var(--theme-text-muted)]

                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-primary)]

                            disabled:opacity-60
                            disabled:cursor-not-allowed

                            transition-colors
                        "

                        title="Actualizar notificaciones"
                        aria-label="Actualizar notificaciones"
                    >
                        {{-- Estado normal --}}
                        <span
                            wire:loading.class="invisible"
                            wire:target="refreshNotifications"

                            class="
                                flex
                                items-center
                                justify-center
                            "
                        >
                            <svg
                                class="w-4 h-4"

                                viewBox="0 0 24 24"

                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <path d="M20 6v5h-5"/>
                                <path d="M4 18v-5h5"/>
                                <path d="M6.1 9A7 7 0 0118.7 6.7L20 11"/>
                                <path d="M17.9 15A7 7 0 015.3 17.3L4 13"/>
                            </svg>
                        </span>


                        {{-- Estado cargando --}}
                        <span
                            wire:loading.flex
                            wire:target="refreshNotifications"

                            class="
                                absolute
                                inset-0

                                items-center
                                justify-center
                            "
                        >
                            <svg
                                class="w-4 h-4 animate-spin"

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
                                Actualizando notificaciones...
                            </span>
                        </span>
                    </button>


                    {{-- =============================================
                        CERRAR
                    ============================================== --}}
                    <button
                        id="notifications-panel-close-button"

                        type="button"

                        onclick="window.closeNotificationsPanelFast()"

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

                        aria-label="Cerrar notificaciones"
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
            </div>


            {{-- ====================================================
                CONTENIDO
            ==================================================== --}}
            <div
                class="
                    max-h-[min(440px,calc(100vh-10rem))]
                    overflow-y-auto
                "
            >

                {{-- =================================================
                    TODAVÍA NO SE HAN CARGADO
                ================================================= --}}
                @if (! $notificationsLoaded)

                    <div
                        class="
                            min-h-48

                            px-5
                            py-10

                            flex
                            items-center
                            justify-center
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
                            Cargando notificaciones...
                        </span>
                    </div>


                {{-- =================================================
                    SIN NOTIFICACIONES
                ================================================= --}}
                @elseif (count($notifications) === 0)

                    <div
                        class="
                            min-h-52

                            px-6
                            py-10

                            flex
                            flex-col
                            items-center
                            justify-center

                            text-center
                        "
                    >
                        <div
                            class="
                                w-11
                                h-11

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
                                <path
                                    d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                                />

                                <path d="M10 21h4"/>
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
                            No hay nada nuevo
                        </p>


                        <p
                            class="
                                mt-1

                                max-w-64

                                text-xs
                                leading-relaxed
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cuando tengas una nueva notificación aparecerá aquí.
                        </p>
                    </div>


                {{-- =================================================
                    LISTADO
                ================================================= --}}
                @else

                    <div
                        class="
                            divide-y
                            divide-[var(--theme-border)]
                        "
                    >
                        @foreach ($notifications as $notification)

                            <div
                                wire:key="notification-{{ $notification['id'] }}"

                                class="
                                    relative

                                    flex
                                    items-stretch

                                    {{ ! $notification['leida']
                                        ? 'bg-[var(--theme-primary-soft-subtle)]'
                                        : 'bg-[var(--theme-surface)]'
                                    }}
                                "
                            >

                                {{-- =================================
                                    CONTENIDO / ACCIÓN PRINCIPAL
                                ================================= --}}
                                <button
                                    type="button"

                                    wire:click="openNotification({{ $notification['id'] }})"

                                    wire:loading.attr="disabled"
                                    wire:target="openNotification({{ $notification['id'] }})"

                                    class="
                                        group

                                        min-w-0
                                        flex-1

                                        px-4
                                        py-3.5

                                        flex
                                        items-start
                                        gap-3

                                        text-left

                                        hover:bg-[var(--theme-surface-soft)]

                                        disabled:cursor-wait

                                        transition-colors
                                    "

                                    @if (filled($notification['url_destino']))
                                        title="Abrir detalle"
                                    @else
                                        title="Marcar como leída"
                                    @endif
                                >

                                    {{-- =============================
                                        ICONO
                                    ============================== --}}
                                    <div
                                        class="
                                            w-9
                                            h-9
                                            shrink-0

                                            rounded-lg

                                            flex
                                            items-center
                                            justify-center

                                            {{ strtoupper($notification['tipo']) === 'SEGURIDAD'
                                                ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                                : 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]'
                                            }}
                                        "
                                    >
                                        @if (strtoupper($notification['tipo']) === 'SEGURIDAD')

                                            <svg
                                                class="w-4.5 h-4.5"

                                                viewBox="0 0 24 24"

                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                            >
                                                <path
                                                    d="M12 3l7 3v5c0 4.8-2.8 8-7 10-4.2-2-7-5.2-7-10V6l7-3z"
                                                />

                                                <path
                                                    d="M9.5 12l1.7 1.7L15 10"
                                                />
                                            </svg>

                                        @else

                                            <svg
                                                class="w-4.5 h-4.5"

                                                viewBox="0 0 24 24"

                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                            >
                                                <path
                                                    d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                                                />

                                                <path d="M10 21h4"/>
                                            </svg>

                                        @endif
                                    </div>


                                    {{-- =============================
                                        INFORMACIÓN
                                    ============================== --}}
                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="
                                                flex
                                                items-start
                                                gap-2
                                            "
                                        >
                                            <p
                                                class="
                                                    min-w-0
                                                    flex-1

                                                    text-xs
                                                    font-semibold
                                                    leading-relaxed
                                                    text-[var(--theme-text-strong)]
                                                "
                                            >
                                                {{ $notification['titulo'] }}
                                            </p>


                                            @if (! $notification['leida'])

                                                <span
                                                    class="
                                                        mt-1.5

                                                        w-2
                                                        h-2
                                                        shrink-0

                                                        rounded-full

                                                        bg-[var(--theme-danger)]
                                                    "
                                                    title="No leída"
                                                ></span>

                                            @endif
                                        </div>


                                        <p
                                            class="
                                                mt-1

                                                text-[11px]
                                                leading-relaxed
                                                text-[var(--theme-text-muted)]
                                            "
                                        >
                                            {{ $notification['mensaje'] }}
                                        </p>


                                        <div
                                            class="
                                                mt-2

                                                flex
                                                flex-wrap
                                                items-center
                                                gap-2
                                            "
                                        >
                                            <span
                                                class="
                                                    text-[10px]
                                                    text-[var(--theme-text-muted)]
                                                "
                                            >
                                                {{ $notification['fecha'] }}
                                            </span>


                                            @if (strtoupper($notification['tipo']) === 'SEGURIDAD')

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center

                                                        px-1.5
                                                        py-0.5

                                                        rounded-full

                                                        bg-[var(--theme-primary-soft)]
                                                        text-[var(--theme-primary)]

                                                        text-[9px]
                                                        font-medium
                                                    "
                                                >
                                                    Seguridad
                                                </span>

                                            @endif


                                            @if ($notification['leida'])

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        gap-1

                                                        text-[9px]
                                                        font-medium
                                                        text-[var(--theme-text-muted)]
                                                    "
                                                >
                                                    <svg
                                                        class="w-3 h-3"

                                                        viewBox="0 0 24 24"

                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path d="M5 12l4 4L19 6"/>
                                                    </svg>

                                                    Leída
                                                </span>

                                            @endif


                                            @if (filled($notification['url_destino']))

                                                <span
                                                    class="
                                                        ml-auto

                                                        inline-flex
                                                        items-center
                                                        gap-1

                                                        text-[9px]
                                                        font-medium
                                                        text-[var(--theme-primary)]

                                                        opacity-80
                                                        group-hover:opacity-100

                                                        transition-opacity
                                                    "
                                                >
                                                    Ver detalle

                                                    <svg
                                                        class="w-3 h-3"

                                                        viewBox="0 0 24 24"

                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path d="M9 6l6 6-6 6"/>
                                                    </svg>
                                                </span>

                                            @endif
                                        </div>

                                    </div>
                                </button>


                                {{-- =================================
                                    ELIMINAR INDIVIDUAL
                                ================================= --}}
                                <div
                                    class="
                                        py-3
                                        pr-2.5

                                        flex
                                        items-start
                                    "
                                >
                                    <button
                                        type="button"

                                        wire:click="deleteNotification({{ $notification['id'] }})"

                                        wire:loading.attr="disabled"
                                        wire:target="deleteNotification({{ $notification['id'] }})"

                                        class="
                                            relative

                                            w-7
                                            h-7
                                            shrink-0

                                            rounded-md

                                            flex
                                            items-center
                                            justify-center

                                            text-[var(--theme-text-muted)]

                                            hover:bg-[var(--theme-danger-soft)]
                                            hover:text-[var(--theme-danger)]

                                            disabled:opacity-60
                                            disabled:cursor-not-allowed

                                            transition-colors
                                        "

                                        title="Eliminar notificación"
                                        aria-label="Eliminar notificación"
                                    >
                                        <span
                                            wire:loading.class="invisible"
                                            wire:target="deleteNotification({{ $notification['id'] }})"

                                            class="
                                                flex
                                                items-center
                                                justify-center
                                            "
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"

                                                viewBox="0 0 24 24"

                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <path d="M4 7h16"/>
                                                <path d="M9 7V4h6v3"/>
                                                <path d="M7 7l1 13h8l1-13"/>
                                                <path d="M10 11v5"/>
                                                <path d="M14 11v5"/>
                                            </svg>
                                        </span>


                                        <span
                                            wire:loading.flex
                                            wire:target="deleteNotification({{ $notification['id'] }})"

                                            class="
                                                absolute
                                                inset-0

                                                items-center
                                                justify-center
                                            "
                                        >
                                            <svg
                                                class="w-3.5 h-3.5 animate-spin"

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
                                                Eliminando notificación...
                                            </span>
                                        </span>
                                    </button>
                                </div>

                            </div>

                        @endforeach
                    </div>

                @endif

            </div>


            {{-- ====================================================
                PIE
            ==================================================== --}}
            @if ($notificationsLoaded && count($notifications) > 0)

                <div
                    class="
                        px-4
                        py-2.5

                        border-t
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface-soft)]
                    "
                >
                    <p
                        class="
                            text-center

                            text-[10px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Mostrando las notificaciones más recientes.
                    </p>
                </div>

            @endif

        </div>

    </div>


    @script
    <script>

    (() => {

        /*
        |--------------------------------------------------------------------------
        | Comprobar si ya fueron cargadas
        |--------------------------------------------------------------------------
        */

        const notificationsAlreadyLoaded =
            () => {

                const state =
                    document.getElementById(
                        'notifications-panel-loaded-state'
                    );

                return state?.dataset.loaded === '1';

            };


        /*
        |--------------------------------------------------------------------------
        | Abrir inmediatamente
        |--------------------------------------------------------------------------
        */

        window.openNotificationsPanelFast =
            () => {

                const root =
                    document.getElementById(
                        'notifications-panel-root'
                    );

                const card =
                    document.getElementById(
                        'notifications-panel-card'
                    );


                if (!root || !card) {
                    return;
                }


                if (
                    window.__notificationsPanelCloseTimer
                ) {
                    clearTimeout(
                        window.__notificationsPanelCloseTimer
                    );

                    window.__notificationsPanelCloseTimer =
                        null;
                }


                const reduceMotion =
                    window.matchMedia?.(
                        '(prefers-reduced-motion: reduce)'
                    )?.matches ?? false;


                root.classList.remove(
                    'hidden'
                );

                root.setAttribute(
                    'aria-hidden',
                    'false'
                );


                card.style.opacity =
                    '0';

                card.style.transform =
                    'translateY(-8px) scale(0.985)';

                card.style.transitionDuration =
                    reduceMotion
                        ? '0ms'
                        : '180ms';


                requestAnimationFrame(() => {

                    requestAnimationFrame(() => {

                        card.style.opacity =
                            '1';

                        card.style.transform =
                            'translateY(0) scale(1)';

                    });

                });


                if (
                    !notificationsAlreadyLoaded()
                ) {
                    $wire.loadNotifications();
                }

            };


        /*
        |--------------------------------------------------------------------------
        | Cerrar inmediatamente
        |--------------------------------------------------------------------------
        */

        window.closeNotificationsPanelFast =
            () => {

                const root =
                    document.getElementById(
                        'notifications-panel-root'
                    );

                const card =
                    document.getElementById(
                        'notifications-panel-card'
                    );


                if (
                    !root ||
                    !card ||
                    root.classList.contains('hidden')
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
                        : 140;


                card.style.opacity =
                    '0';

                card.style.transform =
                    'translateY(-6px) scale(0.99)';


                root.setAttribute(
                    'aria-hidden',
                    'true'
                );


                if (
                    window.__notificationsPanelCloseTimer
                ) {
                    clearTimeout(
                        window.__notificationsPanelCloseTimer
                    );
                }


                window.__notificationsPanelCloseTimer =
                    window.setTimeout(
                        () => {

                            root.classList.add(
                                'hidden'
                            );

                            window.__notificationsPanelCloseTimer =
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
            !window.__notificationsPanelListenersInstalled
        ) {

            window.__notificationsPanelListenersInstalled =
                true;


            window.addEventListener(
                'keydown',
                (event) => {

                    if (
                        event.key !== 'Escape'
                    ) {
                        return;
                    }


                    const root =
                        document.getElementById(
                            'notifications-panel-root'
                        );


                    if (
                        root &&
                        !root.classList.contains(
                            'hidden'
                        )
                    ) {
                        window
                            .closeNotificationsPanelFast();
                    }

                }
            );

        }

    })();

    </script>
    @endscript

</div>