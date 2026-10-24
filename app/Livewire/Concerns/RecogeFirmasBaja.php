<?php

namespace App\Livewire\Concerns;

trait RecogeFirmasBaja
{
    public array $firmas = [
        'responsable_departamental' => '',
        'mantenimiento' => '',
        'gerente_hotel' => '',
        'auditor_costos' => '',
        'gerente_financiero' => '',
    ];

    public const FIRMANTES = [
        'responsable_departamental' => 'Responsable Departamental',
        'mantenimiento' => 'Mantenimiento (si aplica)',
        'gerente_hotel' => 'Gerente de Hotel / General Manager',
        'auditor_costos' => 'Auditor de Costos',
        'gerente_financiero' => 'Gerente Financiero',
    ];

    protected function reglasFirmas(): array
    {
        $rules = [];
        foreach (self::FIRMANTES as $key => $label) {
            $rules['firmas.'.$key] = [$key === 'mantenimiento' ? 'nullable' : 'required', 'string', 'max:255'];
        }
        return $rules;
    }

    protected function nombresFirmas(): array
    {
        return array_map(fn ($name) => trim((string) $name), array_intersect_key($this->firmas, self::FIRMANTES));
    }
}
