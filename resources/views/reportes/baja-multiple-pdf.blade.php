<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>{{ $documento['folio'] }}</title><style>@page { margin:24px; } body { margin:0; }</style></head><body>
@include('reportes.partials.baja-multiple-documento', ['documento' => $documento])
</body></html>
