{{-- ============================================================
    IMPORTAR EQUIPOS DESDE CSV (App\Livewire\EquiposImportar)
============================================================ --}}
@php
    $tarjeta = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    $listas = $cuentas['listas'];
@endphp

<div
    class="space-y-5"
    data-listas="{{ $listas }}"
    x-data="{
        avance: 0,
        corriendo: false,
        async importar() {
            if (this.corriendo) return;
            this.corriendo = true;
            const total = Number(this.$root.dataset.listas || 0);
            let aviso = window.trackitToast?.('loading', 'Guardando los equipos del archivo…', 'Importando') ?? null;

            try {
                let resultado = { pendientes: total };

                while (resultado.pendientes > 0) {
                    resultado = await $wire.importarLote();
                    this.avance = total > 0 ? Math.round(((total - resultado.pendientes) / total) * 100) : 100;
                }

                if (aviso) window.trackitToastActualizar?.(aviso, 'success', resultado.importadas + ' equipos importados.', 'Importación terminada');
            } catch (error) {
                if (aviso) window.trackitToastActualizar?.(aviso, 'error', window.trackitMensajeError(error, 'importar los equipos'), 'Error al importar');
            } finally {
                this.corriendo = false;
            }
        },
    }"
>
    {{-- 1. PLANTILLA Y ARCHIVO --}}
    <section class="{{ $tarjeta }}">
        <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Importar equipos desde CSV</h2>
        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
            Descarga la plantilla del tipo de equipo, llénala y súbela. La plantilla de <strong>Excel</strong> trae las columnas anchas y listas para elegir las opciones;
            también se acepta un <strong>CSV</strong> (por ejemplo, el que exportas desde Inventario).
        </p>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Plantillas --}}
            <div x-data="{ tipo: '' }">
                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Plantilla por tipo de equipo</label>
                <div class="flex gap-2">
                    <select x-model="tipo" class="h-10 w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]">
                        <option value="">Elige un tipo…</option>
                        @foreach ($tiposPlantilla as $tipo)
                            <option value="{{ route('catalogos.tipos-equipo.plantilla-csv', $tipo['id']) }}">{{ $tipo['nombre'] }}</option>
                        @endforeach
                    </select>
                    {{-- Excel: columnas anchas y listas desplegables. CSV: texto plano. --}}
                    <button
                        type="button"
                        x-bind:disabled="! tipo"
                        x-on:click="const url = tipo.replace('plantilla-csv', 'plantilla-xlsx'); window.trackitDescargar ? window.trackitDescargar(url) : (window.location.href = url)"
                        class="h-10 shrink-0 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-50"
                    >Excel</button>
                    <button
                        type="button"
                        x-bind:disabled="! tipo"
                        x-on:click="window.trackitDescargar ? window.trackitDescargar(tipo) : (window.location.href = tipo)"
                        class="h-10 shrink-0 rounded-md border border-[var(--theme-border-strong)] px-4 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:cursor-not-allowed disabled:opacity-50"
                    >CSV</button>
                </div>
            </div>

            {{-- Archivo --}}
            <div>
                <label for="importar-archivo" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Archivo (Excel o CSV) *</label>
                <input
                    id="importar-archivo"
                    type="file"
                    accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    wire:model="archivo"
                    x-bind:disabled="corriendo"
                    class="block w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-sm text-[var(--theme-text)] file:mr-3 file:h-10 file:border-0 file:bg-[var(--theme-surface-soft)] file:px-4 file:text-sm file:font-medium file:text-[var(--theme-text)]"
                >
                <p wire:loading wire:target="archivo" class="mt-1 text-xs text-[var(--theme-text-muted)]">Leyendo el archivo…</p>
                @error('archivo') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>
        </div>

        <ul class="mt-4 list-disc space-y-1 pl-5 text-xs text-[var(--theme-text-muted)]">
            <li>La primera fila son los encabezados; no cambies sus nombres. Las columnas con * son obligatorias.</li>
            <li>Escribe las listas con su texto, como aparecen en el sistema (Estado «Disponible», Marca «Dell», Hotel…).</li>
            <li>Fechas en dd/mm/aaaa. Sí/No como «Sí» o «No». El código de inventario se asigna solo.</li>
            <li>Hasta 500 equipos por archivo. Las filas con errores no se importan; corrígelas y vuelve a subir solo esas.</li>
        </ul>
    </section>

    {{-- 2. VISTA PREVIA --}}
    @if ($cuentas['total'] > 0)
        <section class="{{ $tarjeta }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">{{ $nombreArchivo }}</h2>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        {{ $cuentas['total'] }} filas ·
                        <span class="text-[var(--theme-success)]">{{ $cuentas['listas'] + $cuentas['importadas'] }} sin errores</span> ·
                        <span class="{{ $cuentas['errores'] ? 'text-[var(--theme-danger)]' : '' }}">{{ $cuentas['errores'] }} con errores</span>
                        @if ($cuentas['importadas']) · {{ $cuentas['importadas'] }} importadas @endif
                    </p>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="descartar"
                        x-bind:disabled="corriendo"
                        class="h-10 rounded-md border border-[var(--theme-border-strong)] px-4 text-sm font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)] disabled:opacity-50"
                    >{{ $terminado ? 'Importar otro archivo' : 'Descartar' }}</button>

                    @if (! $terminado && $listas > 0)
                        <button
                            type="button"
                            x-on:click="importar()"
                            x-bind:disabled="corriendo"
                            class="h-10 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:opacity-70"
                            x-text="corriendo ? 'Importando… ' + avance + '%' : @js('Importar '.$listas.($listas === 1 ? ' equipo' : ' equipos'))"
                        >Importar</button>
                    @endif

                    @if ($terminado)
                        <a href="{{ route('equipos.index') }}" wire:navigate class="inline-flex h-10 items-center rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)]">Ir a Inventario</a>
                    @endif
                </div>
            </div>

            {{-- Barra de avance --}}
            <div x-show="corriendo" x-cloak class="mt-4 h-2 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:style="{ width: avance + '%' }"></div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-lg border border-[var(--theme-border)]">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)]">
                        <tr>
                            <th class="px-3 py-2 font-medium">Fila</th>
                            <th class="px-3 py-2 font-medium">Tipo</th>
                            <th class="px-3 py-2 font-medium">Nombre</th>
                            <th class="px-3 py-2 font-medium">Número de serie</th>
                            <th class="px-3 py-2 font-medium">Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @foreach ($filas as $fila)
                            <tr wire:key="importar-fila-{{ $fila['fila'] }}" class="align-top">
                                <td class="px-3 py-2 text-[var(--theme-text-muted)]">{{ $fila['fila'] }}</td>
                                <td class="px-3 py-2 text-[var(--theme-text)]">{{ $fila['tipo'] ?: '—' }}</td>
                                <td class="px-3 py-2 font-medium text-[var(--theme-text-strong)]">{{ $fila['nombre'] ?: '—' }}</td>
                                <td class="px-3 py-2 text-[var(--theme-text)]">{{ $fila['serie'] ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    @switch($fila['estado'])
                                        @case('importada')
                                            <span class="inline-flex rounded-md bg-[var(--theme-success-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-success)]">Importada · {{ $fila['codigo'] }}</span>
                                            @break
                                        @case('lista')
                                            <span class="inline-flex rounded-md bg-[var(--theme-primary-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-primary)]">Lista para importar</span>
                                            @break
                                        @default
                                            <span class="inline-flex rounded-md bg-[var(--theme-danger-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-danger)]">{{ $fila['estado'] === 'fallo' ? 'No se guardó' : 'Con errores' }}</span>
                                            <ul class="mt-1 space-y-0.5 text-xs text-[var(--theme-danger)]">
                                                @foreach ($fila['mensajes'] as $mensaje)
                                                    <li>{{ $mensaje }}</li>
                                                @endforeach
                                            </ul>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
