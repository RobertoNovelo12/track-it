<div class="space-y-5 text-sm text-[var(--theme-text)]" @if($procesando) wire:poll.2s="procesarSiguiente" @endif>
    <fieldset @disabled($procesando) class="space-y-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
            <span class="shrink-0 text-sm font-medium text-[var(--theme-text-strong)]">Estado de la baja</span>
            <div class="grid w-full grid-cols-2 overflow-hidden rounded-md border border-[var(--theme-border)] sm:max-w-xl">
            @foreach(['resguardo' => 'En resguardo', 'completadas' => 'Completadas'] as $key => $label)
                <button type="button" wire:click="$set('estado', '{{ $key }}')" aria-pressed="{{ $estado === $key ? 'true' : 'false' }}" class="px-3 py-2.5 text-sm font-medium transition-colors disabled:opacity-50 {{ $estado === $key ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]' : 'text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]' }}">{{ $label }}</button>
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
                        <button wire:click="$set('confirmando', true)" @disabled($procesando) class="rounded-md bg-[var(--theme-primary)] px-4 py-2 text-white">Finalizar bajas</button>
                    @endif
                </div>
            @endif
        </div>
        @if($confirmando && !$procesando)
            <div class="mb-5 space-y-4 rounded-md border border-[var(--theme-border)] p-4">
                <p>Se completará la baja de {{ count($seleccionados) }} equipos y se cerrarán sus asignaciones activas. El historial se conservará.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-xs text-[var(--theme-text-muted)]">Fecha de baja<input type="date" wire:model="fechaBaja" class="mt-1.5 block h-10 w-full text-sm text-[var(--theme-text)] rounded border border-[var(--theme-border)] bg-[var(--theme-surface)] p-2"></label>
                </div>
                <button wire:click="iniciarFinalizacion" wire:loading.attr="disabled" class="rounded bg-[var(--theme-primary)] px-4 py-2 text-white">Confirmar finalización</button>
                <button wire:click="$set('confirmando', false)" class="px-4 py-2">Cancelar</button>
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
