@extends('layouts.app')

@section('title', 'Asignación de Equipo')

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:asignacion-create lazy />
    </div>

@endsection