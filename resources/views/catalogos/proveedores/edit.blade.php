@extends('layouts.app')

@section('title', 'Editar proveedor')

@section('content')

<livewire:catalogos.proveedor-editor
    :proveedor-id="$proveedorId"
/>

@endsection
