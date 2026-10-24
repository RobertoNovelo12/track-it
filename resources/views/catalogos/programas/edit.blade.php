@extends('layouts.app')

@section('title', 'Editar programa')

@section('content')

<livewire:catalogos.programa-editor
    :programa-id="$programaId"
/>

@endsection
