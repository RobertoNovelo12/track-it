{{-- ============================================================
    SKELETON DE TABLA
    ------------------------------------------------------------
    Misma estructura que la tabla de datos (components/data-table):
    barra de resultados, encabezados, filas con la misma altura,
    tarjetas en móvil y paginación.

    <x-ui.skeleton-table
        :columns="['ID Equipo', 'Nombre de equipo', 'Marca', 'Estado']"
        :rows="8"
    />

    Columnas con ancho y tipo:
        :columns="[
            ['label' => 'ID Equipo', 'width' => '140px'],
            ['label' => 'Estado', 'width' => '120px', 'type' => 'status'],
        ]"

    Props:
    - columns     Lista de encabezados (texto o arreglo label/width/type).
                  Un número crea esa cantidad de columnas sin título.
    - rows        Filas simuladas (5–8 recomendado).
    - selectable  Columna de casillas al inicio.
    - actions     Columna de acciones al final.
    - toolbar     Barra de resultados superior.
    - pagination  Paginación inferior.
    - min-width   Ancho mínimo de la tabla (igual que en la tabla real).
============================================================ --}}
@props([
    'columns' => 6,
    'rows' => 6,
    'selectable' => false,
    'actions' => true,
    'toolbar' => true,
    'pagination' => true,
    'minWidth' => null,
])

@php
    $columnas = collect(is_int($columns) ? range(1, $columns) : $columns)
        ->map(fn ($columna) => is_array($columna)
            ? $columna + ['label' => '', 'width' => null, 'type' => 'text']
            : ['label' => is_string($columna) ? $columna : '', 'width' => null, 'type' => 'text'])
        ->values();

    // Anchos variados para que las filas no se vean como una cuadrícula rígida.
    $anchos = ['w-3/4', 'w-1/2', 'w-2/3', 'w-5/6', 'w-3/5'];
@endphp

<div
    {{ $attributes->class([
        'relative',
        'lg:rounded-lg lg:border lg:border-[var(--theme-border)] lg:bg-[var(--theme-surface)]',
    ]) }}
    aria-busy="true"
    aria-live="polite"
>
    <span class="sr-only">Cargando registros…</span>

    {{-- ========================================================
        BARRA DE RESULTADOS
    ======================================================== --}}
    @if ($toolbar)
        <div class="mb-3 flex flex-col gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between lg:mb-0 lg:rounded-none lg:border-0 lg:border-b lg:bg-transparent lg:px-5 lg:py-4">
            <x-ui.skeleton class="h-4 w-48" />

            <div class="flex items-center gap-3">
                <x-ui.skeleton class="h-[34px] w-20" />
                <x-ui.skeleton class="h-[34px] w-24" />
                <x-ui.skeleton class="h-[34px] w-[34px]" />
            </div>
        </div>
    @endif


    {{-- ========================================================
        ESCRITORIO
    ======================================================== --}}
    <div class="hidden overflow-hidden lg:block">
        <table
            class="w-full table-fixed text-xs 2xl:text-sm"
            @if ($minWidth) style="min-width: {{ $minWidth }};" @endif
        >
            <colgroup>
                @if ($selectable)
                    <col style="width: 56px;">
                @endif

                @foreach ($columnas as $columna)
                    <col @if ($columna['width']) style="width: {{ $columna['width'] }};" @endif>
                @endforeach

                @if ($actions)
                    <col style="width: 90px;">
                @endif
            </colgroup>

            <thead>
                <tr class="h-11 border-b border-[var(--theme-border)] text-left 2xl:h-12">
                    @if ($selectable)
                        <th class="px-4 py-3 2xl:px-5">
                            <x-ui.skeleton class="h-4 w-4" />
                        </th>
                    @endif

                    @foreach ($columnas as $columna)
                        <th class="px-2.5 py-3 font-medium text-[var(--theme-text-muted)] 2xl:px-3">
                            @if ($columna['label'] !== '')
                                <span class="block truncate">{{ $columna['label'] }}</span>
                            @else
                                <x-ui.skeleton class="h-3 w-20" />
                            @endif
                        </th>
                    @endforeach

                    @if ($actions)
                        <th class="px-2.5 py-3 text-right font-medium text-[var(--theme-text-muted)] 2xl:px-3">
                            Acciones
                        </th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @for ($fila = 0; $fila < $rows; $fila++)
                    <tr class="h-12 border-b border-[var(--theme-border)] 2xl:h-14">
                        @if ($selectable)
                            <td class="px-4 py-2.5 2xl:px-5">
                                <x-ui.skeleton class="h-4 w-4" />
                            </td>
                        @endif

                        @foreach ($columnas as $indice => $columna)
                            <td class="px-2.5 py-2.5 2xl:px-3">
                                @if ($columna['type'] === 'status')
                                    <x-ui.skeleton variant="pill" class="h-5 w-16" />
                                @else
                                    <x-ui.skeleton class="h-3 {{ $anchos[($fila + $indice) % count($anchos)] }}" />
                                @endif
                            </td>
                        @endforeach

                        @if ($actions)
                            <td class="px-2.5 py-2.5 2xl:px-3">
                                <div class="flex justify-end gap-2">
                                    <x-ui.skeleton class="h-6 w-6" />
                                    <x-ui.skeleton class="h-6 w-6" />
                                </div>
                            </td>
                        @endif
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>


    {{-- ========================================================
        MÓVIL: TARJETAS
    ======================================================== --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
        @for ($fila = 0; $fila < min($rows, 4); $fila++)
            <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">
                <div class="flex items-start gap-3">
                    <x-ui.skeleton class="h-5 w-5" />

                    <div class="min-w-0 flex-1 space-y-2">
                        <x-ui.skeleton class="h-4 w-2/3" />
                        <x-ui.skeleton class="h-3 w-1/3" />
                    </div>

                    <x-ui.skeleton class="h-6 w-6" />
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    @for ($dato = 0; $dato < 4; $dato++)
                        <div class="space-y-1.5">
                            <x-ui.skeleton class="h-2.5 w-1/2" />
                            <x-ui.skeleton class="h-3 w-3/4" />
                        </div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>


    {{-- ========================================================
        PAGINACIÓN
    ======================================================== --}}
    @if ($pagination)
        <div class="mt-3 flex items-center justify-end gap-1 px-5 py-4 lg:mt-0 lg:border-t lg:border-[var(--theme-border)]">
            @for ($pagina = 0; $pagina < 5; $pagina++)
                <x-ui.skeleton variant="circle" class="h-8 w-8" />
            @endfor
        </div>
    @endif
</div>
