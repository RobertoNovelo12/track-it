{{-- ============================================================
    MÉTRICAS (Administración)
    ------------------------------------------------------------
    <x-admin.metrics :items="[
        ['label' => 'Total de hoteles', 'value' => 6],
        ['label' => 'Activos', 'value' => 6],
        ['label' => 'Inactivos', 'value' => 0],
    ]" />

    La primera tarjeta se resalta. De 2 a 4 tarjetas.
============================================================ --}}
@props(['items' => []])

@php
    $items = array_values(array_filter($items));
    $columnas = match (count($items)) {
        1 => 'sm:grid-cols-1',
        2 => 'sm:grid-cols-2',
        4 => 'sm:grid-cols-2 xl:grid-cols-4',
        default => 'sm:grid-cols-3',
    };
@endphp

@if ($items !== [])
    <div {{ $attributes->class(['grid grid-cols-1 gap-3', $columnas]) }}>
        @foreach ($items as $indice => $item)
            <div
                @class([
                    'rounded-xl border bg-[var(--theme-surface)] px-4 py-3.5',
                    'border-[var(--theme-primary)]' => $indice === 0,
                    'border-[var(--theme-border)]' => $indice !== 0,
                ])
            >
                <p
                    @class([
                        'text-2xl font-semibold leading-tight',
                        'text-[var(--theme-primary)]' => $indice === 0,
                        'text-[var(--theme-text-strong)]' => $indice !== 0,
                    ])
                >
                    {{ is_numeric($item['value']) ? number_format((float) $item['value']) : $item['value'] }}
                </p>

                <p class="mt-0.5 text-[11px] text-[var(--theme-text-muted)]">
                    {{ $item['label'] }}
                </p>
            </div>
        @endforeach
    </div>
@endif
