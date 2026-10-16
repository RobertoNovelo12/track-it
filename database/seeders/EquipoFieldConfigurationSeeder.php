<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EquipoFieldConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {

            /*
            |--------------------------------------------------------------------------
            | CAMPOS MAESTROS
            |--------------------------------------------------------------------------
            |
            | Un campo se registra UNA sola vez.
            |
            | Después tipo_equipo_campos determina:
            |
            | - qué tipo de equipo lo utiliza;
            | - si es visible;
            | - si es obligatorio;
            | - en qué orden aparece;
            | - dónde se guarda.
            |
            */

            $campos = [

                /*
                |--------------------------------------------------------------------------
                | CAMPOS GENERALES DE EQUIPOS
                |--------------------------------------------------------------------------
                */

                'nombre_equipo' => [
                    'nombre' =>
                        'Nombre',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Nombre descriptivo del equipo.',

                    'placeholder' =>
                        'Nombre del equipo',
                ],

                'host' => [
                    'nombre' =>
                        'Host',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Nombre de host del equipo.',

                    'placeholder' =>
                        'Nombre de host',
                ],

                'id_estado_activo' => [
                    'nombre' =>
                        'Estado',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Estado actual del equipo.',

                    'placeholder' =>
                        'Seleccionar estado',
                ],

                'id_marca' => [
                    'nombre' =>
                        'Marca',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Marca asociada al equipo.',

                    'placeholder' =>
                        'Seleccionar marca',
                ],

                'id_modelo' => [
                    'nombre' =>
                        'Modelo',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Modelo asociado al equipo.',

                    'placeholder' =>
                        'Seleccionar modelo',
                ],

                'fecha_compra' => [
                    'nombre' =>
                        'Fecha de compra',

                    'tipo_control' =>
                        'date',

                    'descripcion' =>
                        'Fecha de compra registrada para el equipo.',

                    'placeholder' =>
                        null,
                ],

                'direccion_mac' => [
                    'nombre' =>
                        'Dirección MAC',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Dirección MAC del equipo.',

                    'placeholder' =>
                        '00:00:00:00:00:00',
                ],

                'direccion_ip' => [
                    'nombre' =>
                        'Dirección IP',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Dirección IP asignada al equipo.',

                    'placeholder' =>
                        '192.168.1.100',
                ],

                'numero_factura' => [
                    'nombre' =>
                        'Número de factura',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Número de factura relacionado con la compra.',

                    'placeholder' =>
                        'Número de factura',
                ],

                'numero_serie' => [
                    'nombre' =>
                        'Número de serie',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Número de serie del equipo.',

                    'placeholder' =>
                        'Número de serie',
                ],

                'service_tag' => [
                    'nombre' =>
                        'Service Tag',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Service Tag del equipo.',

                    'placeholder' =>
                        'Service Tag',
                ],

                'id_proveedor' => [
                    'nombre' =>
                        'Proveedor',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Proveedor asociado al equipo.',

                    'placeholder' =>
                        'Seleccionar proveedor',
                ],

                'propietario' => [
                    'nombre' =>
                        'Propietario',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Responsable o propietario asociado al equipo.',

                    'placeholder' =>
                        'Nombre del propietario',
                ],

                'id_condicion_activo' => [
                    'nombre' =>
                        'Condición',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Condición física o funcional del equipo.',

                    'placeholder' =>
                        'Seleccionar condición',
                ],

                'id_area' => [
                    'nombre' =>
                        'Hotel / Área',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Área principal donde se encuentra el equipo.',

                    'placeholder' =>
                        'Seleccionar área',
                ],

                'id_departamento' => [
                    'nombre' =>
                        'Departamento',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Departamento asociado al equipo.',

                    'placeholder' =>
                        'Seleccionar departamento',
                ],

                'fecha_fin_garantia' => [
                    'nombre' =>
                        'Fin de garantía',

                    'tipo_control' =>
                        'date',

                    'descripcion' =>
                        'Fecha de finalización de la garantía.',

                    'placeholder' =>
                        null,
                ],

                'comentarios' => [
                    'nombre' =>
                        'Comentarios',

                    'tipo_control' =>
                        'textarea',

                    'descripcion' =>
                        'Comentarios adicionales del equipo.',

                    'placeholder' =>
                        'Comentarios adicionales...',
                ],


                /*
                |--------------------------------------------------------------------------
                | CAMPOS ESPECÍFICOS
                |--------------------------------------------------------------------------
                */

                'procesador' => [
                    'nombre' =>
                        'Procesador',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Procesador instalado en el equipo.',

                    'placeholder' =>
                        'Procesador',
                ],

                'ram_gb' => [
                    'nombre' =>
                        'RAM (GB)',

                    'tipo_control' =>
                        'number',

                    'descripcion' =>
                        'Cantidad de memoria RAM en GB.',

                    'placeholder' =>
                        'Ej. 16',
                ],

                'almacenamiento_gb' => [
                    'nombre' =>
                        'Almacenamiento (GB)',

                    'tipo_control' =>
                        'number',

                    'descripcion' =>
                        'Capacidad de almacenamiento en GB.',

                    'placeholder' =>
                        'Ej. 512',
                ],

                'id_tipo_almacenamiento' => [
                    'nombre' =>
                        'Tipo de almacenamiento',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Tipo de unidad de almacenamiento.',

                    'placeholder' =>
                        'Seleccionar tipo',
                ],

                'sistema_operativo' => [
                    'nombre' =>
                        'Sistema operativo',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Sistema operativo instalado.',

                    'placeholder' =>
                        'Sistema operativo',
                ],

                'incluye_cargador' => [
                    'nombre' =>
                        'Incluye cargador',

                    'tipo_control' =>
                        'boolean',

                    'descripcion' =>
                        'Indica si el equipo incluye cargador.',

                    'placeholder' =>
                        null,
                ],

                'tamano_pulgadas' => [
                    'nombre' =>
                        'Tamaño (pulgadas)',

                    'tipo_control' =>
                        'number',

                    'descripcion' =>
                        'Tamaño del equipo o pantalla en pulgadas.',

                    'placeholder' =>
                        'Ej. 24',
                ],

                'id_tipo_conexion' => [
                    'nombre' =>
                        'Tipo de conexión',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Tipo de conexión utilizada por el equipo.',

                    'placeholder' =>
                        'Seleccionar conexión',
                ],

                'imei' => [
                    'nombre' =>
                        'IMEI',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'IMEI del dispositivo.',

                    'placeholder' =>
                        'IMEI',
                ],

                'imei_1' => [
                    'nombre' =>
                        'IMEI 1',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'IMEI principal del dispositivo.',

                    'placeholder' =>
                        'IMEI 1',
                ],

                'imei_2' => [
                    'nombre' =>
                        'IMEI 2',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'IMEI secundario del dispositivo.',

                    'placeholder' =>
                        'IMEI 2',
                ],

                'numero_telefonico' => [
                    'nombre' =>
                        'Número telefónico',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Número telefónico asociado.',

                    'placeholder' =>
                        'Número telefónico',
                ],

                'color' => [
                    'nombre' =>
                        'Color',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Color del equipo.',

                    'placeholder' =>
                        'Color',
                ],

                'id_tipo_impresora' => [
                    'nombre' =>
                        'Tipo de impresora',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Clasificación de la impresora.',

                    'placeholder' =>
                        'Seleccionar tipo',
                ],

                'nombre_red' => [
                    'nombre' =>
                        'Nombre en red',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Nombre utilizado por el equipo en la red.',

                    'placeholder' =>
                        'Nombre en red',
                ],

                'numero_puertos' => [
                    'nombre' =>
                        'Número de puertos',

                    'tipo_control' =>
                        'number',

                    'descripcion' =>
                        'Cantidad de puertos disponibles.',

                    'placeholder' =>
                        'Ej. 24',
                ],

                'velocidad' => [
                    'nombre' =>
                        'Velocidad',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Velocidad soportada por el dispositivo.',

                    'placeholder' =>
                        'Ej. 1 Gbps',
                ],

                'administrable' => [
                    'nombre' =>
                        'Administrable',

                    'tipo_control' =>
                        'boolean',

                    'descripcion' =>
                        'Indica si el dispositivo es administrable.',

                    'placeholder' =>
                        null,
                ],

                'nombre_ap' => [
                    'nombre' =>
                        'Nombre del Access Point',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Nombre asignado al Access Point.',

                    'placeholder' =>
                        'Nombre del Access Point',
                ],

                'id_tipo_servidor' => [
                    'nombre' =>
                        'Tipo de servidor',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Clasificación del servidor.',

                    'placeholder' =>
                        'Seleccionar tipo',
                ],

                'capacidad_va' => [
                    'nombre' =>
                        'Capacidad (VA)',

                    'tipo_control' =>
                        'number',

                    'descripcion' =>
                        'Capacidad del UPS expresada en VA.',

                    'placeholder' =>
                        'Ej. 1500',
                ],

                'fecha_cambio_bateria' => [
                    'nombre' =>
                        'Fecha de cambio de batería',

                    'tipo_control' =>
                        'date',

                    'descripcion' =>
                        'Fecha del último cambio de batería.',

                    'placeholder' =>
                        null,
                ],

                'id_estado_bateria' => [
                    'nombre' =>
                        'Estado de batería',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Estado actual de la batería.',

                    'placeholder' =>
                        'Seleccionar estado',
                ],

                'id_tipo_telefono' => [
                    'nombre' =>
                        'Tipo de teléfono',

                    'tipo_control' =>
                        'select',

                    'descripcion' =>
                        'Clasificación del teléfono.',

                    'placeholder' =>
                        'Seleccionar tipo',
                ],

                'extension' => [
                    'nombre' =>
                        'Extensión',

                    'tipo_control' =>
                        'text',

                    'descripcion' =>
                        'Extensión telefónica asignada.',

                    'placeholder' =>
                        'Extensión',
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | INSERTAR CAMPOS
            |--------------------------------------------------------------------------
            |
            | insertOrIgnore evita modificar configuraciones ya existentes si
            | este seeder llegara a ejecutarse nuevamente.
            |
            */

            foreach ($campos as $clave => $campo) {
                DB::table(
                    'campos_equipo'
                )->insertOrIgnore([
                    'clave' =>
                        $clave,

                    'nombre' =>
                        $campo['nombre'],

                    'tipo_control' =>
                        $campo['tipo_control'],

                    'descripcion' =>
                        $campo['descripcion'],

                    'placeholder' =>
                        $campo['placeholder'],

                    'es_sistema' =>
                        true,

                    'activo' =>
                        true,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | IDS DE CAMPOS
            |--------------------------------------------------------------------------
            */

            $campoIds =
                DB::table(
                    'campos_equipo'
                )
                    ->whereIn(
                        'clave',
                        array_keys(
                            $campos
                        )
                    )
                    ->pluck(
                        'id_campo',
                        'clave'
                    );


            /*
            |--------------------------------------------------------------------------
            | CATÁLOGOS NECESARIOS
            |--------------------------------------------------------------------------
            */

            $catalogosNecesarios = [
                'condicion_activo',
                'tipo_almacenamiento',
                'tipo_conexion',
                'tipo_impresora',
                'tipo_servidor',
                'estado_bateria',
                'tipo_telefono',
            ];


            $catalogoIds =
                DB::table(
                    'catalogos'
                )
                    ->whereIn(
                        'clave',
                        $catalogosNecesarios
                    )
                    ->pluck(
                        'id_catalogo',
                        'clave'
                    );


            /*
            |--------------------------------------------------------------------------
            | Validar catálogos
            |--------------------------------------------------------------------------
            */

            foreach (
                $catalogosNecesarios
                as $catalogo
            ) {
                if (
                    ! isset(
                        $catalogoIds[
                            $catalogo
                        ]
                    )
                ) {
                    throw new RuntimeException(
                        "No existe el catálogo requerido: {$catalogo}"
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | FUNCIÓN PARA ASOCIAR UN CAMPO
            |--------------------------------------------------------------------------
            */

            $asociarCampo =
                function (
                    int $idTipoEquipo,
                    string $clave,
                    string $origenDato,
                    ?string $tablaDestino,
                    ?string $columnaDestino,
                    ?string $fuenteOpciones,
                    ?string $catalogoClave,
                    string $seccion,
                    bool $visible,
                    bool $obligatorio,
                    int $orden
                ) use (
                    $campoIds,
                    $catalogoIds
                ): void {

                    if (
                        ! isset(
                            $campoIds[
                                $clave
                            ]
                        )
                    ) {
                        throw new RuntimeException(
                            "No existe el campo maestro: {$clave}"
                        );
                    }


                    $idCatalogo =
                        $catalogoClave !== null
                            ? (
                                $catalogoIds[
                                    $catalogoClave
                                ]
                                ?? null
                            )
                            : null;


                    DB::table(
                        'tipo_equipo_campos'
                    )->insertOrIgnore([
                        'id_tipo_equipo' =>
                            $idTipoEquipo,

                        'id_campo' =>
                            (int) $campoIds[
                                $clave
                            ],

                        'origen_dato' =>
                            $origenDato,

                        'tabla_destino' =>
                            $tablaDestino,

                        'columna_destino' =>
                            $columnaDestino,

                        'fuente_opciones' =>
                            $fuenteOpciones,

                        'id_catalogo' =>
                            $idCatalogo,

                        'seccion' =>
                            $seccion,

                        'visible' =>
                            $visible,

                        'obligatorio' =>
                            $obligatorio,

                        'orden' =>
                            $orden,
                    ]);
                };


            /*
            |--------------------------------------------------------------------------
            | CONFIGURACIÓN GENERAL
            |--------------------------------------------------------------------------
            |
            | Estos campos se asocian inicialmente a todos los tipos existentes.
            |
            | Se conserva la obligatoriedad que actualmente utiliza el formulario:
            |
            | - Nombre
            | - Estado
            | - Marca
            | - Número de serie
            | - Número de factura
            | - Área
            |
            | Service Tag e IP quedan disponibles pero inicialmente ocultos,
            | porque existen físicamente en equipos pero todavía no forman parte
            | del formulario actual.
            |
            */

            $camposGenerales = [

                [
                    'clave' =>
                        'nombre_equipo',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'nombre_equipo',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        10,
                ],

                [
                    'clave' =>
                        'host',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'host',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        20,
                ],

                [
                    'clave' =>
                        'id_estado_activo',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_estado_activo',

                    'fuente' =>
                        'estados_equipo',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        30,
                ],

                [
                    'clave' =>
                        'id_marca',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_marca',

                    'fuente' =>
                        'marcas',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        40,
                ],

                [
                    'clave' =>
                        'id_modelo',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_modelo',

                    'fuente' =>
                        'modelos',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        50,
                ],

                [
                    'clave' =>
                        'numero_serie',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'numero_serie',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        60,
                ],

                [
                    'clave' =>
                        'service_tag',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'service_tag',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        false,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        70,
                ],

                [
                    'clave' =>
                        'direccion_mac',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'direccion_mac',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        80,
                ],

                [
                    'clave' =>
                        'direccion_ip',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'direccion_ip',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'general',

                    'visible' =>
                        false,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        90,
                ],

                [
                    'clave' =>
                        'fecha_compra',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'fecha_compra',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'compra',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        100,
                ],

                [
                    'clave' =>
                        'numero_factura',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'numero_factura',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'compra',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        110,
                ],

                [
                    'clave' =>
                        'id_proveedor',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_proveedor',

                    'fuente' =>
                        'proveedores',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'compra',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        120,
                ],

                [
                    'clave' =>
                        'fecha_fin_garantia',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'fecha_fin_garantia',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'compra',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        130,
                ],

                [
                    'clave' =>
                        'id_condicion_activo',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_condicion_activo',

                    'fuente' =>
                        'catalogo',

                    'catalogo' =>
                        'condicion_activo',

                    'seccion' =>
                        'general',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        140,
                ],

                [
                    'clave' =>
                        'id_area',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_area',

                    'fuente' =>
                        'areas',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'ubicacion',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        true,

                    'orden' =>
                        150,
                ],

                [
                    'clave' =>
                        'id_departamento',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'id_departamento',

                    'fuente' =>
                        'departamentos',

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'ubicacion',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        160,
                ],

                [
                    'clave' =>
                        'propietario',

                    'origen' =>
                        'asignacion',

                    'tabla' =>
                        'asignaciones',

                    'columna' =>
                        'nombre_colaborador',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'adicional',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        170,
                ],

                [
                    'clave' =>
                        'comentarios',

                    'origen' =>
                        'equipos',

                    'tabla' =>
                        'equipos',

                    'columna' =>
                        'comentarios',

                    'fuente' =>
                        null,

                    'catalogo' =>
                        null,

                    'seccion' =>
                        'adicional',

                    'visible' =>
                        true,

                    'obligatorio' =>
                        false,

                    'orden' =>
                        180,
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | TIPOS EXISTENTES
            |--------------------------------------------------------------------------
            */

            $tiposEquipo =
                DB::table(
                    'tipos_equipo'
                )
                    ->pluck(
                        'id_tipo_equipo'
                    );


            /*
            |--------------------------------------------------------------------------
            | ASOCIAR CAMPOS GENERALES
            |--------------------------------------------------------------------------
            */

            foreach (
                $tiposEquipo
                as $idTipoEquipo
            ) {
                foreach (
                    $camposGenerales
                    as $campo
                ) {
                    $asociarCampo(
                        (int) $idTipoEquipo,
                        $campo['clave'],
                        $campo['origen'],
                        $campo['tabla'],
                        $campo['columna'],
                        $campo['fuente'],
                        $campo['catalogo'],
                        $campo['seccion'],
                        $campo['visible'],
                        $campo['obligatorio'],
                        $campo['orden']
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CONFIGURACIÓN ESPECÍFICA
            |--------------------------------------------------------------------------
            |
            | Se mantiene la correspondencia que actualmente utiliza el sistema
            | para las tablas especializadas de cada tipo.
            |
            */

            $baseComputadora = [

                [
                    'clave' =>
                        'procesador',

                    'tipo' =>
                        'text',
                ],

                [
                    'clave' =>
                        'ram_gb',

                    'tipo' =>
                        'number',
                ],

                [
                    'clave' =>
                        'almacenamiento_gb',

                    'tipo' =>
                        'number',
                ],

                [
                    'clave' =>
                        'id_tipo_almacenamiento',

                    'tipo' =>
                        'catalog',

                    'catalogo' =>
                        'tipo_almacenamiento',
                ],

                [
                    'clave' =>
                        'sistema_operativo',

                    'tipo' =>
                        'text',
                ],
            ];


            $configuracionEspecifica = [

                1 => [
                    'tabla' =>
                        'computadoras_escritorio',

                    'campos' =>
                        $baseComputadora,
                ],

                2 => [
                    'tabla' =>
                        'laptops',

                    'campos' =>
                        array_merge(
                            $baseComputadora,
                            [
                                [
                                    'clave' =>
                                        'incluye_cargador',

                                    'tipo' =>
                                        'boolean',
                                ],
                            ]
                        ),
                ],

                3 => [
                    'tabla' =>
                        'monitores',

                    'campos' => [
                        [
                            'clave' =>
                                'tamano_pulgadas',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],
                    ],
                ],

                4 => [
                    'tabla' =>
                        'tablets',

                    'campos' => [
                        [
                            'clave' =>
                                'imei',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'almacenamiento_gb',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'sistema_operativo',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'color',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'incluye_cargador',

                            'tipo' =>
                                'boolean',
                        ],
                    ],
                ],

                5 => [
                    'tabla' =>
                        'moviles',

                    'campos' => [
                        [
                            'clave' =>
                                'imei_1',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'imei_2',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'numero_telefonico',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'almacenamiento_gb',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'sistema_operativo',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'color',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'incluye_cargador',

                            'tipo' =>
                                'boolean',
                        ],
                    ],
                ],

                6 => [
                    'tabla' =>
                        'impresoras',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_impresora',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_impresora',
                        ],

                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],

                        [
                            'clave' =>
                                'nombre_red',

                            'tipo' =>
                                'text',
                        ],
                    ],
                ],

                7 => [
                    'tabla' =>
                        'escaneres',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],
                    ],
                ],

                8 => [
                    'tabla' =>
                        'grabadores_llaves',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],
                    ],
                ],

                9 => [
                    'tabla' =>
                        'tpv',

                    'campos' =>
                        $baseComputadora,
                ],

                10 => [
                    'tabla' =>
                        'switches',

                    'campos' => [
                        [
                            'clave' =>
                                'numero_puertos',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'velocidad',

                            'tipo' =>
                                'text',
                        ],

                        [
                            'clave' =>
                                'administrable',

                            'tipo' =>
                                'boolean',
                        ],
                    ],
                ],

                11 => [
                    'tabla' =>
                        'routers',

                    'campos' => [
                        [
                            'clave' =>
                                'numero_puertos',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'velocidad',

                            'tipo' =>
                                'text',
                        ],
                    ],
                ],

                12 => [
                    'tabla' =>
                        'access_points',

                    'campos' => [
                        [
                            'clave' =>
                                'nombre_ap',

                            'tipo' =>
                                'text',
                        ],
                    ],
                ],

                13 => [
                    'tabla' =>
                        'servidores',

                    'campos' =>
                        array_merge(
                            [
                                [
                                    'clave' =>
                                        'id_tipo_servidor',

                                    'tipo' =>
                                        'catalog',

                                    'catalogo' =>
                                        'tipo_servidor',
                                ],
                            ],
                            $baseComputadora
                        ),
                ],

                14 => [
                    'tabla' =>
                        'ups',

                    'campos' => [
                        [
                            'clave' =>
                                'capacidad_va',

                            'tipo' =>
                                'number',
                        ],

                        [
                            'clave' =>
                                'fecha_cambio_bateria',

                            'tipo' =>
                                'date',
                        ],

                        [
                            'clave' =>
                                'id_estado_bateria',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'estado_bateria',
                        ],
                    ],
                ],

                15 => [
                    'tabla' =>
                        'telefonos',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_telefono',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_telefono',
                        ],

                        [
                            'clave' =>
                                'extension',

                            'tipo' =>
                                'text',
                        ],
                    ],
                ],

                16 => [
                    'tabla' =>
                        'teclados',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],
                    ],
                ],

                17 => [
                    'tabla' =>
                        'mouse',

                    'campos' => [
                        [
                            'clave' =>
                                'id_tipo_conexion',

                            'tipo' =>
                                'catalog',

                            'catalogo' =>
                                'tipo_conexion',
                        ],
                    ],
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | ASOCIAR CAMPOS ESPECÍFICOS
            |--------------------------------------------------------------------------
            */

            foreach (
                $configuracionEspecifica
                as $idTipoEquipo => $configuracion
            ) {
                /*
                |--------------------------------------------------------------------------
                | Si ese tipo ya no existe, simplemente no hacemos nada.
                |--------------------------------------------------------------------------
                */

                if (
                    ! $tiposEquipo->contains(
                        $idTipoEquipo
                    )
                ) {
                    continue;
                }


                $orden =
                    200;


                foreach (
                    $configuracion['campos']
                    as $campo
                ) {
                    $esCatalogo =
                        (
                            $campo['tipo']
                            ?? null
                        ) === 'catalog';


                    $asociarCampo(
                        $idTipoEquipo,
                        $campo['clave'],
                        'tabla_tipo',
                        $configuracion['tabla'],
                        $campo['clave'],
                        $esCatalogo
                            ? 'catalogo'
                            : null,
                        $esCatalogo
                            ? (
                                $campo['catalogo']
                                ?? null
                            )
                            : null,
                        'especifico',
                        true,
                        false,
                        $orden
                    );


                    $orden +=
                        10;
                }
            }
        });
    }
}