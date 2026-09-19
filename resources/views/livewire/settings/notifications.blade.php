<div class="space-y-5">

    {{-- ============================================================
        ACTIVIDAD OPERATIVA
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M4 7h15"/>
                    <path d="M16 4l3 3-3 3"/>
                    <path d="M20 17H5"/>
                    <path d="M8 14l-3 3 3 3"/>
                </svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Actividad operativa
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Elige qué cambios relacionados con la operación quieres recibir en tu centro de notificaciones.
                </p>

            </div>
        </div>


        {{-- ========================================================
            ASIGNACIONES Y MOVIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignaciones y movimientos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos cuando un equipo sea asignado, transferido, devuelto o cambie de responsable.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarAsignacionesMovimientos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            MANTENIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mantenimientos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos sobre mantenimientos programados, próximos, vencidos o completados.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarMantenimientos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            CAMBIOS EN EQUIPOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Cambios en equipos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Avísame cuando cambie el estado de un equipo o se registre una modificación importante.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarCambiosEquipos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>
    </section>


    {{-- ============================================================
        ADMINISTRACIÓN
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                    <circle cx="17" cy="9" r="2"/>
                    <path d="M15.5 14.5c3.2.3 5.5 2.2 5.5 5.5"/>
                </svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Administración
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Controla las notificaciones relacionadas con procesos administrativos del sistema.
                </p>

            </div>
        </div>


        {{-- ========================================================
            USUARIOS Y ACCESOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Usuarios y accesos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos sobre altas de usuarios, solicitudes, permisos y otros cambios administrativos.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarUsuariosAccesos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            REPORTES
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Reportes
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Avísame cuando un reporte o una exportación solicitada esté disponible.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarReportes"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>
    </section>


    {{-- ============================================================
        CENTRO DE NOTIFICACIONES
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Centro de notificaciones
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Configura cómo quieres visualizar y conservar tus notificaciones dentro de Track-It.
                </p>

            </div>
        </div>


        {{-- ========================================================
            SOLO NO LEÍDAS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mostrar solo notificaciones no leídas
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Oculta del panel las notificaciones que ya hayas revisado.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="soloNoLeidas"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            MANTENER HISTORIAL
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mantener historial
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Conserva las notificaciones leídas hasta que decidas eliminarlas manualmente.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="mantenerHistorial"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        {{-- ========================================================
            PIE / GUARDAR
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-t
                border-[var(--theme-border)]

                bg-[var(--theme-surface-soft)]

                flex
                justify-end
            "
        >
            <button
                type="button"

                wire:click="saveNotificationPreferences"

                wire:loading.attr="disabled"
                wire:target="saveNotificationPreferences"

                class="
                    relative

                    h-9
                    w-full
                    sm:w-auto
                    sm:min-w-36

                    px-4

                    rounded-md

                    bg-[var(--theme-primary)]
                    text-white

                    text-xs
                    font-medium

                    hover:bg-[var(--theme-primary-hover)]

                    disabled:opacity-60
                    disabled:cursor-not-allowed

                    transition-colors
                "
            >
                <span
                    wire:loading.class="invisible"
                    wire:target="saveNotificationPreferences"
                >
                    Guardar cambios
                </span>


                <span
                    wire:loading.flex
                    wire:target="saveNotificationPreferences"

                    class="
                        absolute
                        inset-0

                        items-center
                        justify-center
                    "
                >
                    <svg
                        class="
                            w-4
                            h-4

                            animate-spin
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
                        Guardando preferencias...
                    </span>
                </span>
            </button>
        </div>
    </section>

</div>