@php
    $filas = $filas ?? [];
    $logo = $logo ?? app(\App\Services\ImagenPdfService::class)->logoDataUri();
    $dato = fn ($v) => filled($v) ? $v : 'Sin dato';
@endphp
<style>
    .bajas-tabla { width:100%; border-collapse:collapse; font-family:DejaVu Sans, sans-serif; font-size:8px; }
    .bajas-tabla th, .bajas-tabla td { border:1px solid #111; padding:4px; vertical-align:middle; overflow-wrap:break-word; }
    .bajas-tabla th { background:#ddd; text-align:center; }
    .bajas-tabla thead { display:table-header-group; }
    .bajas-tabla tr { page-break-inside:avoid; }
</style>
<div style="font-family:DejaVu Sans,sans-serif;color:#111;">
    @if($logo)<img src="{{ $logo }}" style="height:55px;float:left;">@endif
    <div style="text-align:center;font-size:10px;padding:8px 0 25px;">DEPARTAMENTO RECURSOS MATERIALES Y SERVICIOS<br><br>ALMACÉN - INVENTARIOS</div>
    <h3 style="text-align:center;font-size:12px;clear:both;">BAJAS DE BIENES MUEBLES Y EQUIPOS</h3>
    <p style="font-size:9px;">Fecha de generación: {{ \App\Support\Fecha::dia(now()) }}</p>
    <table class="bajas-tabla">
        <thead><tr>@foreach(['#','FECHA','SOLICITANTE','DEPARTAMENTO','DESCRIPCIÓN','INVENTARIO','SERIE','MARCA','MODELO','UBICACIÓN','OBSERVACIÓN'] as $titulo)<th>{{ $titulo }}</th>@endforeach</tr></thead>
        <tbody>@forelse($filas as $fila)<tr><td>{{ $loop->iteration }}</td>@foreach(['fecha','solicitante','area','descripcion','inventario','serie','marca','modelo','ubicacion','observacion'] as $campo)<td>{{ $dato($fila[$campo] ?? null) }}</td>@endforeach</tr>@empty<tr><td colspan="11" style="text-align:center;">Sin registros</td></tr>@endforelse</tbody>
    </table>
</div>
