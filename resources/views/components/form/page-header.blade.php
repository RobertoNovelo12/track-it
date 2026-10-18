{{-- ============================================================
    ENCABEZADO DE FORMULARIO (agregar / editar)
    ------------------------------------------------------------
    Mismo diseño que "Nueva Alta de equipo":

    <x-form.page-header
        title="Editar hotel"
        :crumbs="[
            ['label' => 'Administración', 'href' => route('catalogos.index')],
            ['label' => 'Hoteles', 'href' => route('catalogos.index', ['section' => 'hoteles'])],
            ['label' => 'Editar'],
        ]"
    >
        (opcional) botones a la derecha
    </x-form.page-header>
============================================================ --}}
@props([
    'title',
    'crumbs' => [],
])

<div class="mb-5 flex items-start justify-between gap-3 sm:mb-6 sm:gap-4">

    {{-- Título y ruta --}}
    <div class="min-w-0 flex-1">

        <h1 class="text-xl font-semibold leading-tight text-[var(--theme-text-strong)]">
            {{ $title }}
        </h1>

        @if ($crumbs !== [])
            <p class="mt-1 text-xs leading-relaxed text-[var(--theme-text-muted)]">
                @foreach ($crumbs as $crumb)
                    @if (! $loop->first)
                        <span class="mx-1" aria-hidden="true">›</span>
                    @endif

                    @if (! empty($crumb['href']))
                        <a
                            href="{{ $crumb['href'] }}"
                            wire:navigate.hover
                            class="transition-colors hover:text-[var(--theme-primary)]"
                        >{{ $crumb['label'] }}</a>
                    @else
                        <span>{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </p>
        @endif

    </div>

    {{-- Acciones opcionales --}}
    @if (trim(preg_replace('/<!--.*?-->/s', '', (string) $slot)) !== '')
        <div class="flex shrink-0 items-center gap-2">
            {{ $slot }}
        </div>
    @endif

</div>
