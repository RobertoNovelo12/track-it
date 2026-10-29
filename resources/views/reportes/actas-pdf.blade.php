

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Actas de equipo tecnológico</title>

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

            font-family: DejaVu Sans, Arial, sans-serif;

            font-size: 9px;

            line-height: 1.35;

            color: #000000;

        }



        .pagina {

            padding: 12mm 16mm 10mm;

            page-break-after: always;

        }



        .pagina:last-child {

            page-break-after: auto;

        }



        .encabezado {

            border-bottom: 1px solid #000000;

            padding-bottom: 7px;

        }



        .marca {

            color: #a58d40;

            font-size: 23px;

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

            margin-top: 7px;

            color: #000000;

            font-size: 7.5px;

        }



        .titulo {

            margin-top: 11px;

            margin-bottom: 4px;

            font-size: 14px;

            font-weight: normal;

            color: #000000;

        }



        .subtitulo {

            margin-bottom: 8px;

            color: #000000;

            font-size: 8px;

        }



        .seccion {

            margin-top: 9px;

            margin-bottom: 5px;

            padding: 6px 9px;





            font-size: 8.5px;




        background: #000000; color: #ffffff; font-weight: normal;
}



        .seccion .numero {

            display: inline-block;

            width: 25px;

        }



        table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .datos td {

            border: 1px solid #000000;

            padding: 7px 9px;

            height: 37px;

            vertical-align: top;

            word-wrap: break-word;

        }



        .etiqueta {

            margin-bottom: 4px;

            font-size: 7px;

            color: #000000;

            font-weight: normal;

            text-transform: uppercase;

        }



        .valor {

            font-size: 9px;

            line-height: 1.35;

            color: #000000;

            font-weight: normal;

            word-wrap: break-word;

        }



        .equipos th {





            padding: 7px 6px;

            text-align: left;

            font-size: 7px;


        background: #000000; color: #ffffff; font-weight: normal;
}



        .equipos td {

            border: 1px solid #000000;

            padding: 7px 6px;

            height: 38px;

            vertical-align: top;

            font-size: 8px;

            word-wrap: break-word;

        }



        .equipo-secundario {

            margin-top: 3px;

            color: #000000;

            font-size: 7.5px;

        }



        .observaciones {

            border: 1px solid #000000;

            padding: 7px 9px;

            min-height: 37px;

            page-break-inside: avoid;

        }



        .observaciones .contenido {

            font-size: 8.5px;

            line-height: 1.4;

            white-space: pre-line;

            word-wrap: break-word;

        }



        .compromiso {

            margin: 5px 2px 7px;

            color: #000000;

            font-size: 8px;

            line-height: 1.45;

            text-align: justify;

            page-break-inside: avoid;

        }



        .bloque-firmas {

            page-break-inside: avoid;

        }



        .firmas td {

            border: 1px solid #000000;

            padding: 8px 10px;

            height: 80px;

            vertical-align: top;

        }



        .firma-titulo {

            color: #000000;

            font-size: 8px;

            font-weight: normal;

        }



        .linea-firma {

            margin-top: 36px;

            border-bottom: 1px solid #000000;

        }



        .firma-pie {

            margin-top: 5px;

            color: #000000;

            font-size: 7px;

            line-height: 1.45;

        }



        .pie {

            border-top: 1px solid #000000;

            margin-top: 7px;

            padding-top: 5px;

            font-size: 7px;

            color: #000000;

        }



        */ REASIGNACIÓN: MÁS CAMPOS, MISMA HOJA */



        .pagina.reasignacion .seccion {

            margin-top: 7px;

            margin-bottom: 4px;

            padding: 5px 9px;


        background: #000000; color: #ffffff; font-weight: normal;
}



        .pagina.reasignacion .datos td {

            height: 31px;

            padding: 5px 8px;

        }



        .pagina.reasignacion .etiqueta {

            margin-bottom: 2px;

            font-size: 6.5px;

        }



        .pagina.reasignacion .valor {

            font-size: 8px;

        }



        .pagina.reasignacion .equipos th,

        .pagina.reasignacion .equipos td {

            padding: 5px 6px;

        }



        .pagina.reasignacion .observaciones {

            min-height: 28px;

            padding: 5px 8px;

        }



        .pagina.reasignacion .compromiso {

            font-size: 7.4px;

            line-height: 1.38;

            margin: 4px 2px;

        }



        .pagina.reasignacion .firmas td {

            height: 70px;

            padding: 6px 9px;

        }



        .pagina.reasignacion .linea-firma {

            margin-top: 28px;

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
    // Logo actual de la organización (o el predeterminado), reducido y sin transparencia.
    $logoPalladiumPdf = app(\App\Services\ImagenPdfService::class)->logoDataUri();
@endphp


@foreach ($actas as $acta)

    @php

        $esReasignacion = ($acta['tipo'] ?? '') === 'reasignaciones';

        $anterior = $acta['anterior'] ?? [];

        $nuevo = $acta['nuevo'] ?? [];

        $equipo = $acta['equipo'] ?? [];



        $dato = static fn ($valor) =>

            filled($valor) ? (string) $valor : '—';



        $marcaModelo = trim(

            ($equipo['marca'] ?? '') . ' ' .

            ($equipo['modelo'] ?? '')

        );



        $areaAnterior = implode(' / ', array_unique(array_filter([

            $anterior['departamento'] ?? null,

            $anterior['area'] ?? null,

        ])));



        $areaNuevo = implode(' / ', array_unique(array_filter([

            $nuevo['departamento'] ?? null,

            $nuevo['area'] ?? null,

        ])));

    @endphp



    <div class="pagina {{ $esReasignacion ? 'reasignacion' : '' }}">



        <div class="encabezado">

            @if ($logoPalladiumPdf)
                <img class="logo-palladium-izquierda" src="{{ $logoPalladiumPdf }}" alt="Palladium Hotel Group">
            @endif
            <div class="institucion">

                GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI

            </div>

        </div>



        <div class="titulo">

            {{ $esReasignacion

                ? 'REASIGNACIÓN DE EQUIPO'

                : 'ACTA DE ASIGNACIÓN DE EQUIPO TECNOLÓGICO' }}

        </div>



        <div class="subtitulo">

            {{ $esReasignacion

                ? 'Constancia de transferencia de resguardo entre trabajadores'

                : 'Constancia de entrega, recepción y responsabilidad del resguardo' }}

        </div>



        <div class="seccion">

            <span class="numero">01</span>

            DATOS DE LA

            {{ $esReasignacion ? 'REASIGNACIÓN' : 'ASIGNACIÓN' }}

        </div>



        @if ($esReasignacion)

            <table class="datos">

                <tr>

                    <td width="35%">

                        <div class="etiqueta">Fecha de reasignación</div>

                        <div class="valor">

                            {{ $dato($acta['fecha'] ?? null) }}

                        </div>

                    </td>

                    <td width="65%">

                        <div class="etiqueta">Motivo de reasignación</div>

                        <div class="valor">

                            {{ $dato($acta['motivo'] ?? null) }}

                        </div>

                    </td>

                </tr>

            </table>



            <div class="seccion">

                <span class="numero">02</span>

                ASIGNACIÓN ANTERIOR

            </div>



            <table class="datos">

                <tr>

                    <td width="50%">

                        <div class="etiqueta">Responsable actual / anterior</div>

                        <div class="valor">

                            {{ $dato($anterior['nombre'] ?? null) }}

                        </div>

                    </td>

                    <td width="50%">

                        <div class="etiqueta">Número de empleado</div>

                        <div class="valor">

                            {{ $dato($anterior['numero'] ?? null) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Sede anterior</div>

                        <div class="valor">

                            {{ $dato($anterior['sede'] ?? null) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Hotel / Departamento anterior</div>

                        <div class="valor">

                            {{ $dato($areaAnterior) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Ubicación anterior</div>

                        <div class="valor">

                            {{ $dato($anterior['ubicacion'] ?? null) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Host del equipo</div>

                        <div class="valor">

                            {{ $dato($equipo['host'] ?? null) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Fecha de asignación anterior</div>

                        <div class="valor">

                            {{ $dato($anterior['fecha'] ?? null) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Tipo de asignación anterior</div>

                        <div class="valor">

                            {{ $dato($anterior['tipo'] ?? null) }}

                        </div>

                    </td>

                </tr>

            </table>



            <div class="seccion">

                <span class="numero">03</span>

                NUEVA REASIGNACIÓN

            </div>



            <table class="datos">

                <tr>

                    <td width="50%">

                        <div class="etiqueta">Nuevo colaborador</div>

                        <div class="valor">

                            {{ $dato($nuevo['nombre'] ?? null) }}

                        </div>

                    </td>

                    <td width="50%">

                        <div class="etiqueta">Número de empleado</div>

                        <div class="valor">

                            {{ $dato($nuevo['numero'] ?? null) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Nueva sede</div>

                        <div class="valor">

                            {{ $dato($nuevo['sede'] ?? null) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Nuevo hotel</div>

                        <div class="valor">

                            {{ $dato($nuevo['area'] ?? null) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Nuevo departamento</div>

                        <div class="valor">

                            {{ $dato($nuevo['departamento'] ?? null) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Nueva ubicación</div>

                        <div class="valor">

                            {{ $dato($nuevo['ubicacion'] ?? null) }}

                        </div>

                    </td>

                </tr>

            </table>



        @else

            <table class="datos">

                <tr>

                    <td width="34%">

                        <div class="etiqueta">Host del equipo</div>

                        <div class="valor">

                            {{ $dato($equipo['host'] ?? null) }}

                        </div>

                    </td>

                    <td width="33%">

                        <div class="etiqueta">Fecha de entrega</div>

                        <div class="valor">

                            {{ $dato($acta['fecha'] ?? null) }}

                        </div>

                    </td>

                    <td width="33%">

                        <div class="etiqueta">Tipo de asignación</div>

                        <div class="valor">

                            {{ $dato($nuevo['tipo'] ?? null) }}

                        </div>

                    </td>

                </tr>

            </table>



            <div class="seccion">

                <span class="numero">02</span>

                DATOS DEL TRABAJADOR RESPONSABLE

            </div>



            <table class="datos">

                <tr>

                    <td width="50%">

                        <div class="etiqueta">Nombre completo</div>

                        <div class="valor">

                            {{ $dato($nuevo['nombre'] ?? null) }}

                        </div>

                    </td>

                    <td width="50%">

                        <div class="etiqueta">Número de empleado</div>

                        <div class="valor">

                            {{ $dato($nuevo['numero'] ?? null) }}

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="etiqueta">Departamento / Hotel</div>

                        <div class="valor">

                            {{ $dato($areaNuevo) }}

                        </div>

                    </td>

                    <td>

                        <div class="etiqueta">Ubicación de trabajo</div>

                        <div class="valor">

                            {{ $dato($nuevo['ubicacion'] ?? null) }}

                        </div>

                    </td>

                </tr>

            </table>

        @endif



        <div class="seccion">

            <span class="numero">{{ $esReasignacion ? '04' : '03' }}</span>

            {{ $esReasignacion

                ? 'EQUIPO REASIGNADO'

                : 'EQUIPOS ASIGNADOS' }}

        </div>



        <table class="equipos">

            <thead>

                <tr>

                    <th width="18%">CÓDIGO</th>

                    <th width="32%">EQUIPO / MARCA-MODELO</th>

                    <th width="22%">N.º DE SERIE</th>

                    <th width="28%">CONDICIÓN / ACCESORIOS</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>{{ $dato($equipo['codigo'] ?? null) }}</td>

                    <td>

                        {{ $dato($equipo['nombre'] ?? null) }}

                        @if ($marcaModelo !== '')

                            <div class="equipo-secundario">

                                {{ $marcaModelo }}

                            </div>

                        @endif

                    </td>

                    <td>{{ $dato($equipo['serie'] ?? null) }}</td>

                    <td>{{ $dato($equipo['condicion'] ?? null) }}</td>

                </tr>

            </tbody>

        </table>



        <div class="seccion">

            <span class="numero">{{ $esReasignacion ? '05' : '04' }}</span>

            {{ $esReasignacion

                ? 'OBSERVACIONES Y COMPROMISO DE RESGUARDO'

                : 'ESTADO, ENTREGA Y OBSERVACIONES' }}

        </div>



        <div class="observaciones">

            <div class="etiqueta">Observaciones de entrega</div>

            <div class="contenido">

                {{ $dato($acta['observaciones'] ?? null) }}

            </div>

        </div>



        @unless ($esReasignacion)

            <div class="seccion">

                <span class="numero">05</span>

                COMPROMISO DE RESGUARDO

            </div>

        @endunless



        <div class="compromiso">

            @if ($esReasignacion)

                Declaro que el equipo indicado cambia de responsable

                y se entrega en las condiciones señaladas.

                El nuevo responsable se compromete a utilizarlo para

                fines laborales, cuidarlo, reportar incidentes y

                devolverlo cuando corresponda. Los daños por negligencia

                o uso indebido se atenderán conforme a las políticas

                del hotel y la legislación aplicable.

            @else

                Declaro recibir los equipos y accesorios descritos

                en las condiciones indicadas. Me comprometo a

                utilizarlos para fines de trabajo, cuidarlos,

                reportar cualquier incidente y devolverlos cuando

                corresponda. En caso de daños ocasionados por mi

                negligencia o uso indebido, asumiré los costos que

                correspondan, previa evaluación y conforme a las

                políticas internas del hotel y la legislación aplicable.

            @endif

        </div>



        <div class="bloque-firmas">

            <div class="seccion">

                <span class="numero">06</span>

                FIRMAS DE ENTREGA Y RECEPCIÓN

            </div>



            <table class="firmas">

                <tr>

                    <td width="50%">

                        <div class="firma-titulo">

                            Entregó · Departamento de Sistemas

                        </div>

                        <div class="linea-firma"></div>

                        <div class="firma-pie">

                            Nombre y firma

                            <div>Fecha: ____ / ____ / ______</div>

                        </div>

                    </td>

                    <td width="50%">

                        <div class="firma-titulo">

                            Recibió ·

                            {{ $esReasignacion

                                ? 'Nuevo responsable'

                                : 'Trabajador responsable' }}

                        </div>

                        <div class="linea-firma"></div>

                        <div class="firma-pie">

                            Nombre y firma

                            <div>Fecha: ____ / ____ / ______</div>

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
