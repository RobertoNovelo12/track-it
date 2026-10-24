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

    {{-- ============================================================*

*        RESUMEN DEL EQUIPO*

*    ============================================================ --}}

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

            {{-- Acciones: Editar + Exportar --}}
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">

            {{--
                Editar: todos los roles (Lectura abre el formulario en solo
                lectura, igual que en la tabla de Equipos). Los equipos dados
                de baja no se pueden editar.
            --}}
            @unless ($dadoDeBaja)
                <a
                    href="{{ route('equipos.edit', $this->equipoId) }}"
                    wire:navigate.hover
                    class="flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-transparent bg-[var(--theme-primary)] px-4 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] sm:w-auto"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>

                    @can('gestionar')
                        Editar
                    @else
                        Ver formulario
                    @endcan
                </a>
            @endunless

            {{-- Duplicar: alta de un equipo parecido con los mismos datos comunes --}}
            @can('gestionar')
                <a
                    href="{{ route('equipos.create', ['duplicar' => $equipoId]) }}"
                    wire:navigate.hover
                    class="flex h-10 w-full shrink-0 items-center justify-center gap-2 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:text-[var(--theme-primary)] sm:w-auto"
                    title="Dar de alta un equipo parecido: copia tipo, marca, modelo, factura, hotel y especificaciones"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="8" y="8" width="12" height="12" rx="2"/>
                        <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>
                    </svg>
                    Duplicar
                </a>
            @endcan

            {{-- Ficha completa del equipo --}}
            <a

                href="{{ route('equipos.ficha', $equipoId) }}"

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

                    hover:text-[var(--theme-primary)]

                "

                title="Descargar ficha del equipo en PDF"

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
            </a>

            </div>

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

    {{-- ============================================================*

*        TARJETAS PRINCIPALES*

*    ============================================================ --}}

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

        {{-- ========================================================*

*            INFORMACIÓN GENERAL*

*        ======================================================== --}}

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

                {{-- Código de barras (solo el número del código de inventario) --}}
                @if (\App\Support\CodigoBarras::numero($equipo['codigo_inventario'] ?? null))
                    <div class="flex justify-between gap-4">
                        <dt class="text-xs text-[var(--theme-text-muted)]">
                            Código de barras
                        </dt>

                        <dd>
                            <x-equipo.codigo-barras
                                :codigo="$equipo['codigo_inventario']"
                                class="items-end"
                            />
                        </dd>
                    </div>
                @endif

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

        {{-- ========================================================*

*            ESPECIFICACIONES TÉCNICAS*

*        ======================================================== --}}

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

        {{-- ========================================================*

*            ASIGNACIÓN ACTUAL*

*        ======================================================== --}}

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

                            {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] }} / Departamento

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

                {{-- Devolver: cierra la asignación; el equipo queda disponible --}}
                @can('gestionar')
                    <a
                        href="{{ route('asignaciones.devolver', ['equipo' => $equipoId]) }}"
                        wire:navigate.hover
                        class="mt-4 inline-flex h-9 w-full items-center justify-center gap-2 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14L4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/></svg>
                        Devolver equipo
                    </a>
                @endcan

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
        PROGRAMAS INSTALADOS (tipos con «Incluye programas»)
    ============================================================ --}}
    @if ($muestraProgramas)
        <section class="mb-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:mb-6 sm:p-5" aria-labelledby="equipo-programas-titulo">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-[var(--theme-text)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="14" rx="1.5"/>
                        <path d="M3 8h18M7 12h4M7 15h6"/>
                    </svg>
                    <h3 id="equipo-programas-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)] sm:text-base">
                        Programas instalados
                    </h3>
                    @if ($programas !== [])
                        <span class="rounded-md bg-[var(--theme-surface-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-text-muted)]">{{ count($programas) }}</span>
                    @endif
                </div>

                @can('gestionar')
                    @unless ($dadoDeBaja)
                        <a href="{{ route('equipos.edit', $this->equipoId) }}" wire:navigate.hover class="text-xs font-medium text-[var(--theme-primary)] hover:underline">
                            {{ $programas === [] ? 'Agregar programas' : 'Cambiar programas' }}
                        </a>
                    @endunless
                @endcan
            </div>

            @if ($programas === [])
                <p class="rounded-lg border border-dashed border-[var(--theme-border-strong)] px-4 py-5 text-center text-sm text-[var(--theme-text-muted)]">
                    Sin programas registrados.
                </p>
            @else
                <ul class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($programas as $programa)
                        <li wire:key="equipo-programa-{{ $programa['id'] }}" class="flex items-start justify-between gap-3 rounded-lg border border-[var(--theme-border)] px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--theme-text)]" title="{{ $programa['nombre'] }}">{{ $programa['nombre'] }}</p>

                                @php
                                    $detallePrograma = trim(implode(' · ', array_filter([
                                        $programa['empresa'],
                                        $programa['version'] !== '' ? 'v' . ltrim($programa['version'], 'vV') : null,
                                        $programa['plataforma'],
                                        $programa['peso'],
                                    ])));
                                @endphp

                                @if ($detallePrograma !== '')
                                    <p class="truncate text-xs text-[var(--theme-text-muted)]" title="{{ $detallePrograma }}">{{ $detallePrograma }}</p>
                                @endif

                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @if ($programa['categoria'] !== '')
                                        <span class="rounded-md border border-[var(--theme-border)] px-1.5 py-0.5 text-[10px] text-[var(--theme-text-muted)]">{{ $programa['categoria'] }}</span>
                                    @endif
                                    @if ($programa['licencia'] !== 'Gratuita')
                                        <span class="rounded-md bg-[var(--theme-warning-soft)] px-1.5 py-0.5 text-[10px] font-medium text-[var(--theme-warning)]">{{ $programa['licencia'] }}</span>
                                    @endif
                                    @if ($programa['requiere_clave'])
                                        <span class="rounded-md bg-[var(--theme-surface-soft)] px-1.5 py-0.5 text-[10px] font-medium text-[var(--theme-text)]">Requiere clave</span>
                                    @endif
                                    @unless ($programa['activo'])
                                        <span class="rounded-md bg-[var(--theme-surface-soft)] px-1.5 py-0.5 text-[10px] text-[var(--theme-text-muted)]">Inactivo</span>
                                    @endunless
                                </div>
                            </div>

                            @if ($programa['enlace'] !== '')
                                <a
                                    href="{{ $programa['enlace'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-8 shrink-0 items-center gap-1.5 rounded-md border border-[var(--theme-border-strong)] px-2.5 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                                    title="Abrir el enlace de descarga de {{ $programa['nombre'] }}"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg>
                                    Descargar
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    {{-- ============================================================*

*        HISTORIALES*

*    ============================================================ --}}

    <div

        class="

            grid

            grid-cols-1

            xl:grid-cols-2

            gap-4

            sm:gap-5

        "

    >

        {{-- ========================================================*

*            MOVIMIENTOS*

*        ======================================================== --}}

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

                                    Hotel / Ubicación

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

        {{-- ========================================================*

*            MANTENIMIENTOS*

*        ======================================================== --}}

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

    {{-- ============================================================*

*        COMENTARIOS*

*    ============================================================ --}}

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