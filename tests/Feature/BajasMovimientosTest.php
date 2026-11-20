<?php

namespace Tests\Feature;

use App\Livewire\BajasIndex;
use App\Services\BajaEquipoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BajasMovimientosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Gate::define('gestionar', fn ($user = null) => true);
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo'); $t->integer('id_tipo_equipo'); $t->string('nombre_equipo'); $t->string('codigo_inventario'); $t->string('numero_serie')->nullable();
        });
        Schema::create('tipos_equipo', function (Blueprint $t) { $t->integer('id_tipo_equipo'); $t->string('nombre'); });
        Schema::create('bajas_resguardo', function (Blueprint $t) {
            $t->integer('id_equipo'); $t->date('fecha_resguardo'); $t->timestamp('fecha_completada')->nullable(); $t->string('motivo'); $t->string('observaciones')->nullable();
        });
        Schema::create('bajas', function (Blueprint $t) { $t->integer('id_equipo'); $t->date('fecha_baja'); $t->string('motivo_baja'); });
        DB::table('tipos_equipo')->insert(['id_tipo_equipo' => 1, 'nombre' => 'Laptop']);
        foreach (range(1, 25) as $id) {
            DB::table('equipos')->insert(['id_equipo' => $id, 'id_tipo_equipo' => 1, 'nombre_equipo' => 'Laptop '.$id, 'codigo_inventario' => 'ACT-'.$id]);
            DB::table('bajas_resguardo')->insert(['id_equipo' => $id, 'fecha_resguardo' => $id === 25 ? '2024-03-01' : '2024-02-29', 'motivo' => 'Daño físico']);
        }
    }

    public function test_selecciona_todos_los_resultados_del_mes_incluido_bisiesto_y_no_solo_la_pagina(): void
    {
        $c = new BajasIndex;
        $c->inicio = $c->fin = '2024-02';
        $c->seleccionarTodos();
        $this->assertCount(24, $c->seleccionados);
        $this->assertNotContains('25', $c->seleccionados);
        $html = $c->render()->with(get_object_vars($c))->with('errors', new \Illuminate\Support\ViewErrorBag)->render();
        $this->assertStringContainsString('24 resultados', $html);
        $this->assertStringContainsString('Finalizar bajas', $html);
        $c->updated('inicio');
        $this->assertSame([], $c->seleccionados);
    }

    public function test_semana_incluye_sus_dias_y_separa_completadas(): void
    {
        DB::table('bajas')->insert(['id_equipo' => 1, 'fecha_baja' => '2024-03-01', 'motivo_baja' => 'Daño físico']);
        $c = new BajasIndex;
        $c->periodo = 'semana'; $c->inicio = $c->fin = '2024-W09';
        $c->seleccionarTodos();
        $this->assertCount(24, $c->seleccionados);
        $this->assertNotContains('1', $c->seleccionados);
        $c->estado = 'completadas';
        $query = (new \ReflectionMethod($c, 'consulta'))->invoke($c);
        $this->assertSame([1], $query->pluck('e.id_equipo')->all());
    }

    public function test_rechaza_rango_invertido(): void
    {
        $c = new BajasIndex; $c->inicio = '2024-03'; $c->fin = '2024-02';
        $this->expectException(ValidationException::class);
        $c->seleccionarTodos();
    }

    public function test_rechaza_semana_inexistente(): void
    {
        $c = new BajasIndex; $c->periodo = 'semana'; $c->inicio = '2024-W54';
        $this->expectException(ValidationException::class);
        $c->seleccionarTodos();
    }

    public function test_revalida_la_seleccion_antes_de_completar(): void
    {
        $c = new BajasIndex; $c->seleccionados = ['999']; $c->autoriza = 'María Elena López Pérez'; $c->fechaBaja = '2024-03-02';
        $this->expectException(ValidationException::class);
        $c->iniciarFinalizacion();
    }

    public function test_finaliza_por_lotes_reutilizando_el_servicio_y_el_motivo_original(): void
    {
        $service = $this->mock(BajaEquipoService::class);
        $service->shouldReceive('darDeBaja')->once()->with(1, 'Daño físico', '2024-03-02', null, 'María Elena López Pérez', 0)->andReturn('BAJA-1');
        $service->shouldReceive('despuesDeBajas')->once()->with([1]);
        $c = new BajasIndex; $c->seleccionados = ['1']; $c->autoriza = 'María Elena López Pérez'; $c->fechaBaja = '2024-03-02';
        $c->iniciarFinalizacion();
        $this->assertTrue($c->procesando);
        $c->procesarSiguiente();
        $this->assertSame(1, $c->completadas);
        $this->assertFalse($c->procesando);
        $this->assertSame([], $c->seleccionados);
    }

    public function test_modulo_mantenimiento_ya_no_tiene_rutas_ni_tipo_de_reporte(): void
    {
        $this->assertFalse(app('router')->has('mantenimientos.index'));
        $this->assertTrue(app('router')->has('asignaciones.bajas'));
        $this->assertNotContains('mantenimientos', \App\Services\ReporteDataService::TIPOS);
    }
}
