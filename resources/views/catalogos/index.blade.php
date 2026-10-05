@extends('layouts.app')

@section('title', 'Catálogos Base')

@section('content')

@include('catalogos.partials.marcas-modelos-alpine')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:catalogos.marcas-modelos />
    </div>

@endsection