{{-- ============================================================
    BARRA DE SELECCIÓN (inventario)
    ------------------------------------------------------------
    Solo aparece cuando hay equipos marcados (en cualquier página).
    Escritorio: encima de la tabla. Móvil: fija abajo, sobre el menú.
    Solo dos botones: «Asignar» y «Acciones» (exportar, baja, papelera).
    El estado y las acciones están en equipos/estado-alpine.
============================================================ --}}
@php
    $puedeDarDeBaja = \Illuminate\Support\Facades\Gate::allows('gestionar');
    $puedePapelera = \App\Support\PapeleraEntidades::existe('equipo')
        && \Illuminate\Support\Facades\Gate::allows(\App\Support\PapeleraEntidades::de('equipo')['permiso']);

    $botonBarra = 'inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]';
    $itemMenu = 'block w-full px-4 py-2.5 text-left text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]';
    $menuFlotante = 'absolute z-30 w-60 overflow-hidden rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] text-left shadow-lg shadow-[var(--theme-shadow)]';
@endphp

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
    {{-- Sin transición de salida: al quitar el último se oculta al
         instante (si no, se alcanzaba a ver «0 equipos seleccionados»). --}}
    x-on:click.outside="menuSeleccion = null"
    x-on:keydown.escape.window="menuSeleccion = null"
    @trackit-cerrar-menus.window="menuSeleccion = null"
    role="region"
    aria-label="Acciones para los equipos seleccionados"
    class="fixed inset-x-3 bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] z-40 lg:absolute lg:inset-x-px lg:top-px lg:bottom-auto lg:z-30 lg:rounded-t-lg lg:bg-[var(--theme-surface)]"
>
    <div class="flex items-center gap-2 rounded-xl border border-[var(--theme-primary-border,var(--theme-border))] bg-[var(--theme-surface)] px-2.5 py-2 shadow-lg shadow-[var(--theme-shadow)] lg:h-full lg:rounded-none lg:rounded-t-lg lg:border-0 lg:border-b lg:border-[var(--theme-border)] lg:bg-[var(--theme-primary-soft-subtle,var(--theme-surface-soft))] lg:px-4 lg:shadow-none">

        {{-- QUITAR SELECCIÓN --}}
        <button
            type="button"
            x-on:click="limpiarSeleccion()"
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text-strong)]"
            title="Quitar selección"
            aria-label="Quitar selección"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        {{-- CONTADOR --}}
        <p class="min-w-0 flex-1 truncate text-sm text-[var(--theme-text)]" aria-live="polite">
            <strong class="font-semibold text-[var(--theme-text-strong)]" x-text="seleccion.length"></strong>
            <span x-text="seleccion.length === 1 ? 'equipo seleccionado' : 'equipos seleccionados'"></span>
        </p>

        {{-- ====================================================
            ASIGNAR (principal) + ACCIONES (el resto, en un menú)
            Escritorio: el menú abre hacia abajo. Móvil: hacia arriba,
            porque la barra está pegada abajo.
        ===================================================== --}}
        @if ($puedeDarDeBaja)
            <button
                type="button"
                x-on:click="asignarSeleccion()"
                :class="seleccionBajas.length > 0 && 'opacity-50'"
                :title="seleccionBajas.length > 0 ? 'La selección incluye equipos dados de baja' : 'Asignar a una persona o a un departamento'"
                class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-md bg-[var(--theme-primary)] px-3 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>
                <span>Asignar<span class="hidden sm:inline" x-text="seleccion.length > 1 ? ' (' + seleccion.length + ')' : ''"></span></span>
            </button>
        @endif

        <div class="relative shrink-0">
            <button
                type="button"
                x-on:click="abrirMenuSeleccion(menuSeleccion === 'baja' ? 'baja' : 'acciones')"
                :aria-expanded="(menuSeleccion === 'acciones' || menuSeleccion === 'baja').toString()"
                aria-haspopup="menu"
                class="{{ $botonBarra }}"
            >
                Acciones
                <svg class="h-3.5 w-3.5 transition-transform" :class="(menuSeleccion === 'acciones' || menuSeleccion === 'baja') && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            {{-- Menú principal --}}
            <div x-show="menuSeleccion === 'acciones'" x-cloak x-transition.opacity.duration.100ms role="menu" class="{{ $menuFlotante }} right-0 bottom-full mb-2 lg:bottom-auto lg:top-full lg:mb-0 lg:mt-2">
                <button type="button" role="menuitem" x-on:click="exportarSeleccion('pdf')" :disabled="exportando" class="{{ $itemMenu }}">Exportar a PDF</button>
                <button type="button" role="menuitem" x-on:click="exportarSeleccion('xlsx')" :disabled="exportando" class="{{ $itemMenu }}">Exportar a Excel</button>

                @if ($puedeDarDeBaja)
                    <button type="button" role="menuitem" x-on:click="darDeBajaSeleccion()" class="{{ $itemMenu }} border-t border-[var(--theme-border)]" :class="seleccionBajas.length > 0 && 'opacity-50'">
                        <span x-text="seleccion.length > 1 ? 'Dar de baja (' + seleccion.length + ')…' : 'Dar de baja'">Dar de baja</span>
                    </button>
                @endif

                @if ($puedePapelera)
                    <button type="button" role="menuitem" x-on:click="moverSeleccionAPapelera()" class="block w-full border-t border-[var(--theme-border)] px-4 py-2.5 text-left text-sm font-medium text-[var(--theme-danger)] transition-colors hover:bg-[var(--theme-danger-soft)]">
                        Mover a la papelera
                    </button>
                @endif
            </div>

            {{-- Baja de varios: conjunta o uno por uno --}}
            <div x-show="menuSeleccion === 'baja'" x-cloak x-transition.opacity.duration.100ms role="menu" class="{{ $menuFlotante }} right-0 bottom-full mb-2 lg:bottom-auto lg:top-full lg:mb-0 lg:mt-2">
                <button type="button" role="menuitem" x-on:click="darDeBajaSeleccion('conjunta')" class="{{ $itemMenu }}">
                    Dar de baja a todos
                    <span class="block text-xs text-[var(--theme-text-muted)]">Un solo formulario y una acta</span>
                </button>
                <button type="button" role="menuitem" x-on:click="darDeBajaSeleccion('lote')" class="{{ $itemMenu }}">
                    Baja uno por uno
                    <span class="block text-xs text-[var(--theme-text-muted)]">Pasa al siguiente al confirmar</span>
                </button>
            </div>
        </div>
    </div>
</div>
