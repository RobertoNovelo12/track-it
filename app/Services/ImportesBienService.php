<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class ImportesBienService
{
    /** El formulario captura porcentaje; los atributos y CSV conservan IVA como importe. */
    public function calcular(array $payload, array $definitions): array
    {
        if (! array_key_exists('ivaPorcentaje', $payload)) { return $payload; }
        $claves = [];
        foreach ($definitions as $key => $field) {
            $clave = preg_replace('/^institucion_/', '', $field['clave'] ?? '');
            if (in_array($clave, ['costo_unitario', 'iva', 'total'], true)) { $claves[$clave] = $key; }
        }
        if (count($claves) !== 3) { return $payload; }
        $costo = $payload['childData'][$claves['costo_unitario']] ?? null;
        $porcentaje = $payload['ivaPorcentaje'];
        Validator::make(['childData' => [$claves['costo_unitario'] => $costo, $claves['iva'] => $porcentaje]], [
            'childData.'.$claves['costo_unitario'] => ['nullable', 'numeric', 'min:0'],
            'childData.'.$claves['iva'] => ['nullable', 'numeric', 'between:0,100'],
        ], ['childData.'.$claves['iva'].'.between' => 'El IVA debe ser un porcentaje entre 0 y 100.', 'childData.'.$claves['iva'].'.numeric' => 'Indica el porcentaje de IVA con un número.'])->validate();
        $importe = null;
        $total = null;
        if (filled($costo) && filled($porcentaje)) {
            $costo = round((float) $costo, 2);
            $importe = round($costo * (float) $porcentaje / 100, 2);
            $total = round($costo + $importe, 2);
        }
        $payload['childData'][$claves['iva']] = $importe;
        $payload['childData'][$claves['total']] = $total;
        return $payload;
    }
}
