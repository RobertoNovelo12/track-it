@extends('layouts.app')

@section('title', 'Nueva sede')

@section('content')

<x-form.page-header
    title="Nueva sede"
    :crumbs="[
        ['label' => 'Administración', 'href' => route('catalogos.index')],
        ['label' => 'Sedes', 'href' => route('catalogos.index', ['section' => 'organizacion', 'tab' => 'sedes'])],
        ['label' => 'Nueva'],
    ]"
/>

<livewire:catalogos.organizacion
    :nueva-sede="true"
    :key="'nueva-sede'"
/>

@endsection
