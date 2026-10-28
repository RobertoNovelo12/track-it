{{-- ============================================================
    BOTÓN CON ESTADO DE CARGA
    ------------------------------------------------------------
    Conserva su ancho en todos los estados (los tres textos ocupan
    la misma celda), muestra el spinner dentro y se deshabilita
    mientras procesa para evitar envíos duplicados.

    Con una acción de Livewire:
        <x-ui.button wire:click="save" target="save" loading-text="Guardando…">
            Guardar cambios
        </x-ui.button>

        <x-ui.button type="submit" target="save" loading-text="Creando…">
            Crear usuario
        </x-ui.button>

    Con Alpine (la carga la controla una variable):
        <x-ui.button x-loading="guardando" x-success="guardado" loading-text="Guardando…">
            Guardar
        </x-ui.button>

    Props:
    - variant       primary | secondary | danger | danger-soft | ghost
    - size          sm | compact | md | lg   (compact = modales, h-9)
    - type          button | submit
    - target        Método(s) de Livewire que activan la carga (wire:target).
    - disable-target  Métodos que deshabilitan el botón (por defecto, target).
                    Útil cuando dos botones no deben pulsarse a la vez.
    - x-loading     Expresión de Alpine que indica carga.
    - x-success     Expresión de Alpine que muestra el estado de éxito.
    - x-disabled    Expresión de Alpine que deshabilita (por defecto, x-loading).
    - loading-text  Texto mientras procesa (por defecto «Procesando…»).
    - success-text  Texto de éxito (por defecto «Listo»).
    - icon          Slot opcional con el icono del estado normal.
============================================================ --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'target' => null,
    'disableTarget' => null,
    'xLoading' => null,
    'xDisabled' => null,
    'xSuccess' => null,
    'loadingText' => 'Procesando…',
    'successText' => 'Listo',
])

@php
    $variantes = [
        'primary' => 'border border-transparent bg-[var(--theme-primary)] text-white hover:bg-[var(--theme-primary-hover)]',
        'secondary' => 'border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]',
        'danger' => 'border border-transparent bg-[var(--theme-danger)] text-white hover:opacity-90',
        'danger-soft' => 'border border-transparent bg-[var(--theme-danger-soft)] text-[var(--theme-danger)] hover:opacity-80',
        'ghost' => 'border border-transparent bg-transparent text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]',
    ];

    $tamanos = [
        'sm' => 'h-8 px-3 text-xs',
        'compact' => 'h-9 px-4 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
    ];

    $capa = 'col-start-1 row-start-1 inline-flex items-center justify-center gap-2 transition-opacity duration-[var(--motion-fast)]';
@endphp

<button
    type="{{ $type }}"

    {{ $attributes->class([
        'inline-flex select-none items-center justify-center rounded-[var(--radius-md)] font-medium',
        'transition-colors duration-[var(--motion-fast)]',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--theme-primary)] focus-visible:ring-offset-1 focus-visible:ring-offset-[var(--theme-surface)]',
        'disabled:cursor-not-allowed disabled:opacity-60',
        $variantes[$variant] ?? $variantes['primary'],
        $tamanos[$size] ?? $tamanos['md'],
    ]) }}

    @if ($target || $disableTarget)
        wire:loading.attr="disabled"
        wire:target="{{ $disableTarget ?? $target }}"
    @endif

    @if ($xLoading || $xDisabled)
        x-bind:disabled="{{ $xDisabled ?? $xLoading }}"
    @endif

    @if ($xLoading)
        x-bind:aria-busy="({{ $xLoading }}) ? 'true' : 'false'"
    @endif
>
    <span class="grid">
        {{-- Estado normal --}}
        <span
            class="{{ $capa }}"

            @if ($target)
                wire:loading.class="opacity-0"
                wire:target="{{ $target }}"
            @endif

            @if ($xLoading || $xSuccess)
                x-bind:class="{
                    'opacity-0': {{ $xLoading ?: 'false' }} || {{ $xSuccess ?: 'false' }}
                }"
            @endif
        >
            {{ $icon ?? '' }}
            <span>{{ $slot }}</span>
        </span>

        {{-- Procesando --}}
        <span
            class="{{ $capa }} opacity-0"
            aria-hidden="true"

            @if ($target)
                wire:loading.class.remove="opacity-0"
                wire:target="{{ $target }}"
            @endif

            @if ($xLoading)
                x-bind:class="{ 'opacity-0': ! ({{ $xLoading }}) }"
            @endif
        >
            <x-ui.spinner size="sm" />
            <span>{{ $loadingText }}</span>
        </span>

        {{-- Éxito (solo con x-success) --}}
        @if ($xSuccess)
            <span
                class="{{ $capa }} opacity-0"
                aria-hidden="true"
                x-bind:class="{ 'opacity-0': ! ({{ $xSuccess }}) }"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-e1c3743') }}"></use></svg>
                <span>{{ $successText }}</span>
            </span>
        @endif
    </span>
</button>
