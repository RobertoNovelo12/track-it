<?php

namespace App\Services;

use App\Exports\ReporteInventarioExport;
use App\Models\ReporteGenerado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/*
|--------------------------------------------------------------------------
| Archivo de un reporte del historial
|--------------------------------------------------------------------------
|
| Los archivos se guardan en el disco local (storage/app/private). En
| Render ese disco se borra en cada despliegue o reinicio, y cada equipo
| de desarrollo tiene el suyo, pero el historial vive en la base de datos
| compartida: el registro existía y el archivo no, y «Ver» respondía 404.
|
| Si falta el archivo, se vuelve a generar con los mismos filtros
| guardados en el historial (misma lógica que ReportesIndex::generarPdf
| y ::generarHojaCalculo). Refleja los datos actuales del sistema.
|
*/
class ReporteArchivoService
{
    /**
     * Devuelve true si el archivo existe (o se pudo regenerar).
     */
    public function asegurar(ReporteGenerado $reporte): bool
    {
        $disco = Storage::disk('local');

        if ($reporte->ruta_archivo && $disco->exists($reporte->ruta_archivo)) {
            return true;
        }

        try {
            return $this->regenerar($reporte);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function regenerar(ReporteGenerado $reporte): bool
    {
        $filtros = (array) ($reporte->filtros ?? []);

        $tipos = array_values(array_filter((array) ($filtros['tipos_reporte'] ?? [])));

        if ($tipos === [] && $reporte->tipo_reporte && $reporte->tipo_reporte !== 'multiple') {
            $tipos = [$reporte->tipo_reporte];
        }

        if ($tipos === []) {
            return false;
        }

        $filtros['tipos_reporte'] = $tipos;

        $ruta = $reporte->ruta_archivo
            ?: 'reportes/' . basename((string) ($reporte->nombre_archivo ?: 'reporte-' . $reporte->id_reporte));

        $formato = strtoupper((string) $reporte->formato);

        if ($formato === 'PDF') {
            $contenido = $this->pdf($reporte, $tipos, $filtros);

            if ($contenido === null || ! Storage::disk('local')->put($ruta, $contenido)) {
                return false;
            }
        } elseif (in_array($formato, ['XLSX', 'CSV'], true)) {
            $datos = app(ReporteDataService::class)->datosCombinados($tipos, $filtros);

            if ($datos->isEmpty()) {
                return false;
            }

            $guardado = Excel::store(
                new ReporteInventarioExport($datos),
                $ruta,
                'local',
                $formato === 'XLSX' ? ExcelWriter::XLSX : ExcelWriter::CSV
            );

            if (! $guardado) {
                return false;
            }
        } else {
            return false;
        }

        if ($reporte->ruta_archivo !== $ruta) {
            $reporte->forceFill(['ruta_archivo' => $ruta])->save();
        }

        return true;
    }

    /**
     * Mismo criterio de vistas que ReportesIndex::generarPdf.
     */
    private function pdf(ReporteGenerado $reporte, array $tipos, array $filtros): ?string
    {
        $reportes = app(ReporteDataService::class);

        $esUnSoloTipo = count($tipos) === 1;
        $tipo = $tipos[0];

        $comunes = [
            'filtros' => $filtros,
            'generadoPor' => $this->nombreUsuario($reporte),
            'generadoEn' => $reporte->fecha_generacion ?? now(),
        ];

        if ($esUnSoloTipo && $tipo === 'bajas') {
            $datos = app(ReporteBajasActaService::class)->obtener($filtros);
            $vista = ['reportes.baja-acta-pdf', ['bajas' => $datos], 'letter', 'portrait'];
        } elseif ($esUnSoloTipo && in_array($tipo, ['asignaciones', 'reasignaciones'], true)) {
            $datos = app(ReporteActasService::class)->obtener($tipo, $filtros);
            $vista = ['reportes.actas-pdf', ['actas' => $datos, 'tipoActa' => $tipo], 'letter', 'portrait'];
        } elseif ($esUnSoloTipo && $tipo === 'mantenimientos') {
            $datos = app(ReporteMantenimientoActaService::class)->obtener($filtros);
            $vista = ['reportes.mantenimiento-pdf', ['mantenimientos' => $datos], 'letter', 'portrait'];
        } elseif ($esUnSoloTipo && $tipo === 'altas') {
            $datos = app(ReporteAltasActaService::class)->obtener($filtros);
            $vista = ['reportes.alta-pdf', ['altas' => $datos], 'letter', 'portrait'];
        } else {
            $tipoGuardado = $esUnSoloTipo ? $tipo : 'multiple';
            $datos = $reportes->datosCombinados($tipos, $filtros);
            $vista = ['reportes.pdf', [
                'titulo' => $esUnSoloTipo ? 'Reporte de ' . $reportes->etiqueta($tipoGuardado) : 'Reporte combinado',
                'tipoReporte' => $tipoGuardado,
                'datos' => $datos,
            ], 'a4', 'landscape'];
        }

        if ($datos->isEmpty()) {
            return null;
        }

        [$nombreVista, $variables, $papel, $orientacion] = $vista;

        $pdf = Pdf::loadView($nombreVista, $variables + $comunes)
            ->setPaper($papel, $orientacion);

        $pdf->setOption([
            'enable_font_subsetting' => true,
            'enable_remote' => false,
            'enable_php' => false,
        ]);

        return $pdf->output();
    }

    private function nombreUsuario(ReporteGenerado $reporte): string
    {
        $usuario = $reporte->usuario;

        $nombre = trim(implode(' ', array_filter([
            $usuario?->nombres ?? $usuario?->name,
            $usuario?->apellido_paterno,
            $usuario?->apellido_materno,
        ])));

        return $nombre !== '' ? $nombre : 'Usuario del sistema';
    }
}
