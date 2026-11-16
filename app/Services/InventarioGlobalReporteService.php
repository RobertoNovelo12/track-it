<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InventarioGlobalReporteService
{
    public const FORMATOS = ['global' => 'Inventario global de bienes muebles y equipos', 'intangibles' => 'Inventario de bienes intangibles', 'inmuebles' => 'Relación de bienes inmuebles', 'vehiculos' => 'Relación de vehículos al servicio de la institución'];
    public const COMPLETAS = [
        'inventario' => 'N.º de inventario', 'descripcion' => 'Descripción del bien', 'marca' => 'Marca', 'modelo' => 'Modelo',
        'serie' => 'N.º de serie', 'factura' => 'Factura', 'adquisicion' => 'Fecha de adquisición', 'proveedor' => 'Proveedor',
        'costo_unitario' => 'Costo unitario', 'iva' => 'IVA', 'total' => 'Total', 'partida' => 'Partida',
        'procedimiento_adquisicion' => 'Procedimiento de adquisición', 'resguardatario' => 'Resguardatario',
        'unidad' => 'Unidad administrativa', 'fuente_financiamiento' => 'Fuente de financiamiento',
    ];

    public function columnas(string $formato): array
    {
        return match ($formato) {
            'resguardo' => ['inventario'=>'N.º de inventario', 'descripcion'=>'Descripción del bien', 'marca'=>'Marca', 'modelo'=>'Modelo', 'serie'=>'N.º de serie', 'fecha_resguardo'=>'Fecha de resguardo', 'motivo_resguardo'=>'Motivo de revisión', 'edificio'=>'Edificio', 'ubicacion'=>'Ubicación', 'unidad'=>'Departamento responsable', 'resguardatario'=>'Resguardatario', 'observaciones_resguardo'=>'Observaciones'],
            'global', 'intangibles' => self::COMPLETAS,
            'inmuebles' => ['descripcion_uso' => 'Descripción del inmueble y uso que se le da', 'institucion_responsable' => 'Institución a cargo del inmueble', 'domicilio_inmueble' => 'Domicilio del inmueble', 'tipo_posesion' => 'Tipo de posesión', 'titulo_propiedad' => 'Título que acredita la propiedad o posesión del bien'],
            'vehiculos' => ['serie' => 'N.º de serie', 'placas' => 'Placas de circulación', 'descripcion' => 'Descripción del vehículo', 'modelo' => 'Modelo', 'marca' => 'Marca', 'anio_vehiculo' => 'Año', 'unidad' => 'Área de asignación', 'resguardatario' => 'Nombre del resguardante', 'uso_destino' => 'Uso y destino'],
            default => throw new \InvalidArgumentException('Formato de inventario inválido.'),
        };
    }

    public function query(string $formato, array $filtros = [])
    {
        $this->columnas($formato);
        $atributos = ['inventario_institucional', 'costo_unitario', 'iva', 'total', 'partida', 'procedimiento_adquisicion', 'fuente_financiamiento', 'institucion_responsable', 'domicilio_inmueble', 'tipo_posesion', 'titulo_propiedad', 'placas', 'anio_vehiculo', 'uso_destino', 'resguardatario'];
        $datos = DB::table('atributos_equipo as a')->leftJoin('campos_equipo as c', 'c.id_campo', '=', 'a.id_campo')->select('a.id_equipo')->groupBy('a.id_equipo');
        foreach ($atributos as $clave) {
            $datos->selectRaw("MAX(CASE WHEN c.clave IN (?, ?) THEN NULLIF(a.valor, '') END) as {$clave}", [$clave, 'institucion_'.$clave]);
        }
        $query = DB::table('equipos as e')->leftJoin('tipos_equipo as t', 't.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('proveedores as p', 'p.id_proveedor', '=', 'e.id_proveedor')->leftJoin('departamentos as d', 'd.id_departamento', '=', 'e.id_departamento')
            ->leftJoinSub($datos, 'datos', 'datos.id_equipo', '=', 'e.id_equipo')
            ->select(['e.id_equipo', DB::raw("COALESCE(datos.inventario_institucional, e.codigo_inventario) as inventario"), 'e.nombre_equipo as descripcion', 'ma.nombre as marca', 'mo.nombre as modelo', 'e.numero_serie as serie', 'e.numero_factura as factura', 'e.fecha_compra as adquisicion', 'p.nombre as proveedor', 'd.nombre as unidad'])
            ->selectRaw("COALESCE((SELECT a.nombre_colaborador FROM asignaciones a WHERE a.id_equipo = e.id_equipo AND a.fecha_fin IS NULL ORDER BY a.fecha_asignacion DESC, a.id_asignacion DESC LIMIT 1), datos.resguardatario) as resguardatario");
        foreach ($atributos as $clave) { if ($clave !== 'resguardatario') { $query->addSelect('datos.'.$clave); } }
        $query->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('bajas as baja_completada')->whereColumn('baja_completada.id_equipo', 'e.id_equipo');
        });
        if ($formato === 'resguardo') {
            $query->join('bajas_resguardo as br', 'br.id_equipo', '=', 'e.id_equipo')->whereNull('br.fecha_completada')
                ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'e.id_unidad')
                ->leftJoin('ubicaciones as ubic', 'ubic.id_ubicacion', '=', 'e.id_ubicacion')
                ->addSelect(['br.fecha_resguardo', 'br.motivo as motivo_resguardo', 'br.observaciones as observaciones_resguardo', 'u.nombre as edificio', 'ubic.nombre as ubicacion']);
        } else { $query->where('t.clasificacion_bien', $formato); }
        foreach (['tipo_equipo' => 'e.id_tipo_equipo', 'estado' => 'e.id_estado_activo', 'marca' => 'e.id_marca', 'modelo' => 'e.id_modelo'] as $filtro => $columna) {
            if (!empty($filtros[$filtro])) { $query->where($columna, $filtros[$filtro]); }
        }
        $fecha = $formato === 'resguardo' ? 'br.fecha_resguardo' : 'e.fecha_registro';
        if (!empty($filtros['fecha_inicio'])) { $query->whereDate($fecha, '>=', $filtros['fecha_inicio']); }
        if (!empty($filtros['fecha_fin'])) { $query->whereDate($fecha, '<=', $filtros['fecha_fin']); }
        if (filled($filtros['busqueda'] ?? null)) {
            $texto = '%'.mb_strtolower(trim($filtros['busqueda'])).'%';
            $query->where(function ($q) use ($texto) {
                foreach (['e.nombre_equipo', 'e.codigo_inventario', 'e.numero_serie', 'datos.inventario_institucional'] as $columna) {
                    $q->orWhereRaw("LOWER(COALESCE({$columna}, '')) LIKE ?", [$texto]);
                }
            });
        }
        return $query->orderBy('e.codigo_inventario')->orderBy('e.id_equipo');
    }

    public function celda(object|array $fila, string $clave): string
    {
        $valor = data_get($fila, $clave);
        if ($clave === 'descripcion_uso') {
            $valor = collect([data_get($fila, 'descripcion'), data_get($fila, 'uso_destino')])->filter()->implode(' · ');
        }
        if ($valor === null || $valor === '') { return 'Sin dato'; }
        if (in_array($clave, ['costo_unitario', 'iva', 'total']) && is_numeric($valor)) { return number_format((float) $valor, 2, '.', ','); }
        if (in_array($clave, ['adquisicion', 'fecha_resguardo'])) { return \Carbon\Carbon::parse($valor)->format('d/m/Y'); }
        return (string) $valor;
    }

    public function titulo(string $formato): string
    {
        return $formato === 'resguardo' ? 'Bajas en resguardo · pendientes de revisión' : self::FORMATOS[$formato];
    }
}
