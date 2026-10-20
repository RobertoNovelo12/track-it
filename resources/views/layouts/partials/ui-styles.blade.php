<style>
    /*
    |--------------------------------------------------------------------------
    | SISTEMA DE CARGA Y MOVIMIENTO (UX fase 1)
    |--------------------------------------------------------------------------
    |
    | Valores globales que comparten todos los componentes de la carpeta
    | components/ui. La guía visual está en /ui/componentes (solo Admin).
    |
    | Ritmo de animaciones
    |   --motion-fast   Hover y respuesta al clic          (100–180 ms)
    |   --motion-base   Menús, tooltips y desplegables     (120–200 ms)
    |   --motion-modal  Modales y paneles                  (160–240 ms)
    |
    | Placeholders
    |   --skeleton-base / --skeleton-shine  colores del skeleton
    |
    | Radios
    |   --radius-sm (6px) controles pequeños, --radius-md (8px) botones e
    |   inputs, --radius-lg (12px) tarjetas.
    |
    */

    :root {
        --motion-fast: 150ms;
        --motion-base: 180ms;
        --motion-modal: 220ms;
        --ease-standard: cubic-bezier(0.2, 0, 0, 1);
        --ease-out: cubic-bezier(0.22, 1, 0.36, 1);

        --skeleton-base: var(--theme-surface-soft);
        --skeleton-shine: color-mix(in srgb, var(--theme-surface-soft) 55%, var(--theme-border-strong));

        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Skeleton: brillo tenue que recorre el bloque
    |--------------------------------------------------------------------------
    */

    .ui-skeleton {
        display: block;
        border-radius: var(--radius-sm);
        background-color: var(--skeleton-base);
        background-image: linear-gradient(
            90deg,
            var(--skeleton-base) 0%,
            var(--skeleton-shine) 50%,
            var(--skeleton-base) 100%
        );
        background-size: 200% 100%;
        animation: ui-skeleton-shine 1.4s ease-in-out infinite;
    }

    @keyframes ui-skeleton-shine {
        from { background-position: 100% 0; }
        to { background-position: -100% 0; }
    }


    /*
    |--------------------------------------------------------------------------
    | Spinner: un solo estilo para toda la aplicación
    |--------------------------------------------------------------------------
    */

    .ui-spinner {
        animation: ui-spin 0.8s linear infinite;
        transform-origin: center;
    }

    @keyframes ui-spin {
        to { transform: rotate(360deg); }
    }


    /*
    |--------------------------------------------------------------------------
    | Barra indeterminada (contenido que se actualiza)
    |--------------------------------------------------------------------------
    |
    | Franja que recorre el borde superior de una tabla o tarjeta mientras
    | se actualizan sus datos. Duración real desconocida: no simula avance.
    |
    */

    .ui-progress-indeterminate {
        width: 35%;
        border-radius: 9999px;
        animation: ui-progress 1.1s var(--ease-standard) infinite;
    }

    @keyframes ui-progress {
        from { transform: translateX(-100%); }
        to { transform: translateX(300%); }
    }


    /*
    |--------------------------------------------------------------------------
    | Entrada de contenido que reemplaza a un skeleton
    |--------------------------------------------------------------------------
    */

    .ui-fade-in {
        animation: ui-fade-in var(--motion-base) var(--ease-out) both;
    }

    @keyframes ui-fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }


    /*
    |--------------------------------------------------------------------------
    | Barra de progreso al navegar (Livewire wire:navigate)
    |--------------------------------------------------------------------------
    |
    | Usa el color principal del tema en lugar del azul de Livewire y no
    | muestra el spinner de esquina.
    |
    */

    #nprogress {
        pointer-events: none;
    }

    #nprogress .bar {
        background: var(--theme-primary) !important;
        height: 2px !important;
        z-index: 2000 !important;
    }

    #nprogress .peg {
        box-shadow: 0 0 10px var(--theme-primary), 0 0 5px var(--theme-primary) !important;
    }

    #nprogress .spinner {
        display: none !important;
    }


    /*
    |--------------------------------------------------------------------------
    | Reducir movimiento (preferencia del sistema operativo)
    |--------------------------------------------------------------------------
    |
    | Se eliminan transiciones y animaciones decorativas. Los spinners
    | siguen girando (más despacio) porque comunican que algo está en
    | proceso; el skeleton queda estático.
    |
    */

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }

        .ui-spinner,
        .animate-spin,
        .ui-progress-indeterminate {
            animation-duration: 1.6s !important;
            animation-iteration-count: infinite !important;
        }

        .ui-skeleton {
            animation: none !important;
        }
    }
    /* Alpine: elementos ocultos hasta que Alpine los inicializa. */
    [x-cloak] {
        display: none !important;
    }
</style>
