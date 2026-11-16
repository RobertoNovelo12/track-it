<?php

namespace App\Exports;

use App\Services\InventarioGlobalReporteService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromArray, WithStyles, WithColumnWidths, WithCustomValueBinder};
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class InventarioGlobalReporteExport extends StringValueBinder implements FromArray, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    public function __construct(private Collection $filas, private string $formato) {}
    public function array(): array
    {
        $servicio = app(InventarioGlobalReporteService::class);
        $columnas = $servicio->columnas($this->formato);
        return [[$servicio->titulo($this->formato)], array_values($columnas), ...$this->filas->map(fn ($fila) => array_map(fn ($clave) => $servicio->celda($fila, $clave), array_keys($columnas)))->all()];
    }
    public function columnWidths(): array
    {
        $columnas = app(InventarioGlobalReporteService::class)->columnas($this->formato);
        $anchos = [];
        foreach (array_keys($columnas) as $i => $clave) { $anchos[Coordinate::stringFromColumnIndex($i + 1)] = in_array($clave, ['descripcion', 'descripcion_uso', 'domicilio_inmueble', 'resguardatario', 'unidad']) ? 35 : 22; }
        return $anchos;
    }
    public function styles(Worksheet $sheet): array
    {
        $fin = Coordinate::stringFromColumnIndex(count(app(InventarioGlobalReporteService::class)->columnas($this->formato)));
        $sheet->mergeCells('A1:'.$fin.'1');
        $sheet->freezePane('A3');
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true)->setVertical('center');
        $sheet->getStyle('A2:'.$fin.$sheet->getHighestRow())->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(48);
        return [1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']], 2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '9DC3E6']], 'alignment' => ['horizontal' => 'center']]];
    }
}
