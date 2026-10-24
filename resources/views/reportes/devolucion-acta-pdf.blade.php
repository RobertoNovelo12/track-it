

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Acta de devolución de equipo</title>

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
{{-- ============================================================
    ACTA DE DEVOLUCIÓN DE EQUIPO
    Mismos estilos que las actas de asignación (reportes/actas-pdf).
    Datos: App\Services\DevolucionService::datosActa().
============================================================ --}}
@php
    $rutaLogoReporte = public_path('images/logo-grand-palladium.png');
    $logoPalladiumPdf = is_file($rutaLogoReporte)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($rutaLogoReporte))
        : null;

    $dato = static fn ($valor) => filled($valor) ? (string) $valor : '—';
    $fecha = static fn ($valor) => filled($valor) ? \Illuminate\Support\Carbon::parse($valor)->format('d/m/Y') : '—';

    $marcaModelo = trim(($acta['marca'] ?? '') . ' ' . ($acta['modelo'] ?? ''));
    $organizacion = implode(' / ', array_filter([$acta['hotel'] ?? null, $acta['departamento'] ?? null]));
@endphp

<div class="pagina">
    <div class="encabezado">
        @if ($logoPalladiumPdf)
            <img class="logo-palladium-izquierda" src="{{ $logoPalladiumPdf }}" alt="Palladium Hotel Group">
        @endif
        <div class="institucion">
            GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI
        </div>
    </div>

    <div class="titulo">ACTA DE DEVOLUCIÓN DE EQUIPO TECNOLÓGICO</div>
    <div class="subtitulo">Constancia de devolución y fin del resguardo</div>

    <div class="seccion"><span class="numero">01</span> DATOS DE LA DEVOLUCIÓN</div>
    <table class="datos">
        <tr>
            <td width="30%">
                <div class="etiqueta">Fecha de devolución</div>
                <div class="valor">{{ $fecha($acta['fecha_hora'] ?? null) }}</div>
            </td>
            <td width="40%">
                <div class="etiqueta">Motivo</div>
                <div class="valor">{{ $dato($acta['motivo'] ?? null) }}</div>
            </td>
            <td width="30%">
                <div class="etiqueta">Folio del movimiento</div>
                <div class="valor">DEV-{{ str_pad((string) ($acta['id_movimiento'] ?? ''), 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    <div class="seccion"><span class="numero">02</span> TRABAJADOR QUE DEVUELVE</div>
    <table class="datos">
        <tr>
            <td width="50%">
                <div class="etiqueta">Nombre completo</div>
                <div class="valor">{{ $dato($acta['nombre_colaborador'] ?? null) }}</div>
            </td>
            <td width="50%">
                <div class="etiqueta">Número de empleado</div>
                <div class="valor">{{ $dato($acta['numero_colaborador'] ?? null) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="etiqueta">Hotel / Departamento</div>
                <div class="valor">{{ $dato($organizacion) }}</div>
            </td>
            <td>
                <div class="etiqueta">Tuvo el equipo desde</div>
                <div class="valor">{{ $fecha($acta['fecha_asignacion'] ?? null) }}</div>
            </td>
        </tr>
    </table>

    <div class="seccion"><span class="numero">03</span> EQUIPO DEVUELTO</div>
    <table class="equipos">
        <thead>
            <tr>
                <th width="18%">CÓDIGO</th>
                <th width="32%">EQUIPO / MARCA-MODELO</th>
                <th width="22%">N.º DE SERIE</th>
                <th width="28%">CONDICIÓN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $dato($acta['codigo_inventario'] ?? null) }}</td>
                <td>
                    {{ $dato($acta['nombre_equipo'] ?? $acta['tipo'] ?? null) }}
                    @if ($marcaModelo !== '')
                        <div class="equipo-secundario">{{ $marcaModelo }}</div>
                    @endif
                </td>
                <td>{{ $dato($acta['numero_serie'] ?? null) }}</td>
                <td>{{ $dato($acta['condicion'] ?? null) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="seccion"><span class="numero">04</span> ESTADO EN QUE SE RECIBE Y OBSERVACIONES</div>
    <div class="observaciones">
        <div class="etiqueta">Observaciones de la devolución</div>
        <div class="contenido">{{ $dato($acta['observaciones'] ?? null) }}</div>
    </div>

    <div class="compromiso">
        El trabajador entrega el equipo descrito y queda liberado de su resguardo a partir de la
        fecha indicada. El Departamento de Sistemas lo recibe en las condiciones señaladas en las
        observaciones. Cualquier faltante o daño no reportado al momento de la entrega se atenderá
        conforme a las políticas internas del hotel y la legislación aplicable.
    </div>

    <div class="bloque-firmas">
        <div class="seccion"><span class="numero">05</span> FIRMAS DE ENTREGA Y RECEPCIÓN</div>
        <table class="firmas">
            <tr>
                <td width="50%">
                    <div class="firma-titulo">Entregó · Trabajador</div>
                    <div class="linea-firma"></div>
                    <div class="firma-pie">
                        {{ $dato($acta['nombre_colaborador'] ?? null) }}
                        <div>Fecha: ____ / ____ / ______</div>
                    </div>
                </td>
                <td width="50%">
                    <div class="firma-titulo">Recibió · Departamento de Sistemas</div>
                    <div class="linea-firma"></div>
                    <div class="firma-pie">
                        {{ filled($acta['recibido_por'] ?? null) ? $acta['recibido_por'] : 'Nombre y firma' }}
                        <div>Fecha: ____ / ____ / ______</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="pie">Departamento de Sistemas · Control de Activos TI</div>
</div>
</body>
</html>
