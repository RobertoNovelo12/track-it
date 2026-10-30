@extends('layouts.app')

@php
    $tipoRegistro = request()->query('tipo') === 'modelo' ? 'modelo' : 'marca';
@endphp

@section('title', $tipoRegistro === 'modelo' ? 'Nuevo modelo' : 'Nueva marca')

@section('content')

    <livewire:catalogos.crear-registro lazy :tipo="$tipoRegistro" />

@endsection
