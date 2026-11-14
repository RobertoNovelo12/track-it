@extends('layouts.app')

@section('title', 'Restablecer instancia')

@section('content')

<x-form.page-header
    title="Restablecer instancia"
    :crumbs="[
        ['label' => 'Administración', 'href' => route('catalogos.index')],
        ['label' => 'Plantillas', 'href' => route('catalogos.index', ['section' => 'plantillas'])],
        ['label' => 'Restablecer'],
    ]"
/>

<livewire:restablecer-instancia />

@endsection
