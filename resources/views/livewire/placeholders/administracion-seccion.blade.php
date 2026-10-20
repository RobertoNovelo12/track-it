{{-- ============================================================
    ADMINISTRACIÓN · SECCIÓN EN CARGA
    ------------------------------------------------------------
    Único esqueleto para todas las pestañas (lazy). Solo cubre lo
    que depende de datos: filtros, métricas y tabla. El título y
    la ruta ya están en la página y no se repiten aquí.

    Cada componente puede enviar las columnas reales de su tabla
    (mismo formato que <x-ui.skeleton-table>):

        return view('livewire.placeholders.administracion-seccion', [
            'columnas' => [['label' => 'Marca', 'width' => '20%'], ...],
            'anchoMinimo' => '900px',
        ]);
============================================================ --}}
@php
    $columnas ??= 5;
    $anchoMinimo ??= null;
    $metricas ??= 3;
@endphp

<div class="min-w-0 space-y-5" aria-busy="true" aria-live="polite">
    <span class="sr-only">Cargando sección…</span>

    {{-- Buscador y filtros --}}
    <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-3 sm:p-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <x-ui.skeleton class="h-10 w-full sm:flex-1" />
            <x-ui.skeleton class="h-10 w-full sm:w-48" />
            <x-ui.skeleton class="h-10 w-full sm:w-32" />
        </div>
    </div>

    {{-- Métricas --}}
    @if ($metricas > 0)
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @for ($i = 0; $i < $metricas; $i++)
                <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3.5">
                    <x-ui.skeleton class="h-7 w-12" />
                    <x-ui.skeleton class="mt-2 h-3 w-24" />
                </div>
            @endfor
        </div>
    @endif

    {{-- Tabla --}}
    <x-ui.skeleton-table
        :columns="$columnas"
        :min-width="$anchoMinimo"
    />
</div>
