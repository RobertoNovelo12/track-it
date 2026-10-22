@extends('layouts.app')

@section('title', 'Nuevo tipo de ubicación')

@section('content')

{{-- Mismo formulario que Editar (tipoId = 0 crea uno nuevo). --}}
<livewire:catalogos.tipo-ubicacion-editor :tipo-id="0" />

@endsection
