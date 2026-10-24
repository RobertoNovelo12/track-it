@extends('layouts.app')

@section('title', 'Nuevo programa')

@section('content')

{{-- Mismo formulario que Editar (programaId = 0 crea uno nuevo). --}}
<livewire:catalogos.programa-editor
    :programa-id="0"
/>

@endsection
