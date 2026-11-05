@extends('layouts.app')

@section('title', 'Importar marcas y modelos')

@section('content')
    {{-- Fila superior: «Volver» a la izquierda y plantillas a la derecha. --}}
    <x-form.page-header title="Importar marcas y modelos" :destino="route('catalogos.index', ['section' => 'marcas'])">
        <div class="ml-auto relative" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
            <button
                type="button"
                x-on:click="abierto = ! abierto"
                x-bind:aria-expanded="abierto"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:px-4 sm:text-sm"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
                Descargar plantilla
                <svg class="h-3.5 w-3.5 transition-transform" x-bind:class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div x-show="abierto" x-cloak x-transition.origin.top.right class="absolute right-0 z-30 mt-1 w-72 overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] py-1 theme-shadow-xl">
                <button type="button" x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.plantilla', ['formato' => 'xlsx'])))" class="block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                    Excel <span class="text-xs text-[var(--theme-text-muted)]">(hojas Marcas y Modelos)</span>
                </button>
                <button type="button" x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.plantilla', ['formato' => 'csv', 'clase' => 'marca'])))" class="block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                    CSV de marcas
                </button>
                <button type="button" x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.plantilla', ['formato' => 'csv', 'clase' => 'modelo'])))" class="block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]">
                    CSV de modelos
                </button>
            </div>
        </div>
    </x-form.page-header>

    <livewire:catalogos.marcas-modelos-importar lazy />
@endsection
