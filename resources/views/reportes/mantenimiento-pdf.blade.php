<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Actas de mantenimiento tecnológico</title>

    <style>

        @page {

            size: letter portrait;

            margin: 0;

        }



        * {

            box-sizing: border-box;

        }



        html, body {

            margin: 0;

            padding: 0;

        }



        body {

            font-family: 'Aptos', 'DejaVu Sans', sans-serif;

            color: #273f57;

            font-size: 9px;

            line-height: 1.28;

        }



        .pagina {

            padding: 10mm 16mm 9mm;

            page-break-after: always;

        }



        .pagina:last-child {

            page-break-after: auto;

        }



        .encabezado {

            padding-bottom: 6px;

            border-bottom: 2px solid #ad9647;

        }



        .marca {

            color: #a58d40;

            font-size: 22px;

            letter-spacing: 2px;

            line-height: 1.15;

        }



        .grupo {

            margin-top: 2px;

            color: #a58d40;

            font-size: 7px;

            letter-spacing: 4px;

        }



        .institucion {
            clear: both;

            margin-top: 6px;

            color: #718293;

            font-size: 7.5px;

        }



        .titulo {

            margin: 9px 0 3px;

            font-size: 13px;

            font-weight: normal;

            color: #213c55;

        }



        .subtitulo {

            margin-bottom: 6px;

            font-size: 8px;

            color: #748494;

        }



        .seccion {

            margin-top: 5px;

            margin-bottom: 3px;

            padding: 5px 8px;

            background: #ffffff;

            color: #2b435b;

            font-size: 8.5px;

            font-weight: bold;

            line-height: 1.2;

        }



        .numero {

            display: inline-block;

            width: 25px;

        }



        table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .datos td {

            border: 1px solid #d6dfe7;

            padding: 5px 8px;

            vertical-align: top;

            word-wrap: break-word;

        }



        .etiqueta {

            margin-bottom: 3px;

            font-size: 7px;

            color: #617b91;

            font-weight: bold;

            text-transform: uppercase;

        }



        .valor {

            font-size: 8.5px;

            font-weight: bold;

            color: #253d56;

            line-height: 1.28;

            word-wrap: break-word;

        }



        .tabla th {

            background: #ffffff;

            color: #253d55;

            padding: 6px;

            text-align: left;

            font-size: 7px;

        }



        .tabla td {

            padding: 6px;

            border: 1px solid #d6dfe7;

            font-size: 8px;

            vertical-align: top;

            word-wrap: break-word;

        }



        .descripcion-grid {

            width: 100%;

            margin-bottom: 3px;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .descripcion-grid td {

            border: 1px solid #d6dfe7;

            padding: 5px 8px;

            vertical-align: top;

        }



        .descripcion-grid .contenido {

            margin-top: 2px;

            font-size: 8.5px;

            color: #344b62;

            line-height: 1.32;

            white-space: pre-line;

            word-wrap: break-word;

            min-height: 16px;

        }



        .sin-refacciones {

            border: 1px solid #d6dfe7;

            background: #ffffff;

            padding: 6px 8px;

            font-size: 8px;

            color: #607487;

        }



        .bloque-firmas {

            page-break-inside: avoid;

        }



        .firmas td {

            width: 50%;

            border: 1px solid #d6dfe7;

            padding: 8px 10px;

            vertical-align: top;

        }



        .firma-titulo {

            font-size: 8px;

            font-weight: bold;

            color: #2b435b;

        }



        .linea-firma {

            margin-top: 46px;

            border-bottom: 1px solid #8797a6;

        }



        .firma-pie {

            margin-top: 5px;

            font-size: 7px;

            line-height: 1.35;

            color: #6f8193;

        }



        .firma-fecha {

            margin-top: 2px;

        }



        .pie {

            margin-top: 7px;

            padding-top: 5px;

            border-top: 1px solid #e0e5ea;

            font-size: 7px;

            color: #8593a0;

        }

    
        /* Compatible con DomPDF: float y dimensiones fijas. */
        .logo-palladium-izquierda {
            float: none;
            display: block;
            width: 42mm;
            height: auto;
            margin: 0 0 8px 0;
        }
</style>

</head>

<body>
@php
    $rutaLogoReporte = public_path('images/logo-grand-palladium.png');
    $logoPalladiumPdf = is_file($rutaLogoReporte)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($rutaLogoReporte))
        : null;
@endphp


@foreach ($mantenimientos as $m)

    @php

        $equipo = $m['equipo'] ?? [];

        $refacciones = $m['refacciones'] ?? [];



        $dato = static fn ($valor) =>

            filled($valor) ? trim((string) $valor) : '—';



        $marcaModelo = trim(implode(' ', array_filter([

            $equipo['marca'] ?? null,

            $equipo['modelo'] ?? null,

        ])));



        $prioridadEstado = implode('  ', array_filter([

            $m['prioridad'] ?? null,

            $m['estado'] ?? null,

        ]));

    @endphp



    <div class="pagina">



        {{-- ENCABEZADO --}}

        <div class="encabezado">

            @if ($logoPalladiumPdf)
                <img class="logo-palladium-izquierda" src="{{ $logoPalladiumPdf }}" alt="Palladium Hotel Group">
            @endif
            <div class="institucion">

                GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI

            </div>

        </div>



        <div class="titulo">

            ACTA DE MANTENIMIENTO DE EQUIPO TECNOLÓGICO

        </div>



        <div class="subtitulo">

            Registro técnico de intervención, diagnóstico y seguimiento del equipo

        </div>



        {{-- 01. INFORMACIÓN GENERAL --}}

        <div class="seccion">

            <span class="numero">01</span>

            INFORMACIÓN GENERAL DEL EQUIPO

        </div>



        <table class="datos">

            <tr>

                <td width="34%">

                    <div class="etiqueta">Código de inventario</div>

                    <div class="valor">{{ $dato($equipo['codigo'] ?? null) }}</div>

                </td>

                <td width="33%">

                    <div class="etiqueta">Equipo</div>

                    <div class="valor">{{ $dato($equipo['nombre'] ?? null) }}</div>

                </td>

                <td width="33%">

                    <div class="etiqueta">Host</div>

                    <div class="valor">{{ $dato($equipo['host'] ?? null) }}</div>

                </td>

            </tr>

            <tr>

                <td>

                    <div class="etiqueta">Marca / Modelo</div>

                    <div class="valor">{{ $dato($marcaModelo) }}</div>

                </td>

                <td>

                    <div class="etiqueta">Número de serie</div>

                    <div class="valor">{{ $dato($equipo['serie'] ?? null) }}</div>

                </td>

                <td>

                    <div class="etiqueta">Folio de mantenimiento</div>

                    <div class="valor">{{ $dato($m['folio'] ?? null) }}</div>

                </td>

            </tr>

        </table>



        {{-- 02. INFORMACIÓN DEL MANTENIMIENTO --}}

        <div class="seccion">

            <span class="numero">02</span>

            INFORMACIÓN DEL MANTENIMIENTO

        </div>



        <table class="datos">

            <tr>

                <td width="50%">

                    <div class="etiqueta">Tipo de mantenimiento</div>

                    <div class="valor">{{ $dato($m['tipo_mantenimiento'] ?? null) }}</div>

                </td>

                <td width="50%">

                    <div class="etiqueta">Tipo de intervención</div>

                    <div class="valor">{{ $dato($m['tipo_intervencion'] ?? null) }}</div>

                </td>

            </tr>

            <tr>

                <td>

                    <div class="etiqueta">Técnico responsable</div>

                    <div class="valor">{{ $dato($m['tecnico'] ?? null) }}</div>

                </td>

                <td>

                    <div class="etiqueta">Prioridad / Estado</div>

                    <div class="valor">{{ $dato($prioridadEstado) }}</div>

                </td>

            </tr>

        </table>



        {{-- 03. CONTROL DE TIEMPOS --}}

        <div class="seccion">

            <span class="numero">03</span>

            CONTROL DE TIEMPOS

        </div>



        <table class="datos">

            <tr>

                <td width="25%">

                    <div class="etiqueta">Fecha de inicio</div>

                    <div class="valor">{{ $dato($m['fecha_inicio'] ?? null) }}</div>

                </td>

                <td width="25%">

                    <div class="etiqueta">Hora de inicio</div>

                    <div class="valor">{{ $dato($m['hora_inicio'] ?? null) }}</div>

                </td>

                <td width="25%">

                    <div class="etiqueta">Fecha de fin</div>

                    <div class="valor">{{ $dato($m['fecha_fin'] ?? null) }}</div>

                </td>

                <td width="25%">

                    <div class="etiqueta">Hora de fin</div>

                    <div class="valor">{{ $dato($m['hora_fin'] ?? null) }}</div>

                </td>

            </tr>

        </table>



        {{-- 04. DESCRIPCIÓN TÉCNICA --}}

        <div class="seccion">

            <span class="numero">04</span>

            DESCRIPCIÓN TÉCNICA DE LA INTERVENCIÓN

        </div>



        <table class="descripcion-grid">

            <tr>

                <td width="50%">

                    <div class="etiqueta">Falla reportada / Motivo</div>

                    <div class="contenido">{{ $dato($m['falla_reportada'] ?? null) }}</div>

                </td>

                <td width="50%">

                    <div class="etiqueta">Intervención realizada</div>

                    <div class="contenido">{{ $dato($m['intervencion_realizada'] ?? null) }}</div>

                </td>

            </tr>

            <tr>

                <td colspan="2">

                    <div class="etiqueta">Diagnóstico técnico</div>

                    <div class="contenido">{{ $dato($m['diagnostico'] ?? null) }}</div>

                </td>

            </tr>

        </table>



        {{-- 05. REFACCIONES --}}

        <div class="seccion">

            <span class="numero">05</span>

            REFACCIONES / PARTES UTILIZADAS

        </div>



        @if (count($refacciones) > 0)

            <table class="tabla">

                <thead>

                    <tr>

                        <th width="42%">REFACCIÓN / PARTE</th>

                        <th width="28%">CÓDIGO DE PARTE</th>

                        <th width="15%">CANTIDAD</th>

                        <th width="15%">UNIDAD</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($refacciones as $refaccion)

                        <tr>

                            <td>{{ $dato($refaccion['nombre'] ?? null) }}</td>

                            <td>{{ $dato($refaccion['codigo'] ?? null) }}</td>

                            <td>{{ $dato($refaccion['cantidad'] ?? null) }}</td>

                            <td>{{ $dato($refaccion['unidad'] ?? null) }}</td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="sin-refacciones">

                No se requirió la sustitución ni instalación de refacciones durante la intervención.

            </div>

        @endif



        {{-- 06. INFORMACIÓN ADICIONAL --}}

        <div class="seccion">

            <span class="numero">06</span>

            INFORMACIÓN ADICIONAL

        </div>



        <table class="datos">

            <tr>

                <td width="70%">

                    <div class="etiqueta">Observaciones</div>

                    <div class="valor">{{ $dato($m['observaciones'] ?? null) }}</div>

                </td>

                <td width="30%">

                    <div class="etiqueta">Próximo mantenimiento</div>

                    <div class="valor">{{ $dato($m['proximo_mantenimiento'] ?? null) }}</div>

                </td>

            </tr>

        </table>



        {{-- 07. FIRMAS --}}

        <div class="bloque-firmas">

            <div class="seccion">

                <span class="numero">07</span>

                FIRMAS DE REALIZACIÓN Y RECEPCIÓN

            </div>



            <table class="firmas">

                <tr>

                    <td width="50%">

                        <div class="firma-titulo">

                            Realizó · Técnico responsable

                        </div>

                        <div class="linea-firma"></div>

                        <div class="firma-pie">

                            {{ $dato($m['tecnico'] ?? null) }}

                            <div class="firma-fecha">

                                Fecha: ____ / ____ / ______

                            </div>

                        </div>

                    </td>



                    <td width="50%">

                        <div class="firma-titulo">

                            Recibió · Responsable del equipo

                        </div>

                        <div class="linea-firma"></div>

                        <div class="firma-pie">

                            Nombre y firma

                            <div class="firma-fecha">

                                Fecha: ____ / ____ / ______

                            </div>

                        </div>

                    </td>

                </tr>

            </table>

        </div>



        <div class="pie">

            Departamento de Sistemas · Control de Activos TI

        </div>

    </div>

@endforeach

</body>

</html>