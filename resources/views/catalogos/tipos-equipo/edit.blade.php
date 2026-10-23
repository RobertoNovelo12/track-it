@extends('layouts.app')


@section(
    'title',
    'Editar tipo de equipo'
)


@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >

        {{-- ============================================================
            ENCABEZADO: «Volver» arriba a la izquierda
        ============================================================ --}}
        <div class="mb-6 hidden items-center md:flex">
            @include('layouts.partials.boton-volver', ['destino' => route('catalogos.index', ['section' => 'equipos'])])
        </div>


        {{-- ============================================================
            EDITOR DEL TIPO DE EQUIPO
        ============================================================ --}}
        <livewire:catalogos.tipo-equipo-editor
            :tipo-equipo-id="$tipoEquipoId"
            :key="'tipo-equipo-editor-' . $tipoEquipoId"
        />

    </div>

@endsection