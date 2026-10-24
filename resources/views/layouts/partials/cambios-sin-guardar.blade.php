{{-- ============================================================
    AVISO DE CAMBIOS SIN GUARDAR
    ------------------------------------------------------------
    Formularios marcados con data-avisar-cambios: si el usuario
    escribió algo y no lo ha guardado, al salir se le pregunta.
    - Navegación dentro del sistema (menú, «Volver», enlaces):
      ventana de confirmación del sistema (trackitConfirmar).
    - Cerrar o recargar la pestaña: aviso del navegador.
    Se considera guardado al enviar el formulario o al pulsar un
    botón con type="submit" o data-guardar. Cualquier cambio
    posterior vuelve a activar el aviso.
    Campos que no cuentan: type="search" o dentro de data-sin-aviso.
============================================================ --}}
<script>
    (() => {
        if (window.__trackitCambiosSinGuardar) {
            return;
        }

        window.__trackitCambiosSinGuardar = true;

        const SELECTOR = '[data-avisar-cambios]';

        const contenedorDe = (el) => {
            if (! (el instanceof Element) || el.closest('[data-sin-aviso]')) {
                return null;
            }

            return el.closest(SELECTOR);
        };

        const marcar = (evento) => {
            if (! evento.isTrusted || evento.target?.type === 'search') {
                return;
            }

            contenedorDe(evento.target)?.setAttribute('data-con-cambios', '');
        };

        const limpiar = (contenedor) => contenedor?.removeAttribute('data-con-cambios');

        window.trackitHayCambiosSinGuardar = () => Boolean(document.querySelector(SELECTOR + '[data-con-cambios]'));

        window.trackitDescartarCambios = () => document
            .querySelectorAll(SELECTOR + '[data-con-cambios]')
            .forEach(limpiar);

        document.addEventListener('input', marcar, true);
        document.addEventListener('change', marcar, true);

        // Guardar: el formulario se envía o se pulsa el botón de guardar.
        document.addEventListener('submit', (evento) => limpiar(contenedorDe(evento.target)), true);

        document.addEventListener('click', (evento) => {
            const boton = evento.target instanceof Element
                ? evento.target.closest('[type="submit"], [data-guardar]')
                : null;

            if (boton) {
                limpiar(contenedorDe(boton));
            }
        }, true);

        // Navegación dentro del sistema (wire:navigate, «Volver», menú).
        document.addEventListener('livewire:navigate', (evento) => {
            if (! window.trackitHayCambiosSinGuardar() || evento.detail?.history) {
                return;
            }

            evento.preventDefault();

            const destino = String(evento.detail?.url ?? '');

            const salir = () => {
                window.trackitDescartarCambios();

                if (destino) {
                    window.Livewire.navigate(destino);
                }
            };

            if (! window.trackitConfirmar) {
                salir();

                return;
            }

            window.trackitConfirmar({
                titulo: '¿Salir sin guardar?',
                mensaje: 'Hiciste cambios en este formulario que todavía no se han guardado. Si sales, se perderán.',
                confirmar: 'Salir sin guardar',
                cancelar: 'Seguir editando',
                peligro: true,
            }).then((confirmado) => confirmado && salir());
        });

        /*
         * Enlaces normales (sin wire:navigate, p. ej. «Cancelar»): antes
         * recargaban la página y salía el aviso del navegador («¿Deseas
         * abandonar el sitio?»). Ahora se usa la misma ventana del sistema.
         */
        document.addEventListener('click', (evento) => {
            if (
                evento.defaultPrevented
                || evento.button !== 0
                || evento.metaKey || evento.ctrlKey || evento.shiftKey || evento.altKey
                || ! window.trackitHayCambiosSinGuardar()
                || ! window.trackitConfirmar
            ) {
                return;
            }

            const enlace = evento.target instanceof Element ? evento.target.closest('a[href]') : null;

            if (
                ! enlace
                || enlace.hasAttribute('download')
                || (enlace.target && enlace.target !== '_self')
                || enlace.hasAttribute('wire:navigate')
                || enlace.hasAttribute('wire:navigate.hover')
                || enlace.closest('[data-sin-aviso]')
            ) {
                return;
            }

            const destino = new URL(enlace.href, window.location.href);

            // Solo páginas del sistema; anclas (#) de la misma página no salen.
            if (
                destino.origin !== window.location.origin
                || (destino.pathname === window.location.pathname && destino.search === window.location.search && destino.hash)
            ) {
                return;
            }

            evento.preventDefault();

            window.trackitConfirmar({
                titulo: '¿Salir sin guardar?',
                mensaje: 'Hiciste cambios en este formulario que todavía no se han guardado. Si sales, se perderán.',
                confirmar: 'Salir sin guardar',
                cancelar: 'Seguir editando',
                peligro: true,
            }).then((confirmado) => {
                if (! confirmado) {
                    return;
                }

                window.trackitDescartarCambios();

                window.Livewire?.navigate
                    ? window.Livewire.navigate(destino.href)
                    : (window.location.href = destino.href);
            });
        }, true);

        // Cerrar o recargar la pestaña.
        window.addEventListener('beforeunload', (evento) => {
            if (window.trackitHayCambiosSinGuardar()) {
                evento.preventDefault();
                evento.returnValue = '';
            }
        });
    })();
</script>
