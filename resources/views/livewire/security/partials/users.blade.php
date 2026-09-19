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

                                                data-open-approval-modal
                                                data-approval-user-id="{{ $usuario->id_usuario }}"
                                                data-approval-user-name="{{
                                                    trim(
                                                        implode(' ', array_filter([
                                                            $usuario->nombres,
                                                            $usuario->apellido_paterno,
                                                            $usuario->apellido_materno,
                                                        ]))
                                                    )
                                                }}"
                                                data-approval-user-email="{{ $usuario->correo }}"

                                                onclick="window.openApprovalUserModalFromButton(this)"

                                                class="
                                                    h-8
                                                    px-3

                                                    inline-flex
                                                    items-center
                                                    justify-center
                                                    gap-1.5

                                                    rounded-md

                                                    bg-[var(--theme-primary)]
                                                    text-white

                                                    text-[11px]
                                                    font-medium

                                                    hover:bg-[var(--theme-primary-hover)]

                                                    transition-colors
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

                                                Aprobar
                                            </button>

                                        @endif


                                        <button
                                            type="button"
                                            data-user-id="{{ $usuario->id_usuario }}"
                                            onclick="window.openUserDetailsModalFromButton(this)"
                                            title="Ver detalles"
                                            class="
                                                w-8
                                                h-8

                                                flex
                                                items-center
                                                justify-center

                                                rounded-md

                                                border
                                                border-[var(--theme-border)]

                                                bg-[var(--theme-surface)]

                                                text-[var(--theme-text-muted)]

                                                hover:bg-[var(--theme-primary-soft)]
                                                hover:text-[var(--theme-primary)]
                                                hover:border-[var(--theme-primary-border)]

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

                        {{-- ====================================================
                            ACCIONES MÓVIL
                        ==================================================== --}}
                        <div
                            class="
                                mt-4
                                pt-3

                                border-t
                                border-[var(--theme-border)]

                                flex
                                items-center
                                justify-end
                                gap-2
                            "
                        >

                            <button
                                type="button"
                                data-user-id="{{ $usuario->id_usuario }}"
                                onclick="window.openUserDetailsModalFromButton(this)"
                                class="
                                    h-9
                                    px-3

                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2

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

                                Ver detalles
                            </button>


                            @if ($estadoClave === 'PENDIENTE')

                                <button
                                    type="button"

                                    data-open-approval-modal
                                    data-approval-user-id="{{ $usuario->id_usuario }}"
                                    data-approval-user-name="{{
                                        trim(
                                            implode(' ', array_filter([
                                                $usuario->nombres,
                                                $usuario->apellido_paterno,
                                                $usuario->apellido_materno,
                                            ]))
                                        )
                                    }}"
                                    data-approval-user-email="{{ $usuario->correo }}"

                                    onclick="window.openApprovalUserModalFromButton(this)"

                                    class="
                                        h-9
                                        px-3

                                        inline-flex
                                        items-center
                                        justify-center
                                        gap-2

                                        rounded-md

                                        bg-[var(--theme-primary)]
                                        text-white

                                        text-xs
                                        font-medium

                                        hover:bg-[var(--theme-primary-hover)]

                                        transition-colors
                                    "
                                >
                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path d="M5 12l4 4L19 6"/>
                                    </svg>

                                    Aprobar
                                </button>

                            @endif

                        </div>

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