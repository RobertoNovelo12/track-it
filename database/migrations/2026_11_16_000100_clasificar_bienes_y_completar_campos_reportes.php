<?php

use App\Services\TipoEquipoFormBuilderService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_equipo', function (Blueprint $table) {
            $table->string('clasificacion_bien', 20)->default('global')->index();
        });

        // Solo la migración interpreta nombres antiguos; las consultas usan la clave estable.
        foreach (['intangibles' => ['intangible', 'intangibles'], 'inmuebles' => ['inmueble', 'inmuebles'], 'vehiculos' => ['vehículo', 'vehículos', 'vehiculo', 'vehiculos']] as $clave => $nombres) {
            DB::table('tipos_equipo')->whereIn(DB::raw('LOWER(nombre)'), $nombres)->update(['clasificacion_bien' => $clave]);
        }
        foreach (['Mobiliario' => 'global', 'Intangible' => 'intangibles', 'Inmueble' => 'inmuebles', 'Vehículo' => 'vehiculos'] as $nombre => $clave) {
            $tipo = DB::table('tipos_equipo')->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();
            if ($tipo) {
                DB::table('tipos_equipo')->where('id_tipo_equipo', $tipo->id_tipo_equipo)->update(['activo' => true]);
            } else {
                DB::table('tipos_equipo')->insert(['nombre' => $nombre, 'activo' => true, 'clasificacion_bien' => $clave]);
            }
        }
        foreach (DB::table('tipos_equipo')->pluck('id_tipo_equipo') as $id) {
            app(TipoEquipoFormBuilderService::class)->ensureRequiredFields((int) $id);
        }
    }

    public function down(): void
    {
        // Los atributos y sus valores se conservan incluso al revertir la clasificación.
        Schema::table('tipos_equipo', fn (Blueprint $table) => $table->dropColumn('clasificacion_bien'));
    }
};
