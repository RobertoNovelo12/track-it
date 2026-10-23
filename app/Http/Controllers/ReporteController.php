<?php

namespace App\Http\Controllers;

use App\Models\ReporteGenerado;
use Illuminate\Support\Facades\Storage;

class ReporteController extends Controller
{
    public function show(ReporteGenerado $reporte)
    {
        $this->validarArchivo($reporte);

        /*
         * Solamente los PDF pueden mostrarse dentro del navegador.
         * Excel y CSV se descargan directamente.
         */
        if (strtoupper($reporte->formato) !== 'PDF') {
            return $this->download($reporte);
        }

        return response()->file(
            Storage::disk('local')->path($reporte->ruta_archivo),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'inline; filename="%s"',
                    basename($reporte->nombre_archivo)
                ),
            ]
        );
    }

    public function download(ReporteGenerado $reporte)
    {
        $this->validarArchivo($reporte);

        return Storage::disk('local')->download(
            $reporte->ruta_archivo,
            basename($reporte->nombre_archivo),
            [
                'Content-Type' => $this->contentType($reporte),
            ]
        );
    }

    private function validarArchivo(
        ReporteGenerado $reporte
    ): void {
        /*
         * El historial está en la base de datos compartida, pero el
         * archivo vive en el disco local (en Render se borra en cada
         * despliegue). Si falta, se regenera con los filtros guardados.
         */
        abort_unless(
            app(\App\Services\ReporteArchivoService::class)->asegurar($reporte),
            404,
            'El archivo del reporte no está disponible y no se pudo volver a generar.'
        );
    }

    private function contentType(
        ReporteGenerado $reporte
    ): string {
        return match (strtoupper($reporte->formato)) {
            'PDF' => 'application/pdf',
            'XLSX' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'CSV' => 'text/csv; charset=UTF-8',
            default => 'application/octet-stream',
        };
    }
}