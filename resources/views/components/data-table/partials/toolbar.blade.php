{{-- ============================================================
    BARRA DE RESULTADOS
    ------------------------------------------------------------
    - Escritorio (lg+): encabezado de la tabla.
    - Pantallas pequeñas: tarjeta propia sobre los registros,
      con selector "Ordenar por" porque no hay encabezados.
============================================================ --}}
@php
    $total = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $rows->total()
        : $registros->count();

@endphp

<div
    class="
        mb-3
        lg:mb-0

        flex
        flex-col
        gap-3

        sm:flex-row
        sm:items-center
        sm:justify-between

        px-4
        py-3

        lg:px-5
        lg:py-4

        rounded-xl
        lg:rounded-none

        border
        border-[var(--theme-border)]

        lg:border-0
        lg:border-b

        bg-[var(--theme-surface)]
        lg:bg-transparent
    "
>

    {{-- ========================================================
        RESULTADOS
    ======================================================== --}}
    <p class="text-xs xl:text-sm text-[var(--theme-text)]">

        Resultados:

        <span class="font-medium">
            {{ number_format($total) }}
        </span>

        {{ $total === 1 ? $resultSingular : $resultPlural }}

    </p>


    {{-- ========================================================
        CONTROLES
    ======================================================== --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">

        {{-- Controles adicionales del módulo --}}
        {{ $toolbarActions ?? '' }}


        {{-- ====================================================
            ORDENAR POR (sin encabezados visibles)
            Lista con buscador (x-searchable-select) en modo local:
            al elegir un campo se llama a la acción de ordenamiento.
        ==================================================== --}}
        @if ($columnasOrdenables->isNotEmpty())
            <div
                class="flex items-center gap-2 lg:hidden"
                x-data="{ campo: @js((string) $sortField) }"
                x-init="$watch('campo', valor => { if (valor) $wire.call(@js($sortAction), valor) })"
            >
                <span class="text-xs text-[var(--theme-text-muted)]">
                    Ordenar por
                </span>

                <div class="w-40">
                    <x-searchable-select
                        x-model="campo"
                        :options="$columnasOrdenables->map(fn ($columna) => ['value' => $columna['sort'], 'label' => $columna['label']])->values()->all()"
                        placeholder="Campo"
                        :show-clear="false"
                        compact
                    />
                </div>
            </div>
        @endif


        {{-- ====================================================
            CANTIDAD POR PÁGINA
        ==================================================== --}}
        @if ($perPageModel)
            <div class="flex items-center gap-2">

                <span class="text-xs text-[var(--theme-text-muted)]">
                    Mostrar
                </span>

                <div class="w-20">
                    <x-searchable-select
                        :wire-model="$perPageModel"
                        :options="collect($perPageOptions)->map(fn ($opcion) => ['value' => (string) $opcion, 'label' => (string) $opcion])->all()"
                        placeholder="10"
                        :show-clear="false"
                        compact
                    />
                </div>

                <span class="hidden sm:inline text-xs text-[var(--theme-text-muted)]">
                    Por página
                </span>

            </div>
        @endif


        {{-- ====================================================
            DIRECCIÓN DEL ORDENAMIENTO
        ==================================================== --}}
        @if ($sortModel)
            <div class="w-24">
                <x-searchable-select
                    :wire-model="$sortModel"
                    :options="[
                        ['value' => 'asc', 'label' => 'ASC'],
                        ['value' => 'desc', 'label' => 'DESC'],
                    ]"
                    placeholder="Orden"
                    :show-clear="false"
                    compact
                />
            </div>
        @endif


        {{-- ====================================================
            ACTUALIZAR TABLA
        ==================================================== --}}
        @if ($refreshAction)
            <button
                type="button"

                wire:click="{{ $refreshAction }}"

                wire:loading.attr="disabled"
                wire:target="{{ $refreshAction }}"

                title="Actualizar tabla"
                aria-label="Actualizar tabla"

                class="
                    w-[34px]
                    h-[34px]

                    shrink-0

                    flex
                    items-center
                    justify-center

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-primary)]
                    hover:border-[var(--theme-primary-border)]

                    disabled:opacity-60
                    disabled:cursor-not-allowed

                    transition-colors
                "
            >
                <svg
                    wire:loading.class="animate-spin"
                    wire:target="{{ $refreshAction }}"

                    class="w-4 h-4"

                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"

                    aria-hidden="true"
                >
                    <path d="M20 11a8 8 0 1 0-2.3 5.7"/>
                    <path d="M20 4v7h-7"/>
                </svg>
            </button>
        @endif

    </div>

</div>
