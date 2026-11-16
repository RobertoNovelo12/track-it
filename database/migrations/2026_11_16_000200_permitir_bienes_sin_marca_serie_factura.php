<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', fn (Blueprint $table) => $table->bigInteger('id_marca')->nullable()->change());
        $tipos = DB::table('tipos_equipo')->whereIn('clasificacion_bien', ['inmuebles', 'intangibles'])
            ->orWhereRaw('LOWER(nombre) = ?', ['mobiliario'])->pluck('id_tipo_equipo');
        $campos = DB::table('campos_equipo')->whereIn('clave', ['id_marca', 'numero_serie', 'numero_factura'])->pluck('id_campo');
        DB::table('tipo_equipo_campos')->whereIn('id_tipo_equipo', $tipos)->whereIn('id_campo', $campos)->update(['obligatorio' => false]);
        DB::table('tipo_equipo_campos')->whereIn('id_tipo_equipo', DB::table('tipos_equipo')->where('clasificacion_bien', 'vehiculos')->select('id_tipo_equipo'))
            ->whereIn('id_campo', DB::table('campos_equipo')->where('clave', 'numero_factura')->select('id_campo'))->update(['obligatorio' => false]);
    }

    public function down(): void
    {
        // No imponer NOT NULL de nuevo: podría invalidar bienes registrados sin marca.
    }
};
