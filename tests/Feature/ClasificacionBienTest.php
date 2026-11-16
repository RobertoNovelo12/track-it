<?php

namespace Tests\Feature;

use App\Services\ClasificacionBienService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClasificacionBienTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('tipos_equipo', function (Blueprint $t) {
            $t->integer('id_tipo_equipo'); $t->string('clasificacion_bien');
        });
        Schema::create('campos_equipo', function (Blueprint $t) {
            $t->increments('id_campo'); $t->string('clave')->unique(); $t->string('nombre');
            $t->string('tipo_control'); $t->boolean('es_sistema'); $t->boolean('activo');
            $t->string('descripcion')->nullable(); $t->string('placeholder')->nullable();
        });
        Schema::create('tipo_equipo_campos', function (Blueprint $t) {
            $t->increments('id_tipo_campo'); $t->integer('id_tipo_equipo'); $t->integer('id_campo');
            foreach (['etiqueta', 'origen_dato', 'tabla_destino', 'columna_destino', 'fuente_opciones', 'seccion'] as $col) { $t->string($col)->nullable(); }
            $t->integer('id_seccion'); $t->integer('orden'); $t->boolean('visible'); $t->boolean('obligatorio');
            $t->integer('id_catalogo')->nullable();
        });
        Schema::create('tipo_equipo_secciones', function (Blueprint $t) {
            $t->integer('id_seccion'); $t->integer('id_tipo_equipo'); $t->string('clave');
            $t->string('nombre'); $t->integer('orden'); $t->boolean('es_base');
        });
    }

    public function test_campos_se_reutilizan_y_conservan_etiquetas_y_configuracion(): void
    {
        $s = new ClasificacionBienService;
        $secciones = ['general' => 1, 'informacion_tipo' => 2];
        $s->completarCampos(1, 'global', $secciones);
        $campo = DB::table('campos_equipo')->where('clave', 'institucion_total')->first();
        DB::table('tipo_equipo_campos')->where('id_campo', $campo->id_campo)->update(['etiqueta' => 'Importe registrado', 'obligatorio' => true]);
        $s->completarCampos(1, 'global', $secciones);
        $this->assertSame(8, DB::table('campos_equipo')->count());
        $this->assertSame(8, DB::table('tipo_equipo_campos')->count());
        $relacion = DB::table('tipo_equipo_campos')->where('id_campo', $campo->id_campo)->first();
        $this->assertSame('Importe registrado', $relacion->etiqueta);
        $this->assertTrue((bool) $relacion->obligatorio);
        $this->assertSame('institucion_total', DB::table('campos_equipo')->where('id_campo', $campo->id_campo)->value('clave'));
        $s->completarCampos(1, 'inmuebles', $secciones, true);
        $this->assertSame(5, DB::table('tipo_equipo_campos')->where('visible', true)->count());
        $this->assertSame(13, DB::table('tipo_equipo_campos')->count());
    }

    public function test_rechaza_un_tipo_de_otra_clasificacion(): void
    {
        DB::table('tipos_equipo')->insert(['id_tipo_equipo' => 1, 'clasificacion_bien' => 'vehiculos']);
        (new ClasificacionBienService)->validar('vehiculos', 1);
        $this->expectException(ValidationException::class);
        (new ClasificacionBienService)->validar('global', 1);
    }

    public function test_exige_clasificacion(): void
    {
        $this->expectException(ValidationException::class);
        (new ClasificacionBienService)->validar('', 1);
    }

    public function test_inmueble_no_necesita_otro_edificio(): void
    {
        DB::table('tipos_equipo')->insert(['id_tipo_equipo' => 20, 'clasificacion_bien' => 'inmuebles']);
        $s = new ClasificacionBienService;
        $this->assertSame(['id_unidad' => null, 'id_departamento' => null, 'id_ubicacion' => null], $s->inmuebleSinUnidad(['idTipoEquipo' => 20, 'idHotel' => '', 'idDepartamento' => '', 'idUbicacion' => '']));
        $this->assertNull($s->inmuebleSinUnidad(['idTipoEquipo' => 20, 'idHotel' => 1]));
        $this->assertFalse($s->esInmueble(99));
        $reporte = new \App\Services\InventarioGlobalReporteService;
        $this->assertSame('Edificio A · Biblioteca', $reporte->celda(['descripcion' => 'Edificio A', 'uso_destino' => 'Biblioteca'], 'descripcion_uso'));
    }

    public function test_marca_debe_corresponder_al_tipo_y_conserva_la_marca_historica(): void
    {
        Schema::create('modelos', function (Blueprint $t) {
            $t->integer('id_tipo_equipo'); $t->integer('id_marca'); $t->boolean('activo');
        });
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo'); $t->integer('id_marca');
        });
        DB::table('modelos')->insert(['id_tipo_equipo' => 2, 'id_marca' => 10, 'activo' => true]);
        DB::table('equipos')->insert(['id_equipo' => 1, 'id_marca' => 20]);
        $s = new ClasificacionBienService;
        $s->validarMarca(2, 10);
        $s->validarMarca(2, null);
        $s->validarMarca(2, 20, 1);
        $this->expectException(ValidationException::class);
        $s->validarMarca(2, 20);
    }

    public function test_alta_y_edicion_leen_los_mismos_campos_y_validan_sus_valores(): void
    {
        (new ClasificacionBienService)->completarCampos(20, 'vehiculos', ['general' => 1, 'informacion_tipo' => 2]);
        $create = new \App\Livewire\EquipoCreate;
        $edit = new \App\Livewire\EquipoEdit;
        $camposAlta = (new \ReflectionMethod($create, 'configuredChildFields'))->invoke($create, 20);
        $camposEdicion = (new \ReflectionMethod($edit, 'configuredChildFields'))->invoke($edit, 20);
        $this->assertSame(array_keys($camposAlta), array_keys($camposEdicion));
        $this->assertCount(4, $camposAlta);
        $datos = [];
        foreach ($camposAlta as $key => $campo) { $datos[$key] = $campo['tipo_control'] === 'number' ? '2024' : 'Valor registrado'; }
        foreach ([$create, $edit] as $componente) {
            $this->assertSame($datos, (new \ReflectionMethod($componente, 'validatedChildData'))->invoke($componente, $datos, $camposAlta));
        }
        DB::table('tipo_equipo_campos')->update(['etiqueta' => 'Etiqueta modificada']);
        $otro = new \App\Livewire\EquipoEdit;
        $this->assertSame(array_keys($camposAlta), array_keys((new \ReflectionMethod($otro, 'configuredChildFields'))->invoke($otro, 20)));
    }
}
