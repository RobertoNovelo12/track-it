@extends('layouts.app')

@section('title', 'Reasignación de Equipo')

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:reasignacion-create lazy />
    </div>

@endsection 