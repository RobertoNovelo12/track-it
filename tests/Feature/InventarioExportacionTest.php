<?php

namespace Tests\Feature;

use App\Exports\InventarioEquiposExport;
use App\Services\InventarioDocumentoService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class InventarioExportacionTest extends TestCase
{
    private function equipo(): array
    {
        return array_fill_keys([
            'codigo', 'nombre', 'tipo', 'marca', 'modelo', 'serie', 'service_tag',
            'estado', 'situacion', 'responsable', 'ultimo_responsable', 'hotel',
            'departamento', 'area', 'ubicacion', 'condicion', 'host', 'ip', 'mac',
            'proveedor', 'factura', 'fecha_compra', 'fecha_garantia', 'fecha_registro',
            'fecha_asignacion', 'fecha_baja', 'motivo_baja', 'comentarios_baja', 'descripcion',
        ], '—') + [
            'especificaciones' => ['Memoria' => '16 GB'],
            'historial' => [
                ['fecha' => '2026-01-01', 'tipo' => 'Alta', 'detalle' => 'Registro inicial'],
                ['fecha' => '2026-02-01', 'tipo' => 'Reasignación', 'detalle' => 'Responsable anterior / nuevo'],
                ['fecha' => '2026-03-01', 'tipo' => 'Baja', 'detalle' => 'Daño irreversible'],
            ],
        ];
    }

    public function test_individual_pdf_only_contains_identification_and_location(): void
    {
        $equipo = array_replace($this->equipo(), ['codigo' => 'ACT-001']);
        $html = view('inventario-exportacion.ficha', ['equipo' => $equipo, 'generadoEn' => now()])->render();
        foreach (['Identificación del equipo', 'Responsable y ubicación'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('Historial completo', $html);
        $this->assertStringNotContainsString('Información técnica y administrativa', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $response = app(InventarioDocumentoService::class)->descargar([$equipo]);
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('ficha_ACT-001.pdf', $response->headers->get('Content-Disposition'));
        ob_start();
        $response->sendContent();
        $bytes = ob_get_clean();
        $this->assertStringStartsWith('%PDF-', $bytes);
    }

    public function test_multiple_export_has_table_and_all_histories(): void
    {
        $equipos = [$this->equipo(), array_replace($this->equipo(), ['codigo' => 'ACT-002'])];
        $rows = (new InventarioEquiposExport($equipos))->array();
        $this->assertCount(3, $rows);
        $column = array_search('Historial completo', $rows[0], true);
        $this->assertNotFalse($column);
        $this->assertStringContainsString('Reasignación', $rows[2][$column]);
        $this->assertStringContainsString('Baja', $rows[1][$column]);
        $html = view('inventario-exportacion.tabla', ['equipos' => $equipos, 'generadoEn' => now()])->render();
        $this->assertSame(0, substr_count($html, 'Historial completo'));
        $this->assertStringNotContainsString('Ficha informativa de equipo', $html);
        $this->assertStringContainsString('ACT-002', $html);
    }

    public function test_excel_preserves_serials_and_does_not_evaluate_formulas(): void
    {
        $export = new InventarioEquiposExport([$this->equipo()]);
        $sheet = (new Spreadsheet())->getActiveSheet();
        foreach (['A1' => '0000123', 'A2' => '=1+1'] as $coordinate => $value) {
            $cell = $sheet->getCell($coordinate);
            $export->bindValue($cell, $value);
            $this->assertSame('s', $cell->getDataType());
            $this->assertSame($value, $cell->getValue());
        }
        $this->assertContains(['HISTORIAL COMPLETO', ''], $export->array());
    }
    public function test_excel_uses_normal_row_heights_and_no_filters(): void
    {
        $export = new InventarioEquiposExport([$this->equipo(), $this->equipo()]);
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray($export->array());
        $export->styles($sheet);
        $this->assertSame('', $sheet->getAutoFilter()->getRange());
        $this->assertEquals(15, $sheet->getRowDimension(2)->getRowHeight());
        $this->assertFalse($sheet->getStyle('A2')->getAlignment()->getWrapText());
    }

}
