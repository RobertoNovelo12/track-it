@extends('layouts.app')

@section(
    'title',
    $tipo === 'marca'
        ? 'Editar marca'
        : 'Editar modelo'
)

@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >
        <livewire:catalogos.editar-registro
            :tipo="$tipo"
            :registro-id="$registroId"
            lazy
        />
    </div>

@endsection