<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'Mobiliario' => ['Silla', 'Mesa'],
            'Intangible' => ['Software', 'Licencia de software'],
            'Inmueble' => ['Edificio', 'Terreno'],
            'Vehículo' => ['Automóvil', 'Camioneta'],
        ] as $origen => $nombres) {
            $plantilla = DB::table('tipos_equipo')->where('nombre', $origen)->first();
            if (! $plantilla) { continue; }
            $secciones = DB::table('tipo_equipo_secciones')->where('id_tipo_equipo', $plantilla->id_tipo_equipo)->get();
            $campos = DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $plantilla->id_tipo_equipo)->get();
            foreach ($nombres as $nombre) {
                if (DB::table('tipos_equipo')->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists()) { continue; }
                $id = DB::table('tipos_equipo')->insertGetId([
                    'nombre' => $nombre, 'activo' => true, 'clasificacion_bien' => $plantilla->clasificacion_bien,
                ], 'id_tipo_equipo');
                $mapa = [];
                foreach ($secciones as $seccion) {
                    $datos = (array) $seccion;
                    unset($datos['id_seccion']);
                    $datos['id_tipo_equipo'] = $id;
                    if ($datos['clave'] === 'informacion_tipo') { $datos['nombre'] = 'Información de '.$nombre; }
                    $mapa[$seccion->id_seccion] = DB::table('tipo_equipo_secciones')->insertGetId($datos, 'id_seccion');
                }
                $filas = [];
                foreach ($campos as $campo) {
                    $datos = (array) $campo;
                    unset($datos['id_tipo_campo']);
                    $datos['id_tipo_equipo'] = $id;
                    $datos['id_seccion'] = $mapa[$campo->id_seccion] ?? null;
                    $filas[] = $datos;
                }
                if ($filas) { DB::table('tipo_equipo_campos')->insert($filas); }
            }
        }
    }

    public function down(): void
    {
        // Conservar tipos y campos: pueden tener bienes registrados.
    }
};
