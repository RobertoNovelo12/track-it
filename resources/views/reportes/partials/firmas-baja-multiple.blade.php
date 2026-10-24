
@php
    $nombresFirmas = $firmasDocumento ?? [];

@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | FIRMAS EXCLUSIVAS PARA BAJA MÚLTIPLE
    |--------------------------------------------------------------------------
    */

    .bm-documento table.firmas-baja-multiple {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        border-spacing: 0;
        page-break-inside: avoid;
        margin-top: 8px;
    }

    .bm-documento .firmas-baja-multiple > tbody > tr > td {
        width: 25%;
        border: 0;
        padding: 0 0 6px;
        vertical-align: top;
    }

    /*
    |--------------------------------------------------------------------------
    | RECUADROS DE FIRMA
    |--------------------------------------------------------------------------
    */

    .bm-documento .firmas-baja-multiple .firma-recuadro {
        border: 1px solid #000000;
        color: #000000;
        background: #ffffff;
        font-size: 8px;
        page-break-inside: avoid;
    }

    /*
    |--------------------------------------------------------------------------
    | ENCABEZADO DE CADA FIRMA
    |--------------------------------------------------------------------------
    */

    .bm-documento .firmas-baja-multiple .firma-cargo {
        border-bottom: 1px solid #000000;
        text-align: center;
        padding: 5px 6px;
        font-weight: normal;
        background: #ffffff;
        color: #000000;
    }

    /*
    |--------------------------------------------------------------------------
    | ESPACIO DISPONIBLE PARA FIRMAR
    |--------------------------------------------------------------------------
    | Se ajusta para que exista suficiente espacio
    | sin desperdiciar demasiada altura de la hoja.
    */

    .bm-documento .firmas-baja-multiple .bmfm-espacio {
        height: 38px;
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL RESPONSABLE
    |--------------------------------------------------------------------------
    | Nombre alineado a la izquierda.
    | Fecha debajo del nombre.
    */

    .bm-documento .firmas-baja-multiple .bmfm-datos {
        padding: 0 7px 7px;
        text-align: left;
        background: #ffffff;
    }

    .bm-documento .firmas-baja-multiple .bmfm-nombre {
        display: block;
        margin: 0 0 4px;
        padding: 0;
        color: #000000;
        font-size: 8px;
        font-weight: normal;
        line-height: 1.35;
        text-align: left;
        word-wrap: break-word;
    }

    .bm-documento .firmas-baja-multiple .bmfm-fecha {
        display: block;
        margin: 0;
        padding: 0;
        color: #000000;
        font-size: 8px;
        line-height: 1.35;
        text-align: left;
    }

    /*
    |--------------------------------------------------------------------------
    | SEPARACIÓN ENTRE RECUADROS
    |--------------------------------------------------------------------------
    */

    .bm-documento .firmas-baja-multiple td.firma-izquierda {
        padding-right: 4px;
    }

    .bm-documento .firmas-baja-multiple td.firma-derecha {
        padding-left: 4px;
    }
</style>

<table class="firmas-baja-multiple">
    <tbody>
        @foreach (
            array_chunk(
                \App\Livewire\EquipoBaja::FIRMANTES,
                2,
                true
            ) as $fila
        )
            <tr>

                {{-- Espacio izquierdo para centrar la última firma --}}
                @if (count($fila) === 1)
                    <td></td>
                @endif

                @foreach ($fila as $clave => $titulo)

                    <td
                        colspan="2"
                        class="{{
                            count($fila) === 2
                                ? ($loop->first
                                    ? 'firma-izquierda'
                                    : 'firma-derecha')
                                : ''
                        }}"
                    >

                        <div class="firma-recuadro">

                            {{-- Cargo del responsable --}}
                            <div class="firma-cargo">
                                {{ $titulo }}
                            </div>

                            {{-- Espacio para firma manual --}}
                            <div class="bmfm-espacio"></div>

                            {{-- Nombre primero, fecha debajo --}}
                            <div class="bmfm-datos">

                                <div class="bmfm-nombre">
                                    {{ $nombresFirmas[$clave] ?? '—' }}
                                </div>

                                <div class="bmfm-fecha">
                                    Fecha:
                                </div>

                            </div>

                        </div>

                    </td>

                @endforeach

                {{-- Espacio derecho para centrar Gerente Financiero --}}
                @if (count($fila) === 1)
                    <td></td>
                @endif

            </tr>
        @endforeach
    </tbody>
</table>
