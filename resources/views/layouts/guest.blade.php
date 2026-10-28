<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Track-IT') | Grand Palladium Hotels &amp; Resorts
    </title>

    {{-- Aplicación instalable (manifiesto, íconos, service worker) --}}
    @include('layouts.partials.pwa-head')


    {{-- ============================================================
        TEMA
        Se ejecuta antes de cargar los estilos para evitar flash claro.
    ============================================================ --}}
    <script>
        (() => {

            const storageKey = 'trackit_theme';

            const mediaQuery =
                window.matchMedia('(prefers-color-scheme: dark)');


            const normalizePreference = (value) => {

                return ['light', 'dark', 'system'].includes(value)
                    ? value
                    : 'system';

            };


            const getPreference = () => {

                try {

                    return normalizePreference(
                        localStorage.getItem(storageKey)
                    );

                } catch (error) {

                    return 'system';

                }

            };


            const resolveTheme = (preference) => {

                if (preference === 'system') {

                    return mediaQuery.matches
                        ? 'dark'
                        : 'light';

                }

                return preference;

            };


            const applyTheme = (preference) => {

                const normalized =
                    normalizePreference(preference);

                const resolved =
                    resolveTheme(normalized);

                const aplicar = () => {
                    document.documentElement.classList.toggle(
                        'dark',
                        resolved === 'dark'
                    );

                    document.documentElement.dataset.themePreference =
                        normalized;

                    document.documentElement.style.colorScheme =
                        resolved;
                };

                /*
                 * Sin transiciones durante el cambio (si no, unos elementos
                 * se animan desde blanco y otros cambian al instante).
                 */
                if (document.documentElement.classList.contains('dark') !== (resolved === 'dark')) {
                    const estilo = document.createElement('style');
                    estilo.textContent = '*,*::before,*::after{transition:none!important}';
                    document.head.appendChild(estilo);
                    aplicar();
                    window.getComputedStyle(document.documentElement).backgroundColor;
                    requestAnimationFrame(() => requestAnimationFrame(() => estilo.remove()));
                } else {
                    aplicar();
                }

            };


            window.getTrackItTheme = () => {
                return getPreference();
            };


            window.setTrackItTheme = (preference) => {

                const normalized =
                    normalizePreference(preference);

                try {

                    localStorage.setItem(
                        storageKey,
                        normalized
                    );

                } catch (error) {
                    //
                }

                applyTheme(normalized);

            };


            applyTheme(
                getPreference()
            );


            const systemThemeChanged = () => {

                if (getPreference() === 'system') {

                    applyTheme('system');

                }

            };


            if (typeof mediaQuery.addEventListener === 'function') {

                mediaQuery.addEventListener(
                    'change',
                    systemThemeChanged
                );

            } else if (typeof mediaQuery.addListener === 'function') {

                mediaQuery.addListener(
                    systemThemeChanged
                );

            }


            window.addEventListener('storage', (event) => {

                if (event.key === storageKey) {

                    applyTheme(
                        getPreference()
                    );

                }

            });

        })();
    </script>


    {{-- ============================================================
        ASSETS
    ============================================================ --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @include('layouts.partials.organizacion-logo-styles')


    {{-- ============================================================
        FUENTE
    ============================================================ --}}
    @include('layouts.partials.fuente-poppins')

    {{-- Spinner y animaciones comunes (components/ui) --}}
    @include('layouts.partials.ui-styles')
</head>


<body
    id="guestBody"
    class="
        min-h-screen

        bg-[var(--theme-bg)]
        text-[var(--theme-text)]

        font-['Poppins']

        transition-colors
        duration-200
    "
>

    {{-- ============================================================
        CABECERA GUEST

        Móvil:
        - Selector de tema arriba.
        - Logo centrado debajo.
        - Ambos reservan espacio.

        Escritorio:
        - Logo vuelve a la esquina superior izquierda.
        - Selector vuelve a quedar fijo arriba a la derecha.
    ============================================================ --}}
    <header
        id="guestHeader"
        class="
            w-full

            px-4
            pt-4

            sm:p-0
        "
    >

        {{-- ========================================================
            SELECTOR DE TEMA
            Un solo botón; al tocarlo se despliegan Claro, Oscuro y
            Sistema (estilos .guest-tema-* al final del archivo).
        ======================================================== --}}
        <div id="guestThemeWrap" class="guest-tema">

            <button
                type="button"
                id="guestThemeToggle"
                class="guest-tema-boton"
                aria-haspopup="menu"
                aria-expanded="false"
                aria-controls="guestThemeMenu"
                aria-label="Cambiar tema"
                title="Cambiar tema"
                onclick="toggleGuestThemeMenu()"
            >
                {{-- Contraste (mitad clara / mitad oscura) --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5"/>
                    <path d="M12 3.5v17a8.5 8.5 0 0 0 0-17z" fill="currentColor" stroke="none"/>
                </svg>
            </button>

            <div
                id="guestThemeMenu"
                class="guest-tema-menu"
                role="menu"
                aria-labelledby="guestThemeToggle"
            >
                <button type="button" role="menuitemradio" aria-checked="false" data-theme-option="light" class="guest-theme-option guest-tema-opcion" onclick="changeGuestTheme('light')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2.5v2M12 19.5v2M4.6 4.6l1.4 1.4M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4L6 18M18 6l1.4-1.4"/>
                    </svg>
                    <span>Claro</span>
                    <svg class="guest-tema-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </button>

                <button type="button" role="menuitemradio" aria-checked="false" data-theme-option="dark" class="guest-theme-option guest-tema-opcion" onclick="changeGuestTheme('dark')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.5 13.2A8.5 8.5 0 1 1 10.8 3.5a6.6 6.6 0 0 0 9.7 9.7z"/>
                    </svg>
                    <span>Oscuro</span>
                    <svg class="guest-tema-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </button>

                <button type="button" role="menuitemradio" aria-checked="false" data-theme-option="system" class="guest-theme-option guest-tema-opcion" onclick="changeGuestTheme('system')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="13" rx="2"/>
                        <path d="M8 21h8M12 17v4"/>
                    </svg>
                    <span>Sistema</span>
                    <svg class="guest-tema-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </button>
            </div>

        </div>


        {{-- ========================================================
            LOGO
        ======================================================== --}}
        <div
            id="guestLogoWrap"
            class="
                mt-3

                flex
                justify-center

                sm:mt-0
                sm:block
            "
        >

            <div
                class="
                    relative
                    z-40

                    sm:absolute
                    sm:top-6
                    sm:left-8
                "
            >

                @php
                    $organizacion = app(\App\Services\OrganizacionService::class);
                @endphp

                <img
                    id="guestLogo"
                    src="{{ $organizacion->logoInterfazUrl() }}"

                    @if ($organizacion->logoInterfazUrlOscuro()) data-src-claro="{{ $organizacion->logoInterfazUrl() }}" data-src-oscuro="{{ $organizacion->logoInterfazUrlOscuro() }}" @endif
                    alt="{{ $organizacion->nombre() }}"
                    data-dark-mode="{{ $organizacion->logoInterfazModoOscuro() }}"
                    style="--org-logo-w: {{ $organizacion->logoAncho(150) }}px; --org-logo-w-sm: {{ $organizacion->logoAncho(170) }}px; --org-logo-bg: {{ $organizacion->logoColorFondo() }};"
                    class="
                        org-logo

                        h-auto

                        object-contain

                        transition-[filter,opacity]
                        duration-200
                    "
                    onerror="this.style.display='none'"
                >

            </div>

        </div>

    </header>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <main
        id="guestMain"
        class="
            w-full

            flex
            items-start
            justify-center

            px-4

            pt-4
            pb-10

            sm:min-h-screen
            sm:items-center
            sm:py-10
        "
    >

        @yield('content')

    </main>

    <script>
    /*
     * Menú de tema: abrir / cerrar (clic fuera, Esc o al elegir).
     */
    function setGuestThemeMenu(abierto) {
        const contenedor = document.getElementById('guestThemeWrap');
        const boton = document.getElementById('guestThemeToggle');

        if (! contenedor || ! boton) {
            return;
        }

        contenedor.dataset.abierto = abierto ? 'true' : 'false';
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');

        if (abierto) {
            const activa = contenedor.querySelector('.guest-tema-opcion[aria-checked="true"]')
                ?? contenedor.querySelector('.guest-tema-opcion');

            setTimeout(() => activa?.focus(), 60);
        }
    }

    function toggleGuestThemeMenu() {
        setGuestThemeMenu(document.getElementById('guestThemeWrap')?.dataset.abierto !== 'true');
    }

    document.addEventListener('click', (evento) => {
        const contenedor = document.getElementById('guestThemeWrap');

        if (contenedor && contenedor.dataset.abierto === 'true' && ! contenedor.contains(evento.target)) {
            setGuestThemeMenu(false);
        }
    });

    document.addEventListener('keydown', (evento) => {
        const contenedor = document.getElementById('guestThemeWrap');

        if (! contenedor || contenedor.dataset.abierto !== 'true') {
            return;
        }

        if (evento.key === 'Escape') {
            setGuestThemeMenu(false);
            document.getElementById('guestThemeToggle')?.focus();
        }

        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();

            const opciones = [...contenedor.querySelectorAll('.guest-tema-opcion')];
            const actual = opciones.indexOf(document.activeElement);
            const siguiente = (actual + (evento.key === 'ArrowDown' ? 1 : -1) + opciones.length) % opciones.length;

            opciones[siguiente]?.focus();
        }
    });

    function updateGuestThemeSwitcher() {

        const currentTheme =
            window.getTrackItTheme
                ? window.getTrackItTheme()
                : 'system';

        document
            .querySelectorAll('.guest-theme-option')
            .forEach(button => {

                const active =
                    button.dataset.themeOption === currentTheme;

                button.setAttribute('aria-checked', active ? 'true' : 'false');

            });
    }


    function changeGuestTheme(theme) {

        if (window.setTrackItTheme) {
            window.setTrackItTheme(theme);
        }

        updateGuestThemeSwitcher();

        // Cierre con una pequeña pausa para ver la selección.
        setTimeout(() => {
            setGuestThemeMenu(false);
            document.getElementById('guestThemeToggle')?.focus();
        }, 140);
    }


    document.addEventListener(
        'DOMContentLoaded',
        updateGuestThemeSwitcher
    );
</script>

<style>
    /* ============================================================
        SELECTOR DE TEMA (un botón que despliega las opciones)
    ============================================================ */
    .guest-tema {
        position: relative;
        z-index: 50;
        display: flex;
        justify-content: flex-end;
    }

    .guest-tema-boton {
        width: 2.75rem;
        height: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--theme-border);
        border-radius: 0.5rem;
        background: var(--theme-surface);
        color: var(--theme-text-muted);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        cursor: pointer;
        transition: color 160ms ease-out, border-color 160ms ease-out, transform 160ms ease-out;
    }

    .guest-tema-boton svg {
        width: 1.25rem;
        height: 1.25rem;
        transition: transform 280ms cubic-bezier(0.22, 0.8, 0.24, 1);
    }

    .guest-tema-boton:hover,
    .guest-tema[data-abierto="true"] .guest-tema-boton {
        color: var(--theme-primary);
        border-color: var(--theme-border-strong);
    }

    .guest-tema[data-abierto="true"] .guest-tema-boton svg {
        transform: rotate(180deg);
    }

    .guest-tema-boton:active {
        transform: scale(0.96);
    }

    .guest-tema-menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        min-width: 10.5rem;
        padding: 0.3rem;
        border: 1px solid var(--theme-border);
        border-radius: 0.75rem;
        background: var(--theme-surface);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
        transform-origin: top right;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px) scale(0.94);
        transition:
            opacity 160ms ease-out,
            transform 220ms cubic-bezier(0.22, 0.8, 0.24, 1),
            visibility 0s linear 220ms;
    }

    .guest-tema[data-abierto="true"] .guest-tema-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
        transition:
            opacity 160ms ease-out,
            transform 220ms cubic-bezier(0.22, 0.8, 0.24, 1),
            visibility 0s;
    }

    .guest-tema-opcion {
        width: 100%;
        min-height: 2.75rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0 0.7rem;
        border: 0;
        border-radius: 0.5rem;
        background: transparent;
        color: var(--theme-text);
        font-size: 0.875rem;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
        transition: background-color 140ms ease-out, color 140ms ease-out;
    }

    .guest-tema-opcion > svg:first-child {
        width: 1.125rem;
        height: 1.125rem;
        flex-shrink: 0;
        color: var(--theme-text-muted);
    }

    .guest-tema-opcion span {
        flex: 1;
    }

    .guest-tema-check {
        width: 1rem;
        height: 1rem;
        opacity: 0;
        transform: scale(0.6);
        transition: opacity 140ms ease-out, transform 200ms cubic-bezier(0.22, 0.8, 0.24, 1);
    }

    .guest-tema-opcion:hover,
    .guest-tema-opcion:focus-visible {
        background: var(--theme-surface-soft);
        outline: none;
    }

    .guest-tema-opcion[aria-checked="true"] {
        background: var(--theme-primary-soft);
        color: var(--theme-primary);
        font-weight: 600;
    }

    .guest-tema-opcion[aria-checked="true"] > svg:first-child {
        color: var(--theme-primary);
    }

    .guest-tema-opcion[aria-checked="true"] .guest-tema-check {
        opacity: 1;
        transform: scale(1);
    }

    .guest-tema-boton:focus-visible {
        outline: 2px solid var(--theme-primary);
        outline-offset: 2px;
    }

    /* Escritorio: arriba a la derecha, como antes. */
    @media (min-width: 640px) {
        .guest-tema {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
        }
    }

    /*
     * Móvil: logo y tarjeta centrados juntos en la pantalla (un poco
     * por encima del centro); el selector de tema queda arriba.
     */
    @media (max-width: 639.98px) {
        #guestBody {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: calc(6vh + env(safe-area-inset-bottom, 0px));
        }

        #guestHeader {
            display: contents;
        }

        #guestThemeWrap {
            position: absolute;
            top: calc(1rem + env(safe-area-inset-top, 0px));
            right: 1rem;
        }

        #guestLogoWrap {
            margin-top: 0;
            padding: 0 1rem;
        }

        #guestMain {
            padding-top: 1.5rem;
            padding-bottom: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .guest-tema-boton,
        .guest-tema-boton svg,
        .guest-tema-menu,
        .guest-tema-opcion,
        .guest-tema-check {
            transition: none;
        }
    }
</style>

</body>
</html>