@extends('layouts.app')

@section('title', 'Papelera')

@section('content')

<div>
    <div class="mb-6">
        @include('layouts.partials.boton-volver')
    </div>

    <livewire:papelera-index />
</div>

@endsection
