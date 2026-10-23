{{-- ============================================================
    ACCESIBILIDAD: ERRORES DE FORMULARIO
    ------------------------------------------------------------
    Tras cada respuesta de Livewire, los campos con error de
    validación se marcan para lectores de pantalla:
    - aria-invalid="true" en el campo (wire:model="campo").
    - aria-describedby → el mensaje de error que está debajo.
    Si la respuesta vino de una acción (Guardar, Enviar…) y hay
    errores, el foco va al primer campo con error, para que el
    usuario sepa dónde corregir sin buscarlo.
============================================================ --}}
<script>
    (() => {
        if (window.__trackitErroresAccesibles) {
            return;
        }

        window.__trackitErroresAccesibles = true;

        const CAMPOS = 'input, select, textarea, [role="combobox"]';

        // Elementos con wire:model, wire:model.live, wire:model.blur…
        const campoDe = (raiz, nombre) => {
            for (const el of raiz.querySelectorAll('*')) {
                for (const atributo of el.attributes) {
                    if (atributo.name.startsWith('wire:model') && atributo.value === nombre) {
                        return el.matches(CAMPOS) ? el : (el.querySelector(CAMPOS) ?? el);
                    }
                }
            }

            return null;
        };

        // Mensaje de error más cercano (mismo bloque del campo).
        const mensajeDe = (campo) => {
            let bloque = campo.parentElement;

            for (let nivel = 0; bloque && nivel < 3; nivel++, bloque = bloque.parentElement) {
                const mensaje = bloque.querySelector('[class*="theme-danger"]:not(input):not(button):not(svg)');

                if (mensaje && mensaje.textContent.trim() !== '') {
                    return mensaje;
                }
            }

            return null;
        };

        let contador = 0;

        const actualizar = (component, conAccion) => {
            const raiz = component.el;
            const errores = component.snapshot?.memo?.errors ?? {};
            const nombres = Array.isArray(errores) ? [] : Object.keys(errores);

            // Limpiar los que ya no tienen error.
            raiz.querySelectorAll('[aria-invalid="true"][data-error-accesible]').forEach((el) => {
                el.removeAttribute('aria-invalid');
                el.removeAttribute('data-error-accesible');
            });

            let primero = null;

            nombres.forEach((nombre) => {
                const campo = campoDe(raiz, nombre);

                if (! campo) {
                    return;
                }

                campo.setAttribute('aria-invalid', 'true');
                campo.setAttribute('data-error-accesible', '');

                const mensaje = mensajeDe(campo);

                if (mensaje) {
                    mensaje.id ||= 'error-campo-' + (++contador);
                    mensaje.setAttribute('role', 'alert');

                    const ids = new Set((campo.getAttribute('aria-describedby') ?? '').split(' ').filter(Boolean));
                    ids.add(mensaje.id);
                    campo.setAttribute('aria-describedby', [...ids].join(' '));
                }

                primero ??= campo;
            });

            if (conAccion && primero && typeof primero.focus === 'function') {
                primero.focus({ preventScroll: false });
            }
        };

        document.addEventListener('livewire:init', () => {
            window.Livewire.hook('commit', ({ component, commit, succeed }) => {
                const conAccion = (commit?.calls ?? []).length > 0;

                succeed(() => {
                    // Después de que Livewire actualice el HTML.
                    requestAnimationFrame(() => actualizar(component, conAccion));
                });
            });
        });
    })();
</script>
