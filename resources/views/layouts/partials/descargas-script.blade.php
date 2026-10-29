{{-- ============================================================
    DESCARGAS SIN GESTORES DE DESCARGA
    ------------------------------------------------------------
    Gestores como Free Download Manager o IDM capturan la dirección
    de la descarga y la vuelven a pedir por su cuenta, sin la sesión
    del navegador: el servidor responde con el inicio de sesión y
    guardan «login.html».

    Aquí la página descarga el archivo ella misma (fetch, con la
    sesión) y se lo entrega al navegador ya descargado (blob:), que
    los gestores no pueden capturar. El nombre del archivo es el que
    manda el servidor.

    Se aplica sola a los enlaces de la app que descargan:
      - con atributo download,
      - que terminan en /descargar (reportes, actas),
      - /equipos/{id}/exportar (ficha del equipo),
      - con ?descargar=1 (código de barras).
    También cuando se crean y pulsan por código (a.click()).
    Para descargar desde JS: window.trackitDescargar(url).

    Si la respuesta es una página (error, sesión vencida), se abre
    normalmente para que se vea el mensaje.
============================================================ --}}
<script>
    (() => {
        if (window.trackitDescargar) {
            return;
        }

        const esDescarga = (enlace, url) => {
            if (url.origin !== window.location.origin) return false;
            if (enlace && enlace.hasAttribute('download')) return true;
            if (/\/descargar\/?$/.test(url.pathname)) return true;
            if (/^\/equipos\/\d+\/exportar\/?$/.test(url.pathname)) return true;

            return url.searchParams.get('descargar') === '1';
        };

        const nombreArchivo = (respuesta, url, sugerido) => {
            const disposicion = respuesta.headers.get('Content-Disposition') || '';

            let coincidencia = disposicion.match(/filename\*\s*=\s*(?:UTF-8|utf-8)''([^;]+)/);

            if (coincidencia) {
                try {
                    return decodeURIComponent(coincidencia[1].trim().replace(/^"|"$/g, ''));
                } catch (error) {
                    // Se usa el nombre simple.
                }
            }

            coincidencia = disposicion.match(/filename\s*=\s*"?([^";]+)"?/i);

            if (coincidencia) {
                return coincidencia[1].trim();
            }

            if (sugerido) {
                return sugerido;
            }

            try {
                return decodeURIComponent(url.pathname.split('/').filter(Boolean).pop() || 'archivo');
            } catch (error) {
                return 'archivo';
            }
        };

        window.trackitDescargar = async (direccion, sugerido = '') => {
            const url = new URL(direccion, window.location.href);
            let aviso = null;

            // Solo si tarda: un archivo ya generado baja casi al instante.
            const temporizador = setTimeout(() => {
                aviso = window.trackitToast?.('loading', 'Preparando el archivo…', 'Descargando') ?? null;
            }, 600);

            const avisar = (tipo, mensaje, titulo) => {
                if (aviso && window.trackitToastActualizar) {
                    window.trackitToastActualizar(aviso, tipo, mensaje, titulo);
                } else if (tipo !== 'success') {
                    window.trackitToast?.(tipo, mensaje, titulo);
                }
            };

            try {
                const respuesta = await fetch(url.href, { credentials: 'same-origin' });
                const tipo = respuesta.headers.get('Content-Type') || '';
                const adjunto = /attachment/i.test(respuesta.headers.get('Content-Disposition') || '');

                // Una página (inicio de sesión, error, sin permiso): mostrarla tal cual.
                if (! respuesta.ok || (tipo.includes('text/html') && ! adjunto)) {
                    clearTimeout(temporizador);

                    if (aviso && window.trackitToastActualizar) {
                        window.trackitToastActualizar(aviso, 'info', 'Abriendo…', 'Descargando');
                    }

                    window.location.assign(url.href);

                    return;
                }

                const archivo = await respuesta.blob();
                const objeto = URL.createObjectURL(archivo);
                const enlace = document.createElement('a');

                enlace.href = objeto;
                enlace.download = nombreArchivo(respuesta, url, sugerido);
                enlace.dataset.trackitBlob = '1';
                enlace.style.display = 'none';

                document.body.appendChild(enlace);
                enlace.click();
                enlace.remove();

                setTimeout(() => URL.revokeObjectURL(objeto), 60000);

                avisar('success', 'La descarga comenzó.', 'Archivo listo');
            } catch (error) {
                avisar('error', 'No se pudo descargar el archivo. Revisa tu conexión e intenta de nuevo.', 'Error al descargar');
            } finally {
                clearTimeout(temporizador);
            }
        };

        // Fase de captura: corre antes que cualquier otro manejador
        // (wire:navigate, @click.stop) e incluye los a.click() por código.
        document.addEventListener('click', (evento) => {
            if (
                evento.defaultPrevented
                || evento.button !== 0
                || evento.metaKey
                || evento.ctrlKey
                || evento.shiftKey
                || evento.altKey
            ) {
                return;
            }

            const enlace = evento.target?.closest?.('a[href]');

            if (! enlace || enlace.dataset.trackitBlob) {
                return;
            }

            let url;

            try {
                url = new URL(enlace.href, window.location.href);
            } catch (error) {
                return;
            }

            if (url.protocol === 'blob:' || url.protocol === 'data:' || ! esDescarga(enlace, url)) {
                return;
            }

            evento.preventDefault();
            evento.stopImmediatePropagation();

            window.trackitDescargar(url.href, enlace.getAttribute('download') || '');
        }, true);
    })();
</script>
