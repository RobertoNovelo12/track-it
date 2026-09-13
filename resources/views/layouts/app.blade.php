<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Sistema') | Grand Palladium</title>

    {{-- Fuente Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <script>
    (() => {
        const storageKey = 'trackit_theme';

        function getThemePreference() {
            try {
                const saved = localStorage.getItem(storageKey);

                if (
                    saved === 'light' ||
                    saved === 'dark' ||
                    saved === 'system'
                ) {
                    return saved;
                }
            } catch (error) {
                // Si localStorage no está disponible,
                // utilizamos el tema del sistema.
            }

            return 'system';
        }


        function resolveTheme(preference) {
            if (preference === 'system') {
                return window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches
                    ? 'dark'
                    : 'light';
            }

            return preference;
        }


        function applyTheme(preference = getThemePreference()) {
            const resolved = resolveTheme(preference);
            const html = document.documentElement;

            html.classList.toggle(
                'dark',
                resolved === 'dark'
            );

            html.dataset.themePreference = preference;

            html.style.colorScheme = resolved;
        }


        /*
        |--------------------------------------------------------------------------
        | Disponible para el botón que agregaremos después
        |--------------------------------------------------------------------------
        */

        window.setTrackItTheme = function (preference) {
            if (
                !['light', 'dark', 'system'].includes(preference)
            ) {
                return;
            }

            try {
                localStorage.setItem(
                    storageKey,
                    preference
                );
            } catch (error) {
                // Continuamos aunque no pueda guardarse.
            }

            applyTheme(preference);

            window.dispatchEvent(
                new CustomEvent(
                    'trackit-theme-changed',
                    {
                        detail: {
                            preference: preference
                        }
                    }
                )
            );
        };


        window.getTrackItTheme = getThemePreference;


        /*
        |--------------------------------------------------------------------------
        | Aplicar ANTES de pintar la página
        |--------------------------------------------------------------------------
        */

        applyTheme();


        /*
        |--------------------------------------------------------------------------
        | Si está en "Sistema", reaccionar al cambio del SO
        |--------------------------------------------------------------------------
        */

        const systemTheme = window.matchMedia(
            '(prefers-color-scheme: dark)'
        );

        systemTheme.addEventListener(
            'change',
            () => {
                if (
                    getThemePreference() === 'system'
                ) {
                    applyTheme('system');
                }
            }
        );
    })();
</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{--
        Nota: algunas clases se aplican vía JavaScript (toggleSidebar).
        Se dejan también aquí como referencia para que Tailwind pueda
        detectarlas durante el build.
        w-20 md:ml-20 hidden justify-center justify-start
        -translate-x-full translate-x-0
    --}}

<style>
    @media (min-width: 768px) {

        /*
        |--------------------------------------------------------------------------
        | Hamburguesa siempre en la misma posición
        |--------------------------------------------------------------------------
        */

        #sidebarHeader {
            position: relative;
        }

        #sidebarHeader > button {
            position: absolute;

            left: 2.5rem;
            top: 50%;

            transform: translate(-50%, -50%);

            flex-shrink: 0;

            z-index: 10;
        }


        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        #sidebarLogoWrap {
            margin-left: 3.5rem;
        }


        /*
        |--------------------------------------------------------------------------
        | Sidebar cerrado
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed #sidebar {
            width: 5rem !important;
        }

        html.sidebar-collapsed #mainContent {
            margin-left: 5rem !important;
        }

        html.sidebar-collapsed .sidebar-label,
        html.sidebar-collapsed #sidebarLogoWrap {
            display: none !important;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Iconos del menú
    |--------------------------------------------------------------------------
    */

    .sidebar-nav-link > svg {
        flex-shrink: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Logo según el tema
    |--------------------------------------------------------------------------
    */

    #sidebarLogo {
        transition:
            filter 200ms ease,
            opacity 200ms ease;
    }

    html.dark #sidebarLogo {
        filter: brightness(0) invert(1);
        opacity: 0.92;
    }
</style>


<script>
    /*
    |--------------------------------------------------------------------------
    | Aplicar estado ANTES de que se pinte la página
    |--------------------------------------------------------------------------
    */

    try {

        const desktop =
            window.matchMedia('(min-width: 768px)').matches;

        const collapsed =
            localStorage.getItem('sidebarCollapsed') === 'true';

        if (desktop && collapsed) {

            document.documentElement
                .classList
                .add('sidebar-collapsed');

        }

    } catch (error) {
        // Si localStorage no está disponible,
        // simplemente usamos el sidebar abierto.
    }
</script>
</head>

<body class="theme-bg theme-text font-['Poppins']">

<div class="min-h-screen flex">

    {{-- ============================================================
        SIDEBAR
    ============================================================ --}}
    <aside
        id="sidebar"
        class="fixed left-0 top-0 bottom-0
               w-64 theme-bg
               border-r border-[var(--theme-border)]
               flex flex-col z-40
               -translate-x-full md:translate-x-0
               transition-transform md:transition-[width] duration-300 ease-in-out"
    >

        {{-- Encabezado sidebar: hamburguesa + logo --}}
        <div
            id="sidebarHeader"
            class="h-28 flex items-center justify-start gap-10 px-4 border-b border-[var(--theme-border)] shrink-0"
        >

            {{-- Botón hamburguesa (colapsar sidebar) --}}
            <button
                type="button"
                onclick="toggleSidebar()"
                class="
                    shrink-0
                    text-[var(--theme-text-muted)]
                    hover:text-[var(--theme-primary)]
                    transition-colors
                "
                aria-label="Colapsar menú"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                </svg>
            </button>

            {{-- Logo: reemplazar con la imagen del logo --}}
            <a
                id="sidebarLogoWrap"
                href="{{ route('dashboard') }}"
                class="flex items-center min-w-0"
            >
                {{--
                    Coloca el archivo logo-grand-palladium.png dentro de:
                    public/images/logo-grand-palladium.png
                --}}
                <img
                    id="sidebarLogo"
                    src="{{ asset('images/logo-grand-palladium.png') }}"
                    alt="Grand Palladium Hotels & Resorts"
                    class="h-32 w-auto max-w-full object-contain transition-all duration-300"
                >
            </a>

        </div>


        {{-- Navegación --}}
        <nav id="sidebarNav" class="flex-1 px-4 py-6 space-y-1">

            {{-- Vista general --}}
            <a
                href="{{ route('dashboard') }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('dashboard')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M3 11.5L12 4l9 7.5"/>
                    <path d="M5 10v10h14V10"/>
                    <path d="M9 20v-6h6v6"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Vista General</span>
            </a>


            {{-- Equipos --}}
            <a
                href="{{ Route::has('equipos.index') ? route('equipos.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('equipos.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <rect x="3" y="4" width="18" height="13" rx="1"/>
                    <path d="M2 20h20"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Equipos Tecnológicos</span>
            </a>


            {{-- Asignaciones --}}
            <a
                href="{{ Route::has('asignaciones.index') ? route('asignaciones.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('asignaciones.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M4 7h15"/>
                    <path d="M16 4l3 3-3 3"/>
                    <path d="M20 17H5"/>
                    <path d="M8 14l-3 3 3 3"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Asignación/Mov</span>
            </a>


            {{-- Mantenimientos --}}
            <a
                href="{{ Route::has('mantenimientos.index') ? route('mantenimientos.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('mantenimientos.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2z"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Mantenimientos</span>
            </a>


            {{-- Reportes --}}
            <a
                href="{{ Route::has('reportes.index') ? route('reportes.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('reportes.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
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

                <span class="sidebar-label whitespace-nowrap">Reportes</span>
            </a>


            {{-- Seguridad --}}
            <a
                href="{{ Route::has('usuarios.index') ? route('usuarios.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('usuarios.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                    <circle cx="17" cy="9" r="2"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Seguridad y Roles</span>
            </a>


            {{-- Catálogos --}}
            <a
                href="{{ Route::has('catalogos.index') ? route('catalogos.index') : '#' }}"
                class="
                    sidebar-nav-link
                    flex items-center gap-3 px-3 py-3 rounded-md
                    text-sm transition-colors border-l-4

                    {{ request()->routeIs('catalogos.*')
                        ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium border-[var(--theme-primary)]'
                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] border-transparent'
                    }}
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M8 6h13"/>
                    <path d="M8 12h13"/>
                    <path d="M8 18h13"/>
                    <path d="M3 6l1 1 2-2"/>
                    <path d="M3 12l1 1 2-2"/>
                    <path d="M3 18l1 1 2-2"/>
                </svg>

                <span class="sidebar-label whitespace-nowrap">Catálogos Base</span>
            </a>

        </nav>


        {{-- Cerrar sesión --}}
        <div class="p-4 border-t border-[var(--theme-border)] shrink-0">

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button
                    type="submit"
                    class="
                        sidebar-nav-link
                        w-full
                        flex items-center gap-3
                        px-3 py-3
                        text-sm text-[var(--theme-text-muted)]
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    <svg
                        class="w-5 h-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M14 4h7v16h-7"/>
                    </svg>

                    <span class="sidebar-label whitespace-nowrap">Cerrar Sesión</span>
                </button>

            </form>

        </div>

    </aside>

    {{-- Overlay del drawer móvil --}}
    <div
        id="sidebarOverlay"
        onclick="toggleSidebar()"
        class="fixed inset-0 bg-[var(--theme-overlay)] z-30 hidden md:hidden"
    ></div>


    {{-- ============================================================
        CONTENIDO DERECHO
    ============================================================ --}}
    <div
    id="mainContent"
    class="ml-0 md:ml-64 flex-1 min-h-screen transition-[margin-left] duration-300 ease-in-out"
    >

        {{-- ========================================================
            HEADER
        ======================================================== --}}
            <header
                class="
                    h-16
                    theme-bg
                    border-b border-[var(--theme-border)]
                    flex
                    items-center
                    justify-between
                    gap-3
                    sm:gap-5
                    px-4
                    sm:px-6
                    sticky
                    top-0
                    z-30
                "
            >

            <div class="flex items-center gap-3 flex-1 min-w-0">

                {{-- Hamburguesa móvil: abre el drawer --}}
                <button
                    type="button"
                    onclick="toggleSidebar()"
                    class="md:hidden shrink-0 text-[var(--theme-text-muted)] hover:text-[var(--theme-primary)]"
                    aria-label="Abrir menú"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                    </svg>
                </button>


        {{-- ============================================================
            BUSCADOR GLOBAL DE EQUIPOS
        ============================================================ --}}
        <form
            action="{{ route('equipos.search') }}"
            method="GET"
            class="
                relative
                block
                flex-1
                min-w-0
                max-w-96
            "
        >

            {{-- Lupa --}}
            <svg
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
                stroke-width="1.5"
            >
                <circle cx="11" cy="11" r="7"/>
                <path d="M20 20l-4-4"/>
            </svg>


            {{-- Campo --}}
            <input
                type="search"
                name="q"
                value="{{ request()->routeIs('equipos.search') ? request('q') : '' }}"
                placeholder="Buscar equipos..."
                autocomplete="off"
                class="
                    w-full

                    bg-[var(--theme-surface-soft)]

                    rounded-full
                    border-0

                    pl-10
                    pr-4
                    py-2

                    text-xs
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    focus:ring-1
                    focus:ring-[var(--theme-primary)]
                "
            >

        </form>

            </div>


        {{-- ========================================================
            USUARIO / PERFIL
        ======================================================== --}}
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">

            {{-- ====================================================
                NOTIFICACIONES
            ==================================================== --}}
            <button
                type="button"
                class="
                    relative
                    shrink-0

                    w-9
                    h-9

                    flex
                    items-center
                    justify-center

                    rounded-full

                    text-[var(--theme-text-muted)]

                    hover:text-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    transition-colors
                "
                aria-label="Notificaciones"
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>

                {{-- Punto de notificación --}}
                <span
                    class="
                        absolute
                        top-1.5
                        right-1.5

                        w-1.5
                        h-1.5

                        bg-[var(--theme-danger)]
                        rounded-full
                    "
                ></span>
            </button>



            {{-- ====================================================
                MENÚ DE PERFIL
            ==================================================== --}}
                <div
                    x-data="{
                        open: false,
                        theme: window.getTrackItTheme
                            ? window.getTrackItTheme()
                            : 'system'
                    }"

                    @trackit-theme-changed.window="
                        theme = $event.detail.preference
                    "

                    @click.outside="open = false"
                    @keydown.escape.window="open = false"

                    class="relative"
                >

                {{-- =================================================
                    CÁPSULA
                ================================================= --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="
                        group

                        flex
                        items-center
                        gap-2

                        rounded-full

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        p-1

                        sm:pl-3

                        hover:border-[var(--theme-primary-border)]
                        hover:bg-[var(--theme-primary-soft-subtle)]

                        transition-all
                    "
                    :aria-expanded="open"
                    aria-haspopup="true"
                >

                    {{-- Nombre + propiedad --}}
                    <div
                        class="
                            hidden
                            sm:block

                            text-right
                            pl-1
                        "
                    >

                        <p
                            class="
                                max-w-36
                                truncate

                                text-[10px]
                                md:text-[11px]

                                leading-tight
                                font-semibold
                                uppercase
                                text-[var(--theme-text-strong)]
                            "
                        >
                            {{ auth()->user()->nombres ?? auth()->user()->name }}
                            {{ auth()->user()->apellido_paterno ?? '' }}
                        </p>

                        <p
                            class="
                                mt-0.5

                                text-[8px]
                                md:text-[9px]

                                leading-tight
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ auth()->user()->propiedad ?? 'Grand Palladium' }}
                        </p>

                    </div>


                    {{-- Avatar --}}
                    <div
                        class="
                            shrink-0

                            w-9
                            h-9

                            rounded-full

                            border
                            border-[var(--theme-primary-border-strong)]

                            flex
                            items-center
                            justify-center

                            bg-[var(--theme-primary-soft-subtle)]
                            text-[var(--theme-text-strong)]

                            group-hover:border-[var(--theme-primary)]

                            transition-colors
                        "
                    >
                        <svg
                            class="w-6 h-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21c0-5 3-8 8-8s8 3 8 8"/>
                        </svg>
                    </div>


                    {{-- Flecha --}}
                    <svg
                        class="
                            hidden
                            md:block

                            w-3.5
                            h-3.5

                            mr-1

                            text-[var(--theme-text-muted)]

                            transition-transform
                            duration-200
                        "
                        :class="open ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M6 9l6 6 6-6"/>
                    </svg>

                </button>



                {{-- =================================================
                    DROPDOWN
                ================================================= --}}
                <div
                    x-show="open"
                    x-cloak

                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"

                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"

                    class="
                        absolute
                        right-0
                        top-full

                        mt-2

                        w-[300px]
                        max-w-[calc(100vw-2rem)]

                        bg-[var(--theme-surface)]

                        border
                        border-[var(--theme-border)]

                        rounded-2xl

                        theme-shadow-xl

                        overflow-hidden

                        z-50
                    "
                >

                    {{-- =============================================
                        CABECERA DEL PERFIL
                    ============================================== --}}
                    <div class="p-4">

                        <div
                            class="
                                flex
                                items-center
                                gap-3
                            "
                        >

                            {{-- Avatar grande --}}
                            <div
                                class="
                                    shrink-0

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
                                    class="w-7 h-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21c0-5 3-8 8-8s8 3 8 8"/>
                                </svg>
                            </div>


                            {{-- Datos --}}
                            <div class="min-w-0">

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        uppercase
                                        text-[var(--theme-text-strong)]

                                        truncate
                                    "
                                >
                                    {{ auth()->user()->nombres ?? auth()->user()->name }}
                                    {{ auth()->user()->apellido_paterno ?? '' }}
                                </p>


                                <p
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-[var(--theme-text-muted)]
                                        truncate
                                    "
                                >
                                    {{ auth()->user()->puesto ?? 'Administrador TI' }}
                                </p>


                                <p
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-[var(--theme-text-muted)]
                                        truncate
                                    "
                                >
                                    {{ auth()->user()->propiedad ?? 'Grand Palladium' }}
                                </p>


                                @if (!empty(auth()->user()->email))

                                    <p
                                        class="
                                            mt-0.5
                                            text-[11px]
                                            text-[var(--theme-text-muted)]
                                            truncate
                                        "
                                    >
                                        {{ auth()->user()->email }}
                                    </p>

                                @endif

                            </div>

                        </div>

                    </div>



                    <div class="h-px bg-[var(--theme-border)] mx-4"></div>



                    {{-- =============================================
                        OPCIONES PRINCIPALES
                    ============================================== --}}
                    <div class="p-2">

                        {{-- Mi perfil --}}
                        <button
                            type="button"
                            class="
                                w-full

                                flex
                                items-center
                                gap-3

                                px-3
                                py-2.5

                                rounded-lg

                                text-sm
                                text-[var(--theme-text-strong)]

                                hover:bg-[var(--theme-primary-soft)]
                                hover:text-[var(--theme-primary)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-5 h-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21c0-5 3-8 8-8s8 3 8 8"/>
                            </svg>

                            <span>Mi perfil</span>
                        </button>


                        {{-- Notificaciones --}}
                        <button
                            type="button"
                            class="
                                w-full

                                flex
                                items-center
                                gap-3

                                px-3
                                py-2.5

                                rounded-lg

                                text-sm
                                text-[var(--theme-text-strong)]

                                hover:bg-[var(--theme-primary-soft)]
                                hover:text-[var(--theme-primary)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-5 h-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                <path d="M10 21h4"/>
                            </svg>

                            <span class="flex-1 text-left">
                                Notificaciones
                            </span>

                            <span
                                class="
                                    w-2
                                    h-2
                                    rounded-full
                                    bg-[var(--theme-danger)]
                                "
                            ></span>
                        </button>

                        {{-- ============================================================
                            APARIENCIA
                        ============================================================ --}}
                        <div
                            class="
                                px-3
                                py-2.5
                            "
                        >

                            {{-- Título --}}
                            <div
                                class="
                                    flex
                                    items-center
                                    gap-3

                                    text-sm
                                    text-[var(--theme-text-strong)]
                                "
                            >

                                {{-- Icono --}}
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <circle cx="12" cy="12" r="3"/>

                                    <path d="M12 2v2"/>
                                    <path d="M12 20v2"/>

                                    <path d="M4.93 4.93l1.41 1.41"/>
                                    <path d="M17.66 17.66l1.41 1.41"/>

                                    <path d="M2 12h2"/>
                                    <path d="M20 12h2"/>

                                    <path d="M4.93 19.07l1.41-1.41"/>
                                    <path d="M17.66 6.34l1.41-1.41"/>
                                </svg>
                                <span>
                                    Apariencia
                                </span>

                            </div>


                            {{-- ========================================================
                                OPCIONES
                            ======================================================== --}}
                            <div
                                class="
                                    grid
                                    grid-cols-3

                                    gap-2

                                    mt-3
                                "
                            >

                                {{-- Claro --}}
                                <button
                                    type="button"

                                    @click="
                                        window.setTrackItTheme('light');
                                        theme = 'light';
                                    "

                                    :class="
                                        theme === 'light'
                                            ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                            : 'border-[var(--theme-border)] bg-[var(--theme-surface)] text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                                    "

                                    class="
                                        h-9

                                        flex
                                        items-center
                                        justify-center
                                        gap-1.5

                                        border
                                        rounded-lg

                                        text-[11px]
                                        font-medium

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
                                        <circle cx="12" cy="12" r="4"/>

                                        <path d="M12 2v2"/>
                                        <path d="M12 20v2"/>
                                        <path d="M4.93 4.93l1.41 1.41"/>
                                        <path d="M17.66 17.66l1.41 1.41"/>
                                        <path d="M2 12h2"/>
                                        <path d="M20 12h2"/>
                                    </svg>

                                    Claro
                                </button>


                                {{-- Oscuro --}}
                                <button
                                    type="button"

                                    @click="
                                        window.setTrackItTheme('dark');
                                        theme = 'dark';
                                    "

                                    :class="
                                        theme === 'dark'
                                            ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                            : 'border-[var(--theme-border)] bg-[var(--theme-surface)] text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                                    "

                                    class="
                                        h-9

                                        flex
                                        items-center
                                        justify-center
                                        gap-1.5

                                        border
                                        rounded-lg

                                        text-[11px]
                                        font-medium

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
                                        <path
                                            d="
                                                M20 15.5
                                                A8 8 0 0 1 8.5 4
                                                A8 8 0 1 0 20 15.5
                                            "
                                        />
                                    </svg>

                                    Oscuro
                                </button>


                                {{-- Sistema --}}
                                <button
                                    type="button"

                                    @click="
                                        window.setTrackItTheme('system');
                                        theme = 'system';
                                    "

                                    :class="
                                        theme === 'system'
                                            ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                            : 'border-[var(--theme-border)] bg-[var(--theme-surface)] text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]'
                                    "

                                    class="
                                        h-9

                                        flex
                                        items-center
                                        justify-center
                                        gap-1.5

                                        border
                                        rounded-lg

                                        text-[11px]
                                        font-medium

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
                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="13"
                                            rx="1.5"
                                        />

                                        <path d="M8 21h8"/>
                                        <path d="M12 17v4"/>
                                    </svg>

                                    Sistema
                                </button>

                            </div>

                        </div>


                        {{-- Seguridad --}}
                        <button
                            type="button"
                            class="
                                w-full

                                flex
                                items-center
                                gap-3

                                px-3
                                py-2.5

                                rounded-lg

                                text-sm
                                text-[var(--theme-text-strong)]

                                hover:bg-[var(--theme-primary-soft)]
                                hover:text-[var(--theme-primary)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-5 h-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                            </svg>

                            <span>Seguridad</span>
                            </button>

                            {{-- Ayuda --}}
                            <button
                            type="button"
                            class="
                                w-full

                                flex
                                items-center
                                gap-3

                                px-3
                                py-2.5

                                rounded-lg

                                text-sm
                                text-[var(--theme-text-strong)]

                                hover:bg-[var(--theme-primary-soft)]
                                hover:text-[var(--theme-primary)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-5 h-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M9.8 9a2.4 2.4 0 114.4 1.3c-.7.9-2.2 1.2-2.2 2.7"/>
                                <path d="M12 17h.01"/>
                            </svg>

                            <span>Ayuda y soporte</span>
                            </button>

                            </div>



                    <div class="h-px bg-[var(--theme-border)] mx-4"></div>



                    {{-- =============================================
                        CERRAR SESIÓN
                    ============================================== --}}
                    <div class="p-2">

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="
                                    w-full

                                    flex
                                    items-center
                                    gap-3

                                    px-3
                                    py-2.5

                                    rounded-lg

                                    text-sm
                                    font-medium
                                    text-[var(--theme-danger)]

                                    hover:bg-[var(--theme-danger-soft)]

                                    transition-colors
                                "
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path d="M10 17l5-5-5-5"/>
                                    <path d="M15 12H3"/>
                                    <path d="M14 4h7v16h-7"/>
                                </svg>

                                <span>Cerrar sesión</span>
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

            </header>


            {{-- ========================================================
                CONTENIDO ESPECÍFICO DE CADA PÁGINA
            ======================================================== --}}
            <main class="p-6">

                @yield('content')

            </main>

        </div>

</div>

<script>
    function isMobileViewport() {
        return window.matchMedia('(max-width: 767px)').matches;
    }

function applyDesktopSidebarState(collapsed) {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');

    /*
    |--------------------------------------------------------------------------
    | Estado global
    |--------------------------------------------------------------------------
    */

    document.documentElement.classList.toggle(
        'sidebar-collapsed',
        collapsed
    );


    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    if (collapsed) {

        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-20');

        main.classList.remove('md:ml-64');
        main.classList.add('md:ml-20');

    } else {

        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-64');

        main.classList.remove('md:ml-20');
        main.classList.add('md:ml-64');

    }
}

function normalizeMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const labels = document.querySelectorAll('.sidebar-label');
    const logoWrap = document.getElementById('sidebarLogoWrap');

    /*
     * En móvil el drawer siempre utiliza
     * el ancho completo.
     */

    sidebar.classList.remove('w-20');
    sidebar.classList.add('w-64');

    /*
     * Restauramos textos y logo.
     */

    labels.forEach(el => {
        el.classList.remove('hidden');
    });

    logoWrap.classList.remove('hidden');
}

    function applyMobileSidebarState(open) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (open) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        }
    }

    function toggleSidebar() {
        if (isMobileViewport()) {
            const sidebar = document.getElementById('sidebar');
            const isOpen = sidebar.classList.contains('translate-x-0');

            normalizeMobileSidebar();
            applyMobileSidebarState(!isOpen);
            return;
        }

        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const next = !isCollapsed;

        localStorage.setItem('sidebarCollapsed', next);
        applyDesktopSidebarState(next);
    }

    document.addEventListener('DOMContentLoaded', function () {
        let wasMobile = isMobileViewport();

        if (wasMobile) {
            normalizeMobileSidebar();
            applyMobileSidebarState(false);
        } else {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            applyDesktopSidebarState(isCollapsed);
        }

        // Cierra el drawer al tocar un enlace del menú en móvil.
        document.getElementById('sidebarNav').addEventListener('click', function (e) {
            if (isMobileViewport() && e.target.closest('a')) {
                applyMobileSidebarState(false);
            }
        });

        // Solo reajusta cuando realmente se cruza el breakpoint md.
        window.addEventListener('resize', function () {
            const nowMobile = isMobileViewport();

            if (nowMobile === wasMobile) {
                return;
            }

            wasMobile = nowMobile;

            if (nowMobile) {
                normalizeMobileSidebar();
                applyMobileSidebarState(false);
            } else {
                applyMobileSidebarState(false);

                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                applyDesktopSidebarState(isCollapsed);
            }
        });
    });
</script>

@stack('scripts')

</body>
</html>