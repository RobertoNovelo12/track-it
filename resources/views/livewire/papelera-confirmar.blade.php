{{-- ============================================================
    MOVER A LA PAPELERA (confirmación global)
    App\Livewire\PapeleraConfirmar · se abre con
    $dispatch('papelera-mover', { tipo, id })
============================================================ --}}
<div
    {{--
        La ventana se muestra al instante («Revisando…») mientras el
        servidor consulta; luego se reemplaza por el resultado.
    --}}
    x-data="{ cargando: false, temporizador: null,
        empezar() {
            this.cargando = true;
            clearTimeout(this.temporizador);
            this.temporizador = setTimeout(() => this.cargando = false, 20000);
        },
        terminar() {
            this.cargando = false;
            clearTimeout(this.temporizador);
        },
    }"
    @papelera-mover.window="empezar()"
    @papelera-mover-varios.window="empezar()"
    @papelera-sin-abrir.window="terminar()"
>
    <div
        x-show="cargando"
        x-cloak
        x-on:keydown.escape.window="terminar()"
        class="fixed inset-0 z-[210] flex items-center justify-center bg-black/60 px-4 py-6"
        role="presentation"
    >
        <section
            role="dialog"
            aria-modal="true"
            aria-busy="true"
            aria-labelledby="papelera-cargando-titulo"
            class="w-full max-w-md rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5 shadow-xl"
        >
            <div class="flex items-start justify-between gap-3">
                <h2 id="papelera-cargando-titulo" class="text-base font-semibold text-[var(--theme-text-strong)]">Mover a la papelera</h2>

                <button type="button" x-on:click="terminar(); $wire.cerrar()" class="rounded-md p-1 text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]" aria-label="Cerrar ventana">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="mt-5 flex items-center gap-3 text-sm text-[var(--theme-text-muted)]" role="status">
                <span class="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-[var(--theme-border-strong)] border-t-[var(--theme-primary)]" aria-hidden="true"></span>
                Revisando si se puede mover…
            </div>

            <div class="mt-4 space-y-2" aria-hidden="true">
                <div class="h-3 w-3/4 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
                <div class="h-3 w-1/2 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-ui.button variant="secondary" x-on:click="terminar(); $wire.cerrar()">Cancelar</x-ui.button>
            </div>
        </section>
    </div>

    @if ($abierto)
        <div
            x-data
            x-init="terminar()"
            x-on:keydown.escape.window="$wire.cerrar()"
            x-on:click.self="$wire.cerrar()"
            class="fixed inset-0 z-[210] flex items-center justify-center bg-black/60 px-4 py-6"
            role="presentation"
        >
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="papelera-titulo"
                class="w-full max-w-md rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5 shadow-xl"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 id="papelera-titulo" class="text-base font-semibold text-[var(--theme-text-strong)]">
                        @if ($varios || ($desasignable && ! $error))
                            Mover a la papelera
                        @elseif ($bloqueos !== [])
                            No se puede borrar todavía
                        @elseif ($error)
                            No se pudo mover a la papelera
                        @else
                            Mover a la papelera
                        @endif
                    </h2>

                    <button
                        type="button"
                        wire:click="cerrar"
                        class="rounded-md p-1 text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]"
                        aria-label="Cerrar ventana"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                @if ($varios)
                    @php $nMovibles = count($movibles); @endphp

                    @if ($error)
                        <p class="mt-3 text-sm text-[var(--theme-text)]">{{ $error }}</p>
                    @elseif ($nMovibles === 0 && $asignados !== [])
                        <p class="mt-3 text-sm text-[var(--theme-text)]">Todos los seleccionados que se pueden mover tienen un responsable asignado.</p>
                    @elseif ($nMovibles > 0)
                        <p class="mt-3 text-sm text-[var(--theme-text)]">
                            Se {{ $nMovibles === 1 ? 'moverá' : 'moverán' }}
                            <strong class="font-semibold text-[var(--theme-text-strong)]">{{ number_format($nMovibles) }}</strong>
                            {{ $nMovibles === 1 ? 'elemento' : 'elementos' }} a la papelera.
                        </p>
                    @else
                        <p class="mt-3 text-sm text-[var(--theme-text)]">Ninguno de los seleccionados se puede mover todavía.</p>
                    @endif

                    @if ($asignados !== [])
                        <p class="mt-4 text-sm font-medium text-[var(--theme-text-strong)]">
                            {{ count($asignados) }} {{ count($asignados) === 1 ? 'tiene' : 'tienen' }} un responsable asignado:
                        </p>

                        <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
                            @foreach ($asignadosEtiquetas as $asignado)
                                <li>
                                    <span class="block truncate">{{ $asignado['etiqueta'] }}</span>
                                    @if ($asignado['responsable'])
                                        <span class="block truncate text-xs text-[var(--theme-text-muted)]">Asignado a {{ $asignado['responsable'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-2 text-xs text-[var(--theme-text-muted)]">
                            Con «Desasignar y mover» se registra su devolución (quedan «Disponible») y se mueven junto con los demás.
                        </p>
                    @endif

                    @if ($noMovibles !== [])
                        <p class="mt-4 text-sm font-medium text-[var(--theme-text-strong)]">
                            {{ count($noMovibles) }} {{ count($noMovibles) === 1 ? 'no se puede mover' : 'no se pueden mover' }} todavía:
                        </p>

                        <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-sm">
                            @foreach ($noMovibles as $fila)
                                <li>
                                    <span class="block truncate text-[var(--theme-text)]">{{ $fila['etiqueta'] }}</span>
                                    <span class="block text-xs text-[var(--theme-text-muted)]">{{ $fila['motivo'] }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($ayuda)
                            <p class="mt-2 text-xs text-[var(--theme-text-muted)]">{{ $ayuda }}</p>
                        @endif
                    @endif

                    @if ($noEncontrados > 0)
                        <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                            {{ $noEncontrados === 1 ? '1 ya no existe' : $noEncontrados.' ya no existen' }} (se borraron mientras tanto).
                        </p>
                    @endif

                    @if ($excedente > 0)
                        <p class="mt-3 text-xs text-[var(--theme-warning)]">
                            Solo se revisaron los primeros {{ \App\Services\PapeleraService::MAXIMO_LOTE }}; los otros {{ $excedente }} no se tocan.
                        </p>
                    @endif

                    @if (($nMovibles > 0 || $asignados !== []) && ! $error)
                        <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                            Un administrador podrá restaurarlos desde la Papelera durante {{ \App\Support\PapeleraEntidades::DIAS }} días; después se eliminan definitivamente.
                        </p>
                    @endif

                    {{-- Con dos opciones de mover (3 botones): uno debajo de otro, a todo
                         lo ancho, la principal arriba. Si no, en una fila a la derecha. --}}
                    <div @class([
                        'mt-6 flex gap-3',
                        'flex-col' => $nMovibles > 0 && $asignados !== [] && ! $error,
                        'flex-wrap justify-end' => ! ($nMovibles > 0 && $asignados !== [] && ! $error),
                    ])>
                        @if ($nMovibles > 0 && $asignados !== [] && ! $error)
                            <x-ui.button variant="danger-soft" class="w-full justify-center" wire:click="confirmarVarios(true)" target="confirmarVarios" loading-text="Moviendo…">
                                Desasignar y mover {{ number_format($nMovibles + count($asignados)) }}
                            </x-ui.button>

                            <x-ui.button variant="secondary" class="w-full justify-center" wire:click="confirmarVarios(false)" target="confirmarVarios" loading-text="Moviendo…">
                                Mover solo {{ number_format($nMovibles) }} (sin los asignados)
                            </x-ui.button>

                            <button type="button" wire:click="cerrar" wire:loading.attr="disabled" wire:target="confirmarVarios" class="h-9 w-full rounded-md text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]">
                                Cancelar
                            </button>
                        @elseif (($nMovibles > 0 || $asignados !== []) && ! $error)
                            <x-ui.button variant="secondary" wire:click="cerrar" disable-target="confirmarVarios">Cancelar</x-ui.button>

                            <x-ui.button variant="danger-soft" wire:click="confirmarVarios({{ $asignados !== [] ? 'true' : 'false' }})" target="confirmarVarios" loading-text="Moviendo…">
                                {{ $asignados !== [] ? 'Desasignar y mover ' . number_format(count($asignados)) : 'Mover ' . number_format($nMovibles) . ' a la papelera' }}
                            </x-ui.button>
                        @else
                            <x-ui.button variant="secondary" wire:click="cerrar">Entendido</x-ui.button>
                        @endif
                    </div>
                @elseif ($desasignable && ! $error)
                    <p class="mt-3 text-sm text-[var(--theme-text)]">
                        {{ $nombre }} <strong class="font-semibold text-[var(--theme-text-strong)]">«{{ $etiqueta }}»</strong>
                        tiene un responsable asignado.
                    </p>

                    @if ($responsable)
                        <div class="mt-3 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3">
                            <span class="block text-[10px] font-medium uppercase tracking-wide text-[var(--theme-text-muted)]">Asignado a</span>
                            <span class="mt-0.5 block text-sm font-medium text-[var(--theme-text-strong)]">{{ $responsable }}</span>
                        </div>
                    @endif

                    <p class="mt-2 text-xs text-[var(--theme-text-muted)]">
                        Se registrará su devolución (quedará «Disponible», sin responsable) y después se moverá a la papelera.
                        Un administrador podrá restaurarlo durante {{ \App\Support\PapeleraEntidades::DIAS }} días.
                    </p>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <x-ui.button variant="secondary" wire:click="cerrar" disable-target="desasignarYConfirmar">Cancelar</x-ui.button>

                        <x-ui.button variant="danger-soft" wire:click="desasignarYConfirmar" target="desasignarYConfirmar" loading-text="Moviendo…">
                            Desasignar y mover a la papelera
                        </x-ui.button>
                    </div>
                @elseif ($bloqueos !== [])
                    <p class="mt-3 text-sm text-[var(--theme-text)]">
                        {{ $nombre }} <strong class="font-semibold text-[var(--theme-text-strong)]">«{{ $etiqueta }}»</strong>
                        todavía se usa en:
                    </p>

                    <ul class="mt-3 space-y-1.5 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
                        @foreach ($bloqueos as $bloqueo)
                            <li class="flex items-center justify-between gap-3">
                                <span>{{ ucfirst($bloqueo['que']) }}</span>
                                @if ((int) $bloqueo['cantidad'] > 0)
                                    <span class="font-semibold text-[var(--theme-text-strong)]">{{ number_format((int) $bloqueo['cantidad']) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($ayuda)
                        <p class="mt-3 text-sm text-[var(--theme-text-muted)]">{{ $ayuda }}</p>
                    @endif

                    <div class="mt-6 flex justify-end">
                        <x-ui.button variant="secondary" wire:click="cerrar">Entendido</x-ui.button>
                    </div>
                @elseif ($error)
                    <p class="mt-3 text-sm text-[var(--theme-text)]">{{ $error }}</p>

                    <div class="mt-6 flex justify-end">
                        <x-ui.button variant="secondary" wire:click="cerrar">Cerrar</x-ui.button>
                    </div>
                @else
                    <p class="mt-3 text-sm text-[var(--theme-text)]">
                        ¿Mover {{ mb_strtolower($nombre) }}
                        <strong class="font-semibold text-[var(--theme-text-strong)]">«{{ $etiqueta }}»</strong>
                        a la papelera?
                    </p>

                    <p class="mt-2 text-xs text-[var(--theme-text-muted)]">
                        Dejará de aparecer en el sistema. Un administrador podrá restaurarlo desde la Papelera
                        durante {{ \App\Support\PapeleraEntidades::DIAS }} días; después se elimina definitivamente.
                    </p>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <x-ui.button variant="secondary" wire:click="cerrar" disable-target="confirmar">Cancelar</x-ui.button>

                        <x-ui.button variant="danger-soft" wire:click="confirmar" target="confirmar" loading-text="Moviendo…">
                            Mover a la papelera
                        </x-ui.button>
                    </div>
                @endif
            </section>
        </div>
    @endif
</div>
