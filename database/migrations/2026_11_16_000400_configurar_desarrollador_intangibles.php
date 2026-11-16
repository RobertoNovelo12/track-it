<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('tipos_equipo')->where('clasificacion_bien', 'intangibles')->pluck('id_tipo_equipo') as $id) {
            $secciones = DB::table('tipo_equipo_secciones')->where('id_tipo_equipo', $id)->pluck('id_seccion', 'clave')->all();
            app(\App\Services\ClasificacionBienService::class)->completarCampos((int) $id, 'intangibles', $secciones);
        }
    }

    public function down(): void
    {
        // Conservar los valores que se hayan registrado.
    }
};
