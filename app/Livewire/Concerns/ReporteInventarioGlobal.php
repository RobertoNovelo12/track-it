<?php

namespace App\Livewire\Concerns;

use App\Services\InventarioGlobalReporteService;
use App\Exports\InventarioGlobalReporteExport;
use App\Models\ReporteGenerado;
use Illuminate\Support\Facades\Storage;

trait ReporteInventarioGlobal
{
    public string $formatoInventario = 'global';
    public string $formatoBaja = 'completadas';

    public function esBajaEnResguardo(): bool
    {
        return ($this->tiposReporte[0] ?? '') === 'bajas' && $this->formatoBaja === 'resguardo';
    }

    private function validarFormatoInventario(): void
    {
        $this->validate(['formatoInventario' => ['required', \Illuminate\Validation\Rule::in(array_keys(InventarioGlobalReporteService::FORMATOS))]]);
        $this->validate(['formatoBaja' => ['required', 'in:completadas,resguardo']]);
    }

    private function consultarInventarioGlobal(): void
    {
        $this->validarFormatoInventario();
        $formato = $this->esBajaEnResguardo() ? 'resguardo' : $this->formatoInventario;
        $filtros = $this->filtros();
        if ($formato === 'resguardo') { unset($filtros['estado']); }
        $query = app(InventarioGlobalReporteService::class)->query($formato, $filtros);
        $this->previewTotal = (clone $query)->count();
        $this->previewRecords = $query->limit(20)->get()->map(fn ($fila) => (array) $fila)->all();
        $this->previewDocument = ['filas' => $this->previewRecords];
        $this->previewReady = true;
        $this->dispatch('reportes-previa-lista');
    }

    private function generarInventarioGlobal(string $extension)
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');
        $this->validate($this->rules(), $this->messages());
        $this->validarFormatoInventario();
        abort_unless(in_array($extension, ['pdf', 'xlsx', 'csv'], true), 422);
        $this->successMessage = $this->errorMessage = '';
        $ruta = null;
        $registro = null;
        try {
            $formato = $this->esBajaEnResguardo() ? 'resguardo' : $this->formatoInventario;
            $filtros = $this->filtros();
            if ($formato === 'resguardo') { unset($filtros['estado']); }
            $filas = app(InventarioGlobalReporteService::class)->query($formato, $filtros)->get();
            if ($filas->isEmpty()) { $this->errorMessage = 'No hay bienes para generar este inventario con los filtros seleccionados.'; return null; }
            $nombre = 'inventario-'.$formato.'-'.now()->format('Ymd-His').'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)).'.'.$extension;
            $ruta = 'reportes/'.$nombre;
            $contenido = $extension === 'pdf'
                ? \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.inventario-global-pdf', compact('filas', 'formato'))->setPaper(in_array($formato, ['global', 'intangibles']) ? 'a3' : 'a4', 'landscape')->output()
                : \Maatwebsite\Excel\Facades\Excel::raw(new InventarioGlobalReporteExport($filas, $formato), $extension === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV);
            if (!Storage::disk('local')->put($ruta, $contenido)) { throw new \RuntimeException('No se pudo guardar el reporte.'); }
            $registro = ReporteGenerado::create(['id_usuario' => auth()->id(), 'tipo_reporte' => $formato === 'resguardo' ? 'bajas_resguardo' : 'inventario_global', 'formato' => strtoupper($extension),
                'fecha_desde' => $this->fechaInicio ?: null, 'fecha_hasta' => $this->fechaFin ?: null,
                'filtros' => $this->filtros() + ['formato_inventario' => $formato], 'total_registros' => $filas->count(), 'nombre_archivo' => $nombre, 'ruta_archivo' => $ruta]);
            $this->successMessage = 'El inventario se generó y quedó guardado en el historial.';
            $this->descargarArchivo($registro);
        } catch (\Throwable $e) {
            if ($ruta) { Storage::disk('local')->delete($ruta); }
            $registro?->delete();
            report($e);
            $this->errorMessage = 'No fue posible generar el inventario. Revisa el registro de errores.';
        }
        return null;
    }
}
