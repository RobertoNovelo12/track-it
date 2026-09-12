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
        Nota: las clases w-20, ml-20, hidden y justify-center se aplican
        vía JavaScript (toggleSidebar). Este comentario asegura que
        Tailwind las incluya en el build y no las elimine al purgar
        clases no usadas en el HTML estático.
        w-20 ml-20 hidden justify-center justify-start
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


    {{-- ============================================================
        CONTENIDO DERECHO
    ============================================================ --}}
    <div id="mainContent" class="ml-64 flex-1 min-h-screen transition-all duration-300 ease-in-out">

        {{-- ========================================================
            HEADER
        ======================================================== --}}
        <header
            class="
                h-16
                bg-[#FFFCFF]
                border-b border-[#50514F]/10
                flex items-center justify-between
                px-6
                sticky top-0 z-30
            "
        >

            {{-- Buscador --}}
            <div class="relative w-96">

                <svg
                    class="absolute left-3 top-1/2 -translate-y-1/2
                           w-4 h-4 text-[#50514F]/50"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>

                <input
                    type="search"
                    placeholder="Buscar por número de serie, Id o responsable..."
                    class="
                        w-full
                        bg-[#50514F]/5
                        rounded-full
                        border-0
                        pl-10 pr-4 py-2
                        text-xs
                        placeholder:text-[#50514F]/50
                        focus:ring-1 focus:ring-[#247BA0]
                    "
                >

            </div>


            {{-- Usuario --}}
            <div class="flex items-center gap-4">

                {{-- Notificaciones --}}
                <button
                    type="button"
                    class="relative text-[#50514F]/70 hover:text-[#247BA0]"
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
                </button>


                {{-- Información usuario --}}
                <div class="text-right">

                    <p class="text-[11px] font-medium text-[#50514F] uppercase">
                        {{ auth()->user()->nombres ?? auth()->user()->name }}
                        {{ auth()->user()->apellido_paterno ?? '' }}
                    </p>

                    <p class="text-[9px] text-[#50514F]/50">
                        {{ auth()->user()->puesto ?? 'Grand Palladium' }}
                    </p>

                </div>


                {{-- Avatar --}}
                <div
                    class="
                        w-9 h-9
                        rounded-full
                        border border-[#50514F]
                        flex items-center justify-center
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
    function applySidebarState(collapsed) {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('mainContent');
        const header = document.getElementById('sidebarHeader');
        const labels = document.querySelectorAll('.sidebar-label');
        const navLinks = document.querySelectorAll('.sidebar-nav-link');
        const logoWrap = document.getElementById('sidebarLogoWrap');

        if (collapsed) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');
            main.classList.remove('ml-64');
            main.classList.add('ml-20');
            header.classList.remove('justify-start');
            header.classList.add('justify-center');
            labels.forEach(el => el.classList.add('hidden'));
            navLinks.forEach(el => el.classList.add('justify-center'));
            logoWrap.classList.add('hidden');
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');
            main.classList.remove('ml-20');
            main.classList.add('ml-64');
            header.classList.remove('justify-center');
            header.classList.add('justify-start');
            labels.forEach(el => el.classList.remove('hidden'));
            navLinks.forEach(el => el.classList.remove('justify-center'));
            logoWrap.classList.remove('hidden');
        }
    }

    function toggleSidebar() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const next = !isCollapsed;
        localStorage.setItem('sidebarCollapsed', next);
        applySidebarState(next);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        applySidebarState(isCollapsed);
    });
</script>

@stack('scripts')

</body>
</html>