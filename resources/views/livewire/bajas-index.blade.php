<div class="space-y-5 text-sm text-[var(--theme-text)]" @if($procesando) wire:poll.2s="procesarSiguiente" @endif>
    <fieldset @disabled($procesando) class="space-y-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
            <span class="shrink-0 text-sm font-medium text-[var(--theme-text-strong)]">Estado de la baja</span>
            <div class="inline-flex w-fit max-w-full overflow-hidden rounded-md border border-[var(--theme-border)]">
            @foreach(['resguardo' => 'En resguardo', 'completadas' => 'Completadas'] as $key => $label)
                <button type="button" wire:click="$set('estado', '{{ $key }}')" aria-pressed="{{ $estado === $key ? 'true' : 'false' }}" class="whitespace-nowrap px-5 py-2.5 text-sm font-medium transition-colors disabled:opacity-50 {{ $estado === $key ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]' : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]' }}">{{ $label }}</button>
            @endforeach
            </div>
        </div>
        <p class="text-xs text-[var(--theme-text-muted)]">{{ $estado === 'resguardo' ? 'Equipos pendientes de revisión y finalización de baja.' : 'Equipos con baja definitiva registrada.' }}</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-xs text-[var(--theme-text-muted)]">Buscar equipo
                <input wire:model.live.debounce.400ms="buscar" placeholder="Nombre, código o serie" class="mt-1.5 h-10 w-full text-sm text-[var(--theme-text)] rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2">
            </label>
            <label class="text-xs text-[var(--theme-text-muted)]">Filtrar por
                <select wire:model.live="periodo" class="mt-1.5 h-10 w-full text-sm text-[var(--theme-text)] rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2">
                    <option value="mes">Meses</option><option value="semana">Semanas</option><option value="fecha">Fechas</option>
                </select>
            </label>
            @foreach(['inicio' => 'Período inicial', 'fin' => 'Período final'] as $key => $label)
                <label class="text-xs text-[var(--theme-text-muted)]">{{ $label }}
                    <input wire:key="{{ $periodo }}-{{ $key }}" type="{{ ['mes' => 'month', 'semana' => 'week', 'fecha' => 'date'][$periodo] ?? 'date' }}" wire:model.live="{{ $key }}" class="mt-1.5 h-10 w-full text-sm text-[var(--theme-text)] rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2">
                </label>
            @endforeach
        </div>
        <p class="text-xs text-[var(--theme-text-muted)]">El rango incluye ambos períodos completos y filtra por la fecha de {{ $estado === 'resguardo' ? 'ingreso a resguardo' : 'baja definitiva' }}. Sin fechas se muestran todos.</p>
    </fieldset>
    @if($errors->any())
        <div role="alert" class="rounded-md border border-red-500 p-4 text-sm text-red-500">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    @if($procesando || $completadas > 0)
        <p role="status" class="text-sm text-[var(--theme-primary)]">{{ $completadas }} bajas completadas. @if($procesando) Pendientes: {{ count($pendientes) }}. Mantén esta página abierta hasta terminar. @endif</p>
    @endif
    <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-[var(--theme-text-muted)]">{{ $registros->total() }} resultados</p>
            @if($estado === 'resguardo')
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <button wire:click="seleccionarTodos" @disabled($procesando) class="text-[var(--theme-primary)]">Seleccionar todos los resultados</button>
                    <button wire:click="$set('seleccionados', [])" @disabled($procesando)>Quitar selección</button>
                    <span>{{ count($seleccionados) }} seleccionados</span>
                    @if(count($seleccionados))
                        <button type="button" wire:click="$set('confirmando', true)" @disabled($procesando) class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[var(--theme-danger-soft)] px-5 text-sm font-medium text-[var(--theme-danger)] transition-all duration-200 hover:opacity-80 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-0a879ac') }}"></use></svg>
                            Finalizar bajas
                        </button>
                    @endif
                </div>
            @endif
        </div>
        @if($confirmando && !$procesando)
            <div x-data x-on:keydown.escape.window="$wire.set('confirmando', false)" x-on:click.self="$wire.set('confirmando', false)" class="fixed inset-0 z-[210] flex items-center justify-center bg-black/60 px-4 py-6" role="presentation">
                <section role="dialog" aria-modal="true" aria-labelledby="finalizar-bajas-titulo" class="w-full max-w-lg space-y-4 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5 shadow-xl">
                    <h2 id="finalizar-bajas-titulo" class="text-base font-semibold text-[var(--theme-text-strong)]">Finalizar {{ count($seleccionados) === 1 ? 'la baja' : count($seleccionados).' bajas' }}</h2>
                    <div class="space-y-2 text-sm text-[var(--theme-text)]">
                        <p>{{ count($seleccionados) === 1 ? 'El equipo seleccionado se dará' : 'Los '.count($seleccionados).' equipos seleccionados se darán' }} de baja definitiva y <strong>dejará{{ count($seleccionados) === 1 ? '' : 'n' }} de contarse en el inventario activo</strong>. También se cerrarán sus asignaciones vigentes.</p>
                        <p>El registro del equipo, sus asignaciones y su historial de movimientos <strong>se conservan</strong> para consulta y auditoría. Una vez completada, la baja no puede regresar a resguardo.</p>
                    </div>
                    <label class="block text-xs text-[var(--theme-text-muted)]">Fecha de baja<input type="date" wire:model="fechaBaja" class="mt-1.5 block h-10 w-full text-sm text-[var(--theme-text)] rounded border border-[var(--theme-border)] bg-[var(--theme-surface)] p-2"></label>
                    @error('fechaBaja')<p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p>@enderror
                    @error('seleccionados')<p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p>@enderror
                    <div class="flex flex-wrap justify-end gap-3">
                        <button type="button" wire:click="$set('confirmando', false)" class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)]">Cancelar</button>
                        <button type="button" wire:click="iniciarFinalizacion" wire:loading.attr="disabled" wire:target="iniciarFinalizacion" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[var(--theme-danger-soft)] px-5 text-sm font-medium text-[var(--theme-danger)] transition-all duration-200 hover:opacity-80 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg wire:loading.remove wire:target="iniciarFinalizacion" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-0a879ac') }}"></use></svg>
                            <svg wire:loading wire:target="iniciarFinalizacion" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-5387e71') }}"></use></svg>
                            <span wire:loading.remove wire:target="iniciarFinalizacion">Confirmar baja</span>
                            <span wire:loading wire:target="iniciarFinalizacion">Finalizando...</span>
                        </button>
                    </div>
                </section>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead><tr class="border-b border-[var(--theme-border)]">@if($estado === 'resguardo')<th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Seleccionar</th>@endif<th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Código</th><th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Equipo o bien</th><th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Tipo</th><th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Fecha</th><th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Motivo</th><th class="px-3 py-3 text-xs font-medium text-[var(--theme-text-muted)]">Acciones</th></tr></thead>
                <tbody>
                    @forelse($registros as $registro)
                        <tr wire:key="{{ $estado }}-{{ $registro->id_equipo }}" class="border-b border-[var(--theme-border)]">
                            @if($estado === 'resguardo')<td class="p-3"><input type="checkbox" wire:model.live="seleccionados" value="{{ $registro->id_equipo }}" @disabled($procesando) aria-label="Seleccionar {{ $registro->codigo_inventario }}"></td>@endif
                            <td class="p-3">{{ $registro->codigo_inventario }}</td><td class="p-3">{{ $registro->nombre_equipo }}</td><td class="p-3">{{ $registro->tipo }}</td><td class="p-3">{{ \App\Support\Fecha::dia($registro->fecha) }}</td><td class="p-3">{{ $registro->motivo }}</td>
                            <td class="p-3"><a wire:navigate href="{{ route('equipos.show', $registro->id_equipo) }}" class="text-[var(--theme-primary)]">Ver equipo</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-[var(--theme-text-muted)]">No hay bajas con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $registros->links() }}</div>
    </div>
</div>