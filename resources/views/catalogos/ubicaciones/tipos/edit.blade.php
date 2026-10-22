@extends('layouts.app')

@section('title', 'Editar tipo de ubicación')

@section('content')

<livewire:catalogos.tipo-ubicacion-editor
    :tipo-id="$tipoId"
    :key="'editar-tipo-ubicacion-'.$tipoId"
/>

@endsection
