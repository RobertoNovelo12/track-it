<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use LogicException;

class PlantillaInstalacionService
{
    private const DEFINICIONES = [
        'hoteles' => [
            'nombre' => 'Hotelería',
            'version' => 1,

            'etiquetas' => [
                'unidad_singular' => 'Hotel',
                'unidad_plural' => 'Hoteles',

                'buscar_unidad' => 'Buscar hotel...',

                'seleccionar_unidad' =>
                    'Selecciona un hotel.',

                'seleccionar_unidad_primero' =>
                    'Elige un hotel primero',

                'todas_unidades' =>
                    'Todos los hoteles',

                'atiende_todas_unidades' =>
                    'Atiende todos los hoteles',

                'cobertura' =>
                    'Hoteles que atiende',

                'sin_unidades' =>
                    'Sin hoteles asignados',

                'incluye_futuras' =>
                    'Incluye los hoteles que se agreguen después.',

                'departamento_singular' => 'Departamento',
                'departamento_plural' => 'Departamentos',
            ],
        ],

        'escuelas' => [
            'nombre' => 'Educación',
            'version' => 1,

            'etiquetas' => [
                'unidad_singular' => 'Edificio',
                'unidad_plural' => 'Edificios',

                'buscar_unidad' => 'Buscar edificio...',

                'seleccionar_unidad' =>
                    'Selecciona un edificio.',

                'seleccionar_unidad_primero' =>
                    'Elige un edificio primero',

                'todas_unidades' =>
                    'Todos los edificios',

                'atiende_todas_unidades' =>
                    'Atiende todos los edificios',

                'cobertura' =>
                    'Edificios que atiende',

                'sin_unidades' =>
                    'Sin edificios asignados',

                'incluye_futuras' =>
                    'Incluye los edificios que se agreguen después.',

                'departamento_singular' => 'Departamento',
                'departamento_plural' => 'Departamentos',
            ],
        ],
    ];

    public function actual(): array
    {
        $config = DB::table('configuracion_instalacion')
            ->where('id', 1)
            ->first();

        if (!$config) {
            throw new LogicException(
                'La instalación todavía no tiene una plantilla definida.'
            );
        }

        $definition = self::DEFINICIONES[$config->plantilla]
            ?? null;

        if (
            !$definition
            || (int) $config->version_plantilla
                !== $definition['version']
        ) {
            throw new LogicException(
                'La plantilla o su versión no está soportada por esta aplicación.'
            );
        }

        return [
            'clave' => $config->plantilla,
            'nombre' => $definition['nombre'],
            'version' => $definition['version'],
            'configurada_en' => $config->configurada_en,
            'etiquetas' => $definition['etiquetas'],
        ];
    }

    public function etiquetas(): array
    {
        return $this->actual()['etiquetas'];
    }
}