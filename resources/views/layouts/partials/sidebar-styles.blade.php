<style>
    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    |
    | El sidebar únicamente tendrá dos estados finales:
    |
    | Abierto:   16rem / 256px
    | Cerrado:    5rem / 80px
    |
    | Durante la transición NO eliminamos elementos con display:none.
    | De esta forma evitamos saltos y reacomodos del layout.
    |
    */

    #sidebar {
        background: var(--theme-bg, #f8f8f7);

        overflow: hidden;

        transition:
            width 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change: width;
    }


    /*
    |--------------------------------------------------------------------------
    | CONTENIDO PRINCIPAL
    |--------------------------------------------------------------------------
    */

    #mainContent {
        transition:
            margin-left 180ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change: margin-left;
    }


    /*
    |--------------------------------------------------------------------------
    | CABECERA DEL SIDEBAR
    |--------------------------------------------------------------------------
    |
    | El botón queda absoluto para que su movimiento no dependa
    | del tamaño del logo.
    |
    | Esto elimina el salto del botón al colapsar.
    |
    */

    #sidebarHeader {
        position: relative;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    #sidebarLogoWrap {
        max-width: 150px;

        opacity: 1;

        overflow: hidden;

        white-space: nowrap;

        transition:
            max-width 160ms cubic-bezier(0.2, 0.8, 0.2, 1),
            opacity 70ms ease-out 50ms;

        will-change:
            max-width,
            opacity;
    }


    #sidebarLogo {
        display: block;

        transition:
            opacity 100ms ease-out;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGO EN MODO OSCURO
    |--------------------------------------------------------------------------
    |
    | No animamos "filter".
    |
    | El cambio debe ocurrir inmediatamente para evitar el destello
    | negro/blanco que se percibía al cambiar de tema.
    |
    */

    html.dark #sidebarLogo {
        filter: brightness(0) invert(1);

        opacity: 0.92;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN COLAPSAR
    |--------------------------------------------------------------------------
    */

    #sidebarCollapseButton {
        position: absolute;

        top: 50%;
        right: 1.25rem;

        transform: translateY(-50%);

        transition:
            right 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            box-shadow 100ms ease-out;

        will-change: right;
    }


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS DE NAVEGACIÓN
    |--------------------------------------------------------------------------
    */

    .sidebar-nav-link {
        position: relative;

        justify-content: flex-start;

        overflow: hidden;

        border: 1px solid transparent;

        box-shadow: none;

        transition:
            box-shadow 100ms ease-out;
    }


    /*
    |--------------------------------------------------------------------------
    | OPCIÓN ACTIVA
    |--------------------------------------------------------------------------
    |
    | Sin azul.
    | Superficie limpia.
    | Borde tenue.
    | Sombra discreta.
    |
    */

    .sidebar-nav-active {
        background: var(--theme-surface);

        border-color: var(--theme-border);

        color: var(--theme-text-strong);

        box-shadow:
            0 2px 8px rgba(15, 23, 42, 0.06),
            0 1px 2px rgba(15, 23, 42, 0.03);
    }


    /*
    |--------------------------------------------------------------------------
    | HOVER
    |--------------------------------------------------------------------------
    */

    .sidebar-nav-link:not(.sidebar-nav-active):hover {
        background: var(--theme-surface);

        border-color: var(--theme-border);

        color: var(--theme-text-strong);

        box-shadow:
            0 2px 8px rgba(15, 23, 42, 0.035);
    }


    /*
    |--------------------------------------------------------------------------
    | SOMBRA EN MODO OSCURO
    |--------------------------------------------------------------------------
    */

    html.dark .sidebar-nav-active {
        box-shadow:
            0 2px 10px rgba(0, 0, 0, 0.20),
            0 1px 3px rgba(0, 0, 0, 0.12);
    }


    html.dark
    .sidebar-nav-link:not(.sidebar-nav-active):hover {
        box-shadow:
            0 2px 8px rgba(0, 0, 0, 0.12);
    }


    /*
    |--------------------------------------------------------------------------
    | ETIQUETAS
    |--------------------------------------------------------------------------
    |
    | Muy importante:
    |
    | NO usamos:
    |
    | display:none
    | max-width:0
    |
    | durante el cierre.
    |
    | Eso era lo que ocasionaba el salto de los iconos.
    |
    */

    .sidebar-label {
        opacity: 1;

        white-space: nowrap;

        transform: translateX(0);

        transition:
            opacity 80ms ease-out 60ms,
            transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change:
            opacity,
            transform;
    }


    /*
    |--------------------------------------------------------------------------
    | ICONOS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    .sidebar-nav-link > svg {
        flex: 0 0 18px;

        width: 18px;
        height: 18px;

        transition:
            transform 170ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change: transform;
    }


    /*
    |--------------------------------------------------------------------------
    | PIE DEL SIDEBAR
    |--------------------------------------------------------------------------
    */

    .sidebar-footer-link {
        position: relative;

        justify-content: flex-start;

        overflow: hidden;

        transition:
            box-shadow 100ms ease-out;
    }


    .sidebar-footer-link > svg {
        flex: 0 0 18px;

        width: 18px;
        height: 18px;

        transition:
            transform 170ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change: transform;
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCADOR
    |--------------------------------------------------------------------------
    |
    | El buscador NUNCA desaparece del layout.
    |
    | Sidebar abierto:
    |
    | [ 🔍  Búsqueda rápida             Ctrl K ]
    |
    | Sidebar cerrado:
    |
    | [ 🔍 ]
    |
    | De esta forma el menú nunca sube ni baja.
    |
    */

    #sidebarQuickSearch {
        flex-shrink: 0;

        overflow: hidden;

        /*
        | h-11 = 44px
        | pb-4 = 16px
        |
        | Total vertical constante: 60px
        */
        height: 60px;

        padding-left: 1rem;
        padding-right: 1rem;
        padding-bottom: 1rem;

        transition:
            padding-left 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            padding-right 180ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change:
            padding-left,
            padding-right;
    }


    /*
    |--------------------------------------------------------------------------
    | TARJETA DEL BUSCADOR
    |--------------------------------------------------------------------------
    */

    #sidebarQuickSearch > div {
        width: 100%;

        margin-left: auto;
        margin-right: auto;

        overflow: hidden;

        transition:
            width 180ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change: width;
    }


    /*
    |--------------------------------------------------------------------------
    | LUPA DEL BUSCADOR
    |--------------------------------------------------------------------------
    */

    #sidebarQuickSearch > div > svg {
        top: 50%;

        transform: translateY(-50%);

        transition:
            left 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1);

        will-change:
            left,
            transform;
    }


    /*
    |--------------------------------------------------------------------------
    | CAMPO DE BÚSQUEDA
    |--------------------------------------------------------------------------
    */

    #sidebarQuickSearchInput {
        opacity: 1;

        transition:
            opacity 70ms ease-out 70ms;

        will-change: opacity;
    }


    /*
    |--------------------------------------------------------------------------
    | CTRL K
    |--------------------------------------------------------------------------
    */

    #sidebarQuickSearch span {
        opacity: 1;

        transition:
            opacity 70ms ease-out 70ms;

        will-change: opacity;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN DE BÚSQUEDA COMPACTO
    |--------------------------------------------------------------------------
    |
    | Ya no lo utilizaremos.
    |
    | Conservamos el elemento en el partial por ahora para no modificar
    | dos archivos al mismo tiempo, pero siempre permanece oculto.
    |
    */

    #sidebarCollapsedSearchButton {
        display: none !important;
    }


    /*
    |--------------------------------------------------------------------------
    | TÍTULO "MENÚ"
    |--------------------------------------------------------------------------
    |
    | No lo eliminamos del flujo al cerrar.
    |
    | Conserva su altura para que los enlaces no den un salto vertical.
    |
    */

    .sidebar-expanded-only:not(#sidebarQuickSearch) {
        opacity: 1;

        transition:
            opacity 70ms ease-out 60ms;

        will-change: opacity;
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE TEMA SIN FLASH DE COLOR
    |--------------------------------------------------------------------------
    |
    | Todo el color del sidebar debe reaccionar inmediatamente a las
    | variables CSS del tema.
    |
    | Sólo se animan propiedades espaciales:
    |
    | - width
    | - transform
    | - opacity
    | - margin
    | - padding
    |
    */

    #sidebar,
    #sidebar button,
    #sidebar a,
    #sidebar input,
    #sidebar span,
    #sidebar svg,
    #sidebar div {
        color-scheme: inherit;
    }


    /*
    |--------------------------------------------------------------------------
    | ICONOS
    |--------------------------------------------------------------------------
    |
    | currentColor toma inmediatamente el valor correspondiente al tema.
    |--------------------------------------------------------------------------
    */

    #sidebar svg {
        color: inherit;
    }


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS QUE DEFINEN SU PROPIO COLOR
    |--------------------------------------------------------------------------
    */

    #sidebar .sidebar-nav-link > svg,
    #sidebar .sidebar-footer-link > svg,
    #sidebarQuickSearch > div > svg {
        color: var(--theme-text-muted);
    }


    /*
    |--------------------------------------------------------------------------
    | TEXTO
    |--------------------------------------------------------------------------
    */

    #sidebar .sidebar-label {
        transition-property:
            opacity,
            transform;
    }


    /*
    |--------------------------------------------------------------------------
    | FONDOS Y BORDES
    |--------------------------------------------------------------------------
    |
    | Sin transición deliberadamente.
    |--------------------------------------------------------------------------
    */

    #sidebar .sidebar-nav-active,
    #sidebar .sidebar-nav-link,
    #sidebar .sidebar-footer-link,
    #sidebarCollapseButton,
    #sidebarQuickSearch > div {
        transition-property: none;
    }


    /*
    |--------------------------------------------------------------------------
    | RECUPERAR ÚNICAMENTE LAS TRANSICIONES DE MOVIMIENTO
    |--------------------------------------------------------------------------
    */

    #sidebarCollapseButton {
        transition:
            right 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            box-shadow 100ms ease-out;
    }


    #sidebar .sidebar-label {
        transition:
            opacity 80ms ease-out 60ms,
            transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
    }


    #sidebar .sidebar-nav-link > svg,
    #sidebar .sidebar-footer-link > svg {
        transition:
            transform 170ms cubic-bezier(0.2, 0.8, 0.2, 1);
    }


    #sidebarQuickSearch > div {
        transition:
            width 180ms cubic-bezier(0.2, 0.8, 0.2, 1);
    }


    #sidebarQuickSearch > div > svg {
        transition:
            left 180ms cubic-bezier(0.2, 0.8, 0.2, 1),
            transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1);
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR COLAPSADO - ESCRITORIO
    |--------------------------------------------------------------------------
    */

    @media (min-width: 768px) {

        /*
        |--------------------------------------------------------------------------
        | ANCHO
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed #sidebar {
            width: 5rem !important;
        }


        html.sidebar-collapsed #mainContent {
            margin-left: 5rem !important;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed #sidebarLogoWrap {
            max-width: 0;

            opacity: 0;

            pointer-events: none;

            transition-delay:
                0ms,
                0ms;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTÓN DE COLAPSAR
        |--------------------------------------------------------------------------
        |
        | Como mide 36px:
        |
        | 50% - 18px
        |
        | lo coloca exactamente al centro del sidebar.
        |
        */

        html.sidebar-collapsed #sidebarCollapseButton {
            right: calc(50% - 18px);
        }


        /*
        |--------------------------------------------------------------------------
        | ETIQUETAS
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed .sidebar-label {
            opacity: 0;

            transform: translateX(-4px);

            pointer-events: none;

            transition-delay:
                0ms,
                0ms;
        }


        /*
        |--------------------------------------------------------------------------
        | TÍTULO MENÚ
        |--------------------------------------------------------------------------
        |
        | Se vuelve transparente, pero conserva el mismo espacio vertical.
        |
        */

        html.sidebar-collapsed
        .sidebar-expanded-only:not(#sidebarQuickSearch) {
            opacity: 0;

            pointer-events: none;

            transition-delay: 0ms;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCADOR
        |--------------------------------------------------------------------------
        |
        | El formulario permanece exactamente donde estaba.
        |
        | Únicamente reducimos sus laterales hasta obtener una cápsula
        | de aproximadamente 40px.
        |
        */

        html.sidebar-collapsed #sidebarQuickSearch {
            display: block !important;

            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }


        /*
        |--------------------------------------------------------------------------
        | TARJETA DEL BUSCADOR CERRADO
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed
        #sidebarQuickSearch > div {
            width: 40px;
        }


        /*
        |--------------------------------------------------------------------------
        | INPUT CERRADO
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed
        #sidebarQuickSearchInput {
            opacity: 0;

            pointer-events: none;

            transition-delay: 0ms;
        }


        /*
        |--------------------------------------------------------------------------
        | CTRL K CERRADO
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed
        #sidebarQuickSearch span {
            opacity: 0;

            pointer-events: none;

            transition-delay: 0ms;
        }


        /*
        |--------------------------------------------------------------------------
        | CENTRAR LUPA
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed
        #sidebarQuickSearch > div > svg {
            left: 50%;

            transform:
                translate(-50%, -50%);
        }


        /*
        |--------------------------------------------------------------------------
        | ICONOS DEL MENÚ
        |--------------------------------------------------------------------------
        |
        | No cambiamos justify-content.
        |
        | El icono recorre únicamente 5px.
        | Así no existe ningún salto entre posiciones.
        |
        */

        html.sidebar-collapsed
        .sidebar-nav-link > svg {
            transform: translateX(5px);
        }


        /*
        |--------------------------------------------------------------------------
        | ICONOS INFERIORES
        |--------------------------------------------------------------------------
        */

        html.sidebar-collapsed
        .sidebar-footer-link > svg {
            transform: translateX(7px);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MÓVIL
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767px) {

        /*
        |--------------------------------------------------------------------------
        | En móvil no usamos el estado visual colapsado de escritorio.
        |--------------------------------------------------------------------------
        */

        #sidebarHeader {
            min-height: 5.5rem;
        }


        #sidebarLogo {
            width: 128px;

            height: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | Botón móvil
        |--------------------------------------------------------------------------
        */

        #sidebarCollapseButton {
            right: 1rem;
        }


        /*
        |--------------------------------------------------------------------------
        | El buscador siempre es completo dentro del drawer.
        |--------------------------------------------------------------------------
        */

        #sidebarQuickSearch {
            padding-left: 1rem;
            padding-right: 1rem;
        }


        #sidebarQuickSearch > div {
            width: 100%;
        }


        #sidebarQuickSearchInput,
        #sidebarQuickSearch span {
            opacity: 1;

            pointer-events: auto;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BORDE ARRASTRABLE
    |--------------------------------------------------------------------------
    |
    | Zona de agarre del borde derecho del sidebar.
    |
    | Visualmente es casi invisible, pero dejamos un área de 8px para que
    | sea fácil tomarla con el mouse.
    |
    */

    #sidebarResizeHandle {
        display: none;

        position: absolute;

        top: 0;
        right: -4px;

        width: 8px;
        height: 100%;

        z-index: 50;

        cursor: col-resize;

        touch-action: none;

        user-select: none;
    }


    /*
    |--------------------------------------------------------------------------
    | INDICADOR VISUAL
    |--------------------------------------------------------------------------
    |
    | Dibujamos una línea muy discreta únicamente al acercar el mouse.
    |
    */

    #sidebarResizeHandle::after {
        content: '';

        position: absolute;

        top: 0;
        bottom: 0;
        left: 50%;

        width: 2px;

        transform:
            translateX(-50%)
            scaleY(0.96);

        border-radius: 999px;

        background:
            var(--theme-border);

        opacity: 0;

        transition:
            opacity 120ms ease-out,
            background-color 120ms ease-out,
            transform 120ms ease-out;
    }


    /*
    |--------------------------------------------------------------------------
    | HOVER DEL BORDE
    |--------------------------------------------------------------------------
    */

    #sidebarResizeHandle:hover::after {
        opacity: 0.75;

        transform:
            translateX(-50%)
            scaleY(1);
    }


    /*
    |--------------------------------------------------------------------------
    | DURANTE EL ARRASTRE
    |--------------------------------------------------------------------------
    |
    | Cuando el usuario está arrastrando:
    |
    | - sidebar y contenido siguen exactamente al puntero;
    | - quitamos temporalmente la transición de ancho;
    | - mostramos ligeramente el borde;
    |
    */

    html.sidebar-resizing #sidebar {
        transition:
            transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1) !important;

        will-change: width;
    }


    html.sidebar-resizing #mainContent {
        transition: none !important;

        will-change: margin-left;
    }


    html.sidebar-resizing #sidebarResizeHandle::after {
        opacity: 1;

        transform:
            translateX(-50%)
            scaleY(1);
    }


    /*
    |--------------------------------------------------------------------------
    | CURSOR GLOBAL DURANTE EL ARRASTRE
    |--------------------------------------------------------------------------
    */

    html.sidebar-resizing,
    html.sidebar-resizing body {
        cursor: col-resize !important;
    }


    /*
    |--------------------------------------------------------------------------
    | ESCRITORIO
    |--------------------------------------------------------------------------
    */

    @media (min-width: 768px) {

        /*
        | Sobrescribimos la clase Tailwind "hidden" que dejamos
        | temporalmente en sidebar.blade.php.
        */

        #sidebarResizeHandle {
            display: block !important;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MÓVIL
    |--------------------------------------------------------------------------
    |
    | El resize no tiene sentido en el drawer móvil.
    |
    */

    @media (max-width: 767px) {

        #sidebarResizeHandle {
            display: none !important;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDUCIR MOVIMIENTO
    |--------------------------------------------------------------------------
    |
    | Respeta la preferencia de accesibilidad del sistema operativo.
    |
    */

    @media (prefers-reduced-motion: reduce) {

        #sidebar,
        #mainContent,
        #sidebarLogoWrap,
        #sidebarCollapseButton,
        .sidebar-label,
        .sidebar-nav-link,
        .sidebar-nav-link > svg,
        .sidebar-footer-link,
        .sidebar-footer-link > svg,
        #sidebarQuickSearch,
        #sidebarQuickSearch > div,
        #sidebarQuickSearch > div > svg,
        #sidebarQuickSearchInput,
        #sidebarQuickSearch span,
        .sidebar-expanded-only {
            transition-duration: 0.01ms !important;

            transition-delay: 0ms !important;
        }
    }
</style>