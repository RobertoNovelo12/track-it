@props([
    'wireModel',
    'options',
    'label' => '',
    'placeholder' => 'Buscar...',
    'disabled' => false,
    'showAllOnOpen' => false,
])

@php
    $optionsJson = collect($options)
        ->map(fn ($o) => [
            'value' => (string) $o['value'],
            'label' => $o['label'],
        ])
        ->values();
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selectedLabel: '',
        options: @js($optionsJson),
        disabled: @js((bool) $disabled),
        showAllOnOpen: @js((bool) $showAllOnOpen),

        init() {
            this.$nextTick(() => {
                this.syncSelectedFromHidden();
            });
        },

        get filtered() {
            if (this.query === '') {
                return this.options;
            }

            const q = this.query.toLowerCase();

            return this.options.filter(
                option => option.label.toLowerCase().includes(q)
            );
        },

        syncSelectedFromHidden() {
            const value = String(
                this.$refs.hidden?.value ?? ''
            );

            if (value === '') {
                this.selectedLabel = '';
                this.query = '';
                return;
            }

            const option = this.options.find(
                item => String(item.value) === value
            );

            if (option) {
                this.selectedLabel = option.label;
                this.query = option.label;
            }
        },

        pick(value, label) {
            if (this.disabled) {
                return;
            }

            this.selectedLabel = label;
            this.query = label;
            this.open = false;

            this.$refs.hidden.value = value;

            this.$refs.hidden.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        },

        clear() {
            if (this.disabled) {
                return;
            }

            this.selectedLabel = '';
            this.query = '';
            this.open = false;

            this.$refs.hidden.value = '';

            this.$refs.hidden.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        },

        openAndFocus() {
            if (this.disabled) {
                return;
            }

            if (!this.open && this.showAllOnOpen) {
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

            this.query = this.selectedLabel;
        },
    }"

    x-on:reportes-filtros-limpiados.window="
        selectedLabel = '';
        query = '';
        open = false;
    "

    x-on:mantenimientos-filtros-limpiados.window="
        selectedLabel = '';
        query = '';
        open = false;
    "

    @click.outside="closeDropdown()"
    @keydown.escape.window="closeDropdown()"

    class="relative"
    :class="disabled ? 'opacity-50' : ''"
>
    @if ($label)
        <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
            {{ $label }}
        </label>
    @endif

    {{--
        wire:model.live es clave:
        aquí solo se dispara 'input' cuando el usuario elige una opción.
    --}}
    <input
        type="hidden"
        x-ref="hidden"
        wire:model.live="{{ $wireModel }}"
    >

    <div
        @click="openAndFocus()"
        class="relative flex items-center w-full border border-[var(--theme-border-strong)] rounded-md focus-within:ring-1 focus-within:ring-[var(--theme-primary)] focus-within:border-[var(--theme-primary)]"
        :class="disabled ? 'bg-[var(--theme-surface-soft)] cursor-not-allowed' : ''"
    >
        <input
            type="text"
            x-ref="search"
            x-model="query"
            @focus="
                if (!disabled && !open) {
                    if (showAllOnOpen) {
                        query = '';
                    }

                    open = true;
                }
            "
            @input="
                if (!disabled) {
                    open = true;
                }
            "
            :disabled="disabled"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full text-sm text-[var(--theme-text)] border-0 bg-transparent pl-3 pr-8 py-2 placeholder:text-[var(--theme-text-muted)] focus:ring-0 disabled:cursor-not-allowed"
        >

        <svg
            class="absolute right-2 w-4 h-4 text-[var(--theme-text-muted)] pointer-events-none transition-transform duration-150"
            :class="open ? 'rotate-180' : ''"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
        >
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </div>

    <div
        x-show="open && !disabled"
        x-cloak
        class="absolute z-30 mt-1 w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md theme-shadow-xl max-h-[184px] overflow-y-auto"
    >
        <div
            @click="clear()"
            class="px-3 py-2 text-sm text-[var(--theme-text-muted)] hover:bg-[var(--theme-primary-soft-subtle)] cursor-pointer"
        >
            Todos
        </div>

        <template
            x-for="option in filtered"
            :key="option.value"
        >
            <div
                @click="pick(option.value, option.label)"
                x-text="option.label"
                class="px-3 py-2 text-sm text-[var(--theme-text)] hover:bg-[var(--theme-primary-soft-subtle)] cursor-pointer"
            ></div>
        </template>

        <div
            x-show="filtered.length === 0"
            class="px-3 py-2 text-sm text-[var(--theme-text-muted)]"
        >
            Sin resultados
        </div>
    </div>
</div>
