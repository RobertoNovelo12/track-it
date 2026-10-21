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

        const SIDEBAR_SNAP_THRESHOLD =
            150;

        const SIDEBAR_TRANSITION_MS =
            180;

        const MOBILE_DRAWER_TRANSITION_MS =
            300;

        const MOBILE_OVERLAY_TRANSITION_MS =
            220;

        const MOBILE_PREFETCH_TTL =
            60 * 1000;


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


        function getMobileHeader() {

            return document.getElementById(
                'mobileAppHeader'
            );
        }


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


        function isTypingTarget(
            target
        ) {

            if (
                !(target instanceof Element)
            ) {
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
        | ESTADO DE ESCRITORIO
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
                 * El sidebar puede seguir funcionando
                 * aunque localStorage no esté disponible.
                 */
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LIMPIAR TAMAÑOS TEMPORALES
        |--------------------------------------------------------------------------
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
        | BOTÓN DE COLAPSAR
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
        | LIMPIAR ESTILOS MÓVILES AL VOLVER A ESCRITORIO
        |--------------------------------------------------------------------------
        */

        function clearMobileDrawerStyles() {

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();

            const header =
                getMobileHeader();


            if (sidebar) {

                sidebar.style.removeProperty(
                    'transform'
                );

                sidebar.style.removeProperty(
                    'translate'
                );

                sidebar.style.removeProperty(
                    'transition'
                );

                sidebar.style.removeProperty(
                    'will-change'
                );

                delete sidebar.dataset
                    .mobileOpen;
            }


            if (overlay) {

                overlay.classList.add(
                    'hidden'
                );

                overlay.style.removeProperty(
                    'opacity'
                );

                overlay.style.removeProperty(
                    'transition'
                );

                overlay.style.removeProperty(
                    'will-change'
                );

                overlay.style.removeProperty(
                    'pointer-events'
                );
            }


            if (header) {

                header.style.removeProperty(
                    'filter'
                );

                header.style.removeProperty(
                    'transition'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZAR SIDEBAR MÓVIL
        |--------------------------------------------------------------------------
        */

        function normalizeMobileSidebar() {

            const sidebar =
                getSidebar();


            if (!sidebar) {
                return;
            }


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
             * IMPORTANTE:
             *
             * Tailwind 4 puede utilizar la propiedad CSS
             * individual "translate".
             *
             * Nosotros anulamos ese translate en móvil y
             * controlamos TODO el desplazamiento mediante
             * transform.
             *
             * Así la animación es totalmente predecible.
             */
            sidebar.style.setProperty(
                'translate',
                '0 0'
            );


            sidebar.style.transition =
                `transform ${MOBILE_DRAWER_TRANSITION_MS}ms cubic-bezier(0.22, 1, 0.36, 1)`;


            sidebar.style.willChange =
                'transform';
        }


        /*
        |--------------------------------------------------------------------------
        | PREPARAR OVERLAY
        |--------------------------------------------------------------------------
        */

        function prepareMobileOverlay() {

            const overlay =
                getOverlay();


            if (!overlay) {
                return;
            }


            overlay.style.transition =
                `opacity ${MOBILE_OVERLAY_TRANSITION_MS}ms ease-out`;


            overlay.style.willChange =
                'opacity';
        }


        /*
        |--------------------------------------------------------------------------
        | OSCURECER HEADER MÓVIL
        |--------------------------------------------------------------------------
        |
        | No colocamos el overlay por encima del header porque queremos
        | que la campana y el perfil sigan siendo pulsables.
        |
        */

        function setMobileHeaderDimmed(
            dimmed
        ) {

            const header =
                getMobileHeader();


            if (!header) {
                return;
            }


            if (!isMobileViewport()) {

                header.style.removeProperty(
                    'filter'
                );

                header.style.removeProperty(
                    'transition'
                );

                return;
            }


            header.style.transition =
                'transform 200ms ease-out, filter 180ms ease-out';


            if (dimmed) {

                header.style.filter =
                    'brightness(0.55)';

            } else {

                header.style.removeProperty(
                    'filter'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | BLOQUEAR SCROLL EN MÓVIL
        |--------------------------------------------------------------------------
        */

        function lockMobilePageScroll() {

            if (!isMobileViewport()) {
                return;
            }


            if (
                window.__trackItSidebarScrollLock
            ) {
                return;
            }


            const body =
                document.body;

            const html =
                document.documentElement;


            if (
                !body ||
                !html
            ) {
                return;
            }


            const scrollY =
                window.scrollY;


            window.__trackItSidebarScrollLock = {

                scrollY,

                bodyPosition:
                    body.style.position,

                bodyTop:
                    body.style.top,

                bodyLeft:
                    body.style.left,

                bodyRight:
                    body.style.right,

                bodyWidth:
                    body.style.width,

                bodyOverflow:
                    body.style.overflow,

                htmlOverflow:
                    html.style.overflow,
            };


            body.style.position =
                'fixed';

            body.style.top =
                `-${scrollY}px`;

            body.style.left =
                '0';

            body.style.right =
                '0';

            body.style.width =
                '100%';

            body.style.overflow =
                'hidden';


            html.style.overflow =
                'hidden';
        }


        /*
        |--------------------------------------------------------------------------
        | DESBLOQUEAR SCROLL
        |--------------------------------------------------------------------------
        */

        function unlockMobilePageScroll() {

            const lock =
                window.__trackItSidebarScrollLock;


            if (!lock) {
                return;
            }


            const body =
                document.body;

            const html =
                document.documentElement;


            if (
                !body ||
                !html
            ) {

                window.__trackItSidebarScrollLock =
                    null;

                return;
            }


            body.style.position =
                lock.bodyPosition;

            body.style.top =
                lock.bodyTop;

            body.style.left =
                lock.bodyLeft;

            body.style.right =
                lock.bodyRight;

            body.style.width =
                lock.bodyWidth;

            body.style.overflow =
                lock.bodyOverflow;


            html.style.overflow =
                lock.htmlOverflow;


            const scrollY =
                lock.scrollY;


            window.__trackItSidebarScrollLock =
                null;


            window.scrollTo(
                0,
                scrollY
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CANCELAR TIMER DE CIERRE
        |--------------------------------------------------------------------------
        */

        function cancelMobileCloseTimer() {

            if (
                window.__trackItSidebarCloseTimer
            ) {

                window.clearTimeout(
                    window.__trackItSidebarCloseTimer
                );


                window.__trackItSidebarCloseTimer =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO DEL DRAWER MÓVIL
        |--------------------------------------------------------------------------
        */

        function applyMobileSidebarState(
            open,
            immediate = false
        ) {

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();


            if (!sidebar) {
                return;
            }


            normalizeMobileSidebar();
            prepareMobileOverlay();
            cancelMobileCloseTimer();


            /*
            |--------------------------------------------------------------------------
            | ABRIR
            |--------------------------------------------------------------------------
            */

            if (open) {

                window.__trackItDrawerClosing =
                    null;

                window.__trackItDrawerContinuingUntil =
                    0;


                /*
                 * Mantener el header visible antes
                 * de abrir el drawer.
                 */
                window
                    .showTrackItMobileHeader
                    ?.();


                /*
                 * Conservamos estas clases como indicadores
                 * de estado para app.blade.php.
                 *
                 * El movimiento real NO depende de ellas.
                 */
                sidebar.classList.remove(
                    '-translate-x-full'
                );

                sidebar.classList.add(
                    'translate-x-0'
                );


                sidebar.dataset.mobileOpen =
                    '1';


                /*
                 * Empezamos desde fuera de pantalla.
                 */
                sidebar.style.transition =
                    'none';

                sidebar.style.transform =
                    'translate3d(-100%, 0, 0)';


                if (overlay) {

                    overlay.classList.remove(
                        'hidden'
                    );

                    overlay.style.transition =
                        'none';

                    overlay.style.opacity =
                        '0';

                    overlay.style.pointerEvents =
                        'auto';
                }


                /*
                 * Forzamos al navegador a registrar el
                 * estado inicial antes de animar.
                 *
                 * ESTA PARTE ES LA QUE GARANTIZA
                 * LA ANIMACIÓN DE APERTURA.
                 */
                void sidebar.offsetWidth;

                if (overlay) {
                    void overlay.offsetWidth;
                }


                lockMobilePageScroll();
                setMobileHeaderDimmed(true);


                /*
                 * Dos frames garantizan que el navegador
                 * pinte primero -100% y después 0%.
                 */
                window.requestAnimationFrame(
                    () => {

                        window.requestAnimationFrame(
                            () => {

                                if (
                                    sidebar.dataset
                                        .mobileOpen
                                    !== '1'
                                ) {
                                    return;
                                }


                                sidebar.style.transition =
                                    `transform ${MOBILE_DRAWER_TRANSITION_MS}ms cubic-bezier(0.22, 1, 0.36, 1)`;


                                sidebar.style.transform =
                                    'translate3d(0, 0, 0)';


                                if (overlay) {

                                    overlay.style.transition =
                                        `opacity ${MOBILE_OVERLAY_TRANSITION_MS}ms ease-out`;


                                    overlay.style.opacity =
                                        '1';
                                }

                            }
                        );

                    }
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CERRAR INMEDIATAMENTE
            |--------------------------------------------------------------------------
            |
            | Se usa durante:
            |
            | - primera carga
            | - cambio de breakpoint
            | - navegación Livewire
            |
            */

            if (immediate) {

                window.__trackItDrawerClosing =
                    null;

                window.__trackItDrawerContinuingUntil =
                    0;


                sidebar.dataset.mobileOpen =
                    '0';


                sidebar.classList.add(
                    '-translate-x-full'
                );

                sidebar.classList.remove(
                    'translate-x-0'
                );


                sidebar.style.transition =
                    'none';


                sidebar.style.transform =
                    'translate3d(-100%, 0, 0)';


                if (overlay) {

                    overlay.style.transition =
                        'none';

                    overlay.style.opacity =
                        '0';

                    overlay.style.pointerEvents =
                        'none';

                    overlay.classList.add(
                        'hidden'
                    );
                }


                setMobileHeaderDimmed(
                    false
                );


                unlockMobilePageScroll();


                /*
                 * Volvemos a dejar las transiciones listas
                 * para la siguiente apertura.
                 */
                window.requestAnimationFrame(
                    () => {

                        if (
                            !isMobileViewport()
                        ) {
                            return;
                        }


                        sidebar.style.transition =
                            `transform ${MOBILE_DRAWER_TRANSITION_MS}ms cubic-bezier(0.22, 1, 0.36, 1)`;


                        if (overlay) {

                            overlay.style.transition =
                                `opacity ${MOBILE_OVERLAY_TRANSITION_MS}ms ease-out`;
                        }

                    }
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CERRAR CON ANIMACIÓN
            |--------------------------------------------------------------------------
            */

            /*
             * Se registra el inicio para que, si la página nueva llega
             * antes de terminar (navegación rápida o precargada), el
             * drawer de la página nueva continúe desde el mismo punto.
             */
            window.__trackItDrawerClosing = {
                start:
                    performance.now(),
                duration:
                    MOBILE_DRAWER_TRANSITION_MS,
            };


            sidebar.dataset.mobileOpen =
                '0';


            sidebar.classList.add(
                '-translate-x-full'
            );

            sidebar.classList.remove(
                'translate-x-0'
            );


            sidebar.style.transition =
                `transform ${MOBILE_DRAWER_TRANSITION_MS}ms cubic-bezier(0.4, 0, 0.2, 1)`;


            sidebar.style.transform =
                'translate3d(-100%, 0, 0)';


            if (overlay) {

                overlay.style.transition =
                    `opacity ${MOBILE_OVERLAY_TRANSITION_MS}ms ease-in`;


                overlay.style.opacity =
                    '0';


                overlay.style.pointerEvents =
                    'none';
            }


            setMobileHeaderDimmed(
                false
            );


            /*
             * Dejamos bloqueado el scroll mientras
             * el drawer termina de salir.
             */
            window.__trackItSidebarCloseTimer =
                window.setTimeout(
                    () => {

                        if (
                            sidebar.dataset
                                .mobileOpen
                            === '1'
                        ) {
                            return;
                        }


                        overlay
                            ?.classList
                            .add(
                                'hidden'
                            );


                        unlockMobilePageScroll();


                        window.__trackItSidebarCloseTimer =
                            null;

                    },
                    MOBILE_DRAWER_TRANSITION_MS
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SINCRONIZAR ATAJO CTRL K
        |--------------------------------------------------------------------------
        */

        function syncSearchShortcutHint() {

            const search =
                document.getElementById(
                    'sidebarQuickSearch'
                );

            const input =
                getSearchInput();


            if (!search) {
                return;
            }


            const shortcut =
                Array.from(
                    search.querySelectorAll(
                        'span'
                    )
                )
                    .find(
                        element =>
                            element.textContent
                                ?.trim()
                            === 'Ctrl K'
                    );


            if (isMobileViewport()) {

                if (shortcut) {

                    shortcut.style.display =
                        'none';
                }


                if (input) {

                    input.style.paddingRight =
                        '1rem';
                }


                return;
            }


            if (shortcut) {

                shortcut.style.removeProperty(
                    'display'
                );
            }


            if (input) {

                input.style.removeProperty(
                    'padding-right'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TRASPASO DEL CIERRE AL NAVEGAR (MÓVIL)
        |--------------------------------------------------------------------------
        |
        | Livewire reemplaza el <body> al navegar y el drawer nuevo llega
        | cerrado. Si la página carga antes de que termine la animación de
        | cierre, ésta se cortaba de golpe. Aquí se toma la posición exacta
        | del drawer y del overlay justo antes del cambio y el drawer de la
        | página nueva continúa el cierre desde ahí, sin retrasar la carga.
        |
        */

        function captureClosingDrawer() {

            const closing =
                window.__trackItDrawerClosing;

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();


            if (
                !closing
                || !sidebar
            ) {
                return null;
            }


            const elapsed =
                performance.now()
                - closing.start;

            const remaining =
                closing.duration
                - elapsed;


            if (remaining <= 16) {
                return null;
            }


            const width =
                sidebar.offsetWidth
                || SIDEBAR_OPEN_WIDTH;

            let offsetX =
                -width;


            try {

                const matrix =
                    new DOMMatrixReadOnly(
                        getComputedStyle(sidebar)
                            .transform
                    );

                offsetX =
                    matrix.m41;

            } catch (error) {
                // Sin transform legible: se cierra desde fuera de pantalla.
            }


            const opacity =
                overlay
                    ? Number(
                        getComputedStyle(overlay)
                            .opacity
                    ) || 0
                    : 0;


            return {
                ratio:
                    Math.min(
                        Math.max(
                            offsetX / width,
                            -1
                        ),
                        0
                    ),
                opacity,
                remaining:
                    Math.max(
                        remaining,
                        140
                    ),
            };
        }


        function continueDrawerClose(
            handoff
        ) {

            const sidebar =
                getSidebar();

            const overlay =
                getOverlay();


            if (!sidebar) {
                return;
            }


            normalizeMobileSidebar();
            prepareMobileOverlay();
            cancelMobileCloseTimer();


            sidebar.dataset.mobileOpen =
                '0';

            sidebar.classList.add(
                '-translate-x-full'
            );

            sidebar.classList.remove(
                'translate-x-0'
            );


            /*
             * Punto de partida: donde iba el drawer anterior.
             */
            sidebar.style.transition =
                'none';

            sidebar.style.transform =
                `translate3d(${(handoff.ratio * 100).toFixed(2)}%, 0, 0)`;


            if (overlay) {

                overlay.classList.remove(
                    'hidden'
                );

                overlay.style.transition =
                    'none';

                overlay.style.opacity =
                    String(handoff.opacity);

                overlay.style.pointerEvents =
                    'none';
            }


            void sidebar.offsetWidth;


            window.__trackItDrawerContinuingUntil =
                performance.now()
                + handoff.remaining;


            sidebar.style.transition =
                `transform ${Math.round(handoff.remaining)}ms cubic-bezier(0.4, 0, 0.2, 1)`;

            sidebar.style.transform =
                'translate3d(-100%, 0, 0)';


            if (overlay) {

                overlay.style.transition =
                    `opacity ${Math.round(handoff.remaining)}ms ease-in`;

                overlay.style.opacity =
                    '0';
            }


            window.__trackItSidebarCloseTimer =
                window.setTimeout(
                    () => {

                        window.__trackItSidebarCloseTimer =
                            null;

                        window.__trackItDrawerContinuingUntil =
                            0;


                        if (
                            sidebar.dataset
                                .mobileOpen
                            === '1'
                        ) {
                            return;
                        }


                        overlay
                            ?.classList
                            .add(
                                'hidden'
                            );


                        /*
                         * Transición normal lista para la
                         * siguiente apertura.
                         */
                        normalizeMobileSidebar();
                        prepareMobileOverlay();

                    },
                    handoff.remaining
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SINCRONIZAR SIDEBAR
        |--------------------------------------------------------------------------
        */

        function syncSidebarState() {

            syncSearchShortcutHint();


            if (isMobileViewport()) {

                /*
                 * Página nueva con un cierre en curso:
                 * continuar la animación en lugar de cortarla.
                 */
                const handoff =
                    window.__trackItDrawerHandoff;

                if (handoff) {

                    window.__trackItDrawerHandoff =
                        null;

                    continueDrawerClose(
                        handoff
                    );

                    return;
                }


                /*
                 * livewire:navigated llega después del swap:
                 * no interrumpir el cierre que sigue en curso.
                 */
                if (
                    (window.__trackItDrawerContinuingUntil || 0)
                    > performance.now()
                ) {
                    return;
                }


                normalizeMobileSidebar();


                applyMobileSidebarState(
                    false,
                    true
                );


                return;
            }


            cancelMobileCloseTimer();

            unlockMobilePageScroll();

            clearMobileDrawerStyles();

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
        | ESCRITORIO
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
        | PREFETCH DEL MENÚ EN MÓVIL
        |--------------------------------------------------------------------------
        */

        function prefetchMobileSidebarLinks() {

            if (!isMobileViewport()) {
                return;
            }


            window.__trackItMobilePrefetchCache ??=
                new Map();


            const prefetchCache =
                window.__trackItMobilePrefetchCache;


            const sidebarNav =
                document.getElementById(
                    'sidebarNav'
                );


            if (!sidebarNav) {
                return;
            }


            const now =
                Date.now();


            const currentUrl =
                new URL(
                    window.location.href
                );


            const links =
                Array.from(
                    sidebarNav.querySelectorAll(
                        'a[href][wire\\:navigate\\.hover]'
                    )
                )
                    .filter(
                        link => {

                            const href =
                                link.getAttribute(
                                    'href'
                                );


                            if (
                                !href ||
                                href === '#'
                            ) {
                                return false;
                            }


                            const destination =
                                new URL(
                                    href,
                                    window.location.origin
                                );


                            if (
                                destination.origin
                                !==
                                window.location.origin
                            ) {
                                return false;
                            }


                            if (
                                destination.pathname
                                    === currentUrl.pathname
                                &&
                                destination.search
                                    === currentUrl.search
                            ) {
                                return false;
                            }


                            const key =
                                destination.pathname
                                + destination.search;


                            const lastPrefetch =
                                prefetchCache.get(
                                    key
                                );


                            if (
                                lastPrefetch
                                &&
                                (
                                    now
                                    - lastPrefetch
                                )
                                < MOBILE_PREFETCH_TTL
                            ) {
                                return false;
                            }


                            prefetchCache.set(
                                key,
                                now
                            );


                            return true;
                        }
                    );


            links.forEach(
                (
                    link,
                    index
                ) => {

                    window.setTimeout(
                        () => {

                            if (
                                !document.contains(
                                    link
                                )
                            ) {
                                return;
                            }


                            link.dispatchEvent(
                                new MouseEvent(
                                    'mouseenter',
                                    {
                                        bubbles: true,
                                        cancelable: false,
                                        view: window,
                                    }
                                )
                            );

                        },
                        index * 90
                    );
                }
            );


            /*
             * Limpiar entradas vencidas.
             */
            for (
                const [
                    key,
                    timestamp
                ]
                of prefetchCache.entries()
            ) {

                if (
                    now - timestamp
                    > MOBILE_PREFETCH_TTL
                ) {

                    prefetchCache.delete(
                        key
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TOGGLE PÚBLICO
        |--------------------------------------------------------------------------
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
                        sidebar.dataset
                            .mobileOpen
                        === '1'
                        ||
                        sidebar.classList
                            .contains(
                                'translate-x-0'
                            );


                    const willOpen =
                        !isOpen;


                    if (willOpen) {

                        applyMobileSidebarState(
                            true
                        );


                        prefetchMobileSidebarLinks();


                    } else {

                        applyMobileSidebarState(
                            false
                        );
                    }


                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | ESCRITORIO
                |--------------------------------------------------------------------------
                */

                const collapsed =
                    document.documentElement
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


            const value =
                input.value;


            try {

                input.setSelectionRange(
                    value.length,
                    value.length
                );

            } catch (error) {

                /*
                 * Algunos inputs no admiten
                 * selección manual.
                 */
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ABRIR BÚSQUEDA
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
                    MOBILE_DRAWER_TRANSITION_MS + 40
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ESCRITORIO
            |--------------------------------------------------------------------------
            */

            const collapsed =
                document.documentElement
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


        window.openSidebarSearch =
            openSidebarSearch;


        /*
        |--------------------------------------------------------------------------
        | INICIAR RESIZE
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


            try {

                event.target
                    .setPointerCapture(
                        event.pointerId
                    );

            } catch (error) {

                /*
                 * No es obligatorio.
                 */
            }


            document.body.style.userSelect =
                'none';


            document.body.style.cursor =
                'col-resize';
        }


        /*
        |--------------------------------------------------------------------------
        | MOVER RESIZE
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


            const width =
                clamp(
                    event.clientX,
                    SIDEBAR_CLOSED_WIDTH,
                    SIDEBAR_OPEN_WIDTH
                );


            resizeCurrentWidth =
                width;


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


            const previewCollapsed =
                width
                <= SIDEBAR_SNAP_THRESHOLD;


            document.documentElement
                .classList
                .toggle(
                    'sidebar-collapsed',
                    previewCollapsed
                );


            syncCollapseButtonState(
                previewCollapsed
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FINALIZAR RESIZE
        |--------------------------------------------------------------------------
        */

        function finishSidebarResize(
            event = null
        ) {

            if (!resizeActive) {
                return;
            }


            if (
                event
                &&
                resizePointerId !== null
                &&
                event.pointerId
                    !== resizePointerId
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


            const shouldCollapse =
                resizeCurrentWidth
                <= SIDEBAR_SNAP_THRESHOLD;


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


            window.requestAnimationFrame(
                () => {

                    clearTemporarySizing();

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CANCELAR RESIZE
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
        }


        /*
        |--------------------------------------------------------------------------
        | LISTENERS GLOBALES
        |--------------------------------------------------------------------------
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
            |
            | Sólo escritorio.
            |
            */

            document.addEventListener(
                'keydown',
                event => {

                    if (
                        isMobileViewport()
                    ) {
                        return;
                    }


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

                    // El buscador ahora está en la barra superior.
                    if (window.focusTopSearch) {
                        return;
                    }

                    event.preventDefault();
                    openSidebarSearch();
                }
            );


            /*
            |--------------------------------------------------------------------------
            | BUSCADOR DEL SIDEBAR COLAPSADO
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'click',
                event => {

                    if (
                        isMobileViewport()
                    ) {
                        return;
                    }


                    if (
                        !(event.target instanceof Element)
                    ) {
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
                        document.documentElement
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


                    if (
                        !(event.target instanceof Element)
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
            */

            document.addEventListener(
                'pointerdown',
                event => {

                    if (
                        !(event.target instanceof Element)
                    ) {
                        return;
                    }


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
            | PERDER FOCO
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
                        nowMobile
                        === wasMobile
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


                    /*
                     * Aseguramos que el body jamás
                     * llegue bloqueado a otra página.
                     */
                    if (
                        isMobileViewport()
                    ) {

                        const handoff =
                            captureClosingDrawer();


                        if (handoff) {

                            /*
                             * El drawer anterior sigue cerrándose
                             * hasta el swap; el nuevo continúa
                             * desde la misma posición.
                             */
                            window.__trackItDrawerHandoff =
                                handoff;

                            window.__trackItDrawerClosing =
                                null;

                            /*
                             * El temporizador de cierre sigue activo: si la
                             * navegación se cancela, oculta el overlay igual.
                             * Si la página nueva llega, continueDrawerClose lo
                             * reemplaza.
                             */

                            setMobileHeaderDimmed(
                                false
                            );

                            unlockMobilePageScroll();

                        } else {

                            window.__trackItDrawerHandoff =
                                null;

                            applyMobileSidebarState(
                                false,
                                true
                            );
                        }
                    }


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
                'livewire:navigated',
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
</script>