@extends('layouts.app')

@section('title', 'Editar '.mb_strtolower(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']))

@section('content')

@php
    $etiquetasUnidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
@endphp

<x-form.page-header
    :title="'Editar '.mb_strtolower($etiquetasUnidad['unidad_singular'])"
    :crumbs="[
        ['label' => 'Administración', 'href' => route('catalogos.index')],
        ['label' => $etiquetasUnidad['unidad_plural'], 'href' => route('catalogos.index', ['section' => 'hoteles'])],
        ['label' => 'Editar'],
    ]"
/>

<livewire:catalogos.hoteles
    :hotel-id="$hotelId"
    :key="'editar-hotel-'.$hotelId"
/>

@endsection
