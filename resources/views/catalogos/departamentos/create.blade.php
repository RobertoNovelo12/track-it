@extends('layouts.app')

@section('title', 'Nuevo departamento')

@section('content')

{{-- Mismo formulario que Editar (departamentoId = 0 crea uno nuevo). --}}
<livewire:catalogos.departamento-editor
    :departamento-id="0"
/>

@endsection
