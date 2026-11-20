@if ($formato === 'resguardo')
@php
    $filasBaja = collect($filas)->map(fn ($f) => ['fecha'=>\App\Support\Fecha::dia(data_get($f,'fecha_resguardo')), 'solicitante'=>data_get($f,'solicitante'), 'area'=>data_get($f,'unidad'), 'descripcion'=>data_get($f,'descripcion'), 'inventario'=>data_get($f,'inventario'), 'serie'=>data_get($f,'serie'), 'marca'=>data_get($f,'marca'), 'modelo'=>data_get($f,'modelo'), 'ubicacion'=>data_get($f,'ubicacion'), 'observacion'=>data_get($f,'motivo_resguardo')])->all();
@endphp
@include('reportes.partials.baja-documento', ['filas'=>$filasBaja, 'documento'=>[], 'mostrarLogo'=>$mostrarLogo ?? true])
@else
@php
    $servicio = app(\App\Services\InventarioGlobalReporteService::class);
    $columnas = $servicio->columnas($formato);
    $logoOrganizacion = ($mostrarLogo ?? true) ? app(\App\Services\ImagenPdfService::class)->logoDataUri() : null;
@endphp
<div style="font-family: DejaVu Sans, Arial, sans-serif; color:#111; background:white; padding:12px;">
    @if ($logoOrganizacion)
        <div style="margin-bottom:12px;"><img src="{{ $logoOrganizacion }}" alt="Logo de la organización" style="max-width:150px;max-height:80px;"></div>
    @endif
    @if ($formato === 'comodato')
        <p style="text-align:center;font-size:10px;">DEPARTAMENTO RECURSOS MATERIALES Y SERVICIOS</p>
        <p style="text-align:center;font-size:10px;margin-bottom:24px;">ALMACÉN-INVENTARIOS</p>
    @endif
    <h2 style="font-size:14px;text-align:center;margin:8px 0 20px;">{{ $servicio->titulo($formato, $fechaCorte ?? (($fechaFin ?? '') ?: null)) }}</h2>
    <p style="font-size:13px;text-align:left;margin:0 0 16px;">Fecha de generación: {{ \App\Support\Fecha::dia($generadoEn ?? now()) }}</p>
    <table style="width:100%;border-collapse:collapse;table-layout:fixed;font-size:8px;">
        @if ($formato === 'global_resumido')<colgroup><col style="width:28%"><col style="width:49%"><col style="width:23%"></colgroup>@endif
        @if ($formato === 'comodato')
            <colgroup>@foreach ([12,12,10,14,9,6,6,6,5,5,5,4,6] as $ancho)<col style="width:{{ $ancho }}%">@endforeach</colgroup>
        @endif
        <thead style="display:table-header-group;"><tr>
            @foreach ($columnas as $titulo)<th style="border:1px solid #222;background:{{ $formato === 'comodato' ? '#d9d9d9' : '#9dc3e6' }};text-align:center;padding:7px 3px;word-wrap:break-word;">{{ $titulo }}</th>@endforeach
        </tr></thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr style="page-break-inside:avoid;">@foreach ($columnas as $clave => $titulo)<td style="border:1px solid #222;padding:5px 3px;word-wrap:break-word;{{ in_array($clave, ['costo_unitario','iva','total','valor_libros']) ? 'text-align:right;' : '' }}">{{ $clave === 'valor_libros' && is_numeric(data_get($fila, $clave)) ? '$ ' : '' }}{{ $servicio->celda($fila, $clave) }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($columnas) }}" style="border:1px solid #222;padding:20px;text-align:center;">{{ $mensajeVacio ?? 'No hay bienes con los filtros seleccionados.' }}</td></tr>
            @endforelse
            @if ($formato === 'comodato' && count($filas))
                <tr>
                    @foreach ($columnas as $clave => $titulo)
                        <td style="padding:8px 3px;text-align:right;font-weight:bold;">{{ $clave === 'solicitante' ? 'Totales' : (in_array($clave, ['costo_unitario','iva','total']) ? '$ '.number_format(collect($filas)->sum(fn ($fila) => is_numeric(data_get($fila, $clave)) ? (float) data_get($fila, $clave) : 0), 2, '.', ',') : '') }}</td>
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>
    @include('reportes.partials.firmas-inventario')
</div>

@endif
