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
            bg-white
            border
            border-[#50514F]/10
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
                        bg-[#247BA0]/10
                        flex
                        items-center
                        justify-center
                        text-[#247BA0]
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
                                text-[#25344A]
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
                                text-[#50514F]/60
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
                            text-[#50514F]/55
                        "
                    >

                        <span>
                            Código:
                            <span
                                class="
                                    font-semibold
                                    text-[#50514F]/80
                                "
                            >
                                {{ $equipo['codigo_inventario'] ?? '—' }}
                            </span>
                        </span>


                        @if (!empty($equipo['numero_serie']))
                            <span>
                                SN:
                                <span class="text-[#50514F]/80">
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
                    border-[#50514F]/20
                    rounded-lg
                    bg-white

                    px-4

                    text-sm
                    font-medium
                    text-[#50514F]/65

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
                border-[#50514F]/10
            "
        >

            <div>
                <p class="text-[11px] text-[#50514F]/45">
                    Tipo
                </p>

                <p class="text-xs font-medium text-[#50514F]/80 mt-1">
                    {{ $equipo['tipo_equipo'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[#50514F]/45">
                    Marca
                </p>

                <p class="text-xs font-medium text-[#50514F]/80 mt-1">
                    {{ $equipo['marca'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[#50514F]/45">
                    Modelo
                </p>

                <p class="text-xs font-medium text-[#50514F]/80 mt-1">
                    {{ $equipo['modelo'] ?? '—' }}
                </p>
            </div>


            <div>
                <p class="text-[11px] text-[#50514F]/45">
                    Ubicación
                </p>

                <p class="text-xs font-medium text-[#50514F]/80 mt-1">
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
                bg-white
                border
                border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-5
            "
        >

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[#50514F]/70"
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
                        text-[#50514F]
                    "
                >
                    Información general
                </h3>

            </div>


            <dl class="space-y-3">

                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Nombre del activo
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[#50514F]/80
                            text-right
                            break-words
                        "
                    >
                        {{ $equipo['nombre_equipo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Tipo
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['tipo_equipo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Marca
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['marca'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Modelo
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['modelo'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Número de serie
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[#50514F]/80
                            text-right
                            break-all
                        "
                    >
                        {{ $equipo['numero_serie'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Código de inventario
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['codigo_inventario'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Estado
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['estado'] ?? 'Sin estado' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Condición
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['condicion'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Host
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['host'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Dirección MAC
                    </dt>

                    <dd
                        class="
                            text-xs
                            font-medium
                            text-[#50514F]/80
                            text-right
                            break-all
                        "
                    >
                        {{ $equipo['direccion_mac'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Factura
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $equipo['numero_factura'] ?? '—' }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Fecha de compra
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $formatDate($equipo['fecha_compra'] ?? null) }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Garantía
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
                        {{ $formatDate($equipo['fecha_fin_garantia'] ?? null) }}
                    </dd>

                </div>


                <div class="flex justify-between gap-4">

                    <dt class="text-xs text-[#50514F]/55">
                        Proveedor
                    </dt>

                    <dd class="text-xs font-medium text-[#50514F]/80 text-right">
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
                bg-white
                border
                border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-5
            "
        >

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[#50514F]/70"
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
                        text-[#50514F]
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

                            <dt class="text-xs text-[#50514F]/55">
                                {{ $item['label'] }}
                            </dt>

                            <dd
                                class="
                                    text-xs
                                    font-medium
                                    text-[#50514F]/80
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
                        text-[#50514F]/40
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
                bg-white
                border
                border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-5
            "
        >

            <div class="flex items-center gap-2 mb-5">

                <svg
                    class="w-5 h-5 text-[#50514F]/70"
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
                        text-[#50514F]
                    "
                >
                    Asignación actual
                </h3>

            </div>


            @if ($asignacionActual)

                <dl class="space-y-3">

                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[#50514F]/55">
                            Responsable
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[#50514F]/80
                                text-right
                            "
                        >
                            {{ $asignacionActual['nombre_colaborador'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[#50514F]/55">
                            Área / Departamento
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[#50514F]/80
                                text-right
                            "
                        >
                            {{ $asignacionActual['ubicacion'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[#50514F]/55">
                            Tipo
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[#50514F]/80
                                text-right
                            "
                        >
                            {{ $asignacionActual['tipo_asignacion'] ?? '—' }}
                        </dd>

                    </div>


                    <div class="flex justify-between gap-4">

                        <dt class="text-xs text-[#50514F]/55">
                            Fecha de asignación
                        </dt>

                        <dd
                            class="
                                text-xs
                                font-medium
                                text-[#50514F]/80
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
                            bg-[#50514F]/5
                            flex
                            items-center
                            justify-center
                            mb-3
                            text-[#50514F]/35
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

                    <p class="text-sm text-[#50514F]/50">
                        Equipo disponible
                    </p>

                    <p class="text-xs text-[#50514F]/35 mt-1">
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
                bg-white
                border
                border-[#50514F]/10
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
                    border-[#50514F]/10
                "
            >

                <svg
                    class="w-5 h-5 text-[#50514F]/70"
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
                        text-[#50514F]
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
                                    text-[#50514F]/50
                                    border-b
                                    border-[#50514F]/10
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
                                        border-[#50514F]/5
                                        last:border-0
                                    "
                                >

                                    <td class="px-5 py-3 text-[#50514F]/70">
                                        {{ $formatDate(
                                            $movimiento['fecha']
                                            ?? null
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-[#50514F]/70">
                                        {{ $movimiento['operacion'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-3 text-[#50514F]/70">
                                        {{ $movimiento['responsable'] ?? '—' }}
                                    </td>

                                    <td class="px-5 py-3 text-[#50514F]/70">
                                        {{ $movimiento['ubicacion'] ?? '—' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- Móvil --}}
                <div class="md:hidden divide-y divide-[#50514F]/10">

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
                                        text-[#50514F]
                                    "
                                >
                                    {{ $movimiento['operacion'] ?? 'Movimiento' }}
                                </p>

                                <span
                                    class="
                                        shrink-0
                                        text-xs
                                        text-[#50514F]/45
                                    "
                                >
                                    {{ $formatDate(
                                        $movimiento['fecha']
                                        ?? null
                                    ) }}
                                </span>

                            </div>


                            <div class="mt-2 space-y-1">

                                <p class="text-xs text-[#50514F]/60">
                                    <span class="text-[#50514F]/40">
                                        Responsable:
                                    </span>

                                    {{ $movimiento['responsable'] ?? '—' }}
                                </p>

                                <p class="text-xs text-[#50514F]/60">
                                    <span class="text-[#50514F]/40">
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
                        text-[#50514F]/40
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
                bg-white
                border
                border-[#50514F]/10
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
                    border-[#50514F]/10
                "
            >

                <svg
                    class="w-5 h-5 text-[#50514F]/70"
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
                        text-[#50514F]
                    "
                >
                    Mantenimiento e intervenciones
                </h3>

            </div>


            @if (count($mantenimientos) > 0)

                <div class="divide-y divide-[#50514F]/10">

                    @foreach ($mantenimientos as $mantenimiento)

                        <div class="p-4">

                            <p class="text-sm font-medium text-[#50514F]">
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
                            bg-[#50514F]/5
                            flex
                            items-center
                            justify-center
                            mb-3
                            text-[#50514F]/35
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

                    <p class="text-sm text-[#50514F]/50">
                        Sin mantenimientos registrados
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[#50514F]/35
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

                bg-[#247BA0]/5
                border
                border-[#247BA0]/10
                rounded-xl

                px-4
                py-3
            "
        >

            <p
                class="
                    text-xs
                    font-semibold
                    text-[#247BA0]
                    mb-1
                "
            >
                Comentarios
            </p>

            <p
                class="
                    text-xs
                    leading-relaxed
                    text-[#50514F]/70
                "
            >
                {{ $equipo['comentarios'] }}
            </p>

        </section>

    @endif

</div>