<?php

namespace Tests\Feature;

use App\Livewire\EquipoBaja;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class FormatoBajasPdfTest extends TestCase
{
    public function test_individual_and_multiple_have_two_pages_with_five_signers(): void
    {
        $firmas = array_fill_keys(array_keys(EquipoBaja::FIRMANTES), 'Nombre de prueba');
        $documento = [
            'logo' => '', 'organizacion' => 'Hotel de prueba', 'folio' => 'BM-PRUEBA',
            'fecha' => '2026-10-23', 'hotel' => 'Hotel', 'departamento' => 'Sistemas',
            'solicitante' => 'Solicitante', 'motivo' => 'Fin de vida útil', 'observaciones' => '',
            'autoriza' => 'NO MOSTRAR', 'puesto' => 'NO MOSTRAR', 'firmas' => $firmas, 'fotos' => [],
            'equipos' => array_fill(0, 10, ['codigo_inventario' => 'ACT-00061', 'nombre_equipo' => 'Laptop de prueba', 'marca' => 'Lenovo', 'modelo' => 'ThinkPad', 'numero_serie' => 'SERIE-123']),
        ];
        $baja = ['folio' => 'B-PRUEBA', 'fecha' => '23/10/2026', 'motivo' => 'Fin de vida útil', 'comentarios' => '', 'autoriza' => 'NO MOSTRAR', 'firmas' => $firmas, 'fotos' => [], 'equipo' => []];
        foreach ([['reportes.baja-multiple-pdf', ['documento' => $documento]], ['reportes.baja-acta-pdf', ['bajas' => collect([$baja])]]] as [$view, $data]) {
            $html = view($view, $data)->render();
            $this->assertStringNotContainsString('NO MOSTRAR', $html);
            foreach (EquipoBaja::FIRMANTES as $label) { $this->assertStringContainsString($label, $html); }
            $partial = $view === 'reportes.baja-multiple-pdf' ? 'reportes.partials.firmas-baja-multiple' : 'reportes.partials.firmas-baja';
            $firmasHtml = view($partial, ['firmasDocumento' => $firmas, 'fechaDocumento' => '2026-10-23'])->render();
            $this->assertSame(5, substr_count($firmasHtml, 'Fecha:'));
            $this->assertStringNotContainsString('23/10/2026', $firmasHtml);
            $this->assertStringContainsString('Sistemas', $firmasHtml);
            $pdf = Pdf::loadHTML($html)->setPaper('letter');
            $pdf->output();
            $this->assertSame(2, $pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }
}
