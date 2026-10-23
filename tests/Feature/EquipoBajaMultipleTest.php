<?php

namespace Tests\Feature;

use App\Livewire\EquipoBajaMultiple;
use App\Models\ReporteGenerado;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EquipoBajaMultipleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $plantilla = \Mockery::mock(\App\Services\PlantillaInstalacionService::class)->makePartial();
        $plantilla->shouldReceive('etiquetas')->andReturn(['unidad_singular' => 'Hotel', 'buscar_unidad' => 'Buscar hotel...']);
        $this->app->instance(\App\Services\PlantillaInstalacionService::class, $plantilla);
        // Base efímera: nunca crear ni modificar equipos en la conexión real.
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', config('database.default'));
        Gate::define('gestionar', fn () => true);
        $usuario = new Usuario;
        $usuario->id_usuario = 1;
        $this->actingAs($usuario);
        Schema::create('usuarios', function (Blueprint $t) {
            $t->integer('id_usuario')->primary(); $t->string('nombres'); $t->string('apellido_paterno');
        });
        DB::table('usuarios')->insert(['id_usuario' => 1, 'nombres' => 'Usuario', 'apellido_paterno' => 'Prueba']);
        foreach (['tipos_equipo' => 'id_tipo_equipo', 'marcas' => 'id_marca', 'modelos' => 'id_modelo', 'unidades_organizativas' => 'id_unidad', 'estados_equipo' => 'id_estado_equipo'] as $tabla => $id) {
            Schema::create($tabla, function (Blueprint $t) use ($id) {
                $t->integer($id)->primary(); $t->string('nombre'); $t->string('clave')->nullable(); $t->boolean('activo')->default(true); $t->integer('orden')->default(0);
            });
        }
        Schema::create('departamentos', function (Blueprint $t) {
            $t->integer('id_departamento')->primary(); $t->string('nombre'); $t->integer('id_departamento_principal')->nullable(); $t->boolean('activo')->default(true); $t->boolean('atiende_todas_unidades')->default(true);
        });
        Schema::create('departamento_unidad', function (Blueprint $t) { $t->integer('id_departamento'); $t->integer('id_unidad'); });
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo')->primary(); $t->string('codigo_inventario'); $t->string('nombre_equipo'); $t->string('numero_serie');
            foreach (['id_estado_activo', 'id_tipo_equipo', 'id_marca', 'id_modelo', 'id_unidad'] as $campo) { $t->integer($campo)->nullable(); }
        });
        Schema::create('bajas', function (Blueprint $t) { $t->id('id_baja'); $t->integer('id_equipo'); });
        Schema::create('reportes_generados', function (Blueprint $t) {
            $t->id('id_reporte'); $t->integer('id_usuario'); $t->integer('total_registros');
            foreach (['tipo_reporte', 'formato', 'fecha_desde', 'fecha_hasta', 'filtros', 'nombre_archivo', 'ruta_archivo'] as $campo) { $t->text($campo)->nullable(); }
        });
        DB::table('estados_equipo')->insert([['id_estado_equipo' => 1, 'nombre' => 'Baja', 'clave' => 'BAJA'], ['id_estado_equipo' => 2, 'nombre' => 'Disponible', 'clave' => 'DISPONIBLE']]);
        DB::table('unidades_organizativas')->insert(['id_unidad' => 1, 'nombre' => 'Hotel de prueba']);
        DB::table('departamentos')->insert(['id_departamento' => 1, 'nombre' => 'Sistemas']);
        foreach (range(1, 12) as $id) {
            DB::table('equipos')->insert(['id_equipo' => $id, 'codigo_inventario' => sprintf('ACT-%03d', $id), 'nombre_equipo' => 'Equipo '.$id, 'numero_serie' => 'SERIE-'.$id, 'id_estado_activo' => $id === 12 ? 2 : 1, 'id_unidad' => 1]);
            if ($id !== 12) { DB::table('bajas')->insert(['id_equipo' => $id]); }
        }
        Storage::fake('local');
    }

    public function test_only_previously_decommissioned_equipment_can_continue(): void
    {
        Livewire::test(EquipoBajaMultiple::class)->call('siguiente')->assertHasErrors('seleccionados')
            ->set('seleccionados', ['12'])->call('siguiente')->assertHasErrors('seleccionados')->assertSet('detalles', false)
            ->set('seleccionados', ['1', '2'])->call('siguiente')->assertHasNoErrors()->assertSet('detalles', true)->assertSee('Vista rápida del documento final');
    }

    public function test_selection_limit_and_selection_across_searches(): void
    {
        Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', array_map('strval', range(1, 11)))
            ->assertHasErrors('seleccionados')->assertSet('seleccionados', array_map('strval', range(1, 10)))
            ->set('buscar', 'SERIE-11')->assertSee('SERIE-11')->assertSet('seleccionados', array_map('strval', range(1, 10)))
            ->call('siguiente')->assertHasNoErrors()->assertSet('detalles', true);
    }

    public function test_select_page_and_deselect_page(): void
    {
        Livewire::test(EquipoBajaMultiple::class)->call('seleccionarPagina')->assertSet('seleccionados', ['1', '10', '11', '2', '3', '4', '5', '6', '7', '8'])
            ->call('seleccionarPagina')->assertSet('seleccionados', []);
    }

    public function test_state_is_checked_again_when_saving(): void
    {
        $component = Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', ['1'])->call('siguiente');
        DB::table('equipos')->where('id_equipo', 1)->update(['id_estado_activo' => 2]);
        $component->call('guardar')->assertHasErrors('seleccionados');
        $this->assertSame(0, ReporteGenerado::count());
    }

    public function test_saves_pdf_evidence_and_report_without_duplicate_bajas(): void
    {
        $component = Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', ['1', '2'])->call('siguiente')
            ->set('solicitante', 'Solicitante de prueba')->set('departamentoId', '1')->set('motivoBaja', 'Fin de vida útil')
            ->set('observaciones', '')->set('autoriza', 'Responsable de prueba')->set('puesto', 'Sistemas')
            ->set('fotoFrontal', UploadedFile::fake()->image('frontal.jpg'))->call('guardar')->assertHasNoErrors()->assertRedirect(route('equipos.index'));
        $this->assertSame(ReporteGenerado::first()->getKey(), session('descargar_reporte'));
        $reporte = ReporteGenerado::firstOrFail();
        $this->assertSame(2, $reporte->total_registros);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($reporte->ruta_archivo));
        $descarga = app(\App\Http\Controllers\ReporteController::class)->download($reporte);
        $this->assertSame('application/pdf', $descarga->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $descarga->headers->get('Content-Disposition'));
        Storage::disk('local')->assertExists($reporte->filtros['baja_multiple']['fotos']['fotoFrontal']);
        $this->assertSame(11, DB::table('bajas')->count());
        $this->assertSame(1, DB::table('equipos')->where('id_equipo', 1)->value('id_estado_activo'));
        $component->call('guardar');
        $this->assertSame(1, ReporteGenerado::count());
    }

    public function test_rejects_invalid_photos_and_missing_fields(): void
    {
        Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', ['1'])->call('siguiente')->call('guardar')
            ->assertHasErrors(['solicitante', 'departamentoId', 'motivoBaja', 'autoriza', 'puesto'])
            ->set('fotoFrontal', UploadedFile::fake()->create('archivo.pdf', 20, 'application/pdf'))->assertHasErrors('fotoFrontal');
    }

    public function test_read_only_users_cannot_open_component(): void
    {
        Gate::define('gestionar', fn () => false);
        Livewire::test(EquipoBajaMultiple::class)->assertForbidden();
    }

    public function test_shared_table_controls_keep_selection_and_limit_large_pages(): void
    {
        Livewire::test(EquipoBajaMultiple::class)
            ->set('perPage', 25)->call('seleccionarPagina')
            ->assertSet('seleccionados', ['1', '10', '11', '2', '3', '4', '5', '6', '7', '8'])
            ->call('seleccionarPagina')->assertSet('seleccionados', [])
            ->set('seleccionados', ['2'])->set('sort', 'desc')->call('sortBy', 'hotel')
            ->assertSet('seleccionados', ['2'])->assertSet('sortField', 'hotel')
            ->call('sortBy', 'invalid')->assertSet('sortField', 'hotel')
            ->assertSee('Por página')->assertDontSee('>Inventario</th>', false);
    }

    public function test_uploaded_photos_replace_the_selectors_and_can_be_removed(): void
    {
        Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', ['1'])->call('siguiente')
            ->set('fotoFrontal', UploadedFile::fake()->image('frontal.jpg'))
            ->set('fotoTrasera', UploadedFile::fake()->image('trasera.jpg'))
            ->assertDontSee('Seleccionar fotografía')->assertDontSee('Máx. 10 equipos')
            ->assertDontSee('← Volver')->assertSee('Generar PDF')
            ->call('eliminarFoto', 'fotoFrontal')->assertSee('Seleccionar fotografía');
    }

    public function test_preview_reads_current_organization_logo(): void
    {
        $marca = \Mockery::mock(\App\Services\OrganizacionService::class)->makePartial();
        $marca->shouldReceive('logoUrl')->andReturn('https://example.test/logo-v2.png');
        $marca->shouldReceive('nombre')->andReturn('Organización de prueba');
        $this->app->instance(\App\Services\OrganizacionService::class, $marca);
        Livewire::test(EquipoBajaMultiple::class)->set('seleccionados', ['1'])->call('siguiente')
            ->assertSee('https://example.test/logo-v2.png', false)->assertSee('Organización de prueba');
    }

    public function test_inventory_filters_show_configured_fields_and_reset_on_type_change(): void
    {
        Schema::table('equipos', fn (Blueprint $t) => $t->string('service_tag')->nullable());
        Schema::create('campos_equipo', function (Blueprint $t) {
            $t->integer('id_campo'); $t->string('nombre'); $t->string('tipo_control'); $t->boolean('activo');
        });
        Schema::create('tipo_equipo_secciones', function (Blueprint $t) {
            $t->integer('id_seccion'); $t->string('clave'); $t->integer('orden');
        });
        Schema::create('tipo_equipo_campos', function (Blueprint $t) {
            foreach (['id_tipo_campo', 'id_tipo_equipo', 'id_campo', 'id_seccion', 'orden'] as $campo) { $t->integer($campo); }
            $t->boolean('visible'); $t->integer('id_catalogo')->nullable();
            foreach (['origen_dato', 'tabla_destino', 'columna_destino', 'etiqueta', 'fuente_opciones'] as $campo) { $t->string($campo)->nullable(); }
        });
        DB::table('tipos_equipo')->insert([['id_tipo_equipo' => 1, 'nombre' => 'Portátil'], ['id_tipo_equipo' => 2, 'nombre' => 'Monitor']]);
        DB::table('campos_equipo')->insert(['id_campo' => 1, 'nombre' => 'Etiqueta especial', 'tipo_control' => 'select', 'activo' => true]);
        DB::table('tipo_equipo_secciones')->insert(['id_seccion' => 1, 'clave' => 'tecnica', 'orden' => 1]);
        DB::table('tipo_equipo_campos')->insert(['id_tipo_campo' => 10, 'id_tipo_equipo' => 1, 'id_campo' => 1, 'id_seccion' => 1, 'orden' => 1, 'visible' => true, 'origen_dato' => 'equipos', 'tabla_destino' => 'equipos', 'columna_destino' => 'service_tag']);
        DB::table('equipos')->whereIn('id_equipo', [1, 2])->update(['id_tipo_equipo' => 1]);
        DB::table('equipos')->where('id_equipo', 1)->update(['service_tag' => 'etiqueta-1']);
        DB::table('equipos')->where('id_equipo', 3)->update(['id_tipo_equipo' => 2]);

        Livewire::test(EquipoBajaMultiple::class)->assertSee('Filtros de búsqueda')->assertSee('Número de factura')
            ->assertSee('Aplicar filtros')->assertSee('Limpiar filtros')
            ->set('seleccionados', ['1'])->set('tipoActivo', '1')->assertSee('Etiqueta especial')
            ->set('camposTipo.10', 'etiqueta-1')->assertSee('SERIE-1')->assertDontSee('SERIE-2')
            ->set('tipoActivo', '2')->assertSet('camposTipo', [])->assertDontSee('Etiqueta especial')->assertSee('SERIE-3')
            ->call('resetFilters')->assertSet('tipoActivo', null)->assertSet('seleccionados', ['1'])->assertSee('SERIE-2');
    }
}

