{{-- ============================================================
    ENCABEZADO DE FORMULARIO (agregar / editar)
    ------------------------------------------------------------
    El título y la ruta se muestran en la barra superior
    (layouts/partials/encabezado + App\Support\Encabezado).
    Aquí queda la fila superior: «Volver» a la izquierda y los
    botones opcionales de la pantalla a la derecha.

    <x-form.page-header title="Editar hotel" :crumbs="[...]">
        (opcional) botones a la derecha
    </x-form.page-header>

    title y crumbs se conservan por compatibilidad (no se pintan).
    destino (opcional): a dónde lleva «Volver» si no hay pantalla
    anterior (por omisión, el nivel anterior de la ruta).
    :volver="false" dentro de componentes Livewire diferidos (lazy):
    ahí «Volver» ya lo pone el layout desde la carga inicial.
============================================================ --}}
@props([
    'title' => null,
    'crumbs' => [],
    'destino' => null,
    'volver' => true,
])

@php
    $tieneBotones = trim(preg_replace('/<!--.*?-->/s', '', (string) $slot)) !== '';
@endphp

@if ($volver || $tieneBotones)
<div @class([
    'mb-5 flex-wrap items-center gap-2 sm:mb-6',
    'flex' => $tieneBotones,
    'hidden md:flex' => ! $tieneBotones,
])>
    @if ($volver && $destino)
        @include('layouts.partials.boton-volver', ['destino' => $destino])
    @elseif ($volver)
        @include('layouts.partials.boton-volver')
    @endif

    @if ($tieneBotones)
        <div class="ml-auto flex flex-wrap items-center justify-end gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
@endif
