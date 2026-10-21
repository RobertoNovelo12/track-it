{{-- ============================================================
    ENCABEZADO DE FORMULARIO (agregar / editar)
    ------------------------------------------------------------
    El título y la ruta ahora se muestran en la barra superior
    (layouts/partials/encabezado + App\Support\Encabezado).
    Aquí solo quedan los botones opcionales de la pantalla.

    <x-form.page-header title="Editar hotel" :crumbs="[...]">
        (opcional) botones a la derecha
    </x-form.page-header>

    title y crumbs se conservan por compatibilidad (no se pintan).
============================================================ --}}
@props([
    'title' => null,
    'crumbs' => [],
])

@if (trim(preg_replace('/<!--.*?-->/s', '', (string) $slot)) !== '')
    <div class="mb-5 flex flex-wrap items-center justify-end gap-2 sm:mb-6">
        {{ $slot }}
    </div>
@endif
