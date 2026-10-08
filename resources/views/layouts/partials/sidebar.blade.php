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

    $sidebarItems = [
        [
            'label' => 'Vista General',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'icon' => 'dashboard',
        ],

        [
            'label' => 'Equipos Tecnológicos',
            'url' => Route::has('equipos.index')
                ? route('equipos.index')
                : '#',
            'active' => request()->routeIs('equipos.*'),
            'icon' => 'equipos',
        ],

        [
            'label' => 'Asignación/Mov',
            'url' => Route::has('asignaciones.index')
                ? route('asignaciones.index')
                : '#',
            'active' => request()->routeIs('asignaciones.*'),
            'icon' => 'asignaciones',
        ],

        [
            'label' => 'Mantenimientos',
            'url' => Route::has('mantenimientos.index')
                ? route('mantenimientos.index')
                : '#',
            'active' => request()->routeIs('mantenimientos.*'),
            'icon' => 'mantenimientos',
        ],

        [
            'label' => 'Reportes',
            'url' => Route::has('reportes.index')
                ? route('reportes.index')
                : '#',
            'active' => request()->routeIs('reportes.*'),
            'icon' => 'reportes',
        ],

        [
            'label' => 'Seguridad y Roles',
            'url' => Route::has('usuarios.index')
                ? route('usuarios.index')
                : '#',
            'active' => request()->routeIs('usuarios.*'),
            'icon' => 'seguridad',
        ],

        [
            'label' => 'Catálogos Base',
            'url' => Route::has('catalogos.index')
                ? route('catalogos.index')
                : '#',
            'active' => request()->routeIs('catalogos.*'),
            'icon' => 'catalogos',
        ],
    ];
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
                <img
                    id="sidebarLogo"

                    src="{{ asset('images/logo-grand-palladium.png') }}"

                    alt="Grand Palladium Hotels & Resorts"

                    class="
                        w-[135px]
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


    {{-- ========================================================
        BÚSQUEDA RÁPIDA
    ======================================================== --}}
    @persist('sidebar-global-equipment-search')

        <form
            id="sidebarQuickSearch"

            action="{{ route('equipos.search') }}"

            method="GET"

            x-data="{
                query: '',
                ready: false,

                init() {

                    const serverQuery = @js(
                        request()->routeIs('equipos.search')
                            ? (string) request('q', '')
                            : null
                    );

                    let savedQuery = '';

                    try {

                        savedQuery =
                            localStorage.getItem(
                                'trackit_global_equipment_search'
                            ) ?? '';

                    } catch (error) {

                        savedQuery = '';

                    }


                    if (serverQuery !== null) {

                        this.query =
                            serverQuery;

                        this.persist();

                    } else {

                        this.query =
                            savedQuery;

                    }


                    this.ready = true;
                },

                persist() {

                    const value =
                        String(
                            this.query
                            ?? ''
                        );

                    try {

                        if (value.trim() === '') {

                            localStorage.removeItem(
                                'trackit_global_equipment_search'
                            );

                            return;
                        }


                        localStorage.setItem(
                            'trackit_global_equipment_search',
                            value
                        );

                    } catch (error) {
                        //
                    }
                },
            }"

            @submit="persist()"

            @keydown.window.ctrl.k.prevent="
                $refs.quickSearch?.focus()
            "

            class="
                sidebar-expanded-only

                shrink-0

                px-4
                pb-4
            "
        >

            <div
                class="
                    relative

                    h-11

                    flex
                    items-center

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    shadow-[0_2px_8px_rgba(15,23,42,0.035)]

                    focus-within:border-[var(--theme-border-strong)]

                    transition-all
                "
            >

                {{-- Lupa --}}
                <svg
                    class="
                        absolute

                        left-3.5

                        w-[18px]
                        h-[18px]

                        text-[var(--theme-text-muted)]

                        pointer-events-none
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


                {{-- Campo --}}
                <input
                    id="sidebarQuickSearchInput"

                    x-ref="quickSearch"

                    type="search"

                    name="q"

                    x-model="query"

                    @input="persist()"

                    :placeholder="
                        ready
                            ? 'Búsqueda rápida'
                            : ''
                    "

                    autocomplete="off"

                    class="
                        w-full
                        h-full

                        border-0

                        bg-transparent

                        pl-10
                        pr-16

                        text-xs
                        font-medium

                        text-[var(--theme-text)]

                        placeholder:text-[var(--theme-text-muted)]
                        placeholder:font-normal

                        focus:ring-0
                        focus:outline-none
                    "
                >


                {{-- Ctrl K --}}
                <span
                    class="
                        absolute
                        right-2.5

                        inline-flex
                        items-center
                        justify-center

                        h-6

                        px-2

                        rounded-md

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface-soft)]

                        text-[9px]
                        font-medium
                        text-[var(--theme-text-muted)]

                        shadow-[0_1px_2px_rgba(15,23,42,0.03)]

                        pointer-events-none
                    "
                >
                    Ctrl K
                </span>

            </div>

        </form>

    @endpersist


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

            space-y-1
        "
    >

        @foreach ($sidebarItems as $item)

            <a
                href="{{ $item['url'] }}"

                wire:navigate.hover

                class="
                    sidebar-nav-link

                    {{ $item['active']
                        ? 'sidebar-nav-active'
                        : ''
                    }}

                    min-h-10

                    flex
                    items-center

                    gap-3

                    px-3.5

                    rounded-lg

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


                    {{-- Catálogos --}}
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

            </a>

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

            space-y-1
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

                min-h-10

                flex
                items-center

                gap-3

                px-2

                rounded-lg

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
                min-h-10

                flex
                items-center

                gap-3

                px-2

                rounded-lg

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
                    d="
                        M20 15.5
                        A8 8 0 0 1 8.5 4
                        A8 8 0 1 0 20 15.5
                    "
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
                min-h-10

                flex
                items-center

                gap-3

                px-2

                rounded-lg

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