@php
    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS DEL SIDEBAR
    |--------------------------------------------------------------------------
    |
    | Centralizamos aquí las opciones principales para evitar repetir
    | el mismo HTML siete veces.
    |
    */

    /*
     * Submenú de Equipos Tecnológicos: un enlace por tipo de equipo
     * que abre el listado ya filtrado (?tipo_activo=ID).
     * Se cachea en el módulo «catalogos» (solo arrays).
     */
    $tiposEquipoMenu = [];

    if (Route::has('equipos.index')) {
        try {
            $tiposEquipoMenu = app(\App\Services\SystemCacheService::class)->rememberSimple(
                'catalogos',
                'sidebar.tipos_equipo',
                fn (): array => \Illuminate\Support\Facades\DB::table('tipos_equipo')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_tipo_equipo', 'nombre'])
                    ->map(fn ($tipo): array => [
                        'id' => (int) $tipo->id_tipo_equipo,
                        'nombre' => (string) $tipo->nombre,
                    ])
                    ->all()
            );
        } catch (\Throwable $e) {
            report($e);
            $tiposEquipoMenu = [];
        }
    }

    $tipoActivoMenu = request()->routeIs('equipos.index')
        ? (string) request()->query('tipo_activo', '')
        : '';

    $submenuEquipos = array_map(
        fn (array $tipo): array => [
            'label' => $tipo['nombre'],
            'url' => route('equipos.index', ['tipo_activo' => $tipo['id']]),
            'active' => $tipoActivoMenu === (string) $tipo['id'],
        ],
        $tiposEquipoMenu
    );

    $sidebarItems = [
        [
            'label' => 'Dashboard',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'icon' => 'dashboard',
        ],

        [
            'label' => 'Inventario',
            'url' => Route::has('equipos.index')
                ? route('equipos.index')
                : '#',
            'active' => request()->routeIs('equipos.*'),
            'icon' => 'equipos',
            'submenu' => $submenuEquipos,
            'submenuClave' => 'equipos',
        ],

        [
            'label' => 'Movimientos',
            'url' => Route::has('asignaciones.index')
                ? route('asignaciones.index')
                : '#',
            'active' => request()->routeIs('asignaciones.*'),
            'icon' => 'asignaciones',
            'permiso' => 'gestionar',
        ],

        [
            'label' => 'Mantenimientos',
            'url' => Route::has('mantenimientos.index')
                ? route('mantenimientos.index')
                : '#',
            'active' => request()->routeIs('mantenimientos.*'),
            'icon' => 'mantenimientos',
            'permiso' => 'gestionar',
        ],

        [
            'label' => 'Reportes',
            'url' => Route::has('reportes.index')
                ? route('reportes.index')
                : '#',
            'active' => request()->routeIs('reportes.*'),
            'icon' => 'reportes',
            'permiso' => 'gestionar',
        ],

        [
            'label' => 'Seguridad',
            'url' => Route::has('usuarios.index')
                ? route('usuarios.index')
                : '#',
            'active' => request()->routeIs('usuarios.*'),
            'icon' => 'seguridad',
            'permiso' => 'administrar',
        ],

        [
            'label' => 'Administración',
            'url' => Route::has('catalogos.index')
                ? route('catalogos.index')
                : '#',
            'active' => request()->routeIs('catalogos.*'),
            'icon' => 'catalogos',
            'permiso' => 'gestionar',
        ],
    ];

    /*
     * Solo las opciones que el rol del usuario puede abrir
     * (permisos definidos en AppServiceProvider).
     */
    $sidebarItems = array_values(array_filter(
        $sidebarItems,
        fn (array $item): bool => empty($item['permiso'])
            || \Illuminate\Support\Facades\Gate::allows($item['permiso'])
    ));
@endphp


{{-- ============================================================
    SIDEBAR
============================================================ --}}
<aside
    id="sidebar"

    class="
        fixed
        left-0
        top-0
        bottom-0

        w-64

        theme-bg

        border-r
        border-[var(--theme-border)]

        flex
        flex-col

        z-40

        -translate-x-full
        md:translate-x-0

        transition-transform
        md:transition-[width]

        duration-200
        ease-out
    "
>

    {{-- ========================================================
        CABECERA
    ======================================================== --}}
    <div
        id="sidebarHeader"

        class="
            h-24

            shrink-0

            flex
            items-center
            justify-between

            gap-3

            px-5
        "
    >

        {{-- ====================================================
            LOGO
        ==================================================== --}}
        @persist('sidebar-logo')

            <a
                id="sidebarLogoWrap"

                href="{{ route('dashboard') }}"

                wire:navigate.hover

                class="
                    flex
                    items-center

                    min-w-0
                "
            >
                @php
                    $organizacion = app(\App\Services\OrganizacionService::class);
                @endphp

                <img
                    id="sidebarLogo"

                    src="{{ $organizacion->logoUrl() }}"

                    alt="{{ $organizacion->nombre() }}"

                    data-dark-mode="{{ $organizacion->logoModoOscuro() }}"

                    style="--org-logo-w: {{ $organizacion->logoAncho(135) }}px; --org-logo-bg: {{ $organizacion->logoColorFondo() }};"

                    class="
                        org-logo

                        h-auto

                        object-contain

                        transition-all
                        duration-200
                    "
                >
            </a>

        @endpersist


        {{-- ====================================================
            COLAPSAR
        ==================================================== --}}
        <button
            id="sidebarCollapseButton"

            type="button"

            onclick="toggleSidebar()"

            class="
                shrink-0

                w-9
                h-9

                flex
                items-center
                justify-center

                rounded-lg

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]

                text-[var(--theme-text-muted)]

                shadow-[0_1px_3px_rgba(15,23,42,0.04)]

                hover:text-[var(--theme-text-strong)]
                hover:border-[var(--theme-border-strong)]

                transition-all
            "

            aria-label="Colapsar menú"

            title="Colapsar menú (Ctrl+B)"
        >
            <svg
                class="
                    w-4
                    h-4
                "

                viewBox="0 0 24 24"

                fill="none"

                stroke="currentColor"

                stroke-width="1.6"

                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M15 6l-6 6 6 6"/>
                <path d="M4 4v16"/>
            </svg>
        </button>

    </div>


    {{-- ========================================================
        BUSCADOR COMPACTO
        --------------------------------------------------------
        Permanecerá oculto por ahora.

        En el siguiente paso aparecerá únicamente cuando el
        sidebar esté colapsado.
    ======================================================== --}}
    <button
        id="sidebarCollapsedSearchButton"

        type="button"

        class="
            hidden

            w-10
            h-10

            mx-auto
            mb-3

            items-center
            justify-center

            rounded-lg

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]

            text-[var(--theme-text-muted)]
        "

        aria-label="Abrir búsqueda rápida"

        title="Búsqueda rápida (Ctrl+K)"
    >
        <svg
            class="
                w-[18px]
                h-[18px]
            "

            viewBox="0 0 24 24"

            fill="none"

            stroke="currentColor"

            stroke-width="1.6"

            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle
                cx="11"
                cy="11"
                r="7"
            />

            <path
                d="M20 20l-4-4"
            />
        </svg>
    </button>


    {{-- La búsqueda rápida (Ctrl K) está en la barra superior (layouts/app). --}}



    {{-- ========================================================
        TÍTULO MENÚ
    ======================================================== --}}
    <div
        class="
            sidebar-expanded-only

            shrink-0

            px-5
            pb-2

            text-[11px]
            font-medium

            text-[var(--theme-text-muted)]
        "
    >
        Menú
    </div>


    {{-- ========================================================
        NAVEGACIÓN
    ======================================================== --}}
    <nav
        id="sidebarNav"

        class="
            flex-1

            min-h-0

            overflow-y-auto

            px-3

            space-y-0.5
        "
    >

        @foreach ($sidebarItems as $item)

            @php
                $tieneSubmenu = ! empty($item['submenu']);
                $submenuActivo = $tieneSubmenu
                    && collect($item['submenu'])->contains('active', true);
            @endphp

            {{-- Elemento con submenú: el enlace lleva al listado sin filtros
                y la flecha de la derecha solo despliega las opciones. --}}
            <div
                @if ($tieneSubmenu)
                    x-data="{
                        abierto: {{ $submenuActivo ? 'true' : 'false' }},
                        clave: 'trackit_submenu_{{ $item['submenuClave'] }}',
                        init() {
                            if (! {{ $submenuActivo ? 'true' : 'false' }}) {
                                try { this.abierto = localStorage.getItem(this.clave) === '1'; } catch (e) {}
                            }
                        },
                        alternar() {
                            this.abierto = ! this.abierto;
                            try { localStorage.setItem(this.clave, this.abierto ? '1' : '0'); } catch (e) {}
                        },
                    }"
                @endif
                class="relative"
            >

            <a
                href="{{ $item['url'] }}"
                @if ($item['active']) aria-current="page" @endif

                wire:navigate.hover

                class="
                    sidebar-nav-link

                    {{ $item['active']
                        ? 'sidebar-nav-active'
                        : ''
                    }}

                    min-h-8

                    flex
                    items-center

                    gap-3

                    px-2.5

                    rounded-md

                    text-sm
                "
            >

                {{-- ============================================
                    ICONO
                ============================================ --}}
                @switch($item['icon'])

                    {{-- Dashboard --}}
                    @case('dashboard')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M3 11.5L12 4l9 7.5"/>
                            <path d="M5 10v10h14V10"/>
                            <path d="M9 20v-6h6v6"/>
                        </svg>

                        @break


                    {{-- Equipos --}}
                    @case('equipos')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            {{-- Caja de inventario --}}
                            <path d="M21 8l-9-5-9 5 9 5 9-5z"/>
                            <path d="M3 8v8l9 5 9-5V8"/>
                            <path d="M12 13v8"/>
                        </svg>

                        @break


                    {{-- Asignaciones --}}
                    @case('asignaciones')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M4 7h15"/>
                            <path d="M16 4l3 3-3 3"/>

                            <path d="M20 17H5"/>
                            <path d="M8 14l-3 3 3 3"/>
                        </svg>

                        @break


                    {{-- Mantenimientos --}}
                    @case('mantenimientos')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="
                                    M14.7 6.3
                                    a4 4 0 00-5.4 5.4
                                    L3 18
                                    v3
                                    h3
                                    l6.3-6.3
                                    a4 4 0 005.4-5.4
                                    l-2.5 2.5
                                    -2-2z
                                "
                            />
                        </svg>

                        @break


                    {{-- Reportes --}}
                    @case('reportes')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M4 20h16"/>
                            <path d="M7 17V9"/>
                            <path d="M12 17V4"/>
                            <path d="M17 17v-6"/>
                        </svg>

                        @break


                    {{-- Seguridad --}}
                    @case('seguridad')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="
                                    M12 3
                                    l7 3
                                    v5
                                    c0 5-3 8-7 10
                                    -4-2-7-5-7-10
                                    V6
                                    l7-3z
                                "
                            />
                        </svg>

                        @break


                    {{-- Administración --}}
                    @case('catalogos')

                        <svg
                            class="
                                w-[18px]
                                h-[18px]

                                text-[var(--theme-text-muted)]
                            "

                            viewBox="0 0 24 24"

                            fill="none"

                            stroke="currentColor"

                            stroke-width="1.6"

                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="4"
                                y="3"

                                width="16"
                                height="18"

                                rx="2"
                            />

                            <path d="M8 8h8"/>
                            <path d="M8 12h8"/>
                            <path d="M8 16h5"/>
                        </svg>

                        @break

                @endswitch


                {{-- ============================================
                    TEXTO
                ============================================ --}}
                <span
                    class="
                        sidebar-label

                        flex-1

                        min-w-0

                        truncate
                        whitespace-nowrap

                        {{ $item['active']
                            ? 'font-semibold text-[var(--theme-text-strong)]'
                            : 'font-medium text-[var(--theme-text)]'
                        }}
                    "
                >
                    {{ $item['label'] }}
                </span>

                {{-- Espacio para la flecha del submenú --}}
                @if ($tieneSubmenu)
                    <span class="sidebar-expanded-only w-6 shrink-0" aria-hidden="true"></span>
                @endif

            </a>

            @if ($tieneSubmenu)
                <button
                    type="button"
                    x-on:click.stop.prevent="alternar()"
                    x-bind:aria-expanded="abierto ? 'true' : 'false'"
                    aria-label="Mostrar tipos de equipo"
                    title="Tipos de equipo"
                    class="
                        sidebar-expanded-only
                        sidebar-submenu-toggle

                        absolute
                        right-1.5
                        top-1

                        w-6
                        h-6

                        flex
                        items-center
                        justify-center

                        rounded-md

                        text-[var(--theme-text-muted)]

                        hover:bg-[var(--theme-surface)]
                        hover:text-[var(--theme-text-strong)]

                        transition-colors
                    "
                >
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        x-bind:class="abierto ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <div
                    x-show="abierto"
                    x-collapse.duration.200ms
                    @unless ($submenuActivo) x-cloak @endunless
                    class="sidebar-submenu"
                >
                    <div class="ml-[1.15rem] mt-1 mb-1 pl-3 border-l border-[var(--theme-border)] space-y-0.5">
                        @foreach ($item['submenu'] as $sub)
                            <a
                                href="{{ $sub['url'] }}"
                                @if ($sub['active']) aria-current="page" @endif
                                wire:navigate.hover
                                class="
                                    block

                                    truncate

                                    px-3
                                    py-1.5

                                    rounded-md

                                    text-[13px]

                                    transition-colors

                                    {{ $sub['active']
                                        ? 'bg-[color-mix(in_srgb,var(--theme-surface)_94%,var(--theme-text-strong)_6%)] font-semibold text-[var(--theme-text-strong)]'
                                        : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface)] hover:text-[var(--theme-text-strong)]'
                                    }}
                                "
                                title="{{ $sub['label'] }}"
                            >
                                {{ $sub['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            </div>

        @endforeach

    </nav>


    {{-- ========================================================
        OPCIONES INFERIORES
    ======================================================== --}}
    <div
        id="sidebarFooter"

        class="
            shrink-0

            mx-4

            pt-3
            pb-4

            border-t
            border-[var(--theme-border)]

            space-y-0.5
        "
    >

        {{-- ====================================================
            MI CUENTA
        ==================================================== --}}
        <a
            href="{{ route('ajustes.index', ['section' => 'cuenta']) }}"

            wire:navigate.hover

            class="
                sidebar-footer-link

                min-h-8

                flex
                items-center

                gap-3

                px-2

                rounded-md

                text-sm
                font-medium

                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface)]
                hover:text-[var(--theme-text-strong)]

                transition-colors
            "
        >

            <svg
                class="
                    w-[18px]
                    h-[18px]
                "

                viewBox="0 0 24 24"

                fill="none"

                stroke="currentColor"

                stroke-width="1.6"

                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle
                    cx="12"
                    cy="8"
                    r="3.5"
                />

                <path
                    d="
                        M5 21
                        c0-4.5 2.6-7 7-7
                        s7 2.5 7 7
                    "
                />
            </svg>


            <span
                class="
                    sidebar-label

                    whitespace-nowrap
                "
            >
                Mi cuenta
            </span>

        </a>


        {{-- ====================================================
            MODO OSCURO
        ==================================================== --}}
        <button
            type="button"

            x-data="{
                isDark:
                    document
                        .documentElement
                        .classList
                        .contains('dark')
            }"

            @trackit-theme-changed.window="
                isDark =
                    document
                        .documentElement
                        .classList
                        .contains('dark')
            "

            @click="
                window.setTrackItTheme(
                    isDark
                        ? 'light'
                        : 'dark'
                )
            "

            class="
                sidebar-footer-link

                w-full
                min-h-8

                flex
                items-center

                gap-3

                px-2

                rounded-md

                text-sm
                font-medium

                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface)]
                hover:text-[var(--theme-text-strong)]

                transition-colors
            "
        >

            {{-- Luna --}}
            <svg
                x-show="!isDark"

                class="
                    w-[18px]
                    h-[18px]
                "

                viewBox="0 0 24 24"

                fill="none"

                stroke="currentColor"

                stroke-width="1.6"

                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path
                    d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"
                />
            </svg>


            {{-- Sol --}}
            <svg
                x-show="isDark"

                x-cloak

                class="
                    w-[18px]
                    h-[18px]
                "

                viewBox="0 0 24 24"

                fill="none"

                stroke="currentColor"

                stroke-width="1.6"

                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="4"
                />

                <path d="M12 2v2"/>
                <path d="M12 20v2"/>

                <path d="M4.93 4.93l1.41 1.41"/>
                <path d="M17.66 17.66l1.41 1.41"/>

                <path d="M2 12h2"/>
                <path d="M20 12h2"/>

                <path d="M4.93 19.07l1.41-1.41"/>
                <path d="M17.66 6.34l1.41-1.41"/>
            </svg>


            <span
                class="
                    sidebar-label

                    whitespace-nowrap
                "

                x-text="
                    isDark
                        ? 'Modo claro'
                        : 'Modo oscuro'
                "
            >
                Modo oscuro
            </span>

        </button>


        {{-- ====================================================
            AYUDA
        ==================================================== --}}
        <button
            type="button"

            class="
                sidebar-footer-link

                w-full
                min-h-8

                flex
                items-center

                gap-3

                px-2

                rounded-md

                text-sm
                font-medium

                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface)]
                hover:text-[var(--theme-text-strong)]

                transition-colors
            "
        >

            <svg
                class="
                    w-[18px]
                    h-[18px]
                "

                viewBox="0 0 24 24"

                fill="none"

                stroke="currentColor"

                stroke-width="1.6"

                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                />

                <path
                    d="
                        M9.8 9
                        a2.4 2.4 0 1 1 4.4 1.3
                        c-.7.9-2.2 1.2-2.2 2.7
                    "
                />

                <path
                    d="M12 17h.01"
                />
            </svg>


            <span
                class="
                    sidebar-label

                    whitespace-nowrap
                "
            >
                Ayuda
            </span>

        </button>

    </div>


    {{-- ========================================================
        BORDE ARRASTRABLE
        --------------------------------------------------------
        Se habilitará posteriormente desde sidebar-styles
        y sidebar-script.
    ======================================================== --}}
    <div
        id="sidebarResizeHandle"

        class="hidden"

        aria-hidden="true"
    ></div>

</aside>