@extends('layouts.app')

@section('title', 'Nueva Alta de Equipo')

@section('content')

{{-- Formulario Livewire --}}
{{-- ?duplicar=ID: copia los datos comunes de otro equipo (ver EquipoCreate). --}}
<livewire:equipo-create :duplicar="(int) request()->query('duplicar') ?: null" />

@endsection