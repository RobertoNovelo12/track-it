{{-- ============================================================
    BARRA DE SELECCIÓN GENÉRICA (x-data-table bulk-trash="tipo")
    ------------------------------------------------------------
    Aparece al marcar filas (en cualquier página). Escritorio: se pone
    encima de la barra de resultados, del mismo alto (no mueve nada).
    Móvil: fija abajo, sobre el menú inferior.
    «Mover a la papelera» revisa todos y explica los que no se pueden
    (App\Livewire\PapeleraConfirmar::abrirVarios).
============================================================ --}}
<div
    x-data="{
        alto: null,
        medir() {
            const barra = this.$el.parentElement?.querySelector(':scope > [data-tabla-toolbar]');
            this.alto = barra && window.innerWidth >= 1024 ? barra.offsetHeight : null;
        },
    }"
    x-init="medir()"
    x-effect="seleccion.length > 0 && $nextTick(() => medir())"
    @resize.window="medir()"
    :style="alto ? { height: alto + 'px' } : {}"
    x-show="seleccion.length > 0"
    x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    role="region"
    aria-label="Acciones para la selección"
    class="fixed inset-x-3 bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] z-40 lg:absolute lg:inset-x-px lg:top-px lg:bottom-auto lg:z-30 lg:rounded-t-lg lg:bg-[var(--theme-surface)]"
>
    <div class="flex h-full items-center gap-2 rounded-xl border border-[var(--theme-primary-border,var(--theme-border))] bg-[var(--theme-surface)] px-2.5 py-2 shadow-lg shadow-[var(--theme-shadow)] lg:rounded-none lg:rounded-t-lg lg:border-0 lg:border-b lg:border-[var(--theme-border)] lg:bg-[var(--theme-primary-soft-subtle,var(--theme-surface-soft))] lg:px-4 lg:shadow-none">

        <button
            type="button"
            x-on:click="limpiarSeleccion()"
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text-strong)]"
            title="Quitar selección"
            aria-label="Quitar selección"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        <p class="min-w-0 flex-1 truncate text-sm text-[var(--theme-text)]" aria-live="polite">
            <strong class="font-semibold text-[var(--theme-text-strong)]" x-text="seleccion.length"></strong>
            <span x-text="seleccion.length === 1 ? 'seleccionado' : 'seleccionados'"></span>
        </p>

        <button
            type="button"
            x-on:click="moverSeleccionAPapelera()"
            class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-md bg-[var(--theme-danger-soft)] px-3 text-sm font-medium text-[var(--theme-danger)] transition-opacity hover:opacity-80"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-papelera') }}"></use></svg>
            <span class="hidden sm:inline">Mover a la papelera</span>
            <span class="sm:hidden">Papelera</span>
        </button>
    </div>
</div>
