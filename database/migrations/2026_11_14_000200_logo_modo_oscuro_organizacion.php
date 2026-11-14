<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Logotipo opcional para modo oscuro (Administración > Organización).
 * Si existe, en modo oscuro se muestra tal cual en lugar de aplicar la
 * «Apariencia en modo oscuro» al logotipo principal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_organizacion', function (Blueprint $table) {
            if (! Schema::hasColumn('configuracion_organizacion', 'logo_oscuro_base64')) {
                $table->text('logo_oscuro_base64')->nullable();
                $table->string('logo_oscuro_mime', 50)->nullable();
                $table->string('logo_oscuro_nombre_original', 255)->nullable();
                $table->string('logo_oscuro_version', 40)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_organizacion', function (Blueprint $table) {
            $table->dropColumn(['logo_oscuro_base64', 'logo_oscuro_mime', 'logo_oscuro_nombre_original', 'logo_oscuro_version']);
        });
    }
};
