@php
    $dato = static fn ($valor) =>
        filled($valor) ? trim((string) $valor) : '—';

    $marcaModelo = trim(implode(' ', array_filter([
        $alta['marca'] ?? null,
        $alta['modelo'] ?? null,
    ])));

    $secciones = [
        ['01', 'DATOS DEL REGISTRO', [
            [
                ['Código de inventario', $alta['codigo'] ?? null],
                ['Fecha de registro', $alta['fecha_registro'] ?? null],
            ],
        ]],

        ['02', 'INFORMACIÓN GENERAL DEL EQUIPO', [
            [
                ['Nombre del equipo', $alta['nombre'] ?? null],
                ['Host', $alta['host'] ?? null],
            ],
            [
                ['Tipo de equipo', $alta['tipo'] ?? null],
                ['Marca / Modelo', $marcaModelo],
            ],
            [
                ['Número de serie', $alta['serie'] ?? null],
                ['Dirección MAC', $alta['mac'] ?? null],
            ],
        ]],

        ['03', 'INFORMACIÓN DE ADQUISICIÓN', [
            [
                ['Fecha de compra', $alta['fecha_compra'] ?? null],
                ['Número de factura', $alta['factura'] ?? null],
            ],
            [
                ['Proveedor', $alta['proveedor'] ?? null],
                ['Vigencia de garantía', $alta['garantia'] ?? null],
            ],
        ]],

        ['04', 'ESTADO Y RESPONSABILIDAD DEL ACTIVO', [
            [
                ['Estado', $alta['estado'] ?? null],
                ['Estatus / Condición', $alta['condicion'] ?? null],
            ],
            [
                ['Propietario / Responsable', $alta['responsable'] ?? null],
                ['Hotel', $alta['hotel'] ?? null],
            ],
            [
                ['Departamento', $alta['departamento'] ?? null],
            ],
        ]],

        ['05', 'INFORMACIÓN ADICIONAL', [
            [
                ['Comentarios', $alta['comentarios'] ?? null],
            ],
        ]],
    ];
@endphp

<style>
    .alta-vista {
        background: #ffffff;
        color: #000000;
        font: 7px/1.35 'Aptos', 'Segoe UI', Arial, sans-serif;
        padding: 5%;
        overflow-wrap: anywhere;
    }

    .alta-vista * {
        box-sizing: border-box;
    }

    .alta-vista .logo-palladium-izquierda {
        display: block;
        float: none;
        width: 32%;
        max-width: 135px;
        height: auto;
        margin: 0 0 8px 0;
    }

    .alta-vista .marca {
        color: #a58d40;
        font-size: 16px;
        letter-spacing: 2px;
    }

    .alta-vista .grupo {
        color: #a58d40;
        font-size: 6px;
        letter-spacing: 3px;
    }

    .alta-vista .institucion {
        clear: both;
        color: #000000;
        font-size: 6px;
        padding: 5px 0;
        border-bottom: 1px solid #000000;
    }

    .alta-vista h2 {
        font-size: 11px;
        color: #000000;
        font-weight: normal;
        margin: 9px 0 3px;
    }

    .alta-vista .subtitulo {
        color: #000000;
        font-size: 6px;
        margin-bottom: 7px;
    }

    .alta-vista .seccion {



        padding: 6px;
        margin: 8px 0 4px;

        background: #000000; color: #ffffff; font-weight: normal;
}

    .alta-vista table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .alta-vista td {
        border: 1px solid #000000;
        padding: 6px;
        vertical-align: top;
        width: 50%;
    }

    .alta-vista .etiqueta {
        color: #000000;
        font-size: 6px;
        text-transform: uppercase;
        font-weight: normal;
        display: block;
        margin-bottom: 4px;
    }


.alta-vista .valor {
    color: #000000;
    font-weight: normal;
    white-space: pre-line;
    overflow-wrap: anywhere;
    display: block;
}


    .alta-vista .linea {
        height: 42px;
        border-bottom: 1px solid #000000;
    }

    .alta-vista .pie {
        color: #000000;
        font-size: 6px;
        padding-top: 6px;
        border-top: 1px solid #000000;
        margin-top: 9px;
    }
</style>

<div class="overflow-hidden border border-black bg-white shadow-sm">
    <article class="alta-vista">
        <img
            class="logo-palladium-izquierda"
            src="{{ app(\App\Services\OrganizacionService::class)->logoDocumentosUrl() }}"
            alt="Palladium Hotel Group"
        >

        <div class="institucion">
            GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI
        </div>

        <h2>
            ACTA DE ALTA DE EQUIPO TECNOLÓGICO
        </h2>

        <div class="subtitulo">
            Registro de incorporación al inventario ·
            Departamento de Sistemas
        </div>

        @foreach ($secciones as [$numero, $titulo, $filas])
            <div class="seccion">
                {{ $numero }} &nbsp; {{ $titulo }}
            </div>

            <table>
                <tbody>
                    @foreach ($filas as $fila)
                        <tr>
                            @foreach ($fila as [$etiqueta, $valor])
                                <td
                                    @if (count($fila) === 1)
                                        colspan="2"
                                    @endif
                                >
                                    <span class="etiqueta">
                                        {{ $etiqueta }}
                                    </span>

                                    <span class="valor">
                                        {{ $dato($valor) }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <div class="seccion">
            06 &nbsp; FIRMAS DE REGISTRO Y VALIDACIÓN
        </div>

        <table>
            <tr>
                <td>
                    <strong>
                        Registró · Departamento de Sistemas
                    </strong>

                    <div class="linea"></div>
                    <div>Nombre y firma</div>
                    <div>Fecha: ____ / ____ / ______</div>
                </td>

                <td>
                    <strong>
                        Validó · Responsable de Sistemas
                    </strong>

                    <div class="linea"></div>
                    <div>Nombre y firma</div>
                    <div>Fecha: ____ / ____ / ______</div>
                </td>
            </tr>
        </table>

        <div class="pie">
            Departamento de Sistemas ·
            Control de Activos TI · Vista previa
        </div>
    </article>
</div>
