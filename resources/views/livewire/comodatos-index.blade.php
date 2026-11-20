<div class="space-y-5 text-sm">
@php($input = 'h-10 w-full rounded border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]')
@if(session('comodato_ok'))<div role="status" class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">{{ session('comodato_ok') }}</div>@endif
<form wire:submit="guardar" class="space-y-5">
<section class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] p-5 sm:p-6">
<div class="grid gap-5 sm:grid-cols-2">
<div>
<x-searchable-select wire-model="equipoId" :options="$equipos->map(fn ($e) => ['value'=>(string)$e->id_equipo, 'label'=>$e->codigo_inventario.' · '.$e->nombre_equipo])->all()" label="Mueble o equipo *" placeholder="Escribe el nombre o código del bien..." :show-clear="false" :empty-as-null="false" />
@error('equipoId')<p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>@enderror
</div>
@foreach(['solicitante'=>'Solicitante','escuela'=>'Escuela o institución','numero'=>'Número de comodato','inicio'=>'Fecha inicial','fin'=>'Fecha final'] as $campo=>$label)
<div><label for="comodato-{{ $campo }}" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">{{ $label }} *</label><input id="comodato-{{ $campo }}" type="{{ in_array($campo,['inicio','fin'])?'date':'text' }}" wire:model="{{ $campo }}" class="{{ $input }}" @if(!in_array($campo,['inicio','fin'])) maxlength="255" placeholder="{{ $label }}" @endif>
@error($campo)<p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>@enderror</div>
@endforeach
</div></section>
<div class="flex justify-end"><button type="submit" class="inline-flex h-10 items-center justify-center rounded bg-[var(--theme-primary)] px-5 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:opacity-50" wire:loading.attr="disabled">Registrar comodato</button></div>
</form>
<section class="overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)]">
<div class="flex items-center justify-between border-b border-[var(--theme-border)] px-5 py-5"><h2 class="text-sm font-medium text-[var(--theme-text)]">Historial de comodatos</h2><span class="text-xs text-[var(--theme-text-muted)]">{{ $registros->total() }} resultados</span></div>
<div class="overflow-x-auto"><table class="w-full min-w-[850px] text-left text-sm"><thead><tr class="h-12 border-b border-[var(--theme-border)]">@foreach(['Bien','Solicitante','Institución','Comodato','Periodo','Estado'] as $titulo)<th class="px-5 py-3 font-medium text-[var(--theme-text-muted)]">{{ $titulo }}</th>@endforeach</tr></thead><tbody class="divide-y divide-[var(--theme-border)]">@forelse($registros as $c)<tr class="text-[var(--theme-text)] hover:bg-[var(--theme-surface-hover)]"><td class="px-5 py-4"><span class="block font-medium">{{ $c->codigo_inventario }}</span><span>{{ $c->nombre_equipo }}</span></td><td class="px-5 py-4">{{ $c->solicitante }}</td><td class="px-5 py-4">{{ $c->escuela }}</td><td class="px-5 py-4">{{ $c->numero }}</td><td class="px-5 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($c->inicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($c->fin)->format('d/m/Y') }}</td><td class="px-5 py-4"><span class="rounded bg-[var(--theme-primary-soft)] px-2 py-1 text-xs text-[var(--theme-primary)]">{{ $c->inicio > now()->toDateString() ? 'Programado' : ($c->fin < now()->toDateString() ? 'Periodo vencido' : 'En comodato') }}</span></td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-[var(--theme-text-muted)]">No hay comodatos registrados.</td></tr>@endforelse</tbody></table></div><div class="border-t border-[var(--theme-border)] px-5 py-4">{{ $registros->links() }}</div>
</section></div>