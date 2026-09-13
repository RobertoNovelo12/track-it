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

                document.documentElement.classList.toggle(
                    'dark',
                    resolved === 'dark'
                );

                document.documentElement.dataset.themePreference =
                    normalized;

                document.documentElement.style.colorScheme =
                    resolved;

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


    {{-- ============================================================
        FUENTE
    ============================================================ --}}
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>


<body
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
        LOGO
    ============================================================ --}}
<div
    class="
        absolute
        top-5
        left-1/2
        -translate-x-1/2

        sm:top-6
        sm:left-8
        sm:translate-x-0

        z-40
    "
>
            {{-- Logo modo claro --}}
            <img
                id="guestLogo"
                src="{{ asset('images/logo-grand-palladium.png') }}"
                alt="Grand Palladium Hotels & Resorts"
                class="
                    h-[135px]
                    sm:h-[164px]
                    w-auto

                    object-contain

                    transition-[filter,opacity]
                    duration-200
                "
                onerror="this.style.display='none'"
            >
        </div>

{{-- ============================================================
    SELECTOR DE TEMA
============================================================ --}}
<div
    id="guestThemeSwitcher"
    class="
        fixed
        top-4
        right-4
        sm:top-6
        sm:right-6
        z-50

        flex
        items-center

        p-1

        rounded-lg

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border)]

        shadow-sm
    "
>

    {{-- Claro --}}
    <button
        type="button"
        data-theme-option="light"
        onclick="changeGuestTheme('light')"
        class="
            guest-theme-option

            w-9
            h-9

            flex
            items-center
            justify-center

            rounded-md

            text-[var(--theme-text-muted)]

            transition-colors
        "
        title="Tema claro"
        aria-label="Tema claro"
    >
        <svg
            class="w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2"/>
            <path d="M12 20v2"/>
            <path d="M4.93 4.93l1.41 1.41"/>
            <path d="M17.66 17.66l1.41 1.41"/>
            <path d="M2 12h2"/>
            <path d="M20 12h2"/>
            <path d="M4.93 19.07l1.41-1.41"/>
            <path d="M17.66 6.34l1.41-1.41"/>
        </svg>
    </button>


    {{-- Oscuro --}}
    <button
        type="button"
        data-theme-option="dark"
        onclick="changeGuestTheme('dark')"
        class="
            guest-theme-option

            w-9
            h-9

            flex
            items-center
            justify-center

            rounded-md

            text-[var(--theme-text-muted)]

            transition-colors
        "
        title="Tema oscuro"
        aria-label="Tema oscuro"
    >
        <svg
            class="w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path
                d="M20 15.5A8 8 0 018.5 4
                   8 8 0 1020 15.5z"
            />
        </svg>
    </button>


    {{-- Sistema --}}
    <button
        type="button"
        data-theme-option="system"
        onclick="changeGuestTheme('system')"
        class="
            guest-theme-option

            w-9
            h-9

            flex
            items-center
            justify-center

            rounded-md

            text-[var(--theme-text-muted)]

            transition-colors
        "
        title="Usar tema del sistema"
        aria-label="Usar tema del sistema"
    >
        <svg
            class="w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <rect x="3" y="4" width="18" height="13" rx="2"/>
            <path d="M8 21h8"/>
            <path d="M12 17v4"/>
        </svg>
    </button>

</div>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <main
        class="
            min-h-screen

            flex
            items-center
            justify-center

            px-4
            py-10
        "
    >

        @yield('content')

    </main>

    <script>
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

                button.style.backgroundColor =
                    active
                        ? 'var(--theme-primary-soft)'
                        : 'transparent';

                button.style.color =
                    active
                        ? 'var(--theme-primary)'
                        : 'var(--theme-text-muted)';

            });
    }


    function changeGuestTheme(theme) {

        if (window.setTrackItTheme) {
            window.setTrackItTheme(theme);
        }

        updateGuestThemeSwitcher();
    }


    document.addEventListener(
        'DOMContentLoaded',
        updateGuestThemeSwitcher
    );
</script>

</body>
</html>