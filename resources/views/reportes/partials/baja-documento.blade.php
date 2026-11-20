@php
    $filas = $filas ?? app(\App\Services\DatosDocumentoBajaService::class)->filas($documento ?? []);
    $fotos = $fotos ?? ($documento['fotos'] ?? []);
    $logo = ($mostrarLogo ?? true) ? ($documento['logo'] ?? app(\App\Services\ImagenPdfService::class)->logoDataUri()) : '';
@endphp
<div style="font-family:DejaVu Sans,Arial,sans-serif;background:#fff;color:#111;padding:12px;font-size:10px;">
@if (count($filas) >= 2)
    @include('reportes.partials.bajas-tabla', ['filas' => $filas])
@else
    @if($logo)<img src="{{ $logo }}" style="max-width:140px;max-height:70px;" alt="Logo de la organización">@endif
    <h2 style="text-align:center;font-size:15px;">ACTA DE BAJA DE BIENES MUEBLES Y EQUIPOS</h2>
    <p>Fecha de generación: {{ \App\Support\Fecha::dia(now()) }}</p>
    <table style="width:100%;border-collapse:collapse;">
    @foreach(['fecha'=>'Fecha de baja','solicitante'=>'Solicitante','area'=>'Departamento','descripcion'=>'Descripción del bien','inventario'=>'Número de inventario','serie'=>'Serie','marca'=>'Marca','modelo'=>'Modelo','ubicacion'=>'Ubicación','observacion'=>'Motivo de baja'] as $campo=>$etiqueta)
        <tr><th style="border:1px solid #222;background:#ddd;text-align:left;padding:8px;width:30%;">{{ $etiqueta }}</th><td style="border:1px solid #222;padding:8px;">{{ filled($filas[0][$campo] ?? null) ? $filas[0][$campo] : 'Sin dato' }}</td></tr>
    @endforeach
    </table>
@endif
@include('reportes.partials.firmas-inventario')
@if(collect($fotos)->filter()->isNotEmpty())
    <div style="page-break-before:always;">
    @if($logo)<img src="{{ $logo }}" style="max-width:140px;max-height:70px;" alt="Logo de la organización">@endif
    <h3>Evidencia fotográfica</h3>
    <table style="width:100%;table-layout:fixed;">
    @foreach(array_chunk(collect($fotos)->filter()->all(), 2, true) as $grupo)
        <tr>@foreach($grupo as $titulo=>$imagen)<td style="text-align:center;padding:12px;vertical-align:top;"><p>{{ $titulo }}</p><img src="{{ $imagen }}" style="max-width:280px;max-height:230px;"></td>@endforeach</tr>
    @endforeach
    </table></div>
@endif
</div>
