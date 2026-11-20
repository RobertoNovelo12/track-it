<?php
namespace Tests\Feature;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;
class FormatoBajasPdfTest extends TestCase
{
    public function test_ficha_individual_y_tabla_desde_dos_con_dos_firmas_y_fotos(): void
    {
        $this->mock(\App\Services\ImagenPdfService::class)->shouldReceive('logoDataUri')->andReturn(null);
        $foto = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=';
        foreach ([1, 2, 10] as $cantidad) {
            $documento = ['fecha'=>'2026-11-19','solicitante'=>'María Elena López Pérez','motivo'=>'Daño irreversible','numero_ticket'=>'NO MOSTRAR', 'fotos'=>['Vista frontal'=>$foto],
                'equipos'=>array_fill(0,$cantidad,['nombre_equipo'=>'Mesa escolar','inventario'=>'000045','departamento'=>'Recursos materiales','ubicacion'=>'Almacén','numero_serie'=>'SN123','marca'=>'Marca','modelo'=>'Modelo'])];
            $html = view('reportes.baja-multiple-pdf',compact('documento'))->render();
            $this->assertSame(2, substr_count($html,'Nombre y firma'));
            foreach (['María Elena López Pérez','Recursos materiales','Almacén','Daño irreversible','000045','Evidencia fotográfica',$foto] as $texto) { $this->assertStringContainsString($texto,$html); }
            $this->assertStringNotContainsString('NO MOSTRAR',$html);
            if ($cantidad >= 2) { $this->assertStringContainsString('DEPARTAMENTO</th>',$html); }
            else { $this->assertStringContainsString('Descripción del bien',$html); }
            $pdf = Pdf::loadHTML($html)->setPaper('letter',$cantidad>=2?'landscape':'portrait');
            $this->assertStringStartsWith('%PDF-',$pdf->output());
            $this->assertGreaterThanOrEqual(2,$pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }
}