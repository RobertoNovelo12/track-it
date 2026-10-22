{{-- ============================================================
    LOGOTIPO DE LA ORGANIZACIÓN
    ------------------------------------------------------------
    Estilos en línea (no requieren recompilar Vite).

    - Tamaño: --org-logo-w se define en cada <img> a partir de la
      escala configurada en Administración › Organización.
    - Modo oscuro: data-dark-mode="original|invertir|blanco|fondo".
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

    /*
     * «Con fondo»: placa de color detrás del logotipo (--org-logo-bg,
     * definido en cada <img>). Los colores del logotipo no cambian.
     */
    html.dark .org-logo[data-dark-mode="fondo"],
    .org-logo-preview-dark[data-dark-mode="fondo"] {
        filter: none !important;
        opacity: 1 !important;
        background-color: var(--org-logo-bg, #FFFFFF);
        padding: 6px 10px;
        border-radius: 10px;
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

{{-- ============================================================
    COLOR PRINCIPAL DE LA ORGANIZACIÓN
    ------------------------------------------------------------
    Administración › Organización › Colores. Reemplaza
    --theme-primary y sus variantes; en modo oscuro se aclara un
    poco para conservar el contraste (igual que el tema base).
============================================================ --}}
@php
    $colorOrganizacion = app(\App\Services\OrganizacionService::class)->colorPrimario();
@endphp

@if ($colorOrganizacion)
    <style>
        :root {
            --theme-primary: {{ $colorOrganizacion }};
            --theme-primary-hover: color-mix(in srgb, {{ $colorOrganizacion }} 82%, #000000);
            --theme-primary-soft: color-mix(in srgb, {{ $colorOrganizacion }} 10%, transparent);
            --theme-primary-soft-subtle: color-mix(in srgb, {{ $colorOrganizacion }} 5%, transparent);
            --theme-primary-border: color-mix(in srgb, {{ $colorOrganizacion }} 30%, transparent);
            --theme-primary-border-strong: color-mix(in srgb, {{ $colorOrganizacion }} 50%, transparent);
        }

        html.dark {
            --theme-primary: color-mix(in srgb, {{ $colorOrganizacion }} 85%, #FFFFFF);
            --theme-primary-hover: color-mix(in srgb, {{ $colorOrganizacion }} 72%, #FFFFFF);
            --theme-primary-soft: color-mix(in srgb, {{ $colorOrganizacion }} 14%, transparent);
            --theme-primary-soft-subtle: color-mix(in srgb, {{ $colorOrganizacion }} 9%, transparent);
            --theme-primary-border: color-mix(in srgb, {{ $colorOrganizacion }} 40%, transparent);
            --theme-primary-border-strong: color-mix(in srgb, {{ $colorOrganizacion }} 60%, transparent);
        }
    </style>
@endif
