{{-- Resultado de una fila de la importación ($r: estado, mensajes, cambios, codigo) --}}
@switch($r['estado'])
                                        @case('importada')
                                            <span class="inline-flex rounded-md bg-[var(--theme-success-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-success)]">Importada · {{ $r['codigo'] }}</span>
                                            @break
                                        @case('lista')
                                            <span class="inline-flex rounded-md bg-[var(--theme-primary-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-primary)]">Lista para importar</span>
                                            @break
                                        @case('actualizar')
                                        @case('actualizada')
                                            <span class="inline-flex rounded-md {{ $r['estado'] === 'actualizada' ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]' : 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]' }} px-2 py-0.5 text-xs font-medium">
                                                {{ $r['estado'] === 'actualizada' ? 'Actualizada' : 'Se actualizará' }} · {{ $r['codigo'] }} · {{ count($r['cambios'] ?? []) }} {{ count($r['cambios'] ?? []) === 1 ? 'cambio' : 'cambios' }}
                                            </span>
                                            <ul class="mt-1 space-y-0.5 text-xs text-[var(--theme-text)]">
                                                @foreach ($r['cambios'] ?? [] as $cambio)
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
                                                @foreach ($r['mensajes'] as $mensaje)
                                                    <li class="text-[var(--theme-warning)]">{{ $mensaje }}</li>
                                                @endforeach
                                            </ul>
                                            @break
                                        @case('sin_cambios')
                                            <span class="inline-flex rounded-md bg-[var(--theme-surface-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-text-muted)]">Sin cambios · {{ $r['codigo'] }}</span>
                                            @foreach ($r['mensajes'] as $mensaje)
                                                <p class="mt-1 text-xs text-[var(--theme-warning)]">{{ $mensaje }}</p>
                                            @endforeach
                                            @break
                                        @default
                                            <span class="inline-flex rounded-md bg-[var(--theme-danger-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-danger)]">{{ $r['estado'] === 'fallo' ? 'No se guardó' : 'Con errores' }}</span>
                                            <ul class="mt-1 space-y-0.5 text-xs text-[var(--theme-danger)]">
                                                @foreach ($r['mensajes'] as $mensaje)
                                                    <li>{{ $mensaje }}</li>
                                                @endforeach
                                            </ul>

                                            {{-- Valor fuera de la lista: corregirlo aquí (en todas las filas que lo traen) --}}
                                            @foreach (collect($r['faltan'] ?? [])->where('clase', 'opcion')->unique('clave') as $opcion)
                                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="text-[var(--theme-text-muted)]">{{ $opcion['columna'] }}: usar</span>
                                                    <select
                                                        x-on:change="if ($event.target.value) { revisarTras(() => $wire.corregirValor(@js($opcion['clave']), $event.target.value)); }"
                                                        x-bind:disabled="corriendo || revisando"
                                                        class="h-8 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-2 text-xs text-[var(--theme-text)] focus:border-[var(--theme-primary)] focus:outline-none"
                                                        aria-label="Corregir «{{ $opcion['valor'] }}» en {{ $opcion['columna'] }}"
                                                    >
                                                        <option value="">Elegir una opción…</option>
                                                        @foreach ($opcion['opciones'] as $valida)
                                                            <option value="{{ $valida }}">{{ $valida }}</option>
                                                        @endforeach
                                                        <option value="__quitar__">{{ ! empty($r['existe']) ? 'Dejar sin cambiar' : 'Dejar vacío' }}</option>
                                                    </select>
                                                </div>
                                            @endforeach
                                    @endswitch
