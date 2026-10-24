{{-- ============================================================
    PROGRAMAS DEL TIPO DE EQUIPO
    ------------------------------------------------------------
    «Incluye programas»: los equipos de este tipo muestran la
    sección Programas al darlos de alta o editarlos. Los marcados
    aquí vienen seleccionados por defecto en cada equipo nuevo.
    Se guarda con «Guardar configuración» (alpine: programas).
============================================================ --}}
<section
    x-show="programas.disponible"
    x-cloak
    class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4"
    aria-labelledby="tipo-programas-titulo"
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h2 id="tipo-programas-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Programas
            </h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Software de los equipos de este tipo.
            </p>
        </div>

        <a
            :href="programas.url_catalogo"
            wire:navigate.hover
            class="shrink-0 text-xs font-medium text-[var(--theme-primary)] hover:underline"
        >
            Administrar programas
        </a>
    </div>

    {{-- Casilla principal --}}
    <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors"
        :class="programas.incluye
            ? 'border-[var(--theme-primary-border-strong)] bg-[var(--theme-primary-soft-subtle)]'
            : 'border-[var(--theme-border)] hover:border-[var(--theme-border-strong)]'"
    >
        <input
            type="checkbox"
            :checked="programas.incluye"
            @change="alternarIncluyeProgramas()"
            class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
        >

        <span>
            <span class="block text-sm font-semibold text-[var(--theme-text-strong)]">
                Incluye programas
            </span>
            <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                Al dar de alta o editar un equipo de este tipo aparecerá la sección para elegir sus programas.
            </span>
        </span>
    </label>

    {{-- Predeterminados --}}
    <div x-show="programas.incluye" x-cloak class="mt-5">
        <div class="space-y-3">
            <div>
                <h3 class="text-sm font-medium text-[var(--theme-text)]">Programas predeterminados</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Vendrán marcados en cada equipo nuevo de este tipo; se pueden quitar o agregar otros en el equipo.
                    <span class="font-medium text-[var(--theme-text)]" x-text="programas.seleccion.length + (programas.seleccion.length === 1 ? ' seleccionado' : ' seleccionados')"></span>
                </p>
            </div>

            <div class="w-full" x-show="programas.opciones.length > 0">
                <input
                    type="search"
                    x-model="programasBuscar"
                    placeholder="Buscar programa..."
                    aria-label="Buscar programa"
                    autocomplete="off"
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                >
            </div>
        </div>

        {{-- Sin programas en el catálogo --}}
        <div
            x-show="programas.opciones.length === 0"
            class="mt-4 rounded-lg border border-dashed border-[var(--theme-border-strong)] px-4 py-5 text-center text-sm text-[var(--theme-text-muted)]"
        >
            Todavía no hay programas registrados.
            <a :href="programas.url_nuevo" wire:navigate.hover class="font-medium text-[var(--theme-primary)] hover:underline">Agregar el primero</a>
        </div>

        <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-1" x-show="programas.opciones.length > 0">
            <template x-for="opcion in programasFiltrados()" :key="opcion.value">
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-lg border px-3 py-2.5 transition-colors"
                    :class="programaElegido(opcion.value)
                        ? 'border-[var(--theme-primary-border-strong)] bg-[var(--theme-primary-soft-subtle)]'
                        : 'border-[var(--theme-border)] hover:border-[var(--theme-border-strong)]'"
                >
                    <input
                        type="checkbox"
                        :checked="programaElegido(opcion.value)"
                        @change="alternarPrograma(opcion.value)"
                        class="mt-0.5 rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                    >

                    <span class="min-w-0">
                        <span class="block truncate text-sm text-[var(--theme-text)]" x-text="opcion.label"></span>
                        <span class="block truncate text-xs text-[var(--theme-text-muted)]" x-show="opcion.detalle || opcion.categoria"
                            x-text="[opcion.detalle, opcion.categoria].filter(Boolean).join(' · ')"></span>
                        <span class="mt-0.5 inline-block rounded-md bg-[var(--theme-surface-soft)] px-1.5 py-0.5 text-[10px] text-[var(--theme-text-muted)]" x-show="! opcion.activo">Inactivo</span>
                    </span>
                </label>
            </template>

            <p
                x-show="programasBuscar.trim() !== '' && programasFiltrados().length === 0"
                class="text-sm text-[var(--theme-text-muted)]"
            >
                Ningún programa coincide con la búsqueda.
            </p>
        </div>
    </div>

    <p class="mt-4 text-xs text-[var(--theme-text-muted)]" x-show="! programas.incluye && programas.seleccion.length > 0">
        Los programas predeterminados se conservan por si vuelves a activar la opción.
    </p>
</section>
