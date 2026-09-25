@props([
    'wireModel',
    'options',
    'label' => '',
    'placeholder' => 'Buscar...',
    'disabled' => false,
    'showClear' => true,
    'clearLabel' => 'Todos',
    'clearValue' => '',
    'compact' => false,
])

@php
    $optionsJson = collect($options)
        ->map(fn ($option) => [
            'value' => (string) $option['value'],
            'label' => (string) $option['label'],
        ])
        ->values()
        ->all();
@endphp


<div
    x-data="{
        open: false,

        query: '',

        selected: $wire.entangle('{{ $wireModel }}').live,

        options: @js($optionsJson),

        disabled: @js((bool) $disabled),

        showClear: @js((bool) $showClear),

        clearLabel: @js((string) $clearLabel),

        clearValue: @js($clearValue),


        get filtered() {
            const query =
                String(this.query ?? '')
                    .trim()
                    .toLowerCase();

            if (query === '') {
                return this.options;
            }

            return this.options.filter(option =>
                String(option.label ?? '')
                    .toLowerCase()
                    .includes(query)
            );
        },


        init() {
            this.syncSelectedLabel();

            this.$watch(
                'selected',
                () => {
                    if (!this.open) {
                        this.syncSelectedLabel();
                    }
                }
            );
        },


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
                    String(option.value) === selectedValue
            ) ?? null;
        },


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
                && String(this.selected) === String(this.clearValue)
            ) {
                this.query =
                    this.clearLabel;

                return;
            }

            this.query = '';
        },


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


        closeDropdown() {
            if (!this.open) {
                return;
            }

            this.open = false;

            this.syncSelectedLabel();
        },


        pick(value, label) {
            if (this.disabled) {
                return;
            }

            this.selected =
                value === ''
                    ? null
                    : value;

            this.query =
                label;

            this.open =
                false;
        },


        clear() {
            if (this.disabled) {
                return;
            }

            this.selected =
                this.clearValue === ''
                || this.clearValue === null
                    ? null
                    : this.clearValue;

            this.query =
                this.clearValue === ''
                || this.clearValue === null
                    ? ''
                    : this.clearLabel;

            this.open =
                false;
        },


        handleInput() {
            if (this.disabled) {
                return;
            }

            this.open =
                true;
        },
    }"

    @click.outside="closeDropdown()"

    @keydown.escape.window="closeDropdown()"

    class="relative"

    :class="
        disabled
            ? 'opacity-50'
            : ''
    "
>

    {{-- ============================================================
        LABEL
    ============================================================ --}}
    @if ($label)

        <label
            class="
                block

                text-xs
                text-[var(--theme-text-muted)]

                mb-1.5
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
            if (!open) {
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

            focus-within:ring-1
            focus-within:ring-[var(--theme-primary)]
            focus-within:border-[var(--theme-primary)]
        "

        :class="
            disabled
                ? 'bg-[var(--theme-surface-soft)] cursor-not-allowed'
                : 'bg-[var(--theme-surface)]'
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

            placeholder="{{ $placeholder }}"

            autocomplete="off"

            @class([
                'w-full',
                'min-w-0',

                'text-[var(--theme-text)]',

                'border-0',
                'bg-transparent',

                'pl-3',
                'pr-8',

                'placeholder:text-[var(--theme-text-muted)]',

                'focus:ring-0',

                'disabled:cursor-not-allowed',

                'text-xs py-2' => $compact,
                'text-sm py-2.5' => ! $compact,
            ])
        >


        {{-- FLECHA --}}
        <svg
            class="
                absolute

                right-2

                w-4
                h-4

                text-[var(--theme-text-muted)]

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

        {{-- LIMPIAR / VALOR GENERAL --}}
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


        {{-- OPCIONES --}}
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


        {{-- SIN RESULTADOS --}}
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