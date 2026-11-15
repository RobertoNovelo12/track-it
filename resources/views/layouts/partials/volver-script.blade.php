{{-- ============================================================
    «VOLVER»: recuerda la pantalla anterior dentro del sistema
    ------------------------------------------------------------
    Se guarda en sessionStorage (por pestaña). trackitVolver(destino):
    1. Pantalla anterior, si es del sistema y no es un formulario
       (crear/editar/baja): así no se regresa a un formulario ya
       guardado.
    2. Si no, el nivel anterior de la ruta de navegación (destino).
    Al usar «Volver» la pantalla de la que se sale no queda como
    «anterior», para no ir y venir entre dos pantallas.
============================================================ --}}
<script>
    (() => {
        if (window.__trackitVolverListo) {
            return;
        }

        window.__trackitVolverListo = true;

        const leer = (clave) => {
            try { return sessionStorage.getItem(clave); } catch (e) { return null; }
        };

        const escribir = (clave, valor) => {
            try {
                valor === null ? sessionStorage.removeItem(clave) : sessionStorage.setItem(clave, valor);
            } catch (e) {}
        };

        const sinHash = (url) => String(url ?? '').split('#')[0];

        const esFormulario = (url) => {
            try {
                return /\/(crear|editar|nuevo|nueva|baja|create|edit)(\/|$)/i.test(new URL(url, location.origin).pathname);
            } catch (e) {
                return true;
            }
        };

        /*
         * Nombre y desplazamiento de cada pantalla visitada (por pestaña):
         * «← Inventario» y, al regresar, la lista queda donde estaba.
         */
        const leerMapa = (clave) => {
            try { return JSON.parse(leer(clave) || '{}') || {}; } catch (e) { return {}; }
        };

        const guardarEnMapa = (clave, url, valor) => {
            const mapa = leerMapa(clave);
            mapa[url] = valor;
            // Solo las últimas 30 pantallas.
            const llaves = Object.keys(mapa);
            llaves.slice(0, Math.max(0, llaves.length - 30)).forEach((k) => delete mapa[k]);
            escribir(clave, JSON.stringify(mapa));
        };

        const tituloDePagina = () => String(document.title || '').split(' | ')[0].trim();

        const restaurarDesplazamiento = (url) => {
            const y = Number(leerMapa('trackit.volver.scroll')[url] || 0);

            if (y <= 0) return;

            // Las secciones diferidas llegan después: se espera a que haya altura (máx. ~3 s).
            let intentos = 0;
            let cancelado = false;
            const cancelar = () => { cancelado = true; };

            ['wheel', 'touchstart', 'keydown'].forEach((e) => window.addEventListener(e, cancelar, { once: true, passive: true }));

            const intentar = () => {
                if (cancelado) return;

                if (document.documentElement.scrollHeight - window.innerHeight >= y || intentos >= 30) {
                    window.scrollTo({ top: y, behavior: 'instant' });
                    return;
                }

                intentos++;
                setTimeout(intentar, 100);
            };

            requestAnimationFrame(intentar);
        };

        /*
         * Transición al cambiar de pantalla: al abrir un formulario (crear /
         * editar) el contenido entra desde la derecha y al regresar desde la
         * izquierda, para que se sienta parte de la misma sección.
         */
        const animarEntrada = (sentido) => {
            const contenido = document.getElementById('contenido-principal');

            if (! contenido || ! sentido || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            contenido.classList.remove('tk-entra-adelante', 'tk-entra-atras');
            void contenido.offsetWidth;
            contenido.classList.add('tk-entra-' + sentido);
            contenido.addEventListener('animationend', () => contenido.classList.remove('tk-entra-' + sentido), { once: true });
        };

        const registrar = (navegando = false) => {
            const actual = sinHash(location.href);
            const ultima = leer('trackit.volver.actual');

            guardarEnMapa('trackit.volver.titulos', actual, tituloDePagina());

            if (navegando) {
                animarEntrada(
                    leer('trackit.volver.regresando') === '1'
                        ? 'atras'
                        : (esFormulario(actual) ? 'adelante' : (ultima && esFormulario(ultima) ? 'atras' : null))
                );
            }

            if (leer('trackit.volver.regresando') === '1') {
                // Llegamos con «Volver»: no guardar de dónde venimos y regresar al mismo punto.
                escribir('trackit.volver.regresando', null);
                escribir('trackit.volver.previa', null);
                restaurarDesplazamiento(actual);
            } else if (ultima && ultima !== actual) {
                escribir('trackit.volver.previa', ultima);
            }

            escribir('trackit.volver.actual', actual);
        };

        // Nombre de la pantalla anterior (para «← Inventario»).
        window.trackitTituloPrevio = () => {
            const previa = window.trackitPaginaPrevia?.();

            return previa ? (leerMapa('trackit.volver.titulos')[previa] || null) : null;
        };

        window.trackitPaginaPrevia = () => {
            const previa = leer('trackit.volver.previa');

            if (! previa || previa === sinHash(location.href) || esFormulario(previa)) {
                return null;
            }

            try {
                return new URL(previa).origin === location.origin ? previa : null;
            } catch (e) {
                return null;
            }
        };

        window.trackitVolver = (destino = null) => {
            const url = window.trackitPaginaPrevia() || destino;

            if (! url) {
                history.length > 1 ? history.back() : (location.href = '/');

                return;
            }

            escribir('trackit.volver.regresando', '1');
            window.Livewire?.navigate ? window.Livewire.navigate(url) : (location.href = url);
        };

        /*
         * Justo antes de salir se guarda la dirección tal como está en ese
         * momento: las pestañas de Administración cambian ?section= sin
         * navegar (replaceState), y sin esto «Volver» regresaba a la
         * pestaña con la que se entró (Equipos).
         */
        const recordarActual = () => {
            const actual = sinHash(location.href);
            escribir('trackit.volver.actual', actual);
            guardarEnMapa('trackit.volver.scroll', actual, Math.round(window.scrollY));
            guardarEnMapa('trackit.volver.titulos', actual, tituloDePagina());
        };

        registrar(false);
        document.addEventListener('livewire:navigated', () => registrar(true));
        document.addEventListener('livewire:navigate', recordarActual);
        window.addEventListener('pagehide', recordarActual);
    })();
</script>
