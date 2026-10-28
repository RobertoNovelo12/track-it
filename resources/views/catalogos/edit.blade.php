@extends('layouts.app')

@section(
    'title',
    $tipo === 'marca'
        ? 'Editar marca'
        : 'Editar modelo'
)

@section('content')

    <livewire:catalogos.editar-registro
        :tipo="$tipo"
        :registro-id="$registroId" />

@endsection
