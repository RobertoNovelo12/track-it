<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;

use Livewire\Component;

class EquipoShow extends Component

{

    public int $equipoId;

    public function mount(int $equipoId): void

    {

        $this->equipoId = $equipoId;

    }

    protected function childConfig(): array

    {

        $baseCompu = [

            'procesador' => 'text',

            'ram_gb' => 'number',

            'almacenamiento_gb' => 'number',

            'id_tipo_almacenamiento' => 'catalog',

            'sistema_operativo' => 'text',

        ];

        return [

            1 => [

                'table' => 'computadoras_escritorio',

                'fields' => $baseCompu,

            ],

            2 => [

                'table' => 'laptops',

                'fields' => $baseCompu + [

                    'incluye_cargador' => 'boolean',

                ],

            ],

            3 => [

                'table' => 'monitores',

                'fields' => [

                    'tamano_pulgadas' => 'number',

                    'id_tipo_conexion' => 'catalog',

                ],

            ],

            4 => [

                'table' => 'tablets',

                'fields' => [

                    'imei' => 'text',

                    'almacenamiento_gb' => 'number',

                    'sistema_operativo' => 'text',

                    'color' => 'text',

                    'incluye_cargador' => 'boolean',

                ],

            ],

            5 => [

                'table' => 'moviles',

                'fields' => [

                    'imei_1' => 'text',

                    'imei_2' => 'text',

                    'numero_telefonico' => 'text',

                    'almacenamiento_gb' => 'number',

                    'sistema_operativo' => 'text',

                    'color' => 'text',

                    'incluye_cargador' => 'boolean',

                ],

            ],

            6 => [

                'table' => 'impresoras',

                'fields' => [

                    'id_tipo_impresora' => 'catalog',

                    'id_tipo_conexion' => 'catalog',

                    'nombre_red' => 'text',

                ],

            ],

            7 => [

                'table' => 'escaneres',

                'fields' => [

                    'id_tipo_conexion' => 'catalog',

                ],

            ],

            8 => [

                'table' => 'grabadores_llaves',

                'fields' => [

                    'id_tipo_conexion' => 'catalog',

                ],

            ],

            9 => [

                'table' => 'tpv',

                'fields' => $baseCompu,

            ],

            10 => [

                'table' => 'switches',

                'fields' => [

                    'numero_puertos' => 'number',

                    'velocidad' => 'text',

                    'administrable' => 'boolean',

                ],

            ],

            11 => [

                'table' => 'routers',

                'fields' => [

                    'numero_puertos' => 'number',

                    'velocidad' => 'text',

                ],

            ],

            12 => [

                'table' => 'access_points',

                'fields' => [

                    'nombre_ap' => 'text',

                ],

            ],

            13 => [

                'table' => 'servidores',

                'fields' => [

                    'id_tipo_servidor' => 'catalog',

                ] + $baseCompu,

            ],

            14 => [

                'table' => 'ups',

                'fields' => [

                    'capacidad_va' => 'number',

                    'fecha_cambio_bateria' => 'date',

                    'id_estado_bateria' => 'catalog',

                ],

            ],

            15 => [

                'table' => 'telefonos',

                'fields' => [

                    'id_tipo_telefono' => 'catalog',

                    'extension' => 'text',

                ],

            ],

            16 => [

                'table' => 'teclados',

                'fields' => [

                    'id_tipo_conexion' => 'catalog',

                ],

            ],

            17 => [

                'table' => 'mouse',

                'fields' => [

                    'id_tipo_conexion' => 'catalog',

                ],

            ],

        ];

    }

    protected function fieldLabels(): array

    {

        return [

            'procesador' =>

                'Procesador',

            'ram_gb' =>

                'Memoria RAM',

            'almacenamiento_gb' =>

                'Almacenamiento',

            'id_tipo_almacenamiento' =>

                'Tipo de almacenamiento',

            'sistema_operativo' =>

                'Sistema operativo',

            'incluye_cargador' =>

                'Incluye cargador',

            'tamano_pulgadas' =>

                'Tamaño',

            'id_tipo_conexion' =>

                'Tipo de conexión',

            'imei' =>

                'IMEI',

            'imei_1' =>

                'IMEI 1',

            'imei_2' =>

                'IMEI 2',

            'numero_telefonico' =>

                'Número telefónico',

            'color' =>

                'Color',

            'id_tipo_impresora' =>

                'Tipo de impresora',

            'nombre_red' =>

                'Nombre en red',

            'numero_puertos' =>

                'Número de puertos',

            'velocidad' =>

                'Velocidad',

            'administrable' =>

                'Administrable',

            'nombre_ap' =>

                'Nombre del Access Point',

            'id_tipo_servidor' =>

                'Tipo de servidor',

            'capacidad_va' =>

                'Capacidad',

            'fecha_cambio_bateria' =>

                'Cambio de batería',

            'id_estado_bateria' =>

                'Estado de batería',

            'id_tipo_telefono' =>

                'Tipo de teléfono',

            'extension' =>

                'Extensión',

        ];

    }

    protected function cargarEquipo(): array

    {

        $equipo = DB::table('equipos as e')

            ->leftJoin(

                'tipos_equipo as te',

                'te.id_tipo_equipo',

                '=',

                'e.id_tipo_equipo'

            )

            ->leftJoin(

                'marcas as ma',

                'ma.id_marca',

                '=',

                'e.id_marca'

            )

            ->leftJoin(

                'modelos as mo',

                'mo.id_modelo',

                '=',

                'e.id_modelo'

            )

            ->leftJoin(

                'proveedores as pr',

                'pr.id_proveedor',

                '=',

                'e.id_proveedor'

            )

            ->leftJoin(

                'estados_equipo as ee',

                'ee.id_estado_equipo',

                '=',

                'e.id_estado_activo'

            )

            ->leftJoin(

                'catalogo_valores as condicion',

                'condicion.id_valor',

                '=',

                'e.id_condicion_activo'

            )

            ->leftJoin('unidades_organizativas as h', 'h.id_unidad', '=', 'e.id_unidad')

            ->leftJoin(

                'departamentos as dep',

                'dep.id_departamento',

                '=',

                'e.id_departamento'

            )


            ->where(

                'e.id_equipo',

                $this->equipoId

            )

            ->select([

                'e.id_equipo',

                'e.codigo_inventario',

                'e.nombre_equipo',

                'e.host',

                'e.numero_serie',

                'e.direccion_mac',

                'e.numero_factura',

                'e.fecha_compra',

                'e.fecha_fin_garantia',

                'e.comentarios',

                'e.id_estado_activo',

                'e.id_tipo_equipo',

                'e.id_condicion_activo',


                'e.id_departamento',

                'e.id_unidad',

                'te.nombre as tipo_equipo',

                'ma.nombre as marca',

                'mo.nombre as modelo',

                'pr.nombre as proveedor',

                'ee.nombre as estado',

                'condicion.nombre as condicion',

                'h.nombre as hotel',

                'dep.nombre as departamento',

            ])

            // Dirección IP (campo opcional de algunos tipos).
            ->when(
                \App\Support\Esquema::tieneColumna('equipos', 'direccion_ip'),
                fn ($consulta) => $consulta->addSelect('e.direccion_ip')
            )

            // Ubicación del equipo (campo «Ubicación» del tipo).
            ->when(
                \App\Services\UbicacionOrganizacionalService::equiposTienenUbicacion(),
                fn ($consulta) => $consulta
                    ->leftJoin('ubicaciones as ub_equipo', 'ub_equipo.id_ubicacion', '=', 'e.id_ubicacion')
                    ->addSelect('ub_equipo.nombre as ubicacion_equipo')
            )

            ->first();

        abort_unless($equipo, 404);

        $data = (array) $equipo;

        /*
         * Ubicación: Hotel / Departamento (las áreas ya no se usan).
         */
        $data['ubicacion'] = implode(' / ', array_filter([
            $data['hotel'] ?? null,
            $data['departamento'] ?? null,
            $data['ubicacion_equipo'] ?? null,
        ])) ?: '—';


        $data['estado'] =

            $data['estado']

            ?? 'Sin estado';

        return $data;

    }

    /*
     * Campos del formulario configurado para el tipo (Administración ›
     * Tipos de equipo): solo los visibles. «base» son columnas de equipos
     * (factura, compra, proveedor, IP…); «extra» son las especificaciones
     * propias del tipo (atributos o tabla del tipo).
     */
    protected function camposDelTipo(int $tipoId): array
    {
        $filas = DB::table('tipo_equipo_campos as tec')
            ->join('campos_equipo as ce', 'ce.id_campo', '=', 'tec.id_campo')
            ->where('tec.id_tipo_equipo', $tipoId)
            ->where('ce.activo', true)
            ->where('tec.visible', true)
            ->orderBy('tec.orden')
            ->orderBy('tec.id_tipo_campo')
            ->get([
                'tec.id_tipo_campo',
                'tec.id_campo',
                'tec.origen_dato',
                'tec.tabla_destino',
                'tec.columna_destino',
                'tec.fuente_opciones',
                'tec.id_catalogo',
                'tec.etiqueta',
                'ce.clave',
                'ce.nombre',
                'ce.tipo_control',
            ]);

        $base = [];
        $extra = [];

        foreach ($filas as $fila) {
            $campo = (array) $fila;
            $campo['etiqueta'] = trim((string) $fila->etiqueta) !== '' ? $fila->etiqueta : $fila->nombre;

            if ($fila->origen_dato === 'equipos') {
                $base[(string) $fila->clave] = $campo['etiqueta'];
            } elseif (in_array($fila->origen_dato, ['atributo', 'tabla_tipo'], true)) {
                $extra[] = $campo;
            }
        }

        return ['base' => $base, 'extra' => $extra];
    }

    /*
     * Datos generales opcionales: solo los que el formulario del tipo usa
     * (antes se mostraban siempre Host, MAC, Garantía y Condición, campos
     * de versiones anteriores que ya no se capturan).
     */
    protected function camposGenerales(array $equipo, array $base): array
    {
        $formatoFecha = function ($valor): string {
            if (blank($valor)) {
                return '—';
            }

            try {
                return \Carbon\Carbon::parse($valor)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $valor;
            }
        };

        $opcionales = [
            'direccion_ip' => fn () => $equipo['direccion_ip'] ?? null,
            'numero_factura' => fn () => $equipo['numero_factura'] ?? null,
            'fecha_compra' => fn () => $formatoFecha($equipo['fecha_compra'] ?? null),
            'id_proveedor' => fn () => $equipo['proveedor'] ?? null,
        ];

        $resultado = [];

        foreach ($base as $clave => $etiqueta) {
            if (! isset($opcionales[$clave])) {
                continue;
            }

            $valor = $opcionales[$clave]();

            $resultado[] = [
                'label' => $etiqueta,
                'value' => filled($valor) ? (string) $valor : '—',
            ];
        }

        return $resultado;
    }

    protected function cargarEspecificaciones(
        array $equipo,
        array $campos
    ): array {
        if ($campos === []) {
            return [];
        }

        // Valores guardados como atributos del equipo.
        $atributos = DB::table('atributos_equipo')
            ->where('id_equipo', $this->equipoId)
            ->whereNotNull('id_campo')
            ->pluck('valor', 'id_campo')
            ->all();

        // Valores en la tabla propia del tipo (solo tablas conocidas).
        $tablasConocidas = collect($this->childConfig())->pluck('table')->all();
        $filasTabla = [];

        foreach (collect($campos)->where('origen_dato', 'tabla_tipo')->pluck('tabla_destino')->unique() as $tabla) {
            if (in_array($tabla, $tablasConocidas, true) && \App\Support\Esquema::tieneTabla($tabla)) {
                $filasTabla[$tabla] = (array) (DB::table($tabla)->where('id_equipo', $this->equipoId)->first() ?? []);
            }
        }

        // Nombres de las opciones de las listas.
        $selects = collect($campos)->where('tipo_control', 'select');
        $opcionesPropias = DB::table('tipo_equipo_campo_opciones')
            ->whereIn('id_tipo_campo', $selects->where('fuente_opciones', 'personalizado')->pluck('id_tipo_campo')->all() ?: [0])
            ->pluck('nombre', 'id_opcion')
            ->all();
        $opcionesCatalogo = DB::table('catalogo_valores')
            ->whereIn('id_catalogo', $selects->where('fuente_opciones', 'catalogo')->pluck('id_catalogo')->filter()->all() ?: [0])
            ->pluck('nombre', 'id_valor')
            ->all();

        $resultado = [];

        foreach ($campos as $campo) {
            $valor = $campo['origen_dato'] === 'atributo'
                ? ($atributos[$campo['id_campo']] ?? null)
                : ($filasTabla[$campo['tabla_destino']][$campo['columna_destino']] ?? null);

            $resultado[] = [
                'campo' => $campo['clave'],
                'label' => $campo['etiqueta'],
                'value' => $this->formatearEspecificacion($campo, $valor, $opcionesPropias, $opcionesCatalogo),
            ];
        }

        return $resultado;
    }

    protected function formatearEspecificacion(
        array $campo,
        mixed $valor,
        array $opcionesPropias,
        array $opcionesCatalogo
    ): string {
        if ($valor === null || $valor === '') {
            return '—';
        }

        return match ($campo['tipo_control']) {
            'boolean' => in_array($valor, [true, 1, '1', 'true', 't'], true) ? 'Sí' : 'No',
            'date' => rescue(fn () => \Carbon\Carbon::parse($valor)->format('d/m/Y'), (string) $valor, false),
            'select' => (string) (match ($campo['fuente_opciones']) {
                'personalizado' => $opcionesPropias[(int) $valor] ?? $valor,
                'catalogo' => $opcionesCatalogo[(int) $valor] ?? $valor,
                default => $valor,
            }),
            default => (string) $valor,
        };
    }

    protected function cargarAsignaciones(): array

    {

        $rows = DB::table(

            'asignaciones as asi'

        )

            ->leftJoin('unidades_organizativas as hotel_asig', 'hotel_asig.id_unidad', '=', 'asi.id_unidad')

            ->leftJoin(

                'departamentos as dep',

                'dep.id_departamento',

                '=',

                'asi.id_departamento'

            )

            ->leftJoin(

                'catalogo_valores as tipo',

                'tipo.id_valor',

                '=',

                'asi.id_tipo_asignacion'

            )

            ->where(

                'asi.id_equipo',

                $this->equipoId

            )

            ->orderByDesc(

                'asi.fecha_asignacion'

            )

            ->limit(5)

            ->select([

                'asi.nombre_colaborador',

                'asi.fecha_asignacion',

                'asi.id_unidad',

                'asi.id_departamento',

                'asi.fecha_fin',

                'asi.id_tipo_asignacion',

                'hotel_asig.nombre as hotel_nombre',

                'dep.nombre as departamento_nombre',

                'tipo.nombre as tipo_asignacion',

            ])

            ->get();

        return $rows

            ->map(function ($row) {

                $row =

                    (array) $row;

                // Hotel / Departamento (las áreas ya no se usan).
                $ubicacion = implode(' / ', array_filter([
                    $row['hotel_nombre'] ?? null,
                    $row['departamento_nombre'] ?? null,
                ])) ?: '—';

                return [

                    'nombre_colaborador' =>

                        $row['nombre_colaborador']

                        ?? '—',

                    'fecha_asignacion' =>

                        $row['fecha_asignacion']

                        ?? null,

                    'tipo_asignacion' =>

                        $row['tipo_asignacion']

                        ?? 'Asignación',

                    'ubicacion' =>

                        $ubicacion,

                    // Con fecha de fin: asignación cerrada (reasignada o devuelta).
                    'fecha_fin' =>
                        $row['fecha_fin'] ?? null,

                ];

            })

            ->values()

            ->all();

    }

    public function placeholder()

    {

        return view(

            'livewire.placeholders.equipo-show'

        );

    }

    public function render()

    {

        $equipo =

            $this->cargarEquipo();

        // Solo los campos del formulario configurado para este tipo.
        $camposTipo = $this->camposDelTipo((int) ($equipo['id_tipo_equipo'] ?? 0));

        $camposGenerales = $this->camposGenerales($equipo, $camposTipo['base']);

        $especificaciones = $this->cargarEspecificaciones($equipo, $camposTipo['extra']);

        $asignaciones =

            $this->cargarAsignaciones();

        $atributos = DB::table('atributos_equipo as a')
            ->join('campos_equipo as c', 'c.id_campo', '=', 'a.id_campo')
            ->join('tipo_equipo_campos as tc', function ($join) use ($equipo) {
                $join->on('tc.id_campo', '=', 'c.id_campo')->where('tc.id_tipo_equipo', $equipo['id_tipo_equipo']);
            })
            ->leftJoin('catalogo_valores as cv', function ($join) {
                $join->on('cv.id_catalogo', '=', 'tc.id_catalogo')->whereRaw('CAST(cv.id_valor AS TEXT) = a.valor');
            })
            ->leftJoin('tipo_equipo_campo_opciones as op', function ($join) {
                $join->on('op.id_tipo_campo', '=', 'tc.id_tipo_campo')->whereRaw('CAST(op.id_opcion AS TEXT) = a.valor');
            })
            ->where('a.id_equipo', $this->equipoId)->where('tc.origen_dato', 'atributo')->where('tc.visible', true)
            ->orderBy('tc.orden')->get(['c.clave', 'c.tipo_control', 'c.nombre', 'tc.etiqueta', 'a.valor', 'cv.nombre as catalogo', 'op.nombre as opcion'])
            ->map(fn ($campo) => [
                'campo' => $campo->clave, 'label' => $campo->etiqueta ?: $campo->nombre,
                'value' => $campo->tipo_control === 'boolean' ? (filter_var($campo->valor, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No') : ($campo->catalogo ?? $campo->opcion ?? $campo->valor ?? '—'),
            ])->all();
        $especificaciones = array_merge($especificaciones, $atributos);
        $clasificacionBien = \App\Services\ClasificacionBienService::OPCIONES[
            app(\App\Services\ClasificacionBienService::class)->porTipo()[$equipo['id_tipo_equipo']]
        ];

        // Solo la vigente: si se devolvió, el equipo no tiene responsable.
        $asignacionActual = collect($asignaciones)
            ->first(fn (array $asignacion) => empty($asignacion['fecha_fin']));

        $movimientos =

            collect($asignaciones)

                ->map(

                    fn ($asignacion) => [

                        'fecha' =>

                            $asignacion[

                                'fecha_asignacion'

                            ]

                            ?? null,

                        'operacion' =>

                            $asignacion[

                                'tipo_asignacion'

                            ]

                            ?? 'Asignación',

                        'responsable' =>

                            $asignacion[

                                'nombre_colaborador'

                            ]

                            ?? '—',

                        'ubicacion' =>

                            $asignacion[

                                'ubicacion'

                            ]

                            ?? '—',

                    ]

                )

                ->all();

        /*
         * Botón «Editar»: el formulario no se abre para equipos dados de
         * baja (mismo criterio que EquipoEdit::equipoDadoDeBaja).
         */
        $dadoDeBaja = DB::table('equipos as e')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->where('e.id_equipo', $this->equipoId)
            ->where(function ($query): void {
                $query->where('ee.clave', 'BAJA')
                    ->orWhereExists(function ($subquery): void {
                        $subquery->selectRaw('1')
                            ->from('bajas as b')
                            ->whereColumn('b.id_equipo', 'e.id_equipo');
                    });
            })
            ->exists();


        /*
         * Programas instalados: la sección aparece si el tipo «Incluye
         * programas» o si el equipo ya tiene alguno registrado.
         */
        $servicioProgramas = app(\App\Services\ProgramasService::class);
        $programas = $servicioProgramas->delEquipo($this->equipoId);
        $muestraProgramas = $servicioProgramas->disponible()
            && ($programas !== [] || $servicioProgramas->tipoIncluye((int) ($equipo['id_tipo_equipo'] ?? 0)));

        return view(
            'livewire.equipo-show',
            compact(
                'equipo',
                'clasificacionBien',
                'camposGenerales',
                'especificaciones',
                'asignacionActual',
                'movimientos',
                'dadoDeBaja',
                'programas',
                'muestraProgramas'
            )
        );

    }

}
