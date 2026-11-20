<?php

namespace App\Exports;

use App\Services\InventarioGlobalReporteService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromArray, WithStyles, WithColumnWidths, WithCustomValueBinder, WithDrawings};
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class InventarioGlobalReporteExport extends StringValueBinder implements FromArray, WithStyles, WithColumnWidths, WithCustomValueBinder, WithDrawings
{
    public function __construct(private Collection $filas, private string $formato, private ?string $fechaCorte = null, private bool $incluirPresentacion = true) {}
    public function array(): array
    {
        $servicio = app(InventarioGlobalReporteService::class);
        $columnas = $servicio->columnas($this->formato);
        $filas = [[$servicio->titulo($this->formato, $this->fechaCorte)], array_values($columnas), ...$this->filas->map(fn ($fila) => array_map(fn ($clave) => $servicio->celda($fila, $clave), array_keys($columnas)))->all()];
        if ($this->formato === 'comodato' && $this->filas->isNotEmpty()) {
            $filas[] = array_map(fn ($clave) => $clave === 'solicitante' ? 'Totales' : (in_array($clave, ['costo_unitario', 'iva', 'total']) ? number_format($this->filas->sum(fn ($fila) => is_numeric(data_get($fila, $clave)) ? (float) data_get($fila, $clave) : 0), 2, '.', ',') : ''), array_keys($columnas));
        }
        return $filas;
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
        if (! $this->incluirPresentacion) { return []; }
        $fin = Coordinate::stringFromColumnIndex(count(app(InventarioGlobalReporteService::class)->columnas($this->formato)));
        $sheet->mergeCells('A1:'.$fin.'1');
        $sheet->freezePane('A3');
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true)->setVertical('center');
        $sheet->getStyle('A2:'.$fin.$sheet->getHighestRow())->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(48);
        // Reservar un encabezado para el logo sin mezclarlo con los datos del CSV.
        $sheet->insertNewRowBefore(1);
        foreach ($sheet->getDrawingCollection() as $logo) { $logo->setCoordinates('A1'); }
        $sheet->getRowDimension(1)->setRowHeight(70);
        $sheet->mergeCells('B1:'.$fin.'1');
        $sheet->setCellValue('B1', 'Fecha de generación: '.\App\Support\Fecha::dia(now()));
        $sheet->getStyle('B1:'.$fin.'1')->getAlignment()->setHorizontal('left')->setVertical('center');
        $sheet->getStyle('B1:'.$fin.'1')->getFont()->setSize(13);
        $sheet->freezePane('A4');
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
        $n = count(app(InventarioGlobalReporteService::class)->columnas($this->formato));
        $filaFirma = $sheet->getHighestRow() + 3;
        $a = Coordinate::stringFromColumnIndex(1 + intdiv($n, 4));
        $b = Coordinate::stringFromColumnIndex($n - intdiv($n, 4));
        foreach (InventarioGlobalReporteService::FIRMAS as $tituloFirma => $cargoFirma) {
            foreach ([$filaFirma, $filaFirma + 2, $filaFirma + 3] as $fila) {
                $sheet->mergeCells($a.$fila.':'.$b.$fila);
                $sheet->getStyle($a.$fila.':'.$b.$fila)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            }
            $sheet->setCellValue($a.$filaFirma, $tituloFirma);
            $sheet->setCellValue($a.($filaFirma + 2), 'Nombre y firma');
            $sheet->setCellValue($a.($filaFirma + 3), $cargoFirma);
            $sheet->getStyle($a.($filaFirma + 2).':'.$b.($filaFirma + 2))->getBorders()->getTop()->setBorderStyle('thin');
            $sheet->getRowDimension($filaFirma + 1)->setRowHeight(65);
            $sheet->getRowDimension($filaFirma + 3)->setRowHeight(45);
            $sheet->getRowDimension($filaFirma + 4)->setRowHeight(24);
            $sheet->getRowDimension($filaFirma + 5)->setRowHeight(24);
            $filaFirma += 6;
        }
        return [2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']], 3 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '9DC3E6']], 'alignment' => ['horizontal' => 'center']]];
    }

    public function drawings(): array
    {
        if (! $this->incluirPresentacion) { return []; }
        $uri = app(\App\Services\ImagenPdfService::class)->logoDataUri();
        if (! $uri) { return []; }
        $contenido = base64_decode(explode(',', $uri, 2)[1] ?? '', true);
        if (! $contenido) { return []; }
        $logo = \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::fromString($contenido);
        $logo->setName('Logo de la organización');
        $logo->setCoordinates('A1');
        $logo->setHeight(75);
        if ($logo->getWidth() > 150) { $logo->setWidth(150); }
        $logo->setOffsetX(8);
        $logo->setOffsetY(6);
        return [$logo];
    }
}
