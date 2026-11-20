<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['bajas', 'bajas_resguardo'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->string('solicitante', 255)->nullable());
        }
    }

    public function down(): void
    {
        foreach (['bajas', 'bajas_resguardo'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('solicitante'));
        }
    }
};
