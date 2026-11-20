@include('reportes.partials.baja-documento', ['documento'=> $baja ?? [], 'filas'=> $baja['filas'] ?? app(\App\Services\DatosDocumentoBajaService::class)->filas($baja ?? [])])
