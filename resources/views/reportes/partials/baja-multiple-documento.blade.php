<style>
    .bm-documento { background:#fff; color:#15304e; font-family:DejaVu Sans,Arial,sans-serif; padding:24px; font-size:10px; line-height:1.5; overflow-wrap:anywhere; }
    .bm-documento .bm-marca { color:#bb900d; font-size:24px; letter-spacing:2px; line-height:1.1; }
    .bm-documento .bm-grupo { color:#bb900d; font-size:7px; letter-spacing:4px; margin-bottom:15px; }
    .bm-documento .bm-institucion { border-bottom:2px solid #bb900d; padding-bottom:6px; font-size:7px; }
    .bm-documento h2 { color:#164285; font-size:12px; margin:10px 0 3px; font-weight:bold; }
    .bm-documento h3 { color:#164285; font-size:9px; margin:16px 0 6px; font-weight:bold; }
    .bm-documento table { width:100%; border-collapse:collapse; table-layout:fixed; }
    .bm-documento td,.bm-documento th { border:1px solid #cdd8e6; padding:7px; vertical-align:top; word-wrap:break-word; }
    .bm-documento th { background:#edf1f5; font-size:8px; text-align:left; }
    .bm-documento .bm-label { display:block; color:#35619b; font-size:7px; margin-bottom:3px; }
    .bm-documento .bm-meta { text-align:right; font-size:8px; margin:8px 0; }
    .bm-documento .bm-fotos td { width:50%; text-align:center; }
    .bm-documento .bm-fotos img { max-width:100%; height:125px; object-fit:contain; }
    .bm-documento .bm-sin-foto { height:125px; padding-top:40px; color:#778597; font-size:8px; }
    .bm-documento .bm-firmas { margin-top:25px; }
    .bm-documento .bm-firmas td { border:0; width:50%; padding:20px 12px 0; text-align:center; }
    .bm-documento .bm-linea { border-top:1px solid #9baec5; padding-top:5px; }
    .bm-documento .bm-pie { border-top:1px solid #cdd8e6; margin-top:20px; padding-top:6px; color:#778597; font-size:7px; }
    .bm-documento tr,.bm-documento .bm-evidencias { page-break-inside:avoid; }
</style>
<article class="bm-documento">
    <img src="{{ $documento['logo'] }}" alt="{{ $documento['organizacion'] }}" style="max-width:190px; max-height:70px; margin-bottom:15px;">
    <div class="bm-institucion">{{ $documento['organizacion'] }} | CONTROL DE ACTIVOS TI</div>
    <h2>ACTA DE BAJA MÚLTIPLE DE EQUIPOS TECNOLÓGICOS</h2>
    <div>Solicitud de baja de equipos · Departamento de Sistemas</div>
    <div class="bm-meta">Folio: {{ $documento['folio'] }}<br>Fecha: {{ $documento['fecha'] ?: '—' }}</div>
    <h3>01 &nbsp; INFORMACIÓN GENERAL</h3>
    <table><tr><td><span class="bm-label">HOTEL / SEDE</span>{{ $documento['hotel'] ?: '—' }}</td><td><span class="bm-label">DEPARTAMENTO</span>{{ $documento['departamento'] ?: '—' }}</td></tr>
        <tr><td><span class="bm-label">QUIÉN SOLICITA LA BAJA</span>{{ $documento['solicitante'] ?: '—' }}</td><td><span class="bm-label">FECHA DE BAJA</span>{{ $documento['fecha'] ?: '—' }}</td></tr>
        <tr><td colspan="2"><span class="bm-label">MOTIVO DE LA BAJA</span>{{ $documento['motivo'] ?: '—' }}</td></tr>
        <tr><td colspan="2"><span class="bm-label">OBSERVACIONES</span>{{ $documento['observaciones'] ?: '—' }}</td></tr>
    </table>
    <h3>02 &nbsp; AUTORIZACIÓN</h3>
    <table><tr><td><span class="bm-label">QUIÉN AUTORIZA</span>{{ $documento['autoriza'] ?: '—' }}</td><td><span class="bm-label">PUESTO</span>{{ $documento['puesto'] ?: '—' }}</td></tr></table>
    <h3>03 &nbsp; EQUIPOS INCLUIDOS ({{ count($documento['equipos']) }})</h3>
    <table><thead><tr><th>Inventario</th><th>Equipo</th><th>Marca / Modelo</th><th>Número de serie</th></tr></thead><tbody>
        @foreach ($documento['equipos'] as $equipo)<tr><td>{{ $equipo['codigo_inventario'] }}</td><td>{{ $equipo['nombre_equipo'] }}</td><td>{{ $equipo['marca'] ?: '—' }} / {{ $equipo['modelo'] ?: '—' }}</td><td>{{ $equipo['numero_serie'] ?: '—' }}</td></tr>@endforeach
    </tbody></table>
    <div class="bm-evidencias">
        <h3>04 &nbsp; EVIDENCIA FOTOGRÁFICA</h3>
        <table class="bm-fotos"><tr>@foreach (['Vista frontal', 'Vista trasera'] as $titulo)<td><span class="bm-label">{{ $titulo }} de los equipos</span>@if (isset($documento['fotos'][$titulo]))<img src="{{ $documento['fotos'][$titulo] }}" alt="{{ $titulo }}">@else<div class="bm-sin-foto">Evidencia fotográfica no registrada</div>@endif</td>@endforeach</tr></table>
        <table class="bm-firmas"><tr><td><div class="bm-linea">Solicitante<br>{{ $documento['solicitante'] ?: 'Nombre y firma' }}</div></td><td><div class="bm-linea">Autoriza<br>{{ $documento['autoriza'] ?: 'Nombre y firma' }}</div></td></tr></table>
    </div>
    <div class="bm-pie">Departamento de Sistemas · Acta conjunta de equipos previamente dados de baja</div>
</article>
