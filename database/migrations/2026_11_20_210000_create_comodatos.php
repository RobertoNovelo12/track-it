<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('comodatos', function(Blueprint $t){ $t->id(); $t->unsignedBigInteger('id_equipo'); $t->string('solicitante'); $t->string('escuela'); $t->string('numero'); $t->date('inicio'); $t->date('fin'); $t->unsignedBigInteger('registrado_por'); $t->timestamps(); $t->index(['id_equipo','fin']); }); }
 public function down(): void { Schema::dropIfExists('comodatos'); }
};
