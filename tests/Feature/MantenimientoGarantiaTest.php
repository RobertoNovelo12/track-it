<?php

namespace Tests\Feature;

use App\Exports\ReporteInventarioExport;
use App\Livewire\MantenimientoCreate;
use App\Livewire\MantenimientoEdit;
use App\Models\Mantenimiento;
use App\Services\NotificacionService;
use App\Services\ReporteDataService;
use App\Services\ReporteMantenimientoActaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MantenimientoGarantiaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        \Illuminate\Support\Facades\Gate::define('gestionar', fn () => true);
        $usuario = new \App\Models\User;
        $usuario->id_usuario = 1;
        $this->actingAs($usuario);
        foreach (['tipos_equipo' => 'id_tipo_equipo', 'marcas' => 'id_marca', 'modelos' => 'id_modelo', 'proveedores' => 'id_proveedor', 'estados_equipo' => 'id_estado_equipo', 'catalogos' => 'id_catalogo', 'catalogo_valores' => 'id_valor'] as $tabla => $id) {
            Schema::create($tabla, function (Blueprint $t) use ($id) {
                $t->integer($id)->primary(); $t->string('nombre')->nullable(); $t->string('clave')->nullable();
                $t->boolean('activo')->default(true); $t->integer('orden')->default(0);
                if ($id === 'id_valor') { $t->integer('id_catalogo'); }
            });
        }
        Schema::create('usuarios', function (Blueprint $t) {
            $t->integer('id_usuario')->primary(); $t->string('nombres'); $t->string('apellido_paterno')->nullable(); $t->string('apellido_materno')->nullable();
        });
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo')->primary();
            foreach (['codigo_inventario', 'nombre_equipo', 'numero_serie', 'host', 'fecha_fin_garantia'] as $c) { $t->string($c)->nullable(); }
            foreach (['id_tipo_equipo', 'id_marca', 'id_modelo', 'id_estado_activo'] as $c) { $t->integer($c)->nullable(); }
        });
        Schema::create('bajas', fn (Blueprint $t) => $t->integer('id_equipo'));
        Schema::create('mantenimientos', function (Blueprint $t) {
            $t->id('id_mantenimiento');
            foreach (['id_equipo', 'id_tipo_mantenimiento', 'id_tipo_intervencion', 'id_estado_mantenimiento', 'id_prioridad', 'id_tecnico', 'id_proveedor'] as $c) { $t->integer($c)->nullable(); }
            foreach (['folio', 'fecha_intervencion', 'fecha_fin', 'hora_inicio', 'hora_fin', 'falla_reportada', 'diagnostico', 'intervencion_realizada', 'observaciones', 'fecha_proximo_mantenimiento', 'fecha_registro'] as $c) { $t->text($c)->nullable(); }
        });
        Schema::create('mantenimiento_refacciones', function (Blueprint $t) {
            $t->id('id_mantenimiento_refaccion'); $t->integer('id_mantenimiento');
            foreach (['nombre_refaccion', 'codigo_parte', 'cantidad', 'unidad'] as $c) { $t->text($c)->nullable(); }
        });
        DB::table('catalogos')->insert(['id_catalogo' => 1, 'clave' => 'estado_mantenimiento']);
        DB::table('catalogo_valores')->insert([
            ['id_valor' => 1, 'id_catalogo' => 1, 'clave' => 'EN_GARANTIA', 'nombre' => 'En garantía'],
            ['id_valor' => 2, 'id_catalogo' => 1, 'clave' => 'COMPLETADO', 'nombre' => 'Completado'],
        ]);
        DB::table('usuarios')->insert(['id_usuario' => 1, 'nombres' => 'Técnico']);
        DB::table('proveedores')->insert(['id_proveedor' => 1, 'nombre' => 'Servicio Técnico Externo']);
        DB::table('equipos')->insert(['id_equipo' => 1, 'codigo_inventario' => 'ACT-1', 'nombre_equipo' => 'Equipo actual', 'host' => 'HOST-ACTUAL', 'fecha_fin_garantia' => '2027-05-20']);
        $notificaciones = \Mockery::mock(NotificacionService::class);
        $notificaciones->shouldReceive('nombreActor')->andReturn('Usuario de prueba');
        $notificaciones->shouldReceive('nombreCatalogo')->andReturn('En garantía');
        $notificaciones->shouldReceive('etiquetaEquipo')->andReturn('Equipo de prueba');
        $notificaciones->shouldReceive('notificar')->andReturn(0);
        $this->app->instance(NotificacionService::class, $notificaciones);
    }

    private function crear(string $estado = '1', string $proveedor = '1'): int
    {
        $component = new MantenimientoCreate;
        $component->equipoId = '1';
        $component->tipoMantenimiento = '1';
        $component->estadoMantenimiento = $estado;
        $component->tecnicoId = '1';
        $component->proveedorId = $proveedor;
        $component->fechaIntervencion = '2026-10-27';
        $component->guardar();

        return (int) DB::table('mantenimientos')->max('id_mantenimiento');
    }

    public function test_create_and_edit_save_the_guarantee_provider(): void
    {
        $id = $this->crear();
        $this->assertDatabaseHas('mantenimientos', ['id_mantenimiento' => $id, 'id_proveedor' => 1]);
        $this->assertSame('Servicio Técnico Externo', Mantenimiento::findOrFail($id)->proveedor->nombre);
        $edit = new MantenimientoEdit;
        $edit->mount($id);
        $this->assertSame('1', $edit->proveedorId);
        $edit->estadoMantenimiento = '2';
        $edit->updatedEstadoMantenimiento();
        $this->assertSame('', $edit->proveedorId);
        $edit->guardar();
        $this->assertDatabaseHas('mantenimientos', ['id_mantenimiento' => $id, 'id_proveedor' => null, 'id_estado_mantenimiento' => 2]);
    }

    public function test_provider_is_optional_and_not_saved_outside_guarantee(): void
    {
        $id = $this->crear('1', '');
        $this->assertDatabaseHas('mantenimientos', ['id_mantenimiento' => $id, 'id_proveedor' => null]);
        $id = $this->crear('2');
        $this->assertDatabaseHas('mantenimientos', ['id_mantenimiento' => $id, 'id_proveedor' => null]);
    }

    public function test_clearing_state_does_not_query_an_empty_integer(): void
    {
        foreach ([new MantenimientoCreate, new MantenimientoEdit] as $component) {
            $component->proveedorId = '1';
            DB::enableQueryLog();
            DB::flushQueryLog();
            $component->updatedEstadoMantenimiento();
            $this->assertSame([], DB::getQueryLog());
            $this->assertSame('', $component->proveedorId);
            DB::disableQueryLog();
        }
    }

    public function test_reports_include_provider_state_and_equipment_warranty(): void
    {
        $this->crear();
        $m = app(ReporteMantenimientoActaService::class)->obtener()->sole();
        $this->assertSame('Servicio Técnico Externo', $m['proveedor']);
        $this->assertSame('En garantía', $m['estado']);
        $this->assertSame('20/05/2027', $m['fin_garantia_equipo']);
        $this->assertSame('HOST-ACTUAL', $m['equipo']['host']);
        foreach (['reportes.mantenimiento-previa' => ['m' => $m], 'reportes.mantenimiento-pdf' => ['mantenimientos' => collect([$m])]] as $view => $data) {
            $html = view($view, $data)->render();
            foreach (['Proveedor externo', 'Servicio Técnico Externo', 'En garantía', '20/05/2027'] as $texto) { $this->assertStringContainsString($texto, $html); }
        }
        $pdf = Pdf::loadView('reportes.mantenimiento-pdf', ['mantenimientos' => collect([$m])]);
        $this->assertStringStartsWith('%PDF-', $pdf->output());
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        $fila = app(ReporteDataService::class)->query('mantenimientos', [])->sole();
        $export = new ReporteInventarioExport(collect([$fila]));
        $this->assertStringContainsString('Proveedor: Servicio Técnico Externo', $export->map($fila)[9]);
        $this->assertStringContainsString('2027-05-20', $export->map($fila)[9]);
        $this->assertSame('En garantía', $export->map($fila)[7]);
    }
}
