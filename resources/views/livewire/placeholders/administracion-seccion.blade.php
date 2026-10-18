{{-- ============================================================
    ADMINISTRACIÓN · SECCIÓN EN CARGA
    ------------------------------------------------------------
    Único esqueleto para todas las pestañas (lazy). Solo cubre lo
    que depende de datos: filtros, métricas y tabla. El título y
    la ruta ya están en la página y no se repiten aquí.
============================================================ --}}
<div class="min-w-0 space-y-5" aria-busy="true" aria-live="polite">
    <span class="sr-only">Cargando sección...</span>

    <div class="animate-pulse space-y-5">

        {{-- Buscador y filtros --}}
        <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="h-10 flex-1 rounded-md bg-[var(--theme-surface-soft)]"></div>
                <div class="h-10 rounded-md bg-[var(--theme-surface-soft)] sm:w-48"></div>
                <div class="h-10 rounded-md bg-[var(--theme-surface-soft)] sm:w-32"></div>
            </div>
        </div>

        {{-- Métricas --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3.5">
                    <div class="h-7 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="mt-2 h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                </div>
            @endfor
        </div>

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
            <div class="flex items-center justify-between gap-4 border-b border-[var(--theme-border)] px-5 py-4">
                <div class="h-3 w-48 rounded bg-[var(--theme-surface-soft)]"></div>
                <div class="h-8 w-40 rounded-md bg-[var(--theme-surface-soft)]"></div>
            </div>

            <div class="h-11 border-b border-[var(--theme-border)] bg-[var(--theme-surface-soft)]"></div>

            @for ($i = 0; $i < 6; $i++)
                <div class="flex items-center gap-4 border-b border-[var(--theme-border)] px-5 py-4 last:border-b-0">
                    <div class="h-3 w-1/4 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="h-3 w-1/5 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="h-3 w-1/6 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="ml-auto h-6 w-14 rounded-full bg-[var(--theme-surface-soft)]"></div>
                </div>
            @endfor
        </div>

    </div>
</div>
