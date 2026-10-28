@php
    $sections = [
        'cuenta' => [
            'label' => 'Cuenta',
            'icon' => 'user',
        ],

        'seguridad' => [
            'label' => 'Seguridad',
            'icon' => 'lock',
        ],

        'notificaciones' => [
            'label' => 'Notificaciones',
            'icon' => 'bell',
        ],

        'preferencias' => [
            'label' => 'Preferencias',
            'icon' => 'settings',
        ],
    ];
@endphp


{{-- La sección activa se agrega a la ruta de la barra superior
    (Inicio › Ajustes › Cuenta). --}}
<div
    x-data="{
        activeSection: @js($section),
        etiquetasSeccion: @js(collect($sections)->map(fn ($seccion) => $seccion['label'])->all()),
    }"

    x-effect="$dispatch('encabezado-seccion', { miga: etiquetasSeccion[activeSection] ?? '' })"

    class="mb-5"
>
    {{-- ============================================================
        MÓVIL - SELECT PERSONALIZADO
    ============================================================ --}}
    <div
        x-data="{
            open: false
        }"

        @click.outside="open = false"
        @trackit-cerrar-menus.window="open = false"

        @keydown.escape.window="
            open = false
        "

        class="
            relative
            sm:hidden
        "
    >
        {{-- ========================================================
            BOTÓN PRINCIPAL
        ======================================================== --}}
        <button
            type="button"

            @click="
                open = !open
            "

            :aria-expanded="open"

            class="
                w-full
                h-11

                px-3

                flex
                items-center
                justify-between
                gap-3

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border-strong)]

                text-sm
                font-medium
                text-[var(--theme-text)]

                hover:border-[var(--theme-primary-border)]

                focus:outline-none
                focus:ring-1
                focus:ring-[var(--theme-primary)]

                transition-colors
            "
        >
            <span
                class="
                    flex
                    items-center
                    gap-3

                    min-w-0
                "
            >
                {{-- ====================================================
                    ICONO ACTIVO
                ==================================================== --}}
                <span
                    class="
                        w-7
                        h-7
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-primary-soft)]
                        text-[var(--theme-primary)]
                    "
                >
                    {{-- CUENTA --}}
                    <svg
                        x-show="activeSection === 'cuenta'"
                        x-cloak

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-871b7a5') }}"></use></svg>


                    {{-- SEGURIDAD --}}
                    <svg
                        x-show="activeSection === 'seguridad'"
                        x-cloak

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-660e321') }}"></use></svg>


                    {{-- NOTIFICACIONES --}}
                    <svg
                        x-show="activeSection === 'notificaciones'"
                        x-cloak

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>


                    {{-- PREFERENCIAS --}}
                    <svg
                        x-show="activeSection === 'preferencias'"
                        x-cloak

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-7ed7b69') }}"></use></svg>
                </span>


                {{-- ====================================================
                    TEXTO ACTIVO
                ==================================================== --}}
                <span class="truncate">

                    @foreach ($sections as $key => $item)

                        <span
                            x-show="activeSection === '{{ $key }}'"
                            x-cloak
                        >
                            {{ $item['label'] }}
                        </span>

                    @endforeach

                </span>
            </span>


            {{-- ========================================================
                FLECHA
            ======================================================== --}}
            <svg
                class="
                    w-4
                    h-4
                    shrink-0

                    text-[var(--theme-text-muted)]

                    transition-transform
                    duration-200
                "

                :class="{
                    'rotate-180': open
                }"

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
        </button>


        {{-- ========================================================
            MENÚ MÓVIL
        ======================================================== --}}
        <div
            x-show="open"
            x-cloak

            x-transition:enter="
                transition
                ease-out
                duration-150
            "

            x-transition:enter-start="
                opacity-0
                -translate-y-1
                scale-[0.98]
            "

            x-transition:enter-end="
                opacity-100
                translate-y-0
                scale-100
            "

            x-transition:leave="
                transition
                ease-in
                duration-100
            "

            x-transition:leave-start="
                opacity-100
                scale-100
            "

            x-transition:leave-end="
                opacity-0
                scale-[0.98]
            "

            class="
                absolute
                left-0
                right-0
                top-full

                mt-2

                z-50

                p-1.5

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border-strong)]

                theme-shadow-xl
            "
        >
            @foreach ($sections as $key => $item)

                <button
                    type="button"

                    @click="
                        activeSection = '{{ $key }}';
                        open = false;
                    "

                    wire:click="setSection('{{ $key }}')"

                    wire:key="settings-mobile-{{ $key }}"

                    class="
                        w-full

                        px-3
                        py-2.5

                        flex
                        items-center
                        justify-between
                        gap-3

                        rounded-lg

                        text-left
                        text-sm

                        transition-colors
                    "

                    :class="
                        activeSection === '{{ $key }}'
                            ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)] font-medium'
                            : 'text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]'
                    "
                >
                    <span
                        class="
                            flex
                            items-center
                            gap-3

                            min-w-0
                        "
                    >
                        {{-- Icono --}}
                        <span
                            class="
                                w-7
                                h-7
                                shrink-0

                                rounded-lg

                                flex
                                items-center
                                justify-center
                            "

                            :class="
                                activeSection === '{{ $key }}'
                                    ? 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'
                                    : 'text-[var(--theme-text-muted)]'
                            "
                        >
                            @if ($item['icon'] === 'user')

                                <svg
                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                ><use href="{{ \App\Support\Icono::url('i-871b7a5') }}"></use></svg>

                            @elseif ($item['icon'] === 'lock')

                                <svg
                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                ><use href="{{ \App\Support\Icono::url('i-660e321') }}"></use></svg>

                            @elseif ($item['icon'] === 'bell')

                                <svg
                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>

                            @else

                                <svg
                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                ><use href="{{ \App\Support\Icono::url('i-7ed7b69') }}"></use></svg>

                            @endif
                        </span>


                        <span class="truncate">
                            {{ $item['label'] }}
                        </span>
                    </span>


                    {{-- Check activo --}}
                    <svg
                        x-show="activeSection === '{{ $key }}'"
                        x-cloak

                        class="
                            w-4
                            h-4
                            shrink-0
                        "

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    ><use href="{{ \App\Support\Icono::url('i-42997ad') }}"></use></svg>
                </button>

            @endforeach
        </div>
    </div>


    {{-- ============================================================
        TABLET / ESCRITORIO - PESTAÑAS
    ============================================================ --}}
    <div
        class="
            hidden
            sm:flex
            flex-wrap
            gap-2
        "
    >
        @foreach ($sections as $key => $item)

            <button
                type="button"

                @click="
                    activeSection = '{{ $key }}'
                "

                wire:click="setSection('{{ $key }}')"

                wire:key="settings-tab-{{ $key }}"

                class="
                    h-9
                    px-3

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    border

                    text-xs
                    font-medium

                    transition-colors
                "

                :class="
                    activeSection === '{{ $key }}'
                        ? 'bg-[var(--theme-primary-soft)] border-[var(--theme-primary-border)] text-[var(--theme-primary)]'
                        : 'bg-[var(--theme-surface)] border-[var(--theme-border)] text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] hover:border-[var(--theme-border-strong)]'
                "
            >
                @if ($item['icon'] === 'user')

                    <svg
                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-871b7a5') }}"></use></svg>

                @elseif ($item['icon'] === 'lock')

                    <svg
                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-660e321') }}"></use></svg>

                @elseif ($item['icon'] === 'bell')

                    <svg
                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>

                @else

                    <svg
                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    ><use href="{{ \App\Support\Icono::url('i-7ed7b69') }}"></use></svg>

                @endif


                {{ $item['label'] }}
            </button>

        @endforeach
    </div>
</div>