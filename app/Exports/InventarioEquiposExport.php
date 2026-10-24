<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/** La exportación de un equipo es una ficha; la de varios, una tabla. */
class InventarioEquiposExport extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromArray, WithStyles, WithTitle, \Maatwebsite\Excel\Concerns\WithCustomValueBinder
{
    public function __construct(private readonly array $equipos) {}

    public function title(): string
    {
        return count($this->equipos) === 1 ? 'Ficha del equipo' : 'Inventario';
    }

    public function array(): array
    {
        if (count($this->equipos) === 1) {
            $e = $this->equipos[0];
            $filas = [
                ['FICHA INFORMATIVA DE EQUIPO', ''],
                ['Emitido', now()->format('d/m/Y H:i')],
                ['Nota', 'Documento de consulta del inventario. No sustituye un acta oficial de alta o baja.'],
                ['Código de inventario', $e['codigo']],
                ['Nombre', $e['nombre']], ['Tipo', $e['tipo']],
                ['Marca', $e['marca']], ['Modelo', $e['modelo']],
                ['Número de serie', $e['serie']], ['Service Tag', $e['service_tag']],
                ['Estado', $e['estado']], ['Situación actual', $e['situacion']],
                ['Responsable actual', $e['responsable']],
                ['Último responsable registrado', $e['ultimo_responsable']],
                ['Hotel', $e['hotel']], ['Departamento', $e['departamento']],
                ['Ubicación', $e['ubicacion']],
                ['Fecha de asignación vigente', $e['fecha_asignacion']],
                ['Fecha de baja', $e['fecha_baja']],
                ['Motivo de baja', $e['motivo_baja']],
                ['Observaciones de baja', $e['comentarios_baja']],
                ['Host', $e['host']], ['Dirección IP', $e['ip']], ['Dirección MAC', $e['mac']],
                ['Condición', $e['condicion']], ['Proveedor', $e['proveedor']],
                ['Factura', $e['factura']], ['Fecha de compra', $e['fecha_compra']],
                ['Fin de garantía', $e['fecha_garantia']], ['Fecha de registro', $e['fecha_registro']],
                ['Descripción / comentarios', $e['descripcion']],
                ['ESPECIFICACIONES DEL TIPO', ''],
            ];
            foreach ($e['especificaciones'] as $campo => $valor) {
                $filas[] = [(string) $campo, (string) $valor];
            }
            if (($e['programas'] ?? []) !== []) {
                $filas[] = ['PROGRAMAS INSTALADOS', ''];
                foreach ($e['programas'] as $programa) {
                    $filas[] = [$programa['nombre'], $programa['detalle'] ?: '—'];
                }
            }
            $filas[] = ['HISTORIAL COMPLETO', ''];
            foreach ($e['historial'] as $evento) {
                $filas[] = [$evento['fecha'].' · '.$evento['tipo'], $evento['detalle']];
            }
            return $filas;
        }

        $campos = [];
        foreach ($this->equipos as $e) {
            foreach (array_keys($e['especificaciones']) as $nombre) {
                $campos[$nombre] = true;
            }
        }
        $campos = array_keys($campos);

        $filas = [[
            'Código', 'Equipo', 'Tipo', 'Marca', 'Modelo', 'Serie', 'Service Tag',
            'Estado', 'Situación', 'Responsable actual', 'Último responsable',
            'Hotel', 'Departamento', 'Ubicación', 'Condición',
            'Host', 'IP', 'MAC', 'Proveedor', 'Factura', 'Compra', 'Garantía',
            'Fecha de registro', 'Fecha de baja', 'Motivo de baja', 'Observaciones de baja', 'Descripción', 'Programas', 'Historial completo', ...$campos,
        ]];

        foreach ($this->equipos as $e) {
            $fila = [
                $e['codigo'], $e['nombre'], $e['tipo'], $e['marca'], $e['modelo'],
                $e['serie'], $e['service_tag'], $e['estado'], $e['situacion'],
                $e['responsable'], $e['ultimo_responsable'], $e['hotel'],
                $e['departamento'], $e['ubicacion'], $e['condicion'],
                $e['host'], $e['ip'], $e['mac'], $e['proveedor'],
                $e['factura'], $e['fecha_compra'], $e['fecha_garantia'],
                $e['fecha_registro'], $e['fecha_baja'], $e['motivo_baja'], $e['comentarios_baja'], $e['descripcion'],
                ($e['programas'] ?? []) !== [] ? implode(', ', array_column($e['programas'], 'nombre')) : '—',
                implode("\n", array_map(fn ($evento) => $evento['fecha'].' | '.$evento['tipo'].' | '.$evento['detalle'], $e['historial'])),
            ];
            foreach ($campos as $campo) {
                $fila[] = $e['especificaciones'][$campo] ?? '—';
            }
            $filas[] = $fila;
        }

        return $filas;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11, 'color' => ['rgb' => '000000']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $uno = count($this->equipos) === 1;
        $ultima = $sheet->getHighestColumn();
        $filas = $sheet->getHighestRow();
        $sheet->getDefaultRowDimension()->setRowHeight(15);
        $sheet->getStyle('A1:'.$ultima.$filas)->getAlignment()
            ->setVertical('center')->setWrapText(false);
        $sheet->getStyle('A1:'.$ultima.$filas)->getFont()->setSize(11);
        for ($row = 1; $row <= $filas; $row++) {
            $sheet->getRowDimension($row)->setRowHeight($row === 1 ? 20 : 15);
            if ($row > 1 && $row % 2 === 0) {
                $sheet->getStyle('A'.$row.':'.$ultima.$row)->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB('FFFFFF');
            }
        }
        $sheet->getStyle('A1:'.$ultima.'1')->applyFromArray([
            'font' => ['bold' => false, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
        ]);
        if ($uno) {
            foreach ($this->array() as $indice => $fila) {
                if (in_array($fila[0], ['ESPECIFICACIONES DEL TIPO', 'PROGRAMAS INSTALADOS', 'HISTORIAL COMPLETO'], true)) {
                    $numero = $indice + 1;
                    $sheet->getStyle('A'.$numero.':'.$ultima.$numero)->applyFromArray([
                        'font' => ['bold' => false, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
                    ]);
                }
            }
        }
        for ($col = 1; $col <= Coordinate::columnIndexFromString($ultima); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(20);
        }
        $sheet->getColumnDimension('A')->setWidth($uno ? 32 : 18);
        $sheet->getColumnDimension('B')->setWidth($uno ? 55 : 30);
        return [];
    }
}
