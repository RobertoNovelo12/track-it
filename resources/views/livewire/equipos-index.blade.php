<div
    x-data="@include('livewire.equipos.estado-alpine')"

    @exportar-inventario.window="exportar($event.detail.formato)"
    @inventario-exportacion-vacia.window="exportacionVacia()"
    x-effect="
        document.body.classList.toggle(
            'overflow-hidden',
            filtersOpen
        )
    "

    @keydown.escape.window="
        filtersOpen = false
    "

    @resize.window="
        if (window.innerWidth >= 768) {
            filtersOpen = false
        }
    "
>

    {{-- El progreso de la exportación se muestra en el aviso global (toast). --}}
    {{-- ============================================================
        X-CLOAK
    ============================================================ --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>


    {{-- ============================================================
        CONTROLES MÓVILES
    ============================================================ --}}
    @include(
        'livewire.equipos.filters.mobile'
    )


    {{-- ============================================================
        PANEL DE FILTROS - MÓVIL
    ============================================================ --}}
    @include(
        'livewire.equipos.filters.mobile-sheet'
    )


    {{-- ============================================================
        FILTROS - ESCRITORIO
    ============================================================ --}}
    @include(
        'livewire.equipos.filters.desktop'
    )


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    @include(
        'livewire.equipos.results.section'
    )

</div>