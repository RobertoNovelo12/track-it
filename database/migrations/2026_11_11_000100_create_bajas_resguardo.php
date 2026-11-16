<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bajas_resguardo', function (Blueprint $t) {
            $t->id();
            $t->bigInteger('id_equipo')->unique();
            $t->date('fecha_resguardo');
            $t->string('motivo', 500);
            $t->text('observaciones')->nullable();
            $t->bigInteger('registrado_por');
            $t->timestamp('fecha_registro')->useCurrent();
            $t->timestamp('fecha_completada')->nullable();
            $t->index('fecha_completada');
            $t->foreign('id_equipo')->references('id_equipo')->on('equipos')->restrictOnDelete();
        });
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE bajas_resguardo ENABLE ROW LEVEL SECURITY');
        }
    }
    public function down(): void { Schema::dropIfExists('bajas_resguardo'); }
};
