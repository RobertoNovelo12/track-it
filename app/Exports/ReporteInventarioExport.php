<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class ReporteInventarioExport implements
FromCollection,
WithHeadings,
WithMapping,
WithStyles,
ShouldAutoSize,
WithColumnWidths,
WithTitle,
WithCustomCsvSettings
{
    public function __construct(
        private readonly Collection $datos
    ) {
    }

    public function collection(): Collection
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            'Tipo de reporte',
            'Código / Folio',
            'Equipo',
            'Tipo de equipo',
            'Marca',
            'Modelo',
            'Número de serie',
            'Estado',
            'Fecha',
            'Detalle',
        ];
    }

    public function map($fila): array
    {
        $tipoReporte = data_get($fila, 'tipo_reporte_etiqueta')
            ?: data_get($fila, 'tipo_reporte')
            ?: 'General';

        return [
            $tipoReporte,
            data_get($fila, 'codigo') ?: '—',
            data_get($fila, 'elemento') ?: '—',
            data_get($fila, 'tipo') ?: '—',
            data_get($fila, 'marca') ?: '—',
            data_get($fila, 'modelo') ?: '—',
            data_get($fila, 'serie') ?: '—',
            data_get($fila, 'estado') ?: '—',
            $this->formatearFecha(data_get($fila, 'fecha')),
            data_get($fila, 'detalle') ?: '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => [
                    'bold'  => true,
                    'color' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
                'fill'      => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF247BA0',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 18,
            'C' => 28,
            'J' => 55,
        ];
    }

    public function title(): string
    {
        return 'Reporte de activos';
        
    }

    public function getCsvSettings(): array
{
    return [
        'delimiter' => ',',
        'use_bom' => true,
        'excel_compatibility' => true,
    ];
}

    private function formatearFecha(mixed $fecha): string
    {
        if (blank($fecha)) {
            return '—';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y');
        } catch (Throwable) {
            return (string) $fecha;
        }
    }
}
