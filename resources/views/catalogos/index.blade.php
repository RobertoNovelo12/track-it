@extends('layouts.app')

@section('title', 'Catálogos Base')

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:catalogos.marcas-modelos lazy />
    </div>

@endsection