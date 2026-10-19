@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')

<x-form.page-header
    title="Nuevo usuario"
    :crumbs="[
        ['label' => 'Inicio'],
        ['label' => 'Seguridad y Roles', 'href' => route('usuarios.index')],
        ['label' => 'Nuevo usuario'],
    ]"
/>

<livewire:usuario-create />

@endsection
