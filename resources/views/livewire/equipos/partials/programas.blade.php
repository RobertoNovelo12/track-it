{{-- ============================================================
    PROGRAMAS DEL EQUIPO (alta y edición)
    ------------------------------------------------------------
    Aparece solo si el tipo elegido tiene «Incluye programas»
    (Administración › Equipos › Editar). En el alta vienen marcados
    los predeterminados del tipo.

    Necesita en el x-data del formulario: programas (ids elegidos),
    programasBuscar, tipoIncluyeProgramas, programaElegido(),
    alternarPrograma(), programasFiltrados(), marcarPredeterminados(),
    programasPredeterminados().
============================================================ --}}
<template x-if="tipoIncluyeProgramas">
    <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6" aria-labelledby="equipo-programas-titulo">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h2 id="equipo-programas-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)]">Programas</h2>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Software instalado en este equipo.
                    <span class="font-medium text-[var(--theme-text)]" x-text="programas.length + (programas.length === 1 ? ' seleccionado' : ' seleccionados')"></span>
                </p>
            </div>

            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center" x-show="(data.programas?.opciones ?? []).length > 0">
                <button
                    type="button"
                    x-show="programasPredeterminados().length > 0"
                    @click="marcarPredeterminados()"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-[var(--theme-border-strong)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                    title="Agrega los programas predeterminados de este tipo de equipo"
                >
                    Marcar predeterminados
                </button>

                <input
                    type="search"
                    x-model="programasBuscar"
                    placeholder="Buscar programa..."
                    aria-label="Buscar programa"
                    autocomplete="off"
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)] sm:w-64"
                >
            </div>
        </div>

        <div
            x-show="(data.programas?.opciones ?? []).length === 0"
            class="mt-4 rounded-lg border border-dashed border-[var(--theme-border-strong)] px-4 py-5 text-center text-sm text-[var(--theme-text-muted)]"
        >
            Todavía no hay programas registrados en Administración › Programas.
        </div>

        <div class="mt-4 grid max-h-[26rem] grid-cols-1 gap-2 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3" x-show="(data.programas?.opciones ?? []).length > 0">
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
                        <span class="block truncate text-xs text-[var(--theme-text-muted)]" x-show="opcion.detalle || opcion.categoria" x-text="[opcion.detalle, opcion.categoria].filter(Boolean).join(' · ')"></span>
                        <span class="mt-0.5 inline-block rounded-md bg-[var(--theme-surface-soft)] px-1.5 py-0.5 text-[10px] text-[var(--theme-text-muted)]" x-show="opcion.activo === false">Inactivo</span>
                    </span>
                </label>
            </template>

            <p x-show="programasBuscar.trim() !== '' && programasFiltrados().length === 0" class="text-sm text-[var(--theme-text-muted)] md:col-span-2 xl:col-span-3">
                Ningún programa coincide con la búsqueda.
            </p>
        </div>

        <p x-show="errorFor('programas')" x-text="errorFor('programas')" class="mt-2 text-xs text-[var(--theme-danger)]"></p>
    </section>
</template>
