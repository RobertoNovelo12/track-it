@extends('layouts.app')

@section('title', 'Detalle del Equipo')

@section('content')

<div>

    {{-- «Volver» lo agrega el layout (layouts/partials/boton-volver). --}}


    {{-- ============================================================
        COMPONENTE DE DETALLE
    ============================================================ --}}
    <livewire:equipo-show
        :equipo-id="$equipoId"
    />

</div>

@endsection