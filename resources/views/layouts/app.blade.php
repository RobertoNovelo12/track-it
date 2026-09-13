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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{--
        Nota: algunas clases se aplican vía JavaScript (toggleSidebar).
        Se dejan también aquí como referencia para que Tailwind pueda
        detectarlas durante el build.
        w-20 md:ml-20 hidden justify-center justify-start
        -translate-x-full translate-x-0
    --}}
</head>

<body class="bg-[#FFFCFF] text-[#50514F] font-['Poppins']">

<div class="min-h-screen flex">

    {{-- ============================================================
        SIDEBAR
    ============================================================ --}}
    <aside
        id="sidebar"
        class="fixed left-0 top-0 bottom-0
               w-64 bg-[#FFFCFF]
               border-r border-[#50514F]/10
               flex flex-col z-40
               -translate-x-full md:translate-x-0
               transition-all duration-300 ease-in-out overflow-hidden"
    >

        {{-- Encabezado sidebar: hamburguesa + logo --}}
        <div
            id="sidebarHeader"
            class="h-28 flex items-center justify-start gap-10 px-4 border-b border-[#50514F]/10 shrink-0"
        >

            {{-- Botón hamburguesa (colapsar sidebar) --}}
            <button
                type="button"
                onclick="toggleSidebar()"
                class="shrink-0 text-[#50514F]/70 hover:text-[#247BA0]"
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
                        ? 'bg-[#247BA0]/10 text-[#247BA0] font-medium border-[#247BA0]'
                        : 'text-[#50514F]/80 hover:bg-[#50514F]/5 border-transparent'
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
        <div class="p-4 border-t border-[#50514F]/10 shrink-0">

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button
                    type="submit"
                    class="
                        sidebar-nav-link
                        w-full
                        flex items-center gap-3
                        px-3 py-3
                        text-sm text-[#50514F]/70
                        hover:text-[#247BA0]
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
        class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"
    ></div>


    {{-- ============================================================
        CONTENIDO DERECHO
    ============================================================ --}}
    <div id="mainContent" class="ml-0 md:ml-64 flex-1 min-h-screen transition-all duration-300 ease-in-out">

        {{-- ========================================================
            HEADER
        ======================================================== --}}
            <header
                class="
                    h-16
                    bg-[#FFFCFF]
                    border-b border-[#50514F]/10
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
                    class="md:hidden shrink-0 text-[#50514F]/70 hover:text-[#247BA0]"
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

                    text-[#50514F]/50
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

                    bg-[#50514F]/5

                    rounded-full
                    border-0

                    pl-10
                    pr-4
                    py-2

                    text-xs
                    text-[#50514F]

                    placeholder:text-[#50514F]/50

                    focus:ring-1
                    focus:ring-[#247BA0]
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

                    text-[#50514F]/70

                    hover:text-[#247BA0]
                    hover:bg-[#247BA0]/5

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

                        bg-red-500
                        rounded-full
                    "
                ></span>
            </button>



            {{-- ====================================================
                MENÚ DE PERFIL
            ==================================================== --}}
            <div
                x-data="{ open: false }"
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
                        border-[#50514F]/10

                        bg-white

                        p-1

                        sm:pl-3

                        hover:border-[#247BA0]/30
                        hover:bg-[#247BA0]/[0.03]

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
                                text-[#25344A]
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
                                text-[#50514F]/45
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
                            border-[#247BA0]/50

                            flex
                            items-center
                            justify-center

                            bg-[#247BA0]/5
                            text-[#25344A]

                            group-hover:border-[#247BA0]

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

                            text-[#50514F]/40

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

                        bg-white

                        border
                        border-[#50514F]/10

                        rounded-2xl

                        shadow-xl
                        shadow-black/10

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

                                    bg-[#247BA0]/10
                                    text-[#247BA0]
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
                                        text-[#25344A]

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
                                        text-[#50514F]/65
                                        truncate
                                    "
                                >
                                    {{ auth()->user()->puesto ?? 'Administrador TI' }}
                                </p>


                                <p
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-[#50514F]/55
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
                                            text-[#50514F]/45
                                            truncate
                                        "
                                    >
                                        {{ auth()->user()->email }}
                                    </p>

                                @endif

                            </div>

                        </div>

                    </div>



                    <div class="h-px bg-[#50514F]/10 mx-4"></div>



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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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
                                    bg-red-500
                                "
                            ></span>
                        </button>


                        {{-- Preferencias --}}
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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6 1.7 1.7 0 00-.4 1.1V21H9.6v-.1A1.7 1.7 0 009 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1 1.7 1.7 0 00-1.1-.4H3V9.6h.1A1.7 1.7 0 004.6 9a1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6 1.7 1.7 0 00.4-1.1V3h4v.1A1.7 1.7 0 0015 4.6a1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9a1.7 1.7 0 00.6 1 1.7 1.7 0 001.1.4h.1v4h-.1a1.7 1.7 0 00-1.7.6z"/>
                            </svg>

                            <span>Preferencias</span>
                        </button>


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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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

                    </div>



                    <div class="h-px bg-[#50514F]/10 mx-4"></div>



                    {{-- =============================================
                        OPCIONES SECUNDARIAS
                    ============================================== --}}
                    <div class="p-2">

                        {{-- Cambiar propiedad --}}
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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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
                                <path d="M4 21h16"/>
                                <path d="M6 21V7l6-4 6 4v14"/>
                                <path d="M9 10h2"/>
                                <path d="M13 10h2"/>
                                <path d="M9 14h2"/>
                                <path d="M13 14h2"/>
                            </svg>

                            <span>Cambiar propiedad</span>
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
                                text-[#25344A]

                                hover:bg-[#247BA0]/5
                                hover:text-[#247BA0]

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



                    <div class="h-px bg-[#50514F]/10 mx-4"></div>



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
                                    text-red-500

                                    hover:bg-red-50

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
        const header = document.getElementById('sidebarHeader');
        const labels = document.querySelectorAll('.sidebar-label');
        const navLinks = document.querySelectorAll('.sidebar-nav-link');
        const logoWrap = document.getElementById('sidebarLogoWrap');

        if (collapsed) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');

            main.classList.remove('md:ml-64');
            main.classList.add('md:ml-20');

            header.classList.remove('justify-start');
            header.classList.add('justify-center');

            labels.forEach(el => el.classList.add('hidden'));
            navLinks.forEach(el => el.classList.add('justify-center'));
            logoWrap.classList.add('hidden');
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            main.classList.remove('md:ml-20');
            main.classList.add('md:ml-64');

            header.classList.remove('justify-center');
            header.classList.add('justify-start');

            labels.forEach(el => el.classList.remove('hidden'));
            navLinks.forEach(el => el.classList.remove('justify-center'));
            logoWrap.classList.remove('hidden');
        }
    }

    function normalizeMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const header = document.getElementById('sidebarHeader');
        const labels = document.querySelectorAll('.sidebar-label');
        const navLinks = document.querySelectorAll('.sidebar-nav-link');
        const logoWrap = document.getElementById('sidebarLogoWrap');

        // En móvil el drawer siempre usa su ancho completo,
        // aunque en escritorio se haya guardado como colapsado.
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-64');

        header.classList.remove('justify-center');
        header.classList.add('justify-start');

        labels.forEach(el => el.classList.remove('hidden'));
        navLinks.forEach(el => el.classList.remove('justify-center'));
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