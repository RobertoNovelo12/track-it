@extends('layouts.app')

@section('title', 'Generación de Reportes')

@section('content')

<div>
    <div class="mb-6">
    <h1 class="text-2xl font-bold text-[var(--theme-text-strong)]">
        Generación de reportes
    </h1>

    <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
        Busca activos, aplica filtros y genera documentos en Excel, PDF o CSV.
    </p>
</div>
    <livewire:reportes-index />
</div>

@endsection