<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bajas', fn (Blueprint $table) => $table->json('firmas')->nullable());
    }

    public function down(): void
    {
        Schema::table('bajas', fn (Blueprint $table) => $table->dropColumn('firmas'));
    }
};
