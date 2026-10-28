{{-- ============================================================
    FILTROS ESPECÍFICOS DEL TIPO DE EQUIPO
    ------------------------------------------------------------
    Se generan con la configuración del formulario del tipo
    (secciones «Información de {Tipo}» y personalizadas). Si se
    agrega un campo nuevo, aparece aquí sin cambiar código.

    Variables:
    - $camposTipoFiltros  Campos del tipo (EquiposIndex::getCamposFiltrables)
    - $tipoNombre         Nombre del tipo seleccionado
    - $variante           'escritorio' | 'movil'
============================================================ --}}
@php
    $esMovil = ($variante ?? 'escritorio') === 'movil';

    $claseInput = 'w-full px-3 py-2 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors';
@endphp

@if (! empty($camposTipoFiltros))
    <div
        wire:key="filtros-tipo-{{ $variante ?? 'escritorio' }}-{{ $tipoActivo }}"
        @class([
            'ui-fade-in',
            'mb-4 rounded-md border border-dashed border-[var(--theme-border-strong)] p-4' => ! $esMovil,
            'space-y-5 border-t border-[var(--theme-border)] pt-5' => $esMovil,
        ])
    >
        <p @class([
            'flex items-center gap-2 text-xs font-semibold text-[var(--theme-text-strong)]',
            'mb-3' => ! $esMovil,
        ])>
            <svg class="h-3.5 w-3.5 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-ab35c9d') }}"></use></svg>
            Filtros de {{ $tipoNombre ?: 'este tipo' }}
        </p>

        <div @class([
            'grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4' => ! $esMovil,
            'space-y-5' => $esMovil,
        ])>
            @foreach ($camposTipoFiltros as $campo)
                @php
                    $modelo = 'camposTipo.' . $campo['id'];
                @endphp

                <div wire:key="campo-tipo-{{ $variante ?? 'escritorio' }}-{{ $campo['id'] }}">
                    @if (in_array($campo['control'], ['select', 'boolean'], true))
                        <x-searchable-select
                            :label="$campo['etiqueta']"
                            :wire-model="$modelo"
                            :options="$campo['opciones']"
                            :placeholder="'Buscar ' . mb_strtolower($campo['etiqueta']) . '...'"
                        />
                    @else
                        <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                            {{ $campo['etiqueta'] }}
                        </label>

                        <input
                            type="{{ match ($campo['control']) { 'number' => 'number', 'date' => 'date', default => 'text' } }}"
                            @if ($campo['control'] === 'number') step="any" inputmode="decimal" @endif
                            wire:model="{{ $modelo }}"
                            placeholder="{{ $campo['control'] === 'number' ? 'Valor exacto...' : 'Buscar...' }}"
                            autocomplete="off"
                            class="{{ $claseInput }}"
                        >
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
