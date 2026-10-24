
@extends('layouts.app')

@section('title', 'Dar de baja equipo')

@section('content')

<div>

    {{--
        Descargar el PDF generado para el equipo anterior.
        El documento permanece guardado en Reportes.
    --}}
    @if (session('descargar_reporte'))
        <div
            x-data
            x-init="
                const enlace = document.createElement('a');

                enlace.href = @js(
                    route(
                        'reportes.download',
                        session('descargar_reporte')
                    )
                );

                enlace.style.display = 'none';

                document.body.appendChild(enlace);
                enlace.click();
                enlace.remove();
            "
            hidden
            aria-hidden="true"
        ></div>
    @endif

    {{--
        Se genera una instancia independiente para cada equipo.

        La clave evita que Livewire reutilice
        los datos y fotografías del formulario anterior.
    --}}
    <livewire:equipo-baja
        :equipo-id="$equipoId"
        :lote="request()->query('cola')"
        :key="'baja-individual-equipo-'.$equipoId"
    />

</div>

@endsection
