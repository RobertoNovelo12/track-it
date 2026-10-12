<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalogos = [
            'tipo_mantenimiento' => [
                'nombre' => 'Tipo de mantenimiento',
                'descripcion' => 'Tipos de mantenimiento realizados a los equipos.',
                'valores' => [
                    ['clave' => 'PREVENTIVO', 'nombre' => 'Preventivo'],
                    ['clave' => 'CORRECTIVO', 'nombre' => 'Correctivo'],
                ],
            ],

            'tipo_intervencion' => [
                'nombre' => 'Tipo de intervención',
                'descripcion' => 'Tipos de intervención realizadas durante un mantenimiento.',
                'valores' => [
                    ['clave' => 'INSPECCION', 'nombre' => 'Inspección'],
                    ['clave' => 'LIMPIEZA', 'nombre' => 'Limpieza'],
                    ['clave' => 'REPARACION', 'nombre' => 'Reparación'],
                    ['clave' => 'REEMPLAZO', 'nombre' => 'Reemplazo'],
                    ['clave' => 'CONFIGURACION', 'nombre' => 'Configuración'],
                    ['clave' => 'ACTUALIZACION', 'nombre' => 'Actualización'],
                ],
            ],

            'estado_mantenimiento' => [
                'nombre' => 'Estado de mantenimiento',
                'descripcion' => 'Estados disponibles para un mantenimiento.',
                'valores' => [
                    ['clave' => 'PROGRAMADO', 'nombre' => 'Programado'],
                    ['clave' => 'EN_PROCESO', 'nombre' => 'En proceso'],
                    ['clave' => 'COMPLETADO', 'nombre' => 'Completado'],
                    ['clave' => 'CANCELADO', 'nombre' => 'Cancelado'],
                ],
            ],

            'prioridad_mantenimiento' => [
                'nombre' => 'Prioridad de mantenimiento',
                'descripcion' => 'Nivel de prioridad de un mantenimiento.',
                'valores' => [
                    ['clave' => 'BAJA', 'nombre' => 'Baja'],
                    ['clave' => 'MEDIA', 'nombre' => 'Media'],
                    ['clave' => 'ALTA', 'nombre' => 'Alta'],
                    ['clave' => 'URGENTE', 'nombre' => 'Urgente'],
                ],
            ],
        ];

        DB::transaction(function () use ($catalogos) {
            foreach ($catalogos as $claveCatalogo => $catalogo) {
                DB::table('catalogos')->updateOrInsert(
                    [
                        'clave' => $claveCatalogo,
                    ],
                    [
                        'nombre' => $catalogo['nombre'],
                        'descripcion' => $catalogo['descripcion'],
                    ]
                );

                $idCatalogo = DB::table('catalogos')
                    ->where('clave', $claveCatalogo)
                    ->value('id_catalogo');

                foreach ($catalogo['valores'] as $index => $valor) {
                    DB::table('catalogo_valores')->updateOrInsert(
                        [
                            'id_catalogo' => $idCatalogo,
                            'clave' => $valor['clave'],
                        ],
                        [
                            'nombre' => $valor['nombre'],
                            'descripcion' => null,
                            'orden' => $index + 1,
                            'activo' => true,
                        ]
                    );
                }
            }
        });
    }
}