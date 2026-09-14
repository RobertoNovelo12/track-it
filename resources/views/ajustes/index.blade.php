@extends('layouts.app')

@section('title', 'Ajustes')

@section('content')

<div>

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="mb-6">

        <h1
            class="
                text-xl
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Ajustes
        </h1>

    </div>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <livewire:ajustes-cuenta lazy />

</div>

@endsection