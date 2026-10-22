<div
    x-data="{
        filtersOpen: false,
        exportando: false,
        async exportar(formato) {
            if (this.exportando) return;
            const escritorio = [...this.$root.querySelectorAll('.row-checkbox:checked')]
                .filter(el => el.getClientRects().length).map(el => el.value);
            const movil = [...this.$root.querySelectorAll('[data-export-selected]')]
                .filter(el => el.getClientRects().length).map(el => el.dataset.exportSelected);
            this.exportando = true;
            try {
                await this.$wire.exportarInventario(formato, [...new Set([...escritorio, ...movil])]);
            } catch (error) {
                alert('No se pudo generar el documento. Intenta nuevamente.');
            } finally {
                this.exportando = false;
            }
        }
    }"

    @exportar-inventario.window="exportar($event.detail.formato)"
    @inventario-exportacion-vacia.window="alert('No hay equipos para exportar con estos filtros o selección.')"
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

    <div x-cloak x-show="exportando" role="status" class="fixed bottom-5 right-5 z-50 rounded-lg bg-[var(--theme-surface)] border border-[var(--theme-border)] p-4 shadow-lg">
        Generando documento…
    </div>
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