{{-- ============================================================
    MENÚ INFERIOR (MÓVIL, < 768 px)
    ------------------------------------------------------------
    Sustituye al menú lateral en teléfonos:

    Inicio · Inventario · Escanear · Movimientos · Más

    - Inventario abre una hoja con «Todos los equipos» y los tipos.
    - Escanear abre el escáner de códigos de barras (escaner-movil).
    - Más abre una hoja con el resto de módulos (según el rol),
      Mi cuenta y el modo claro/oscuro.
    - Se oculta mientras el teclado está abierto (campo enfocado).

    Estilos en línea (no requieren recompilar Vite). En escritorio
    no se muestra: ahí sigue el menú lateral.
============================================================ --}}
@php
    $puedeGestionar = \Illuminate\Support\Facades\Gate::allows('gestionar');
    $puedeAdministrar = \Illuminate\Support\Facades\Gate::allows('administrar');

    /*
     * Tipos de equipo: misma consulta y caché que el menú lateral.
     */
    $tiposMenuInferior = [];

    if (Route::has('equipos.index')) {
        try {
            $tiposMenuInferior = app(\App\Services\SystemCacheService::class)->rememberSimple(
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
            $tiposMenuInferior = [];
        }
    }

    $enInventario = request()->routeIs('equipos.*');
    $tipoActivoInferior = request()->routeIs('equipos.index')
        ? (string) request()->query('tipo_activo', '')
        : '';

    /*
     * Opciones de la hoja «Más» (solo las que el rol puede abrir).
     */
    $opcionesMas = array_values(array_filter([
        [
            'label' => 'Mantenimientos',
            'route' => 'mantenimientos.index',
            'activo' => request()->routeIs('mantenimientos.*'),
            'icon' => 'mantenimientos',
            'visible' => $puedeGestionar,
        ],
        [
            'label' => 'Reportes',
            'route' => 'reportes.index',
            'activo' => request()->routeIs('reportes.*'),
            'icon' => 'reportes',
            'visible' => $puedeGestionar,
        ],
        [
            'label' => 'Seguridad',
            'route' => 'usuarios.index',
            'activo' => request()->routeIs('usuarios.*'),
            'icon' => 'seguridad',
            'visible' => $puedeAdministrar,
        ],
        [
            'label' => 'Administración',
            'route' => 'catalogos.index',
            'activo' => request()->routeIs('catalogos.*'),
            'icon' => 'catalogos',
            'visible' => $puedeGestionar,
        ],
    ], fn (array $opcion): bool => $opcion['visible'] && Route::has($opcion['route'])));

    $masActivo = collect($opcionesMas)->contains('activo', true)
        || request()->routeIs('ajustes.*');

    $organizacionMenu = app(\App\Services\OrganizacionService::class);
@endphp

<style>
    /* Solo en teléfonos: en escritorio se usa el menú lateral. */
    .mi-raiz { display: none; }

    @media (max-width: 767.98px) {
        .mi-raiz { display: block; }

        /* Espacio para que la barra no tape el final del contenido. */
        #contenido-principal {
            padding-bottom: calc(6rem + env(safe-area-inset-bottom, 0px));
        }

        /*
         * Sin la barra azul de progreso de Livewire al navegar: en el
         * teléfono la respuesta la da el esqueleto de carga
         * (layouts/partials/carga-navegacion).
         */
        #nprogress {
            display: none !important;
        }

        /* Los avisos aparecen por encima de la barra. */
        #trackitToastRegion {
            bottom: calc(5.25rem + env(safe-area-inset-bottom, 0px));
        }
    }

    .mi-barra {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 45;
        display: flex;
        align-items: stretch;
        justify-content: space-around;
        gap: 0;
        height: calc(4rem + env(safe-area-inset-bottom, 0px));
        padding: 0 0.25rem env(safe-area-inset-bottom, 0px);
        background: var(--theme-surface);
        border-top: 1px solid var(--theme-border);
        box-shadow: 0 -6px 18px rgba(0, 0, 0, 0.06);
        transition: transform 180ms ease-out;
    }

    .mi-barra[data-oculta="true"] {
        transform: translateY(110%);
    }

    .mi-item {
        flex: 1 1 0;
        min-width: 0;
        min-height: 3rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        border: 0;
        background: transparent;
        color: var(--theme-text-muted);
        font-size: 0.65rem;
        font-weight: 500;
        letter-spacing: -0.01em;
        line-height: 1;
        text-decoration: none;
        border-radius: 0.5rem;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .mi-item svg {
        width: 1.375rem;
        height: 1.375rem;
        flex-shrink: 0;
    }

    .mi-item span {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mi-item:focus-visible {
        outline: 2px solid var(--theme-primary);
        outline-offset: -2px;
    }

    /* Activo: color, peso y una marca superior (no solo color). */
    .mi-item[data-activo="true"] {
        color: var(--theme-primary);
        font-weight: 600;
        position: relative;
    }

    /*
     * Marca superior: un solo indicador que se desliza hacia la
     * sección tocada (antes de que cargue la página, así no se ve
     * trabado). Sin JavaScript se queda oculto y el activo sigue
     * marcado por color y peso.
     */
    .mi-indicador {
        position: absolute;
        top: 0;
        left: 0;
        width: 1.75rem;
        height: 3px;
        border-radius: 0 0 3px 3px;
        background: var(--theme-primary);
        opacity: 0;
        pointer-events: none;
        transform: translateX(0);
        transition:
            transform 280ms cubic-bezier(0.22, 0.8, 0.24, 1),
            opacity 150ms ease-out;
        will-change: transform;
    }

    .mi-item {
        transition: color 180ms ease-out;
    }

    /* Botón central: Escanear. */
    .mi-escanear {
        flex: 1 1 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        border: 0;
        background: transparent;
        color: var(--theme-text);
        font-size: 0.65rem;
        font-weight: 600;
        line-height: 1;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .mi-escanear-circulo {
        width: 3rem;
        height: 3rem;
        margin-top: -1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: var(--theme-primary);
        color: #FFFFFF;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
        border: 3px solid var(--theme-surface);
    }

    .mi-escanear-circulo svg {
        width: 1.375rem;
        height: 1.375rem;
    }

    .mi-escanear:focus-visible .mi-escanear-circulo {
        outline: 2px solid var(--theme-primary);
        outline-offset: 2px;
    }

    /* Hojas (Inventario / Más). */
    .mi-fondo {
        position: fixed;
        inset: 0;
        z-index: 60;
        background: var(--theme-overlay, rgba(0, 0, 0, 0.45));
    }

    .mi-hoja {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 61;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        background: var(--theme-surface);
        border-top: 1px solid var(--theme-border);
        border-radius: 1rem 1rem 0 0;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.2);
    }

    .mi-hoja-asa {
        display: flex;
        justify-content: center;
        padding: 0.6rem 0 0.25rem;
        touch-action: none;
    }

    .mi-hoja-asa::before {
        content: "";
        width: 2.5rem;
        height: 4px;
        border-radius: 9999px;
        background: var(--theme-border-strong);
    }

    .mi-hoja-cabecera {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.25rem 1.25rem 0.75rem;
        touch-action: none;
    }

    .mi-hoja-titulo {
        font-size: 1rem;
        font-weight: 600;
        color: var(--theme-text-strong);
    }

    .mi-hoja-cerrar {
        width: 2.75rem;
        height: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 0.5rem;
        background: transparent;
        color: var(--theme-text-muted);
        cursor: pointer;
    }

    .mi-hoja-cerrar:focus-visible,
    .mi-opcion:focus-visible {
        outline: 2px solid var(--theme-primary);
        outline-offset: -2px;
    }

    .mi-hoja-cuerpo {
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0 0.75rem 1rem;
    }

    .mi-opcion {
        width: 100%;
        min-height: 3rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0 0.75rem;
        border: 0;
        border-radius: 0.5rem;
        background: transparent;
        color: var(--theme-text);
        font-size: 0.9375rem;
        font-weight: 500;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .mi-opcion svg {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
        color: var(--theme-text-muted);
    }

    .mi-opcion:active {
        background: var(--theme-surface-soft);
    }

    .mi-opcion[data-activo="true"] {
        background: var(--theme-primary-soft);
        color: var(--theme-primary);
        font-weight: 600;
    }

    .mi-opcion[data-activo="true"] svg {
        color: var(--theme-primary);
    }

    .mi-opcion-principal {
        font-weight: 600;
        color: var(--theme-text-strong);
    }

    .mi-pasos {
        margin: 0.25rem 0.75rem 0.5rem 2.75rem;
        padding-left: 1rem;
        color: var(--theme-text-muted);
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .mi-pasos li + li {
        margin-top: 0.25rem;
    }

    .mi-pasos strong {
        color: var(--theme-text-strong);
        font-weight: 600;
    }

    .mi-separador {
        height: 1px;
        margin: 0.5rem 0.75rem;
        background: var(--theme-border);
    }

    .mi-subtitulo {
        padding: 0.5rem 0.75rem 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--theme-text-muted);
    }

    .mi-logo-hoja {
        display: flex;
        justify-content: center;
        padding: 0.25rem 1.25rem 1rem;
    }

    .mi-logo-hoja img {
        width: min(var(--org-logo-w, 135px), 170px);
        max-height: 3.5rem;
        height: auto;
        object-fit: contain;
    }

    .mi-anim-entrar { transition: transform 200ms ease-out; }
    .mi-anim-salir { transition: transform 150ms ease-in; }
    .mi-abajo { transform: translateY(100%); }
    .mi-arriba { transform: translateY(0); }

    @media (prefers-reduced-motion: reduce) {
        .mi-indicador,
        .mi-item,
        .mi-barra,
        .mi-anim-entrar,
        .mi-anim-salir { transition: none; }
    }
</style>

<div
    class="mi-raiz"

    x-data="{
        hoja: null,

        init() {
            // Posición inicial (sin animar) bajo la sección activa.
            this.$nextTick(() => this.moverIndicador(this.itemActivo(), false));

            const reubicar = () => this.moverIndicador(this.itemActivo(), false);
            window.addEventListener('resize', reubicar, { passive: true });
            document.addEventListener('livewire:navigating', () => window.removeEventListener('resize', reubicar), { once: true });
        },

        itemActivo() {
            return this.$root.querySelector('.mi-barra .mi-item[data-activo=true]');
        },

        moverIndicador(elemento, animar = true) {
            const indicador = this.$refs.indicador;

            if (! indicador) {
                return;
            }

            if (! elemento) {
                indicador.style.opacity = '0';

                return;
            }

            const barra = indicador.parentElement.getBoundingClientRect();
            const caja = elemento.getBoundingClientRect();
            const x = caja.left - barra.left + (caja.width - indicador.offsetWidth) / 2;

            if (! animar) {
                indicador.style.transition = 'none';
            }

            indicador.style.transform = 'translateX(' + x + 'px)';
            indicador.style.opacity = '1';

            if (! animar) {
                indicador.offsetWidth;
                indicador.style.transition = '';
            }
        },

        /*
         * Al tocar: la sección se marca y la línea se desliza en ese
         * momento; la página nueva llega con la línea ya en su lugar.
         */
        marcar(elemento) {
            if (! elemento) {
                return;
            }

            this.$root.querySelectorAll('.mi-barra .mi-item').forEach((item) => {
                item.dataset.activo = item === elemento ? 'true' : 'false';

                if (item === elemento) {
                    item.setAttribute('aria-current', 'page');
                } else {
                    item.removeAttribute('aria-current');
                }
            });

            this.moverIndicador(elemento);
        },
        teclado: false,
        inicioToque: null,
        origenFoco: null,

        abrir(nombre, evento) {
            this.origenFoco = evento?.currentTarget ?? null;
            this.hoja = nombre;
            document.documentElement.style.overflow = 'hidden';

            this.$nextTick(() => {
                [...(this.$refs['hoja_' + nombre]?.querySelectorAll('a, button') ?? [])].find(elemento => elemento.offsetParent !== null)?.focus();
            });
        },

        cerrar() {
            if (! this.hoja) {
                return;
            }

            this.hoja = null;
            document.documentElement.style.overflow = '';
            this.origenFoco?.focus?.();
        },

        /*
         * Teclado en pantalla: con un campo de texto enfocado la barra
         * se oculta para no tapar el formulario.
         */
        esCampoDeTexto(elemento) {
            if (! elemento || ! elemento.matches) {
                return false;
            }

            if (elemento.matches('textarea, select, [contenteditable=true], [contenteditable=\'\']')) {
                return true;
            }

            return elemento.matches('input')
                && ! ['checkbox', 'radio', 'button', 'submit', 'reset', 'range', 'color', 'file', 'image', 'hidden'].includes(String(elemento.type || '').toLowerCase());
        },

        revisarTeclado() {
            setTimeout(() => {
                this.teclado = this.esCampoDeTexto(document.activeElement);
            }, 60);
        },

        /* Deslizar hacia abajo desde el asa o la cabecera cierra la hoja. */
        empezarToque(evento) {
            this.inicioToque = evento.touches?.[0]?.clientY ?? null;
        },

        terminarToque(evento) {
            const fin = evento.changedTouches?.[0]?.clientY ?? null;

            if (this.inicioToque !== null && fin !== null && fin - this.inicioToque > 60) {
                this.cerrar();
            }

            this.inicioToque = null;
        },

        /* El foco no sale de la hoja abierta (Tab / Shift+Tab). */
        atraparFoco(evento) {
            const hoja = this.$refs['hoja_' + this.hoja];

            if (! hoja) {
                return;
            }

            const elementos = [...hoja.querySelectorAll('a[href], button:not([disabled])')].filter(elemento => elemento.offsetParent !== null);

            if (elementos.length === 0) {
                return;
            }

            const primero = elementos[0];
            const ultimo = elementos[elementos.length - 1];

            if (evento.shiftKey && document.activeElement === primero) {
                evento.preventDefault();
                ultimo.focus();
            } else if (! evento.shiftKey && document.activeElement === ultimo) {
                evento.preventDefault();
                primero.focus();
            }
        },
    }"

    x-on:focusin.window="revisarTeclado()"
    x-on:focusout.window="revisarTeclado()"
    x-on:keydown.escape.window="cerrar()"
    x-on:livewire:navigating.window="hoja = null; document.documentElement.style.overflow = ''"
>

    {{-- ========================================================
        BARRA
    ======================================================== --}}
    <nav
        class="mi-barra"
        aria-label="Menú principal"
        x-bind:data-oculta="teclado ? 'true' : 'false'"
        x-bind:aria-hidden="teclado ? 'true' : 'false'"
    >
        <span class="mi-indicador" x-ref="indicador" aria-hidden="true"></span>

        {{-- INICIO --}}
        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            x-on:click="marcar($el)"
            class="mi-item"
            data-activo="{{ request()->routeIs('dashboard') ? 'true' : 'false' }}"
            @if (request()->routeIs('dashboard')) aria-current="page" @endif
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 11.5L12 4l9 7.5"/>
                <path d="M5 10v10h14V10"/>
                <path d="M9 20v-6h6v6"/>
            </svg>
            <span>Inicio</span>
        </a>

        {{-- INVENTARIO (abre la hoja de tipos) --}}
        @if (Route::has('equipos.index'))
            <button
                type="button"
                class="mi-item"
                x-ref="itemInventario"
                data-activo="{{ $enInventario ? 'true' : 'false' }}"
                @if ($enInventario) aria-current="page" @endif
                aria-haspopup="dialog"
                x-bind:aria-expanded="hoja === 'inventario' ? 'true' : 'false'"
                x-on:click="abrir('inventario', $event)"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 8l-9-5-9 5 9 5 9-5z"/>
                    <path d="M3 8v8l9 5 9-5V8"/>
                    <path d="M12 13v8"/>
                </svg>
                <span>Inventario</span>
            </button>
        @endif

        {{-- ESCANEAR (botón central) --}}
        <button
            type="button"
            class="mi-escanear"
            aria-label="Escanear código de barras"
            x-on:click="$dispatch('abrir-escaner')"
        >
            <span class="mi-escanear-circulo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2"/>
                    <path d="M8 9v6M11 9v6M13.5 9v6M16 9v6"/>
                </svg>
            </span>
            <span>Escanear</span>
        </button>

        {{-- MOVIMIENTOS --}}
        @if ($puedeGestionar && Route::has('asignaciones.index'))
            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate
                x-on:click="marcar($el)"
                class="mi-item"
                data-activo="{{ request()->routeIs('asignaciones.*') ? 'true' : 'false' }}"
                @if (request()->routeIs('asignaciones.*')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 7h15"/>
                    <path d="M16 4l3 3-3 3"/>
                    <path d="M20 17H5"/>
                    <path d="M8 14l-3 3 3 3"/>
                </svg>
                <span>Movimientos</span>
            </a>
        @endif

        {{-- MÁS --}}
        <button
            type="button"
            class="mi-item"
            x-ref="itemMas"
            data-activo="{{ $masActivo ? 'true' : 'false' }}"
            @if ($masActivo) aria-current="page" @endif
            aria-haspopup="dialog"
            x-bind:aria-expanded="hoja === 'mas' ? 'true' : 'false'"
            x-on:click="abrir('mas', $event)"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6h16"/>
                <path d="M4 12h16"/>
                <path d="M4 18h16"/>
            </svg>
            <span>Más</span>
        </button>
    </nav>


    {{-- ========================================================
        FONDO DE LAS HOJAS
    ======================================================== --}}
    <div
        class="mi-fondo"
        x-show="hoja !== null"
        x-cloak
        x-transition.opacity.duration.150ms
        x-on:click="cerrar()"
        aria-hidden="true"
    ></div>


    {{-- ========================================================
        HOJA: INVENTARIO
    ======================================================== --}}
    @if (Route::has('equipos.index'))
        <div
            x-ref="hoja_inventario"
            x-on:click="if ($event.target.closest('a[href]')) marcar($refs.itemInventario)"
            class="mi-hoja"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mi-titulo-inventario"
            x-show="hoja === 'inventario'"
            x-cloak
            x-transition:enter="mi-anim-entrar"
            x-transition:enter-start="mi-abajo"
            x-transition:enter-end="mi-arriba"
            x-transition:leave="mi-anim-salir"
            x-transition:leave-start="mi-arriba"
            x-transition:leave-end="mi-abajo"
            x-on:keydown.tab="atraparFoco($event)"
        >
            <div class="mi-hoja-asa" x-on:touchstart.passive="empezarToque($event)" x-on:touchend="terminarToque($event)" aria-hidden="true"></div>

            <div class="mi-hoja-cabecera" x-on:touchstart.passive="empezarToque($event)" x-on:touchend="terminarToque($event)">
                <h2 id="mi-titulo-inventario" class="mi-hoja-titulo">Inventario</h2>

                <button type="button" class="mi-hoja-cerrar" x-on:click="cerrar()" aria-label="Cerrar">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <div class="mi-hoja-cuerpo">
                @php
                    $todosActivo = request()->routeIs('equipos.index') && $tipoActivoInferior === '';
                @endphp

                <a
                    href="{{ route('equipos.index') }}"
                    wire:navigate
                    class="mi-opcion mi-opcion-principal"
                    data-activo="{{ $todosActivo ? 'true' : 'false' }}"
                    @if ($todosActivo) aria-current="page" @endif
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z"/>
                        <path d="M3 8v8l9 5 9-5V8"/>
                        <path d="M12 13v8"/>
                    </svg>
                    Todos los equipos
                </a>

                @if ($tiposMenuInferior !== [])
                    <div class="mi-separador" role="presentation"></div>
                    <p class="mi-subtitulo">Por tipo de equipo</p>

                    @foreach ($tiposMenuInferior as $tipo)
                        @php
                            $tipoActivo = $tipoActivoInferior === (string) $tipo['id'];
                        @endphp

                        <a
                            href="{{ route('equipos.index', ['tipo_activo' => $tipo['id']]) }}"
                            wire:navigate
                            class="mi-opcion"
                            data-activo="{{ $tipoActivo ? 'true' : 'false' }}"
                            @if ($tipoActivo) aria-current="page" @endif
                        >
                            {{ $tipo['nombre'] }}
                        </a>
                    @endforeach
                @endif

                @if ($puedeGestionar && Route::has('equipos.create'))
                    <div class="mi-separador" role="presentation"></div>

                    <a
                        href="{{ route('equipos.create') }}"
                        wire:navigate
                        class="mi-opcion"
                        data-activo="{{ request()->routeIs('equipos.create') ? 'true' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Añadir equipo
                    </a>
                @endif
            </div>
        </div>
    @endif


    {{-- ========================================================
        HOJA: MÁS
    ======================================================== --}}
    <div
        x-ref="hoja_mas"
        x-on:click="if ($event.target.closest('a[href]')) marcar($refs.itemMas)"
        class="mi-hoja"
        role="dialog"
        aria-modal="true"
        aria-label="Más opciones"
        x-show="hoja === 'mas'"
        x-cloak
        x-transition:enter="mi-anim-entrar"
        x-transition:enter-start="mi-abajo"
        x-transition:enter-end="mi-arriba"
        x-transition:leave="mi-anim-salir"
        x-transition:leave-start="mi-arriba"
        x-transition:leave-end="mi-abajo"
        x-on:keydown.tab="atraparFoco($event)"
    >
        <div class="mi-hoja-asa" x-on:touchstart.passive="empezarToque($event)" x-on:touchend="terminarToque($event)" aria-hidden="true"></div>

        <div class="mi-hoja-cabecera" x-on:touchstart.passive="empezarToque($event)" x-on:touchend="terminarToque($event)">
            <span class="mi-hoja-titulo">Más</span>

            <button type="button" class="mi-hoja-cerrar" x-on:click="cerrar()" aria-label="Cerrar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div class="mi-hoja-cuerpo">
            {{-- Logotipo completo --}}
            <div class="mi-logo-hoja">
                <img
                    class="org-logo"
                    src="{{ $organizacionMenu->logoInterfazUrl() }}"

                    @if ($organizacionMenu->logoInterfazUrlOscuro()) data-src-claro="{{ $organizacionMenu->logoInterfazUrl() }}" data-src-oscuro="{{ $organizacionMenu->logoInterfazUrlOscuro() }}" @endif
                    alt="{{ $organizacionMenu->nombre() }}"
                    data-dark-mode="{{ $organizacionMenu->logoInterfazModoOscuro() }}"
                    style="--org-logo-w: {{ $organizacionMenu->logoAncho(135) }}px; --org-logo-bg: {{ $organizacionMenu->logoColorFondo() }};"
                >
            </div>

            @foreach ($opcionesMas as $opcion)
                <a
                    href="{{ route($opcion['route']) }}"
                    wire:navigate
                    class="mi-opcion"
                    data-activo="{{ $opcion['activo'] ? 'true' : 'false' }}"
                    @if ($opcion['activo']) aria-current="page" @endif
                >
                    @switch($opcion['icon'])
                        @case('mantenimientos')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2z"/>
                            </svg>
                            @break

                        @case('reportes')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 20h16"/>
                                <path d="M7 17V9"/>
                                <path d="M12 17V4"/>
                                <path d="M17 17v-6"/>
                            </svg>
                            @break

                        @case('seguridad')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                            </svg>
                            @break

                        @case('catalogos')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="3" width="16" height="18" rx="2"/>
                                <path d="M8 8h8"/>
                                <path d="M8 12h8"/>
                                <path d="M8 16h5"/>
                            </svg>
                            @break
                    @endswitch

                    {{ $opcion['label'] }}
                </a>
            @endforeach

            @if ($opcionesMas !== [])
                <div class="mi-separador" role="presentation"></div>
            @endif

            {{-- MI CUENTA --}}
            @if (Route::has('ajustes.index'))
                @php
                    $cuentaActiva = request()->routeIs('ajustes.*');
                @endphp

                <a
                    href="{{ route('ajustes.index', ['section' => 'cuenta']) }}"
                    wire:navigate
                    class="mi-opcion"
                    data-activo="{{ $cuentaActiva ? 'true' : 'false' }}"
                    @if ($cuentaActiva) aria-current="page" @endif
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    Mi cuenta
                </a>
            @endif

            {{--
                INSTALAR APLICACIÓN (ver layouts/partials/pwa-head):
                botón si el navegador lo permite; en iPhone, los pasos.
            --}}
            <div
                x-data="{
                    disponible: !! window.trackitInstalacion,
                    instalada: window.trackitAppInstalada?.() ?? false,
                    ios: window.trackitEsIos?.() ?? false,
                    verPasos: false,
                    init() {
                        window.addEventListener('trackit-instalable', () => this.disponible = !! window.trackitInstalacion);
                        window.addEventListener('trackit-instalada', () => { this.instalada = true; this.disponible = false; });
                    },
                }"
                x-show="! instalada && (disponible || ios)"
                x-cloak
            >
                <button
                    type="button"
                    class="mi-opcion"
                    x-show="disponible"
                    x-on:click="window.trackitInstalarApp()"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 4v11"/>
                        <path d="M7.5 10.5L12 15l4.5-4.5"/>
                        <path d="M5 19h14"/>
                    </svg>
                    Instalar aplicación
                </button>

                <button
                    type="button"
                    class="mi-opcion"
                    x-show="! disponible && ios"
                    x-on:click="verPasos = ! verPasos"
                    x-bind:aria-expanded="verPasos ? 'true' : 'false'"
                    aria-controls="mi-pasos-ios"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 4v11"/>
                        <path d="M7.5 10.5L12 15l4.5-4.5"/>
                        <path d="M5 19h14"/>
                    </svg>
                    Instalar en el iPhone
                </button>

                <ol id="mi-pasos-ios" class="mi-pasos" x-show="verPasos" x-cloak>
                    <li>En Safari, toca <strong>Compartir</strong> (el cuadro con la flecha hacia arriba).</li>
                    <li>Elige <strong>Agregar a pantalla de inicio</strong>.</li>
                    <li>Toca <strong>Agregar</strong>. Track IT aparecerá como una app.</li>
                </ol>
            </div>

            {{-- MODO CLARO / OSCURO --}}
            <button
                type="button"
                class="mi-opcion"
                x-data="{ oscuro: document.documentElement.classList.contains('dark') }"
                x-on:trackit-theme-changed.window="oscuro = document.documentElement.classList.contains('dark')"
                x-on:click="window.setTrackItTheme?.(oscuro ? 'light' : 'dark')"
            >
                <svg x-show="oscuro" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
                </svg>
                <svg x-show="! oscuro" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
                </svg>
                <span x-text="oscuro ? 'Modo claro' : 'Modo oscuro'">Modo oscuro</span>
            </button>
        </div>
    </div>
</div>
