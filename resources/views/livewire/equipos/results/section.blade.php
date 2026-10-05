{{-- ============================================================
    SECCIÓN DE RESULTADOS
============================================================ --}}
<div
    class="
        relative

        bg-transparent
        border-0
        rounded-none
        overflow-visible

        md:bg-[var(--theme-surface)]

        md:border
        md:border-[var(--theme-border)]

        md:rounded-lg

        md:overflow-hidden
    "
>

    {{-- ========================================================
        OVERLAY DE CARGA
    ======================================================== --}}
    <div
        wire:loading.delay

        class="
            absolute
            inset-0

            bg-[var(--theme-surface)]

            opacity-50

            z-10

            pointer-events-none
        "
    ></div>


    {{-- ========================================================
        CABECERA DE RESULTADOS
    ======================================================== --}}
    @include(
        'livewire.equipos.results.header'
    )


    {{-- ========================================================
        TABLA - ESCRITORIO
    ======================================================== --}}
    @include(
        'livewire.equipos.results.table'
    )


    {{-- ========================================================
        TARJETAS - MÓVIL
    ======================================================== --}}
    @include(
        'livewire.equipos.results.mobile'
    )


    {{-- ========================================================
        PAGINACIÓN
    ======================================================== --}}
    @include(
        'livewire.equipos.results.pagination'
    )

</div>