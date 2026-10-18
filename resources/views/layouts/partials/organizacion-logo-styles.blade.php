{{-- ============================================================
    LOGOTIPO DE LA ORGANIZACIÓN
    ------------------------------------------------------------
    Estilos en línea (no requieren recompilar Vite).

    - Tamaño: --org-logo-w se define en cada <img> a partir de la
      escala configurada en Administración › Organización.
    - Modo oscuro: data-dark-mode="original|invertir|blanco".
      Se usa !important para sustituir las reglas anteriores
      basadas en #sidebarLogo / #guestLogo.
============================================================ --}}
<style>
    html.dark .org-logo[data-dark-mode="original"],
    .org-logo-preview-dark[data-dark-mode="original"] {
        filter: none !important;
        opacity: 1 !important;
    }

    html.dark .org-logo[data-dark-mode="invertir"],
    .org-logo-preview-dark[data-dark-mode="invertir"] {
        filter: invert(1) hue-rotate(180deg) !important;
        opacity: 1 !important;
    }

    html.dark .org-logo[data-dark-mode="blanco"],
    .org-logo-preview-dark[data-dark-mode="blanco"] {
        filter: brightness(0) invert(1) !important;
        opacity: 0.92 !important;
    }

    #guestLogo.org-logo {
        width: var(--org-logo-w, 150px);
        max-height: 6rem;
        height: auto;
    }

    @media (min-width: 640px) {
        #guestLogo.org-logo {
            width: var(--org-logo-w-sm, 170px);
        }
    }
</style>
