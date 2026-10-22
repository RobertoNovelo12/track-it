@extends('layouts.app')

@section('title', 'Editar ubicación')

@section('content')

<livewire:catalogos.ubicacion-editor
    :ubicacion-id="$ubicacionId"
    :key="'editar-ubicacion-'.$ubicacionId"
/>

@endsection
