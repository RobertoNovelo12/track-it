@extends('layouts.app')

@section('title', 'Generación de reportes')

@section('content')

<div>
    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="mb-6">


        {{-- Descripción --}}
        <p
            class="
                text-sm
                text-[var(--theme-text-muted)]
            "
        >
            Busca activos, aplica filtros y genera documentos en Excel, PDF o CSV.
        </p>

    </div>


    {{-- ============================================================
        COMPONENTE DE REPORTES
    ============================================================ --}}
    <livewire:reportes-index />

</div>

@endsection