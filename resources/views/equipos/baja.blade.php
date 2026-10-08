@extends('layouts.app')

@section('title', 'Dar de baja equipo')

@section('content')

<div>

    <livewire:equipo-baja
        :equipo-id="$equipoId"
    />

</div>

@endsection