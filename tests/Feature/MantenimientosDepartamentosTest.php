<?php

namespace Tests\Feature;

use App\Livewire\MantenimientosIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MantenimientosDepartamentosTest extends TestCase
{
    public function test_filter_shows_principals_and_includes_legacy_equipment(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        foreach (['departamentos' => 'id_departamento', 'catalogos' => 'id_catalogo', 'catalogo_valores' => 'id_valor', 'tipos_equipo' => 'id_tipo_equipo', 'marcas' => 'id_marca', 'modelos' => 'id_modelo'] as $name => $key) {
            Schema::create($name, function (Blueprint $t) use ($key) {
                $t->integer($key)->primary();
                $t->string('nombre');
                $t->string('clave')->nullable();
                $t->boolean('activo')->default(true);
                $t->integer('orden')->default(0);
                if ($key === 'id_departamento') { $t->integer('id_departamento_principal')->nullable(); }
                if ($key === 'id_valor') { $t->integer('id_catalogo')->nullable(); }
            });
        }
        Schema::create('usuarios', function (Blueprint $t) {
            $t->integer('id_usuario')->primary();
            $t->string('nombres'); $t->string('apellido_paterno'); $t->string('apellido_materno')->nullable();
        });
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo')->primary();
            foreach (['codigo_inventario', 'nombre_equipo', 'numero_serie'] as $c) { $t->string($c)->nullable(); }
            foreach (['id_tipo_equipo', 'id_marca', 'id_modelo', 'id_departamento'] as $c) { $t->integer($c)->nullable(); }
        });
        Schema::create('mantenimientos', function (Blueprint $t) {
            $t->integer('id_mantenimiento')->primary();
            foreach (['folio', 'fecha_intervencion', 'intervencion_realizada'] as $c) { $t->string($c)->nullable(); }
            foreach (['id_equipo', 'id_tipo_mantenimiento', 'id_estado_mantenimiento', 'id_tipo_intervencion', 'id_tecnico'] as $c) { $t->integer($c)->nullable(); }
        });
        foreach (range(1, 5) as $id) {
            DB::table('departamentos')->insert(['id_departamento' => $id, 'nombre' => 'Spa y Recreación', 'id_departamento_principal' => $id === 1 ? null : 1]);
            DB::table('equipos')->insert(['id_equipo' => $id, 'id_departamento' => $id, 'nombre_equipo' => 'Equipo '.$id]);
            DB::table('mantenimientos')->insert(['id_mantenimiento' => $id, 'id_equipo' => $id, 'folio' => 'MNT-'.$id]);
        }
        DB::table('departamentos')->insert(['id_departamento' => 6, 'nombre' => 'Otro departamento']);
        DB::table('equipos')->insert(['id_equipo' => 6, 'id_departamento' => 6]);
        DB::table('mantenimientos')->insert(['id_mantenimiento' => 6, 'id_equipo' => 6, 'folio' => 'OTRO']);

        Livewire::test(MantenimientosIndex::class)
            ->assertViewHas('departamentos', fn ($items) => $items->count() === 2 && $items->where('nombre', 'Spa y Recreación')->count() === 1)
            ->set('areaDepartamento', 'departamento:1')->call('aplicarFiltros')
            ->assertViewHas('mantenimientos', fn ($items) => $items->total() === 5)
            ->assertDontSee('OTRO')
            ->call('limpiarFiltros')
            ->assertViewHas('mantenimientos', fn ($items) => $items->total() === 6);
    }
}
