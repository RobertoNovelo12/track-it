{{-- ============================================================
    NAVEGACIÓN: MENÚS Y PRECARGA
    ------------------------------------------------------------
    - Mientras carga, cada pantalla muestra su placeholder
      detallado (componentes lazy). No hay spinner ni esqueleto
      genérico: daban sensación de lentitud.
    - Los menús (hojas del menú inferior, perfil, acciones) se
      cierran al tocar la opción, sin esperar a que cargue.
    - Móvil: la página se empieza a pedir al tocar el enlace.
============================================================ --}}
<script>
    (() => {
        // Una sola vez por carga completa (el layout se reemplaza al navegar).
        if (window.trackitCargaNavegacion) {
            return;
        }

        window.trackitCargaNavegacion = true;

        document.addEventListener('livewire:navigate', (evento) => {
            // Se espera a que los demás oyentes decidan si cancelan (cambios sin guardar).
            setTimeout(() => {
                if (! evento.defaultPrevented) {
                    window.dispatchEvent(new CustomEvent('trackit-cerrar-menus'));
                }
            }, 0);
        });

        /*
         * Precarga al tocar (móvil). En el teléfono no hay «hover»:
         * Livewire empezaría a pedir la página hasta soltar el dedo.
         * Al tocar un enlace wire:navigate.hover se simula el hover,
         * así la página se pide desde que el dedo toca la pantalla.
         * Si el dedo se desliza (scroll) se cancela.
         */
        let enlaceTocado = null;
        let puntoInicial = null;

        const soltarEnlace = () => {
            enlaceTocado?.dispatchEvent(new MouseEvent('mouseleave'));
            enlaceTocado = null;
        };

        const opciones = { passive: true, capture: true };

        document.addEventListener('touchstart', (evento) => {
            const enlace = evento.target.closest?.('a[wire\\:navigate\\.hover]');

            if (! enlace || evento.touches.length !== 1) {
                return;
            }

            enlaceTocado = enlace;
            puntoInicial = evento.touches[0];
            enlace.dispatchEvent(new MouseEvent('mouseenter'));
        }, opciones);

        document.addEventListener('touchmove', (evento) => {
            if (! enlaceTocado || ! puntoInicial) {
                return;
            }

            const punto = evento.touches[0];

            if (Math.abs(punto.clientX - puntoInicial.clientX) > 10 || Math.abs(punto.clientY - puntoInicial.clientY) > 10) {
                soltarEnlace();
            }
        }, opciones);

        document.addEventListener('touchcancel', soltarEnlace, opciones);

        // Al soltar no se cancela: la precarga sigue y el clic la aprovecha.
        document.addEventListener('touchend', () => { enlaceTocado = null; }, opciones);
    })();
</script>
