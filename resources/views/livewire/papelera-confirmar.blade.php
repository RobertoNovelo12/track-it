{{-- ============================================================
    MOVER A LA PAPELERA (confirmación global)
    App\Livewire\PapeleraConfirmar · se abre con
    $dispatch('papelera-mover', { tipo, id })
============================================================ --}}
<div>
    @if ($abierto)
        <div
            x-data
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
                        @if ($bloqueos !== [])
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

                @if ($bloqueos !== [])
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
