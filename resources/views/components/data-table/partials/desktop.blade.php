{{-- ============================================================
    TABLA - ESCRITORIO
============================================================ --}}
<div
    x-data="{
        /* Celda copiada recientemente (fila|columna) para mostrar la palomita. */
        copiado: null,
        copiadoTimer: null,

        toggleAll(source) {
            this.$root
                .querySelectorAll('.row-checkbox:not(:disabled)')
                .forEach(checkbox => {
                    checkbox.checked = source.checked;
                });
        },

        async copiar(valor, clave) {
            const text = String(valor ?? '');

            if (text === '') {
                return;
            }

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const textarea = document.createElement('textarea');

                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    textarea.style.pointerEvents = 'none';

                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    textarea.remove();
                }

                this.copiado = clave;

                clearTimeout(this.copiadoTimer);

                this.copiadoTimer = setTimeout(() => {
                    this.copiado = null;
                }, 1200);
            } catch (error) {
                this.copiado = null;
            }
        }
    }"

    class="
        hidden
        lg:block
        relative
    "
>

    {{-- ========================================================
        CONTENEDOR CON SCROLL HORIZONTAL
    ======================================================== --}}
    <div
        class="overflow-x-auto transition-opacity duration-[var(--motion-base)]"
        wire:loading.delay.class="opacity-60"
        @if (filled($loadingTarget ?? null)) wire:target="{{ $loadingTarget }}" @elseif (filled($loadingExcept ?? null)) wire:target.except="{{ $loadingExcept }}" @endif
        @scroll="$dispatch('data-table-scroll')"
    >

        <table
            class="
                w-full
                table-fixed

                text-xs
                2xl:text-sm
            "
            @if ($minWidth) style="min-width: {{ $minWidth }};" @endif
        >

            {{-- ====================================================
                ANCHOS FIJOS DE COLUMNAS
            ==================================================== --}}
            <colgroup>
                @if ($selectable)
                    <col style="width: 56px;">
                @endif

                @foreach ($columnasEscritorio as $columna)
                    <col @if ($columna['width']) style="width: {{ $columna['width'] }};" @endif>
                @endforeach

                @if ($tieneAcciones)
                    <col style="width: 90px;">
                @endif
            </colgroup>


            {{-- ====================================================
                ENCABEZADOS
            ==================================================== --}}
            <thead>

                <tr
                    class="
                        h-11
                        2xl:h-12

                        border-b
                        border-[var(--theme-border)]

                        text-left
                    "
                >

                    @if ($selectable)
                        <th class="px-4 2xl:px-5 py-3">
                            <input
                                type="checkbox"
                                @if ($selectionAlpine)
                                    @php
                                        $filasPagina = $registros->filter(fn ($r) => ! ($rowSelectable instanceof \Closure && ! $rowSelectable($r)));
                                        $idsPagina = $filasPagina->map(fn ($r) => (string) $clave($r))->values()->all();
                                        $bajasPagina = $filasPagina
                                            ->filter(fn ($r) => $rowDecommissioned instanceof \Closure && $rowDecommissioned($r))
                                            ->map(fn ($r) => (string) $clave($r))->values()->all();
                                    @endphp
                                    x-bind:checked="paginaSeleccionada(@js($idsPagina))"
                                    @click="alternarPagina(@js($idsPagina), $event.target.checked, @js($bajasPagina))"
                                @elseif ($selectionModel)
                                    wire:click="{{ $selectPageAction }}"
                                    @checked($registros->isNotEmpty() && ($registros->every(fn ($row) => in_array((string) $clave($row), $selectedKeys)) || ($selectionLimit && count($selectedKeys) >= $selectionLimit && $registros->contains(fn ($row) => in_array((string) $clave($row), $selectedKeys)))))
                                @else
                                    @click="toggleAll($event.target)"
                                @endif
                                aria-label="Seleccionar todos"
                                class="rounded border-[var(--theme-border-strong)]"
                            >
                        </th>
                    @endif


                    @foreach ($columnasEscritorio as $columna)
                        <th
                            title="{{ $columna['label'] }}"
                            @if ($columna['sort'])
                                aria-sort="{{ $sortField === $columna['sort'] ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                            @endif
                            class="
                                px-2.5
                                2xl:px-3
                                py-3

                                font-medium
                                text-[var(--theme-text-muted)]

                                whitespace-nowrap
                            "
                        >
                            @if ($columna['sort'])
                                <button
                                    type="button"

                                    wire:click="{{ $sortAction }}('{{ $columna['sort'] }}')"

                                    class="
                                        w-full

                                        inline-flex
                                        items-center
                                        gap-1.5

                                        text-left

                                        hover:text-[var(--theme-text-strong)]

                                        transition-colors
                                    "
                                >
                                    <span class="truncate">
                                        {{ $columna['label'] }}
                                    </span>

                                    <span
                                        class="
                                            shrink-0

                                            inline-flex
                                            items-center
                                            justify-center

                                            w-4

                                            text-[10px]
                                            leading-none
                                        "
                                    >
                                        @if ($sortField === $columna['sort'])
                                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                        @else
                                            ↕
                                        @endif
                                    </span>
                                </button>
                            @else
                                <span class="block truncate">
                                    {{ $columna['label'] }}
                                </span>
                            @endif
                        </th>
                    @endforeach


                    @if ($tieneAcciones)
                        <th
                            class="
                                sticky
                                right-0
                                z-[1]

                                px-2.5
                                2xl:px-3
                                py-3

                                bg-[var(--theme-surface)]

                                shadow-[-8px_0_8px_-8px_var(--theme-shadow)]

                                font-medium
                                text-[var(--theme-text-muted)]

                                text-right

                                whitespace-nowrap
                            "
                        >
                            Acciones
                        </th>
                    @endif

                </tr>

            </thead>


            {{-- ====================================================
                REGISTROS
            ==================================================== --}}
            <tbody>

                @forelse ($registros as $row)

                    @php
                        $id = $clave($row);
                        $accionesFila = $acciones($row);

                        $detailUrl = $accionesFila['detail'] ?: '#';
                        $editUrl = $accionesFila['edit'] ?: '#';
                        $puedeEditar = filled($accionesFila['edit']);
                    @endphp

                    <tr
                        wire:key="{{ $keyPrefix }}-{{ $id }}"
                        @if ($highlightKey !== null && (string) $highlightKey === (string) $id)
                            style="background-color: var(--theme-primary-soft-subtle); box-shadow: inset 3px 0 var(--theme-primary);"
                            aria-label="Archivo recién generado"
                        @endif

                        class="
                            group

                            h-12
                            2xl:h-14

                            border-b
                            border-[var(--theme-border)]

                            hover:bg-[var(--theme-primary-soft-subtle)]

                            transition-colors
                        "
                    >

                        {{-- =============================================
                            SELECCIÓN
                        ============================================== --}}
                        @if ($selectable)
                            <td class="px-4 2xl:px-5 py-2.5 2xl:py-3">
                                <input
                                    type="checkbox"
                                    name="ids[]"
                                    data-dado-de-baja="{{ $rowDecommissioned instanceof \Closure && $rowDecommissioned($row) ? '1' : '0' }}"
                                    @disabled($rowSelectable instanceof \Closure && !$rowSelectable($row))
                                    value="{{ $id }}"
                                    @if ($selectionAlpine)
                                        x-bind:checked="estaSeleccionado(@js((string) $id))"
                                        @change="alternarSeleccion(@js((string) $id), $event.target.checked, @js($rowDecommissioned instanceof \Closure && $rowDecommissioned($row)))"
                                    @elseif ($selectionModel)
                                        wire:model.live="{{ $selectionModel }}"
                                        @disabled($selectionLimit && count($selectedKeys) >= $selectionLimit && !in_array((string) $id, $selectedKeys))
                                    @endif
                                    aria-label="Seleccionar registro"
                                    class="
                                        row-checkbox disabled:cursor-not-allowed disabled:opacity-40

                                        rounded

                                        border-[var(--theme-border-strong)]
                                    "
                                >
                            </td>
                        @endif


                        {{-- =============================================
                            CELDAS
                        ============================================== --}}
                        @foreach ($columnasEscritorio as $columna)
                            @php
                                $texto = $valor($row, $columna);
                            @endphp

                            @php
                                /*
                                 * Clic en el texto: Editar; si no se puede editar
                                 * (p. ej. equipo de baja), Ver detalle. Al pasar el
                                 * cursor aparece «Copiar» junto al texto.
                                 */
                                $conAcceso = $columna['popover'] && $columna['type'] !== 'download';
                                $enlace = $conAcceso
                                    ? ($puedeEditar ? $editUrl : ($accionesFila['detail'] ?: null))
                                    : null;
                                $copiable = $conAcceso
                                    && in_array($columna['type'], ['text', 'list'], true)
                                    && ! $estaVacio($row, $columna);
                                $claveCopia = $id . '|' . $columna['key'];
                                $claseEnlace = 'rounded-sm hover:text-[var(--theme-primary)] hover:underline underline-offset-2 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[var(--theme-primary)]';
                            @endphp

                            <td
                                @class([
                                    'group/celda px-2.5 2xl:px-3 py-2.5 2xl:py-3 overflow-hidden',
                                    'text-[var(--theme-text)]' => $columna['type'] === 'text',
                                    'font-medium' => $columna['strong'],
                                ])
                            >
                                <div class="flex min-w-0 items-center gap-1">
                                    @if ($columna['type'] === 'download')
                                        @include('components.data-table.partials.download')
                                    @elseif ($columna['type'] === 'status')
                                        @if ($enlace)
                                            <a href="{{ $enlace }}" wire:navigate.hover class="inline-flex rounded-sm hover:opacity-80">
                                                <x-status-badge :status="$texto" />
                                            </a>
                                        @else
                                            <span class="inline-flex"><x-status-badge :status="$texto" /></span>
                                        @endif
                                    @elseif ($columna['type'] === 'list')
                                        @php $elementosLista = $lista($row, $columna); @endphp

                                        @if ($elementosLista === [])
                                            <span class="truncate text-[var(--theme-text-muted)]">{{ $columna['default'] }}</span>
                                        @else
                                            <{{ $enlace ? 'a' : 'span' }}
                                                @if ($enlace) href="{{ $enlace }}" wire:navigate.hover @endif
                                                class="flex min-w-0 gap-1 overflow-hidden {{ $enlace ? 'rounded-sm hover:opacity-80' : '' }}"
                                            >
                                                @foreach ($elementosLista as $elemento)
                                                    <span class="shrink-0 rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[11px] text-[var(--theme-text)] whitespace-nowrap">
                                                        {{ $elemento }}
                                                    </span>
                                                @endforeach
                                            </{{ $enlace ? 'a' : 'span' }}>
                                        @endif
                                    @elseif ($enlace)
                                        <a href="{{ $enlace }}" wire:navigate.hover class="min-w-0 truncate {{ $claseEnlace }}">{{ $texto }}</a>
                                    @else
                                        <span class="min-w-0 truncate">{{ $texto }}</span>
                                    @endif

                                    @if ($copiable)
                                        <button
                                            type="button"
                                            @click.stop="copiar(@js($texto), @js($claveCopia))"
                                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[var(--theme-text-muted)] opacity-0 transition-opacity hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text-strong)] focus-visible:opacity-100 group-hover/celda:opacity-100"
                                            :class="copiado === @js($claveCopia) ? '!opacity-100 !text-[var(--theme-success,var(--theme-primary))]' : ''"
                                            :aria-label="copiado === @js($claveCopia) ? 'Copiado' : 'Copiar'"
                                            title="Copiar"
                                        >
                                            <svg x-show="copiado !== @js($claveCopia)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-d8c0e16') }}"></use></svg>
                                            <svg x-show="copiado === @js($claveCopia)" x-cloak class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endforeach


                        {{-- =============================================
                            ACCIONES
                        ============================================== --}}
                        @if ($tieneAcciones)
                            {{--
                                El menú usa posición fija (no lo recorta el scroll de la
                                tabla). Mientras está abierto, la celda sube de capa para
                                que la columna fija de las filas siguientes no lo tape.
                            --}}
                            <td
                                x-data="{
                                    menuOpen: false,
                                    menuTop: 0,
                                    menuLeft: 0,

                                    toggleMenu(boton) {
                                        if (this.menuOpen) {
                                            this.menuOpen = false;

                                            return;
                                        }

                                        const rect = boton.getBoundingClientRect();
                                        const ancho = 160;

                                        this.menuLeft = Math.max(8, Math.min(rect.right - ancho, window.innerWidth - ancho - 8));
                                        this.menuTop = rect.bottom + 4;
                                        this.menuOpen = true;

                                        /* Solo puede haber un menú abierto. */
                                        this.$dispatch('data-table-menu-open', this.$root);

                                        this.$nextTick(() => {
                                            const menu = this.$refs.menu;

                                            if (menu && this.menuTop + menu.offsetHeight > window.innerHeight - 8) {
                                                this.menuTop = Math.max(8, rect.top - menu.offsetHeight - 4);
                                            }
                                        });
                                    }
                                }"
                                @keydown.escape.window="menuOpen = false"
                                @scroll.window="menuOpen = false"
                                @resize.window="menuOpen = false"
                                @data-table-scroll.window="menuOpen = false"
                                @data-table-menu-open.window="if ($event.detail !== $el) menuOpen = false"
                                :style="menuOpen ? 'z-index: 40;' : ''"

                                class="
                                    sticky
                                    right-0

                                    px-2.5
                                    2xl:px-3
                                    py-2.5
                                    2xl:py-3

                                    bg-[var(--theme-surface)]
                                    group-hover:bg-[var(--theme-surface-soft)]

                                    shadow-[-8px_0_8px_-8px_var(--theme-shadow)]

                                    transition-colors
                                "
                            >

                                <div

                                    class="
                                        relative

                                        flex
                                        items-center
                                        justify-end

                                        gap-0.5
                                    "
                                >

                                    {{-- VER DETALLE (área táctil de 32 px; el icono no cambia) --}}
                                    @if ($accionesFila['detail'])
                                        <a
                                            href="{{ $accionesFila['detail'] }}"

                                            wire:navigate.hover

                                            class="
                                                inline-flex
                                                h-8
                                                w-8
                                                shrink-0
                                                items-center
                                                justify-center

                                                rounded-md

                                                text-[var(--theme-primary)]

                                                hover:bg-[var(--theme-primary-soft-subtle)]
                                                hover:text-[var(--theme-primary-hover)]
                                            "

                                            title="Ver detalle"
                                            aria-label="Ver detalle"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                aria-hidden="true"
                                            ><use href="{{ \App\Support\Icono::url('i-41f2153') }}"></use></svg>
                                        </a>
                                    @endif


                                    {{-- MÁS ACCIONES --}}
                                    @if ($accionesFila['menu'] !== [])
                                        <button
                                            type="button"

                                            @click.stop="toggleMenu($el)"

                                            class="
                                                inline-flex
                                                h-8
                                                w-8
                                                shrink-0
                                                items-center
                                                justify-center

                                                rounded-md

                                                text-[var(--theme-text-muted)]

                                                hover:bg-[var(--theme-surface-soft)]
                                                hover:text-[var(--theme-text-strong)]
                                            "

                                            title="Más acciones"
                                            aria-label="Más acciones"
                                            :aria-expanded="menuOpen"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            ><use href="{{ \App\Support\Icono::url('i-6f7d564') }}"></use></svg>
                                        </button>

                                        <div
                                            x-ref="menu"
                                            x-show="menuOpen"
                                            x-cloak

                                            @click.outside="menuOpen = false"
                                            @trackit-cerrar-menus.window="menuOpen = false"

                                            :style="'top:' + menuTop + 'px; left:' + menuLeft + 'px; z-index: 130;'"

                                            class="
                                                fixed

                                                w-40

                                                bg-[var(--theme-surface)]

                                                border
                                                border-[var(--theme-border)]

                                                rounded-md

                                                shadow-lg

                                                overflow-hidden

                                                text-left
                                            "
                                        >
                                            @include('components.data-table.partials.menu-items', [
                                                'items' => $accionesFila['menu'],
                                                'itemPadding' => 'py-2',
                                            ])
                                        </div>
                                    @else
                                        {{-- Reserva el espacio para alinear el icono de detalle --}}
                                        <span class="h-8 w-8 shrink-0" aria-hidden="true"></span>
                                    @endif

                                </div>

                            </td>
                        @endif

                    </tr>

                @empty

                    <tr>
                        <td colspan="{{ $totalColumnas }}">
                            <x-ui.empty-state
                                :variant="$emptyVariant"
                                :title="$emptyTitle"
                                :description="$emptyMessage"
                            />
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


</div>
