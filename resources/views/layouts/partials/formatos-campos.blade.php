{{-- ============================================================
    FORMATO DE CAMPOS (teclado del celular y ejemplos)
    ------------------------------------------------------------
    window.trackitFormatoCampo(texto) devuelve, según la clave y la
    etiqueta del campo (IMEI, Dirección IP, Dirección MAC…):
    - inputmode: qué teclado abre el celular (numérico, teléfono…).
    - mayusculas / sinCorrector: series y códigos sin autocorrector.
    - ejemplo: texto de ejemplo para el campo vacío.
    - ayuda: formato esperado, visible debajo ANTES de escribir.
    - maxlength: largo máximo cuando el formato es fijo.
    Lo usan los formularios de equipo (alta y edición); un campo
    nuevo con alguno de estos nombres lo recibe solo.
============================================================ --}}
<script>
    window.trackitFormatoCampo = (clave) => {
        // Clave + etiqueta, sin acentos: «tipo_5_campo_12 Dirección MAC».
        const nombre = String(clave ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

        if (/imei/.test(nombre)) {
            return {
                inputmode: 'numeric',
                maxlength: 15,
                sinCorrector: true,
                ejemplo: 'Ej. 356938035643809',
                ayuda: '15 dígitos. Se consulta marcando *#06# en el teléfono.',
            };
        }

        if (/telefon|celular/.test(nombre)) {
            return { inputmode: 'tel', sinCorrector: true, ejemplo: 'Ej. 998 123 4567' };
        }

        if (/(^|[\s_])ip($|[\s_])/.test(nombre)) {
            return {
                inputmode: 'decimal',
                sinCorrector: true,
                ejemplo: 'Ej. 192.168.1.20',
                ayuda: 'Cuatro números del 0 al 255 separados por puntos.',
            };
        }

        // Con límites: «almacenamiento» no es una MAC.
        if (/(^|[\s_])mac($|[\s_])/.test(nombre)) {
            return {
                mayusculas: true,
                sinCorrector: true,
                maxlength: 17,
                ejemplo: 'Ej. 4C:CC:2A:00:1B:6A',
                ayuda: 'Seis pares de letras o números separados por dos puntos (:).',
            };
        }

        if (/serie|service.?tag|factura|codigo|serial/.test(nombre)) {
            return { mayusculas: true, sinCorrector: true };
        }

        if (/host/.test(nombre)) {
            return { sinCorrector: true, ejemplo: 'Ej. 030-150-123-PO' };
        }

        return {};
    };
</script>
