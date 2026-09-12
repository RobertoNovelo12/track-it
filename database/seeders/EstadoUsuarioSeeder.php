<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('catalogos')->updateOrInsert(
            ['clave' => 'estado_usuario'],
            [
                'nombre' => 'Estado de usuario',
                'descripcion' => 'Estatus de la cuenta de un usuario del sistema',
            ]
        );

        $idCatalogo = DB::table('catalogos')->where('clave', 'estado_usuario')->value('id_catalogo');

        $valores = [
            ['clave' => 'pendiente', 'nombre' => 'Pendiente de aprobación', 'orden' => 1],
            ['clave' => 'activo', 'nombre' => 'Activo', 'orden' => 2],
            ['clave' => 'bloqueado', 'nombre' => 'Bloqueado', 'orden' => 3],
            ['clave' => 'rechazado', 'nombre' => 'Rechazado', 'orden' => 4],
        ];

        foreach ($valores as $valor) {
            DB::table('catalogo_valores')->updateOrInsert(
                ['id_catalogo' => $idCatalogo, 'clave' => $valor['clave']],
                [
                    'nombre' => $valor['nombre'],
                    'orden' => $valor['orden'],
                    'activo' => true,
                ]
            );
        }
    }
}