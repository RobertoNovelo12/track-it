@extends('layouts.app')

@section('title', 'Nueva ubicación')

@section('content')

{{-- Mismo formulario que Editar (ubicacionId = 0 crea una nueva). --}}
<livewire:catalogos.ubicacion-editor :ubicacion-id="0" />

@endsection
