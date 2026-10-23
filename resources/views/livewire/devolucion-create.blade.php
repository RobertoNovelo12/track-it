{{-- ============================================================
    ASIGNACIONES › DEVOLVER EQUIPO
    ------------------------------------------------------------
    Mismo diseño que los demás formularios: secciones en
    tarjetas, etiquetas visibles y acciones abajo a la derecha.
============================================================ --}}
@php
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]';
    $etiqueta = 'mb-1.5 block text-xs text-[var(--theme-text-muted)]';
    $error = 'mt-1 text-xs text-[var(--theme-danger)]';
@endphp

<div class="space-y-5">

    @include('layouts.partials.boton-volver', ['destino' => route('asignaciones.index')])

    {{-- Confirmación de la última devolución --}}
    @if ($ultima)
        <div role="status" class="flex flex-col gap-3 rounded-xl border border-[var(--theme-success-border)] bg-[var(--theme-success-soft)] px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--theme-surface)] text-[var(--theme-success)]" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        Se devolvió {{ $ultima['codigo'] }}{{ $ultima['nombre'] !== '' ? ' · ' . $ultima['nombre'] : '' }}
                    </p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text)]">
                        @if ($ultima['colaborador'] !== '') Ya no está a cargo de {{ $ultima['colaborador'] }}. @endif
                        Quedó disponible para asignarse. Imprime el acta para recabar las firmas.
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                <a
                    href="{{ route('asignaciones.devoluciones.acta', $ultima['movimiento']) }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-[var(--theme-primary)] px-3.5 text-xs font-medium text-white hover:bg-[var(--theme-primary-hover)]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                    Acta de devolución
                </a>
                <a
                    href="{{ route('equipos.show', $ultima['id_equipo']) }}"
                    wire:navigate.hover
                    class="inline-flex h-9 items-center rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3.5 text-xs font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                >
                    Ver equipo
                </a>
            </div>
        </div>
    @endif

    @unless ($disponible)
        <p role="alert" class="rounded-lg border border-[var(--theme-warning-border)] bg-[var(--theme-warning-soft)] px-4 py-3 text-xs text-[var(--theme-text)]">
            Falta ejecutar la actualización de la base de datos (<code class="font-mono">php artisan migrate</code>) para registrar devoluciones.
        </p>
    @endunless

    <form data-avisar-cambios wire:submit="guardar" class="space-y-5">

        {{-- ========================================================
            EQUIPO
        ======================================================== --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Equipo que se devuelve</h2>
            <p class="mb-5 mt-1 text-xs text-[var(--theme-text-muted)]">
                Busca por código, nombre del equipo o nombre del colaborador. Solo aparecen equipos con responsable.
            </p>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <x-searchable-select
                        label="Equipo *"
                        wire-model="idEquipo"
                        :options="$equipos"
                        placeholder="Buscar equipo o colaborador..."
                        :show-clear="false"
                    />
                    @error('idEquipo') <p class="{{ $error }}">{{ $message }}</p> @enderror

                    @if ($equipos === [])
                        <p class="mt-2 text-xs text-[var(--theme-text-muted)]">No hay equipos asignados en este momento.</p>
                    @endif
                </div>

                {{-- Responsable actual --}}
                <div class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] p-4">
                    @if ($asignacion)
                        <p class="text-[11px] font-medium uppercase tracking-wide text-[var(--theme-text-muted)]">Responsable actual</p>
                        <p class="mt-1 text-sm font-semibold text-[var(--theme-text-strong)]">{{ $asignacion['nombre_colaborador'] ?: 'Sin nombre' }}</p>
                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                            <div>
                                <dt class="text-[var(--theme-text-muted)]">Equipo</dt>
                                <dd class="text-[var(--theme-text)]">{{ $asignacion['codigo_inventario'] }} · {{ $asignacion['nombre_equipo'] ?: $asignacion['tipo'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[var(--theme-text-muted)]">Asignado desde</dt>
                                <dd class="text-[var(--theme-text)]">{{ \Illuminate\Support\Carbon::parse($asignacion['fecha_asignacion'])->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-[var(--theme-text-muted)]">Hotel / Departamento</dt>
                                <dd class="text-[var(--theme-text)]">{{ implode(' / ', array_filter([$asignacion['hotel'], $asignacion['departamento']])) ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[var(--theme-text-muted)]">N.º de colaborador</dt>
                                <dd class="text-[var(--theme-text)]">{{ $asignacion['numero_colaborador'] ?: '—' }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-xs text-[var(--theme-text-muted)]">Elige un equipo para ver quién lo tiene asignado.</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ========================================================
            DATOS DE LA DEVOLUCIÓN
        ======================================================== --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">Datos de la devolución</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="devolucion-fecha" class="{{ $etiqueta }}">Fecha de devolución *</label>
                    <input id="devolucion-fecha" type="date" wire:model="fecha" max="{{ now()->toDateString() }}" class="{{ $campo }}">
                    @error('fecha') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-searchable-select
                        label="Motivo *"
                        wire-model="motivo"
                        :options="collect($motivos)->map(fn ($nombre, $clave) => ['value' => $clave, 'label' => $nombre])->values()->all()"
                        placeholder="Elige el motivo..."
                        :show-clear="false"
                    />
                    @error('motivo') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devolucion-recibe" class="{{ $etiqueta }}">Recibe el equipo *</label>
                    <input id="devolucion-recibe" type="text" wire:model="recibidoPor" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">Quien recibe en Sistemas; aparece en el acta.</p>
                    @error('recibidoPor') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                @if ($motivo === 'otro')
                    <div class="md:col-span-2 xl:col-span-3">
                        <label for="devolucion-motivo-otro" class="{{ $etiqueta }}">Describe el motivo *</label>
                        <input id="devolucion-motivo-otro" type="text" wire:model="motivoOtro" maxlength="255" class="{{ $campo }}">
                        @error('motivoOtro') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="md:col-span-2 xl:col-span-3">
                    <label for="devolucion-observaciones" class="{{ $etiqueta }}">Observaciones</label>
                    <textarea id="devolucion-observaciones" wire:model="observaciones" rows="3" maxlength="1000" placeholder="Estado en que se recibe, accesorios entregados (cargador, mouse…), daños visibles." class="{{ $campo }}"></textarea>
                    @error('observaciones') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Acciones --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <x-ui.button
                type="submit"
                target="guardar"
                size="lg"
                loading-text="Registrando…"
                class="w-full sm:w-auto"
                :disabled="! $disponible"
            >
                Registrar devolución
            </x-ui.button>
        </div>
    </form>
</div>
