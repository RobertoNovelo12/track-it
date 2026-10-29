<div>

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <section
        class="
            flex
            flex-col
            xl:flex-row
            xl:items-end
            md:flex-row md:items-center md:justify-between
            gap-5
            mb-6
        "
    >


        {{-- ========================================================
            ACCIONES SUPERIORES («Volver» a la izquierda, en escritorio)
        ======================================================== --}}
        @include('layouts.partials.boton-volver', ['destino' => url('/')])

        {{-- Nuevo movimiento (un solo botón) --}}
        <div class="w-full md:w-auto">
            <span class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white opacity-80 md:w-auto">
                Nuevo movimiento
            </span>
        </div>
    </section>


    {{-- ============================================================
        MÉTRICAS PRINCIPALES
    ============================================================ --}}
    <section
        class="
            grid
            grid-cols-1
            md:grid-cols-3
            gap-3
            sm:gap-4
            mb-5
        "
    >

        {{-- ========================================================
            EQUIPOS ASIGNADOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-af649ce') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos Asignados
                </p>

                {{-- Solo el dato es desconocido --}}
                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

                <p
                    class="
                        mt-1
                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos en uso
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>
        </div>


        {{-- ========================================================
            EQUIPOS NO ASIGNADOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-37cc6c3') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos No Asignados
                </p>

                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

                <p
                    class="
                        mt-1
                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos disponibles
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>
        </div>


        {{-- ========================================================
            MOVIMIENTOS
        ======================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                px-4
                sm:px-5
                py-4
                flex
                items-center
                gap-4
            "
        >
            <div
                class="
                    shrink-0
                    w-14
                    h-14
                    rounded-xl
                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]
                    flex
                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Movimientos recientes
                </p>

                <div class="mt-1 animate-pulse">
                    <div
                        class="
                            h-6
                            w-14
                            rounded
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>
                </div>

                <p
                    class="
                        mt-1
                        text-[11px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Últimos 7 días
                </p>
            </div>

            <svg
                class="
                    shrink-0
                    w-5
                    h-5
                    text-[var(--theme-text-muted)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-352d1b3') }}"></use></svg>
        </div>

    </section>


    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[1.05fr_1fr]
            gap-4
        "
    >

        {{-- ========================================================
            EQUIPOS ASIGNADOS POR HOTEL
        ======================================================== --}}
        <div class="flex min-w-0 flex-col gap-4">
        <section
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                p-4
                sm:p-5
            "
        >

            {{-- Cabecera conocida --}}
            <div
                class="
                    flex
                    flex-col
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
                    gap-3
                "
            >
                <div
                    class="
                        flex
                        items-start
                        gap-3
                        min-w-0
                    "
                >
                    <svg
                        class="
                            shrink-0
                            w-5
                            h-5
                            mt-0.5
                            text-[var(--theme-text-strong)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    ><use href="{{ \App\Support\Icono::url('i-1f0e5a3') }}"></use></svg>

                    <div class="min-w-0">
                        <h2
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Equipos asignados por hotel
                        </h2>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Distribución de equipos en uso por hotel de la organización.
                        </p>
                    </div>
                </div>


                {{-- Sabemos qué control existe, solo aún no hay datos --}}
                <button
                    type="button"
                    disabled
                    class="
                        shrink-0
                        h-9
                        w-full
                        sm:w-auto
                        px-3
                        inline-flex
                        items-center
                        justify-between
                        gap-4
                        border
                        border-[var(--theme-border-strong)]
                        rounded-lg
                        bg-[var(--theme-surface)]
                        text-xs
                        text-[var(--theme-text-muted)]
                        disabled:cursor-default
                        disabled:opacity-100
                    "
                >
                    Todos los hoteles

                    <svg
                        class="w-3.5 h-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
                </button>
            </div>


            {{-- ====================================================
                DATOS DEL GRÁFICO
            ==================================================== --}}
            <div
                class="
                    mt-7
                    flex
                    flex-col
                    lg:flex-row
                    items-center
                    justify-center
                    gap-8
                "
            >

                {{-- Donut desconocido --}}
                <div
                    class="
                        relative
                        shrink-0
                        w-56
                        h-56
                        sm:w-64
                        sm:h-64
                        animate-pulse
                    "
                >
                    <div
                        class="
                            absolute
                            inset-0
                            rounded-full
                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                    <div
                        class="
                            absolute
                            inset-[25%]
                            rounded-full
                            bg-[var(--theme-surface)]
                            flex
                            flex-col
                            items-center
                            justify-center
                            gap-2
                        "
                    >
                        <div
                            class="
                                h-7
                                w-16
                                rounded
                                bg-[var(--theme-surface-soft)]
                            "
                        ></div>

                        <span
                            class="
                                text-[10px]
                                sm:text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Equipos asignados
                        </span>
                    </div>
                </div>


                {{-- Leyenda --}}
                <div
                    class="
                        w-full
                        max-w-xs
                        space-y-2.5
                        animate-pulse
                    "
                >
                    @for ($i = 0; $i < 5; $i++)
                        <div
                            class="
                                flex
                                items-center
                                gap-2.5
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-2.5
                                    h-2.5
                                    rounded-full
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></span>

                            <div
                                class="
                                    h-2.5
                                    flex-1
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                            <div
                                class="
                                    h-2.5
                                    w-6
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>
                        </div>
                    @endfor
                </div>

            </div>
        </section>

        {{-- Disponibles para asignar --}}
        <section class="flex flex-1 flex-col rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5" aria-hidden="true">
            <div class="h-4 w-44 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
            <div class="mt-2 h-3 w-56 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
            <div class="mt-5 space-y-4">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 space-y-2">
                            <div class="h-3 w-2/3 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
                            <div class="h-2.5 w-1/2 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
                        </div>
                        <div class="h-8 w-20 animate-pulse rounded-md bg-[var(--theme-surface-soft)]"></div>
                    </div>
                @endfor
            </div>
        </section>
        </div>


        {{-- ========================================================
            RESUMEN DE ACTIVIDAD
        ======================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]
                border
                border-[var(--theme-border)]
                rounded-xl
                overflow-hidden
            "
        >

            {{-- Cabecera --}}
            <div
                class="
                    px-4
                    sm:px-5
                    pt-4
                    sm:pt-5
                "
            >
                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-4
                    "
                >
                    <div
                        class="
                            flex
                            items-start
                            gap-3
                            min-w-0
                        "
                    >
                        <svg
                            class="
                                shrink-0
                                w-5
                                h-5
                                mt-0.5
                                text-[var(--theme-text-strong)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        ><use href="{{ \App\Support\Icono::url('i-e9c3e4a') }}"></use></svg>

                        <div class="min-w-0">
                            <h2
                                class="
                                    text-sm
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Resumen de actividad
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Información general de movimientos y asignaciones.
                            </p>
                        </div>
                    </div>


                    <span
                        class="
                            hidden
                            sm:inline-flex
                            items-center
                            gap-1
                            text-xs
                            font-medium
                            text-[var(--theme-primary)]
                        "
                    >
                        Ver reporte completo

                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        ><use href="{{ \App\Support\Icono::url('i-ab28778') }}"></use></svg>
                    </span>
                </div>


                {{-- ====================================================
                    MINI MÉTRICAS
                ==================================================== --}}
                <div
                    class="
                        grid
                        grid-cols-2
                        sm:grid-cols-4
                        gap-2
                        mt-5
                        mb-5
                    "
                >

                    {{-- Mini métricas neutras, igual que la vista cargada --}}
                    {{-- Asignaciones hoy --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-[var(--theme-border)]
                            bg-[var(--theme-surface-soft)]
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]
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
                                ><use href="{{ \App\Support\Icono::url('i-16d6c5f') }}"></use></svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-border)]
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Asignaciones hoy
                        </p>
                    </div>


                    {{-- Reasignaciones hoy --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-[var(--theme-border)]
                            bg-[var(--theme-surface-soft)]
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]
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
                                ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-border)]
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Reasignaciones hoy
                        </p>
                    </div>


                    {{-- Asignaciones este mes --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-[var(--theme-border)]
                            bg-[var(--theme-surface-soft)]
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]
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
                                ><use href="{{ \App\Support\Icono::url('i-172bef0') }}"></use></svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-border)]
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Asignaciones este mes
                        </p>
                    </div>


                    {{-- Reasignaciones este mes --}}
                    <div
                        class="
                            rounded-xl
                            border
                            border-[var(--theme-border)]
                            bg-[var(--theme-surface-soft)]
                            p-3
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <span
                                class="
                                    shrink-0
                                    w-7
                                    h-7
                                    rounded-lg
                                    bg-[var(--theme-surface)]
                                    text-[var(--theme-text-muted)]
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
                                ><use href="{{ \App\Support\Icono::url('i-1f0e5a3') }}"></use></svg>
                            </span>

                            <div class="animate-pulse">
                                <div
                                    class="
                                        h-5
                                        w-8
                                        rounded
                                        bg-[var(--theme-border)]
                                    "
                                ></div>
                            </div>
                        </div>

                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-snug
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Reasignaciones este mes
                        </p>
                    </div>

                </div>
            </div>


            {{-- ====================================================
                ACTIVIDAD RECIENTE
            ==================================================== --}}
            <div>

                {{-- Título conocido --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3
                        border-y
                        border-[var(--theme-border)]
                        flex
                        items-center
                        justify-between
                        gap-3
                    "
                >
                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <svg
                            class="
                                w-4
                                h-4
                                text-[var(--theme-text-strong)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        ><use href="{{ \App\Support\Icono::url('i-a5f1f42') }}"></use></svg>

                        <h3
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Actividad reciente
                        </h3>
                    </div>

                    <span
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-primary)]
                        "
                    >
                        Últimos movimientos
                    </span>
                </div>


                {{-- Encabezado escritorio conocido --}}
                <div
                    class="
                        hidden
                        sm:grid
                        grid-cols-[80px_1fr_90px]
                        gap-3
                        px-5
                        py-2
                        bg-[var(--theme-surface-soft)]
                        border-b
                        border-[var(--theme-border)]
                        text-[9px]
                        font-medium
                        uppercase
                        tracking-wide
                        text-[var(--theme-text-muted)]
                    "
                >
                    <div>
                        Acción
                    </div>

                    <div>
                        Detalle
                    </div>

                    <div class="text-right">
                        Hora
                    </div>
                </div>


                {{-- ====================================================
                    FILAS DESCONOCIDAS
                ==================================================== --}}
                <div class="animate-pulse">

                    @for ($i = 0; $i < 5; $i++)
                        <div
                            class="
                                grid
                                grid-cols-[36px_1fr_auto]
                                sm:grid-cols-[80px_1fr_90px]
                                items-center
                                gap-3
                                px-4
                                sm:px-5
                                py-2.5
                                border-b
                                border-[var(--theme-border)]
                                last:border-b-0
                            "
                        >

                            {{-- Acción --}}
                            <div>
                                <div
                                    class="
                                        w-8
                                        h-7
                                        rounded-lg
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </div>


                            {{-- Detalle --}}
                            <div class="min-w-0">
                                <div
                                    class="
                                        h-2.5
                                        w-full
                                        max-w-56
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>

                                <div
                                    class="
                                        mt-2
                                        h-2
                                        w-24
                                        rounded
                                        bg-[var(--theme-surface-soft)]
                                    "
                                ></div>
                            </div>


                            {{-- Hora --}}
                            <div
                                class="
                                    justify-self-end
                                    h-2.5
                                    w-12
                                    rounded
                                    bg-[var(--theme-surface-soft)]
                                "
                            ></div>

                        </div>
                    @endfor

                </div>

            </div>

        </section>

    </div>

</div>