

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Orden de decomiso de activos</title>



    <style>

        /**

*        |-------------------------------------------------------*

*        | HOJA CARTA Y ÁREA IMPRIMIBLE*

*        |-------------------------------------------------------*

*        | Carta: 215.9 mm de ancho.*

*        | Contenido: 180 mm.*

*        | Margen izquierdo y derecho: 17.95 mm.*

*        |*

*        | El margen se establece en el contenedor y no*

*        | depende de que DomPDF respete @page margin.*

*        */



        @page {

            size: letter portrait;

            margin: 0;

        }



        html,

        body {

            margin: 0;

            padding: 0;

        }



        body {

            font-family: 'Aptos', 'DejaVu Sans', sans-serif;

            font-size: 8px;

            line-height: 1.35;

            color: #273f57;

            background: #ffffff;

        }



        .pagina {

            width: 180mm;

            margin-left: 17.95mm;

            margin-right: 0;

            padding-top: 10mm;

            padding-bottom: 6mm;

            page-break-after: always;

        }



        .pagina-ultima {

            page-break-after: auto;

        }



        /**

*        |-------------------------------------------------------*

*        | ENCABEZADO*

*        |-------------------------------------------------------*

*        */



        .encabezado {

            padding-bottom: 8px;

            border-bottom: 2px solid #ad9647;

        }



        .marca {

            color: #a58d40;

            font-size: 21px;

            letter-spacing: 2px;

            line-height: 1.1;

        }



        .grupo {

            margin-top: 3px;

            color: #a58d40;

            font-size: 6px;

            letter-spacing: 4px;

        }



        .institucion {
            clear: both;

            margin-top: 7px;

            color: #718293;

            font-size: 6.5px;

        }



        .titulo {

            margin-top: 12px;

            margin-bottom: 4px;

            color: #213c55;

            font-size: 13px;

            font-weight: bold;

        }



        .subtitulo {

            margin-bottom: 10px;

            color: #748494;

            font-size: 7.5px;

        }



        /**

*        |-------------------------------------------------------*

*        | SECCIONES*

*        |-------------------------------------------------------*

*        */



        .seccion {

            margin-top: 6px;

            margin-bottom: 3px;

            padding: 4px 6px;

            background: #eff1f3;

            color: #2b435b;

            font-size: 8px;

            font-weight: bold;

            page-break-inside: avoid;

        }



        .numero {

            display: inline-block;

            width: 22px;

            font-weight: bold;

        }



        /**

*        |-------------------------------------------------------*

*        | TABLAS*

*        |-------------------------------------------------------*

*        */



        .tabla-datos {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .tabla-datos td {

            padding: 5px;

            border: 1px solid #d1d5db;

            vertical-align: top;

            word-wrap: break-word;

        }



        .etiqueta {

            display: block;

            margin-bottom: 3px;

            color: #617b91;

            font-size: 6px;

            font-weight: bold;

            text-transform: uppercase;

        }



        .valor {

            display: block;

            color: #253d56;

            font-size: 8px;

            font-weight: bold;

            line-height: 1.45;

            word-wrap: break-word;

        }



        .texto-largo {

            white-space: pre-line;
            font-size: 7px;
            line-height: 1.25;

            font-weight: normal;

        }



        /**

*        |-------------------------------------------------------*

*        | FIRMAS*

*        |-------------------------------------------------------*

*        */



        .tabla-firmas {

            width: 100%;

            margin-top: 5px;

            border-collapse: collapse;

            table-layout: fixed;

            page-break-inside: avoid;

        }



        .tabla-firmas td {

            width: 50%;

            padding: 9px;

            border: 1px solid #d1d5db;

            vertical-align: top;

        }



        .firma-titulo {

            color: #213c55;

            font-size: 7.5px;

            font-weight: bold;

        }



        .linea-firma {

            margin-top: 52px;

            border-bottom: 1px solid #d1d5db;

        }



        .firma-pie {

            margin-top: 5px;

            color: #718293;

            font-size: 6.5px;

            line-height: 1.5;

        }



        /**

*        |-------------------------------------------------------*

*        | PIE*

*        |-------------------------------------------------------*

*        */



        .pie {

            margin-top: 11px;

            padding-top: 6px;

            border-top: 1px solid #e0e5ea;

            color: #8593a0;

            font-size: 6.5px;

        }



        .pie-numero {

            float: right;

        }



        /**

*        |-------------------------------------------------------*

*        | FOTOGRAFÍAS*

*        |-------------------------------------------------------*

*        */



        .tabla-fotos {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .tabla-fotos td {

            width: 50%;

            padding: 4px;

            vertical-align: top;

        }



        .foto-titulo {

            padding: 5px;

            border: 1px solid #d1d5db;

            background: #eff1f3;

            color: #2b435b;

            font-size: 7px;

            font-weight: bold;

        }



        .foto-contenedor {

            height: 340px;

            padding: 6px;

            border: 1px solid #d1d5db;

            border-top: 0;

            text-align: center;

            vertical-align: middle;

        }



        .foto-contenedor img {

            max-width: 100%;

            max-height: 325px;

            width: auto;

            height: auto;

        }



        .sin-foto {

            padding-top: 150px;

            color: #8694a2;

            font-size: 7px;

            text-align: center;

        }



        .nota-fotos {

            margin-top: 9px;

            color: #748494;

            font-size: 7px;

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
    $logoOrganizacion = app(\App\Services\OrganizacionService::class)->logoContenido();
    $rutaLogoReporte = public_path('images/logo-grand-palladium.png');
    $logoPalladiumPdf = $logoOrganizacion
        ? 'data:'.$logoOrganizacion['mime'].';base64,'.base64_encode($logoOrganizacion['contenido'])
        : (is_file($rutaLogoReporte)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($rutaLogoReporte))
        : null);
@endphp




@foreach ($bajas as $baja)



    @php

        $equipo = $baja['equipo'] ?? [];

        $fotos = $baja['fotos'] ?? [];



        $dato = static fn ($valor) =>

            filled($valor) ? trim((string) $valor) : '—';



        $marcaModelo = trim(implode(' ', array_filter([

            $equipo['marca'] ?? null,

            $equipo['modelo'] ?? null,

        ])));



        $evidencias = [

            [

                'titulo' => 'VISTA FRONTAL',

                'imagen' => $fotos['frontal'] ?? null,

            ],

            [

                'titulo' => 'VISTA TRASERA',

                'imagen' => $fotos['trasera'] ?? null,

            ],

            [

                'titulo' => 'VISTA LATERAL 1',

                'imagen' => $fotos['lateral_1'] ?? null,

            ],

            [

                'titulo' => 'VISTA LATERAL 2',

                'imagen' => $fotos['lateral_2'] ?? null,

            ],

        ];

    @endphp



    {{-- PÁGINA 1: INFORMACIÓN Y FIRMAS --}}

    <div class="pagina">



        <div class="encabezado">

            @if ($logoPalladiumPdf)
                <img class="logo-palladium-izquierda" src="{{ $logoPalladiumPdf }}" alt="Palladium Hotel Group">
            @endif
            <div class="institucion">

                {{ app(\App\Services\OrganizacionService::class)->nombre() }} | CONTROL DE ACTIVOS TI

            </div>

        </div>



        <div class="titulo">

            ORDEN DE DECOMISO DE ACTIVOS

        </div>



        <div class="subtitulo">

            Baja de equipo tecnológico · Control documental y autorización

        </div>



        {{-- SECCIÓN 01 --}}

        <div class="seccion">

            <span class="numero">01</span>

            DATOS DE LA BAJA

        </div>



        <table class="tabla-datos">

            <tbody>

                <tr>

                    <td width="33.33%">

                        <span class="etiqueta">Fecha de baja</span>

                        <span class="valor">

                            {{ $dato($baja['fecha'] ?? null) }}

                        </span>

                    </td>



                    <td width="33.33%">

                        <span class="etiqueta">Folio de baja</span>

                        <span class="valor">

                            {{ $dato($baja['folio'] ?? null) }}

                        </span>

                    </td>



                    <td width="33.34%">

                        <span class="etiqueta">

                            Código de inventario

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['codigo'] ?? null) }}

                        </span>

                    </td>

                </tr>

            </tbody>

        </table>



        {{-- SECCIÓN 02 --}}

        <div class="seccion">

            <span class="numero">02</span>

            INFORMACIÓN GENERAL DEL EQUIPO

        </div>



        <table class="tabla-datos">

            <tbody>

                <tr>

                    <td width="50%">

                        <span class="etiqueta">

                            Nombre del activo

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['nombre'] ?? null) }}

                        </span>

                    </td>



                    <td width="50%">

                        <span class="etiqueta">

                            Número de serie

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['serie'] ?? null) }}

                        </span>

                    </td>

                </tr>



                <tr>

                    <td>

                        <span class="etiqueta">

                            Tipo de equipo

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['tipo'] ?? null) }}

                        </span>

                    </td>



                    <td>

                        <span class="etiqueta">

                            Marca / Modelo

                        </span>

                        <span class="valor">

                            {{ $dato($marcaModelo) }}

                        </span>

                    </td>

                </tr>



                <tr>

                    <td>

                        <span class="etiqueta">

                            Fecha de registro

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['fecha_registro'] ?? null) }}

                        </span>

                    </td>



                    <td>

                        <span class="etiqueta">

                            Factura

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['factura'] ?? null) }}

                        </span>

                    </td>

                </tr>



                <tr>

                    <td>

                        <span class="etiqueta">

                            Proveedor

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['proveedor'] ?? null) }}

                        </span>

                    </td>



                    <td>

                        <span class="etiqueta">

                            Garantía

                        </span>

                        <span class="valor">

                            {{ $dato($equipo['garantia'] ?? null) }}

                        </span>

                    </td>

                </tr>

            </tbody>

        </table>



        {{-- SECCIÓN 03 --}}

        <div class="seccion">

            <span class="numero">03</span>

            JUSTIFICACIÓN DE LA BAJA

        </div>



        <table class="tabla-datos">

            <tbody>

                <tr>

                    <td>

                        <span class="etiqueta">

                            Motivo de baja

                        </span>

                        <span class="valor texto-largo">{{ $dato($baja['motivo'] ?? null) }}</span>

                    </td>

                </tr>



                <tr>

                    <td>

                        <span class="etiqueta">

                            Comentarios y detalles adicionales

                        </span>

                        <span class="valor texto-largo">{{ $dato($baja['comentarios'] ?? null) }}</span>

                    </td>

                </tr>

            </tbody>

        </table>



        {{-- SECCIÓN 04 --}}

        <div class="seccion">

            <span class="numero">04</span>

            AUTORIZACIÓN Y FIRMAS DE BAJA

        </div>



        @include('reportes.partials.firmas-baja', ['firmasDocumento' => $baja['firmas'] ?? [], 'fechaDocumento' => $baja['fecha'] ?? ''])

    </div>



    {{-- PÁGINA 2: EVIDENCIAS FOTOGRÁFICAS --}}

    <div class="pagina {{ $loop->last ? 'pagina-ultima' : '' }}">



        <table class="tabla-fotos">

            <tbody>

                @foreach (array_chunk($evidencias, 2) as $fila)

                    <tr>

                        @foreach ($fila as $evidencia)

                            <td width="50%">

                                <div class="foto-titulo">

                                    {{ $evidencia['titulo'] }}

                                </div>



                                <div class="foto-contenedor">

                                    @if ($evidencia['imagen'])

                                        <img

                                            src="{{ $evidencia['imagen'] }}"

                                            alt="{{ $evidencia['titulo'] }}"

                                        >

                                    @else

                                        <div class="sin-foto">

                                            Evidencia fotográfica

                                            no registrada

                                        </div>

                                    @endif

                                </div>

                            </td>

                        @endforeach

                    </tr>

                @endforeach

            </tbody>

        </table>



    </div>



@endforeach



</body>

</html>
