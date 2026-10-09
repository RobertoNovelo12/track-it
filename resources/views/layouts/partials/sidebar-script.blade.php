<script>
    (() => {

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN
        |--------------------------------------------------------------------------
        */

        const STORAGE_KEY =
            'sidebarCollapsed';

        const DESKTOP_BREAKPOINT =
            768;

        const SIDEBAR_OPEN_WIDTH =
            256;

        const SIDEBAR_CLOSED_WIDTH =
            80;

        /*
        |--------------------------------------------------------------------------
        | Punto de decisión del arrastre
        |--------------------------------------------------------------------------
        |
        | Si el usuario suelta el sidebar por debajo de este ancho,
        | se cerrará automáticamente.
        |
        | Por encima, volverá a abrirse completamente.
        |
        */

        const SIDEBAR_SNAP_THRESHOLD =
            150;

        const SIDEBAR_TRANSITION_MS =
            180;


        /*
        |--------------------------------------------------------------------------
        | ESTADO DE ARRASTRE
        |--------------------------------------------------------------------------
        */

        let resizeActive =
            false;

        let resizePointerId =
            null;

        let resizeCurrentWidth =
            SIDEBAR_OPEN_WIDTH;


        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function isMobileViewport() {

            return window.innerWidth
                < DESKTOP_BREAKPOINT;

        }


        function getSidebar() {

            return document.getElementById(
                'sidebar'
            );

        }


        function getMainContent() {

            return document.getElementById(
                'mainContent'
            );

        }


        function getOverlay() {

            return document.getElementById(
                'sidebarOverlay'
            );

        }


        function getSearchInput() {

            return document.getElementById(
                'sidebarQuickSearchInput'
            );

        }


        function getCollapseButton() {

            return document.getElementById(
                'sidebarCollapseButton'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLAMP
        |--------------------------------------------------------------------------
        */

        function clamp(
            value,
            min,
            max
        ) {

            return Math.min(
                Math.max(
                    value,
                    min
                ),
                max
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ELEMENTO DE ESCRITURA
        |--------------------------------------------------------------------------
        |
        | Ctrl+B no debe interferir cuando el usuario está escribiendo
        | dentro de un formulario.
        |
        */

        function isTypingTarget(target) {

            if (!(target instanceof Element)) {
                return false;
            }


            if (
                target.matches(
                    'input, textarea, select'
                )
            ) {
                return true;
            }


            if (
                target.isContentEditable
            ) {
                return true;
            }


            return Boolean(
                target.closest(
                    '[contenteditable="true"]'
                )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LEER ESTADO GUARDADO
        |--------------------------------------------------------------------------
        */

        function getStoredDesktopState() {

            try {

                return localStorage.getItem(
                    STORAGE_KEY
                ) === 'true';

            } catch (error) {

                return false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | GUARDAR ESTADO
        |--------------------------------------------------------------------------
        */

        function saveDesktopState(
            collapsed
        ) {

            try {

                localStorage.setItem(
                    STORAGE_KEY,
                    collapsed
                        ? 'true'
                        : 'false'
                );

            } catch (error) {

                /*
                | El sidebar puede continuar funcionando aunque
                | localStorage no esté disponible.
                */

            }

        }


        /*
        |--------------------------------------------------------------------------
        | LIMPIAR TAMAÑOS TEMPORALES
        |--------------------------------------------------------------------------
        |
        | Durante el drag utilizamos valores inline.
        |
        | Una vez decidido el estado final, CSS vuelve a tomar el control.
        |
        */

        function clearTemporarySizing() {

            const sidebar =
                getSidebar();

            const mainContent =
                getMainContent();


            if (sidebar) {

                sidebar.style.removeProperty(
                    'width'
                );

            }


            if (mainContent) {

                mainContent.style.removeProperty(
                    'margin-left'
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR BOTÓN
        |--------------------------------------------------------------------------
        */

        function syncCollapseButtonState(
            collapsed
        ) {

            const button =
                getCollapseButton();


            if (!button) {
                return;
            }


            button.setAttribute(
                'aria-label',
                collapsed
                    ? 'Abrir menú'
                    : 'Colapsar menú'
            );


            button.setAttribute(
                'title',
                collapsed
                    ? 'Abrir menú (Ctrl+B)'
                    : 'Colapsar menú (Ctrl+B)'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | APLICAR ESTADO DE ESCRITORIO
        |--------------------------------------------------------------------------
        */

        function applyDesktopSidebarState(
            collapsed,
            persist = false
        ) {

            if (isMobileViewport()) {
                return;
            }


            document.documentElement
                .classList
                .toggle(
                    'sidebar-collapsed',
                    collapsed
                );


            syncCollapseButtonState(
                collapsed
            );


            if (persist) {

                saveDesktopState(
                    collapsed
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ESTABLECER ESTADO FINAL
        |--------------------------------------------------------------------------
        */

        function setDesktopSidebarState(
            collapsed
        ) {

            if (isMobileViewport()) {
                return;
            }


            clearTemporarySizing();


            applyDesktopSidebarState(
                collapsed,
                true
            );

        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZAR MÓVIL
        |--------------------------------------------------------------------------
        */

        function normalizeMobileSidebar() {

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();


            if (!sidebar) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | El modo colapsado pertenece únicamente a escritorio
            |--------------------------------------------------------------------------
            */

            document.documentElement
                .classList
                .remove(
                    'sidebar-collapsed'
                );


            clearTemporarySizing();


            sidebar.classList.remove(
                'w-20'
            );

            sidebar.classList.add(
                'w-64'
            );


            /*
            |--------------------------------------------------------------------------
            | Drawer inicialmente cerrado
            |--------------------------------------------------------------------------
            */

            sidebar.classList.add(
                '-translate-x-full'
            );

            sidebar.classList.remove(
                'translate-x-0'
            );


            overlay
                ?.classList
                .add(
                    'hidden'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO DEL DRAWER MÓVIL
        |--------------------------------------------------------------------------
        */

        function applyMobileSidebarState(
            open
        ) {

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();


            if (!sidebar) {
                return;
            }


            if (open) {

                sidebar.classList.remove(
                    '-translate-x-full'
                );

                sidebar.classList.add(
                    'translate-x-0'
                );


                overlay
                    ?.classList
                    .remove(
                        'hidden'
                    );


                return;

            }


            sidebar.classList.add(
                '-translate-x-full'
            );

            sidebar.classList.remove(
                'translate-x-0'
            );


            overlay
                ?.classList
                .add(
                    'hidden'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | SINCRONIZAR ESTADO
        |--------------------------------------------------------------------------
        */

        function syncSidebarState() {

            if (isMobileViewport()) {

                normalizeMobileSidebar();

                applyMobileSidebarState(
                    false
                );


                return;

            }


            clearTemporarySizing();


            const collapsed =
                getStoredDesktopState();


            applyDesktopSidebarState(
                collapsed,
                false
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ABRIR SIDEBAR DE ESCRITORIO
        |--------------------------------------------------------------------------
        */

        function openDesktopSidebar() {

            if (isMobileViewport()) {
                return;
            }


            setDesktopSidebarState(
                false
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CERRAR SIDEBAR DE ESCRITORIO
        |--------------------------------------------------------------------------
        */

        function closeDesktopSidebar() {

            if (isMobileViewport()) {
                return;
            }


            setDesktopSidebarState(
                true
            );

        }


        /*
        |--------------------------------------------------------------------------
        | TOGGLE PÚBLICO
        |--------------------------------------------------------------------------
        |
        | Sigue siendo compatible con:
        |
        | onclick="toggleSidebar()"
        |
        */

        window.toggleSidebar =
            function () {

                /*
                |--------------------------------------------------------------------------
                | MÓVIL
                |--------------------------------------------------------------------------
                */

                if (isMobileViewport()) {

                    const sidebar =
                        getSidebar();


                    if (!sidebar) {
                        return;
                    }


                    const isOpen =
                        sidebar
                            .classList
                            .contains(
                                'translate-x-0'
                            );


                    applyMobileSidebarState(
                        !isOpen
                    );


                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | ESCRITORIO
                |--------------------------------------------------------------------------
                */

                const collapsed =
                    document
                        .documentElement
                        .classList
                        .contains(
                            'sidebar-collapsed'
                        );


                setDesktopSidebarState(
                    !collapsed
                );

            };


        /*
        |--------------------------------------------------------------------------
        | ENFOCAR BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        function focusSidebarSearch() {

            const input =
                getSearchInput();


            if (!input) {
                return;
            }


            input.focus();


            /*
            |--------------------------------------------------------------------------
            | Colocar cursor al final
            |--------------------------------------------------------------------------
            */

            const value =
                input.value;


            try {

                input.setSelectionRange(
                    value.length,
                    value.length
                );

            } catch (error) {

                /*
                | Algunos tipos de input no admiten selección manual.
                */

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ABRIR Y ENFOCAR BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        function openSidebarSearch() {

            /*
            |--------------------------------------------------------------------------
            | MÓVIL
            |--------------------------------------------------------------------------
            */

            if (isMobileViewport()) {

                applyMobileSidebarState(
                    true
                );


                window.setTimeout(
                    () => {

                        focusSidebarSearch();

                    },
                    SIDEBAR_TRANSITION_MS + 40
                );


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | ESCRITORIO
            |--------------------------------------------------------------------------
            */

            const collapsed =
                document
                    .documentElement
                    .classList
                    .contains(
                        'sidebar-collapsed'
                    );


            if (!collapsed) {

                focusSidebarSearch();

                return;

            }


            openDesktopSidebar();


            window.setTimeout(
                () => {

                    focusSidebarSearch();

                },
                SIDEBAR_TRANSITION_MS + 30
            );

        }


        /*
        |--------------------------------------------------------------------------
        | EXPONER BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        window.openSidebarSearch =
            openSidebarSearch;


        /*
        |--------------------------------------------------------------------------
        | INICIO DEL ARRASTRE
        |--------------------------------------------------------------------------
        */

        function startSidebarResize(
            event
        ) {

            if (isMobileViewport()) {
                return;
            }


            if (
                event.button !== 0
            ) {
                return;
            }


            const sidebar =
                getSidebar();

            const mainContent =
                getMainContent();


            if (
                !sidebar ||
                !mainContent
            ) {
                return;
            }


            event.preventDefault();


            resizeActive =
                true;

            resizePointerId =
                event.pointerId;


            resizeCurrentWidth =
                sidebar
                    .getBoundingClientRect()
                    .width;


            document.documentElement
                .classList
                .add(
                    'sidebar-resizing'
                );


            /*
            |--------------------------------------------------------------------------
            | Capturar puntero
            |--------------------------------------------------------------------------
            */

            try {

                event
                    .target
                    .setPointerCapture(
                        event.pointerId
                    );

            } catch (error) {

                /*
                | No es obligatorio para continuar.
                */

            }


            /*
            |--------------------------------------------------------------------------
            | Evitar selección accidental de texto
            |--------------------------------------------------------------------------
            */

            document.body.style.userSelect =
                'none';

            document.body.style.cursor =
                'col-resize';

        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR ARRASTRE
        |--------------------------------------------------------------------------
        */

        function updateSidebarResize(
            event
        ) {

            if (!resizeActive) {
                return;
            }


            if (
                event.pointerId
                !== resizePointerId
            ) {
                return;
            }


            const sidebar =
                getSidebar();

            const mainContent =
                getMainContent();


            if (
                !sidebar ||
                !mainContent
            ) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CALCULAR ANCHO
            |--------------------------------------------------------------------------
            */

            const width =
                clamp(
                    event.clientX,
                    SIDEBAR_CLOSED_WIDTH,
                    SIDEBAR_OPEN_WIDTH
                );


            resizeCurrentWidth =
                width;


            /*
            |--------------------------------------------------------------------------
            | MOVER SIDEBAR EN TIEMPO REAL
            |--------------------------------------------------------------------------
            */

            sidebar.style.setProperty(
                'width',
                `${width}px`,
                'important'
            );


            mainContent.style.setProperty(
                'margin-left',
                `${width}px`,
                'important'
            );


            /*
            |--------------------------------------------------------------------------
            | PREVISUALIZACIÓN DEL ESTADO
            |--------------------------------------------------------------------------
            |
            | Antes manteníamos "sidebar-collapsed" hasta soltar el mouse.
            |
            | Eso provocaba:
            |
            | sidebar ancho
            | +
            | contenido todavía colapsado
            |
            | Ahora cambiamos el estado visual DURANTE el drag.
            |
            */

            const previewCollapsed =
                width
                <= SIDEBAR_SNAP_THRESHOLD;


            document.documentElement
                .classList
                .toggle(
                    'sidebar-collapsed',
                    previewCollapsed
                );


            /*
            |--------------------------------------------------------------------------
            | SINCRONIZAR BOTÓN
            |--------------------------------------------------------------------------
            */

            syncCollapseButtonState(
                previewCollapsed
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FINALIZAR ARRASTRE
        |--------------------------------------------------------------------------
        */

        function finishSidebarResize(
            event = null
        ) {

            if (!resizeActive) {
                return;
            }


            if (
                event &&
                resizePointerId !== null &&
                event.pointerId !== resizePointerId
            ) {
                return;
            }


            resizeActive =
                false;

            resizePointerId =
                null;


            document.documentElement
                .classList
                .remove(
                    'sidebar-resizing'
                );


            document.body.style.removeProperty(
                'user-select'
            );

            document.body.style.removeProperty(
                'cursor'
            );


            /*
            |--------------------------------------------------------------------------
            | SNAP AUTOMÁTICO
            |--------------------------------------------------------------------------
            */

            const shouldCollapse =
                resizeCurrentWidth
                <= SIDEBAR_SNAP_THRESHOLD;


            /*
            |--------------------------------------------------------------------------
            | Primero definimos el estado final manteniendo el ancho temporal.
            |--------------------------------------------------------------------------
            */

            document.documentElement
                .classList
                .toggle(
                    'sidebar-collapsed',
                    shouldCollapse
                );


            saveDesktopState(
                shouldCollapse
            );


            syncCollapseButtonState(
                shouldCollapse
            );


            /*
            |--------------------------------------------------------------------------
            | En el siguiente frame retiramos el ancho temporal.
            |
            | CSS realizará el snap final hacia 80px o 256px.
            |--------------------------------------------------------------------------
            */

            window.requestAnimationFrame(
                () => {

                    clearTemporarySizing();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CANCELAR ARRASTRE
        |--------------------------------------------------------------------------
        */

        function cancelSidebarResize() {

            if (!resizeActive) {
                return;
            }


            resizeActive =
                false;

            resizePointerId =
                null;


            document.documentElement
                .classList
                .remove(
                    'sidebar-resizing'
                );


            document.body.style.removeProperty(
                'user-select'
            );

            document.body.style.removeProperty(
                'cursor'
            );


            clearTemporarySizing();


            syncSidebarState();

        }


        /*
        |--------------------------------------------------------------------------
        | LISTENERS GLOBALES
        |--------------------------------------------------------------------------
        |
        | Sólo se registran una vez.
        |
        | Esto es importante porque Livewire puede volver a procesar
        | scripts después de una navegación.
        |
        */

        if (
            !window.__trackItSidebarListenersInstalled
        ) {

            window.__trackItSidebarListenersInstalled =
                true;


            /*
            |--------------------------------------------------------------------------
            | CTRL + B
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                event => {

                    if (
                        event.defaultPrevented
                    ) {
                        return;
                    }


                    if (
                        !(event.ctrlKey || event.metaKey)
                    ) {
                        return;
                    }


                    if (
                        event.altKey ||
                        event.shiftKey
                    ) {
                        return;
                    }


                    if (
                        event.key.toLowerCase()
                        !== 'b'
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | No interferir mientras se escribe
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isTypingTarget(
                            event.target
                        )
                    ) {
                        return;
                    }


                    event.preventDefault();


                    window.toggleSidebar();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CTRL + K
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                event => {

                    if (
                        event.defaultPrevented
                    ) {
                        return;
                    }


                    if (
                        !(event.ctrlKey || event.metaKey)
                    ) {
                        return;
                    }


                    if (
                        event.altKey ||
                        event.shiftKey
                    ) {
                        return;
                    }


                    if (
                        event.key.toLowerCase()
                        !== 'k'
                    ) {
                        return;
                    }


                    event.preventDefault();


                    openSidebarSearch();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CLIC EN BUSCADOR COMPRIMIDO
            |--------------------------------------------------------------------------
            |
            | Como utilizamos EL MISMO buscador, no hay un segundo componente.
            |
            */

            document.addEventListener(
                'click',
                event => {

                    if (isMobileViewport()) {
                        return;
                    }


                    const search =
                        event.target.closest(
                            '#sidebarQuickSearch'
                        );


                    if (!search) {
                        return;
                    }


                    const collapsed =
                        document
                            .documentElement
                            .classList
                            .contains(
                                'sidebar-collapsed'
                            );


                    if (!collapsed) {
                        return;
                    }


                    event.preventDefault();


                    openSidebarSearch();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CERRAR DRAWER MÓVIL AL NAVEGAR
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'click',
                event => {

                    if (
                        !isMobileViewport()
                    ) {
                        return;
                    }


                    const link =
                        event.target.closest(
                            '#sidebarNav a'
                        );


                    if (!link) {
                        return;
                    }


                    applyMobileSidebarState(
                        false
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | INICIAR RESIZE
            |--------------------------------------------------------------------------
            |
            | Delegación para que funcione también después de wire:navigate.hover.
            |
            */

            document.addEventListener(
                'pointerdown',
                event => {

                    const handle =
                        event.target.closest(
                            '#sidebarResizeHandle'
                        );


                    if (!handle) {
                        return;
                    }


                    startSidebarResize(
                        event
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | MOVER RESIZE
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'pointermove',
                event => {

                    updateSidebarResize(
                        event
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | FINALIZAR RESIZE
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'pointerup',
                event => {

                    finishSidebarResize(
                        event
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CANCELAR RESIZE
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'pointercancel',
                () => {

                    cancelSidebarResize();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | PERDER FOCO DE LA VENTANA
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'blur',
                () => {

                    if (resizeActive) {

                        finishSidebarResize();

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CAMBIO MÓVIL / ESCRITORIO
            |--------------------------------------------------------------------------
            */

            let wasMobile =
                isMobileViewport();


            window.addEventListener(
                'resize',
                () => {

                    const nowMobile =
                        isMobileViewport();


                    if (
                        nowMobile === wasMobile
                    ) {
                        return;
                    }


                    wasMobile =
                        nowMobile;


                    cancelSidebarResize();


                    syncSidebarState();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | LIVEWIRE - ANTES DEL SWAP
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'livewire:navigating',
                event => {

                    cancelSidebarResize();


                    if (
                        typeof event.detail?.onSwap
                        !== 'function'
                    ) {
                        return;
                    }


                    event.detail.onSwap(
                        () => {

                            syncSidebarState();

                        }
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | LIVEWIRE - NAVEGACIÓN TERMINADA
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'livewire:navigate.hoverd',
                () => {

                    syncSidebarState();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | EXPONER SINCRONIZADOR
        |--------------------------------------------------------------------------
        */

        window.syncTrackItSidebarState =
            syncSidebarState;


        /*
        |--------------------------------------------------------------------------
        | PRIMERA CARGA
        |--------------------------------------------------------------------------
        */

        if (
            document.readyState === 'loading'
        ) {

            document.addEventListener(
                'DOMContentLoaded',
                () => {

                    syncSidebarState();

                },
                {
                    once: true,
                }
            );

        } else {

            syncSidebarState();

        }

    })();

    /*
    |--------------------------------------------------------------------------
    | PREFETCH DE NAVEGACIÓN EN MÓVIL
    |--------------------------------------------------------------------------
    |
    | En escritorio wire:navigate.hover empieza a precargar cuando
    | el cursor permanece sobre el enlace.
    |
    | En móvil no existe hover, así que cuando el usuario toca un enlace
    | le damos focus inmediatamente. Livewire utiliza también el focus
    | para iniciar el prefetch de wire:navigate.hover.
    |
    */

    document.addEventListener(
        'pointerdown',
        (event) => {

            /*
            * Solamente nos interesa touch o lápiz.
            * El mouse ya utiliza hover normalmente.
            */
            if (
                event.pointerType !== 'touch'
                &&
                event.pointerType !== 'pen'
            ) {
                return;
            }


            const link =
                event.target.closest(
                    'a[href]'
                );


            if (! link) {
                return;
            }


            /*
            * Solamente enlaces que ya decidimos
            * que son navegación Livewire.
            */
            if (
                ! link.hasAttribute(
                    'wire:navigate.hover'
                )
            ) {
                return;
            }


            /*
            * Dar focus dispara el prefetch de Livewire.
            * preventScroll evita movimientos inesperados
            * de la pantalla.
            */
            try {

                link.focus({
                    preventScroll: true,
                });

            } catch (error) {

                link.focus();
            }
        },
        {
            capture: true,
            passive: true,
        }
    );
</script>