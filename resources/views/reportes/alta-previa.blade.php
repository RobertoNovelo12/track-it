
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
                ['Área', $alta['area'] ?? null],
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
        background: #fff;
        color: #273f57;
        font: 7px/1.35 Arial, sans-serif;
        padding: 5%;
        overflow-wrap: anywhere;
    }

    .alta-vista * {
        box-sizing: border-box;
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
        color: #718293;
        font-size: 6px;
        padding: 5px 0;
        border-bottom: 2px solid #ad9647;
    }

    .alta-vista h2 {
        font-size: 11px;
        color: #213c55;
        font-weight: 800;
        margin: 9px 0 3px;
    }

    .alta-vista .subtitulo {
        color: #748494;
        font-size: 6px;
        margin-bottom: 7px;
    }

    .alta-vista .seccion {
        background: #eff1f3;
        color: #2b435b;
        font-weight: bold;
        padding: 6px;
        margin: 8px 0 4px;
    }

    .alta-vista table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .alta-vista td {
        border: 1px solid #d6dfe7;
        padding: 6px;
        vertical-align: top;
        width: 50%;
    }

    .alta-vista .etiqueta {
        color: #617b91;
        font-size: 6px;
        text-transform: uppercase;
        font-weight: bold;
        display: block;
        margin-bottom: 4px;
    }

    .alta-vista .valor {
        color: #253d56;
        font-weight: 600;
        white-space: pre-line;
        overflow-wrap: anywhere;
        display: block;
    }

    .alta-vista .linea {
        height: 42px;
        border-bottom: 1px solid #8797a6;
    }

    .alta-vista .pie {
        color: #718293;
        font-size: 6px;
        padding-top: 6px;
        border-top: 1px solid #e0e5ea;
        margin-top: 9px;
    }
</style>

<div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
    <article class="alta-vista">

        <div class="marca">PALLADIUM</div>
        <div class="grupo">HOTEL GROUP</div>

        <div class="institucion">
            GRAND PALLADIUM RIVIERA MAYA |
            CONTROL DE ACTIVOS TI
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
