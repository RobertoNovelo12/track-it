{{-- ============================================================
    CARGA ENTRE PANTALLAS (MÓVIL)
    ------------------------------------------------------------
    En el celular, al cambiar de pantalla con wire:navigate no había
    ninguna señal y parecía congelado. Ahora aparece al centro el
    logotipo de la organización con un destello de cristal y, debajo,
    una barra gruesa de bordes redondeados que avanza:

    1. livewire:navigate (si nadie lo canceló, p. ej. «cambios sin
       guardar»): se muestra tras 120 ms (las cargas rápidas no
       parpadean) y la barra avanza cada vez más lento hasta ~90 %.
    2. livewire:navigated: la barra se llena y se desvanece. Desde
       ahí cada sección muestra su placeholder (componentes lazy).

    Solo en pantallas de hasta 767 px; en PC se mantiene como está.
    Cubre solo el contenido: el encabezado y el menú inferior quedan
    visibles y sin desenfoque.
    Va dentro de @persist para seguir visible durante el cambio.
    Estilos propios (no depende de compilar Tailwind).
============================================================ --}}
@php
    $organizacionCarga = app(\App\Services\OrganizacionService::class);
@endphp

<div id="trackitCargaPantalla" class="tcp" aria-hidden="true" role="progressbar" aria-label="Cargando pantalla">
    <div class="tcp-centro">
        <div class="tcp-logo">
            <img
                class="org-logo tcp-logo-img"
                src="{{ $organizacionCarga->logoInterfazUrl() }}"
                @if ($organizacionCarga->logoInterfazUrlOscuro()) data-src-claro="{{ $organizacionCarga->logoInterfazUrl() }}" data-src-oscuro="{{ $organizacionCarga->logoInterfazUrlOscuro() }}" @endif
                alt=""
                data-dark-mode="{{ $organizacionCarga->logoInterfazModoOscuro() }}"
                style="--org-logo-w: {{ $organizacionCarga->logoAncho(150) }}px; --org-logo-bg: {{ $organizacionCarga->logoColorFondo() }};"
            >
            {{-- Destello: recorre solo la silueta del logotipo (máscara). --}}
            <span class="tcp-brillo"></span>
        </div>

        <div class="tcp-barra"><span class="tcp-avance"></span></div>
    </div>
</div>

<style>
    .tcp {
        position: fixed;
        inset: 0; /* top y bottom se ajustan al mostrar (encabezado / menú) */
        /* Debajo del menú inferior (z-index 45) para no tapar «Escanear». */
        z-index: 44;
        display: none;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--theme-bg, var(--theme-surface)) 97%, transparent);
        -webkit-backdrop-filter: blur(3px);
        backdrop-filter: blur(3px);
        opacity: 0;
        transition: opacity 160ms ease-out;
    }

    .tcp[data-visible='true'] { display: flex; }
    .tcp[data-entrando='true'] { opacity: 1; }

    .tcp-centro {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 22px;
        width: min(240px, 70vw);
    }

    .tcp-logo {
        position: relative;
        display: inline-block;
        line-height: 0;
    }

    .tcp-logo-img {
        width: var(--org-logo-w, 150px);
        max-width: 70vw;
        height: auto;
        object-fit: contain;
    }

    /* Reflejo de cristal: franja clara diagonal que cruza el logotipo. */
    .tcp-brillo {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, 0.85) 50%, transparent 65%);
        background-size: 250% 100%;
        background-repeat: no-repeat;
        -webkit-mask: var(--tcp-mascara) center / contain no-repeat;
        mask: var(--tcp-mascara) center / contain no-repeat;
        animation: tcp-brillo 1.6s ease-in-out infinite;
    }

    @keyframes tcp-brillo {
        0% { background-position: 130% 0; }
        60%, 100% { background-position: -30% 0; }
    }

    .tcp-barra {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        overflow: hidden;
        background: var(--theme-surface-soft, rgba(127, 127, 127, 0.15));
        box-shadow: inset 0 0 0 1px var(--theme-border, rgba(127, 127, 127, 0.25));
    }

    .tcp-avance {
        display: block;
        height: 100%;
        width: 0;
        border-radius: inherit;
        background: var(--theme-primary);
        transition: width 260ms ease-out;
    }

    @media (prefers-reduced-motion: reduce) {
        .tcp-brillo { animation: none; opacity: 0; }
        .tcp-avance { transition: none; }
    }
</style>

<script>
    (() => {
        if (window.__trackitCargaPantalla) {
            return;
        }

        window.__trackitCargaPantalla = true;

        const movil = window.matchMedia('(max-width: 767.98px)');

        let retardo = null;
        let avance = null;
        let salida = null;
        let progreso = 0;
        let activa = false;

        const nodo = () => document.getElementById('trackitCargaPantalla');

        const pintar = () => {
            const barra = nodo()?.querySelector('.tcp-avance');

            if (barra) barra.style.width = progreso + '%';
        };

        const mostrar = () => {
            const el = nodo();

            if (! el) return;

            // La máscara del destello usa el logotipo que se ve (claro u oscuro).
            const img = el.querySelector('.tcp-logo-img');
            const src = img?.currentSrc || img?.getAttribute('src');

            if (src) el.style.setProperty('--tcp-mascara', 'url("' + src.replace(/"/g, '\\"') + '")');

            /*
             * Solo sobre el contenido: el encabezado (búsqueda, perfil,
             * notificaciones) y el menú inferior quedan visibles y sin
             * desenfoque.
             */
            const encabezado = document.getElementById('mobileAppHeader');
            const barra = document.querySelector('.mi-barra');
            const alto = window.innerHeight;
            // offsetParent no sirve con elementos fijos: se mira su tamaño.
            const seVe = (nodo) => !! nodo && nodo.getBoundingClientRect().height > 0
                && getComputedStyle(nodo).visibility !== 'hidden';

            const arriba = seVe(encabezado)
                ? Math.max(0, Math.min(alto, encabezado.getBoundingClientRect().bottom))
                : 0;

            const abajo = seVe(barra) && barra.dataset.oculta !== 'true'
                ? Math.max(0, alto - barra.getBoundingClientRect().top)
                : 0;

            el.style.top = arriba + 'px';
            el.style.bottom = abajo + 'px';

            clearTimeout(salida);
            progreso = 8;
            pintar();

            el.dataset.visible = 'true';
            el.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => { el.dataset.entrando = 'true'; });

            // Cada vez más lento: se acerca a 90 % sin llegar.
            clearInterval(avance);
            avance = setInterval(() => {
                progreso += (90 - progreso) * 0.08;
                pintar();
            }, 180);
        };

        const ocultar = () => {
            clearTimeout(retardo);
            clearInterval(avance);

            const el = nodo();

            // Si está a la vista se quita siempre, aunque el estado se haya perdido.
            if (! el || el.dataset.visible !== 'true') {
                activa = false;
                return;
            }

            activa = false;
            progreso = 100;
            pintar();

            salida = setTimeout(() => {
                el.dataset.entrando = 'false';

                salida = setTimeout(() => {
                    el.dataset.visible = 'false';
                    el.setAttribute('aria-hidden', 'true');
                    progreso = 0;
                    pintar();
                }, 170);
            }, 220);
        };

        /*
         * Cada salida lleva un número. Con una pantalla ya precargada,
         * Livewire puede terminar (livewire:navigated) antes de que corra
         * el setTimeout de abajo; sin este control la carga se mostraba
         * después de llegar y ya nunca se quitaba.
         */
        let salidas = 0;
        let llegadas = 0;
        let seguro = null;

        document.addEventListener('livewire:navigate', (evento) => {
            const numero = ++salidas;

            // Esperar a que «cambios sin guardar» decida si cancela.
            setTimeout(() => {
                if (evento.defaultPrevented || ! movil.matches || llegadas >= numero) return;

                activa = true;
                clearTimeout(retardo);
                retardo = setTimeout(() => {
                    if (activa && llegadas < numero) mostrar();
                }, 120);

                // Por si la navegación nunca termina: se quita a los 15 s.
                clearTimeout(seguro);
                seguro = setTimeout(ocultar, 15000);
            }, 0);
        });

        document.addEventListener('livewire:navigated', () => {
            llegadas = salidas;
            clearTimeout(seguro);
            ocultar();
        });

        // Si la navegación falla (sin conexión), no dejar la pantalla tapada.
        window.addEventListener('offline', ocultar);
    })();
</script>
