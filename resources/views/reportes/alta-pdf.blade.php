

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Acta de alta de equipo tecnológico</title>



    <style>

        @page {

            size: letter portrait;

            margin: 0;

        }



        html, body {

            margin: 0;

            padding: 0;

        }



        body {

            font-family: DejaVu Sans, Arial, sans-serif;

            font-size: 8px;

            line-height: 1.35;

            color: #000000;

            background: #ffffff;

        }



        /**

*         * Mismo ancho y márgenes del PDF de Bajas.*

*         */

        .pagina {

            width: 180mm;

            margin-left: 17.95mm;

            padding-top: 12mm;

            padding-bottom: 10mm;

            page-break-after: always;

        }



        .pagina-ultima {

            page-break-after: auto;

        }



        .encabezado {

            padding-bottom: 8px;

            border-bottom: 1px solid #000000;

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

            color: #000000;

            font-size: 6.5px;

        }



        .titulo {

            margin-top: 12px;

            margin-bottom: 4px;

            color: #000000;

            font-size: 13px;

            font-weight: normal;

        }



        .subtitulo {

            margin-bottom: 9px;

            color: #000000;

            font-size: 7.5px;

        }



        .seccion {

            margin-top: 9px;

            margin-bottom: 4px;

            padding: 6px 9px;





            font-size: 8px;



            page-break-inside: avoid;


        background: #000000; color: #ffffff; font-weight: normal;
}



        .numero {

            display: inline-block;

            width: 22px;

        }



        .tabla-datos,

        .tabla-firmas {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }



        .tabla-datos td {

            padding: 7px 8px;

            border: 1px solid #000000;

            vertical-align: top;

            word-wrap: break-word;

        }



        .etiqueta {

            display: block;

            margin-bottom: 4px;

            color: #000000;

            font-size: 6px;

            font-weight: normal;

            text-transform: uppercase;

        }



        .valor {

            display: block;

            color: #000000;

            font-size: 8px;

            font-weight: normal;

            line-height: 1.4;

            word-wrap: break-word;

        }



        .valor-texto {

            font-weight: normal;

            white-space: pre-line;

        }



        .tabla-firmas {

            margin-top: 5px;

            page-break-inside: avoid;

        }



        .tabla-firmas td {

            width: 50%;

            padding: 9px;

            border: 1px solid #000000;

            vertical-align: top;

        }



        .firma-titulo {

            color: #000000;

            font-size: 7.5px;

            font-weight: normal;

        }



        .linea-firma {

            margin-top: 48px;

            border-bottom: 1px solid #000000;

        }



        .firma-pie {

            margin-top: 5px;

            color: #000000;

            font-size: 6.5px;

        }



        .pie {

            margin-top: 11px;

            padding-top: 6px;

            border-top: 1px solid #000000;

            color: #000000;

            font-size: 6.5px;

        }



        .pie-derecho {

            float: right;

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




@foreach ($altas as $alta)



    @php

        $dato = static fn ($valor) =>

            filled($valor) ? trim((string) $valor) : '—';



        $marcaModelo = trim(implode(' ', array_filter([

            $alta['marca'] ?? null,

            $alta['modelo'] ?? null,

        ])));

    @endphp



    <div class="pagina {{ $loop->last ? 'pagina-ultima' : '' }}">



        <div class="encabezado">

            @if ($logoPalladiumPdf)
                <img class="logo-palladium-izquierda" src="{{ $logoPalladiumPdf }}" alt="Palladium Hotel Group">
            @endif
            <div class="institucion">

                GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI

            </div>

        </div>



        <div class="titulo">

            ACTA DE ALTA DE EQUIPO TECNOLÓGICO

        </div>



        <div class="subtitulo">

            Registro de incorporación al inventario ·

            Departamento de Sistemas

        </div>



        {{-- 01. REGISTRO --}}

        <div class="seccion">

            <span class="numero">01</span>

            DATOS DEL REGISTRO

        </div>



        <table class="tabla-datos">

            <tr>

                <td width="50%">

                    <span class="etiqueta">Código de inventario</span>

                    <span class="valor">

                        {{ $dato($alta['codigo'] ?? null) }}

                    </span>

                </td>



                <td width="50%">

                    <span class="etiqueta">Fecha de registro</span>

                    <span class="valor">

                        {{ $dato($alta['fecha_registro'] ?? null) }}

                    </span>

                </td>

            </tr>

        </table>



        {{-- 02. INFORMACIÓN GENERAL --}}

        <div class="seccion">

            <span class="numero">02</span>

            INFORMACIÓN GENERAL DEL EQUIPO

        </div>



        <table class="tabla-datos">

            <tr>

                <td width="50%">

                    <span class="etiqueta">Nombre del equipo</span>

                    <span class="valor">

                        {{ $dato($alta['nombre'] ?? null) }}

                    </span>

                </td>



                <td width="50%">

                    <span class="etiqueta">Host</span>

                    <span class="valor">

                        {{ $dato($alta['host'] ?? null) }}

                    </span>

                </td>

            </tr>



            <tr>

                <td>

                    <span class="etiqueta">Tipo de equipo</span>

                    <span class="valor">

                        {{ $dato($alta['tipo'] ?? null) }}

                    </span>

                </td>



                <td>

                    <span class="etiqueta">Marca / Modelo</span>

                    <span class="valor">

                        {{ $dato($marcaModelo) }}

                    </span>

                </td>

            </tr>



            <tr>

                <td>

                    <span class="etiqueta">Número de serie</span>

                    <span class="valor">

                        {{ $dato($alta['serie'] ?? null) }}

                    </span>

                </td>



                <td>

                    <span class="etiqueta">Dirección MAC</span>

                    <span class="valor">

                        {{ $dato($alta['mac'] ?? null) }}

                    </span>

                </td>

            </tr>

        </table>



        {{-- 03. ADQUISICIÓN --}}

        <div class="seccion">

            <span class="numero">03</span>

            INFORMACIÓN DE ADQUISICIÓN

        </div>



        <table class="tabla-datos">

            <tr>

                <td width="50%">

                    <span class="etiqueta">Fecha de compra</span>

                    <span class="valor">

                        {{ $dato($alta['fecha_compra'] ?? null) }}

                    </span>

                </td>



                <td width="50%">

                    <span class="etiqueta">Número de factura</span>

                    <span class="valor">

                        {{ $dato($alta['factura'] ?? null) }}

                    </span>

                </td>

            </tr>



            <tr>

                <td>

                    <span class="etiqueta">Proveedor</span>

                    <span class="valor">

                        {{ $dato($alta['proveedor'] ?? null) }}

                    </span>

                </td>



                <td>

                    <span class="etiqueta">Vigencia de garantía</span>

                    <span class="valor">

                        {{ $dato($alta['garantia'] ?? null) }}

                    </span>

                </td>

            </tr>

        </table>



        {{-- 04. ESTADO Y RESPONSABILIDAD --}}

        <div class="seccion">

            <span class="numero">04</span>

            ESTADO Y RESPONSABILIDAD DEL ACTIVO

        </div>



        <table class="tabla-datos">

            <tr>

                <td width="50%">

                    <span class="etiqueta">Estado</span>

                    <span class="valor">

                        {{ $dato($alta['estado'] ?? null) }}

                    </span>

                </td>



                <td width="50%">

                    <span class="etiqueta">Estatus / Condición</span>

                    <span class="valor">

                        {{ $dato($alta['condicion'] ?? null) }}

                    </span>

                </td>

            </tr>



            <tr>

                <td>

                    <span class="etiqueta">Propietario / Responsable</span>

                    <span class="valor">

                        {{ $dato($alta['responsable'] ?? null) }}

                    </span>

                </td>



                <td>

                    <span class="etiqueta">Hotel</span>

                    <span class="valor">

                        {{ $dato($alta['hotel'] ?? null) }}

                    </span>

                </td>

            </tr>



            <tr>

                <td colspan="2">

                    <span class="etiqueta">Departamento</span>

                    <span class="valor">

                        {{ $dato($alta['departamento'] ?? null) }}

                    </span>

                </td>

            </tr>

        </table>



        {{-- 05. COMENTARIOS --}}

        <div class="seccion">

            <span class="numero">05</span>

            INFORMACIÓN ADICIONAL

        </div>



        <table class="tabla-datos">

            <tr>

                <td>

                    <span class="etiqueta">Comentarios</span>

                    <span class="valor valor-texto">

                        {{ $dato($alta['comentarios'] ?? null) }}

                    </span>

                </td>

            </tr>

            {{-- Programas instalados (solo si tiene) --}}
            @if (! empty($alta['programas'] ?? []))
                <tr>
                    <td>
                        <span class="etiqueta">Programas instalados</span>
                        <span class="valor valor-texto">{{ implode(', ', $alta['programas']) }}</span>
                    </td>
                </tr>
            @endif

        </table>



        {{-- 06. FIRMAS --}}

        <div class="seccion">

            <span class="numero">06</span>

            FIRMAS DE REGISTRO Y VALIDACIÓN

        </div>



        <table class="tabla-firmas">

            <tr>

                <td width="50%">

                    <div class="firma-titulo">

                        Registró · Departamento de Sistemas

                    </div>



                    <div class="linea-firma"></div>



                    <div class="firma-pie">

                        Nombre y firma

                        <div>Fecha: ____ / ____ / ______</div>

                    </div>

                </td>



                <td width="50%">

                    <div class="firma-titulo">

                        Validó · Responsable de Sistemas

                    </div>



                    <div class="linea-firma"></div>



                    <div class="firma-pie">

                        Nombre y firma

                        <div>Fecha: ____ / ____ / ______</div>

                    </div>

                </td>

            </tr>

        </table>



        <div class="pie">

            Departamento de Sistemas · Control de Activos TI



            <span class="pie-derecho">

                ACTA DE ALTA

            </span>

        </div>

    </div>



@endforeach



</body>

</html>
