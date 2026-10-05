<div
    x-data="{
        filtersOpen: false
    }"

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