<?php

use App\Services\TipoEquipoFormBuilderService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Tipo de equipo
    |--------------------------------------------------------------------------
    */

    public int $tipoEquipoId;


    /*
    |--------------------------------------------------------------------------
    | Datos iniciales
    |--------------------------------------------------------------------------
    */

    public array $initialData = [];


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(
        int $tipoEquipoId
    ): void {
        $this->tipoEquipoId =
            $tipoEquipoId;


        $this->initialData =
            $this->builder()
                ->load(
                    $this->tipoEquipoId
                );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar configuración
    |--------------------------------------------------------------------------
    */

    public function saveConfiguration(
        array $fields
    ): array {
        return $this->builder()
            ->save(
                $this->tipoEquipoId,
                $fields
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar opciones de listas del sistema
    |--------------------------------------------------------------------------
    */

    public function loadFieldOptions(
        array $field
    ): array {
        $source =
            isset(
                $field[
                    'fuente_opciones'
                ]
            )
                ? (string) $field[
                    'fuente_opciones'
                ]
                : null;


        $idCatalogo =
            isset(
                $field[
                    'id_catalogo'
                ]
            )
            &&
            is_numeric(
                $field[
                    'id_catalogo'
                ]
            )
                ? (int) $field[
                    'id_catalogo'
                ]
                : null;


        /*
        |--------------------------------------------------------------------------
        | Catálogo configurable
        |--------------------------------------------------------------------------
        */

        if (
            $source === 'catalogo'
            &&
            $idCatalogo !== null
        ) {
            $catalogo =
                DB::table(
                    'catalogos'
                )
                    ->where(
                        'id_catalogo',
                        $idCatalogo
                    )
                    ->first([
                        'nombre',
                    ]);


            $items =
                DB::table(
                    'catalogo_valores'
                )
                    ->where(
                        'id_catalogo',
                        $idCatalogo
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->orderBy(
                        'orden'
                    )
                    ->orderBy(
                        'nombre'
                    )
                    ->get([
                        'id_valor',
                        'nombre',
                    ])
                    ->map(
                        fn ($item): array => [
                            'id' =>
                                (int) $item->id_valor,

                            'nombre' =>
                                (string) $item->nombre,
                        ]
                    )
                    ->values()
                    ->all();


            return [
                'source' =>
                    $catalogo
                        ? (string) $catalogo->nombre
                        : 'Catálogo del sistema',

                'items' =>
                    $items,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Fuentes directas
        |--------------------------------------------------------------------------
        */

        $definitions = [

            'estados_equipo' => [
                'table' =>
                    'estados_equipo',

                'id' =>
                    'id_estado_equipo',

                'label' =>
                    'Estados de equipo',

                'order' =>
                    'orden',
            ],


            'marcas' => [
                'table' =>
                    'marcas',

                'id' =>
                    'id_marca',

                'label' =>
                    'Marcas',

                'order' =>
                    'nombre',
            ],


            'modelos' => [
                'table' =>
                    'modelos',

                'id' =>
                    'id_modelo',

                'label' =>
                    'Modelos',

                'order' =>
                    'nombre',
            ],


            'proveedores' => [
                'table' =>
                    'proveedores',

                'id' =>
                    'id_proveedor',

                'label' =>
                    'Proveedores',

                'order' =>
                    'nombre',
            ],


            'unidades_organizativas' => [
                'table' => 'unidades_organizativas',
                'id' => 'id_unidad',
                'label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_plural'],
                'order' => 'nombre',
            ],

            'areas' => [
                'table' =>
                    'areas',

                'id' =>
                    'id_area',

                'label' =>
                    'Hoteles / Áreas',

                'order' =>
                    'nombre',
            ],


            'departamentos' => [
                'table' =>
                    'departamentos',

                'id' =>
                    'id_departamento',

                'label' =>
                    'Departamentos',

                'order' =>
                    'nombre',
            ],
        ];


        $definition =
            $definitions[
                $source
            ]
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Fuente desconocida
        |--------------------------------------------------------------------------
        */

        if (! $definition) {
            return [
                'source' =>
                    'Sin fuente configurada',

                'items' =>
                    [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Consulta
        |--------------------------------------------------------------------------
        */

        $query =
            DB::table(
                $definition[
                    'table'
                ]
            )
                ->where(
                    'activo',
                    true
                );


        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        */

        $query->orderBy(
            $definition[
                'order'
            ]
        );


        if (
            $definition[
                'order'
            ] !== 'nombre'
        ) {
            $query->orderBy(
                'nombre'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Opciones
        |--------------------------------------------------------------------------
        */

        $items =
            $query
                ->get([
                    $definition[
                        'id'
                    ],
                    'nombre',
                ])
                ->map(
                    function (
                        $item
                    ) use (
                        $definition
                    ): array {
                        $idColumn =
                            $definition[
                                'id'
                            ];


                        return [
                            'id' =>
                                (int) $item->$idColumn,

                            'nombre' =>
                                (string) $item->nombre,
                        ];
                    }
                )
                ->values()
                ->all();


        return [
            'source' =>
                $definition[
                    'label'
                ],

            'items' =>
                $items,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Servicio
    |--------------------------------------------------------------------------
    */

    private function builder(): TipoEquipoFormBuilderService
    {
        return app(
            TipoEquipoFormBuilderService::class
        );
    }
};

?>

{{-- ============================================================
    RAÍZ DEL COMPONENTE LIVEWIRE
============================================================ --}}
<div>
    {{-- ========================================================
        CONTROLADOR ALPINE
    ======================================================== --}}
    @include('components.catalogos.tipo-equipo-editor.alpine')

    {{-- ========================================================
        EDITOR LOCAL ALPINE
    ======================================================== --}}
    <div
        wire:ignore

        x-data="
            tipoEquipoFormBuilder(
                @js($initialData),
                $wire
            )
        "

        class="space-y-5"
    >
        {{-- TOAST --}}
        @include('components.catalogos.tipo-equipo-editor.toast')

        {{-- ENCABEZADO --}}
        @include('components.catalogos.tipo-equipo-editor.header')

        {{-- CONSTRUCTOR --}}
        <div
            class="
                grid
                grid-cols-1
                xl:grid-cols-[330px_minmax(0,1fr)]
                gap-5
                items-start
            "
        >
            {{-- PALETA --}}
            @include('components.catalogos.tipo-equipo-editor.palette')

            {{-- FORMULARIO --}}
            @include('components.catalogos.tipo-equipo-editor.builder')
        </div>

        {{-- MODAL DE OPCIONES --}}
        @include('components.catalogos.tipo-equipo-editor.options-modal')
    </div>
</div>