@extends('layouts.app')

@section('title', 'Nuevo proveedor')

@section('content')

{{-- Mismo formulario que Editar (proveedorId = 0 crea uno nuevo). --}}
<livewire:catalogos.proveedor-editor
    :proveedor-id="0"
/>

@endsection
