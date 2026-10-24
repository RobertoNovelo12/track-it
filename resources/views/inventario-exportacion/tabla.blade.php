<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de equipos</title>
    <style>
        @page { margin: 24px 28px 35px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.2; color: #28394a; }
        .marca { font-weight: bold; color: #a28b53; font-size: 14px; letter-spacing: 1px; }
        h1 { margin: 6px 0; font-size: 15px; color: #18354b; }
        .sub { color: #657b8d; font-size: 8px; margin: 4px 0 13px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #1f4057; color: white; font-weight: bold; font-size: 8px; padding: 5px 4px; text-align: left; }
        td { border: 1px solid #d7dfe5; padding: 3px; vertical-align: top; word-wrap: break-word; }
        tr:nth-child(even) td { background: #f4f7f9; }
        tr { page-break-inside: avoid; }
        .pie { position: fixed; bottom: -22px; left: 0; right: 0; text-align: right; color: #738595; font-size: 7px; }
        .mini { font-size: 8px; }
        td div + div { margin-top: 2px; }
        thead { display: table-header-group; }
    </style>
</head>
<body>
    <div class="pie">Track-IT · Emitido el {{ $generadoEn->format('d/m/Y H:i') }}</div>
    @include('inventario-exportacion.logo')
    <h1>Inventario de equipos tecnológicos</h1>
    <div class="sub">{{ count($equipos) }} equipos incluidos · Estado y responsable según registros del sistema · {{ $generadoEn->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th style="width:18%">Equipo</th>
                <th style="width:19%">Identificación</th>
                <th style="width:13%">Estado / situación</th>
                <th style="width:23%">Responsable / ubicación</th>
                <th style="width:27%">Especificaciones / descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipos as $e)
                <tr>
                    <td>
                        <div><strong>{{ $e['codigo'] }}</strong></div>
                        <div>{{ $e['nombre'] }}</div>
                        <div><strong>Tipo:</strong> {{ $e['tipo'] }}</div>
                    </td>
                    <td>
                        <div><strong>Marca:</strong> {{ $e['marca'] }}</div>
                        <div><strong>Modelo:</strong> {{ $e['modelo'] }}</div>
                        <div><strong>Serie:</strong> {{ $e['serie'] }}</div>
                    </td>
                    <td>
                        <div><strong>Estado:</strong> {{ $e['estado'] }}</div>
                        <div><strong>Situación:</strong> {{ $e['situacion'] }}</div>
                    </td>
                    <td>
                        <div><strong>Responsable:</strong> {{ $e['responsable'] }}</div>
                        <div><strong>Hotel:</strong> {{ $e['hotel'] }}</div>
                        <div><strong>Departamento:</strong> {{ $e['departamento'] }}</div>
                        <div><strong>Ubicación:</strong> {{ $e['ubicacion'] }}</div>
                    </td>
                    <td class="mini">
                        @foreach($e['especificaciones'] as $nombre => $valor)
                            <strong>{{ $nombre }}:</strong> {{ $valor }}@if(!$loop->last); @endif
                        @endforeach
                        @if($e['motivo_baja'] !== '—')
                            <div><strong>Baja:</strong> {{ $e['motivo_baja'] }} ({{ $e['fecha_baja'] }})</div>
                        @endif
                        @if(($e['programas'] ?? []) !== [])
                            <div><strong>Programas:</strong> {{ implode(', ', array_column($e['programas'], 'nombre')) }}</div>
                        @endif
                        @if($e['descripcion'] !== '—')
                            <div><strong>Descripción:</strong> {{ $e['descripcion'] }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
