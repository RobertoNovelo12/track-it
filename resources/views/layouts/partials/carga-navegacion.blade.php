{{-- ============================================================
    CARGA AL NAVEGAR Y AL CARGAR COMPONENTES (solo móvil)
    ------------------------------------------------------------
    - Escritorio: sin cambios. Mientras llega la página se ve la
      barra de progreso y después el placeholder detallado de cada
      componente Livewire.
    - Móvil (< 768 px): un único spinner centrado.
      · Al tocar un enlace wire:navigate, si tarda más de 120 ms,
        el contenido se sustituye por el spinner.
      · Los placeholders de componentes lazy (los que Livewire
        marca con $wire.__lazyLoad) también se muestran como
        spinner, para todas las pantallas sin tocar cada vista.
    - Los menús (hojas del menú inferior, perfil) se cierran al
      tocar la opción, sin esperar a que cargue la página.
    Estilos en línea (no requieren Vite).
============================================================ --}}
<style>
    #esqueletoNavegacion {
        display: none;
    }

    @keyframes trackit-girar {
        to { transform: rotate(360deg); }
    }

    .trackit-spinner {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 9999px;
        border: 3px solid color-mix(in srgb, var(--theme-primary) 18%, transparent);
        border-top-color: var(--theme-primary);
        animation: trackit-girar 0.75s linear infinite;
    }

    @media (max-width: 767.98px) {
        /* Navegación entre páginas */
        html[data-navegando="true"] #esqueletoNavegacion {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
        }

        html[data-navegando="true"] #contenido-principal > :not(#esqueletoNavegacion) {
            display: none !important;
        }

        /* Placeholders de componentes lazy → spinner */
        [x-init*="__lazyLoad"],
        [x-intersect*="__lazyLoad"] {
            position: relative;
            min-height: 60vh;
        }

        [x-init*="__lazyLoad"] > *,
        [x-intersect*="__lazyLoad"] > * {
            display: none !important;
        }

        [x-init*="__lazyLoad"]::after,
        [x-intersect*="__lazyLoad"]::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2.25rem;
            height: 2.25rem;
            margin: -1.125rem 0 0 -1.125rem;
            border-radius: 9999px;
            border: 3px solid color-mix(in srgb, var(--theme-primary) 18%, transparent);
            border-top-color: var(--theme-primary);
            animation: trackit-girar 0.75s linear infinite;
        }

        /* Si hay varios en la misma pantalla, solo gira el primero */
        [x-init*="__lazyLoad"] ~ [x-init*="__lazyLoad"],
        [x-intersect*="__lazyLoad"] ~ [x-intersect*="__lazyLoad"] {
            min-height: 0;
        }

        [x-init*="__lazyLoad"] ~ [x-init*="__lazyLoad"]::after,
        [x-intersect*="__lazyLoad"] ~ [x-intersect*="__lazyLoad"]::after {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .trackit-spinner,
        [x-init*="__lazyLoad"]::after,
        [x-intersect*="__lazyLoad"]::after {
            animation-duration: 1.6s;
        }
    }
</style>

<div id="esqueletoNavegacion" role="status" aria-label="Cargando">
    <div class="trackit-spinner"></div>
</div>

<script>
    (() => {
        // Una sola vez por carga completa (el layout se reemplaza al navegar).
        if (window.trackitCargaNavegacion) {
            return;
        }

        window.trackitCargaNavegacion = true;

        let temporizador = null;
        let limite = null;

        const terminar = () => {
            clearTimeout(temporizador);
            clearTimeout(limite);
            document.documentElement.removeAttribute('data-navegando');
            document.getElementById('contenido-principal')?.removeAttribute('aria-busy');
        };

        const alNavegar = (evento) => {
            // Navegación cancelada (p. ej. cambios sin guardar): nada que mostrar.
            if (evento.defaultPrevented) {
                return;
            }

            // Cierra al instante hojas y menús desplegables abiertos.
            window.dispatchEvent(new CustomEvent('trackit-cerrar-menus'));

            if (! window.matchMedia('(max-width: 767.98px)').matches) {
                return;
            }

            temporizador = setTimeout(() => {
                document.documentElement.setAttribute('data-navegando', 'true');
                document.getElementById('contenido-principal')?.setAttribute('aria-busy', 'true');
            }, 120);

            // Por si la navegación se cancela o falla.
            limite = setTimeout(terminar, 10000);
        };

        document.addEventListener('livewire:navigate', (evento) => {
            terminar();

            // Se espera a que los demás oyentes decidan si cancelan.
            setTimeout(() => alNavegar(evento), 0);
        });

        document.addEventListener('livewire:navigated', terminar);
    })();
</script>
