<div aria-busy="true" aria-label="Cargando formulario de edición" class="space-y-5">
    <div class="flex justify-end gap-3" aria-hidden="true">
        <div class="h-10 w-32 animate-pulse rounded-lg bg-[var(--theme-surface-soft)]"></div>
        <div class="h-10 w-32 animate-pulse rounded-lg bg-[var(--theme-surface-soft)]"></div>
    </div>
    <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="mb-4 text-sm font-semibold text-[var(--theme-text-strong)]">Tipo de equipo</h2>
                <div aria-hidden="true" class="h-11 max-w-lg animate-pulse rounded-md bg-[var(--theme-surface-soft)]"></div>
            </div>
            <div aria-hidden="true" class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
                <div class="h-3 w-40 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
                <div class="h-[72px] w-[200px] animate-pulse rounded-md bg-[var(--theme-surface-soft)]"></div>
            </div>
        </div>
    </section>
    <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
        <p role="status" class="mb-5 text-sm text-[var(--theme-text-muted)]">Cargando los campos configurados del equipo...</p>
        <div aria-hidden="true" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @for ($i = 0; $i < 6; $i++)
                <div class="space-y-2 animate-pulse">
                    <div class="h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="h-11 rounded-md bg-[var(--theme-surface-soft)]"></div>
                </div>
            @endfor
        </div>
    </section>
    <div aria-hidden="true" class="flex justify-end gap-3">
        <div class="h-11 w-28 animate-pulse rounded-lg bg-[var(--theme-surface-soft)]"></div>
        <div class="h-11 w-40 animate-pulse rounded-lg bg-[var(--theme-surface-soft)]"></div>
    </div>
</div>
