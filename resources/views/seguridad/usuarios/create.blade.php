@extends('layouts.app')

@php($soloInventario = $soloInventario ?? false)

@section('title', $soloInventario ? 'Nueva persona sin acceso' : 'Nuevo usuario')

@section('content')

<x-form.page-header
    :title="$soloInventario ? 'Nueva persona sin acceso' : 'Nuevo usuario'"
    :crumbs="[
        ['label' => 'Inicio'],
        ['label' => 'Seguridad y Roles', 'href' => route('usuarios.index')],
        ['label' => $soloInventario ? 'Nueva persona sin acceso' : 'Nuevo usuario'],
    ]"
/>

<livewire:usuario-create :solo-inventario="$soloInventario" />

@endsection
