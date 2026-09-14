<div>

{{-- ============================================================
    INDICADORES
============================================================ --}}
<div
    class="
        grid
        grid-cols-1
        sm:grid-cols-2
        xl:grid-cols-4

        gap-4
        mb-6
    "
>

    {{-- Registrados este mes --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-primary-border)]

            rounded-lg

            p-4

            flex
            items-center
            gap-4
        "
    >

        <div
            class="
                w-12
                h-12
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
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                <path d="M18 7v6"/>
                <path d="M15 10h6"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-primary)]
                "
            >
                Nuevos usuarios
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-primary)]
                "
            >
                {{ number_format($stats['registrados_mes']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Registrados este mes
            </p>

        </div>

    </div>


    {{-- Activos --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-success-border)]

            rounded-lg

            p-4

            flex
            items-center
            gap-4
        "
    >

        <div
            class="
                w-12
                h-12
                shrink-0

                rounded-lg

                flex
                items-center
                justify-center

                bg-[var(--theme-success-soft)]
                text-[var(--theme-success)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                <path d="M16 11l2 2 4-5"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-success)]
                "
            >
                Usuarios activos
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-success)]
                "
            >
                {{ number_format($stats['activos']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Cuentas habilitadas
            </p>

        </div>

    </div>


    {{-- Pendientes --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-warning-border)]

            rounded-lg

            p-4

            flex
            items-center
            gap-4
        "
    >

        <div
            class="
                w-12
                h-12
                shrink-0

                rounded-lg

                flex
                items-center
                justify-center

                bg-[var(--theme-warning-soft)]
                text-[var(--theme-warning)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                <circle cx="18" cy="10" r="3"/>
                <path d="M18 8.5V10l1 1"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-warning)]
                "
            >
                Pendientes
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-warning)]
                "
            >
                {{ number_format($stats['pendientes']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Requieren aprobación
            </p>

        </div>

    </div>


    {{-- Roles --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border-strong)]

            rounded-lg

            p-4

            flex
            items-center
            gap-4
        "
    >

        <div
            class="
                w-12
                h-12
                shrink-0

                rounded-lg

                flex
                items-center
                justify-center

                bg-[var(--theme-surface-soft)]
                text-[var(--theme-text-strong)]
            "
        >
            <svg
                class="w-6 h-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                <circle cx="12" cy="10" r="2"/>
                <path d="M9 16c.5-2 1.5-3 3-3s2.5 1 3 3"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                Roles configurados
            </p>

            <p
                class="
                    text-2xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-text-strong)]
                "
            >
                {{ number_format($stats['roles']) }}
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                {{ number_format($stats['total']) }} usuarios totales
            </p>

        </div>

    </div>

</div>



{{-- ============================================================
    GRID PRINCIPAL
============================================================ --}}
<div
    class="
        grid
        grid-cols-1
        xl:grid-cols-[minmax(0,1fr)_320px]

        gap-5
    "
>

    {{-- ========================================================
        COLUMNA IZQUIERDA
    ======================================================== --}}
    <div class="space-y-5">

        {{-- ====================================================
            GESTIÓN DE USUARIOS
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                overflow-hidden
            "
        >

            {{-- Cabecera --}}
            <div
                class="
                    px-4
                    sm:px-5
                    py-4

                    border-b
                    border-[var(--theme-border)]

                    flex
                    flex-col
                    lg:flex-row
                    lg:items-center
                    lg:justify-between

                    gap-3
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Gestión de usuarios
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Consulta y administra las cuentas registradas.
                    </p>

                </div>


                {{-- Filtros --}}
                <div
                    class="
                        flex
                        flex-col
                        sm:flex-row
                        gap-2
                    "
                >

                    {{-- Buscar --}}
                    <div
                        class="
                            relative
                            w-full
                            sm:w-64
                        "
                    >

                        {{-- Lupa --}}
                        <svg
                            wire:loading.remove
                            wire:target="search"
                            class="
                                absolute
                                left-3
                                top-1/2
                                -translate-y-1/2

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M20 20l-4-4"/>
                        </svg>


                        {{-- Spinner mientras busca --}}
                        <svg
                            wire:loading
                            wire:target="search"
                            class="
                                absolute
                                left-3
                                top-1/2
                                -translate-y-1/2

                                w-4
                                h-4

                                animate-spin

                                text-[var(--theme-primary)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M21 12a9 9 0 00-9-9v3a6 6 0 016 6h3z"
                            />
                        </svg>


                        <input
                            type="search"
                            wire:model="search"
                            wire:keydown.enter="buscar"
                            placeholder="Buscar usuario..."
                            autocomplete="off"
                            class="
                                w-full
                                h-9

                                pl-9
                                pr-3

                                rounded-md

                                bg-[var(--theme-surface-soft)]

                                border
                                border-[var(--theme-border)]

                                text-xs
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]
                            "
                        >

                    </div>


                    {{-- Estado --}}
                    <select
                        wire:model.live="estado"
                        class="
                            h-9

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-xs
                            text-[var(--theme-text)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >
                        <option value="">
                            Todos los estados
                        </option>

                        @foreach ($estados as $estadoOption)

                            <option value="{{ $estadoOption->clave }}">
                                {{ $estadoOption->nombre }}
                            </option>

                        @endforeach
                    </select>

                </div>

            </div>

            {{-- =================================================
                TABLA ESCRITORIO
            ================================================= --}}
            <div class="hidden lg:block overflow-x-auto">

                <table class="w-full text-xs">

                    <thead
                        class="
                            bg-[var(--theme-surface-soft)]
                        "
                    >

                        <tr
                            class="
                                text-left
                                text-[var(--theme-text-muted)]
                            "
                        >

                            <th class="px-4 py-3 font-medium">
                                Usuario
                            </th>

                            <th class="px-4 py-3 font-medium">
                                Correo
                            </th>

                            <th class="px-4 py-3 font-medium">
                                Área / Departamento
                            </th>

                            <th class="px-4 py-3 font-medium">
                                Rol
                            </th>

                            <th class="px-4 py-3 font-medium">
                                Estado
                            </th>

                            <th class="px-4 py-3 font-medium">
                                Registro
                            </th>

                            <th class="px-4 py-3 font-medium text-right">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($usuarios as $usuario)

                            @php
                                $estadoClave = strtoupper(
                                    $usuario->estado_clave ?? ''
                                );

                                $estadoClasses = match ($estadoClave) {
                                    'ACTIVO' =>
                                        'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                                    'PENDIENTE' =>
                                        'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                                    'INACTIVO', 'BAJA' =>
                                        'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                                    default =>
                                        'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
                                };
                            @endphp


                            <tr
                                wire:key="usuario-{{ $usuario->id_usuario }}"
                                class="
                                    border-t
                                    border-[var(--theme-border)]

                                    hover:bg-[var(--theme-primary-soft-subtle)]

                                    transition-colors
                                "
                            >

                                {{-- Usuario --}}
                                <td class="px-4 py-3">

                                    <div
                                        class="
                                            flex
                                            items-center
                                            gap-3
                                        "
                                    >

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

                                                font-medium
                                                text-[11px]
                                                uppercase
                                            "
                                        >
                                            {{ mb_substr($usuario->nombres ?? '?', 0, 1) }}
                                            {{ mb_substr($usuario->apellido_paterno ?? '', 0, 1) }}
                                        </div>


                                        <div class="min-w-0">

                                            <p
                                                class="
                                                    font-medium
                                                    text-[var(--theme-text-strong)]
                                                    truncate
                                                "
                                            >
                                                {{ $usuario->nombres }}
                                                {{ $usuario->apellido_paterno }}
                                            </p>

                                            <p
                                                class="
                                                    mt-0.5
                                                    text-[10px]
                                                    text-[var(--theme-text-muted)]
                                                    truncate
                                                "
                                            >
                                                {{ '@' . $usuario->username }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- Correo --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $usuario->correo }}
                                </td>


                                {{-- Área --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        text-[var(--theme-text)]
                                    "
                                >
                                    @if ($usuario->departamento_nombre)

                                        {{ $usuario->departamento_nombre }}

                                    @elseif ($usuario->area_nombre)

                                        {{ $usuario->area_nombre }}

                                    @else

                                        <span class="text-[var(--theme-text-muted)]">
                                            Sin asignar
                                        </span>

                                    @endif
                                </td>


                                {{-- Rol --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $usuario->rol_nombre ?? 'Sin rol' }}
                                </td>


                                {{-- Estado --}}
                                <td class="px-4 py-3">

                                    <span
                                        class="
                                            inline-flex
                                            items-center

                                            px-2
                                            py-1

                                            rounded-full

                                            text-[10px]
                                            font-medium

                                            {{ $estadoClasses }}
                                        "
                                    >
                                        {{ $usuario->estado_nombre }}
                                    </span>

                                </td>


                                {{-- Registro --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        whitespace-nowrap

                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ $usuario->fecha_registro
                                        ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                                        : '—'
                                    }}
                                </td>


                                {{-- Acciones --}}
                                <td class="px-4 py-3">

                                    <div
                                        class="
                                            flex
                                            items-center
                                            justify-end
                                            gap-2
                                        "
                                    >

                                            @if ($estadoClave === 'PENDIENTE')

                                                <button
                                                    type="button"
                                                    wire:click="openApprovalModal({{ $usuario->id_usuario }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openApprovalModal({{ $usuario->id_usuario }})"
                                                    class="
                                                        h-7
                                                        px-2.5

                                                        rounded-md

                                                        bg-[var(--theme-success-soft)]
                                                        text-[var(--theme-success)]

                                                        text-[10px]
                                                        font-medium

                                                        hover:opacity-80

                                                        disabled:opacity-50
                                                        disabled:cursor-not-allowed

                                                        transition
                                                    "
                                                >
                                                    Aprobar
                                                </button>

                                            @endif


                                        <button
                                            type="button"
                                            title="Ver usuario"
                                            class="
                                                w-7
                                                h-7

                                                flex
                                                items-center
                                                justify-center

                                                rounded-md

                                                text-[var(--theme-text-muted)]

                                                hover:bg-[var(--theme-primary-soft)]
                                                hover:text-[var(--theme-primary)]

                                                transition-colors
                                            "
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                            >
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="
                                        px-5
                                        py-12

                                        text-center
                                        text-sm
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    No se encontraron usuarios.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            {{-- =================================================
                TARJETAS MÓVIL
            ================================================= --}}
            <div
                class="
                    lg:hidden

                    divide-y
                    divide-[var(--theme-border)]
                "
            >

                @forelse ($usuarios as $usuario)

                    @php
                        $estadoClave = strtoupper(
                            $usuario->estado_clave ?? ''
                        );

                        $estadoClasses = match ($estadoClave) {
                            'ACTIVO' =>
                                'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                            'PENDIENTE' =>
                                'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                            'INACTIVO', 'BAJA' =>
                                'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                            default =>
                                'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
                        };
                    @endphp


                    <article
                        wire:key="usuario-mobile-{{ $usuario->id_usuario }}"
                        class="p-4"
                    >

                        <div class="flex items-start gap-3">

                            <div
                                class="
                                    w-10
                                    h-10
                                    shrink-0

                                    rounded-full

                                    flex
                                    items-center
                                    justify-center

                                    bg-[var(--theme-primary-soft)]
                                    text-[var(--theme-primary)]

                                    text-xs
                                    font-medium
                                    uppercase
                                "
                            >
                                {{ mb_substr($usuario->nombres ?? '?', 0, 1) }}
                                {{ mb_substr($usuario->apellido_paterno ?? '', 0, 1) }}
                            </div>


                            <div class="min-w-0 flex-1">

                                <div
                                    class="
                                        flex
                                        items-start
                                        justify-between
                                        gap-2
                                    "
                                >

                                    <div class="min-w-0">

                                        <p
                                            class="
                                                text-sm
                                                font-medium
                                                text-[var(--theme-text-strong)]
                                            "
                                        >
                                            {{ $usuario->nombres }}
                                            {{ $usuario->apellido_paterno }}
                                        </p>

                                        <p
                                            class="
                                                mt-0.5
                                                text-xs
                                                text-[var(--theme-text-muted)]
                                                break-all
                                            "
                                        >
                                            {{ $usuario->correo }}
                                        </p>

                                    </div>


                                    <span
                                        class="
                                            shrink-0

                                            px-2
                                            py-1

                                            rounded-full

                                            text-[10px]
                                            font-medium

                                            {{ $estadoClasses }}
                                        "
                                    >
                                        {{ $usuario->estado_nombre }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        grid
                                        grid-cols-2

                                        gap-x-4
                                        gap-y-2

                                        mt-4

                                        text-xs
                                    "
                                >

                                    <div>

                                        <p class="text-[var(--theme-text-muted)]">
                                            Rol
                                        </p>

                                        <p
                                            class="
                                                mt-0.5
                                                text-[var(--theme-text)]
                                            "
                                        >
                                            {{ $usuario->rol_nombre ?? 'Sin rol' }}
                                        </p>

                                    </div>


                                    <div>

                                        <p class="text-[var(--theme-text-muted)]">
                                            Área
                                        </p>

                                        <p
                                            class="
                                                mt-0.5
                                                text-[var(--theme-text)]
                                            "
                                        >
                                            {{ $usuario->departamento_nombre
                                                ?? $usuario->area_nombre
                                                ?? 'Sin asignar'
                                            }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                        @if ($estadoClave === 'PENDIENTE')

                        <div
                            class="
                                mt-4
                                pt-3

                                border-t
                                border-[var(--theme-border)]

                                flex
                                justify-end
                            "
                        >

                            <button
                                type="button"
                                wire:click="openApprovalModal({{ $usuario->id_usuario }})"
                                wire:loading.attr="disabled"
                                wire:target="openApprovalModal({{ $usuario->id_usuario }})"
                                class="
                                    h-8
                                    px-3

                                    rounded-md

                                    bg-[var(--theme-success-soft)]
                                    text-[var(--theme-success)]

                                    text-xs
                                    font-medium

                                    hover:opacity-80

                                    disabled:opacity-50
                                    disabled:cursor-not-allowed

                                    transition
                                "
                            >
                                Aprobar usuario
                            </button>

                        </div>

                    @endif

                    </article>

                @empty

                    <div
                        class="
                            px-5
                            py-12

                            text-center
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        No se encontraron usuarios.
                    </div>

                @endforelse

            </div>



            {{-- =================================================
                PAGINACIÓN
            ================================================= --}}
            @if ($usuarios->hasPages())

                <div
                    class="
                        px-4
                        sm:px-5
                        py-3

                        border-t
                        border-[var(--theme-border)]

                        flex
                        items-center
                        justify-between
                        gap-3
                    "
                >

                    <button
                        type="button"
                        wire:click="previousPage('usuariosPage')"
                        @disabled($usuarios->onFirstPage())
                        class="
                            h-8
                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            text-xs
                            text-[var(--theme-text)]

                            hover:bg-[var(--theme-surface-soft)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed
                        "
                    >
                        Anterior
                    </button>


                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Página

                        <span
                            class="
                                font-medium
                                text-[var(--theme-text-strong)]
                            "
                        >
                            {{ $usuarios->currentPage() }}
                        </span>

                        de

                        <span
                            class="
                                font-medium
                                text-[var(--theme-text-strong)]
                            "
                        >
                            {{ $usuarios->lastPage() }}
                        </span>
                    </span>


                    <button
                        type="button"
                        wire:click="nextPage('usuariosPage')"
                        @disabled(! $usuarios->hasMorePages())
                        class="
                            h-8
                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            text-xs
                            text-[var(--theme-text)]

                            hover:bg-[var(--theme-surface-soft)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed
                        "
                    >
                        Siguiente
                    </button>

                </div>

            @endif

        </section>



        {{-- ====================================================
            BITÁCORA
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                overflow-hidden
            "
        >

            <div
                class="
                    px-4
                    sm:px-5
                    py-4

                    border-b
                    border-[var(--theme-border)]
                "
            >

                <h2
                    class="
                        flex
                        items-center
                        gap-2

                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >

                    <svg
                        class="
                            w-5
                            h-5
                            text-[var(--theme-primary)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M6 2h9l5 5v15H6z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>

                    Bitácora de auditoría
                </h2>

            </div>
{{-- ============================================================
    BITÁCORA - ESCRITORIO
============================================================ --}}
<div class="hidden lg:block overflow-x-auto">

    <table class="w-full text-xs">

        <thead
            class="
                bg-[var(--theme-surface-soft)]
                text-[var(--theme-text-muted)]
            "
        >
            <tr>
                <th class="px-4 py-3 text-left font-medium">
                    Fecha y hora
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Usuario
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Acción
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Módulo
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Descripción
                </th>
            </tr>
        </thead>

        <tbody>

            @forelse ($auditoria as $registro)

                <tr
                    class="
                        border-t
                        border-[var(--theme-border)]
                    "
                >

                    <td
                        class="
                            px-4
                            py-3
                            whitespace-nowrap
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ \Illuminate\Support\Carbon::parse($registro->fecha_hora)->format('d/m/Y H:i') }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->usuario_nombre
                            ?: $registro->username
                            ?: 'Sistema'
                        }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->accion }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->modulo }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            min-w-64
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ $registro->descripcion ?: '—' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td
                        colspan="5"
                        class="
                            px-5
                            py-10
                            text-center
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Todavía no hay eventos registrados en la bitácora.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    </div>


    {{-- ============================================================
        BITÁCORA - MÓVIL
    ============================================================ --}}
    <div
        class="
            lg:hidden
            divide-y
            divide-[var(--theme-border)]
        "
    >

        @forelse ($auditoria as $registro)

            <article class="p-4">

                {{-- Encabezado --}}
                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-3
                    "
                >

                    <div class="min-w-0">

                        <p
                            class="
                                text-sm
                                font-medium
                                text-[var(--theme-text-strong)]
                            "
                        >
                            {{ $registro->usuario_nombre
                                ?: $registro->username
                                ?: 'Sistema'
                            }}
                        </p>

                        <p
                            class="
                                mt-1
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ \Illuminate\Support\Carbon::parse($registro->fecha_hora)->format('d/m/Y H:i') }}
                        </p>

                    </div>


                    <span
                        class="
                            shrink-0

                            px-2
                            py-1

                            rounded-full

                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]

                            text-[10px]
                            font-medium
                        "
                    >
                        {{ $registro->modulo }}
                    </span>

                </div>


                {{-- Acción --}}
                <div class="mt-4">

                    <p
                        class="
                            text-[10px]
                            uppercase
                            tracking-wide
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Acción
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ ucfirst(
                            strtolower(
                                str_replace('_', ' ', $registro->accion)
                            )
                        ) }}
                    </p>

                </div>


                {{-- Descripción --}}
                <div class="mt-3">

                    <p
                        class="
                            text-[10px]
                            uppercase
                            tracking-wide
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Descripción
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            leading-relaxed
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->descripcion ?: 'Sin descripción.' }}
                    </p>

                </div>

            </article>

        @empty

            <div
                class="
                    px-5
                    py-10

                    text-center
                    text-sm
                    text-[var(--theme-text-muted)]
                "
            >
                Todavía no hay eventos registrados en la bitácora.
            </div>

        @endforelse

    </div>

        </section>

    </div>



    {{-- ========================================================
        COLUMNA DERECHA
    ======================================================== --}}
    <aside class="space-y-5">

        {{-- ====================================================
            RESUMEN SEMANAL
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                p-4
            "
        >

            <h2
                class="
                    flex
                    items-center
                    gap-2

                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >

                <svg
                    class="
                        w-5
                        h-5
                        text-[var(--theme-primary)]
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M4 20V10"/>
                    <path d="M10 20V4"/>
                    <path d="M16 20v-7"/>
                    <path d="M22 20V7"/>
                </svg>

                Resumen de la semana
            </h2>


            @php
                $maxActividad = max(
                    1,
                    (int) $resumenSemanal->max(
                        fn ($dia) => max(
                            $dia['actividad'],
                            $dia['registros']
                        )
                    )
                );
            @endphp


            <div
                class="
                    mt-6

                    h-44

                    flex
                    items-end
                    justify-between
                    gap-2
                "
            >

                @foreach ($resumenSemanal as $dia)

                    @php
                        $actividadHeight =
                            max(
                                4,
                                ($dia['actividad'] / $maxActividad) * 100
                            );

                        $registroHeight =
                            max(
                                4,
                                ($dia['registros'] / $maxActividad) * 100
                            );
                    @endphp


                    <div
                        class="
                            flex-1
                            h-full

                            flex
                            flex-col
                            justify-end
                            items-center
                        "
                    >

                        <div
                            class="
                                flex
                                items-end
                                justify-center
                                gap-1

                                w-full
                                h-full
                            "
                        >

                            <div
                                class="
                                    w-2.5
                                    max-w-full

                                    rounded-t

                                    bg-[var(--theme-primary)]

                                    opacity-80
                                "
                                style="height: {{ $actividadHeight }}%"
                                title="{{ $dia['actividad'] }} eventos"
                            ></div>


                            <div
                                class="
                                    w-2.5
                                    max-w-full

                                    rounded-t

                                    bg-[var(--theme-success)]

                                    opacity-75
                                "
                                style="height: {{ $registroHeight }}%"
                                title="{{ $dia['registros'] }} registros"
                            ></div>

                        </div>


                        <span
                            class="
                                mt-2
                                text-[9px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $dia['label'] }}
                        </span>

                    </div>

                @endforeach

            </div>


            <div
                class="
                    mt-4

                    flex
                    flex-wrap
                    items-center
                    gap-4

                    text-[10px]
                    text-[var(--theme-text-muted)]
                "
            >

                <span class="flex items-center gap-1.5">

                    <span
                        class="
                            w-2
                            h-2

                            rounded-full

                            bg-[var(--theme-primary)]
                        "
                    ></span>

                    Actividad

                </span>


                <span class="flex items-center gap-1.5">

                    <span
                        class="
                            w-2
                            h-2

                            rounded-full

                            bg-[var(--theme-success)]
                        "
                    ></span>

                    Nuevos usuarios

                </span>

            </div>

        </section>



        {{-- ====================================================
            ALERTAS
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                p-4
            "
        >

            <h2
                class="
                    flex
                    items-center
                    gap-2

                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >

                <svg
                    class="
                        w-5
                        h-5
                        text-[var(--theme-primary)]
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>

                Alertas y seguridad
            </h2>


            <div class="mt-4 space-y-2">

                @foreach ($alertas as $alerta)

                    @php
                        $alertaClasses = match ($alerta['type']) {
                            'danger' =>
                                'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                            'warning' =>
                                'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                            'success' =>
                                'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                            default =>
                                'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',
                        };
                    @endphp


                    <div
                        class="
                            flex
                            items-center
                            gap-3

                            px-3
                            py-2.5

                            rounded-lg

                            border
                            border-[var(--theme-border)]
                        "
                    >

                        <div
                            class="
                                w-8
                                h-8
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                {{ $alertaClasses }}
                            "
                        >
                            <svg
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6"/>
                                <path d="M12 17h.01"/>
                            </svg>
                        </div>


                        <p
                            class="
                                min-w-0
                                flex-1

                                text-[11px]
                                leading-relaxed

                                text-[var(--theme-text)]
                            "
                        >
                            {{ $alerta['title'] }}
                        </p>

                    </div>

                @endforeach

            </div>

        </section>

    </aside>

</div>

{{-- ============================================================
MODAL: APROBAR USUARIO

============================================================ --}}
@if ($approvalModalOpen)

<div
    class="
        fixed
        inset-0
        z-[100]

        flex
        items-center
        justify-center

        px-4
        py-6
    "
    wire:click.self="closeApprovalModal"
>

    {{-- Fondo --}}
    <div
        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
        wire:click="closeApprovalModal"
    ></div>


    {{-- Modal --}}
    <div
        class="
            relative
            z-10

            w-full
            max-w-md

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl

            overflow-hidden
        "
    >

        {{-- Encabezado --}}
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

            <div>

                <h3
                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Aprobar usuario
                </h3>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Asigna un rol antes de activar la cuenta.
                </p>

            </div>


            <button
                type="button"
                wire:click="closeApprovalModal"
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


        <form wire:submit="approveUser">

            <div class="p-5 space-y-5">

                {{-- Usuario --}}
                <div
                    class="
                        p-4

                        rounded-lg

                        bg-[var(--theme-surface-soft)]

                        border
                        border-[var(--theme-border)]
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                w-10
                                h-10
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-primary-soft)]
                                text-[var(--theme-primary)]

                                font-semibold
                                text-sm
                            "
                        >
                            @if ($approvalUserName !== '')
                                {{ mb_strtoupper(mb_substr($approvalUserName, 0, 1)) }}
                            @else
                                U
                            @endif
                        </div>


                        <div class="min-w-0">

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]

                                    truncate
                                "
                            >
                                {{ $approvalUserName }}
                            </p>

                            <p
                                class="
                                    mt-0.5

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    truncate
                                "
                            >
                                {{ $approvalUserEmail }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Rol --}}
                <div>

                    <label
                        for="approvalRoleId"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Rol del usuario

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <select
                        id="approvalRoleId"
                        wire:model="approvalRoleId"
                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                        <option value="">
                            Selecciona un rol
                        </option>

                        @foreach ($roles as $rol)

                            <option value="{{ $rol->id_rol }}">
                                {{ $rol->nombre }}
                            </option>

                        @endforeach

                    </select>


                    @error('approvalRoleId')

                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>

                    @enderror


                    <p
                        class="
                            mt-2
                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        El usuario quedará activo inmediatamente después
                        de confirmar la aprobación.
                    </p>

                </div>

            </div>


            {{-- Acciones --}}
                <div
                    class="
                        px-5
                        py-4

                        border-t
                        border-[var(--theme-border)]

                        flex
                        items-center
                        justify-end
                        gap-2

                        bg-[var(--theme-surface-soft)]
                    "
                >

                    <button
                        type="button"
                        wire:click="closeApprovalModal"
                        wire:loading.attr="disabled"
                        wire:target="approveUser"
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

                            disabled:opacity-50

                            transition-colors
                        "
                    >
                        Cancelar
                    </button>


                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="approveUser"
                        class="
                            h-9
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
                            wire:loading.remove
                            wire:target="approveUser"
                        >
                            Aprobar usuario
                        </span>


                        <span
                            wire:loading
                            wire:target="approveUser"
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
                                    animate-spin
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                />
                            </svg>

                            Aprobando...

                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

@endif

</div>