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
    |   Se definen en resources/css/app.css (@theme): --radius-md (4px)
    |   botones e inputs, --radius-lg (5px), --radius-xl (6px) tarjetas.
    |   Aquí NO se redefinen: antes este :root los subía a 6/8/12 px y
    |   pisaba la escala de Tailwind en todo el sistema.
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

    /*
    |--------------------------------------------------------------------------
    | CONTROL DESLIZANTE (input type="range") — clase .ui-range
    |--------------------------------------------------------------------------
    |
    | Pista delgada con el color de borde del tema; la parte recorrida en
    | --theme-primary (variable --valor = porcentaje, la pone la vista).
    | Perilla blanca con borde primario, como los controles del sistema.
    |
    */
    .ui-range {
        --valor: 50%;
        -webkit-appearance: none;
        appearance: none;
        height: 1.25rem;
        background: transparent;
        cursor: pointer;
    }

    .ui-range:focus {
        outline: none;
    }

    .ui-range::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 9999px;
        background: linear-gradient(
            to right,
            var(--theme-primary) 0%,
            var(--theme-primary) var(--valor),
            var(--theme-border-strong) var(--valor),
            var(--theme-border-strong) 100%
        );
    }

    .ui-range::-moz-range-track {
        height: 6px;
        border-radius: 9999px;
        background: var(--theme-border-strong);
    }

    .ui-range::-moz-range-progress {
        height: 6px;
        border-radius: 9999px;
        background: var(--theme-primary);
    }

    .ui-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        margin-top: -6px;
        border-radius: 9999px;
        border: 2px solid var(--theme-primary);
        background: var(--theme-surface);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.15);
        transition: transform 120ms ease-out, box-shadow 120ms ease-out;
    }

    .ui-range::-moz-range-thumb {
        width: 14px;
        height: 14px;
        border-radius: 9999px;
        border: 2px solid var(--theme-primary);
        background: var(--theme-surface);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.15);
        transition: transform 120ms ease-out, box-shadow 120ms ease-out;
    }

    .ui-range:hover::-webkit-slider-thumb,
    .ui-range:active::-webkit-slider-thumb {
        transform: scale(1.1);
    }

    .ui-range:hover::-moz-range-thumb,
    .ui-range:active::-moz-range-thumb {
        transform: scale(1.1);
    }

    .ui-range:focus-visible::-webkit-slider-thumb {
        box-shadow: 0 0 0 4px var(--theme-primary-soft);
    }

    .ui-range:focus-visible::-moz-range-thumb {
        box-shadow: 0 0 0 4px var(--theme-primary-soft);
    }

    .ui-range:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    /*
    |--------------------------------------------------------------------------
    | OJO DE CONTRASEÑA: la línea se borra / dibuja
    |--------------------------------------------------------------------------
    |
    | Contraseña oculta: ojo tachado. Al mostrarla (aria-pressed="true")
    | la línea se recoge hasta desaparecer y al ocultarla se vuelve a
    | trazar. pathLength="1" en la línea permite animarla con dashoffset.
    |
    */
    .ojo-tachado {
        stroke-dasharray: 1;
        stroke-dashoffset: 0;
        transition: stroke-dashoffset var(--motion-base, 180ms) var(--ease-standard, ease);
    }

    [aria-pressed="true"] .ojo-tachado {
        stroke-dashoffset: 1;
    }

    @media (prefers-reduced-motion: reduce) {
        .ojo-tachado { transition: none; }
    }

    html[data-movimiento="reducido"] .ojo-tachado {
        transition: none;
    }

    /*
    |--------------------------------------------------------------------------
    | ALTURA ÚNICA DE CAMPOS (40 px)
    |--------------------------------------------------------------------------
    |
    | Los formularios usaban tres alturas: py-2 (38 px), h-10 (40 px) y
    | py-2.5 (42 px), y el select con buscador quedaba más alto que los
    | campos de texto. Todos los campos de una línea (input y select
    | nativos con text-sm) miden 40 px, igual que el select con buscador.
    | No afecta a textarea, casillas ni campos compactos (text-xs).
    | Los campos que piden otra altura a propósito (h-11, h-12, py-3:
    | inicio de sesión, filtros del celular) se respetan.
    */
    input.text-sm:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="file"]):not([type="hidden"]):not(.h-full):not(.h-11):not(.h-12):not(.py-3):not(.py-3\.5),
    select.text-sm:not(.h-11):not(.h-12):not(.py-3) {
        height: 2.5rem;
        padding-top: 0;
        padding-bottom: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CAMPOS DE FECHA Y HORA
    |--------------------------------------------------------------------------
    |
    | El calendario que abre el navegador no admite estilos propios; lo
    | que sí se controla: que siga el tema (claro / oscuro), el color de
    | acento y el ícono del campo.
    |
    */
    input[type="date"],
    input[type="datetime-local"],
    input[type="month"],
    input[type="time"] {
        color-scheme: light;
        accent-color: var(--theme-primary);
    }

    html.dark input[type="date"],
    html.dark input[type="datetime-local"],
    html.dark input[type="month"],
    html.dark input[type="time"] {
        color-scheme: dark;
    }

    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="datetime-local"]::-webkit-calendar-picker-indicator,
    input[type="month"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        padding: 3px;
        border-radius: 4px;
        opacity: 0.55;
        transition: opacity 120ms ease-out, background-color 120ms ease-out;
    }

    input[type="date"]:hover::-webkit-calendar-picker-indicator,
    input[type="datetime-local"]:hover::-webkit-calendar-picker-indicator,
    input[type="month"]:hover::-webkit-calendar-picker-indicator,
    input[type="time"]:hover::-webkit-calendar-picker-indicator {
        opacity: 1;
        background-color: var(--theme-surface-soft);
    }
    /*
    |--------------------------------------------------------------------------
    | FOCO VISIBLE CON TECLADO (WCAG 2.4.7)
    |--------------------------------------------------------------------------
    |
    | Muchos controles quitan el contorno (outline-none). Con teclado
    | (:focus-visible) siempre se muestra uno del color principal; con
    | mouse o toque no aparece. Los campos de texto ya marcan el foco con
    | su borde, así que solo se refuerza en botones, enlaces y casillas.
    |
    */
    a:focus-visible,
    button:focus-visible,
    summary:focus-visible,
    [role="button"]:focus-visible,
    [role="switch"]:focus-visible,
    [role="tab"]:focus-visible,
    [role="option"]:focus-visible,
    [tabindex]:not([tabindex="-1"]):focus-visible,
    input[type="checkbox"]:focus-visible,
    input[type="radio"]:focus-visible,
    label:has(> input.sr-only:focus-visible) {
        outline: 2px solid var(--theme-primary) !important;
        outline-offset: 2px !important;
    }

    input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus-visible,
    select:focus-visible,
    textarea:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--theme-primary) 45%, transparent) !important;
        outline-offset: 0 !important;
    }
    /*
    |--------------------------------------------------------------------------
    | TEXTO DEL COLOR PRINCIPAL SOBRE SU FONDO SUAVE (modo claro)
    |--------------------------------------------------------------------------
    |
    | Pestañas, filtros y opciones seleccionadas usan texto principal
    | sobre fondo principal suave: con el azul original daba 4.2:1 (WCAG
    | pide 4.5:1). En modo claro el texto se oscurece un poco (5.7:1).
    | Se calcula con color-mix, así que también funciona con el color
    | personalizado de la organización. En modo oscuro ya cumplía.
    |
    */
    html:not(.dark) .bg-\[var\(--theme-primary-soft\)\].text-\[var\(--theme-primary\)\],
    html:not(.dark) .bg-\[var\(--theme-primary-soft-subtle\)\].text-\[var\(--theme-primary\)\],
    html:not(.dark) .bg-\[var\(--theme-primary-soft\)\] .text-\[var\(--theme-primary\)\],
    html:not(.dark) .bg-\[var\(--theme-primary-soft-subtle\)\] .text-\[var\(--theme-primary\)\],
    html:not(.dark) .hover\:bg-\[var\(--theme-primary-soft\)\].text-\[var\(--theme-primary\)\]:hover,
    html:not(.dark) .hover\:bg-\[var\(--theme-primary-soft-subtle\)\].text-\[var\(--theme-primary\)\]:hover {
        color: color-mix(in srgb, var(--theme-primary) 82%, #000000);
    }
</style>
