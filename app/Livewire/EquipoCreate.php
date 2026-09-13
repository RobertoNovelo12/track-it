<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EquipoCreate extends Component
{
    // --- Información general ---
    public string $nombreEquipo = '';
    public string $host = '';
    public string $idEstadoActivo = '';
    public string $idTipoEquipo = '';
    public string $idModelo = '';
    public string $fechaCompra = '';
    public string $idMarca = '';
    public string $direccionMac = '';
    public string $numeroFactura = '';
    public string $numeroSerie = '';
    public string $idProveedor = '';

    // --- Información adicional (común) ---
    public string $propietario = '';
    public string $idCondicionActivo = '';
    public string $idArea = '';
    public string $idDepartamento = '';
    public string $fechaFinGarantia = '';
    public string $comentarios = '';

    // --- Campos dinámicos de la tabla hija (según tipo de equipo) ---
    public array $childData = [];

    /**
     * Convierte una colección de resultados de DB::table() (objetos stdClass)
     * en un arreglo de arreglos asociativos planos.
     *
     * Esto es indispensable antes de guardar el resultado en caché: stdClass
     * puede fallar al reconstruirse (unserialize) entre peticiones de Livewire,
     * mientras que los arreglos planos siempre se serializan sin problema.
     */
    private function toPlainArray($collection): array
    {
        return $collection->map(fn ($row) => (array) $row)->all();
    }

    /**
     * Mapa: id_tipo_equipo => [tabla hija, [campo => tipo de input]].
     * Tipos soportados: text, number, date, boolean, catalog:<clave_catalogo>.
     */
    protected function childConfig(): array
    {
        $baseCompu = [
            'procesador' => 'text',
            'ram_gb' => 'number',
            'almacenamiento_gb' => 'number',
            'id_tipo_almacenamiento' => 'catalog:tipo_almacenamiento',
            'sistema_operativo' => 'text',
        ];

        return [
            1 => ['table' => 'computadoras_escritorio', 'fields' => $baseCompu],
            2 => ['table' => 'laptops', 'fields' => $baseCompu + ['incluye_cargador' => 'boolean']],
            3 => ['table' => 'monitores', 'fields' => [
                'tamano_pulgadas' => 'number',
                'id_tipo_conexion' => 'catalog:tipo_conexion',
            ]],
            4 => ['table' => 'tablets', 'fields' => [
                'imei' => 'text',
                'almacenamiento_gb' => 'number',
                'sistema_operativo' => 'text',
                'color' => 'text',
                'incluye_cargador' => 'boolean',
            ]],
            5 => ['table' => 'moviles', 'fields' => [
                'imei_1' => 'text',
                'imei_2' => 'text',
                'numero_telefonico' => 'text',
                'almacenamiento_gb' => 'number',
                'sistema_operativo' => 'text',
                'color' => 'text',
                'incluye_cargador' => 'boolean',
            ]],
            6 => ['table' => 'impresoras', 'fields' => [
                'id_tipo_impresora' => 'catalog:tipo_impresora',
                'id_tipo_conexion' => 'catalog:tipo_conexion',
                'nombre_red' => 'text',
            ]],
            7 => ['table' => 'escaneres', 'fields' => [
                'id_tipo_conexion' => 'catalog:tipo_conexion',
            ]],
            8 => ['table' => 'grabadores_llaves', 'fields' => [
                'id_tipo_conexion' => 'catalog:tipo_conexion',
            ]],
            9 => ['table' => 'tpv', 'fields' => $baseCompu],
            10 => ['table' => 'switches', 'fields' => [
                'numero_puertos' => 'number',
                'velocidad' => 'text',
                'administrable' => 'boolean',
            ]],
            11 => ['table' => 'routers', 'fields' => [
                'numero_puertos' => 'number',
                'velocidad' => 'text',
            ]],
            12 => ['table' => 'access_points', 'fields' => [
                'nombre_ap' => 'text',
            ]],
            13 => ['table' => 'servidores', 'fields' => [
                'id_tipo_servidor' => 'catalog:tipo_servidor',
            ] + $baseCompu],
            14 => ['table' => 'ups', 'fields' => [
                'capacidad_va' => 'number',
                'fecha_cambio_bateria' => 'date',
                'id_estado_bateria' => 'catalog:estado_bateria',
            ]],
            15 => ['table' => 'telefonos', 'fields' => [
                'id_tipo_telefono' => 'catalog:tipo_telefono',
                'extension' => 'text',
            ]],
            16 => ['table' => 'teclados', 'fields' => [
                'id_tipo_conexion' => 'catalog:tipo_conexion',
            ]],
            17 => ['table' => 'mouse', 'fields' => [
                'id_tipo_conexion' => 'catalog:tipo_conexion',
            ]],
        ];
    }

    /**
     * Etiquetas legibles para los campos dinámicos.
     */
    protected function fieldLabels(): array
    {
        return [
            'procesador' => 'Procesador',
            'ram_gb' => 'RAM (GB)',
            'almacenamiento_gb' => 'Almacenamiento (GB)',
            'id_tipo_almacenamiento' => 'Tipo de almacenamiento',
            'sistema_operativo' => 'Sistema operativo',
            'incluye_cargador' => 'Incluye cargador',
            'tamano_pulgadas' => 'Tamaño (pulgadas)',
            'id_tipo_conexion' => 'Tipo de conexión',
            'imei' => 'IMEI',
            'color' => 'Color',
            'imei_1' => 'IMEI 1',
            'imei_2' => 'IMEI 2',
            'numero_telefonico' => 'Número telefónico',
            'id_tipo_impresora' => 'Tipo de impresora',
            'nombre_red' => 'Nombre en red',
            'numero_puertos' => 'Número de puertos',
            'velocidad' => 'Velocidad',
            'administrable' => 'Administrable',
            'nombre_ap' => 'Nombre del Access Point',
            'id_tipo_servidor' => 'Tipo de servidor',
            'capacidad_va' => 'Capacidad (VA)',
            'fecha_cambio_bateria' => 'Fecha de cambio de batería',
            'id_estado_bateria' => 'Estado de batería',
            'id_tipo_telefono' => 'Tipo de teléfono',
            'extension' => 'Extensión',
        ];
    }

    public function getChildFieldsProperty(): array
    {
        $config = $this->childConfig();
        $tipo = (int) $this->idTipoEquipo;

        return $config[$tipo]['fields'] ?? [];
    }

    public function getFieldLabelsProperty(): array
    {
        return $this->fieldLabels();
    }

    /**
     * Al cambiar el tipo de equipo, limpia los campos dinámicos anteriores
     * (para no arrastrar datos de un tipo a otro) y también el modelo,
     * ya que el catálogo de modelos depende de tipo + marca.
     */
    public function updatedIdTipoEquipo(): void
    {
        $this->childData = [];
        $this->idModelo = '';
    }

    public function updatedIdMarca(): void
    {
        $this->idModelo = '';
    }

    public function updatedIdArea(): void
    {
        $this->idDepartamento = '';
    }

    // --- Catálogos para los <select> (cacheados: cambian poco) ---
    // Todos regresan arreglos de arreglos asociativos (no stdClass), para
    // que se puedan cachear sin problemas de (de)serialización y para que
    // la vista los lea con $item['campo'] igual que en equipos-index.

    public function getEstadosProperty(): array
    {
        return Cache::remember('form.estados_equipo.v2', now()->addMinutes(30), function () {
            return $this->toPlainArray(
                DB::table('estados_equipo')
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->get(['id_estado_equipo', 'nombre'])
            );
        });
    }

    public function getTiposEquipoProperty(): array
    {
        return Cache::remember('form.tipos_equipo.v2', now()->addMinutes(30), function () {
            return $this->toPlainArray(
                DB::table('tipos_equipo')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_tipo_equipo', 'nombre'])
            );
        });
    }

    public function getMarcasProperty(): array
    {
        return Cache::remember('form.marcas.v2', now()->addMinutes(30), function () {
            return $this->toPlainArray(
                DB::table('marcas')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_marca', 'nombre'])
            );
        });
    }

    public function getModelosProperty(): array
    {
        if ($this->idMarca === '' || $this->idTipoEquipo === '') {
            return [];
        }

        // No se cachea: depende de la combinación marca + tipo elegida.
        return $this->toPlainArray(
            DB::table('modelos')
                ->where('activo', true)
                ->where('id_marca', (int) $this->idMarca)
                ->where('id_tipo_equipo', (int) $this->idTipoEquipo)
                ->orderBy('nombre')
                ->get(['id_modelo', 'nombre'])
        );
    }

    public function getProveedoresProperty(): array
    {
        return Cache::remember('form.proveedores.v2', now()->addMinutes(30), function () {
            return $this->toPlainArray(
                DB::table('proveedores')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_proveedor', 'nombre'])
            );
        });
    }

    public function getCondicionesProperty(): array
    {
        return Cache::remember('form.condicion_activo.v2', now()->addMinutes(30), function () {
            return $this->catalogoValores('condicion_activo');
        });
    }

    public function getAreasProperty(): array
    {
        return Cache::remember('form.areas.v2', now()->addMinutes(30), function () {
            return $this->toPlainArray(
                DB::table('areas')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_area', 'nombre'])
            );
        });
    }

    public function getDepartamentosProperty(): array
    {
        if ($this->idArea === '') {
            return [];
        }

        // No se cachea: depende del área elegida.
        return $this->toPlainArray(
            DB::table('departamentos')
                ->where('activo', true)
                ->where('id_area', (int) $this->idArea)
                ->orderBy('nombre')
                ->get(['id_departamento', 'nombre'])
        );
    }

    /**
     * Opciones para un <select> de tipo "catalog:<clave>" en los campos dinámicos.
     */
    public function catalogOptions(string $clave): array
    {
        return Cache::remember("form.catalogo.$clave.v2", now()->addMinutes(30), function () use ($clave) {
            return $this->catalogoValores($clave);
        });
    }

    private function catalogoValores(string $clave): array
    {
        return $this->toPlainArray(
            DB::table('catalogo_valores')
                ->join('catalogos', 'catalogos.id_catalogo', '=', 'catalogo_valores.id_catalogo')
                ->where('catalogos.clave', $clave)
                ->where('catalogo_valores.activo', true)
                ->orderBy('catalogo_valores.orden')
                ->get(['catalogo_valores.id_valor', 'catalogo_valores.nombre'])
        );
    }

    public function save()
    {
        $childFields = $this->childFields;
        $childTable = $this->childConfig()[(int) $this->idTipoEquipo]['table'] ?? null;

        $rules = [
            'nombreEquipo' => ['required', 'string', 'max:255'],
            'idEstadoActivo' => ['required'],
            'idTipoEquipo' => ['required'],
            'idMarca' => ['required'],
            'numeroSerie' => ['required', 'string', 'max:255', 'unique:equipos,numero_serie'],
            'numeroFactura' => ['required', 'string', 'max:255'],
            'idArea' => ['required'],
        ];

        $this->validate($rules);

        $idEquipo = DB::transaction(function () use ($childFields, $childTable) {
            $idEquipo = DB::table('equipos')->insertGetId([
                'codigo_inventario' => $this->generarCodigoInventario(),
                'nombre_equipo' => $this->nombreEquipo,
                'host' => $this->host ?: null,
                'id_estado_activo' => (int) $this->idEstadoActivo,
                'id_tipo_equipo' => (int) $this->idTipoEquipo,
                'id_modelo' => $this->idModelo !== '' ? (int) $this->idModelo : null,
                'fecha_compra' => $this->fechaCompra ?: null,
                'id_marca' => (int) $this->idMarca,
                'direccion_mac' => $this->direccionMac ?: null,
                'numero_factura' => $this->numeroFactura,
                'numero_serie' => $this->numeroSerie,
                'id_proveedor' => $this->idProveedor !== '' ? (int) $this->idProveedor : null,
                'id_condicion_activo' => $this->idCondicionActivo !== '' ? (int) $this->idCondicionActivo : null,
                'id_area' => (int) $this->idArea,
                'id_departamento' => $this->idDepartamento !== '' ? (int) $this->idDepartamento : null,
                'fecha_fin_garantia' => $this->fechaFinGarantia ?: null,
                'comentarios' => $this->comentarios ?: null,
                'registrado_por' => Auth::id(),
            ], 'id_equipo');

            if ($childTable) {
                $row = ['id_equipo' => $idEquipo];
                foreach ($childFields as $field => $type) {
                    $value = $this->childData[$field] ?? null;

                    if ($type === 'boolean') {
                        $row[$field] = (bool) ($value ?? false);
                    } elseif ($value === null || $value === '') {
                        $row[$field] = null;
                    } elseif ($type === 'number') {
                        $row[$field] = is_numeric($value) ? $value + 0 : null;
                    } else {
                        $row[$field] = $value;
                    }
                }
                DB::table($childTable)->insert($row);
            }

            if (trim($this->propietario) !== '') {
                $idTipoAsignacion = DB::table('catalogo_valores')
                    ->join('catalogos', 'catalogos.id_catalogo', '=', 'catalogo_valores.id_catalogo')
                    ->where('catalogos.clave', 'tipo_asignacion')
                    ->where('catalogo_valores.clave', 'ASIGNACION_INICIAL')
                    ->value('catalogo_valores.id_valor');

                if ($idTipoAsignacion) {
                    DB::table('asignaciones')->insert([
                        'id_equipo' => $idEquipo,
                        'nombre_colaborador' => $this->propietario,
                        'id_area' => (int) $this->idArea,
                        'id_departamento' => $this->idDepartamento !== '' ? (int) $this->idDepartamento : null,
                        'id_tipo_asignacion' => $idTipoAsignacion,
                        'fecha_asignacion' => now(),
                        'asignado_por' => Auth::id(),
                    ]);
                }
            }

            return $idEquipo;
        });

        session()->flash('status', 'Equipo registrado correctamente.');

        return redirect()->route('equipos.index');
    }

    private function generarCodigoInventario(): string
    {
        $ultimo = DB::table('equipos')->max('id_equipo');

        return 'ACT-' . str_pad((string) (($ultimo ?? 0) + 1), 6, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.equipo-create');
    }
}