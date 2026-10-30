@php
    /*
     * Preferencias del usuario (Ajustes › Preferencias):
     * tema y densidad se aplican antes de pintar la página.
     */
    $preferenciasLayout = app(\App\Services\PreferenciasUsuarioService::class)->obtener();
@endphp
    <!DOCTYPE html>
    <html
        lang="es"
        data-densidad="{{ $preferenciasLayout['densidad'] }}"
        @foreach (app(\App\Services\PreferenciasUsuarioService::class)->atributosAccesibilidad($preferenciasLayout) as $atributo => $valorAtributo)
            {{ $atributo }}="{{ $valorAtributo }}"
        @endforeach
    >

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Sistema') | Grand Palladium</title>

        {{-- Aplicación instalable (manifiesto, íconos, service worker) --}}
        @include('layouts.partials.pwa-head')

        {{-- Fuente Poppins (local) --}}
        @include('layouts.partials.fuente-poppins')

        <script>
        /*
         * Tema guardado en el servidor (Ajustes › Preferencias). Tiene
         * prioridad sobre el navegador para que el tema sea el mismo en
         * todos los dispositivos del usuario.
         */
        window.__trackitTemaServidor = @js($preferenciasLayout['tema']);

        (() => {
            const storageKey = 'trackit_theme';

            function getThemePreference() {
                if (
                    ['light', 'dark', 'system'].includes(
                        window.__trackitTemaServidor
                    )
                ) {
                    return window.__trackitTemaServidor;
                }

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



            /*
             * Cambio de tema sin transiciones: muchos elementos tienen
             * transition-colors / transition-all con duraciones distintas,
             * así que al pasar de claro a oscuro unos cambiaban al instante
             * y otros se animaban desde blanco. Durante el cambio se apagan
             * todas las transiciones y se vuelven a encender dos frames después.
             */
            function cambiarTemaSinTransiciones(cambio) {
                const estilo = document.createElement('style');
                estilo.textContent = '*,*::before,*::after{transition:none!important}';
                document.head.appendChild(estilo);

                cambio();

                // Fuerza a aplicar los colores nuevos antes de reactivar.
                window.getComputedStyle(document.documentElement).backgroundColor;

                requestAnimationFrame(() => requestAnimationFrame(() => estilo.remove()));
            }

            function applyTheme(preference = getThemePreference()) {
                const resolved = resolveTheme(preference);
                const html = document.documentElement;

                const aplicar = () => {
                    html.classList.toggle(
                        'dark',
                        resolved === 'dark'
                    );

                    html.dataset.themePreference = preference;

                    html.style.colorScheme = resolved;
                };

                // Solo si el tema visible cambia (la carga inicial no anima nada).
                if (html.classList.contains('dark') !== (resolved === 'dark')) {
                    cambiarTemaSinTransiciones(aplicar);
                } else {
                    aplicar();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Disponible para el botón que agregaremos después
            |--------------------------------------------------------------------------
            */

            window.setTrackItTheme = function (preference, opciones = {}) {
                if (
                    !['light', 'dark', 'system'].includes(preference)
                ) {
                    return;
                }

                window.__trackitTemaServidor = preference;

                /*
                 * Guardar en la cuenta (salvo cuando el cambio ya viene
                 * de Ajustes › Preferencias, que lo guardó por su cuenta).
                 */
                if (opciones.persistir !== false) {
                    const token = document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content');

                    fetch(@js(route('preferencias.tema')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token ?? '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ tema: preference }),
                        keepalive: true,
                    }).catch(() => {});
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
             * Ajustes › Preferencias guardó tema y densidad:
             * se aplican sin recargar.
             */
            window.addEventListener(
                'preferencias-actualizadas',
                (event) => {
                    const detalle = Array.isArray(event.detail)
                        ? (event.detail[0] ?? {})
                        : (event.detail ?? {});

                    if (detalle.densidad) {
                        document.documentElement.dataset.densidad =
                            detalle.densidad;
                    }

                    // Accesibilidad: { 'data-texto': 'grande', … }
                    Object.entries(detalle.accesibilidad ?? {}).forEach(
                        ([atributo, valor]) => document.documentElement.setAttribute(atributo, valor)
                    );

                    if (detalle.tema) {
                        window.setTrackItTheme(
                            detalle.tema,
                            { persistir: false }
                        );
                    }
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Aplicar ANTES de pintar la página
            |--------------------------------------------------------------------------
            */

            applyTheme();


/*
|--------------------------------------------------------------------------
| Antes de intercambiar la página
|--------------------------------------------------------------------------
|
| Aplicamos el tema inmediatamente después del swap del HTML,
| pero antes de que se procesen los scripts de la nueva página.
| Esto evita flashes de tema claro y parpadeos del logo.
|
*/
document.addEventListener(
    'livewire:navigating',
    (event) => {
        event.detail.onSwap(() => {
            applyTheme(
                getThemePreference()
            );
        });
    }
);


    /*
    |--------------------------------------------------------------------------
    | Después de terminar la navegación
    |--------------------------------------------------------------------------
    */
    document.addEventListener(
        'livewire:navigated',
        () => {
            const preference =
                getThemePreference();

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
        }
    );


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

    {{--
        Filtros de Equipos plegados: se lee antes de pintar (como el tema)
        para que el esqueleto de carga ya aparezca plegado y no "salte".
        La clase la mantiene al día livewire/equipos/filters/desktop.
    --}}
    <script>
        /*
         * La preferencia vive en localStorage. Se vuelve a aplicar en cada
         * navegación (wire:navigate y botón «atrás»), porque Livewire
         * reemplaza los atributos de <html> al cambiar de página.
         */
        window.trackitFiltrosPlegados = function () {
            try {
                return localStorage.getItem('trackit_filtros_equipos') === 'plegado';
            } catch (error) {
                return false;
            }
        };

        document.documentElement.classList.toggle('filtros-equipos-plegados', window.trackitFiltrosPlegados());

        if (! window.__trackitFiltrosNavegacion) {
            window.__trackitFiltrosNavegacion = true;

            ['livewire:navigating', 'livewire:navigated'].forEach(function (evento) {
                document.addEventListener(evento, function () {
                    document.documentElement.classList.toggle('filtros-equipos-plegados', window.trackitFiltrosPlegados());
                });
            });
        }
    </script>

    <style>
        html.filtros-equipos-plegados [data-filtros-equipos-cuerpo] {
            display: none;
        }

        /*
         * Foco del buscador con teclado: el contorno va en la caja
         * redondeada (como los demás campos), no en el input interno,
         * que es cuadrado y queda metido dentro del borde.
         */
        .top-search input.top-search-input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        .top-search:has(.top-search-input:focus-visible) {
            border-color: var(--theme-primary);
            outline: 2px solid color-mix(in srgb, var(--theme-primary) 45%, transparent);
            outline-offset: 0;
        }

        /* Preferencia «Resaltar el foco del teclado». */
        html[data-foco="resaltado"] .top-search:has(.top-search-input:focus-visible) {
            outline: 3px solid var(--theme-primary);
            outline-offset: 2px;
            box-shadow: 0 0 0 6px color-mix(in srgb, var(--theme-primary) 22%, transparent);
        }

        /* Sin la «X» nativa del buscador (se usa un botón propio). */
        .top-search-input::-webkit-search-cancel-button,
        .top-search-input::-webkit-search-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }

        html.filtros-equipos-plegados [data-filtros-equipos-flecha] {
            transform: rotate(180deg);
        }
    </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')

        @include('layouts.partials.sidebar-styles')

        @include('layouts.partials.densidad-styles')

        @include('layouts.partials.volver-script')

        {{-- Descargas que los gestores de descargas no capturan --}}
        @include('layouts.partials.descargas-script')

        @include('layouts.partials.accesibilidad-script')

        @include('layouts.partials.cambios-sin-guardar')

        {{-- Ctrl+G / Ctrl+S: guardar el formulario en pantalla --}}
        @include('layouts.partials.atajo-guardar')

        @include('layouts.partials.formatos-campos')

        @include('layouts.partials.mensajes-error')

        @include('layouts.partials.accesibilidad-styles')

        {{-- Sistema de carga y movimiento (components/ui) --}}
        @include('layouts.partials.ui-styles')

        @include('layouts.partials.organizacion-logo-styles')

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

    {{-- Teclado / lector de pantalla: primer elemento al pulsar Tab. --}}
    <a
        href="#contenido-principal"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[300] focus:rounded-lg focus:bg-[var(--theme-primary)] focus:px-4 focus:py-2.5 focus:text-sm focus:font-medium focus:text-white"
    >
        Saltar al contenido
    </a>

    <div class="min-h-screen flex">

{{-- ============================================================
    SIDEBAR
============================================================ --}}
@include('layouts.partials.sidebar')

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
            class="
                ml-0
                md:ml-64

                min-w-0
                flex-1
                min-h-screen

                overflow-x-clip

                transition-[margin-left]
                duration-300
                ease-in-out
            "
        >

            {{-- ========================================================
                HEADER
            ======================================================== --}}
            <header
                id="mobileAppHeader"

                class="
                    h-16

                    theme-bg

                    border-b
                    border-[var(--theme-border)]

                    grid
                    grid-cols-[auto_minmax(0,1fr)_auto]
                    items-center

                    gap-2
                    sm:gap-5

                    px-4
                    sm:px-6

                    sticky
                    top-0
                    z-30

                    transition-transform
                    duration-200
                    ease-out

                    will-change-transform
                "
            >

                    @php
                        $encabezadoPagina = \App\Support\Encabezado::actual();
                    @endphp

                    {{-- Título y ruta de la pantalla (escritorio); en móvil van arriba del contenido --}}
                    <div class="col-start-1 row-start-1 hidden min-w-0 max-w-[45vw] md:block">
                        @include('layouts.partials.encabezado', ['variante' => 'barra', 'encabezado' => $encabezadoPagina])
                    </div>

                    {{-- Móvil: el menú está en la barra inferior y el logotipo en su hoja «Más» (layouts/partials/menu-inferior). --}}


            {{-- ========================================================
                BUSCADOR (Ctrl K) — busca en Equipos tecnológicos.
                En móvil incluye el escáner de códigos de barras.
            ======================================================== --}}
            <form
                id="topQuickSearch"
                action="{{ route('equipos.index') }}"
                method="GET"
                role="search"
                class="col-start-2 min-w-0 w-full md:max-w-md md:justify-self-end"
                x-data="{
                    query: @js(request()->routeIs('equipos.index') ? (string) request('buscar', '') : ''),

                    /*
                     * Se registra aquí y no en x-init: Alpine ejecuta la función
                     * que devuelve una expresión de x-init, y eso enfocaba el
                     * buscador (y abría el teclado) en cada carga de página.
                     */
                    init() {
                        window.focusTopSearch = () => this.enfocar();

                        /*
                         * Al vaciar el buscador estando en Inventario con
                         * ?buscar=, la lista también deja de filtrar por él.
                         */
                        this.$watch('query', (valor) => {
                            if (
                                String(valor ?? '').trim() === ''
                                && new URLSearchParams(window.location.search).has('buscar')
                            ) {
                                window.Livewire?.dispatch('limpiar-busqueda-general');
                            }
                        });
                    },

                    enfocar() {
                        this.$refs.campo?.focus();
                        this.$refs.campo?.select();
                    },
                }"
                @keydown.window.ctrl.k.prevent="enfocar()"
                @keydown.window.meta.k.prevent="enfocar()"
                @submit.prevent="
                    const destino = new URL($el.action, window.location.origin);
                    const texto = String(query ?? '').trim();

                    if (texto !== '') {
                        destino.searchParams.set('buscar', texto);
                    }

                    $refs.campo?.blur();

                    window.Livewire?.navigate
                        ? window.Livewire.navigate(destino.toString())
                        : (window.location.href = destino.toString());
                "
            >
                <div class="top-search relative flex h-10 items-center rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] transition-colors focus-within:border-[var(--theme-border-strong)]">
                    <svg class="pointer-events-none absolute left-3 h-4 w-4 text-[var(--theme-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-8bdcd83') }}"></use></svg>

                    <input
                        x-ref="campo"
                        type="search"
                        name="buscar"
                        x-model="query"
                        placeholder="Buscar equipos"
                        autocomplete="off"
                        enterkeyhint="search"
                        aria-label="Buscar equipos"
                        class="top-search-input h-full w-full min-w-0 border-0 bg-transparent pl-8 pr-9 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-0 md:pl-9 md:pr-[6.25rem]"
                        x-bind:class="String(query ?? '') !== '' && 'max-md:pr-[4.5rem]'"
                    >

                    {{-- Limpiar (reemplaza la «X» nativa del navegador) --}}
                    <button
                        type="button"
                        x-show="String(query ?? '') !== ''"
                        x-cloak
                        x-on:click="query = ''; $refs.campo?.focus()"
                        class="absolute right-1 flex h-7 w-7 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text-strong)] md:right-[3.75rem]"
                        aria-label="Limpiar búsqueda"
                        title="Limpiar"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-c00fbb2') }}"></use></svg>
                    </button>

                    {{-- Atajo (escritorio) --}}
                    <span class="pointer-events-none absolute right-2.5 hidden h-6 items-center rounded border border-[var(--theme-border)] px-1.5 text-[10px] font-medium text-[var(--theme-text-muted)] md:inline-flex">
                        Ctrl K
                    </span>

                    {{-- Escáner de códigos de barras: en móvil ahora está en la barra inferior (oculto aquí). --}}
                    <button
                        type="button"
                        x-on:click="$dispatch('abrir-escaner')"
                        class="absolute right-1 hidden h-8 w-8 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)]"
                        aria-label="Escanear código de barras"
                        title="Escanear código de barras"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-ca2436a') }}"></use></svg>
                    </button>
                </div>
            </form>

            {{-- ========================================================
                USUARIO / PERFIL
            ======================================================== --}}
            @php
                $authenticatedUserId = auth()->id();

                $initialUnreadNotifications = $authenticatedUserId
                    ? \Illuminate\Support\Facades\Cache::remember(
                        'notifications.unread.' . $authenticatedUserId,
                        60,
                        function () use ($authenticatedUserId): int {
                            return (int) \Illuminate\Support\Facades\DB::table(
                                'notificaciones'
                            )
                                ->where(
                                    'id_usuario',
                                    $authenticatedUserId
                                )
                                ->where(
                                    'leida',
                                    false
                                )
                                ->count();
                        }
                    )
                    : 0;
            @endphp

            <div
                x-data="{
                    unreadNotifications: {{ $initialUnreadNotifications }}
                }"

                @notifications-unread-updated.window="
                    unreadNotifications = Number(
                        $event.detail.count ?? 0
                    )
                "

                class="col-start-3 flex shrink-0 items-center gap-2 sm:gap-3"
            >

                {{-- Equipos enviados desde el teléfono (bandeja personal) --}}
                @can('gestionar')
                    <div class="hidden md:block">
                        <livewire:equipos-recibidos-panel />
                    </div>
                @endcan

                {{-- ====================================================
                    NOTIFICACIONES
                ==================================================== --}}
                <button
                    id="boton-notificaciones"
                    type="button"
                    onclick="
                            if (window.innerWidth < 768) {
                                const sidebar =
                                    document.getElementById('sidebar');

                                if (
                                    sidebar &&
                                    sidebar.classList.contains('translate-x-0')
                                ) {
                                    window.toggleSidebar();
                                }
                            }

                            window.openNotificationsPanelFast();
                        "
                    class="
                        relative
                        shrink-0

                        w-9
                        h-9

                        flex
                        items-center
                        justify-center

                        rounded-lg

                        text-[var(--theme-text-muted)]

                        hover:text-[var(--theme-primary)]
                        hover:bg-[var(--theme-primary-soft)]
                        aria-expanded:text-[var(--theme-primary)]
                        aria-expanded:bg-[var(--theme-primary-soft)]

                        transition-colors
                    "
                    aria-expanded="false"
                    aria-label="Notificaciones"
                    aria-haspopup="dialog"
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>

                    {{-- Punto de notificación --}}
                    <span
                        x-show="unreadNotifications > 0"
                        x-cloak

                        class="
                            absolute
                            top-1.5
                            right-1.5

                            w-1.5
                            h-1.5

                            bg-[var(--theme-danger)]
                            rounded-full
                        "

                        aria-hidden="true"
                    ></span>
                </button>



                {{-- ====================================================
                    MENÚ DE PERFIL
                ==================================================== --}}
                    @php
                        /*
                         * Cápsula de perfil: solo el primer nombre y el
                         * hotel del usuario (en caché 10 min por usuario).
                         */
                        $perfilUsuario = auth()->user();

                        $perfilNombreCorto = \Illuminate\Support\Str::of(
                            (string) ($perfilUsuario->nombres ?? $perfilUsuario->name ?? '')
                        )->trim()->explode(' ')->first() ?: 'Usuario';

                        $perfilNombreCorto = mb_convert_case(
                            mb_strtolower($perfilNombreCorto),
                            MB_CASE_TITLE
                        );

                        $perfilUnidad = filled($perfilUsuario->id_unidad ?? null)
                            ? \Illuminate\Support\Facades\Cache::remember(
                                'layout.usuario.unidad.' . $perfilUsuario->getKey(),
                                600,
                                fn () => \Illuminate\Support\Facades\DB::table('unidades_organizativas')
                                    ->where('id_unidad', $perfilUsuario->id_unidad)
                                    ->value('nombre')
                            )
                            : null;
                    @endphp

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
                        @trackit-cerrar-menus.window="open = false"

                        class="relative"
                    >

                    {{-- =================================================
                        CÁPSULA
                    ================================================= --}}
                    <button
                        type="button"
                        @click="
                                if (window.innerWidth < 768) {
                                    const sidebar =
                                        document.getElementById('sidebar');

                                    if (
                                        sidebar &&
                                        sidebar.classList.contains('translate-x-0')
                                    ) {
                                        window.toggleSidebar();
                                    }
                                }

                                open = !open;
                            "
                        class="
                            group

                            flex
                            items-center
                            gap-2

                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]

                            p-1

                            sm:pl-3

                            hover:border-[var(--theme-primary-border)]
                            hover:bg-[var(--theme-primary-soft-subtle)]
                            aria-expanded:border-[var(--theme-primary-border)]
                            aria-expanded:bg-[var(--theme-primary-soft-subtle)]

                            transition-all
                        "
                        :aria-expanded="open ? 'true' : 'false'"
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
                                    max-w-32
                                    truncate

                                    text-xs

                                    leading-tight
                                    font-semibold
                                    text-[var(--theme-text-strong)]
                                "
                                title="{{ trim(($perfilUsuario->nombres ?? $perfilUsuario->name ?? '') . ' ' . ($perfilUsuario->apellido_paterno ?? '')) }}"
                            >
                                {{ $perfilNombreCorto }}
                            </p>

                            @if (filled($perfilUnidad))
                                <p
                                    class="
                                        mt-0.5
                                        max-w-32
                                        truncate

                                        text-[10px]

                                        leading-tight
                                        text-[var(--theme-text-muted)]
                                    "
                                    title="{{ $perfilUnidad }}"
                                >
                                    {{ $perfilUnidad }}
                                </p>
                            @endif

                        </div>


                        {{-- Avatar --}}
                        <div
                            class="
                                shrink-0

                                w-9
                                h-9

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-surface-soft)]
                                text-[var(--theme-text-muted)]

                                group-hover:text-[var(--theme-primary)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-5 h-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                aria-hidden="true"
                            ><use href="{{ \App\Support\Icono::url('i-8464a27') }}"></use></svg>
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
                        ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>

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
                                    ><use href="{{ \App\Support\Icono::url('i-8464a27') }}"></use></svg>
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
                                        {{ $perfilUnidad ?: 'Sin hotel asignado' }}
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
                            <a
                                href="{{ route('ajustes.index', ['section' => 'cuenta']) }}"
                                wire:navigate.hover
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
                                ><use href="{{ \App\Support\Icono::url('i-8464a27') }}"></use></svg>

                                <span>Cuenta</span>
                            </a>


                            {{-- Notificaciones --}}
                            <button
                                type="button"
                                @click="
                                    open = false;
                                    window.openNotificationsPanelFast();
                                "
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
                                ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>

                                <span class="flex-1 text-left">
                                    Notificaciones
                                </span>

                                <span
                                    x-show="unreadNotifications > 0"
                                    x-cloak

                                    class="
                                        w-2
                                        h-2
                                        rounded-full
                                        bg-[var(--theme-danger)]
                                    "

                                    aria-hidden="true"
                                ></span>
                            </button>

                            {{-- ============================================================
                                APARIENCIA
                                Un solo botón con el modo actual; al hacer clic se
                                despliegan Claro / Oscuro / Sistema debajo.
                            ============================================================ --}}
                            <div x-data="{ modos: false }" x-on:keydown.escape.stop="modos ? (modos = false) : (open = false)">
                                @php
                                    $iconosModo = [
                                        'light' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
                                        'dark' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
                                        'system' => '<rect x="3" y="4" width="18" height="13" rx="1.5"/><path d="M8 21h8M12 17v4"/>',
                                    ];
                                    $nombresModo = ['light' => 'Claro', 'dark' => 'Oscuro', 'system' => 'Sistema'];
                                @endphp

                                <button
                                    type="button"
                                    x-on:click="modos = ! modos"
                                    x-bind:aria-expanded="modos ? 'true' : 'false'"
                                    aria-controls="menu-modos-tema"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-[var(--theme-text-strong)] hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)] aria-expanded:bg-[var(--theme-primary-soft-subtle)] transition-colors"
                                >
                                    {{-- Icono del modo actual --}}
                                    @foreach ($iconosModo as $claveModo => $iconoModo)
                                        <svg x-show="theme === @js($claveModo)" @if ($claveModo !== 'light') x-cloak @endif class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconoModo !!}</svg>
                                    @endforeach

                                    <span class="flex-1 text-left">Apariencia</span>

                                    <span
                                        class="text-xs text-[var(--theme-text-muted)]"
                                        x-text="({ light: 'Claro', dark: 'Oscuro', system: 'Sistema' })[theme] ?? 'Sistema'"
                                    >Sistema</span>

                                    <svg class="w-4 h-4 shrink-0 text-[var(--theme-text-muted)] transition-transform duration-150" x-bind:class="modos && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-8fdbbcf') }}"></use></svg>
                                </button>

                                <div
                                    id="menu-modos-tema"
                                    x-show="modos"
                                    x-cloak
                                    x-transition.opacity.duration.120ms
                                    role="group"
                                    aria-label="Modo de color"
                                    class="mt-0.5 space-y-0.5 pl-8"
                                >
                                    @foreach ($nombresModo as $claveModo => $nombreModo)
                                        <button
                                            type="button"
                                            x-on:click="window.setTrackItTheme(@js($claveModo)); theme = @js($claveModo);"
                                            x-bind:aria-pressed="theme === @js($claveModo) ? 'true' : 'false'"
                                            x-bind:class="theme === @js($claveModo) ? 'text-[var(--theme-primary)] font-medium' : 'text-[var(--theme-text)]'"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)] transition-colors"
                                        >
                                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconosModo[$claveModo] !!}</svg>
                                            <span class="flex-1 text-left">{{ $nombreModo }}</span>
                                            <svg x-show="theme === @js($claveModo)" x-cloak class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-e7e7dec') }}"></use></svg>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Seguridad --}}
                            <a
                                href="{{ route('ajustes.index', ['section' => 'seguridad']) }}"
                                wire:navigate.hover
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
                                ><use href="{{ \App\Support\Icono::url('i-518c3f4') }}"></use></svg>

                                <span>Seguridad</span>
                            </a>

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
                                ><use href="{{ \App\Support\Icono::url('i-633a49c') }}"></use></svg>

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
                                    ><use href="{{ \App\Support\Icono::url('i-910a9f0') }}"></use></svg>

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
                <main id="contenido-principal" tabindex="-1" class="p-6 outline-none">

                    {{-- Esqueleto mientras carga la siguiente página (wire:navigate) --}}
                    @include('layouts.partials.carga-navegacion')

                    {{-- Aviso fijo si faltan datos para recuperar la contraseña --}}
                    @include('layouts.partials.aviso-cuenta-incompleta')

                    @include('layouts.partials.encabezado', ['variante' => 'movil', 'encabezado' => $encabezadoPagina ?? null])

                    {{--
                        «Volver» arriba a la izquierda en todas las pantallas.
                        Si la pantalla ya lo puso en su propia fila de acciones
                        (layouts/partials/boton-volver), no se repite aquí.
                        El contenido de la página ya se generó en este punto.
                    --}}
                    @php
                        $contenidoPagina = $__env->yieldContent('content');
                    @endphp

                    @unless (app()->bound('trackit.volver.incluido') || $__env->hasSection('sin-volver'))
                        <div class="mb-4 hidden items-center md:flex">
                            @include('layouts.partials.boton-volver')
                        </div>
                    @endunless

                    {!! $contenidoPagina !!}

                </main>

            </div>

    </div>

    {{-- ============================================================
        PANEL GLOBAL DE NOTIFICACIONES
    ============================================================ --}}
    {{--
        Menú inferior (móvil): Inicio · Inventario · Escanear · Movimientos · Más
        @persist lo conserva al navegar (no se redibuja). La opción
        activa se marca con #trackitRutaActual, que sí viene en cada página.
    --}}
    <span
        id="trackitRutaActual"
        hidden
        data-ruta="{{ \Illuminate\Support\Facades\Route::currentRouteName() }}"
        data-tipo="{{ request()->routeIs('equipos.index') ? (string) request()->query('tipo_activo', '') : '' }}"
    ></span>

    @persist('menu-inferior')
        @include('layouts.partials.menu-inferior')
    @endpersist

    {{-- Escáner de códigos de barras (se abre desde «Escanear» del menú inferior) --}}
    @include('layouts.partials.escaner-movil')

    {{-- «Mover a la papelera»: confirmación global (App\Livewire\PapeleraConfirmar) --}}
    <livewire:papelera-confirmar />

    <livewire:notifications-panel
        :initial-unread-count="$initialUnreadNotifications"
    />

    @include('layouts.partials.sidebar-script')

    {{-- Funciones de la vista general (cargada en diferido) --}}
    @include('layouts.partials.dashboard-script')


    <script>    
        (() => {

            /*
            |--------------------------------------------------------------------------
            | HEADER AUTOMÁTICO EN MÓVIL
            |--------------------------------------------------------------------------
            |
            | - Al bajar: se oculta.
            | - Al subir ligeramente: reaparece.
            | - Cerca del inicio: siempre visible.
            | - En escritorio: siempre visible.
            | - Si sidebar, perfil o notificaciones están abiertos:
            |   permanece visible.
            |
            */

            const MOBILE_HEADER_BREAKPOINT =
                768;


            const MOBILE_HEADER_HIDE_AFTER =
                80;


            const MOBILE_HEADER_HIDE_DISTANCE =
                14;


            const MOBILE_HEADER_SHOW_DISTANCE =
                8;


            /*
            |--------------------------------------------------------------------------
            | Estado global
            |--------------------------------------------------------------------------
            */

            window.__trackItMobileHeaderState ??= {
                lastScrollY: 0,
                downDistance: 0,
                upDistance: 0,
                ticking: false,
            };


            const state =
                window.__trackItMobileHeaderState;


            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

            function isMobileHeaderViewport() {

                return window.innerWidth
                    < MOBILE_HEADER_BREAKPOINT;

            }


            function getMobileAppHeader() {

                return document.getElementById(
                    'mobileAppHeader'
                );

            }


            function mobileOverlayIsOpen() {

                /*
                * Sidebar móvil.
                */
                const sidebar =
                    document.getElementById(
                        'sidebar'
                    );


                const sidebarOpen =
                    sidebar
                    ?.classList
                    .contains(
                        'translate-x-0'
                    )
                    ?? false;


                /*
                * Panel de notificaciones.
                */
                const notifications =
                    document.getElementById(
                        'notifications-panel-root'
                    );


                const notificationsOpen =
                    Boolean(
                        notifications
                        &&
                        !notifications
                            .classList
                            .contains(
                                'hidden'
                            )
                    );


                /*
                * Dropdown del perfil.
                *
                * Alpine actualiza aria-expanded
                * en el botón principal.
                */
                const profileButton =
                    document.querySelector(
                        '#mobileAppHeader button[aria-haspopup="true"][aria-expanded="true"]'
                    );


                const profileOpen =
                    Boolean(
                        profileButton
                    );


                return (
                    sidebarOpen
                    ||
                    notificationsOpen
                    ||
                    profileOpen
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Mostrar
            |--------------------------------------------------------------------------
            */

            function showMobileAppHeader() {

                const header =
                    getMobileAppHeader();


                if (!header) {
                    return;
                }


                header.style.transform =
                    'translateY(0)';


                header.dataset.mobileHeaderHidden =
                    '0';

            }


            /*
            |--------------------------------------------------------------------------
            | Ocultar
            |--------------------------------------------------------------------------
            */

            function hideMobileAppHeader() {

                const header =
                    getMobileAppHeader();


                if (!header) {
                    return;
                }


                if (!isMobileHeaderViewport()) {
                    return;
                }


                header.style.transform =
                    'translateY(-100%)';


                header.dataset.mobileHeaderHidden =
                    '1';

            }


            /*
            |--------------------------------------------------------------------------
            | Exponer mostrar header
            |--------------------------------------------------------------------------
            */

            window.showTrackItMobileHeader =
                showMobileAppHeader;


            /*
            |--------------------------------------------------------------------------
            | Procesar scroll
            |--------------------------------------------------------------------------
            */

            function handleMobileHeaderScroll() {

                state.ticking =
                    false;


                /*
                * Escritorio:
                *
                * nunca ocultamos el header.
                */
                if (!isMobileHeaderViewport()) {

                    showMobileAppHeader();


                    state.lastScrollY =
                        window.scrollY;


                    state.downDistance =
                        0;


                    state.upDistance =
                        0;


                    return;
                }


                const currentScrollY =
                    Math.max(
                        window.scrollY,
                        0
                    );


                /*
                * Cerca de la parte superior:
                *
                * header siempre visible.
                */
                if (
                    currentScrollY
                    <= 24
                ) {

                    showMobileAppHeader();


                    state.lastScrollY =
                        currentScrollY;


                    state.downDistance =
                        0;


                    state.upDistance =
                        0;


                    return;
                }


                /*
                * Si una interfaz superior está abierta,
                * el header no debe desaparecer.
                */
                if (
                    mobileOverlayIsOpen()
                ) {

                    showMobileAppHeader();


                    state.lastScrollY =
                        currentScrollY;


                    state.downDistance =
                        0;


                    state.upDistance =
                        0;


                    return;
                }


                const delta =
                    currentScrollY
                    - state.lastScrollY;


                /*
                * Ignorar movimientos mínimos.
                */
                if (
                    Math.abs(delta)
                    < 2
                ) {
                    return;
                }


                /*
                * Scroll hacia abajo.
                */
                if (delta > 0) {

                    state.downDistance +=
                        delta;


                    state.upDistance =
                        0;


                    if (
                        currentScrollY
                            > MOBILE_HEADER_HIDE_AFTER
                        &&
                        state.downDistance
                            >= MOBILE_HEADER_HIDE_DISTANCE
                    ) {

                        hideMobileAppHeader();


                        state.downDistance =
                            0;
                    }

                }


                /*
                * Scroll hacia arriba.
                */
                else {

                    state.upDistance +=
                        Math.abs(delta);


                    state.downDistance =
                        0;


                    if (
                        state.upDistance
                        >= MOBILE_HEADER_SHOW_DISTANCE
                    ) {

                        showMobileAppHeader();


                        state.upDistance =
                            0;
                    }

                }


                state.lastScrollY =
                    currentScrollY;

            }


            /*
            |--------------------------------------------------------------------------
            | Scroll optimizado con requestAnimationFrame
            |--------------------------------------------------------------------------
            */

            function requestMobileHeaderUpdate() {

                if (state.ticking) {
                    return;
                }


                state.ticking =
                    true;


                window.requestAnimationFrame(
                    handleMobileHeaderScroll
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Sincronizar
            |--------------------------------------------------------------------------
            */

            function resetMobileHeader() {

                showMobileAppHeader();


                state.lastScrollY =
                    Math.max(
                        window.scrollY,
                        0
                    );


                state.downDistance =
                    0;


                state.upDistance =
                    0;


                state.ticking =
                    false;

            }


            /*
            |--------------------------------------------------------------------------
            | Listeners una sola vez
            |--------------------------------------------------------------------------
            */

            if (
                !window.__trackItMobileHeaderListenersInstalled
            ) {

                window.__trackItMobileHeaderListenersInstalled =
                    true;


                window.addEventListener(
                    'scroll',
                    requestMobileHeaderUpdate,
                    {
                        passive: true,
                    }
                );


                window.addEventListener(
                    'resize',
                    resetMobileHeader
                );


                /*
                * Después de wire:navigate,
                * el DOM del header puede haber cambiado.
                */
                document.addEventListener(
                    'livewire:navigated',
                    resetMobileHeader
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Primera carga
            |--------------------------------------------------------------------------
            */

            resetMobileHeader();

        })();
    </script>


    {{--
        Avisos globales (toast). @persist los conserva al navegar con
        wire:navigate, así un aviso emitido antes de cambiar de página
        sigue visible.
    --}}
    @persist('avisos-globales-v2')
        <x-ui.toast-region />

        {{-- Ventana de confirmación del sistema ($dispatch('confirmar', {...})) --}}
        @include('layouts.partials.confirmacion')

        @include('layouts.partials.avisos-eventos')
    @endpersist

    {{--
        Avisos enviados con session()->flash() antes de una redirección.
        Va fuera de @persist para leerse en cada página nueva.

        Formato recomendado:
            session()->flash('toast', ['type' => 'success', 'message' => '...']);

        También se muestran como éxito los avisos anteriores que se
        guardaban con estas claves.
    --}}
    @php
        $avisosSesion = [];

        if (is_array(session('toast'))) {
            $avisosSesion[] = session('toast');
        }

        foreach (['mantenimientoCreado', 'mantenimientoActualizado', 'success', 'usuarioCreado', 'organizacion_mensaje'] as $claveAviso) {
            if (is_string(session($claveAviso)) && session($claveAviso) !== '') {
                $avisosSesion[] = ['type' => 'success', 'message' => session($claveAviso)];
            }
        }
    @endphp

    @if ($avisosSesion !== [])
        <div
            x-data
            x-init="setTimeout(() => @js($avisosSesion).forEach(aviso => $dispatch('toast', aviso)), 50)"
            hidden
        ></div>
    @endif

    @stack('scripts')

    </body>
    </html>