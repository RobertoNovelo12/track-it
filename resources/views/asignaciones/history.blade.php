@extends('layouts.app')

@section('title', 'Historial de Asignaciones y Movimientos')

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:asignaciones-history lazy />
    </div>

@endsection