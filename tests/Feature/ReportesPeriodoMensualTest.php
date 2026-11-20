<?php

namespace Tests\Feature;

use App\Livewire\ReportesIndex;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReportesPeriodoMensualTest extends TestCase
{
    public function test_convierte_meses_completos_incluyendo_bisiestos_y_cambio_de_anio(): void
    {
        $componente = new ReportesIndex;
        foreach ([['2023-12', '2024-02', '2023-12-01', '2024-02-29'], ['2025-02', '2025-02', '2025-02-01', '2025-02-28'], ['2026-01', '2026-06', '2026-01-01', '2026-06-30']] as [$inicio, $fin, $desde, $hasta]) {
            $componente->mesInicio = $inicio;
            $componente->updated('mesInicio');
            $componente->mesFin = $fin;
            $componente->updated('mesFin');
            $componente->validate($componente->getRules());
            $this->assertSame($desde, $componente->fechaInicio);
            $this->assertSame($hasta, $componente->fechaFin);
        }
        $componente->limpiarFiltros();
        $this->assertSame('', $componente->mesInicio);
        $this->assertSame('', $componente->mesFin);
        $this->assertSame('', $componente->fechaInicio);
        $this->assertSame('', $componente->fechaFin);
    }

    public function test_permite_solo_mes_final_y_borrarlo_elimina_el_limite(): void
    {
        $componente = new ReportesIndex;
        $componente->mesFin = '2026-11';
        $componente->updated('mesFin');
        $componente->validate($componente->getRules());
        $this->assertSame('2026-11-30', $componente->fechaFin);
        $componente->mesFin = '';
        $componente->updated('mesFin');
        $this->assertSame('', $componente->fechaFin);
    }

    public function test_rechaza_un_rango_invertido(): void
    {
        $componente = new ReportesIndex;
        $componente->mesInicio = '2026-06';
        $componente->mesFin = '2026-01';
        $componente->updated('mesInicio');
        $componente->updated('mesFin');
        $this->expectException(ValidationException::class);
        $componente->validate($componente->getRules());
    }

    public function test_rechaza_un_mes_invalido_sin_convertirlo_en_otro_mes(): void
    {
        $componente = new ReportesIndex;
        $componente->mesInicio = '2026-13';
        $componente->updated('mesInicio');
        $this->assertSame('', $componente->fechaInicio);
        $this->expectException(ValidationException::class);
        $componente->validate($componente->getRules());
    }
}
