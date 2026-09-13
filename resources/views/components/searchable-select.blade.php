@props([
    'wireModel',
    'options',
    'label' => '',
    'placeholder' => 'Buscar...',
    'disabled' => false,
])

@php
    $optionsJson = collect($options)
        ->map(fn ($o) => ['value' => (string) $o['value'], 'label' => $o['label']])
        ->values();
@endphp

<div
    x-data="{
        open: false,
        query: '',
        options: @js($optionsJson),
        disabled: @js((bool) $disabled),
        get filtered() {
            if (this.query === '') return this.options;
            const q = this.query.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        pick(value, label) {
            if (this.disabled) return;
            this.query = label;
            this.open = false;
            this.$refs.hidden.value = value;
            this.$refs.hidden.dispatchEvent(new Event('input'));
        },
        clear() { this.pick('', ''); },
        openAndFocus() {
            if (this.disabled) return;
            this.open = true;
            this.$nextTick(() => this.$refs.search.focus());
        },
    }"
    @click.outside="open = false"
    class="relative"
    :class="disabled ? 'opacity-50' : ''"
>
    @if ($label)
        <label class="block text-xs text-[#50514F]/60 mb-1.5">{{ $label }}</label>
    @endif

    {{-- wire:model.live es clave: aquí solo se dispara 'input' cuando el
         usuario ELIGE una opción (no en cada tecla del buscador), así que
         usar .live es seguro y necesario para que campos dependientes
         (como los de equipo-create) reaccionen al instante. --}}
    <input type="hidden" x-ref="hidden" wire:model.live="{{ $wireModel }}">

    <div
        @click="openAndFocus()"
        class="relative flex items-center w-full border border-[#50514F]/15 rounded-md focus-within:ring-1 focus-within:ring-[#247BA0] focus-within:border-[#247BA0]"
        :class="disabled ? 'bg-[#50514F]/5 cursor-not-allowed' : ''"
    >
        <input
            type="text"
            x-ref="search"
            x-model="query"
            @focus="if (!disabled) open = true"
            @input="if (!disabled) open = true"
            :disabled="disabled"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full text-sm border-0 bg-transparent pl-3 pr-8 py-2 placeholder:text-[#50514F]/40 focus:ring-0 disabled:cursor-not-allowed"
        >

        <svg
            class="absolute right-2 w-4 h-4 text-[#50514F]/50 pointer-events-none transition-transform duration-150"
            :class="open ? 'rotate-180' : ''"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
        >
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </div>

    <div
        x-show="open && !disabled"
        x-cloak
        class="absolute z-30 mt-1 w-full bg-white border border-[#50514F]/15 rounded-md shadow-lg max-h-[184px] overflow-y-auto"
    >
        <div @click="clear()" class="px-3 py-2 text-sm text-[#50514F]/60 hover:bg-[#247BA0]/5 cursor-pointer">
            Todos
        </div>

        <template x-for="option in filtered" :key="option.value">
            <div @click="pick(option.value, option.label)" x-text="option.label"
                 class="px-3 py-2 text-sm text-[#50514F]/80 hover:bg-[#247BA0]/5 cursor-pointer"></div>
        </template>

        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-[#50514F]/40">
            Sin resultados
        </div>
    </div>
</div>