{{-- ============================================================
    TABLA DE DATOS REUTILIZABLE
    ------------------------------------------------------------
    Diseño tomado de la tabla de Equipos tecnológicos:

    - Escritorio: tabla con columnas fijas, ordenamiento,
      acceso rápido al pasar el cursor (copiar / editar / detalle)
      y menú de acciones por fila.
    - Móvil: cada registro se muestra como tarjeta.
    - Barra de resultados, cantidad por página y paginación.

    Uso básico (dentro de un componente Livewire con WithPagination):

    <x-data-table
        :rows="$hoteles"
        :columns="[
            ['key' => 'nombre', 'label' => 'Hotel', 'width' => '240px', 'sort' => 'nombre', 'strong' => true],
            ['key' => 'codigo', 'label' => 'Código', 'width' => '140px'],
            ['key' => 'estado', 'label' => 'Estado', 'type' => 'status',
                'value' => fn ($h) => $h->activo ? 'Activo' : 'Inactivo'],
        ]"
        row-key="id_unidad"
        key-prefix="hotel"
        :actions="fn ($h) => ['edit' => route('catalogos.hoteles.edit', $h->id_unidad)]"
        :mobile-title="fn ($h) => $h->nombre"
        page-name="hotelesPage"
        result-singular="hotel encontrado"
        result-plural="hoteles encontrados"
    />

    Columnas (todas las claves son opcionales salvo key/label):
    - key        Atributo del registro (también se usa como valor).
    - label      Encabezado.
    - value      Closure fn ($row) => string, o nombre de atributo.
    - default    Texto cuando el valor está vacío (por defecto "—").
    - width      Ancho fijo en escritorio, p. ej. '140px'.
    - sort       Campo que se envía a la acción de ordenamiento.
    - type       'text' (por defecto), 'status' (usa <x-status-badge>)
                 o 'list' (value devuelve un arreglo y se muestran
                 etiquetas; el acceso rápido copia la lista).
    - strong     Resalta el texto (font-medium).
    - popover    false para desactivar el acceso rápido en la celda.
    - desktop    false para mostrar la columna solo en tarjetas.
    - mobile     false para ocultarla en tarjetas; 'filled' para
                 mostrarla solo si tiene valor.
    - mobileLabel Texto de la etiqueta en la tarjeta.

    Ordenamiento: al hacer clic en un encabezado se llama
    sort-action con el campo (asc la primera vez, luego alterna).
    sort-model es la propiedad con la dirección (ASC / DESC).
    En pantallas pequeñas aparece un selector "Ordenar por".

    Acciones: closure fn ($row) => [
        'detail' => url|null,        // icono de ojo y "Detalle"
        'edit'   => url|null,        // "Editar" del acceso rápido
        'menu'   => [                // menú de tres puntos
            ['label' => 'Editar', 'href' => url],
            ['label' => 'Dar de baja', 'href' => url, 'danger' => true],
            ['label' => 'Recalcular', 'click' => "recalcular(5)"],  // wire:click
            ['label' => 'Editar nombre', 'js' => "$dispatch('abrir-modal', {id: 5})"], // Alpine
        ],
    ]
============================================================ --}}
@props([
    'rows',
    'columns' => [],

    'rowKey' => 'id',
    'keyPrefix' => 'fila',

    'selectable' => false,

    'sortField' => null,
    'sortDirection' => 'asc',
    'sortAction' => 'sortBy',

    'actions' => null,

    'mobileTitle' => null,
    'mobileSubtitle' => null,

    'toolbar' => true,
    'resultSingular' => 'registro encontrado',
    'resultPlural' => 'registros encontrados',
    'perPageModel' => null,
    'perPageOptions' => [10, 25, 50, 100],
    'sortModel' => null,
    'refreshAction' => null,

    'pageName' => 'page',

    'minWidth' => null,
    'emptyMessage' => 'No se encontraron registros.',
    'emptyTitle' => 'Sin resultados',
    'emptyVariant' => 'search',
])

@php
    /*
    |--------------------------------------------------------------------------
    | Normalización
    |--------------------------------------------------------------------------
    */

    $columnas = collect($columns)
        ->map(fn (array $columna) => $columna + [
            'value' => null,
            'default' => '—',
            'width' => null,
            'sort' => null,
            'type' => 'text',
            'strong' => false,
            'popover' => true,
            'desktop' => true,
            'mobile' => true,
            'mobileLabel' => null,
        ])
        ->values();

    $columnasEscritorio = $columnas->where('desktop', true)->values();
    $columnasMovil = $columnas->filter(fn ($c) => $c['mobile'] !== false)->values();

    $esPaginado = $rows instanceof \Illuminate\Contracts\Pagination\Paginator;
    $registros = $esPaginado ? collect($rows->items()) : collect($rows);

    $crudo = function ($row, array $columna) {
        $fuente = $columna['value'] ?? $columna['key'];

        return $fuente instanceof \Closure
            ? $fuente($row)
            : data_get($row, $fuente);
    };

    $lista = function ($row, array $columna) use ($crudo): array {
        return collect(\Illuminate\Support\Arr::wrap($crudo($row, $columna)))
            ->filter(fn ($item) => filled($item))
            ->map(fn ($item) => (string) $item)
            ->values()
            ->all();
    };

    $valor = function ($row, array $columna) use ($crudo, $lista): string {
        if ($columna['type'] === 'list') {
            $elementos = $lista($row, $columna);

            return $elementos !== []
                ? implode(', ', $elementos)
                : (string) $columna['default'];
        }

        $resultado = $crudo($row, $columna);

        return filled($resultado)
            ? (string) $resultado
            : (string) $columna['default'];
    };

    $estaVacio = fn ($row, array $columna): bool => blank($crudo($row, $columna));

    $columnasOrdenables = $columnas->filter(fn ($c) => filled($c['sort']))->values();

    $clave = fn ($row) => $rowKey instanceof \Closure
        ? $rowKey($row)
        : data_get($row, $rowKey);

    $acciones = function ($row) use ($actions): array {
        $definidas = $actions instanceof \Closure ? (array) $actions($row) : [];

        return [
            'detail' => $definidas['detail'] ?? null,
            'edit' => $definidas['edit'] ?? null,
            'menu' => array_values(array_filter($definidas['menu'] ?? [])),
        ];
    };

    $tieneAcciones = $actions instanceof \Closure;

    $totalColumnas = $columnasEscritorio->count()
        + ($selectable ? 1 : 0)
        + ($tieneAcciones ? 1 : 0);
@endphp

<div
    {{ $attributes->class([
        'relative',
        // Entrada suave al reemplazar el skeleton de carga.
        'ui-fade-in',
        'bg-transparent border-0 rounded-none overflow-visible',
        // Sin overflow-hidden: las listas desplegables de la barra no deben recortarse.
        'lg:bg-[var(--theme-surface)] lg:border lg:border-[var(--theme-border)] lg:rounded-lg',
    ]) }}
    wire:key="data-table-{{ $keyPrefix }}"
    data-data-table
    {{-- Densidad elegida en Ajustes › Preferencias --}}
    data-densidad="{{ app(\App\Services\PreferenciasUsuarioService::class)->valor('densidad') }}"
>

    {{-- ========================================================
        ACTUALIZANDO (buscar, filtrar, ordenar, paginar)
        Los datos actuales siguen visibles; solo una barra fina
        indeterminada arriba y el aviso «Actualizando…» en la barra
        de resultados (partials/toolbar). Aparece tras 200 ms para
        no parpadear en respuestas rápidas.
    ======================================================== --}}
    <div
        wire:loading.delay
        class="pointer-events-none absolute inset-x-0 top-0 z-20 h-0.5 overflow-hidden lg:rounded-t-lg"
        aria-hidden="true"
    >
        <div class="ui-progress-indeterminate h-full bg-[var(--theme-primary)]"></div>
    </div>


    {{-- ========================================================
        BARRA DE RESULTADOS
    ======================================================== --}}
    @if ($toolbar)
        @include('components.data-table.partials.toolbar')
    @endif


    {{-- ========================================================
        TABLA - ESCRITORIO
    ======================================================== --}}
    @include('components.data-table.partials.desktop')


    {{-- ========================================================
        TARJETAS - MÓVIL
    ======================================================== --}}
    @include('components.data-table.partials.mobile')


    {{-- ========================================================
        PAGINACIÓN
    ======================================================== --}}
    @if ($esPaginado)
        @include('components.data-table.partials.pagination')
    @endif

</div>
