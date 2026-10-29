{{-- ============================================================
    PAPELERA (App\Livewire\PapeleraIndex)
============================================================ --}}
<div
    class="space-y-5"
    x-data="{
        /*
         * Selección en el navegador (como Inventario): marcar y desmarcar
         * no hace peticiones ni vuelve a dibujar la tabla.
         */
        seleccion: [],
        estaSeleccionado(id) { return this.seleccion.includes(String(id)); },
        alternarSeleccion(id, marcado) {
            id = String(id);
            this.seleccion = this.seleccion.filter(x => x !== id);
            if (marcado) this.seleccion.push(id);
        },
        paginaSeleccionada(ids) { return ids.length > 0 && ids.every(id => this.seleccion.includes(String(id))); },
        alternarPagina(ids, marcado) { ids.map(String).forEach(id => this.alternarSeleccion(id, marcado)); },
        limpiarSeleccion() { this.seleccion = []; },

        async restaurarSeleccion() {
            const ids = this.seleccion.map(Number);
            if (ids.length === 0) return;

            const aviso = window.trackitToast?.(
                'loading',
                'Esto puede tardar unos segundos.',
                'Restaurando ' + ids.length + (ids.length === 1 ? ' registro' : ' registros')
            ) ?? null;

            try {
                await $wire.restaurarSeleccion(ids);
                this.limpiarSeleccion();
            } finally {
                if (aviso) window.trackitToastTerminar?.(aviso);
            }
        },

        eliminar: null,
        pedirEliminar(ids) { this.eliminar = ids; },
        cerrar() { this.eliminar = null; },
        async confirmar() {
            const ids = this.eliminar;
            this.eliminar = null;
            if (! ids) return;

            // Aviso con barra de avance mientras se elimina.
            const seleccion = this.seleccion.map(Number);
            const cantidad = ids === 'seleccion' ? seleccion.length : 1;
            const aviso = window.trackitToast?.(
                'loading',
                'Esto puede tardar unos segundos.',
                'Eliminando definitivamente ' + cantidad + (cantidad === 1 ? ' registro' : ' registros')
            ) ?? null;

            try {
                ids === 'seleccion' ? await $wire.eliminarSeleccion(seleccion) : await $wire.eliminar(ids);

                if (ids === 'seleccion') {
                    this.limpiarSeleccion();
                } else {
                    this.alternarSeleccion(ids, false);
                }
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

    {{-- TABLA + ACCIONES SOBRE LA SELECCIÓN
         La barra se pone encima de la barra de resultados (mismo alto). --}}
    <div class="relative">
    <div
        x-data="{
            alto: null,
            medir() {
                const barra = this.$el.parentElement?.querySelector('[data-tabla-toolbar]');
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
                x-on:click="restaurarSeleccion()"
                class="inline-flex h-9 shrink-0 items-center justify-center whitespace-nowrap rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
            >
                Restaurar
            </button>

            <button
                type="button"
                x-on:click="pedirEliminar('seleccion')"
                class="inline-flex h-9 shrink-0 items-center justify-center whitespace-nowrap rounded-md bg-[var(--theme-danger-soft)] px-3 text-sm font-medium text-[var(--theme-danger)] transition-opacity hover:opacity-80"
            >
                <span class="hidden sm:inline">Eliminar definitivamente</span>
                <span class="sm:hidden">Eliminar</span>
            </button>
        </div>
    </div>

    {{-- TABLA --}}
    <x-data-table
        :rows="$elementos"
        row-key="id_papelera"
        key-prefix="papelera"
        selectable
        selection-alpine

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
    </div>

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
                <span x-show="eliminar === 'seleccion'">¿Eliminar definitivamente los <span x-text="seleccion.length"></span> elementos seleccionados?</span>
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
