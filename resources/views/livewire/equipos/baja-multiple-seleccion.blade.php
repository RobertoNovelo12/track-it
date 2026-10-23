@include('livewire.equipos.filters.desktop', ['filtrosTodosLosTamanos' => true])
<div wire:loading.delay role="status" class="text-sm text-[var(--theme-text-muted)]">Cargando equipos…</div>
<x-data-table
    :rows="$equipos" row-key="id_equipo" key-prefix="baja-multiple-equipo"
    :selectable="true" selection-model="seleccionados" :selected-keys="$seleccionados"
    :selection-limit="10" select-page-action="seleccionarPagina"
    per-page-model="perPage" sort-model="sort" :sort-field="$sortField" :sort-direction="$sort"
    refresh-action="$refresh" result-singular="equipo encontrado" result-plural="equipos encontrados"
    empty-message="No hay equipos dados de baja que coincidan con la búsqueda."
    :mobile-title="fn ($e) => $e->nombre_equipo" :mobile-subtitle="fn ($e) => $e->numero_serie"
    :columns="[
        ['key' => 'nombre_equipo', 'label' => 'Equipo', 'sort' => 'equipo', 'strong' => true, 'width' => '280px'],
        ['key' => 'numero_serie', 'label' => 'Número de serie', 'width' => '190px'],
        ['key' => 'marca_modelo', 'label' => 'Marca / Modelo', 'sort' => 'marca', 'width' => '240px', 'value' => fn ($e) => ($e->marca ?: '—').' / '.($e->modelo ?: '—')],
        ['key' => 'hotel', 'label' => 'Hotel', 'sort' => 'hotel', 'width' => '190px'],
        ['key' => 'estado', 'label' => 'Estado', 'sort' => 'estado', 'type' => 'status', 'width' => '120px'],
    ]"
    :actions="fn ($e) => ['detail' => route('equipos.show', $e->id_equipo)]"
/>
@error('seleccionados')<p role="alert" class="text-sm text-[var(--theme-danger)]">{{ $message }}</p>@enderror
<div class="flex flex-wrap items-center justify-between gap-4">
    <span aria-live="polite" class="text-sm text-[var(--theme-text-muted)]">{{ count($seleccionados) }} equipos seleccionados de 10</span>
    <div class="flex items-center gap-3"><a href="{{ route('equipos.index') }}" wire:navigate class="rounded-lg border border-[var(--theme-border-strong)] px-4 py-2 text-sm text-[var(--theme-text)]">Cancelar</a><x-ui.button wire:click="siguiente" target="siguiente" :disabled="count($seleccionados) === 0">Siguiente <span aria-hidden="true">→</span></x-ui.button></div>
</div>
