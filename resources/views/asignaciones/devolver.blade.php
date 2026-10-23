@extends('layouts.app')

@section('title', 'Devolución de equipo')

@section('content')

    <div class="mx-auto w-full max-w-[1600px]">
        {{-- ?equipo=ID: llega desde el detalle del equipo o la tabla de Inventario. --}}
        <livewire:devolucion-create :equipo="(int) request()->query('equipo') ?: null" />
    </div>

@endsection
