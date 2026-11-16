<?php

namespace Tests\Feature;

use App\Services\BajaEquipoService;
use App\Services\BajaResguardoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BajaResguardoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('equipos', function (Blueprint $t) { $t->integer('id_equipo')->primary(); $t->string('codigo_inventario'); $t->integer('id_estado_activo'); });
        Schema::create('estados_equipo', function (Blueprint $t) { $t->integer('id_estado_equipo'); $t->string('clave'); $t->boolean('activo')->default(true); });
        Schema::create('catalogos', function (Blueprint $t) { $t->integer('id_catalogo'); $t->string('clave'); });
        Schema::create('catalogo_valores', function (Blueprint $t) { $t->integer('id_valor'); $t->integer('id_catalogo'); $t->string('clave'); $t->boolean('activo')->default(true); });
        Schema::create('bajas', function (Blueprint $t) {
            $t->id('id_baja');
            foreach (['id_equipo','id_motivo_baja','id_estado_baja','realizado_por'] as $campo) { $t->integer($campo); }
            foreach (['folio','numero_ticket','motivo_baja','fecha_baja','comentarios','autoriza','firmas','ruta_pdf','fecha_registro'] as $campo) { $t->text($campo)->nullable(); }
        });
        Schema::create('asignaciones', function (Blueprint $t) { $t->integer('id_equipo'); $t->timestamp('fecha_fin')->nullable(); });
        Schema::create('bitacora_auditoria', function (Blueprint $t) { $t->integer('id_usuario'); foreach (['accion','modulo','descripcion','fecha_hora'] as $c) { $t->text($c); } });
        (require database_path('migrations/2026_11_11_000100_create_bajas_resguardo.php'))->up();
        DB::table('equipos')->insert(['id_equipo'=>1,'codigo_inventario'=>'ACT-1','id_estado_activo'=>1]);
        DB::table('estados_equipo')->insert([['id_estado_equipo'=>1,'clave'=>'DISPONIBLE'],['id_estado_equipo'=>2,'clave'=>'BAJA']]);
        DB::table('catalogos')->insert([['id_catalogo'=>1,'clave'=>'motivo_baja'],['id_catalogo'=>2,'clave'=>'estado_baja']]);
        DB::table('catalogo_valores')->insert([['id_valor'=>1,'id_catalogo'=>1,'clave'=>'OTRO'],['id_valor'=>2,'id_catalogo'=>2,'clave'=>'COMPLETADA']]);
        DB::table('asignaciones')->insert(['id_equipo'=>1]);
        Storage::fake('local');
    }

    private function resguardar(): void { app(BajaResguardoService::class)->guardar(1, '2026-11-11', 'Revisar daños', 'En almacén', 1); }
    private function completar(): void { DB::transaction(fn () => app(BajaEquipoService::class)->darDeBaja(1, 'Irreparable', '2026-11-11', null, 'Responsable', 1)); }

    public function test_resguardo_conserva_equipo_y_responsable_y_completar_cierra_la_baja(): void
    {
        $this->resguardar();
        $this->assertDatabaseCount('bajas', 0);
        $this->assertDatabaseHas('equipos', ['id_equipo'=>1, 'id_estado_activo'=>1]);
        $this->assertDatabaseHas('asignaciones', ['id_equipo'=>1,'fecha_fin'=>null]);
        $this->assertNotNull(app(BajaResguardoService::class)->pendiente(1));
        Storage::disk('local')->put('reportes/resguardo-anterior.pdf', 'reporte guardado');
        $this->completar();
        $this->assertDatabaseCount('equipos', 1);
        $this->assertDatabaseHas('equipos', ['id_equipo'=>1,'id_estado_activo'=>2]);
        $this->assertDatabaseCount('bajas', 1);
        $this->assertNull(app(BajaResguardoService::class)->pendiente(1));
        $this->assertNotNull(DB::table('asignaciones')->value('fecha_fin'));
        Storage::disk('local')->assertExists('reportes/resguardo-anterior.pdf');
    }

    public function test_no_permite_resguardo_duplicado(): void
    {
        $this->resguardar();
        $this->expectException(ValidationException::class);
        $this->resguardar();
    }

    public function test_no_permite_regresar_a_resguardo_tras_completar(): void
    {
        $this->completar();
        $this->expectException(ValidationException::class);
        $this->resguardar();
    }

    public function test_si_falla_la_transaccion_el_equipo_sigue_en_resguardo(): void
    {
        $this->resguardar();
        try {
            DB::transaction(function () { $this->completar(); throw new \RuntimeException('Fallo al generar acta'); });
        } catch (\RuntimeException $e) { $this->assertSame('Fallo al generar acta', $e->getMessage()); }
        $this->assertDatabaseCount('bajas', 0);
        $this->assertNotNull(app(BajaResguardoService::class)->pendiente(1));
        $this->assertDatabaseHas('equipos', ['id_equipo'=>1, 'id_estado_activo'=>1]);
    }
}
