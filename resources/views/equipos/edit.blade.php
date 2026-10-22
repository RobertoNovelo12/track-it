@extends('layouts.app')

@section('title', 'Editar Equipo')

@section('content')

<div>

    {{-- «Volver» está en la fila de acciones del formulario (livewire/equipo-edit). --}}

    {{-- ============================================================
        COMPONENTE DE EDICIÓN
    ============================================================ --}}
    <livewire:equipo-edit
        :equipo-id="$equipoId"
    />

</div>

@endsection