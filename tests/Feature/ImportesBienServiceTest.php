<?php

namespace Tests\Feature;

use App\Services\ImportesBienService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImportesBienServiceTest extends TestCase
{
    private function definitions(): array
    {
        return [
            'campo1' => ['clave' => 'institucion_costo_unitario'],
            'campo2' => ['clave' => 'institucion_iva', 'etiqueta' => 'Impuesto renombrado'],
            'campo3' => ['clave' => 'institucion_total'],
        ];
    }

    public function test_recalcula_el_total_en_servidor_usando_claves_estables(): void
    {
        $result = (new ImportesBienService)->calcular([
            'ivaPorcentaje' => '16',
            'childData' => ['campo1' => '19.99', 'campo2' => 999, 'campo3' => 999],
        ], $this->definitions());
        $this->assertEquals(3.20, $result['childData']['campo2']);
        $this->assertEquals(23.19, $result['childData']['campo3']);
    }

    public function test_conserva_importes_anteriores_y_csv_sin_porcentaje(): void
    {
        $payload = ['childData' => ['campo1' => 1000, 'campo2' => 160, 'campo3' => 1160]];
        $this->assertSame($payload, (new ImportesBienService)->calcular($payload, $this->definitions()));
    }

    public function test_acepta_tasa_cero(): void
    {
        $result = (new ImportesBienService)->calcular([
            'ivaPorcentaje' => 0, 'childData' => ['campo1' => 1000],
        ], $this->definitions());
        $this->assertEquals(0, $result['childData']['campo2']);
        $this->assertEquals(1000, $result['childData']['campo3']);
    }

    public function test_rechaza_porcentaje_fuera_de_rango(): void
    {
        $this->expectException(ValidationException::class);
        (new ImportesBienService)->calcular([
            'ivaPorcentaje' => 160, 'childData' => ['campo1' => 1000],
        ], $this->definitions());
    }
}
