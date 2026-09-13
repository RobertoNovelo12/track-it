<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CatalogoValor;
use App\Models\Equipo;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\TipoEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipo::query()->with([
            'tipoEquipo',
            'marca',
            'modelo',
            'estadoActivo',
            'area.sede',
            'departamento.area',
        ]);

        $query->when($request->filled('tipo_activo'), function ($q) use ($request) {
            $q->where('id_tipo_equipo', $request->integer('tipo_activo'));
        });

        $query->when($request->filled('marca'), function ($q) use ($request) {
            $q->where('id_marca', $request->integer('marca'));
        });

        $query->when($request->filled('modelo'), function ($q) use ($request) {
            $q->where('id_modelo', $request->integer('modelo'));
        });

        $query->when($request->filled('numero_serie'), function ($q) use ($request) {
            $q->where('numero_serie', 'ilike', '%' . $request->input('numero_serie') . '%');
        });

        $query->when($request->filled('service_tag'), function ($q) use ($request) {
            $q->where('service_tag', $request->input('service_tag'));
        });

        $query->when($request->filled('direccion_ip'), function ($q) use ($request) {
            $q->where('direccion_ip', $request->input('direccion_ip'));
        });

        $query->when($request->filled('estado'), function ($q) use ($request) {
            $q->where('id_estado_activo', $request->integer('estado'));
        });

        // El filtro "Área / Departamento" busca tanto por área directa
        // como por departamento (según cómo esté asignado el equipo).
        $query->when($request->filled('area'), function ($q) use ($request) {
            $areaId = $request->integer('area');
            $q->where(function ($sub) use ($areaId) {
                $sub->where('id_area', $areaId)
                    ->orWhereHas('departamento', function ($dep) use ($areaId) {
                        $dep->where('id_area', $areaId);
                    });
            });
        });

        $sortDirection = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy('codigo_inventario', $sortDirection);

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $equipos = $query->paginate($perPage)->withQueryString();

        return view('equipos.index', array_merge(
            ['equipos' => $equipos],
            $this->opcionesDeFiltros()
        ));
    }

    /**
     * Opciones de los combos de filtros. Se cachean 10 minutos porque
     * catálogos, marcas, modelos y áreas casi nunca cambian, y esto
     * evita repetir ~6 consultas en cada carga de la pantalla.
     *
     * Si editas un catálogo y no ves el cambio reflejado de inmediato,
     * corre: php artisan cache:clear (o espera a que expire el cache).
     */
    private function opcionesDeFiltros(): array
    {
        return Cache::remember('equipos.filtros.opciones', now()->addMinutes(10), function () {
            return [
                'tiposActivo' => TipoEquipo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_tipo_equipo', 'nombre']),

                'marcas' => Marca::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_marca', 'nombre']),

                'modelos' => Modelo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_modelo', 'nombre']),

                'serviceTags' => Equipo::whereNotNull('service_tag')
                    ->distinct()
                    ->orderBy('service_tag')
                    ->pluck('service_tag'),

                'direccionesIp' => Equipo::whereNotNull('direccion_ip')
                    ->distinct()
                    ->orderBy('direccion_ip')
                    ->pluck('direccion_ip'),

                // Ajusta 'estado_activo' a la clave real que usas en tu
                // tabla `catalogos` para agrupar los estados de un equipo.
                'estados' => CatalogoValor::deCatalogo('estado_activo')
                    ->get(['id_valor', 'nombre']),

                'areas' => Area::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_area', 'nombre']),
            ];
        });
    }
}