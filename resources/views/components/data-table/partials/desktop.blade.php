{{-- ============================================================
    TABLA - ESCRITORIO
============================================================ --}}
<div
    x-data="{
        open: false,

        label: '',
        value: '',

        editUrl: '#',
        canEdit: true,
        detailUrl: '#',

        top: 0,
        left: 0,

        copied: false,

        closeTimer: null,
        copiedTimer: null,

        toggleAll(source) {
            this.$root
                .querySelectorAll('.row-checkbox')
                .forEach(checkbox => {
                    checkbox.checked = source.checked;
                });
        },

        showPopover(element) {
            this.cancelClose();

            this.label = String(element.dataset.popoverLabel ?? '');
            this.value = String(element.dataset.popoverValue ?? '');

            this.editUrl = element.dataset.editUrl || '#';
            this.canEdit = element.dataset.canEdit === '1';
            this.detailUrl = element.dataset.detailUrl || '#';

            this.copied = false;
            this.open = true;

            this.$nextTick(() => {
                const rect = element.getBoundingClientRect();
                const popover = this.$refs.cellPopover;

                if (! popover) {
                    return;
                }

                const gap = 8;
                const width = popover.offsetWidth || 290;
                const height = popover.offsetHeight || 120;

                /* Horizontal: centrado sobre la celda, dentro de la ventana. */
                let left = rect.left + (rect.width / 2) - (width / 2);

                left = Math.max(12, Math.min(left, window.innerWidth - width - 12));

                /* Vertical: el acceso rápido siempre aparece arriba. */
                let top = rect.top - height - gap;

                this.left = Math.max(12, left);
                this.top = Math.max(12, top);
            });
        },

        scheduleClose() {
            this.cancelClose();

            this.closeTimer = setTimeout(() => {
                this.open = false;
            }, 180);
        },

        cancelClose() {
            if (! this.closeTimer) {
                return;
            }

            clearTimeout(this.closeTimer);

            this.closeTimer = null;
        },

        closePopover() {
            this.cancelClose();

            this.open = false;
        },

        async copyValue() {
            const text = String(this.value ?? '');

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

                this.copied = true;

                if (this.copiedTimer) {
                    clearTimeout(this.copiedTimer);
                }

                this.copiedTimer = setTimeout(() => {
                    this.copied = false;
                }, 1200);
            } catch (error) {
                this.copied = false;
            }
        }
    }"

    @keydown.escape.window="closePopover()"
    @resize.window="closePopover()"

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
        class="overflow-x-auto"
        @scroll="closePopover(); $dispatch('data-table-scroll')"
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
                                @click="toggleAll($event.target)"
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
                                    value="{{ $id }}"
                                    aria-label="Seleccionar registro"
                                    class="
                                        row-checkbox

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

                            <td
                                @class([
                                    'px-2.5 2xl:px-3 py-2.5 2xl:py-3 overflow-hidden',
                                    'text-[var(--theme-text)]' => $columna['type'] === 'text',
                                    'font-medium' => $columna['strong'],
                                ])
                            >
                                <div
                                    @if ($columna['popover'])
                                        data-popover-label="{{ $columna['label'] }}"
                                        data-popover-value="{{ $texto }}"
                                        data-edit-url="{{ $editUrl }}"
                                        data-can-edit="{{ $puedeEditar ? '1' : '0' }}"
                                        data-detail-url="{{ $detailUrl }}"

                                        @mouseenter="showPopover($event.currentTarget)"
                                        @mouseleave="scheduleClose()"
                                    @endif

                                    @class([
                                        'truncate' => $columna['type'] === 'text',
                                        'inline-flex' => $columna['type'] === 'status',
                                        'flex gap-1 overflow-hidden' => $columna['type'] === 'list',
                                        '-mx-1 px-1 py-1 rounded',
                                        'cursor-pointer hover:bg-[var(--theme-surface-soft)] transition-colors' => $columna['popover'],
                                    ])
                                >
                                    @if ($columna['type'] === 'status')
                                        <x-status-badge :status="$texto" />
                                    @elseif ($columna['type'] === 'list')
                                        @forelse ($lista($row, $columna) as $elemento)
                                            <span class="shrink-0 rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[11px] text-[var(--theme-text)] whitespace-nowrap">
                                                {{ $elemento }}
                                            </span>
                                        @empty
                                            <span class="truncate text-[var(--theme-text-muted)]">{{ $columna['default'] }}</span>
                                        @endforelse
                                    @else
                                        {{ $texto }}
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

                                        gap-2
                                    "
                                >

                                    {{-- VER DETALLE --}}
                                    @if ($accionesFila['detail'])
                                        <a
                                            href="{{ $accionesFila['detail'] }}"

                                            wire:navigate.hover

                                            class="
                                                shrink-0

                                                text-[var(--theme-primary)]

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
                                            >
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                    @endif


                                    {{-- MÁS ACCIONES --}}
                                    @if ($accionesFila['menu'] !== [])
                                        <button
                                            type="button"

                                            @click.stop="toggleMenu($el)"

                                            class="
                                                shrink-0

                                                text-[var(--theme-text-muted)]

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
                                            >
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                        <div
                                            x-ref="menu"
                                            x-show="menuOpen"
                                            x-cloak

                                            @click.outside="menuOpen = false"

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
                                        <span class="w-4 h-4 shrink-0" aria-hidden="true"></span>
                                    @endif

                                </div>

                            </td>
                        @endif

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="{{ $totalColumnas }}"

                            class="
                                h-28

                                px-5
                                py-12

                                text-center

                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $emptyMessage }}
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ============================================================
        ACCESO RÁPIDO DE CELDA

        La información superior no captura el puntero.
        Únicamente los botones mantienen abierto el popover.
    ============================================================ --}}
    <div
        x-ref="cellPopover"

        x-show="open"
        x-cloak

        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        :style="'top:' + top + 'px; left:' + left + 'px;'"

        class="
            fixed
            z-[120]

            w-[290px]
            max-w-[calc(100vw-24px)]

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-lg

            shadow-lg

            overflow-hidden

            pointer-events-none
        "
    >

        {{-- INFORMACIÓN SELECCIONADA --}}
        <div class="px-3 pt-2.5 pb-2 pointer-events-none">

            <div
                class="
                    text-[10px]
                    font-medium

                    uppercase
                    tracking-wide

                    text-[var(--theme-text-muted)]
                "
                x-text="label"
            ></div>

            <div
                class="
                    mt-1

                    max-h-20

                    overflow-hidden

                    break-words

                    text-xs
                    font-medium
                    leading-5

                    text-[var(--theme-text-strong)]
                "
                x-text="value"
            ></div>

        </div>


        {{-- ATAJOS --}}
        <div
            @mouseenter="cancelClose()"
            @mouseleave="scheduleClose()"
            @click.stop

            class="
                pointer-events-auto

                flex
                items-center

                gap-1

                px-2
                py-2

                border-t
                border-[var(--theme-border)]

                bg-[var(--theme-surface-soft)]
            "
        >

            {{-- COPIAR --}}
            <button
                type="button"

                @click="copyValue()"

                class="
                    h-8

                    flex-1

                    inline-flex
                    items-center
                    justify-center

                    gap-1.5

                    rounded-md

                    text-[11px]
                    font-medium

                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface)]

                    transition-colors
                "
            >
                <svg
                    class="w-3.5 h-3.5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="8" y="8" width="11" height="11" rx="2"/>
                    <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
                </svg>

                <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
            </button>


            {{-- EDITAR --}}
            <a
                x-show="canEdit"
                :href="editUrl"
                wire:navigate.hover

                @click="if (editUrl === '#') { $event.preventDefault(); }"

                class="
                    h-8

                    flex-1

                    inline-flex
                    items-center
                    justify-center

                    gap-1.5

                    rounded-md

                    text-[11px]
                    font-medium

                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface)]

                    transition-colors
                "
            >
                <svg
                    class="w-3.5 h-3.5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                </svg>

                <span>Editar</span>
            </a>


            {{-- DETALLE --}}
            <a
                x-show="detailUrl !== '#'"
                :href="detailUrl"

                wire:navigate.hover

                @click="if (detailUrl === '#') { $event.preventDefault(); }"

                class="
                    h-8

                    flex-1

                    inline-flex
                    items-center
                    justify-center

                    gap-1.5

                    rounded-md

                    text-[11px]
                    font-medium

                    text-[var(--theme-primary)]

                    hover:bg-[var(--theme-surface)]

                    transition-colors
                "
            >
                <svg
                    class="w-3.5 h-3.5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>

                <span>Detalle</span>
            </a>

        </div>

    </div>

</div>
