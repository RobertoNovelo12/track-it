@extends('layouts.app')

@section('title', 'Dar de baja equipo')

@section('content')

<div>

    {{-- ?cola=1,2,3: baja en lote desde la selección de Inventario --}}
    <livewire:equipo-baja
        :equipo-id="$equipoId"
        :lote="request()->query('cola')"
    />

</div>

@endsection