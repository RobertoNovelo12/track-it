@php
    $nombresFirmas = $firmasDocumento ?? [];
    $fechaFirma = $fechaDocumento ?? '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFirma)) { $fechaFirma = \Carbon\Carbon::parse($fechaFirma)->format('d/m/Y'); }
@endphp
<style>
    table.firmas-baja { width:100%; table-layout:fixed; border-collapse:collapse; border-spacing:0; page-break-inside:avoid; margin-top:8px; }
    table.firmas-baja td { border:0; padding:0 0 6px; width:25%; vertical-align:top; }
    .firmas-baja .firma-recuadro { border:1px solid #d1d5db; color:#273f57; font-size:8px; }
    .firmas-baja .firma-cargo { border-bottom:1px solid #d1d5db; text-align:center; padding:3px; font-weight:bold; }
    .firmas-baja .firma-nombre { padding:4px 6px; min-height:12px; }
    .firmas-baja .firma-espacio { height:25px; }
    .firmas-baja .firma-fecha { padding:3px 6px; }
    .firmas-baja td.firma-izquierda { padding-right:4px; }
    .firmas-baja td.firma-derecha { padding-left:4px; }
</style>
<table class="firmas-baja">
    @foreach (array_chunk(\App\Livewire\EquipoBaja::FIRMANTES, 2, true) as $fila)
        <tr>
            @if (count($fila) === 1)<td></td>@endif
            @foreach ($fila as $clave => $titulo)
                <td colspan="2" class="{{ count($fila) === 2 ? ($loop->first ? 'firma-izquierda' : 'firma-derecha') : '' }}"><div class="firma-recuadro">
                    <div class="firma-cargo">{{ $titulo }}</div>
                    <div class="firma-nombre">{{ $nombresFirmas[$clave] ?? '' }}</div>
                    <div class="firma-espacio"></div>
                    <div class="firma-fecha">Fecha: {{ $fechaFirma ?: '—' }}</div>
                </div></td>
            @endforeach
            @if (count($fila) === 1)<td></td>@endif
        </tr>
    @endforeach
</table>
