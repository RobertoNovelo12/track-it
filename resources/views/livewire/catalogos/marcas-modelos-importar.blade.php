{{-- ============================================================
    IMPORTAR MARCAS Y MODELOS (App\Livewire\Catalogos\MarcasModelosImportar)
    Mismo diseño que Inventario › Importar.
============================================================ --}}
@php
    $tarjeta = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    $columnasFila = 'lg:grid-cols-[150px_110px_minmax(0,1fr)_minmax(0,1.6fr)]';

    $insignia = fn (string $estado) => match ($estado) {
        'nueva' => ['Se creará', 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'],
        'actualizar' => ['Se actualizará', 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]'],
        'sin_cambios' => ['Sin cambios', 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]'],
        'creada' => ['Creada', 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'],
        'actualizada' => ['Actualizada', 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'],
        'fallo' => ['No se guardó', 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'],
        default => ['Con errores', 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'],
    };
@endphp

<div
    class="space-y-5"
    data-listas-sin="{{ $modos['sin']['listas'] }}"
    data-listas-con="{{ $modos['con']['listas'] }}"
    x-data="{
        actualizar: @js($actualizarExistentes),
        corriendo: false,
        subiendo: false,
        progreso: 0,
        encima: false,
        hechos: 0,
        totalImportar: 0,

        subir(archivo) {
            if (! archivo || this.subiendo || this.corriendo) return;

            if (! /\.(xlsx|csv)$/i.test(archivo.name)) {
                window.trackitToast?.('error', 'Solo se aceptan archivos Excel (.xlsx) o CSV.', 'Archivo no válido');
                return;
            }

            this.subiendo = true;
            this.progreso = 0;

            $wire.upload(
                'archivo',
                archivo,
                () => { this.subiendo = false; },
                () => { this.subiendo = false; window.trackitToast?.('error', 'No se pudo subir el archivo (máximo 4 MB).', 'Archivo no válido'); },
                (evento) => { this.progreso = evento.detail.progress; }
            );
        },

        async importar() {
            if (this.corriendo) return;

            const total = Number((this.actualizar ? this.$root.dataset.listasCon : this.$root.dataset.listasSin) || 0);
            if (total === 0) return;

            this.corriendo = true;
            this.hechos = 0;
            this.totalImportar = total;
            let aviso = window.trackitToast?.('loading', 'Guardando marcas y modelos…', 'Importando') ?? null;

            try {
                let r = { pendientes: total };

                while (r.pendientes > 0) {
                    r = await $wire.importarLote(20, this.actualizar);
                    this.hechos = total - r.pendientes;
                    if (aviso) window.trackitToastActualizar?.(aviso, 'loading', this.hechos + ' de ' + total + ' guardados…', 'Importando');
                }

                const partes = [];
                if (r.creadas) partes.push(r.creadas + (r.creadas === 1 ? ' creado' : ' creados'));
                if (r.actualizadas) partes.push(r.actualizadas + (r.actualizadas === 1 ? ' actualizado' : ' actualizados'));
                if (aviso) window.trackitToastActualizar?.(aviso, 'success', (partes.join(' y ') || 'Sin cambios') + '.', 'Importación terminada');
            } catch (error) {
                if (aviso) window.trackitToastActualizar?.(aviso, 'error', window.trackitMensajeError(error, 'importar'), 'Error al importar');
            } finally {
                this.corriendo = false;
            }
        },
    }"
>
    {{-- ========================================================
        1. ARCHIVO
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Archivo</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Plantilla de marcas y modelos (Excel con dos hojas o CSV) o un archivo exportado desde esta misma pantalla.
            </p>
        </div>

        <label
            for="mm-importar-archivo"
            x-on:dragover.prevent="encima = true"
            x-on:dragleave.prevent="encima = false"
            x-on:drop.prevent="encima = false; subir($event.dataTransfer.files?.[0])"
            x-bind:class="encima ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]' : 'border-[var(--theme-border-strong)] hover:border-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft-subtle)]'"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition-colors"
        >
            <span x-show="! subiendo" class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M5 21h14"/></svg>
            </span>

            <div x-show="subiendo" x-cloak class="w-full max-w-xs">
                <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:class="progreso >= 100 && 'animate-pulse'" x-bind:style="{ width: Math.max(4, progreso) + '%' }"></div>
                </div>
            </div>

            <span class="text-sm font-medium text-[var(--theme-text-strong)]" x-text="subiendo ? (progreso < 100 ? 'Subiendo el archivo… ' + progreso + '%' : 'Revisando las filas del archivo…') : 'Suelta tu Excel o CSV aquí, o haz clic para elegirlo'">Suelta tu Excel o CSV aquí, o haz clic para elegirlo</span>
            <span class="text-xs text-[var(--theme-text-muted)]">.xlsx o .csv · hasta 4 MB y 1,000 filas</span>

            <input
                id="mm-importar-archivo"
                type="file"
                class="sr-only"
                accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                x-on:change="subir($event.target.files?.[0]); $event.target.value = ''"
                x-bind:disabled="corriendo || subiendo"
            >
        </label>

        @error('archivo') <p class="mt-2 text-center text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

        <div class="mt-4 grid grid-cols-1 gap-2 text-xs text-[var(--theme-text-muted)] sm:grid-cols-2">
            @foreach ([
                'En Excel usa las hojas «Marcas» y «Modelos» (puedes llenar solo una). En CSV, una tabla de marcas o una de modelos.',
                'Las marcas se guardan primero: un modelo puede usar una marca nueva de la hoja «Marcas».',
                'Con «Código» (MAR-… / MOD-…) se actualiza ese registro; sin código se reconoce por su nombre.',
                'Al actualizar, una celda vacía conserva el dato actual. Estado: «Activo» o «Inactivo».',
            ] as $consejo)
                <p class="flex items-start gap-2 rounded-lg bg-[var(--theme-surface-soft)] px-3 py-2">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ $consejo }}</span>
                </p>
            @endforeach
        </div>
    </section>

    {{-- RESULTADO DE LA ÚLTIMA IMPORTACIÓN --}}
    @if (($ultimoResultado['creadas'] ?? 0) + ($ultimoResultado['actualizadas'] ?? 0) > 0)
        <div role="status" class="flex flex-col gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: inset 3px 0 var(--theme-success);">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-success-soft)] text-[var(--theme-success)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <p class="text-sm text-[var(--theme-text)]">
                    <strong class="font-semibold text-[var(--theme-text-strong)]">
                        {{ collect([
                            $ultimoResultado['creadas'] ? $ultimoResultado['creadas'].($ultimoResultado['creadas'] === 1 ? ' registro creado' : ' registros creados') : null,
                            $ultimoResultado['actualizadas'] ? $ultimoResultado['actualizadas'].($ultimoResultado['actualizadas'] === 1 ? ' actualizado' : ' actualizados') : null,
                        ])->filter()->implode(' y ') }}.
                    </strong>
                    <span class="block text-xs text-[var(--theme-text-muted)]">
                        {{ $ultimoResultado['marcas'] }} {{ $ultimoResultado['marcas'] === 1 ? 'marca' : 'marcas' }} · {{ $ultimoResultado['modelos'] }} {{ $ultimoResultado['modelos'] === 1 ? 'modelo' : 'modelos' }}
                        @if ($cuentas['total'] > 0) · abajo quedan las filas que no se guardaron. @endif
                    </span>
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('catalogos.index', ['section' => 'marcas']) }}" wire:navigate.hover class="inline-flex h-9 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">Ver marcas</a>
                <a href="{{ route('catalogos.index', ['section' => 'modelos']) }}" wire:navigate.hover class="inline-flex h-9 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">Ver modelos</a>
            </div>
        </div>
    @endif

    {{-- ========================================================
        2. VISTA PREVIA
    ======================================================== --}}
    @if ($cuentas['total'] > 0)
        <x-admin.filter-bar>
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[var(--theme-text-strong)]">{{ $nombreArchivo }}</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        {{ $cuentas['marcas'] }} {{ $cuentas['marcas'] === 1 ? 'marca' : 'marcas' }} · {{ $cuentas['modelos'] }} {{ $cuentas['modelos'] === 1 ? 'modelo' : 'modelos' }}
                    </p>

                    @if ($cuentas['existentes'] > 0)
                        <label class="mt-3 flex cursor-pointer items-start gap-2 text-sm text-[var(--theme-text)]">
                            <input type="checkbox" x-model="actualizar" x-bind:disabled="corriendo" class="mt-0.5 h-4 w-4 rounded border-[var(--theme-border-strong)] accent-[var(--theme-primary)]">
                            <span>
                                <span class="font-medium text-[var(--theme-text-strong)]">Actualizar los que ya existen</span>
                                <span class="block text-xs text-[var(--theme-text-muted)]">
                                    Se reconocen por su código o su nombre. Solo cambian las celdas con valor; una celda vacía conserva el dato actual.
                                </span>
                            </span>
                        </label>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button variant="secondary" wire:click="descartar" x-disabled="corriendo">
                    {{ $terminado ? 'Importar otro archivo' : 'Descartar' }}
                </x-ui.button>

                @if (! $terminado && $modos['sin']['listas'] + $modos['con']['listas'] > 0)
                    <x-ui.button
                        x-show="(actualizar ? {{ $modos['con']['listas'] }} : {{ $modos['sin']['listas'] }}) > 0"
                        x-on:click="importar()"
                        x-loading="corriendo"
                        loading-text="Guardando…"
                    >
                        <span x-text="actualizar ? @js($modos['con']['boton']) : @js($modos['sin']['boton'])">{{ $actualizarExistentes ? $modos['con']['boton'] : $modos['sin']['boton'] }}</span>
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-admin.filter-bar>

        {{-- Métricas (una versión por estado de la casilla) --}}
        @foreach ($modos as $modo => $m)
            <div x-show="{{ $modo === 'con' ? 'actualizar' : '! actualizar' }}" @if ($modo === 'con' && ! $actualizarExistentes || $modo === 'sin' && $actualizarExistentes) x-cloak @endif>
                <x-admin.metrics :items="array_values(array_filter([
                    ['label' => $modo === 'con' ? 'Por guardar (nuevos y actualizaciones)' : 'Listos para crear', 'value' => $m['listas']],
                    ['label' => 'Con errores', 'value' => $m['errores']],
                    $cuentas['existentes'] ? ['label' => $modo === 'con' ? 'Ya existen · '.$m['sinCambios'].' sin cambios' : 'Ya existen', 'value' => $cuentas['existentes']] : null,
                    ['label' => 'Filas en el archivo', 'value' => $cuentas['total']],
                ]))" />
            </div>
        @endforeach

        <div>
            {{-- Avance --}}
            <div x-show="corriendo" x-cloak class="mb-4" role="status">
                <div class="h-2 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:style="{ width: (totalImportar ? Math.max(3, Math.round(hechos / totalImportar * 100)) : 3) + '%' }"></div>
                </div>
                <p class="mt-1.5 text-[11px] tabular-nums text-[var(--theme-text-muted)]" x-text="hechos + ' de ' + totalImportar + ' guardados'"></p>
            </div>

            {{-- Filas: tabla en escritorio y tarjetas en móvil --}}
            <div class="lg:overflow-hidden lg:rounded-lg lg:border lg:border-[var(--theme-border)] lg:bg-[var(--theme-surface)]">
                <div class="hidden h-11 items-center border-b border-[var(--theme-border)] text-xs font-medium text-[var(--theme-text-muted)] lg:grid {{ $columnasFila }}">
                    <div class="px-4">Fila en el archivo</div>
                    <div class="px-4">Tipo</div>
                    <div class="px-4">Nombre</div>
                    <div class="px-4">Resultado</div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:block lg:divide-y lg:divide-[var(--theme-border)]">
                    @foreach ($filas as $fila)
                        <div
                            wire:key="mm-fila-{{ $fila['numero'] }}"
                            class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 text-sm shadow-sm lg:grid lg:items-start lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:text-xs lg:shadow-none lg:hover:bg-[var(--theme-primary-soft-subtle)] 2xl:text-sm {{ $columnasFila }}"
                        >
                            <div class="flex items-center gap-1.5 text-xs text-[var(--theme-text-muted)] lg:contents">
                                <span class="lg:px-4 lg:py-3 lg:text-xs 2xl:text-sm">{{ $fila['etiqueta'] }}</span>
                                <span class="lg:hidden" aria-hidden="true">·</span>
                                <span class="lg:px-4 lg:py-3 lg:text-xs lg:text-[var(--theme-text)] 2xl:text-sm">{{ $fila['clase'] === 'marca' ? 'Marca' : 'Modelo' }}</span>
                            </div>

                            <div class="mt-1 min-w-0 lg:mt-0 lg:px-4 lg:py-3">
                                <p class="break-words font-medium text-[var(--theme-text-strong)]">{{ $fila['nombre'] ?: '—' }}</p>
                                @if ($fila['detalle'] !== '')
                                    <p class="break-words text-xs text-[var(--theme-text-muted)]">{{ $fila['detalle'] }}</p>
                                @endif
                            </div>

                            <div class="mt-2 min-w-0 border-t border-[var(--theme-border)] pt-2 lg:mt-0 lg:border-t-0 lg:px-4 lg:py-3">
                                @php
                                    $variantes = ($fila['existe'] ?? false) && ! in_array($fila['estado'], ['creada', 'actualizada', 'fallo'], true)
                                        ? ['! actualizar' => $fila['sinActualizar'], 'actualizar' => $fila['actualizando']]
                                        : [null => ['estado' => $fila['estado'], 'mensajes' => $fila['mensajes'] ?? []]];
                                @endphp

                                @foreach ($variantes as $condicion => $r)
                                    <div
                                        @if ($condicion) x-show="{{ $condicion }}" @if (($condicion === 'actualizar') !== $actualizarExistentes) x-cloak @endif @endif
                                    >
                                        @php [$texto, $color] = $insignia($r['estado']); @endphp
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $color }}">
                                            {{ $texto }}@if ($fila['codigo'] !== '') · {{ $fila['codigo'] }}@endif
                                            @if (in_array($r['estado'], ['actualizar', 'actualizada'], true)) · {{ count($fila['cambios']) }} {{ count($fila['cambios']) === 1 ? 'cambio' : 'cambios' }} @endif
                                        </span>

                                        @if (in_array($r['estado'], ['actualizar', 'actualizada'], true))
                                            <ul class="mt-1 space-y-0.5 text-xs text-[var(--theme-text)]">
                                                @foreach ($fila['cambios'] as $cambio)
                                                    <li>
                                                        <span class="text-[var(--theme-text-muted)]">{{ $cambio['etiqueta'] }}:</span>
                                                        @if ($cambio['antes'] !== '')
                                                            <span class="text-[var(--theme-text-muted)] line-through">{{ $cambio['antes'] }}</span> →
                                                        @else
                                                            <span class="text-[var(--theme-text-muted)]">(vacío)</span> →
                                                        @endif
                                                        <span class="font-medium text-[var(--theme-text-strong)]">{{ $cambio['despues'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif (! empty($r['mensajes']))
                                            <ul class="mt-1 space-y-0.5 text-xs text-[var(--theme-danger)]">
                                                @foreach ($r['mensajes'] as $mensaje)
                                                    <li>{{ $mensaje }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
