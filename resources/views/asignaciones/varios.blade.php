@extends('layouts.app')

@section('title', 'Asignar varios equipos')

@section('content')
    {{-- ?equipos=1,2,3 llega desde la barra de selección de Inventario. --}}
    <div class="w-full max-w-[1600px] mx-auto">
        <livewire:asignacion-varios
            lazy
            :equipos="(string) request()->query('equipos', '')"
            :volver="(string) request()->query('volver', '')"
        />
    </div>
@endsection
