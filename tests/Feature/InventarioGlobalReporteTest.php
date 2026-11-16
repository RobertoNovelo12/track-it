<?php

namespace Tests\Feature;

use App\Services\InventarioGlobalReporteService;
use App\Exports\InventarioGlobalReporteExport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventarioGlobalReporteTest extends TestCase
{
    public function test_livewire_puede_validar_el_formato_y_leer_las_reglas_del_componente(): void
    {
        $componente = new \App\Livewire\ReportesIndex;
        $componente->formatoInventario = 'global';
        $resultado = $componente->validate(['formatoInventario' => ['required', 'in:global,intangibles,inmuebles,vehiculos']]);
        $this->assertSame('global', $resultado['formatoInventario']);
        $this->assertArrayHasKey('fechaInicio', $componente->getRules());
    }

    public function test_livewire_rechaza_un_formato_invalido_con_error_de_validacion(): void
    {
        $componente = new \App\Livewire\ReportesIndex;
        $componente->formatoInventario = 'desconocido';
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $componente->validate(['formatoInventario' => ['required', 'in:global,intangibles,inmuebles,vehiculos']]);
    }

    public function test_formatos_conservan_todas_las_columnas_de_las_referencias(): void
    {
        $s = new InventarioGlobalReporteService;
        $this->assertCount(16, $s->columnas('global'));
        $this->assertSame($s->columnas('global'), $s->columnas('intangibles'));
        $this->assertCount(5, $s->columnas('inmuebles'));
        $this->assertCount(9, $s->columnas('vehiculos'));
        $this->assertSame('Sin dato', $s->celda([], 'total'));
        $this->assertSame('0.00', $s->celda(['total' => 0], 'total'));
        $this->assertSame('000123', $s->celda(['inventario' => '000123'], 'inventario'));
        $export = new InventarioGlobalReporteExport(collect([(object) ['inventario' => '000123']]), 'global');
        $this->assertCount(16, $export->array()[1]);
        $this->assertSame('000123', $export->array()[2][0]);
        $this->assertSame('Sin dato', $export->array()[2][10]);
    }

    public function test_consulta_separa_formatos_y_preserva_datos_faltantes_y_resguardatario_actual(): void
    {
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        foreach (['tipos_equipo' => 'id_tipo_equipo', 'marcas' => 'id_marca', 'modelos' => 'id_modelo', 'proveedores' => 'id_proveedor', 'departamentos' => 'id_departamento'] as $tabla => $id) {
            Schema::create($tabla, function (Blueprint $t) use ($id) { $t->integer($id); $t->string('nombre'); });
        }
        Schema::create('equipos', function (Blueprint $t) {
            $t->integer('id_equipo'); $t->string('codigo_inventario'); $t->string('nombre_equipo');
            foreach (['id_tipo_equipo','id_marca','id_modelo','id_proveedor','id_departamento','id_estado_activo'] as $c) { $t->integer($c)->nullable(); }
            foreach (['numero_serie','numero_factura','fecha_compra','fecha_registro'] as $c) { $t->string($c)->nullable(); }
        });
        Schema::table('tipos_equipo', fn (Blueprint $t) => $t->string('clasificacion_bien')->default('global'));
        Schema::create('campos_equipo', function (Blueprint $t) { $t->integer('id_campo'); $t->string('clave'); });
        Schema::create('bajas', function (Blueprint $t) { $t->integer('id_equipo'); });
        Schema::create('atributos_equipo', function (Blueprint $t) { $t->integer('id_equipo'); $t->integer('id_campo'); $t->text('valor'); });
        Schema::create('asignaciones', function (Blueprint $t) { $t->integer('id_asignacion'); $t->integer('id_equipo'); $t->string('nombre_colaborador'); $t->date('fecha_asignacion'); $t->date('fecha_fin')->nullable(); });
        foreach (['Laptop','Intangible','Inmueble','Vehículo'] as $i => $nombre) {
            DB::table('tipos_equipo')->insert(['id_tipo_equipo' => $i+1, 'nombre' => $nombre, 'clasificacion_bien' => ['global', 'intangibles', 'inmuebles', 'vehiculos'][$i]]);
            DB::table('equipos')->insert(['id_equipo' => $i+1,'id_tipo_equipo' => $i+1,'codigo_inventario' => '000'.($i+1),'nombre_equipo' => $nombre, 'fecha_registro' => '2026-11-01']);
        }
        DB::table('campos_equipo')->insert(['id_campo' => 1, 'clave' => 'institucion_total']);
        DB::table('atributos_equipo')->insert(['id_equipo' => 1,'id_campo' => 1,'valor' => '4139.05']);
        DB::table('asignaciones')->insert([
            ['id_asignacion'=>1,'id_equipo'=>1,'nombre_colaborador'=>'Anterior','fecha_asignacion'=>'2026-01-01','fecha_fin'=>'2026-02-01'],
            ['id_asignacion'=>2,'id_equipo'=>1,'nombre_colaborador'=>'Actual','fecha_asignacion'=>'2026-02-01','fecha_fin'=>null],
        ]);
        $s = new InventarioGlobalReporteService;
        foreach (array_keys($s::FORMATOS) as $formato) { $this->assertCount(1, $s->query($formato)->get()); }
        DB::table('tipos_equipo')->where('id_tipo_equipo', 4)->update(['nombre' => 'Camioneta institucional']);
        $this->assertCount(1, $s->query('vehiculos')->get());
        $this->assertCount(1, $s->query('global')->get());
        DB::table('campos_equipo')->insert(['id_campo' => 2, 'clave' => 'institucion_resguardatario']);
        DB::table('atributos_equipo')->insert(['id_equipo' => 4, 'id_campo' => 2, 'valor' => 'Responsable registrado']);
        $this->assertSame('Responsable registrado', $s->query('vehiculos')->first()->resguardatario);
        $fila = $s->query('global')->first();
        $this->assertSame('Actual', $fila->resguardatario);
        $this->assertSame('4,139.05', $s->celda($fila, 'total'));
        $this->assertNull($fila->iva);
        $this->assertCount(0, $s->query('global', ['busqueda'=>'Vehículo'])->get());
        $this->assertCount(0, $s->query('global', ['fecha_fin'=>'2026-10-31'])->get());

        Schema::table('equipos', function (Blueprint $t) { $t->integer('id_unidad')->nullable(); $t->integer('id_ubicacion')->nullable(); });
        Schema::create('unidades_organizativas', function (Blueprint $t) { $t->integer('id_unidad'); $t->string('nombre'); });
        Schema::create('ubicaciones', function (Blueprint $t) { $t->integer('id_ubicacion'); $t->string('nombre'); });
        Schema::create('bajas_resguardo', function (Blueprint $t) { $t->integer('id_equipo'); $t->date('fecha_resguardo'); $t->string('motivo'); $t->text('observaciones')->nullable(); $t->timestamp('fecha_completada')->nullable(); });
        DB::table('bajas_resguardo')->insert(['id_equipo'=>4,'fecha_resguardo'=>'2026-11-11','motivo'=>'Revisar motor']);
        $pendientes = $s->query('resguardo')->get();
        $this->assertCount(1, $pendientes);
        $this->assertSame('Vehículo', $pendientes[0]->descripcion);
        $this->assertSame('Revisar motor', $pendientes[0]->motivo_resguardo);
        $this->assertCount(0, $s->query('resguardo', ['fecha_fin'=>'2026-11-10'])->get());
        DB::table('bajas')->insert(['id_equipo'=>4]);
        DB::table('bajas_resguardo')->where('id_equipo',4)->update(['fecha_completada'=>now()]);
        $this->assertCount(0, $s->query('resguardo')->get());
        $this->assertCount(0, $s->query('vehiculos')->get());
    }

    public function test_pdf_y_excel_se_generan_con_las_16_columnas(): void
    {
        $filas = collect([(object) ['inventario'=>'000123','descripcion'=>'Silla de prueba','total'=>'1182.04']]);
        $html = view('reportes.inventario-global-tabla', ['filas'=>$filas,'formato'=>'global'])->render();
        foreach (InventarioGlobalReporteService::COMPLETAS as $titulo) { $this->assertStringContainsString(e($titulo), $html); }
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.inventario-global-pdf', ['filas'=>$filas,'formato'=>'global'])->setPaper('a3','landscape')->output();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $xlsx = \Maatwebsite\Excel\Facades\Excel::raw(new InventarioGlobalReporteExport($filas,'global'), \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $xlsx);
    }
}
