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
    data-tabla-toolbar
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
    <div class="flex items-center gap-3">

        <p class="text-xs xl:text-sm text-[var(--theme-text)]">

            Resultados:

            <span class="font-medium">
                {{ number_format($total) }}
            </span>

            {{ $total === 1 ? $resultSingular : $resultPlural }}

        </p>

        {{-- Indicador discreto mientras se actualizan los datos --}}
        <span
            wire:loading.delay.flex
            @if (filled($loadingTarget ?? null)) wire:target="{{ $loadingTarget }}" @elseif (filled($loadingExcept ?? null)) wire:target.except="{{ $loadingExcept }}" @endif
            class="hidden items-center gap-1.5 text-xs text-[var(--theme-text-muted)]"
            role="status"
        >
            <x-ui.spinner size="sm" class="text-[var(--theme-primary)]" />
            Actualizando…
        </span>

    </div>


    {{-- ========================================================
        CONTROLES
    ======================================================== --}}
    {{-- Móvil: arriba «Ordenar por» + Actualizar; abajo «Mostrar» y la
         dirección (con order-*). Desde sm vuelve al orden normal. --}}
    @php
        // Clases escritas completas para que Tailwind las genere.
        $clasesOrden = [
            2 => 'order-2 sm:order-none',
            3 => 'order-3 sm:order-none',
            4 => 'order-4 sm:order-none',
        ];

        $ordenMovil = fn (int $n) => $columnasOrdenables->isNotEmpty() ? $clasesOrden[$n] : '';
    @endphp

    {{--
        Móvil con «Ordenar por»: rejilla de 2 columnas
        · fila 1: Ordenar por | Copiar + Actualizar
        · fila 2: Mostrar     | ASC / DESC
        Desde sm, una sola fila como antes.
    --}}
    <div @class([
        'w-full items-center gap-2 sm:flex sm:w-auto sm:flex-wrap sm:gap-x-4',
        'grid grid-cols-[minmax(0,1fr)_auto]' => $columnasOrdenables->isNotEmpty(),
        'flex flex-wrap' => $columnasOrdenables->isEmpty(),
    ])>

        {{-- Controles adicionales del módulo --}}
        {{ $toolbarActions ?? '' }}


        {{-- ====================================================
            ORDENAR POR (sin encabezados visibles)
            Lista con buscador (x-searchable-select) en modo local:
            al elegir un campo se llama a la acción de ordenamiento.
        ==================================================== --}}
        @if ($columnasOrdenables->isNotEmpty())
            <div
                class="order-1 flex min-w-0 items-center gap-2 sm:order-none lg:hidden"
                x-data="{ campo: @js((string) $sortField) }"
                x-init="$watch('campo', valor => { if (valor) $wire.call(@js($sortAction), valor) })"
            >
                <span class="w-[4.75rem] shrink-0 whitespace-nowrap text-xs text-[var(--theme-text-muted)] sm:w-auto">
                    Ordenar por
                </span>

                <div class="min-w-0 flex-1 sm:w-40 sm:flex-none">
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
            <div class="flex min-w-0 items-center gap-2 {{ $ordenMovil(3) }}">

                <span class="w-[4.75rem] shrink-0 whitespace-nowrap text-xs text-[var(--theme-text-muted)] sm:w-auto">
                    Mostrar
                </span>

                {{-- Móvil: ocupa el ancho de su columna (alineado con «Campo»); desde sm, fijo. --}}
                <div @class(['sm:w-20', 'min-w-0 flex-1 sm:flex-none' => $columnasOrdenables->isNotEmpty(), 'w-20' => $columnasOrdenables->isEmpty()])>
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
            {{-- Ancho fijo: en móvil define la 2.ª columna (bajo Copiar y Actualizar). --}}
            <div class="w-24 justify-self-end sm:justify-self-auto {{ $ordenMovil(4) }}">
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
            COPIAR TABLA (Markdown o JSON) Y ACTUALIZAR
            Copia las filas de la página que se está viendo, con
            las columnas de la tabla en escritorio.
        ==================================================== --}}
        @php
            $copiaColumnas = $columnasEscritorio
                ->reject(fn ($c) => in_array($c['type'] ?? null, ['download', 'actions'], true))
                ->values();

            $copiaFilas = $registros->map(fn ($r) => $copiaColumnas
                ->mapWithKeys(fn ($c) => [(string) $c['label'] => trim(preg_replace('/\s+/', ' ', strip_tags($valor($r, $c))))])
                ->all()
            )->values()->all();

            /*
             * JSON con nombres técnicos (sin espacios ni acentos): la clave de
             * la columna (codigo_inventario, numero_serie…) o, si no sirve,
             * la etiqueta en snake_case. Sin repetidos.
             */
            $usadasJson = [];
            $clavesJson = $copiaColumnas->mapWithKeys(function ($c) use (&$usadasJson) {
                $base = is_string($c['key'] ?? null) && preg_match('/^[a-z][a-z0-9_]*$/', $c['key'])
                    ? $c['key']
                    : (\Illuminate\Support\Str::snake(preg_replace('/[^A-Za-z0-9]+/', ' ', \Illuminate\Support\Str::ascii((string) $c['label']))) ?: 'campo');
                $base = trim(preg_replace('/_+/', '_', $base), '_') ?: 'campo';
                // «marca_nombre» → «marca», «estado_nombre» → «estado».
                $base = preg_replace('/_nombre$/', '', $base) ?: $base;
                $clave = $base;

                for ($n = 2; isset($usadasJson[$clave]); $n++) {
                    $clave = $base.'_'.$n;
                }

                $usadasJson[$clave] = true;

                return [(string) $c['label'] => $clave];
            })->all();

            /*
             * Filas para JSON. Una columna puede dar varios campos con
             * 'copiar' => fn ($row) => ['hotel' => …, 'departamento' => …]
             * (la tabla los muestra combinados; el JSON los separa).
             */
            $copiaJson = $registros->map(function ($r) use ($copiaColumnas, $clavesJson, $valor) {
                $objeto = [];

                foreach ($copiaColumnas as $c) {
                    if (($c['copiar'] ?? null) instanceof \Closure) {
                        foreach ((array) ($c['copiar'])($r) as $campo => $dato) {
                            $objeto[(string) $campo] = is_scalar($dato) || $dato === null ? $dato : (string) json_encode($dato);
                        }

                        continue;
                    }

                    $objeto[$clavesJson[(string) $c['label']]] = trim(preg_replace('/\s+/', ' ', strip_tags($valor($r, $c))));
                }

                return $objeto;
            })->values()->all();
        @endphp

        <div class="ml-auto flex shrink-0 items-center gap-2 justify-self-end sm:ml-0 sm:justify-self-auto {{ $ordenMovil(2) }}">{{-- Móvil: junto a «Ordenar por» --}}
            @if ($copiaFilas !== [])
                <div
                    class="relative"
                    x-data="{
                        abierto: false,
                        filas: @js($copiaFilas),
                        columnas: @js($copiaColumnas->pluck('label')->map(fn ($l) => (string) $l)->values()),
                        filasJson: @js($copiaJson),

                        json() {
                            return JSON.stringify(this.filasJson, null, 2);
                        },

                        markdown() {
                            const celda = (t) => String(t ?? '').replace(/\|/g, '\\|').replace(/\n/g, ' ');
                            const lineas = [
                                '| ' + this.columnas.map(celda).join(' | ') + ' |',
                                '| ' + this.columnas.map(() => '---').join(' | ') + ' |',
                                ...this.filas.map((f) => '| ' + this.columnas.map((c) => celda(f[c])).join(' | ') + ' |'),
                            ];

                            return lineas.join('\n');
                        },

                        async copiar(formato) {
                            this.abierto = false;
                            const texto = formato === 'json' ? this.json() : this.markdown();

                            try {
                                if (navigator.clipboard?.writeText) {
                                    await navigator.clipboard.writeText(texto);
                                } else {
                                    const area = document.createElement('textarea');
                                    area.value = texto;
                                    area.style.position = 'fixed';
                                    area.style.opacity = '0';
                                    document.body.appendChild(area);
                                    area.select();
                                    document.execCommand('copy');
                                    area.remove();
                                }

                                window.trackitToast?.('success', this.filas.length + (this.filas.length === 1 ? ' fila copiada' : ' filas copiadas') + ' como ' + (formato === 'json' ? 'JSON' : 'Markdown') + '.', 'Copiado');
                            } catch (error) {
                                window.trackitToast?.('error', 'El navegador no permitió copiar al portapapeles.', 'No se pudo copiar');
                            }
                        },
                    }"
                    x-on:click.outside="abierto = false"
                    x-on:keydown.escape.window="abierto = false"
                >
                    <button
                        type="button"
                        x-on:click="abierto = ! abierto"
                        x-bind:aria-expanded="abierto"
                        aria-haspopup="menu"
                        title="Copiar las filas de esta página"
                        class="flex h-[34px] items-center justify-center gap-1 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-2.5 text-xs font-medium text-[var(--theme-text)] transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)]"
                    >
                        <svg class="h-4 w-4 text-[var(--theme-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg>
                        <span class="hidden sm:inline">Copiar</span>
                        <span class="sr-only sm:hidden">Copiar</span>
                        <svg class="h-3.5 w-3.5 text-[var(--theme-text-muted)] transition-transform" x-bind:class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div
                        x-show="abierto"
                        x-cloak
                        x-transition.origin.top.right
                        role="menu"
                        class="absolute right-0 top-full z-30 mt-1 w-52 overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] py-1 text-left theme-shadow-xl"
                    >
                        <button type="button" role="menuitem" x-on:click="copiar('markdown')" class="block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                            Como Markdown <span class="block text-xs text-[var(--theme-text-muted)]">Tabla para documentos o chats</span>
                        </button>
                        <button type="button" role="menuitem" x-on:click="copiar('json')" class="block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                            Como JSON <span class="block text-xs text-[var(--theme-text-muted)]">Datos para otros sistemas</span>
                        </button>
                        <p class="border-t border-[var(--theme-border)] px-3 py-2 text-[11px] text-[var(--theme-text-muted)]">{{ count($copiaFilas) }} {{ count($copiaFilas) === 1 ? 'fila' : 'filas' }} de esta página</p>
                    </div>
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
                ><use href="{{ \App\Support\Icono::url('i-613266f') }}"></use></svg>
            </button>
        @endif

        </div>

    </div>

</div>
