{{-- ============================================================
    MODALES DE UBICACIONES (Administración)
    ------------------------------------------------------------
    Estado en Alpine (ubicacionesAdmin en ⚡ubicaciones): abrir y
    cerrar no consulta al servidor; solo «Guardar».
============================================================ --}}
@php
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';
    $etiqueta = 'mb-1.5 block text-xs text-[var(--theme-text-muted)]';
@endphp

{{-- ============================================================
    CREAR / EDITAR UBICACIÓN
============================================================ --}}
<div
    x-show="visible"
    x-cloak
    x-on:keydown.escape.window="visible && cerrar()"
    class="fixed inset-0 z-[140] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ubicacion-modal-titulo"
>
    <button type="button" x-on:click="cerrar()" class="absolute inset-0 h-full w-full bg-[var(--theme-overlay)]" aria-label="Cerrar"></button>

    <form
        x-on:submit.prevent="guardar()"
        class="relative z-10 flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl"
    >
        <div class="border-b border-[var(--theme-border)] px-5 py-4">
            <h2 id="ubicacion-modal-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)]" x-text="form.id ? 'Editar ubicación' : 'Nueva ubicación'"></h2>
            <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                Lugar del {{ $unidadMinuscula }} donde hay equipos (p. ej. Cocina caliente, Cuarto de comunicaciones, Kids Club).
            </p>
        </div>

        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="ubicacion-nombre" class="{{ $etiqueta }}">Nombre *</label>
                    <input id="ubicacion-nombre" type="text" x-model="form.nombre" maxlength="150" class="{{ $campo }}" placeholder="Ej. Cocina caliente">
                    <p x-show="error('nombre')" x-text="error('nombre')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
                </div>

                <div>
                    <label for="ubicacion-tipo" class="{{ $etiqueta }}">Tipo</label>
                    <select id="ubicacion-tipo" x-model="form.tipo" class="{{ $campo }}">
                        <option value="">Sin tipo</option>
                        <template x-for="tipo in tiposActivos" :key="tipo.id">
                            <option :value="String(tipo.id)" x-text="tipo.nombre + (tipo.activo ? '' : ' (inactivo)')" :selected="String(tipo.id) === String(form.tipo)"></option>
                        </template>
                    </select>
                    <p x-show="error('tipo')" x-text="error('tipo')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
                </div>

                <div>
                    <label for="ubicacion-hotel" class="{{ $etiqueta }}">{{ ucfirst($unidadMinuscula) }}</label>
                    <select id="ubicacion-hotel" x-model="form.hotel" class="{{ $campo }}">
                        <option value="">Cualquier {{ $unidadMinuscula }}</option>
                        <template x-for="hotel in hoteles" :key="hotel.value">
                            <option :value="hotel.value" x-text="hotel.label" :selected="hotel.value === String(form.hotel)"></option>
                        </template>
                    </select>
                    <p x-show="error('hotel')" x-text="error('hotel')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
                </div>
            </div>

            {{-- Departamentos que trabajan en la ubicación --}}
            <div>
                <div class="mb-1.5 flex items-end justify-between gap-3">
                    <span class="text-xs text-[var(--theme-text-muted)]">Departamentos que la usan</span>
                    <span class="text-[11px] text-[var(--theme-text-muted)]" x-text="form.departamentos.length ? form.departamentos.length + ' seleccionado(s)' : 'Ninguno = todos los del {{ $unidadMinuscula }}'"></span>
                </div>

                <div class="max-h-48 overflow-y-auto rounded-md border border-[var(--theme-border)] p-2">
                    <template x-if="departamentosDisponibles.length === 0">
                        <p class="px-1 py-2 text-xs text-[var(--theme-text-muted)]">No hay departamentos activos para este {{ $unidadMinuscula }}.</p>
                    </template>

                    <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                        <template x-for="departamento in departamentosDisponibles" :key="departamento.id">
                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                                <input
                                    type="checkbox"
                                    class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                                    :checked="form.departamentos.includes(departamento.id)"
                                    x-on:change="alternarDepartamento(departamento.id)"
                                >
                                <span class="truncate" x-text="departamento.nombre"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <p x-show="error('departamentos')" x-text="error('departamentos')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div>
                <label for="ubicacion-descripcion" class="{{ $etiqueta }}">Descripción (opcional)</label>
                <textarea id="ubicacion-descripcion" rows="2" x-model="form.descripcion" maxlength="500" class="{{ $campo }}" placeholder="Referencia para encontrarla, piso, edificio…"></textarea>
                <p x-show="error('descripcion')" x-text="error('descripcion')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                <input type="checkbox" x-model="form.activo" class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                Activa (aparece al asignar equipos)
            </label>
        </div>

        <div class="flex justify-end gap-2 border-t border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-5 py-4">
            <button type="button" x-on:click="cerrar()" class="h-10 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-sm font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                Cancelar
            </button>

            <button type="submit" x-bind:disabled="guardando" class="inline-flex h-10 items-center gap-2 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-80">
                <span x-show="guardando"><x-ui.spinner size="sm" /></span>
                <span x-text="guardando ? 'Guardando…' : (form.id ? 'Guardar cambios' : 'Agregar ubicación')"></span>
            </button>
        </div>
    </form>
</div>

{{-- ============================================================
    TIPOS DE UBICACIÓN (lista editable)
============================================================ --}}
<div
    x-show="tiposVisible"
    x-cloak
    x-on:keydown.escape.window="tiposVisible && (tiposVisible = false)"
    class="fixed inset-0 z-[140] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tipos-ubicacion-titulo"
>
    <button type="button" x-on:click="tiposVisible = false" class="absolute inset-0 h-full w-full bg-[var(--theme-overlay)]" aria-label="Cerrar"></button>

    <div class="relative z-10 flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl">
        <div class="border-b border-[var(--theme-border)] px-5 py-4">
            <h2 id="tipos-ubicacion-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)]">Tipos de ubicación</h2>
            <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                Un tipo inactivo ya no se ofrece al crear ubicaciones; las que lo tienen lo conservan.
            </p>
        </div>

        {{-- Agregar --}}
        <form x-on:submit.prevent="nuevoTipo.trim() !== '' && guardarTipo(null, nuevoTipo)" class="flex gap-2 border-b border-[var(--theme-border)] px-5 py-3">
            <input id="tipo-ubicacion-nuevo" type="text" x-model="nuevoTipo" maxlength="100" placeholder="Nuevo tipo, p. ej. Spa" class="{{ $campo }}">
            <button type="submit" x-bind:disabled="trabajandoTipo || nuevoTipo.trim() === ''" class="h-10 shrink-0 rounded-md bg-[var(--theme-primary)] px-3 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:opacity-60">
                Agregar
            </button>
        </form>

        <p x-show="errorTipo" x-text="errorTipo" class="px-5 pt-2 text-xs text-[var(--theme-danger)]"></p>

        <ul class="min-h-0 flex-1 divide-y divide-[var(--theme-border)] overflow-y-auto">
            <template x-for="tipo in tipos" :key="tipo.id">
                <li class="flex items-center gap-2 px-5 py-2.5">
                    <template x-if="editandoTipo === tipo.id">
                        <form x-on:submit.prevent="guardarTipo(tipo.id, nombreTipo)" class="flex min-w-0 flex-1 gap-2">
                            <input type="text" x-model="nombreTipo" maxlength="100" class="{{ $campo }} h-9 py-1.5" x-init="$nextTick(() => $el.focus())">
                            <button type="submit" x-bind:disabled="trabajandoTipo" class="h-9 shrink-0 rounded-md bg-[var(--theme-primary)] px-3 text-xs font-medium text-white disabled:opacity-60">Guardar</button>
                            <button type="button" x-on:click="editandoTipo = null" class="h-9 shrink-0 rounded-md px-2 text-xs text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]">Cancelar</button>
                        </form>
                    </template>

                    <template x-if="editandoTipo !== tipo.id">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <span class="min-w-0 flex-1 truncate text-sm" :class="tipo.activo ? 'text-[var(--theme-text)]' : 'text-[var(--theme-text-muted)] line-through'" x-text="tipo.nombre"></span>

                            <button type="button" x-on:click="editarTipo(tipo)" class="shrink-0 rounded px-2 py-1 text-xs text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]">Renombrar</button>

                            <button
                                type="button"
                                x-on:click="alternarTipo(tipo.id)"
                                x-bind:disabled="trabajandoTipo"
                                class="shrink-0 rounded px-2 py-1 text-xs hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"
                                :class="tipo.activo ? 'text-[var(--theme-danger)]' : 'text-[var(--theme-primary)]'"
                                x-text="tipo.activo ? 'Desactivar' : 'Activar'"
                            ></button>
                        </div>
                    </template>
                </li>
            </template>
        </ul>

        <div class="flex justify-end border-t border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-5 py-3">
            <button type="button" x-on:click="tiposVisible = false" class="h-9 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                Listo
            </button>
        </div>
    </div>
</div>
