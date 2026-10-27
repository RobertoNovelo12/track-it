<?php

namespace Tests\Feature;

use App\Livewire\AsignacionesIndex;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Services\NotificacionService;
use App\Services\ReporteActasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizacionHotelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Gate::define('gestionar', fn () => true);
        $usuario = new Usuario;
        $usuario->id_usuario = 1;
        $this->actingAs($usuario);

        Schema::create('unidades_organizativas', function (Blueprint $t) {
            $t->integer('id_unidad')->primary(); $t->integer('id_sede'); $t->string('nombre'); $t->boolean('activo')->default(true);
        });
        Schema::create('sedes', function (Blueprint $t) { $t->integer('id_sede')->primary(); $t->string('nombre'); });
        Schema::create('departamentos', function (Blueprint $t) {
            $t->integer('id_departamento')->primary(); $t->string('nombre'); $t->integer('id_departamento_principal')->nullable();
            $t->boolean('activo')->default(true); $t->boolean('atiende_todas_unidades')->default(false);
        });
        Schema::create('departamento_unidad', function (Blueprint $t) { $t->integer('id_departamento'); $t->integer('id_unidad'); });
        Schema::create('ubicaciones', function (Blueprint $t) {
            $t->integer('id_ubicacion')->primary(); $t->integer('id_unidad'); $t->integer('id_sede'); $t->string('nombre');
        });
        Schema::create('catalogo_valores', function (Blueprint $t) { $t->integer('id_valor')->primary(); $t->string('nombre'); });
        Schema::create('estados_equipo', function (Blueprint $t) { $t->integer('id_estado_equipo')->primary(); $t->string('clave'); });
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo')->primary(); $t->integer('id_unidad'); $t->integer('id_departamento')->nullable(); $t->integer('id_estado_activo')->nullable();
        });
        Schema::create('bajas', function (Blueprint $t) { $t->integer('id_equipo'); });
        Schema::create('asignaciones', function (Blueprint $t) {
            $t->id('id_asignacion'); $t->integer('id_equipo'); $t->integer('id_unidad'); $t->integer('id_departamento')->nullable();
            $t->integer('id_ubicacion')->nullable(); $t->integer('id_tipo_asignacion'); $t->integer('asignado_por');
            $t->string('nombre_colaborador'); $t->string('numero_colaborador')->nullable(); $t->text('observaciones')->nullable();
            $t->timestamp('fecha_asignacion'); $t->timestamp('fecha_fin')->nullable();
        });
        DB::table('sedes')->insert(['id_sede' => 1, 'nombre' => 'Sede']);
        DB::table('unidades_organizativas')->insert(['id_unidad' => 7, 'id_sede' => 1, 'nombre' => 'Servicios Compartidos']);
        DB::table('departamentos')->insert(['id_departamento' => 1, 'nombre' => 'Sistemas']);
        DB::table('departamento_unidad')->insert(['id_departamento' => 1, 'id_unidad' => 7]);
        DB::table('catalogo_valores')->insert(['id_valor' => 1, 'nombre' => 'Personal']);
        DB::table('equipos')->insert(['id_equipo' => 1, 'id_unidad' => 7]);
        $notificaciones = \Mockery::mock(NotificacionService::class);
        $notificaciones->shouldReceive('nombreActor')->andReturn('Usuario de prueba');
        $notificaciones->shouldReceive('etiquetaEquipo')->andReturn('Equipo de prueba');
        $notificaciones->shouldReceive('notificar')->andReturn(0);
        $this->app->instance(NotificacionService::class, $notificaciones);
    }

    private function asignar(?int $departamento = null): void
    {
        $component = new AsignacionesIndex;
        $component->idEquipoAsignar = '1';
        $component->nombreColaborador = 'Colaborador de prueba';
        $component->idUnidadAsignar = '7';
        $component->idDepartamentoAsignar = $departamento ? (string) $departamento : '';
        $component->idTipoAsignacion = '1';
        $component->guardarAsignacion();
    }

    public function test_assignment_with_only_hotel_and_no_area_schema(): void
    {
        $this->asignar();
        $this->assertDatabaseHas('asignaciones', ['id_equipo' => 1, 'id_unidad' => 7, 'id_departamento' => null]);
        $this->assertFalse(Schema::hasTable('areas'));
    }

    public function test_department_keeps_hotel_and_pdf_reads_its_name(): void
    {
        $this->asignar(1);
        $this->assertDatabaseHas('asignaciones', ['id_unidad' => 7, 'id_departamento' => 1]);
        $method = new \ReflectionMethod(ReporteActasService::class, 'responsable');
        $datos = $method->invoke(app(ReporteActasService::class), 1);
        $this->assertSame('Servicios Compartidos', $datos['area']);
        $this->assertSame('Sistemas', $datos['departamento']);
        $this->assertSame('Sede', $datos['sede']);
    }

    public function test_equipment_location_uses_hotel_without_area_relations(): void
    {
        $equipo = Equipo::findOrFail(1);
        $this->assertSame('Servicios Compartidos', $equipo->ubicacion_organizacional);
        $equipo->id_departamento = 1;
        $equipo->unsetRelation('departamento');
        $this->assertSame('Servicios Compartidos / Sistemas', $equipo->ubicacion_organizacional);
    }
}
