@php
    $servicio = app(\App\Services\InventarioGlobalReporteService::class);
    $columnas = $servicio->columnas($formato);
@endphp
<div style="font-family: DejaVu Sans, Arial, sans-serif; color:#111; background:white; padding:12px;">
    <h2 style="font-size:14px;text-align:center;margin:8px 0 20px;">{{ $servicio->titulo($formato) }}</h2>
    <table style="width:100%;border-collapse:collapse;table-layout:fixed;font-size:8px;">
        <thead style="display:table-header-group;"><tr>
            @foreach ($columnas as $titulo)<th style="border:1px solid #222;background:#9dc3e6;text-align:center;padding:7px 3px;word-wrap:break-word;">{{ $titulo }}</th>@endforeach
        </tr></thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr style="page-break-inside:avoid;">@foreach ($columnas as $clave => $titulo)<td style="border:1px solid #222;padding:5px 3px;word-wrap:break-word;{{ in_array($clave, ['costo_unitario','iva','total']) ? 'text-align:right;' : '' }}">{{ $servicio->celda($fila, $clave) }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($columnas) }}" style="border:1px solid #222;padding:20px;text-align:center;">{{ $mensajeVacio ?? 'No hay bienes con los filtros seleccionados.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
