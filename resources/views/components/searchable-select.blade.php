@props([
    'wireModel' => null,
    'xModel' => null,

    'options' => [],
    'xOptions' => null,

    'label' => '',

    'placeholder' => 'Buscar...',
    'xPlaceholder' => null,

    'disabled' => false,
    'xDisabled' => null,

    'showClear' => true,
    'clearLabel' => 'Todos',
    'clearValue' => '',

    'compact' => false,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Modo del componente
    |--------------------------------------------------------------------------
    |
    | wire-model=""
    |     Comportamiento tradicional Livewire.
    |
    | x-model=""
    |     Comportamiento completamente local con Alpine.
    |
    */

    $localMode =
        filled($xModel);

    $optionsJson =
        collect($options)
            ->map(fn ($option) => [
                ...$option,
                'value' => (string) ($option['value'] ?? ''),
                'label' => (string) ($option['label'] ?? ''),
            ])
            ->values()
            ->all();
@endphp


<div
    x-data="{
        open: false,
        query: '',

        /*
        |--------------------------------------------------------------------------
        | Valor seleccionado
        |--------------------------------------------------------------------------
        */

        selected:
            @if ($localMode)
                ''
            @elseif (filled($wireModel))
                $wire.entangle('{{ $wireModel }}').live
            @else
                ''
            @endif
        ,

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        options: @js($optionsJson),

        disabled:
            @js((bool) $disabled),

        currentPlaceholder:
            @js((string) $placeholder),

        showClear:
            @js((bool) $showClear),

        clearLabel:
            @js((string) $clearLabel),

        clearValue:
            @js($clearValue),


        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        */

        init() {
            this.$nextTick(() => {
                this.syncSelectedLabel();
            });

            this.$watch(
                'selected',
                () => {
                    if (!this.open) {
                        this.syncSelectedLabel();
                    }
                }
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Opciones dinámicas locales
        |--------------------------------------------------------------------------
        */

        normalizeOptions(value) {
            if (!Array.isArray(value)) {
                return [];
            }

            return value.map(option => ({
                ...option,

                value:
                    String(
                        option?.value ?? ''
                    ),

                label:
                    String(
                        option?.label ?? ''
                    ),
            }));
        },

        syncOptions(value) {
            this.options =
                this.normalizeOptions(value);

            this.$nextTick(() => {
                this.syncSelectedLabel();
            });
        },

        syncDisabled(value) {
            this.disabled =
                Boolean(value);

            if (this.disabled) {
                this.open = false;
                this.syncSelectedLabel();
            }
        },

        syncPlaceholder(value) {
            this.currentPlaceholder =
                String(
                    value ?? ''
                );
        },


        /*
        |--------------------------------------------------------------------------
        | Opciones filtradas
        |--------------------------------------------------------------------------
        */

        get filtered() {
            const query =
                String(
                    this.query ?? ''
                )
                    .trim()
                    .toLowerCase();

            if (query === '') {
                return this.options;
            }

            return this.options.filter(
                option =>
                    String(
                        option.label ?? ''
                    )
                        .toLowerCase()
                        .includes(query)
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Obtener opción seleccionada
        |--------------------------------------------------------------------------
        */

        selectedOption() {
            if (
                this.selected === null
                || this.selected === undefined
                || this.selected === ''
            ) {
                return null;
            }

            const selectedValue =
                String(this.selected);

            return this.options.find(
                option =>
                    String(option.value)
                    === selectedValue
            ) ?? null;
        },


        /*
        |--------------------------------------------------------------------------
        | Texto visible
        |--------------------------------------------------------------------------
        */

        syncSelectedLabel() {
            const option =
                this.selectedOption();

            if (option) {
                this.query =
                    option.label;

                return;
            }

            if (
                this.showClear
                && this.clearValue !== ''
                && this.clearValue !== null
                && this.selected !== null
                && this.selected !== undefined
                && String(this.selected)
                    === String(this.clearValue)
            ) {
                this.query =
                    this.clearLabel;

                return;
            }

            this.query = '';
        },


        /*
        |--------------------------------------------------------------------------
        | Abrir
        |--------------------------------------------------------------------------
        */

        openAndFocus() {
            if (this.disabled) {
                return;
            }

            if (!this.open) {
                this.query = '';
            }

            this.open = true;

            this.$nextTick(() => {
                this.$refs.search?.focus();
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Cerrar
        |--------------------------------------------------------------------------
        */

        closeDropdown() {
            if (!this.open) {
                return;
            }

            this.open = false;

            this.syncSelectedLabel();
        },


        /*
        |--------------------------------------------------------------------------
        | Flecha
        |--------------------------------------------------------------------------
        */

        toggleDropdown() {
            if (this.disabled) {
                return;
            }

            if (this.open) {
                this.closeDropdown();

                return;
            }

            this.openAndFocus();
        },


        /*
        |--------------------------------------------------------------------------
        | Seleccionar
        |--------------------------------------------------------------------------
        */

        pick(value, label) {
            if (this.disabled) {
                return;
            }

            this.selected =
                value === ''
                    ? null
                    : String(value);

            this.query =
                String(label ?? '');

            this.open = false;
        },


        /*
        |--------------------------------------------------------------------------
        | Limpiar
        |--------------------------------------------------------------------------
        */

        clear() {
            if (this.disabled) {
                return;
            }

            this.selected =
                this.clearValue === ''
                || this.clearValue === null
                    ? null
                    : String(this.clearValue);

            this.query =
                this.clearValue === ''
                || this.clearValue === null
                    ? ''
                    : this.clearLabel;

            this.open = false;
        },


        /*
        |--------------------------------------------------------------------------
        | Escritura
        |--------------------------------------------------------------------------
        */

        handleInput() {
            if (this.disabled) {
                return;
            }

            this.open = true;
        },
    }"

    @if ($localMode)
        x-modelable="selected"
        x-model="{{ $xModel }}"
    @endif

    @if (filled($xOptions))
        x-effect="syncOptions({{ $xOptions }})"
    @endif

    @if (filled($xDisabled))
        x-effect="syncDisabled({{ $xDisabled }})"
    @endif

    @if (filled($xPlaceholder))
        x-effect="syncPlaceholder({{ $xPlaceholder }})"
    @endif

    @click.outside="closeDropdown()"
    @keydown.escape.window="closeDropdown()"

    class="relative"
>

    {{-- ============================================================
        LABEL
    ============================================================ --}}
    @if ($label)
        <label
            class="
                block
                mb-1.5

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            {{ $label }}
        </label>
    @endif


{{-- ============================================================
    CONTROL
============================================================ --}}
<div
    @click="
        if (!disabled && !open) {
            openAndFocus();
        }
    "

    class="
        relative
        flex
        items-center
        w-full

        border
        border-[var(--theme-border-strong)]

        rounded-md

        bg-[var(--theme-surface)]

        focus-within:ring-1
        focus-within:ring-[var(--theme-primary)]
        focus-within:border-[var(--theme-primary)]
    "

    :class="
        disabled
            ? 'cursor-not-allowed'
            : ''
    "
>

    {{-- INPUT VISIBLE --}}
    <input
        type="text"

        x-ref="search"
        x-model="query"

        @focus="
            if (!disabled && !open) {
                openAndFocus();
            }
        "

        @input="handleInput()"

        :disabled="disabled"
        :placeholder="currentPlaceholder"

        autocomplete="off"

        @class([
            'w-full',
            'min-w-0',

            'text-[var(--theme-text)]',

            'border-0',
            'bg-transparent',

            'pl-3',
            'pr-10',

            'placeholder:text-[var(--theme-text-muted)]',

            'focus:ring-0',

            'disabled:cursor-not-allowed',

            'text-xs py-2' => $compact,
            'text-sm py-2.5' => ! $compact,
        ])
    >

    {{-- FLECHA --}}
    <button
        type="button"

        @click.stop="toggleDropdown()"

        :disabled="disabled"
        :aria-expanded="open ? 'true' : 'false'"

        aria-label="Abrir o cerrar opciones"

        class="
            absolute
            right-0
            top-0
            bottom-0

            w-9

            flex
            items-center
            justify-center

            text-[var(--theme-text-muted)]

            hover:text-[var(--theme-text)]

            disabled:cursor-not-allowed

            transition-colors
        "
    >
        <svg
            class="
                w-4
                h-4

                pointer-events-none

                transition-transform
                duration-150
            "

            :class="
                open
                    ? 'rotate-180'
                    : ''
            "

            viewBox="0 0 24 24"

            fill="none"
            stroke="currentColor"
            stroke-width="1.5"

            aria-hidden="true"
        >
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </button>

</div>


    {{-- ============================================================
        LISTADO
    ============================================================ --}}
    <div
        x-show="open && !disabled"
        x-cloak

        class="
            absolute
            z-30

            mt-1

            w-full
            min-w-full

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border-strong)]

            rounded-md

            theme-shadow-xl

            max-h-[184px]

            overflow-y-auto
        "
    >

        {{-- ========================================================
            LIMPIAR / VALOR GENERAL
        ======================================================== --}}
        @if ($showClear)
            <div
                @click="clear()"

                @class([
                    'text-[var(--theme-text-muted)]',
                    'hover:bg-[var(--theme-primary-soft-subtle)]',
                    'cursor-pointer',

                    'px-3 py-2 text-xs' => $compact,
                    'px-3 py-2 text-sm' => ! $compact,
                ])
            >
                {{ $clearLabel }}
            </div>
        @endif


        {{-- ========================================================
            OPCIONES
        ======================================================== --}}
        <template
            x-for="option in filtered"
            :key="option.value"
        >
            <div
                @click="
                    pick(
                        option.value,
                        option.label
                    )
                "

                x-text="option.label"

                @class([
                    'text-[var(--theme-text)]',
                    'hover:bg-[var(--theme-primary-soft-subtle)]',
                    'cursor-pointer',

                    'px-3 py-2 text-xs' => $compact,
                    'px-3 py-2 text-sm' => ! $compact,
                ])
            ></div>
        </template>


        {{-- ========================================================
            SIN RESULTADOS
        ======================================================== --}}
        <div
            x-show="filtered.length === 0"

            @class([
                'text-[var(--theme-text-muted)]',

                'px-3 py-2 text-xs' => $compact,
                'px-3 py-2 text-sm' => ! $compact,
            ])
        >
            Sin resultados
        </div>

    </div>

</div>