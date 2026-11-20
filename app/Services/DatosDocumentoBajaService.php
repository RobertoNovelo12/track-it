<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatosDocumentoBajaService
{
    public function equipos(array $ids): array
    {
        if (!$ids) { return []; }
        $inventarios = DB::table('atributos_equipo as a')->join('campos_equipo as c', 'c.id_campo', '=', 'a.id_campo')
            ->whereIn('c.clave', ['inventario_institucional', 'institucion_inventario_institucional'])
            ->whereIn('a.id_equipo', $ids)->select('a.id_equipo')->selectRaw("MAX(NULLIF(a.valor, '')) as inventario")->groupBy('a.id_equipo');
        return DB::table('equipos as e')->leftJoin('departamentos as d', 'd.id_departamento', '=', 'e.id_departamento')
            ->leftJoin('ubicaciones as u', 'u.id_ubicacion', '=', 'e.id_ubicacion')
            ->leftJoinSub($inventarios, 'i', 'i.id_equipo', '=', 'e.id_equipo')->whereIn('e.id_equipo', $ids)
            ->select('e.id_equipo', 'd.nombre as departamento', 'u.nombre as ubicacion')
            ->selectRaw('COALESCE(i.inventario, e.codigo_inventario) as inventario')->get()->keyBy('id_equipo')->map(fn ($e) => (array) $e)->all();
    }

    public function filas(array $documento): array
    {
        $equipos = $documento['equipos'] ?? [$documento['equipo'] ?? []];
        return array_map(fn ($e) => [
            'fecha' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $documento['fecha'] ?? '') ? \Carbon\Carbon::parse($documento['fecha'])->format('d/m/Y') : ($documento['fecha'] ?? ''), 'solicitante' => $documento['solicitante'] ?? '',
            'area' => $e['departamento'] ?? $documento['departamento'] ?? '',
            'descripcion' => $e['nombre_equipo'] ?? $e['nombre'] ?? '',
            'inventario' => $e['inventario'] ?? $e['codigo_inventario'] ?? $e['codigo'] ?? '',
            'serie' => $e['numero_serie'] ?? $e['serie'] ?? '', 'marca' => $e['marca'] ?? '', 'modelo' => $e['modelo'] ?? '',
            'ubicacion' => $e['ubicacion'] ?? '', 'observacion' => $documento['motivo'] ?? '',
        ], $equipos);
    }
}
