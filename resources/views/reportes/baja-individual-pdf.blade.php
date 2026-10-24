
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Acta de baja {{ $documento['folio'] }}
    </title>

    <style>
        @page {
            margin: 26px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #273f57;
            font-size: 9px;
        }

        .logo {
            max-width: 165px;
            max-height: 53px;
            margin-bottom: 7px;
        }

        .organizacion {
            font-size: 8px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
        }

        .cabecera {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }

        .cabecera td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .titulo {
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 4px;
        }

        .subtitulo {
            font-size: 9px;
        }

        .metadatos {
            text-align: right;
            font-size: 8px;
        }

        .seccion {
            background: #edf1f3;
            font-weight: bold;
            margin: 13px 0 6px;
            padding: 6px 8px;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .tabla td,
        .tabla th {
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .etiqueta {
            display: block;
            font-size: 7px;
            color: #586b7d;
            margin-bottom: 4px;
        }

        .firmas {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .firmas > tbody > tr > td {
            border: 0;
            padding: 4px;
            vertical-align: top;
        }

        .firma-recuadro {
            border: 1px solid #d1d5db;
        }

        .firma-cargo {
            padding: 6px 4px;
            border-bottom: 1px solid #d1d5db;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
        }

        .firma-espacio {
            height: 44px;
        }

        .firma-datos {
            padding: 4px 7px 7px;
            font-size: 8px;
        }

        .firma-fecha {
            margin-top: 3px;
        }

        .segunda-hoja {
            page-break-before: always;
        }

        .fotos {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 6px 8px;
        }

        .fotos td {
            width: 50%;
            vertical-align: top;
        }

        .foto-titulo {
            padding: 6px;
            font-weight: bold;
            background: #edf1f3;
            border: 1px solid #d1d5db;
        }

        .foto-marco {
            height: 242px;
            text-align: center;
            border: 1px solid #d1d5db;
            border-top: 0;
            padding: 8px;
        }

        .foto-marco img {
            max-width: 100%;
            max-height: 224px;
            width: auto;
            height: auto;
        }
    </style>
</head>

<body>

@php
    $e = $documento['equipo'];

    $fecha = \Carbon\Carbon::parse(
        $documento['fecha']
    )->format('d/m/Y');

    $campos = [
        'Código de inventario' => $e['codigo_inventario'] ?? '—',
        'Nombre del equipo' => $e['nombre_equipo'] ?? '—',
        'Tipo de equipo' => $e['tipo_equipo'] ?? '—',
        'Número de serie' => $e['numero_serie'] ?? '—',
        'Marca' => $e['marca'] ?? '—',
        'Modelo' => $e['modelo'] ?? '—',
        'Número de factura' => $e['numero_factura'] ?? '—',
        'Proveedor' => $e['proveedor'] ?? '—',
    ];
@endphp

@if (!empty($documento['logo']))
    <img
        src="{{ $documento['logo'] }}"
        class="logo"
        alt="Logotipo"
    >
@endif

<div class="organizacion">
    {{ $documento['organizacion'] }}
    | CONTROL DE ACTIVOS TI
</div>

<table class="cabecera">
    <tr>
        <td width="70%">
            <p class="titulo">
                ACTA DE BAJA DE EQUIPO TECNOLÓGICO
            </p>

            <span class="subtitulo">
                Documento individual de baja
            </span>
        </td>

        <td width="30%" class="metadatos">
            Folio: {{ $documento['folio'] }}
            <br>
            Fecha: {{ $fecha }}
        </td>
    </tr>
</table>

<div class="seccion">
    01 &nbsp; DATOS DEL EQUIPO
</div>

<table class="tabla">
    @foreach (
        array_chunk($campos, 2, true) as $fila
    )
        <tr>
            @foreach (
                $fila as $etiqueta => $valor
            )
                <td width="50%">
                    <span class="etiqueta">
                        {{ mb_strtoupper($etiqueta) }}
                    </span>

                    {{ filled($valor) ? $valor : '—' }}
                </td>
            @endforeach

            @if (count($fila) === 1)
                <td></td>
            @endif
        </tr>
    @endforeach
</table>

<div class="seccion">
    02 &nbsp; INFORMACIÓN DE LA BAJA
</div>

<table class="tabla">
    <tr>
        <td width="50%">
            <span class="etiqueta">
                FECHA DE BAJA
            </span>
            {{ $fecha }}
        </td>

        <td width="50%">
            <span class="etiqueta">
                ESTADO RESULTANTE
            </span>
            BAJA
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <span class="etiqueta">
                MOTIVO
            </span>
            {{ $documento['motivo'] }}
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <span class="etiqueta">
                COMENTARIOS / OBSERVACIONES
            </span>
            {{ $documento['comentarios'] ?: '—' }}
        </td>
    </tr>
</table>

<div class="seccion">
    03 &nbsp; FIRMAS DE BAJA
</div>

<table class="firmas">
    <tbody>
        @foreach (
            array_chunk(
                \App\Livewire\EquipoBaja::FIRMANTES,
                2,
                true
            ) as $fila
        )
            <tr>
                @if (count($fila) === 1)
                    <td width="25%"></td>
                @endif

                @foreach (
                    $fila as $clave => $cargo
                )
                    <td colspan="2" width="50%">
                        <div class="firma-recuadro">

                            <div class="firma-cargo">
                                {{ $cargo }}
                            </div>

                            <div class="firma-espacio"></div>

                            <div class="firma-datos">
                                {{ $documento['firmas'][$clave] ?? '—' }}

                                <div class="firma-fecha">
                                    Fecha: {{ $fecha }}
                                </div>
                            </div>

                        </div>
                    </td>
                @endforeach

                @if (count($fila) === 1)
                    <td width="25%"></td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

{{-- SEGUNDA HOJA: EVIDENCIAS --}}

<div class="segunda-hoja">

    @if (!empty($documento['logo']))
        <img
            src="{{ $documento['logo'] }}"
            class="logo"
            alt="Logotipo"
        >
    @endif

    <div class="organizacion">
        {{ $documento['organizacion'] }}
        | CONTROL DE ACTIVOS TI
    </div>

    <table class="cabecera">
        <tr>
            <td width="70%">
                <p class="titulo">
                    EVIDENCIA FOTOGRÁFICA
                </p>

                <span class="subtitulo">
                    {{ $e['codigo_inventario'] ?? '' }}
                    —
                    {{ $e['nombre_equipo'] ?? '' }}
                </span>
            </td>

            <td width="30%" class="metadatos">
                Folio: {{ $documento['folio'] }}
                <br>
                Fecha: {{ $fecha }}
            </td>
        </tr>
    </table>

    <div class="seccion">
        04 &nbsp; FOTOGRAFÍAS DEL EQUIPO
    </div>

    <table class="fotos">
        <tbody>
            @foreach (
                array_chunk(
                    [
                        'Vista frontal',
                        'Vista trasera',
                        'Vista lateral 1',
                        'Vista lateral 2'
                    ],
                    2
                ) as $fila
            )
                <tr>
                    @foreach ($fila as $titulo)
                        <td>
                            <div class="foto-titulo">
                                {{ $titulo }}
                            </div>

                            <div class="foto-marco">
                                @if (
                                    !empty(
                                        $documento['fotos'][$titulo]
                                    )
                                )
                                    <img
                                        src="{{ $documento['fotos'][$titulo] }}"
                                        alt="{{ $titulo }}"
                                    >
                                @else
                                    Evidencia no registrada
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

</body>
</html>
