{{-- ============================================================
    BARRA DE BÚSQUEDA Y FILTROS (Administración)
    ------------------------------------------------------------
    Mismo diseño en todas las pestañas:

    <x-admin.filter-bar>
        <x-admin.search wire:model.live.debounce.350ms="search" placeholder="Buscar..." />
        <x-admin.select wire:model.live="estado">...</x-admin.select>

        <x-slot:actions>
            <x-admin.action-button wire:click="crear">Añadir</x-admin.action-button>
        </x-slot:actions>

        <x-slot:footer>
            (opcional: limpiar filtros, avisos)
        </x-slot:footer>
    </x-admin.filter-bar>
============================================================ --}}
<section {{ $attributes->class('rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-3 sm:p-4') }}>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

        {{-- Buscador y filtros --}}
        <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            {{ $slot }}
        </div>

        {{-- Acciones del apartado --}}
        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                {{ $actions }}
            </div>
        @endisset

    </div>

    {{--
        El pie solo se muestra si tiene contenido real. Livewire agrega
        comentarios <!--[if BLOCK]--> alrededor de los @if, por eso se
        ignoran al comprobarlo (si no, se añadía espacio vacío).
    --}}
    @isset($footer)
        @if (trim(preg_replace('/<!--.*?-->/s', '', (string) $footer)) !== '')
            <div class="mt-3 flex flex-wrap items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    @endisset

</section>
