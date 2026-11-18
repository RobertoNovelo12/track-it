<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Marcas y modelos creados al dar de alta un equipo (escribiendo un nombre
 * que no existía): quedan marcados como «Falta información» hasta que
 * alguien los complete en Administración › Marcas / Modelos.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['marcas', 'modelos'] as $tabla) {
            if (! Schema::hasColumn($tabla, 'pendiente_revision')) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->boolean('pendiente_revision')->default(false);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['marcas', 'modelos'] as $tabla) {
            if (Schema::hasColumn($tabla, 'pendiente_revision')) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->dropColumn('pendiente_revision');
                });
            }
        }
    }
};
