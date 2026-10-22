@extends('layouts.app')

@section('title', 'Tipos de ubicación')

@section('content')

<x-form.page-header title="Tipos de ubicación">
    <a
        href="{{ route('catalogos.index', ['section' => 'ubicaciones']) }}"
        wire:navigate.hover
        class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]"
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M15 6l-6 6 6 6"/>
        </svg>
        Volver a ubicaciones
    </a>
</x-form.page-header>

<livewire:catalogos.tipos-ubicacion />

@endsection
