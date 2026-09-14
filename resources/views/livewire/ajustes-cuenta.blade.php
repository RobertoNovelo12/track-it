<div>

{{-- ============================================================
    TOAST
============================================================ --}}
<div
    x-data="{
        visible: false,
        message: '',

        show(message) {
            this.message = message;
            this.visible = true;

            setTimeout(() => {
                this.visible = false;
            }, 3000);
        }
    }"
    @perfil-actualizado.window="
        show(
            $event.detail.message
                ?? 'Tu información se actualizó correctamente.'
        )
    "
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="
        fixed
        top-5
        right-4
        sm:right-6

        z-[110]

        w-[calc(100%_-_2rem)]
        max-w-sm

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-success-border)]

        rounded-xl

        theme-shadow-xl

        px-4
        py-3

        flex
        items-start
        gap-3
    "
>

    <div
        class="
            shrink-0

            w-8
            h-8

            rounded-full

            flex
            items-center
            justify-center

            bg-[var(--theme-success-soft)]
            text-[var(--theme-success)]
        "
    >
        <svg
            class="w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M5 12l4 4L19 6"/>
        </svg>
    </div>


    <div class="min-w-0">

        <p
            class="
                text-sm
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Perfil actualizado
        </p>

        <p
            class="
                mt-0.5
                text-xs
                text-[var(--theme-text-muted)]
            "
            x-text="message"
        ></p>

    </div>

</div>

    {{-- ============================================================
        NAVEGACIÓN DE AJUSTES
    ============================================================ --}}

    @php
        $sections = [
            'cuenta' => [
                'label' => 'Cuenta',
                'icon' => 'user',
            ],

            'seguridad' => [
                'label' => 'Seguridad',
                'icon' => 'lock',
            ],

            'notificaciones' => [
                'label' => 'Notificaciones',
                'icon' => 'bell',
            ],

            'preferencias' => [
                'label' => 'Preferencias',
                'icon' => 'settings',
            ],
        ];
    @endphp


    <div class="mb-5">

    {{-- ========================================================
        MÓVIL - SELECT PERSONALIZADO
    ======================================================== --}}
    <div
        x-data="{ open: false }"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="
            relative
            sm:hidden
        "
    >

        {{-- Botón principal --}}
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="
                w-full
                h-11

                px-3

                flex
                items-center
                justify-between
                gap-3

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border-strong)]

                text-sm
                font-medium
                text-[var(--theme-text)]

                hover:border-[var(--theme-primary-border)]

                focus:outline-none
                focus:ring-1
                focus:ring-[var(--theme-primary)]

                transition-colors
            "
        >

            <span
                class="
                    flex
                    items-center
                    gap-3
                    min-w-0
                "
            >

                {{-- Icono --}}
                <span
                    class="
                        w-7
                        h-7
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
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <circle cx="12" cy="12" r="3"/>

                        <path
                            d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1-2.8 2.8-.1-.1
                            a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21h-4v-.1
                            a1.7 1.7 0 00-1-1.5 1.7 1.7 0 00-1.9.3l-.1.1L4.2 17
                            l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3v-4h.1
                            a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9L4.2 7 7 4.2
                            l.1.1a1.7 1.7 0 001.9.3 1.7 1.7 0 001-1.5V3h4v.1
                            a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1L19.8 7
                            l-.1.1a1.7 1.7 0 00-.3 1.9 1.7 1.7 0 001.5 1h.1v4h-.1
                            a1.7 1.7 0 00-1.5 1z"
                        />
                    </svg>
                </span>


                <span class="truncate">
                    {{ $sections[$section]['label'] }}
                </span>

            </span>


            {{-- Flecha --}}
            <svg
                class="
                    w-4
                    h-4
                    shrink-0

                    text-[var(--theme-text-muted)]

                    transition-transform
                    duration-200
                "
                :class="{ 'rotate-180': open }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M6 9l6 6 6-6"/>
            </svg>

        </button>



        {{-- Menú --}}
        <div
            x-show="open"
            x-cloak

            x-transition:enter="
                transition
                ease-out
                duration-150
            "
            x-transition:enter-start="
                opacity-0
                -translate-y-1
                scale-[0.98]
            "
            x-transition:enter-end="
                opacity-100
                translate-y-0
                scale-100
            "

            x-transition:leave="
                transition
                ease-in
                duration-100
            "
            x-transition:leave-start="
                opacity-100
                scale-100
            "
            x-transition:leave-end="
                opacity-0
                scale-[0.98]
            "

            class="
                absolute
                left-0
                right-0
                top-full

                mt-2

                z-50

                p-1.5

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border-strong)]

                theme-shadow-xl
            "
        >

            @foreach ($sections as $key => $item)

                <button
                    type="button"
                    wire:click="setSection('{{ $key }}')"
                    @click="open = false"
                    wire:key="settings-mobile-{{ $key }}"
                    class="
                        w-full

                        px-3
                        py-2.5

                        flex
                        items-center
                        justify-between
                        gap-3

                        rounded-lg

                        text-left
                        text-sm

                        transition-colors

                        {{ $section === $key
                            ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium'
                            : 'text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]'
                        }}
                    "
                >

                    <span>
                        {{ $item['label'] }}
                    </span>

                </button>

            @endforeach

        </div>

    </div>

        {{-- ========================================================
            TABLET / ESCRITORIO - PESTAÑAS
        ======================================================== --}}
        <div
            class="
                hidden
                sm:flex
                flex-wrap
                gap-2
            "
        >

            @foreach ($sections as $key => $item)

                <button
                    type="button"
                    wire:click="setSection('{{ $key }}')"
                    wire:key="settings-tab-{{ $key }}"
                    class="
                        h-9
                        px-3

                        inline-flex
                        items-center
                        justify-center
                        gap-2

                        rounded-md

                        border

                        text-xs
                        font-medium

                        transition-colors

                        {{ $section === $key
                            ? 'bg-[var(--theme-primary-soft)] border-[var(--theme-primary-border)] text-[var(--theme-primary)]'
                            : 'bg-[var(--theme-surface)] border-[var(--theme-border)] text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] hover:border-[var(--theme-border-strong)]'
                        }}
                    "
                >

                    @if ($item['icon'] === 'user')

                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M5 21c0-4 3-7 7-7s7 3 7 7"/>
                        </svg>

                    @elseif ($item['icon'] === 'lock')

                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <rect x="5" y="10" width="14" height="10" rx="2"/>
                            <path d="M8 10V7a4 4 0 018 0v3"/>
                        </svg>

                    @elseif ($item['icon'] === 'bell')

                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>

                    @else

                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M4 12h2M18 12h2M12 4v2M12 18v2"/>
                        </svg>

                    @endif

                    {{ $item['label'] }}

                </button>

            @endforeach

        </div>

    </div>


    {{-- ============================================================
        CUENTA
    ============================================================ --}}
    @if ($section === 'cuenta')

        <div
            class="
                grid
                grid-cols-1
                xl:grid-cols-[280px_minmax(0,1fr)]
                gap-5
            "
        >

            {{-- ========================================================
                RESUMEN DE PERFIL
            ======================================================== --}}
            <aside
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    p-5
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        items-center

                        text-center
                    "
                >

                    {{-- Avatar --}}
                    <div
                        class="
                            w-20
                            h-20

                            rounded-full

                            flex
                            items-center
                            justify-center

                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]

                            text-2xl
                            font-semibold
                            uppercase
                        "
                    >
                        {{ mb_substr($usuario->nombres ?? '?', 0, 1) }}
                        {{ mb_substr($usuario->apellido_paterno ?? '', 0, 1) }}
                    </div>


                    <h2
                        class="
                            mt-4

                            text-base
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ $nombreCompleto }}
                    </h2>


                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ '@' . $usuario->username }}
                    </p>


                    {{-- Estado --}}
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


                    <span
                        class="
                            mt-3

                            inline-flex
                            items-center

                            px-2.5
                            py-1

                            rounded-full

                            text-[10px]
                            font-medium

                            {{ $estadoClasses }}
                        "
                    >
                        {{ $usuario->estado_nombre ?? 'Sin estado' }}
                    </span>

                </div>


                <div
                    class="
                        mt-5
                        pt-5

                        border-t
                        border-[var(--theme-border)]

                        space-y-3
                    "
                >

                    <div>

                        <p
                            class="
                                text-[10px]
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Rol
                        </p>

                        <p
                            class="
                                mt-1
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $usuario->rol_nombre ?? 'Sin rol asignado' }}
                        </p>

                    </div>


                    <div>

                        <p
                            class="
                                text-[10px]
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Puesto
                        </p>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $usuario->puesto ?: 'Sin especificar' }}
                        </p>

                    </div>


                    <div>

                        <p
                            class="
                                text-[10px]
                                uppercase
                                tracking-wide
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Número de colaborador
                        </p>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $usuario->numero_colaborador ?: 'Sin asignar' }}
                        </p>

                    </div>

                </div>

            </aside>



            {{-- ========================================================
                INFORMACIÓN
            ======================================================== --}}
            <div class="space-y-5">

                {{-- ====================================================
                    INFORMACIÓN PERSONAL
                ==================================================== --}}
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
                            px-4
                            sm:px-5
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

                            <h2
                                class="
                                    text-sm
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Información personal
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Información básica asociada a tu cuenta.
                            </p>

                        </div>


                        {{-- Visual por ahora --}}
                        <button
                        x-data="{}"
                        type="button"
                        @click="$dispatch('abrir-edicion-perfil')"
                        class="
                            shrink-0

                            h-8
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
                            hover:border-[var(--theme-primary-border)]

                            transition-colors
                        "
                    >
                            <svg
                                class="w-3.5 h-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path d="M4 20h4l10-10-4-4L4 16v4z"/>
                                <path d="M13 7l4 4"/>
                            </svg>

                            <span class="hidden sm:inline">
                                Editar
                            </span>
                        </button>

                    </div>


                    <div
                        class="
                            divide-y
                            divide-[var(--theme-border)]
                        "
                    >

                        {{-- Nombre --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Nombre completo
                            </span>

                            <span
                                class="
                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $nombreCompleto }}
                            </span>
                        </div>


                        {{-- Correo --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Correo electrónico
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                    break-all
                                "
                            >
                                {{ $usuario->correo }}
                            </span>
                        </div>


                        {{-- Usuario --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Nombre de usuario
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->username }}
                            </span>
                        </div>


                        {{-- Teléfono --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Teléfono
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->telefono ?: 'Sin especificar' }}
                            </span>
                        </div>

                    </div>

                </section>



                {{-- ====================================================
                    INFORMACIÓN ORGANIZACIONAL
                ==================================================== --}}
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
                            px-4
                            sm:px-5
                            py-4

                            border-b
                            border-[var(--theme-border)]
                        "
                    >

                        <h2
                            class="
                                text-sm
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Información organizacional
                        </h2>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Datos relacionados con tu operación dentro del sistema.
                        </p>

                    </div>


                    <div
                        class="
                            divide-y
                            divide-[var(--theme-border)]
                        "
                    >

                        {{-- Sede --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Sede
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->sede_nombre ?: 'Sin asignar' }}
                            </span>
                        </div>


                        {{-- Área --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Área
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->area_nombre
                                    ?: 'Sin asignar'
                                }}
                            </span>
                        </div>


                        {{-- Departamento --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Departamento
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->departamento_nombre
                                    ?: 'Sin asignar'
                                }}
                            </span>
                        </div>


                        {{-- Rol --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Rol
                            </span>

                            <span
                                class="
                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->rol_nombre ?? 'Sin rol asignado' }}
                            </span>
                        </div>


                        {{-- Alta --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Fecha de alta
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->fecha_registro
                                    ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                                    : '—'
                                }}
                            </span>
                        </div>


                        {{-- Último acceso --}}
                        <div
                            class="
                                px-4
                                sm:px-5
                                py-3

                                grid
                                grid-cols-1
                                sm:grid-cols-[180px_1fr]

                                gap-1
                                sm:gap-4
                            "
                        >
                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Último acceso
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $usuario->ultimo_acceso
                                    ? \Illuminate\Support\Carbon::parse($usuario->ultimo_acceso)->format('d/m/Y H:i')
                                    : 'Sin registro'
                                }}
                            </span>
                        </div>

                    </div>

                </section>

            </div>

        </div>



    {{-- ============================================================
        SECCIONES PENDIENTES
    ============================================================ --}}
    @else

        <section
            class="
                min-h-72

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                flex
                flex-col
                items-center
                justify-center

                px-6
                py-12

                text-center
            "
        >

            <div
                class="
                    w-12
                    h-12

                    rounded-full

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
                    <path d="M12 6v6l4 2"/>
                    <circle cx="12" cy="12" r="9"/>
                </svg>
            </div>


            <h2
                class="
                    mt-4
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                {{ $sections[$section]['label'] }}
            </h2>


            <p
                class="
                    mt-1
                    max-w-sm

                    text-xs
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            >
                Esta sección estará disponible próximamente.
            </p>

        </section>

    @endif

        {{-- ============================================================
            MODAL: EDITAR PERFIL
        ============================================================ --}}
        <div
            x-data="{ open: false }"
            @abrir-edicion-perfil.window="open = true"
            @perfil-actualizado.window="open = false"
            x-show="open"
            x-cloak

            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"

            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"

            @keydown.escape.window="
                if (open) {
                    open = false;
                    $wire.closeEditProfileModal();
                }
            "

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
        >

            {{-- Fondo --}}
            <div
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"

                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"

                @click="
                    open = false;
                    $wire.closeEditProfileModal();
                "

                class="
                    absolute
                    inset-0

                    bg-[var(--theme-overlay)]
                    backdrop-blur-[2px]
                "
            ></div>


            {{-- Modal --}}
            <div
                class="
                    relative
                    z-10

                    w-full
                    max-w-lg

                    max-h-[90vh]
                    overflow-y-auto

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    theme-shadow-xl
                "
            >

                {{-- Cabecera --}}
                <div
                    class="
                        sticky
                        top-0
                        z-10

                        px-5
                        py-4

                        bg-[var(--theme-surface)]

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
                            Editar información personal
                        </h3>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Actualiza los datos personales de tu cuenta.
                        </p>

                    </div>


                    <button
                        type="button"
                        @click="
                                open = false;
                                $wire.closeEditProfileModal();
                            "
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


                <form wire:submit="updateProfile">

                    <div class="p-5 space-y-4">

                        {{-- Nombre --}}
                        <div>

                            <label
                                for="editNombres"
                                class="
                                    block
                                    mb-1.5

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                Nombre

                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <input
                                id="editNombres"
                                type="text"
                                wire:model="editNombres"
                                autocomplete="given-name"
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

                                    placeholder:text-[var(--theme-text-muted)]

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]
                                "
                            >

                            @error('editNombres')
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

                        </div>


                        {{-- Apellido paterno --}}
                        <div>

                            <label
                                for="editApellidoPaterno"
                                class="
                                    block
                                    mb-1.5

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                Apellido paterno

                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <input
                                id="editApellidoPaterno"
                                type="text"
                                wire:model="editApellidoPaterno"
                                autocomplete="family-name"
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

                            @error('editApellidoPaterno')
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

                        </div>


                        {{-- Apellido materno --}}
                        <div>

                            <label
                                for="editApellidoMaterno"
                                class="
                                    block
                                    mb-1.5

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                Apellido materno

                                <span
                                    class="
                                        font-normal
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    (opcional)
                                </span>
                            </label>

                            <input
                                id="editApellidoMaterno"
                                type="text"
                                wire:model="editApellidoMaterno"
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

                            @error('editApellidoMaterno')
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

                        </div>


                        {{-- Teléfono --}}
                        <div>

                            <label
                                for="editTelefono"
                                class="
                                    block
                                    mb-1.5

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text)]
                                "
                            >
                                Teléfono

                                <span
                                    class="
                                        font-normal
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    (opcional)
                                </span>
                            </label>

                            <input
                                id="editTelefono"
                                type="tel"
                                wire:model="editTelefono"
                                autocomplete="tel"
                                placeholder="Ej. 998 123 4567"
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

                                    placeholder:text-[var(--theme-text-muted)]

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]
                                "
                            >

                            @error('editTelefono')
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

                        </div>


                        {{-- Información no editable --}}
                        <div
                            class="
                                p-3

                                rounded-lg

                                bg-[var(--theme-primary-soft-subtle)]

                                border
                                border-[var(--theme-primary-border)]

                                flex
                                items-start
                                gap-2.5
                            "
                        >

                            <svg
                                class="
                                    w-4
                                    h-4
                                    shrink-0
                                    mt-0.5

                                    text-[var(--theme-primary)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v6"/>
                                <path d="M12 7h.01"/>
                            </svg>


                            <p
                                class="
                                    text-[11px]
                                    leading-relaxed
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                El correo, usuario, puesto, rol, área y departamento
                                deben ser gestionados por un administrador.
                            </p>

                        </div>

                    </div>


                    {{-- Acciones --}}
                    <div
                        class="
                            sticky
                            bottom-0

                            px-5
                            py-4

                            bg-[var(--theme-surface-soft)]

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
                            @click="
                                open = false;
                                $wire.closeEditProfileModal();
                            "
                            wire:loading.attr="disabled"
                            wire:target="updateProfile"
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
                            wire:target="updateProfile"
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
                                wire:target="updateProfile"
                            >
                                Guardar cambios
                            </span>


                            <span
                                wire:loading
                                wire:target="updateProfile"
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

                                Guardando...
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

{{-- FIN ROOT LIVEWIRE --}}
</div>