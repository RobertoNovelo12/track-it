@extends('layouts.app')

@section('title', 'Editar sede')

@section('content')

<x-form.page-header
    title="Editar sede"
    :crumbs="[
        ['label' => 'Administración', 'href' => route('catalogos.index')],
        ['label' => 'Sedes', 'href' => route('catalogos.index', ['section' => 'organizacion', 'tab' => 'sedes'])],
        ['label' => 'Editar'],
    ]"
/>

<livewire:catalogos.organizacion
    :sede-id="$sedeId"
    :key="'editar-sede-'.$sedeId"
/>

@endsection
