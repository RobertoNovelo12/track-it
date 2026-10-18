{{-- ============================================================
    OPCIONES DEL MENÚ DE ACCIONES
    ------------------------------------------------------------
    Cada opción: label + href (enlace), click (wire:click)
    o js (expresión Alpine, p. ej. $dispatch para abrir un modal
    con los datos de la fila sin consultar al servidor).
    El menú se cierra al instante al elegir una opción (sin
    animación de salida, para que no quede detrás de otras filas).
    'danger' => true la muestra en rojo.
============================================================ --}}
@foreach ($items as $item)
    @php
        $clases = 'block w-full px-4 '.$itemPadding.' text-left text-xs transition-colors '
            .(! empty($item['danger'])
                ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                : 'text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]');
    @endphp

    @if (! empty($item['href']))
        <a
            href="{{ $item['href'] }}"
            wire:navigate.hover
            @click="menuOpen = false"
            class="{{ $clases }}"
        >
            {{ $item['label'] }}
        </a>
    @elseif (! empty($item['js']))
        {{-- Acción en el navegador (Alpine), sin petición al servidor --}}
        <button
            type="button"
            @click="menuOpen = false; {{ $item['js'] }}"
            class="{{ $clases }}"
        >
            {{ $item['label'] }}
        </button>
    @elseif (! empty($item['click']))
        <button
            type="button"
            wire:click="{{ $item['click'] }}"
            @if (! empty($item['confirm'])) wire:confirm="{{ $item['confirm'] }}" @endif
            @click="menuOpen = false"
            class="{{ $clases }}"
        >
            {{ $item['label'] }}
        </button>
    @endif
@endforeach
