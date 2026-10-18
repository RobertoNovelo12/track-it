@extends('layouts.app')

@section('title', 'Nuevo mantenimiento')

@section('content')

<x-form.page-header
    title="Nuevo mantenimiento"
    :crumbs="[
        ['label' => 'Mantenimientos', 'href' => route('mantenimientos.index')],
        ['label' => 'Nuevo mantenimiento'],
    ]"
/>

<livewire:mantenimiento-create />

@endsection
