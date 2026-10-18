{{-- ============================================================
    FILTRO CON LISTA (Administración)
    ------------------------------------------------------------
    Usa el componente del proyecto <x-searchable-select> con el
    ancho estándar de la barra de filtros.

    <x-admin.select
        model="estado"
        placeholder="Estado"
        :options="[['value' => 'todos', 'label' => 'Todos los estados'], ...]"
    />

    clear-label agrega la opción para limpiar (valor vacío).
============================================================ --}}
@props([
    'model',
    'options' => [],
    'placeholder' => 'Selecciona...',
    'clearLabel' => null,
])

<div {{ $attributes->class('w-full shrink-0 sm:w-52') }}>
    <x-searchable-select
        :wire-model="$model"
        :options="$options"
        :placeholder="$placeholder"
        :show-clear="filled($clearLabel)"
        :clear-label="$clearLabel ?? 'Todos'"
        :empty-as-null="false"
    />
</div>
