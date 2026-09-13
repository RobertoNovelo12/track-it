<div>

    @php
        $formatDate = function ($value) {
            if (!$value) {
                return '—';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable $e) {
                return $value;
            }
        };

        $nombrePrincipal = trim(
            ($equipo['tipo_equipo'] ?? '') . ' ' .
            ($equipo['marca'] ?? '') . ' ' .
            ($equipo['modelo'] ?? '')
        );

        if ($nombrePrincipal === '') {
            $nombrePrincipal = $equipo['nombre_equipo'] ?? 'Equipo';
        }
    @endphp


    {{-- ============================================================
        RESUMEN DEL EQUIPO
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]
            border
            border-[var(--theme-border)]
            rounded-xl
            p-4
            sm:p-5
            mb-4
            sm:mb-6
        "
    >

        <div
            class="
                flex
                flex-col
                gap-4

                sm:flex-row
                sm:items-start
                sm:justify-between
            "
        >

            {{-- Información principal --}}
            <div class="flex items-start gap-3 min-w-0">

                {{-- Icono --}}
                <div
                    class="
                        shrink-0
                        w-12
                        h-12
                        rounded-xl
                        bg-[var(--theme-primary-soft)]
                        flex
                        items-center
                        justify-center
                        text-[var(--theme-primary)]
                    "
                >
                    <svg
                        class="w-6 h-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <rect
                            x="4"
                            y="4"
                            width="16"
                            height="12"
                            rx="1.5"
                        />

                        <path d="M8 20h8"/>
                        <path d="M12 16v4"/>
                    </svg>
                </div>


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
                            class="
                                text-base
                                sm:text-lg
                                font-semibold
                                text-[var(--theme-text-strong)]
                                break-words
                            "
                        >
                            {{ $nombrePrincipal }}
                        </h2>


                        <x-status-badge
                            :status="$equipo['estado'] ?? 'Sin estado'"
                        />

                    </div>


                    {{-- Nombre capturado --}}
                    @if (!empty($equipo['nombre_equipo']))
                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $equipo['nombre_equipo'] }}
                        </p>
                    @endif


                    {{-- Código --}}
                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            gap-x-3
                            gap-y-1
                            mt-2
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >

                        <span>
                            Código:
                            <span
                                class="
                                    font-semibold
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $equipo['codigo_inventario'] ?? '—' }}
                            </span>
                        </span>


                        @if (!empty($equipo['numero_serie']))
                            <span>
                                SN:
                                <span class="text-[var(--theme-text)]">
                                    {{ $equipo['numero_serie'] }}
                                </span>
                            </span>
                        @endif

                    </div>

                </div>

            </div>


            {{-- Exportar: solo visual --}}
            <button
                type="button"
                class="
                    shrink-0
                    w-full
                    sm:w-auto
                    h-10

                    flex
                    items-center
                    justify-center
                    gap-2

                    border
                    border-[var(--theme-border-strong)]
                    rounded-lg
                    bg-[var(--theme-surface)]

                    px-4

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    cursor-default
                "
                title="Función de exportación no habilitada por el momento"
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M12 3v12"/>
                    <path d="M7 10l5 5 5-5"/>
                    <path d="M4 21h16"/>
                </svg>

                Exportar
            </button>

        </div>


        {{-- Resumen inferior --}}
        <div
            class="
                grid
                grid-cols-2
                lg:grid-cols-4
                gap-3
                mt-5
                pt-4
                border-t
                border-[var(--theme-border)]
            "
        >

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Tipo
                </p>

                <p class="text-xs font-medium text-[var(--theme-text)] mt-1">
                    {{ $equipo['tipo_equipo'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Marca
                </p>

                <p class="text-xs font-medium text-[var(--theme-text)] mt-1">
                    {{ $equipo['marca'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Modelo
                </p>

                <p class="text-xs font-medium text-[var(--theme-text)] mt-1">
                    {{ $equipo['modelo'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Ubicación
                </p>

                <p class="text-xs font-medium text-[var(--theme-text)] mt-1">
                    {{ $equipo['ubicacion'] ?? '—' }}
                </p>
            </div>

        </div>

    </section>


    {{-- ============================================================
        TARJETAS PRINCIPALES
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-3
            gap-4
            sm:gap-5
            mb-4
            sm:mb-6
        "
    >

        {{-- ========================================================
            INFORMACIÓN GENERAL
        ======================================================== --}}
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

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[var(--theme-text)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v6"/>
                    <path d="M12 7h.01"/>
                </svg>

                <h3
                    class="
                        text-sm
                        sm:text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Información general
                </h3>

            </div>


            <dl class="space-y-3">

                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Nombre del activo
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                            text-right
                            break-words
                        "
                    >
                        {{ $equipo['nombre_equipo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Tipo
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['tipo_equipo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Marca
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['marca'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Modelo
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['modelo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Número de serie
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                            text-right
                            break-all
                        "
                    >
                        {{ $equipo['numero_serie'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Código de inventario
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['codigo_inventario'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Estado
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['estado'] ?? 'Sin estado' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Condición
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['condicion'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Host
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['host'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Dirección MAC
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                            text-right
                            break-all
                        "
                    >
                        {{ $equipo['direccion_mac'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Factura
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['numero_factura'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Fecha de compra
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $formatDate($equipo['fecha_compra'] ?? null) }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Garantía
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $formatDate($equipo['fecha_fin_garantia'] ?? null) }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[var(--theme-text-muted)]">
                        Proveedor
                    </dt>

                    <dd class="text-xs font-medium text-[var(--theme-text)] text-right">
                        {{ $equipo['proveedor'] ?? '—' }}
                    </dd>

                </div>

            </dl>

        </section>


        {{-- ========================================================
            ESPECIFICACIONES TÉCNICAS
        ======================================================== --}}
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

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[var(--theme-text)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <rect
                        x="4"
                        y="5"
                        width="16"
                        height="12"
                        rx="1.5"
                    />

                    <path d="M8 21h8"/>
                    <path d="M12 17v4"/>
                </svg>

                <h3
                    class="
                        text-sm
                        sm:text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Especificaciones técnicas
                </h3>

            </div>


            @if (count($especificaciones) > 0)

                <dl class="space-y-3">

                    @foreach ($especificaciones as $item)

                        <div
                            class="
                                flex
                                justify-between
                                gap-4
                            "
                        >

                            <dt class="text-xs text-[var(--theme-text-muted)]">
                                {{ $item['label'] }}
                            </dt>

                            <dd
                                class="
                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                    text-right
                                    break-words
                                "
                            >
                                {{ $item['value'] }}
                            </dd>

                        </div>

                    @endforeach

                </dl>

            @else

                <div
                    class="
                        py-10
                        text-center
                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >
                    No hay especificaciones adicionales registradas.
                </div>

            @endif

        </section>


        {{-- ========================================================
            ASIGNACIÓN ACTUAL
        ======================================================== --}}
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

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[var(--theme-text)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <circle cx="12" cy="8" r="3"/>
                    <path d="M6 21v-2a6 6 0 0112 0v2"/>
                </svg>

                <h3
                    class="
                        text-sm
                        sm:text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignación actual
                </h3>

            </div>


            @if ($asignacionActual)

                <dl class="space-y-3">

                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[var(--theme-text-muted)]">
                            Responsable
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                                text-right
                            "
                        >
                            {{ $asignacionActual['nombre_colaborador'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[var(--theme-text-muted)]">
                            Área / Departamento
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                                text-right
                            "
                        >
                            {{ $asignacionActual['ubicacion'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[var(--theme-text-muted)]">
                            Tipo
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                                text-right
                            "
                        >
                            {{ $asignacionActual['tipo_asignacion'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[var(--theme-text-muted)]">
                            Fecha de asignación
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                                text-right
                            "
                        >
                            {{ $formatDate(
                                $asignacionActual['fecha_asignacion']
                                ?? null
                            ) }}
                        </dd>

                    </div>

                </dl>

            @else

                <div
                    class="
                        flex
                        flex-col
                        items-center
                        justify-center
                        py-10
                        text-center
                    "
                >

                    <div
                        class="
                            w-10
                            h-10
                            rounded-full
                            bg-[var(--theme-surface-soft)]
                            flex
                            items-center
                            justify-center
                            mb-3
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
                            <circle cx="12" cy="8" r="3"/>
                            <path d="M6 21v-2a6 6 0 0112 0v2"/>
                        </svg>
                    </div>

                    <p class="text-sm text-[var(--theme-text-muted)]">
                        Equipo disponible
                    </p>

                    <p class="text-xs text-[var(--theme-text-muted)] mt-1">
                        No tiene una asignación registrada.
                    </p>

                </div>

            @endif

        </section>

    </div>


    {{-- ============================================================
        HISTORIALES
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-2
            gap-4
            sm:gap-5
        "
    >

        {{-- ========================================================
            MOVIMIENTOS
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

            <div
                class="
                    flex
                    items-center
                    gap-2
                    p-4
                    sm:p-5
                    border-b
                    border-[var(--theme-border)]
                "
            >

                <svg
                    class="w-5 h-5 text-[var(--theme-text)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M7 7h11"/>
                    <path d="M15 4l3 3-3 3"/>
                    <path d="M17 17H6"/>
                    <path d="M9 14l-3 3 3 3"/>
                </svg>

                <h3
                    class="
                        text-sm
                        sm:text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Historial de movimientos
                </h3>

            </div>


            @if (count($movimientos) > 0)

                {{-- Escritorio --}}
                <div class="hidden md:block overflow-x-auto">

                    <table class="w-full text-xs">

                        <thead>

                            <tr
                                class="
                                    text-left
                                    text-[var(--theme-text-muted)]
                                    border-b
                                    border-[var(--theme-border)]
                                "
                            >
                                <th class="px-5 py-3 font-medium">
                                    Fecha
                                </th>

                                <th class="px-3 py-3 font-medium">
                                    Operación
                                </th>

                                <th class="px-3 py-3 font-medium">
                                    Responsable
                                </th>

                                <th class="px-5 py-3 font-medium">
                                    Ubicación
                                </th>
                            </tr>

                        </thead>


                        <tbody>

                            @foreach ($movimientos as $movimiento)

                                <tr
                                    class="
                                        border-b
                                        border-[var(--theme-border)]
                                        last:border-0
                                    "
                                >

                                    <td class="px-5 py-3 text-[var(--theme-text)]">
                                        {{ $formatDate(
                                            $movimiento['fecha']
                                            ?? null
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-[var(--theme-text)]">
                                        {{ $movimiento['operacion'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-3 text-[var(--theme-text)]">
                                        {{ $movimiento['responsable'] ?? '—' }}
                                    </td>

                                    <td class="px-5 py-3 text-[var(--theme-text)]">
                                        {{ $movimiento['ubicacion'] ?? '—' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- Móvil --}}
                <div class="md:hidden divide-y divide-[var(--theme-border)]">

                    @foreach ($movimientos as $movimiento)

                        <div class="p-4">

                            <div
                                class="
                                    flex
                                    items-start
                                    justify-between
                                    gap-3
                                "
                            >

                                <p
                                    class="
                                        text-sm
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ $movimiento['operacion'] ?? 'Movimiento' }}
                                </p>

                                <span
                                    class="
                                        shrink-0
                                        text-xs
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ $formatDate(
                                        $movimiento['fecha']
                                        ?? null
                                    ) }}
                                </span>

                            </div>


                            <div class="mt-2 space-y-1">

                                <p class="text-xs text-[var(--theme-text-muted)]">
                                    <span class="text-[var(--theme-text-muted)]">
                                        Responsable:
                                    </span>

                                    {{ $movimiento['responsable'] ?? '—' }}
                                </p>

                                <p class="text-xs text-[var(--theme-text-muted)]">
                                    <span class="text-[var(--theme-text-muted)]">
                                        Ubicación:
                                    </span>

                                    {{ $movimiento['ubicacion'] ?? '—' }}
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div
                    class="
                        px-5
                        py-10
                        text-center
                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >
                    No hay movimientos registrados.
                </div>

            @endif

        </section>


        {{-- ========================================================
            MANTENIMIENTOS
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

            <div
                class="
                    flex
                    items-center
                    gap-2
                    p-4
                    sm:p-5
                    border-b
                    border-[var(--theme-border)]
                "
            >

                <svg
                    class="w-5 h-5 text-[var(--theme-text)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M14 6l4-4 4 4-4 4"/>
                    <path d="M10 18l-4 4-4-4 4-4"/>
                    <path d="M14 10L4 20"/>
                    <path d="M20 4L10 14"/>
                </svg>

                <h3
                    class="
                        text-sm
                        sm:text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mantenimiento e intervenciones
                </h3>

            </div>


            @if (count($mantenimientos) > 0)

                <div class="divide-y divide-[var(--theme-border)]">

                    @foreach ($mantenimientos as $mantenimiento)

                        <div class="p-4">

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ $mantenimiento['tipo'] ?? 'Mantenimiento' }}
                            </p>

                        </div>

                    @endforeach

                </div>

            @else

                <div
                    class="
                        flex
                        flex-col
                        items-center
                        justify-center

                        px-5
                        py-10

                        text-center
                    "
                >

                    <div
                        class="
                            w-10
                            h-10

                            rounded-full

                            bg-[var(--theme-surface-soft)]

                            flex
                            items-center
                            justify-center

                            mb-3

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
                            <path d="M14 6l4-4 4 4-4 4"/>
                            <path d="M10 18l-4 4-4-4 4-4"/>
                            <path d="M14 10L4 20"/>
                            <path d="M20 4L10 14"/>
                        </svg>
                    </div>

                    <p class="text-sm text-[var(--theme-text-muted)]">
                        Sin mantenimientos registrados
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Los mantenimientos del equipo aparecerán aquí.
                    </p>

                </div>

            @endif

        </section>

    </div>


    {{-- ============================================================
        COMENTARIOS
    ============================================================ --}}
    @if (!empty($equipo['comentarios']))

        <section
            class="
                mt-4
                sm:mt-6

                bg-[var(--theme-primary-soft-subtle)]

                border
                border-[var(--theme-primary-border)]

                rounded-xl

                px-4
                py-3
            "
        >

            <p
                class="
                    text-xs
                    font-semibold
                    text-[var(--theme-primary)]
                    mb-1
                "
            >
                Comentarios
            </p>

            <p
                class="
                    text-xs
                    leading-relaxed
                    text-[var(--theme-text)]
                "
            >
                {{ $equipo['comentarios'] }}
            </p>

        </section>

    @endif

</div>