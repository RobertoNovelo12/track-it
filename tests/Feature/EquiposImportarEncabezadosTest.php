<?php

namespace Tests\Feature;

use App\Livewire\EquiposImportar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EquiposImportarEncabezadosTest extends TestCase
{
    public function test_importa_una_fila_por_peticion_aunque_el_cliente_pida_diez(): void
    {
        Gate::define('gestionar', fn ($user = null) => true);
        $componente = new ImportadorLotes;
        $componente->clave = 'test-importacion-lotes';
        $componente->filas = array_map(fn ($n) => ['fila' => $n, 'estado' => 'lista'], range(2, 4));
        Cache::put($componente->clave, ['payloads' => [2 => [], 3 => [], 4 => []]]);
        try {
            $resultado = $componente->importarLote(10);
            $this->assertSame(1, $resultado['procesadas']);
            $this->assertSame(2, $resultado['pendientes']);
            $this->assertSame(1, $componente->guardadas);
            $resultado = $componente->importarLote(10);
            $this->assertSame(1, $resultado['pendientes']);
            $this->assertSame(2, $componente->guardadas);
        } finally {
            Cache::forget($componente->clave);
        }
    }

    public function test_impide_reimportar_inventario_institucional_duplicado_sin_serie(): void
    {
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE campos_equipo (id_campo INTEGER, clave TEXT)');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE atributos_equipo (id_equipo INTEGER, id_campo INTEGER, valor TEXT)');
        \Illuminate\Support\Facades\DB::table('campos_equipo')->insert(['id_campo' => 1, 'clave' => 'institucion_inventario_institucional']);
        foreach ([1, 2] as $id) {
            \Illuminate\Support\Facades\DB::table('atributos_equipo')->insert(['id_equipo' => $id, 'id_campo' => 1, 'valor' => 'IT-001']);
        }
        $vista = [['fila' => 2, 'serie' => '', 'estado' => 'lista', 'mensajes' => []]];
        $datos = ['encabezados' => ['Número de inventario institucional'], 'filas' => [2 => ['IT-001']]];
        $metodo = new \ReflectionMethod(EquiposImportar::class, 'revisarExistentes');
        $metodo->invokeArgs(new EquiposImportar, [&$vista, $datos]);
        $this->assertSame('error', $vista[0]['estado']);
        $this->assertStringContainsString('varios bienes', $vista[0]['mensajes'][0]);
    }

    public function test_la_carga_acepta_ambos_encabezados_y_normaliza_para_corregir_catalogos(): void
    {
        Gate::define('gestionar', fn ($user = null) => true);
        foreach (['Tipo de bien', 'Tipo de bien *', 'Tipo de equipo *'] as $encabezado) {
            $componente = new ImportadorEncabezados;
            $componente->archivo = UploadedFile::fake()->createWithContent('inventario.csv',
                "\xEF\xBB\xBF{$encabezado},Nombre del equipo\nMesa,Mesa de biblioteca\n");
            $componente->updatedArchivo();

            $this->assertFalse($componente->getErrorBag()->has('archivo'));
            $this->assertCount(1, $componente->filas);
            $this->assertSame('lista', $componente->filas[0]['estado']);
            $datos = Cache::get($componente->clave);
            $this->assertStringStartsWith('Tipo de equipo', $datos['encabezados'][0]);
            $this->assertSame('Mesa de biblioteca', $datos['payloads'][2]['nombreEquipo']);
            Cache::forget($componente->clave);
        }
    }

    public function test_la_carga_rechaza_un_archivo_sin_columna_de_tipo(): void
    {
        Gate::define('gestionar', fn ($user = null) => true);
        $componente = new ImportadorEncabezados;
        $componente->archivo = UploadedFile::fake()->createWithContent('inventario.csv', "Nombre del equipo\nMesa de biblioteca\n");
        $componente->updatedArchivo();
        $this->assertTrue($componente->getErrorBag()->has('archivo'));
        $this->assertSame([], $componente->filas);
    }
}

class ImportadorLotes extends EquiposImportar
{
    public int $guardadas = 0;

    protected function guardarEquipo(array $payload): array
    {
        $this->guardadas++;
        return ['ok' => true, 'record' => ['code' => 'ACT-'.$this->guardadas]];
    }
}

// Aísla catálogos y filas: la prueba recorre la carga real y su validación de archivo.
class ImportadorEncabezados extends EquiposImportar
{
    public function getTiposEquipoProperty(): array
    {
        return [['id_tipo_equipo' => 17, 'nombre' => 'Mesa']];
    }

    public function csvFilaAPayload(array $encabezados, array $fila): array
    {
        return [['idTipoEquipo' => '17', 'nombreEquipo' => $fila[1], 'childData' => []], [], 17];
    }
}
