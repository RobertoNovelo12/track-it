@extends('layouts.app')

@section('title', 'Editar hotel')

@section('content')
    <div class="mx-auto w-full max-w-[1600px]">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-[var(--theme-text-strong)]">
                    Editar hotel
                </h1>

                <nav
                    aria-label="Ubicación"
                    class="mt-1 flex items-center gap-2 text-xs text-[var(--theme-text-muted)]"
                >
                    <a
                        href="{{ route('catalogos.index') }}"
                        class="hover:text-[var(--theme-primary)]"
                    >
                        Administración
                    </a>

                    <span>›</span>

                    <a
                        href="{{ route('catalogos.index', ['section' => 'hoteles']) }}"
                        class="hover:text-[var(--theme-primary)]"
                    >
                        Hoteles
                    </a>

                    <span>›</span>
                    <span>Editar</span>
                </nav>
            </div>

            <a
                href="{{ route('catalogos.index', ['section' => 'hoteles']) }}"
                class="inline-flex items-center justify-center rounded-lg border border-[var(--theme-border)] px-4 py-2.5 text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
            >
                Volver a hoteles
            </a>
        </div>

        <livewire:catalogos.hoteles
            :hotel-id="$hotelId"
            :key="'editar-hotel-'.$hotelId"
        />
    </div>
@endsection