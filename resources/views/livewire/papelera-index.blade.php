{{-- ============================================================
    PAPELERA (App\Livewire\PapeleraIndex)
============================================================ --}}
<div
    class="space-y-5"
    x-data="{
        eliminar: null,
        pedirEliminar(ids) { this.eliminar = ids; },
        cerrar() { this.eliminar = null; },
        async confirmar() {
            const ids = this.eliminar;
            this.eliminar = null;
            if (! ids) return;

            // Aviso con barra de avance mientras se elimina.
            const cantidad = ids === 'seleccion' ? ($wire.seleccionados?.length ?? 0) : 1;
            const aviso = window.trackitToast?.(
                'loading',
                'Esto puede tardar unos segundos.',
                'Eliminando definitivamente ' + cantidad + (cantidad === 1 ? ' registro' : ' registros')
            ) ?? null;

            try {
                ids === 'seleccion' ? await $wire.eliminarSeleccion() : await $wire.eliminar(ids);
            } finally {
                if (aviso) window.trackitToastTerminar?.(aviso);
            }
        },
    }"
    x-on:papelera-pedir-eliminar.window="pedirEliminar($event.detail.id)"
>
    {{-- ENCABEZADO --}}
    <section class="rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">
        <div class="flex flex-col gap-1">
            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Papelera</h2>
            <p class="text-sm text-[var(--theme-text-muted)]">
                Lo que se borra en el sistema llega aquí. Puedes restaurarlo durante
                {{ \App\Support\PapeleraEntidades::DIAS }} días; después se elimina definitivamente de forma automática.
            </p>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[1fr_260px_auto]">
            <div>
                <label for="papelera-buscar" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Buscar</label>
                <input
                    id="papelera-buscar"
                    type="search"
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Nombre del elemento o quién lo borró…"
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                >
            </div>

            <x-searchable-select
                label="Tipo"
                wire-model="tipo"
                :options="$tipos"
                placeholder="Todos los tipos"
                :show-all-on-open="true"
            />

            <div class="flex items-end">
                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="h-[42px] w-full rounded-md border border-[var(--theme-border-strong)] px-4 text-sm font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)] md:w-auto"
                >
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- ACCIONES SOBRE LA SELECCIÓN --}}
    @if (count($seleccionados) > 0)
        <div class="flex flex-col gap-3 rounded-md border border-[var(--theme-primary-border,var(--theme-border))] bg-[var(--theme-primary-soft-subtle,var(--theme-surface-soft))] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-[var(--theme-text-strong)]">
                <strong class="font-semibold">{{ count($seleccionados) }}</strong>
                {{ count($seleccionados) === 1 ? 'elemento seleccionado' : 'elementos seleccionados' }}
            </p>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="$set('seleccionados', [])"
                    class="h-9 rounded-md px-3 text-sm font-medium text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]"
                >
                    Quitar selección
                </button>

                <x-ui.button size="compact" variant="secondary" wire:click="restaurarSeleccion" target="restaurarSeleccion" loading-text="Restaurando…">
                    Restaurar
                </x-ui.button>

                <x-ui.button size="compact" variant="danger-soft" x-on:click="pedirEliminar('seleccion')">
                    Eliminar definitivamente
                </x-ui.button>
            </div>
        </div>
    @endif

    {{-- TABLA --}}
    <x-data-table
        :rows="$elementos"
        row-key="id_papelera"
        key-prefix="papelera"
        selectable
        selection-model="seleccionados"
        :selected-keys="$seleccionados"
        select-page-action="seleccionarPagina"

        :sort-field="$sortField"
        :sort-direction="$sort"
        sort-action="sortBy"
        per-page-model="perPage"
        sort-model="sort"
        refresh-action="$refresh"

        result-singular="elemento en la papelera"
        result-plural="elementos en la papelera"
        empty-title="La papelera está vacía"
        empty-message="{{ $total > 0 ? 'Ningún elemento coincide con la búsqueda.' : 'Cuando se borre algo en el sistema aparecerá aquí.' }}"
        empty-variant="{{ $total > 0 ? 'search' : 'empty' }}"
        min-width="1100px"

        :columns="[
            [
                'key' => 'etiqueta',
                'label' => 'Elemento',
                'width' => '300px',
                'sort' => 'etiqueta',
                'strong' => true,
                'mobile' => false,
            ],
            [
                'key' => 'tipo_nombre',
                'label' => 'Tipo',
                'width' => '150px',
                'sort' => 'tipo',
            ],
            [
                'key' => 'detalle',
                'label' => 'Incluye',
                'width' => '220px',
                'mobile' => 'filled',
            ],
            [
                'key' => 'eliminado_por_nombre',
                'label' => 'Eliminado por',
                'width' => '180px',
                'sort' => 'eliminado_por',
            ],
            [
                'key' => 'eliminado_en',
                'label' => 'Eliminado el',
                'width' => '150px',
                'sort' => 'eliminado',
                'value' => fn ($e) => \Illuminate\Support\Carbon::parse($e->eliminado_en)->format('d/m/Y H:i'),
            ],
            [
                'key' => 'dias_restantes',
                'label' => 'Se elimina',
                'width' => '130px',
                'sort' => 'expira',
                'value' => fn ($e) => (int) $e->dias_restantes <= 0
                    ? 'Hoy'
                    : ((int) $e->dias_restantes === 1 ? 'En 1 día' : 'En ' . (int) $e->dias_restantes . ' días'),
            ],
        ]"

        :mobile-title="fn ($e) => $e->etiqueta"
        :mobile-subtitle="fn ($e) => $e->tipo_nombre"

        :actions="fn ($e) => [
            'menu' => [
                ['label' => 'Restaurar', 'click' => 'restaurar(' . (int) $e->id_papelera . ')'],
                [
                    'label' => 'Eliminar definitivamente',
                    'js' => '$dispatch(\'papelera-pedir-eliminar\', { id: ' . (int) $e->id_papelera . ' })',
                    'danger' => true,
                ],
            ],
        ]"
    />

    {{-- CONFIRMAR ELIMINACIÓN DEFINITIVA --}}
    <div
        x-show="eliminar !== null"
        x-cloak
        x-transition.opacity.duration.150ms
        x-on:keydown.escape.window="cerrar()"
        x-on:click.self="cerrar()"
        class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 px-4 py-6"
        role="presentation"
    >
        <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="papelera-eliminar-titulo"
            class="w-full max-w-md rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5 shadow-xl"
        >
            <h2 id="papelera-eliminar-titulo" class="text-base font-semibold text-[var(--theme-text-strong)]">
                Eliminar definitivamente
            </h2>

            <p class="mt-3 text-sm text-[var(--theme-text)]">
                <span x-show="eliminar === 'seleccion'">¿Eliminar definitivamente los {{ count($seleccionados) }} elementos seleccionados?</span>
                <span x-show="eliminar !== 'seleccion'">¿Eliminar definitivamente este elemento?</span>
            </p>

            <p class="mt-2 text-xs text-[var(--theme-text-muted)]">
                Se borran también sus registros relacionados y archivos. Esta acción no se puede deshacer.
            </p>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-ui.button variant="secondary" x-on:click="cerrar()">Cancelar</x-ui.button>
                <x-ui.button variant="danger-soft" x-on:click="confirmar()" target="eliminar,eliminarSeleccion" loading-text="Eliminando…">
                    Eliminar definitivamente
                </x-ui.button>
            </div>
        </section>
    </div>
</div>
