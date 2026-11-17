<?php

namespace Tests\Feature;

use App\Livewire\Concerns\EquiposCsv;
use App\Services\ColaboradoresService;
use App\Services\PlantillaInstalacionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EquiposCsvTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('tipos_equipo', function (Blueprint $t) {
            $t->integer('id_tipo_equipo');
            $t->string('clasificacion_bien');
        });
        foreach (['global', 'intangibles', 'inmuebles', 'vehiculos'] as $i => $clase) {
            DB::table('tipos_equipo')->insert(['id_tipo_equipo' => $i + 1, 'clasificacion_bien' => $clase]);
        }
        $this->mock(PlantillaInstalacionService::class)
            ->shouldReceive('etiquetas')->andReturn(['unidad_singular' => 'Edificio']);
    }

    private function lector(): object
    {
        return new class
        {
            use EquiposCsv;

            public function __construct()
            {
                foreach (['Silla', 'Software académico', 'Edificio', 'Camioneta Pickup'] as $i => $nombre) {
                    $this->csvMemoria['lista.tipos'][] = ['id_tipo_equipo' => $i + 1, 'nombre' => $nombre];
                    $this->csvMemoria['base'][$i + 1] = [
                        'id_marca' => ['visible' => false, 'obligatorio' => false],
                        'numero_serie' => ['visible' => true, 'obligatorio' => false],
                        'numero_factura' => ['visible' => true, 'obligatorio' => false],
                        'fecha_compra' => ['visible' => true, 'obligatorio' => true],
                        'id_ubicacion' => ['visible' => true, 'obligatorio' => true],
                    ];
                }
                $this->csvMemoria['lista.estados'] = [['id_estado_equipo' => 1, 'nombre' => 'Disponible']];
                $this->csvMemoria['lista.hoteles'] = [['id_unidad' => 1, 'nombre' => 'Edificio H']];
                $this->csvMemoria['lista.proveedores'] = [['id_proveedor' => 2, 'nombre' => 'Suministros del Sureste']];
                $this->csvMemoria['lista.usuarios'] = [];
            }

            protected function configuredChildFields(int $tipoId): array
            {
                return ['tipo_'.$tipoId.'_campo_80' => [
                    'etiqueta' => 'Número de inventario institucional',
                    'tipo_control' => 'text', 'obligatorio' => false,
                ]];
            }
        };
    }

    public function test_las_cuatro_clasificaciones_conservan_proveedor_responsable_y_datos_dinamicos(): void
    {
        $lector = $this->lector();
        foreach (['Silla', 'Software académico', 'Edificio', 'Camioneta Pickup'] as $i => $tipo) {
            [$payload, $errores] = $lector->csvFilaAPayload(
                ['Tipo de bien', 'Nombre del bien', 'Estado', 'Edificio', 'Proveedor o vendedor', 'Fecha de compra', 'Responsable', 'Número de inventario institucional'],
                [$tipo, $tipo.' de administración', 'Disponible', $i === 2 ? '' : 'Edificio H', 'Suministros del Sureste', '15/03/2023', 'María Fernanda López Martínez', 'INV-'.($i + 1)]
            );
            $this->assertSame([], $errores);
            $this->assertSame(['global', 'intangibles', 'inmuebles', 'vehiculos'][$i], $payload['clasificacionBien']);
            $this->assertSame('2', $payload['idProveedor']);
            $this->assertSame('2023-03-15', $payload['fechaCompra']);
            $this->assertSame(ColaboradoresService::PREFIJO_TEXTO.'María Fernanda López Martínez', $payload['propietario']);
            $this->assertSame('INV-'.($i + 1), $payload['childData']['tipo_'.($i + 1).'_campo_80']);
        }
    }

    public function test_respeta_obligatorios_y_no_obliga_edificio_ni_ubicacion_para_inmueble(): void
    {
        $lector = $this->lector();
        $columnas = collect($lector->csvColumnas(3))->keyBy('clave');
        $this->assertFalse($columnas['id_unidad']['obligatorio']);
        $this->assertFalse($columnas['id_ubicacion']['obligatorio']);
        $this->assertFalse($columnas->has('id_marca'));
        $this->assertTrue($columnas['fecha_compra']['obligatorio']);
        [$payload, $errores] = $lector->csvFilaAPayload(['Tipo de equipo', 'Nombre del equipo', 'Estado'], ['Edificio', 'Biblioteca', 'Disponible']);
        $this->assertSame(['Falta «Fecha de compra».'], $errores);
        $this->assertArrayNotHasKey('propietario', $payload);
    }

    public function test_rechaza_fecha_invalida_y_tipo_desconocido(): void
    {
        $lector = $this->lector();
        [, $errores] = $lector->csvFilaAPayload(['Tipo de bien', 'Nombre del bien', 'Estado', 'Fecha de compra'], ['Edificio', 'Biblioteca', 'Disponible', '31/02/2023']);
        $this->assertCount(1, $errores);
        $this->assertStringContainsString('no es una fecha válida', $errores[0]);
        [$payload, $errores] = $lector->csvFilaAPayload(['Tipo de bien'], ['Inexistente']);
        $this->assertNull($payload);
        $this->assertCount(1, $errores);
    }

    public function test_lee_delimitadores_y_comas_dentro_de_encabezados_entrecomillados(): void
    {
        foreach ([',', ';', "\t"] as $separador) {
            $stream = fopen('php://temp', 'r+');
            $encabezados = ['Tipo de bien', 'Nombre del software, licencia o intangible'];
            fputcsv($stream, $encabezados, $separador, '"', '\\');
            fputcsv($stream, ['Software académico', 'Plataforma académica'], $separador, '"', '\\');
            rewind($stream);
            [$leidos, $filas] = $this->lector()->csvLeer("\xEF\xBB\xBF".stream_get_contents($stream));
            fclose($stream);
            $this->assertSame($encabezados, $leidos);
            $this->assertSame([['Software académico', 'Plataforma académica']], $filas);
        }
    }

    public function test_clasificaciones_se_consultan_una_vez_para_150_filas(): void
    {
        $lector = $this->lector();
        $consultas = 0;
        DB::listen(function ($query) use (&$consultas) {
            if (str_contains($query->sql, 'tipos_equipo')) {
                $consultas++;
            }
        });
        for ($i = 0; $i < 150; $i++) {
            $lector->csvFilaAPayload(['Tipo de bien', 'Nombre del bien', 'Estado', 'Fecha de compra'],
                ['Edificio', 'Edificio académico', 'Disponible', '15/03/2023']);
        }
        $this->assertSame(1, $consultas);
    }
}
