@extends('layouts.app')

@section('title', 'Nuevo registro')

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:catalogos.crear-registro lazy />
    </div>

@endsection