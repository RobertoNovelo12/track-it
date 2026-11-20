<div style="page-break-inside:avoid;margin-top:36px;font-family:DejaVu Sans,Arial,sans-serif;color:#111;">
    @foreach (\App\Services\InventarioGlobalReporteService::FIRMAS as $tituloFirma => $cargoFirma)
        <div style="width:60%;margin:0 auto {{ $loop->last ? '0' : '64px' }};text-align:center;font-size:11px;">
            <div>{{ $tituloFirma }}</div>
            <div style="height:80px;"></div>
            <div style="border-top:1px solid #222;padding-top:10px;">Nombre y firma</div>
            <div style="margin-top:12px;line-height:1.4;">{{ $cargoFirma }}</div>
        </div>
    @endforeach
</div>
