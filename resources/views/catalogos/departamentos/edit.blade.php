@extends('layouts.app')

@section('title', 'Editar departamento')

@section('content')

<x-form.page-header
    title="Editar departamento"
    :crumbs="[
        ['label' => 'Administración', 'href' => route('catalogos.index')],
        ['label' => 'Departamentos', 'href' => route('catalogos.index', ['section' => 'departamentos'])],
        ['label' => 'Editar'],
    ]"
/>

<livewire:catalogos.departamento-editor
    :departamento-id="$departamentoId"
/>

@endsection
