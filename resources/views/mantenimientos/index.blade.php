@extends('layouts.app')

@section('title', 'Mantenimientos')

@section('content')

<div>
    <div
        class="
            mb-6
            flex flex-col
            gap-4
            sm:flex-row
            sm:items-center
            sm:justify-end
        "
    >

        <a
            href="{{ route('mantenimientos.create') }}"
            wire:navigate
            class="
                inline-flex
                items-center
                justify-center
                rounded-lg
                bg-[var(--theme-primary)]
                px-4
                py-2.5
                text-sm
                font-medium
                text-white
                transition
                hover:bg-[var(--theme-primary-hover)]
            "
        >
            Nuevo mantenimiento
        </a>
    </div>

    <livewire:mantenimientos-index />
</div>

@endsection
