{{-- ============================================================
    TABLA - ESCRITORIO
============================================================ --}}
<div
    x-data="{
        open: false,

        label: '',
        value: '',

        editUrl: '#',
        detailUrl: '#',

        top: 0,
        left: 0,

        copied: false,

        closeTimer: null,
        copiedTimer: null,

        showPopover(element) {
            this.cancelClose();

            this.label =
                String(
                    element.dataset.popoverLabel
                    ?? ''
                );

            this.value =
                String(
                    element.dataset.popoverValue
                    ?? ''
                );

            this.editUrl =
                element.dataset.editUrl
                || '#';

            this.detailUrl =
                element.dataset.detailUrl
                || '#';

            this.copied = false;
            this.open = true;

            this.$nextTick(() => {
                const rect =
                    element.getBoundingClientRect();

                const popover =
                    this.$refs.cellPopover;

                if (! popover) {
                    return;
                }

                const gap = 8;

                const width =
                    popover.offsetWidth
                    || 290;

                const height =
                    popover.offsetHeight
                    || 120;


                /*
                |--------------------------------------------------------------------------
                | POSICIÓN HORIZONTAL
                |--------------------------------------------------------------------------
                */

                let left =
                    rect.left
                    + (rect.width / 2)
                    - (width / 2);

                left =
                    Math.max(
                        12,
                        Math.min(
                            left,
                            window.innerWidth
                                - width
                                - 12
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | POSICIÓN VERTICAL
                |--------------------------------------------------------------------------
                |
                | El acceso rápido siempre aparece arriba.
                |
                */

                let top =
                    rect.top
                    - height
                    - gap;

                top =
                    Math.max(
                        12,
                        top
                    );


                this.left =
                    Math.max(
                        12,
                        left
                    );

                this.top =
                    Math.max(
                        12,
                        top
                    );
            });
        },

        scheduleClose() {
            this.cancelClose();

            this.closeTimer =
                setTimeout(() => {
                    this.open = false;
                }, 180);
        },

        cancelClose() {
            if (! this.closeTimer) {
                return;
            }

            clearTimeout(
                this.closeTimer
            );

            this.closeTimer = null;
        },

        closePopover() {
            this.cancelClose();

            this.open = false;
        },

        async copyValue() {
            const text =
                String(
                    this.value
                    ?? ''
                );

            if (text === '') {
                return;
            }

            try {
                if (
                    navigator.clipboard
                    && window.isSecureContext
                ) {
                    await navigator.clipboard.writeText(
                        text
                    );
                } else {
                    const textarea =
                        document.createElement(
                            'textarea'
                        );

                    textarea.value =
                        text;

                    textarea.style.position =
                        'fixed';

                    textarea.style.opacity =
                        '0';

                    textarea.style.pointerEvents =
                        'none';

                    document.body.appendChild(
                        textarea
                    );

                    textarea.select();

                    document.execCommand(
                        'copy'
                    );

                    textarea.remove();
                }

                this.copied = true;

                if (this.copiedTimer) {
                    clearTimeout(
                        this.copiedTimer
                    );
                }

                this.copiedTimer =
                    setTimeout(() => {
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
        md:block
        relative
    "
>

    {{-- ========================================================
        CONTENEDOR CON SCROLL HORIZONTAL
    ======================================================== --}}
    <div
        class="overflow-x-auto"
        @scroll="closePopover()"
    >

            <table
                class="
                    w-full
                    min-w-[1500px]
                    table-fixed
                    text-sm
                "
            >

            {{-- ====================================================
                ANCHOS FIJOS DE COLUMNAS
            ==================================================== --}}
            <colgroup>

                {{-- Selección --}}
                <col class="w-[56px]">

                {{-- ID Equipo --}}
                <col class="w-[140px]">

                {{-- Nombre de equipo --}}
                <col class="w-[210px]">

                {{-- Tipo --}}
                <col class="w-[145px]">

                {{-- Marca --}}
                <col class="w-[110px]">

                {{-- Modelo --}}
                <col class="w-[165px]">

                {{-- Número de serie --}}
                <col class="w-[175px]">

                {{-- Dirección IP --}}
                <col class="w-[125px]">

                {{-- Estado --}}
                <col class="w-[125px]">

                {{-- Área / Departamento --}}
                <col class="w-[225px]">

                {{-- Acciones --}}
                <col class="w-[90px]">

            </colgroup>


            {{-- ====================================================
                ENCABEZADOS
            ==================================================== --}}
            <thead>

                <tr
                    class="
                        h-12

                        border-b
                        border-[var(--theme-border)]

                        text-left
                    "
                >

                    {{-- =============================================
                        SELECCIÓN
                    ============================================== --}}
                    <th class="px-5 py-3">

                        <input
                            type="checkbox"

                            onclick="toggleAllRows(this)"

                            class="
                                rounded
                                border-[var(--theme-border-strong)]
                            "
                        >

                    </th>


                    {{-- =============================================
                        ID EQUIPO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('codigo_inventario')"

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
                                ID Equipo
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
                                @if ($sortField === 'codigo_inventario')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        NOMBRE DE EQUIPO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('nombre_equipo')"

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
                                Nombre de equipo
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
                                @if ($sortField === 'nombre_equipo')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        TIPO DE EQUIPO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('tipo_equipo')"

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
                                Tipo de Equipo
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
                                @if ($sortField === 'tipo_equipo')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        MARCA
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('marca')"

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
                                Marca
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
                                @if ($sortField === 'marca')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        MODELO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('modelo')"

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
                                Modelo
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
                                @if ($sortField === 'modelo')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        NÚMERO DE SERIE
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('numero_serie')"

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
                                Número de serie
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
                                @if ($sortField === 'numero_serie')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        DIRECCIÓN IP
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('direccion_ip')"

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
                                Dirección IP
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
                                @if ($sortField === 'direccion_ip')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        ESTADO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('estado')"

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
                                Estado
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
                                @if ($sortField === 'estado')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        ÁREA / DEPARTAMENTO
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            whitespace-nowrap
                        "
                    >

                        <button
                            type="button"

                            wire:click="sortBy('ubicacion_organizacional')"

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
                                Área / Departamento
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
                                @if ($sortField === 'ubicacion_organizacional')
                                    {{ $sort === 'asc' ? '▲' : '▼' }}
                                @else
                                    ↕
                                @endif
                            </span>

                        </button>

                    </th>


                    {{-- =============================================
                        ACCIONES
                    ============================================== --}}
                    <th
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text-muted)]

                            text-right

                            whitespace-nowrap
                        "
                    >
                        Acciones
                    </th>

                </tr>

            </thead>


            {{-- ====================================================
                REGISTROS
            ==================================================== --}}
            <tbody>

                @forelse ($equipos as $equipo)

                    @php
                        $detailUrl = Route::has('equipos.show')
                            ? route(
                                'equipos.show',
                                $equipo->id_equipo
                            )
                            : '#';

                        $editUrl = Route::has('equipos.edit')
                            ? route(
                                'equipos.edit',
                                $equipo->id_equipo
                            )
                            : '#';
                    @endphp


                    <tr
                        wire:key="equipo-{{ $equipo->id_equipo }}"

                        class="
                            h-14

                            border-b
                            border-[var(--theme-border)]

                            hover:bg-[var(--theme-primary-soft-subtle)]

                            transition-colors
                        "
                    >

                        {{-- =============================================
                            SELECCIÓN
                        ============================================== --}}
                        <td class="px-5 py-3">

                            <input
                                type="checkbox"

                                name="ids[]"

                                value="{{ $equipo->id_equipo }}"

                                class="
                                    row-checkbox

                                    rounded

                                    border-[var(--theme-border-strong)]
                                "
                            >

                        </td>


                        {{-- =============================================
                            ID EQUIPO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                font-medium
                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="ID Equipo"
                                data-popover-value="{{ $equipo->codigo_inventario }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->codigo_inventario }}
                            </div>

                        </td>


                        {{-- =============================================
                            NOMBRE DE EQUIPO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Nombre de equipo"
                                data-popover-value="{{ $equipo->nombre_equipo ?? '—' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->nombre_equipo ?? '—' }}
                            </div>

                        </td>


                        {{-- =============================================
                            TIPO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Tipo de equipo"
                                data-popover-value="{{ $equipo->tipo_equipo_nombre ?? '—' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->tipo_equipo_nombre ?? '—' }}
                            </div>

                        </td>


                        {{-- =============================================
                            MARCA
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Marca"
                                data-popover-value="{{ $equipo->marca_nombre ?? '—' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->marca_nombre ?? '—' }}
                            </div>

                        </td>


                        {{-- =============================================
                            MODELO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Modelo"
                                data-popover-value="{{ $equipo->modelo_nombre ?? 'N/A' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->modelo_nombre ?? 'N/A' }}
                            </div>

                        </td>


                        {{-- =============================================
                            NÚMERO DE SERIE
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Número de serie"
                                data-popover-value="{{ $equipo->numero_serie ?? 'NA' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->numero_serie ?? 'NA' }}
                            </div>

                        </td>


                        {{-- =============================================
                            DIRECCIÓN IP
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Dirección IP"
                                data-popover-value="{{ $equipo->direccion_ip ?? 'NA' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{ $equipo->direccion_ip ?? 'NA' }}
                            </div>

                        </td>


                        {{-- =============================================
                            ESTADO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden
                            "
                        >

                            <div
                                data-popover-label="Estado"
                                data-popover-value="{{ $equipo->estado_nombre ?? 'Sin estado' }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    inline-flex

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >

                                <x-status-badge
                                    :status="
                                        $equipo->estado_nombre
                                        ?? 'Sin estado'
                                    "
                                />

                            </div>

                        </td>


                        {{-- =============================================
                            ÁREA / DEPARTAMENTO
                        ============================================== --}}
                        <td
                            class="
                                px-3
                                py-3

                                overflow-hidden

                                text-[var(--theme-text)]
                            "
                        >

                            <div
                                data-popover-label="Área / Departamento"
                                data-popover-value="{{
                                    $equipo->ubicacion_organizacional
                                    ?? '—'
                                }}"
                                data-edit-url="{{ $editUrl }}"
                                data-detail-url="{{ $detailUrl }}"

                                @mouseenter="showPopover($event.currentTarget)"
                                @mouseleave="scheduleClose()"

                                class="
                                    truncate

                                    -mx-1
                                    px-1
                                    py-1

                                    rounded

                                    cursor-pointer

                                    hover:bg-[var(--theme-surface-soft)]

                                    transition-colors
                                "
                            >
                                {{
                                    $equipo->ubicacion_organizacional
                                    ?? '—'
                                }}
                            </div>

                        </td>


                        {{-- =============================================
                            ACCIONES
                        ============================================== --}}
                        <td class="px-3 py-3">

                            <div
                                class="
                                    relative

                                    flex
                                    items-center
                                    justify-end

                                    gap-2
                                "
                            >

                                {{-- =====================================
                                    VER DETALLE
                                ====================================== --}}
                                    <a
                                        href="{{ $detailUrl }}"

                                        wire:navigate.hover

                                        class="
                                            shrink-0

                                            text-[var(--theme-primary)]

                                            hover:text-[var(--theme-primary-hover)]
                                        "

                                        title="Ver detalle"
                                    >

                                    <svg
                                        class="w-4 h-4"

                                        viewBox="0 0 24 24"

                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"

                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"
                                        />

                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="3"
                                        />
                                    </svg>

                                </a>


                                {{-- =====================================
                                    MÁS ACCIONES
                                ====================================== --}}
                                <button
                                    type="button"

                                    onclick="
                                        toggleRowMenu(
                                            event,
                                            'menu-{{ $equipo->id_equipo }}'
                                        )
                                    "

                                    class="
                                        shrink-0

                                        text-[var(--theme-text-muted)]

                                        hover:text-[var(--theme-text-strong)]
                                    "

                                    title="Más acciones"
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


                                {{-- =====================================
                                    MENÚ
                                ====================================== --}}
                                <div
                                    id="menu-{{ $equipo->id_equipo }}"

                                    class="
                                        row-menu
                                        hidden

                                        absolute

                                        right-0
                                        top-6

                                        w-40

                                        bg-[var(--theme-surface)]

                                        border
                                        border-[var(--theme-border)]

                                        rounded-md

                                        shadow-md

                                        z-20

                                        text-left
                                    "
                                >

                                    {{-- EDITAR --}}
                                    <a
                                        href="{{ $editUrl }}"
                                        wire:navigate.hover

                                        class="
                                            block

                                            px-4
                                            py-2

                                            text-xs
                                            text-[var(--theme-text)]

                                            hover:bg-[var(--theme-surface-soft)]
                                        "
                                    >
                                        Editar
                                    </a>


                                    {{-- DAR DE BAJA --}}
                                    <button
                                        type="button"

                                        class="
                                            w-full
                                            block

                                            text-left

                                            px-4
                                            py-2

                                            text-xs

                                            text-[var(--theme-danger)]

                                            hover:bg-[var(--theme-danger-soft)]
                                        "
                                    >
                                        Dar de baja
                                    </button>

                                </div>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="11"

                            class="
                                h-28

                                px-5
                                py-12

                                text-center

                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            No se encontraron equipos con los filtros seleccionados.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ============================================================
        POPOVER RÁPIDO DE CELDA

        La información superior no captura el puntero.
        Únicamente los botones mantienen abierto el popover.
    ============================================================ --}}
    <div
        x-ref="cellPopover"

        x-show="open"
        x-cloak

        x-transition:enter="
            transition
            ease-out
            duration-100
        "

        x-transition:enter-start="
            opacity-0
            translate-y-1
        "

        x-transition:enter-end="
            opacity-100
            translate-y-0
        "

        x-transition:leave="
            transition
            ease-in
            duration-75
        "

        x-transition:leave-start="
            opacity-100
        "

        x-transition:leave-end="
            opacity-0
        "

        :style="
            'top:'
            + top
            + 'px; left:'
            + left
            + 'px;'
        "

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

        {{-- ========================================================
            INFORMACIÓN SELECCIONADA
        ======================================================== --}}
        <div
            class="
                px-3
                pt-2.5
                pb-2

                pointer-events-none
            "
        >

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


        {{-- ========================================================
            ATAJOS
        ======================================================== --}}
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

            {{-- ====================================================
                COPIAR
            ==================================================== --}}
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
                    class="
                        w-3.5
                        h-3.5

                        shrink-0
                    "

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"

                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"

                    aria-hidden="true"
                >
                    <rect
                        x="8"
                        y="8"
                        width="11"
                        height="11"
                        rx="2"
                    />

                    <path
                        d="
                            M16 8V5
                            a2 2 0 0 0-2-2
                            H5
                            a2 2 0 0 0-2 2
                            v9
                            a2 2 0 0 0 2 2
                            h3
                        "
                    />
                </svg>


                <span
                    x-text="
                        copied
                            ? 'Copiado'
                            : 'Copiar'
                    "
                ></span>

            </button>


            {{-- ====================================================
                EDITAR
            ==================================================== --}}
            <a
                :href="editUrl"
                wire:navigate.hover

                @click="
                    if (editUrl === '#') {
                        $event.preventDefault();
                    }
                "

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
                    class="
                        w-3.5
                        h-3.5

                        shrink-0
                    "

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"

                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"

                    aria-hidden="true"
                >
                    <path d="M12 20h9"/>

                    <path
                        d="
                            M16.5 3.5
                            a2.1 2.1 0 0 1 3 3

                            L8 18

                            l-4 1

                            1-4

                            Z
                        "
                    />
                </svg>


                <span>
                    Editar
                </span>

            </a>


            {{-- ====================================================
                DETALLE
            ==================================================== --}}
            <a
                :href="detailUrl"

                wire:navigate.hover

                @click="
                    if (detailUrl === '#') {
                        $event.preventDefault();
                    }
                "

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
                    class="
                        w-3.5
                        h-3.5

                        shrink-0
                    "

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"

                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"

                    aria-hidden="true"
                >
                    <path
                        d="
                            M2 12
                            s3.5-7 10-7
                            10 7 10 7
                            -3.5 7-10 7
                            -10-7-10-7z
                        "
                    />

                    <circle
                        cx="12"
                        cy="12"
                        r="3"
                    />
                </svg>


                <span>
                    Detalle
                </span>

            </a>

        </div>

    </div>

</div>