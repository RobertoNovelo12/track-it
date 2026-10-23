<style>
    /*
    |--------------------------------------------------------------------------
    | ACCESIBILIDAD (Ajustes › Preferencias)
    |--------------------------------------------------------------------------
    |
    | Se activan con atributos en <html> (PreferenciasUsuarioService::
    | atributosAccesibilidad). La pantalla de Preferencias los cambia al
    | instante como vista previa; al guardar quedan fijos.
    |
    */

    /*
     * TEXTOS MUY PEQUEÑOS
     * 9 y 10 px se leen mal (sobre todo en móvil): suben a 10 y 11 px
     * en todo el sistema. Las reglas de abajo los escalan desde aquí.
     */
    .text-\[9px\] {
        font-size: 10px;
    }

    .text-\[10px\] {
        font-size: 11px;
    }

    /*
     * TAMAÑO DEL TEXTO
     * Todo lo que usa rem (texto, espacios, botones) crece con el tamaño
     * base. Los textos con tamaño fijo en px se escalan aparte.
     */
    html[data-texto="grande"] {
        --escala-texto: 1.125;
        font-size: 112.5%;
    }

    html[data-texto="muy_grande"] {
        --escala-texto: 1.25;
        font-size: 125%;
    }

    html[data-texto="grande"] .text-\[9px\],
    html[data-texto="muy_grande"] .text-\[9px\] {
        font-size: calc(10px * var(--escala-texto));
    }

    html[data-texto="grande"] .text-\[10px\],
    html[data-texto="muy_grande"] .text-\[10px\] {
        font-size: calc(11px * var(--escala-texto));
    }

    html[data-texto="grande"] .text-\[11px\],
    html[data-texto="muy_grande"] .text-\[11px\] {
        font-size: calc(11px * var(--escala-texto));
    }

    html[data-texto="grande"] .text-\[13px\],
    html[data-texto="muy_grande"] .text-\[13px\] {
        font-size: calc(13px * var(--escala-texto));
    }

    /*
     * REDUCIR ANIMACIONES
     * Transiciones y animaciones instantáneas. Los indicadores de carga
     * (animate-spin) siguen girando para que se note que algo ocurre.
     */
    html[data-movimiento="reducido"] *:not(.animate-spin),
    html[data-movimiento="reducido"] *::before,
    html[data-movimiento="reducido"] *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        transition-delay: 0s !important;
        scroll-behavior: auto !important;
    }

    html[data-movimiento="reducido"] {
        scroll-behavior: auto !important;
    }

    /*
     * ALTO CONTRASTE
     * Texto secundario más oscuro (o más claro en modo oscuro) y bordes
     * más marcados. El color principal de la organización no cambia.
     */
    html[data-contraste="alto"]:not(.dark) {
        --theme-text: #2B2C2A;
        --theme-text-strong: #101B2C;
        --theme-text-muted: #4B4C4A;
        --theme-border: rgba(40, 41, 39, 0.30);
        --theme-border-strong: rgba(40, 41, 39, 0.55);
    }

    html.dark[data-contraste="alto"] {
        --theme-text: #EEF0F1;
        --theme-text-strong: #FFFFFF;
        --theme-text-muted: #C8CDD0;
        --theme-border: rgba(255, 255, 255, 0.24);
        --theme-border-strong: rgba(255, 255, 255, 0.42);
    }

    /*
     * RESALTAR EL FOCO DEL TECLADO
     * Contorno grueso al navegar con Tab, incluso en controles que
     * normalmente lo ocultan.
     */
    html[data-foco="resaltado"] :focus-visible {
        outline: 3px solid var(--theme-primary) !important;
        outline-offset: 2px !important;
        box-shadow: 0 0 0 6px color-mix(in srgb, var(--theme-primary) 22%, transparent) !important;
    }
</style>
