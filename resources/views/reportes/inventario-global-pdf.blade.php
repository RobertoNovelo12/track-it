<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><style>
    @page { margin:110px 18px 18px; }
    body { margin:0; }
    .logo-organizacion { position:fixed; top:-92px; left:12px; height:80px; }
    .logo-organizacion img { max-width:150px; max-height:80px; }
</style></head>
<body>
@php($logoDocumento = app(\App\Services\ImagenPdfService::class)->logoDataUri())
@if ($logoDocumento)
    <div class="logo-organizacion"><img src="{{ $logoDocumento }}" alt="Logo de la organización"></div>
@endif
@include('reportes.inventario-global-tabla', ['mostrarLogo' => false])
</body></html>
