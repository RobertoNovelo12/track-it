{{-- ============================================================
    ATAJO PARA GUARDAR: Ctrl+G (o Ctrl+S; ⌘ en Mac)
    ------------------------------------------------------------
    Pulsa el botón de guardar del formulario en pantalla, igual que
    hacer clic (misma validación y mismos avisos).
    - Formulario: el que tiene el cursor; si no, el primero visible
      marcado con data-avisar-cambios; si no, el único <form> visible.
    - Botón: [data-guardar] o [type=submit] visible y habilitado
      (también los que están fuera del <form> con form="id").
    - Si no hay qué guardar, el atajo no hace nada y el navegador
      no abre «Buscar» ni «Guardar página».
    - Al pasar el cursor por el botón se ve «Guardar registro (Ctrl+G)».
============================================================ --}}
<script>
    (() => {
        if (window.__trackitAtajoGuardar) {
            return;
        }

        window.__trackitAtajoGuardar = true;

        const visible = (el) => el && el.offsetParent !== null && ! el.closest('[hidden], [inert]');

        const botonDe = (contenedor) => {
            if (! contenedor) return null;

            const candidatos = [...contenedor.querySelectorAll('[data-guardar], [type="submit"]')];

            if (contenedor.id) {
                candidatos.push(...document.querySelectorAll(`[form="${CSS.escape(contenedor.id)}"]`));
            }

            return candidatos.find(b => visible(b) && ! b.disabled && b.getAttribute('aria-disabled') !== 'true') ?? null;
        };

        const contenedorActual = () => {
            const activo = document.activeElement;
            const deFoco = activo instanceof Element
                ? activo.closest('[data-avisar-cambios], form')
                : null;

            if (deFoco && visible(deFoco)) return deFoco;

            const marcado = [...document.querySelectorAll('[data-avisar-cambios]')].find(visible);
            if (marcado) return marcado;

            const formularios = [...document.querySelectorAll('main form')].filter(visible);

            return formularios.length === 1 ? formularios[0] : null;
        };

        /*
         * Pista del atajo, como «Colapsar menú (Ctrl+B)»: al pasar el
         * cursor (o enfocar) un botón de guardar de un formulario se le
         * agrega el título «Guardar (Ctrl+G)». Se hace al momento porque
         * muchos formularios cargan después (lazy) o se redibujan.
         */
        const esMac = /Mac|iPhone|iPad/i.test(navigator.platform || navigator.userAgent || '');
        const teclas = esMac ? '⌘G' : 'Ctrl+G';

        const ponerPista = (evento) => {
            const boton = evento.target instanceof Element
                ? evento.target.closest('[data-guardar], [type="submit"]')
                : null;

            // Solo botones de guardar de formularios (no buscadores ni filtros).
            if (! boton || boton.dataset.pistaAtajo) return;
            if (! boton.matches('[data-guardar]') && ! boton.closest('[data-avisar-cambios]')) return;

            boton.dataset.pistaAtajo = '1';
            boton.setAttribute('aria-keyshortcuts', esMac ? 'Meta+G' : 'Control+G');

            const texto = (boton.getAttribute('title') || boton.innerText || 'Guardar').replace(/\s+/g, ' ').trim();

            boton.setAttribute('title', `${texto} (${teclas})`);
        };

        document.addEventListener('pointerover', ponerPista, true);
        document.addEventListener('focusin', ponerPista, true);

        document.addEventListener('keydown', (evento) => {
            const tecla = String(evento.key || '').toLowerCase();

            if (! (evento.ctrlKey || evento.metaKey) || evento.altKey || evento.shiftKey || (tecla !== 'g' && tecla !== 's')) {
                return;
            }

            evento.preventDefault();

            if (evento.repeat) return;

            const contenedor = contenedorActual();
            const boton = botonDe(contenedor);

            if (boton) {
                boton.click();

                return;
            }

            if (contenedor instanceof HTMLFormElement && typeof contenedor.requestSubmit === 'function') {
                contenedor.requestSubmit();
            }
        });
    })();
</script>
