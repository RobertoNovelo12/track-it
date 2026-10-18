@extends('layouts.app')

@section('title', 'Editar mantenimiento')

@section('content')

<x-form.page-header
    title="Editar mantenimiento"
    :crumbs="[
        ['label' => 'Mantenimientos', 'href' => route('mantenimientos.index')],
        ['label' => 'Editar mantenimiento'],
    ]"
/>

<livewire:mantenimiento-edit
    :mantenimiento-id="$mantenimientoId"
/>

@endsection
