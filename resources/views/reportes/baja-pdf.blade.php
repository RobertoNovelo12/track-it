<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Orden de decomiso de activos
    </title>

    <style>
        @page {
            margin: 28px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            font-family:
                DejaVu Sans,
                Arial,
                sans-serif;

            font-size: 11px;

            color: #111827;
        }

        .page {
            width: 100%;
        }

        .page-break {
            page-break-after: always;
        }

        .logo {
            width: 105px;
            height: auto;

            margin-bottom: 24px;
        }

        .document-title {
            width: 82%;

            margin:
                0
                auto
                24px
                auto;

            border: 1px solid #222;

            padding: 4px 8px;

            text-align: center;

            font-size: 15px;
            font-weight: normal;
        }

        .field-row {
            width: 82%;

            margin:
                0
                auto
                6px
                auto;

            font-size: 12px;
        }

        .field-label {
            display: inline-block;

            font-weight: normal;
        }

        .field-value {
            display: inline-block;

            min-width: 520px;

            padding:
                0
                4px
                2px
                4px;

            border-bottom: 1px solid #222;
        }

        .asset-table {
            width: 82%;

            margin:
                12px
                auto
                30px
                auto;

            border-collapse: collapse;

            table-layout: fixed;
        }

        .asset-table th,
        .asset-table td {
            border: 1px solid #222;
        }

        .asset-table th {
            padding: 5px 7px;

            text-align: left;

            font-size: 11px;
            font-weight: normal;

            vertical-align: top;
        }

        .asset-table td {
            height: 105px;

            padding: 8px;

            vertical-align: top;

            font-size: 10px;
            line-height: 1.45;
        }

        .col-cantidad {
    width: 8%;
}

.col-unidad {
    width: 10%;
}

.col-descripcion {
    width: 47%;
}

.col-motivo {
    width: 35%;
}

.col-cantidad,
.col-unidad {
    text-align: center !important;
}

        .signature-table {
            width: 82%;

            margin:
                0
                auto
                26px
                auto;

            border-collapse: separate;
            border-spacing: 10px 0;

            table-layout: fixed;
        }

        .signature-cell {
            width: 50%;

            padding: 0;

            border: 1px solid #222;

            vertical-align: top;
        }

        .signature-title {
            padding: 3px 7px;

            border-bottom: 1px solid #222;

            font-size: 11px;
        }

        .signature-body {
            position: relative;

            height: 62px;

            padding: 7px;
        }

        .signature-date {
            position: absolute;

            left: 7px;
            bottom: 5px;

            font-size: 10px;
        }

        .photo-title {
            width: 82%;

            margin:
                0
                auto
                28px
                auto;

            border: 1px solid #222;

            padding: 4px 8px;

            text-align: center;

            font-size: 15px;
            font-weight: normal;
        }

        .asset-code-row {
            width: 82%;

            margin:
                0
                auto
                15px
                auto;

            font-size: 12px;
        }

        .asset-code {
            display: inline-block;

            min-width: 430px;

            padding-bottom: 2px;

            border-bottom: 1px solid #222;
        }

        .photo-table {
            width: 82%;

            margin: 0 auto;

            border-collapse: collapse;

            table-layout: fixed;
        }

        .photo-table td {
            width: 50%;

            padding: 0;

            border: 1px solid #222;

            vertical-align: top;
        }

        .photo-label {
            height: 23px;

            padding-top: 3px;

            border-bottom: 1px solid #222;

            text-align: center;

            font-size: 11px;
        }

        .photo-box {
            height: 235px;

            padding: 8px;

            text-align: center;
            vertical-align: middle;
        }

        .photo-box img {
            max-width: 100%;
            max-height: 215px;
        }

        .empty-photo {
            height: 215px;
        }

        .description-line {
            display: block;

            margin-top: 4px;

            color: #444;
        }

        .small {
            font-size: 9px;
        }
    </style>
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | Datos que recibirá este formato
    |--------------------------------------------------------------------------
    |
    | No son columnas nuevas de la base de datos.
    | $bajasFormato será preparado posteriormente desde
    | ReporteDataService.
    |
    */

    $bajasFormato = collect(
        $bajasFormato ?? []
    );

    $logo = public_path(
        'images/logo-grand-palladium.png'
    );
@endphp


@forelse ($bajasFormato as $baja)

    @php
        $fechaBaja =
            data_get(
                $baja,
                'fecha_baja'
            );

        $codigoActivo =
            data_get(
                $baja,
                'codigo_inventario'
            );

        $nombreEquipo =
            data_get(
                $baja,
                'nombre_equipo'
            );

        $tipoEquipo =
            data_get(
                $baja,
                'tipo_equipo'
            );

        $marca =
            data_get(
                $baja,
                'marca'
            );

        $modelo =
            data_get(
                $baja,
                'modelo'
            );

        $numeroSerie =
            data_get(
                $baja,
                'numero_serie'
            );

        $departamento =
            data_get(
                $baja,
                'departamento'
            );

        $motivoBaja =
            data_get(
                $baja,
                'motivo_baja'
            );

        $comentarios =
            data_get(
                $baja,
                'comentarios'
            );

        /*
        |--------------------------------------------------------------------------
        | Evidencias
        |--------------------------------------------------------------------------
        |
        | Estos valores serán preparados después desde la tabla evidencias.
        | No estamos creando columnas nuevas.
        |
        */

        $fotoFrontal =
            data_get(
                $baja,
                'evidencia_frontal'
            );

        $fotoTrasera =
            data_get(
                $baja,
                'evidencia_trasera'
            );

        $fotoLateral1 =
            data_get(
                $baja,
                'evidencia_lateral_1'
            );

        $fotoLateral2 =
            data_get(
                $baja,
                'evidencia_lateral_2'
            );
    @endphp


    {{-- ============================================================
        PÁGINA 1
        ORDEN DE DECOMISO
    ============================================================ --}}
    <div class="page page-break">

        @if (is_file($logo))
            <img
                src="{{ $logo }}"
                alt="Grand Palladium"
                class="logo"
            >
        @endif


        <div class="document-title">
            Orden de decomiso de activos
        </div>


        {{-- Fecha --}}
        <div class="field-row">

            <span class="field-label">
                Fecha:
            </span>

            <span class="field-value">
                @if ($fechaBaja)
                    {{
                        \Illuminate\Support\Carbon::parse(
                            $fechaBaja
                        )->format('d/m/Y')
                    }}
                @endif
            </span>

        </div>


        {{-- Departamento --}}
        <div class="field-row">

            <span class="field-label">
                Departamento:
            </span>

            <span class="field-value">
                {{ $departamento ?? '' }}
            </span>

        </div>


        {{-- ========================================================
            TABLA DE ACTIVO
        ======================================================== --}}
        <table class="asset-table">

            <thead>
                <tr>

                    <th class="col-cantidad">
                        Cantidad
                    </th>

                    <th class="col-unidad">
                        Unidad
                    </th>

                    <th class="col-descripcion">
                        Descripción del artículo
                    </th>

                    <th class="col-motivo">
                        Motivo de baja
                    </th>

                </tr>
            </thead>

            <tbody>

                <tr>

                    {{-- Cantidad --}}
<td style="text-align: center;">
    {{ data_get($baja, 'cantidad', 1) }}
</td>

{{-- Unidad --}}
<td style="text-align: center;">
    {{ data_get($baja, 'unidad', 'PZA') }}
</td>


                    {{-- Descripción --}}
<td>

    @if ($nombreEquipo)
        <strong>
            {{ $nombreEquipo }}
        </strong>
    @endif

    @if ($tipoEquipo)
        <span class="description-line">
            Tipo: {{ $tipoEquipo }}
        </span>
    @endif

    @if ($marca)
        <span class="description-line">
            Marca: {{ $marca }}
        </span>
    @endif

    @if ($modelo)
        <span class="description-line">
            Modelo: {{ $modelo }}
        </span>
    @endif

    @if ($numeroSerie)
        <span class="description-line">
            Serie: {{ $numeroSerie }}
        </span>
    @endif

</td>


                    {{-- Motivo --}}
                    <td>

                        {{ $motivoBaja ?? '' }}

                    </td>

                </tr>


                {{-- Espacios adicionales como en el formato original --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

            </tbody>

        </table>


        {{-- ========================================================
            RESPONSABLE / MANTENIMIENTO
        ======================================================== --}}
        <table class="signature-table">

            <tr>

                <td class="signature-cell">

                    <div class="signature-title">
                        Responsable departamental
                    </div>

                    <div class="signature-body">

                        <div class="signature-date">
                            Fecha:
                        </div>

                    </div>

                </td>


                <td class="signature-cell">

                    <div class="signature-title">
                        Mantenimiento (si aplica)
                    </div>

                    <div class="signature-body">

                        <div class="signature-date">
                            Fecha:
                        </div>

                    </div>

                </td>

            </tr>

        </table>


        {{-- ========================================================
            GERENTE / AUDITOR
        ======================================================== --}}
        <table class="signature-table">

            <tr>

                <td class="signature-cell">

                    <div class="signature-title">
                        Gerente de hotel/Gerente manager
                    </div>

                    <div class="signature-body">

                        <div class="signature-date">
                            Fecha:
                        </div>

                    </div>

                </td>


                <td class="signature-cell">

                    <div class="signature-title">
                        Auditor de costos
                    </div>

                    <div class="signature-body">

                        <div class="signature-date">
                            Fecha:
                        </div>

                    </div>

                </td>

            </tr>

        </table>

    </div>



    {{-- ============================================================
        PÁGINA 2
        EVIDENCIAS FOTOGRÁFICAS
    ============================================================ --}}
    <div
        class="
            page
            {{ ! $loop->last ? 'page-break' : '' }}
        "
    >

        @if (is_file($logo))
            <img
                src="{{ $logo }}"
                alt="Grand Palladium"
                class="logo"
            >
        @endif


        <div class="photo-title">
            Evidencias Fotográficas
        </div>


        {{-- Código del activo --}}
        <div class="asset-code-row">

            Código de activo (si aplica):

            <span class="asset-code">
                {{ $codigoActivo ?? '' }}
            </span>

        </div>


        {{-- ========================================================
            FOTOGRAFÍAS
        ======================================================== --}}
        <table class="photo-table">

            <tr>

                {{-- Vista frontal --}}
                <td>

                    <div class="photo-label">
                        Vista frontal
                    </div>

                    <div class="photo-box">

                        @if ($fotoFrontal)

                            <img
                                src="{{ $fotoFrontal }}"
                                alt="Vista frontal"
                            >

                        @else

                            <div class="empty-photo"></div>

                        @endif

                    </div>

                </td>


                {{-- Vista trasera --}}
                <td>

                    <div class="photo-label">
                        Vista trasera
                    </div>

                    <div class="photo-box">

                        @if ($fotoTrasera)

                            <img
                                src="{{ $fotoTrasera }}"
                                alt="Vista trasera"
                            >

                        @else

                            <div class="empty-photo"></div>

                        @endif

                    </div>

                </td>

            </tr>


            <tr>

                {{-- Lateral 1 --}}
                <td>

                    <div class="photo-label">
                        Vista Lateral 1
                    </div>

                    <div class="photo-box">

                        @if ($fotoLateral1)

                            <img
                                src="{{ $fotoLateral1 }}"
                                alt="Vista lateral 1"
                            >

                        @else

                            <div class="empty-photo"></div>

                        @endif

                    </div>

                </td>


                {{-- Lateral 2 --}}
                <td>

                    <div class="photo-label">
                        Vista lateral 2
                    </div>

                    <div class="photo-box">

                        @if ($fotoLateral2)

                            <img
                                src="{{ $fotoLateral2 }}"
                                alt="Vista lateral 2"
                            >

                        @else

                            <div class="empty-photo"></div>

                        @endif

                    </div>

                </td>

            </tr>

        </table>

    </div>

@empty

    <div
        style="
            padding: 40px;
            text-align: center;
        "
    >
        No hay bajas disponibles para generar el documento.
    </div>

@endforelse

</body>
</html>
