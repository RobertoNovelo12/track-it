<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $titulo }}</title>

    <style>
        @page {
            margin: 24px 28px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #3f4548;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        .header {
            width: 100%;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #247ba0;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            width: 125px;
        }

        h1 {
            margin: 0 0 4px;
            color: #25344a;
            font-size: 18px;
        }

        .muted {
            color: #7a7b79;
        }

        .meta {
            width: 100%;
            margin: 0 0 14px;
            border-collapse: collapse;
        }

        .meta td {
            width: 25%;
            padding: 7px 9px;
            border: 1px solid #dde4e8;
            background: #f6f9fa;
        }

        .meta strong {
            display: block;
            color: #25344a;
            font-size: 8px;
            text-transform: uppercase;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data th {
            padding: 7px 5px;
            color: #ffffff;
            background: #247ba0;
            border: 1px solid #1d6688;
            font-size: 8px;
            text-align: left;
        }

        .data td {
            padding: 6px 5px;
            border: 1px solid #dde4e8;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data tr:nth-child(even) td {
            background: #f8fafb;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -22px;
            left: 0;
            color: #7a7b79;
            font-size: 8px;
            text-align: center;
        }

        .empty {
            padding: 30px;
            border: 1px solid #dde4e8;
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <h1>{{ $titulo }}</h1>

                <div class="muted">
                    Sistema de Control de Activos TI
                </div>
            </td>

            <td style="text-align: right;">
                <img
                    src="{{ public_path('images/logo-grand-palladium.png') }}"
                    class="logo"
                    alt="Grand Palladium"
                >
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <strong>Generado por</strong>
                {{ $generadoPor }}
            </td>

            <td>
                <strong>Fecha de generación</strong>
                {{ $generadoEn->format('d/m/Y H:i') }}
            </td>

            <td>
                <strong>Periodo</strong>

                {{ $filtros['fecha_inicio']
                    ? \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y')
                    : 'Sin límite' }}

                —

                {{ $filtros['fecha_fin']
                    ? \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y')
                    : 'Sin límite' }}
            </td>

            <td>
                <strong>Total de registros</strong>
                {{ number_format($datos->count()) }}
            </td>
        </tr>
    </table>

    @if ($datos->isEmpty())
        <div class="empty">
            No se encontraron registros.
        </div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 10%;">Reporte</th>
                    <th style="width: 9%;">Código / Folio</th>
                    <th style="width: 11%;">Equipo</th>
                    <th style="width: 8%;">Tipo</th>
                    <th style="width: 7%;">Marca</th>
                    <th style="width: 8%;">Modelo</th>
                    <th style="width: 10%;">Serie</th>
                    <th style="width: 8%;">Estado</th>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 21%;">Detalle</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($datos as $fila)
                    <tr>
                        <td>{{ $fila->tipo_reporte_etiqueta ?? 'General' }}</td>
                        <td>{{ $fila->codigo ?: '—' }}</td>
                        <td>{{ $fila->elemento ?: '—' }}</td>
                        <td>{{ $fila->tipo ?: '—' }}</td>
                        <td>{{ $fila->marca ?: '—' }}</td>
                        <td>{{ $fila->modelo ?: '—' }}</td>
                        <td>{{ $fila->serie ?: '—' }}</td>
                        <td>{{ $fila->estado ?: '—' }}</td>

                        <td>
                            {{ $fila->fecha
                                ? \Carbon\Carbon::parse($fila->fecha)->format('d/m/Y')
                                : '—' }}
                        </td>

                        <td>{{ $fila->detalle ?: '—' }}</td>
                    </tr>

                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Grand Palladium Riviera Maya · Departamento de Sistemas · {{ $titulo }}
    </div>
</body>
</html>
