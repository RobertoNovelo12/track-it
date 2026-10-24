<?php

namespace App\Services;

use App\Models\ReporteGenerado;
use Illuminate\Support\Facades\Storage;

class DescargaActaBajaService
{
    public function descargar(int $equipo, ?object $baja)
    {
        $disco = Storage::disk('local');
        if (($baja->ruta_pdf ?? null) && $disco->exists($baja->ruta_pdf)) {
            return $disco->download($baja->ruta_pdf, basename($baja->ruta_pdf), ['Content-Type' => 'application/pdf']);
        }

        // Buscar solamente actas que incluyan expresamente este equipo.
        foreach (ReporteGenerado::where('tipo_reporte', 'bajas')->where('formato', 'PDF')->orderByDesc('id_reporte')->cursor() as $reporte) {
            $filtros = (array) $reporte->filtros;
            $individual = $filtros['baja_individual'] ?? [];
            $multiple = $filtros['baja_multiple'] ?? null;
            $coincide = isset($individual['id_baja']) && (int) $individual['id_baja'] === (int) ($baja->id_baja ?? 0);
            if ($multiple) {
                $coincide = collect($multiple['equipos'] ?? [])->contains(fn ($fila) => (int) ($fila['id_equipo'] ?? 0) === $equipo);
            }
            if (!$coincide) {
                continue;
            }
            // Las actas conjuntas conservan sus datos originales para reconstruirlas.
            if ($multiple && !$disco->exists($reporte->ruta_archivo)) {
                app(ReporteArchivoService::class)->asegurar($reporte);
            }
            if ($reporte->ruta_archivo && $disco->exists($reporte->ruta_archivo)) {
                return $disco->download($reporte->ruta_archivo, basename($reporte->nombre_archivo), ['Content-Type' => 'application/pdf']);
            }
        }
        abort(404, 'El acta original de baja no está disponible. Consulta el historial de Reportes.');
    }
}
