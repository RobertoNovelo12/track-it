<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Bajas</title><style>@page { margin:90px 28px 28px; } body { margin:0; }</style></head><body>@php($logoPdf = app(\App\Services\ImagenPdfService::class)->logoDataUri())
@if($logoPdf)<div style="position:fixed;top:-65px;left:12px;"><img src="{{ $logoPdf }}" style="max-width:120px;max-height:60px;"></div>@endif

@php
    $filas = []; $fotos = [];
    foreach ($bajas as $baja) {
        $doc = !empty($baja['conjunta']) ? $baja['documento'] : $baja;
        $filas = array_merge($filas, app(\App\Services\DatosDocumentoBajaService::class)->filas($doc));
        foreach (($doc['fotos'] ?? []) as $titulo=>$imagen) { if($imagen) { $fotos[($doc['folio'] ?? 'Baja').' — '.$titulo] = $imagen; } }
    }
@endphp
@include('reportes.partials.baja-documento', ['filas'=>$filas, 'fotos'=>$fotos, 'documento'=>[], 'mostrarLogo'=>false])
</body></html>