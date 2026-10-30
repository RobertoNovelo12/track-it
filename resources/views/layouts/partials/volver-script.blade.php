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

        const registrar = () => {
            const actual = sinHash(location.href);
            const ultima = leer('trackit.volver.actual');

            if (leer('trackit.volver.regresando') === '1') {
                // Llegamos con «Volver»: no guardar de dónde venimos.
                escribir('trackit.volver.regresando', null);
                escribir('trackit.volver.previa', null);
            } else if (ultima && ultima !== actual) {
                escribir('trackit.volver.previa', ultima);
            }

            escribir('trackit.volver.actual', actual);
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
        const recordarActual = () => escribir('trackit.volver.actual', sinHash(location.href));

        registrar();
        document.addEventListener('livewire:navigated', registrar);
        document.addEventListener('livewire:navigate', recordarActual);
        window.addEventListener('pagehide', recordarActual);
    })();
</script>
