
@php
    $esReasignacion = ($acta['tipo'] ?? '') === 'reasignaciones';
    $anterior = $acta['anterior'] ?? [];
    $nuevo = $acta['nuevo'] ?? [];
    $equipo = $acta['equipo'] ?? [];

    $dato = static fn ($valor) =>
        filled($valor) ? (string) $valor : '—';

    $marcaModelo = trim(
        ($equipo['marca'] ?? '') . ' ' .
        ($equipo['modelo'] ?? '')
    );

    $areaNuevo = implode(' / ', array_unique(array_filter([
        $nuevo['departamento'] ?? null,
        $nuevo['area'] ?? null,
    ])));
@endphp

<style>
    .rp-acta {
        background: #ffffff;
        color: #000000;
        font-family: DejaVu Sans, Arial, sans-serif;
        padding: 5%;
        font-size: 7px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .rp-acta * {
        box-sizing: border-box;
    }

    .rp-acta .logo {
        float: none;
        display: block;
        width: 34%;
        max-width: 145px;
        height: auto;
        margin: 0 0 5px 0;
        margin-left: 0;
        margin-right: auto;
    }

    .rp-acta .submarca {
        clear: both;
        font-size: .9em;
        color: #000000;
        border-bottom: 1px solid #000000;
        padding-bottom: 6px;
    }

    .rp-acta h2 {
        margin: 7px 0 2px;
        color: #000000;
        font-size: 1.55em;
        font-weight: normal;
        line-height: 1.2;
    }

    .rp-acta .sub {
        margin-bottom: 6px;
        color: #000000;
        font-size: .94em;
    }

    .rp-acta .section {
        margin: 7px 0 4px;
        padding: 5px 7px;



        font-size: 1.04em;

        background: #000000; color: #ffffff; font-weight: normal;
}

    .rp-acta .section b {
        margin-right: 8px;
    }

    .rp-acta .fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 1px; padding: 1px; background: #000000;
}

    .rp-acta .fields.three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .rp-acta .field {
        min-width: 0;
        min-height: 28px;
        padding: 5px 7px;
        border: 0; background: #ffffff;
    }

    .rp-acta .field.wide {
        grid-column: 1 / -1;
    }

    .rp-acta .label {
        display: block;
        margin-bottom: 3px;
        color: #000000;
        font-weight: normal;
        font-size: .82em;
        text-transform: uppercase;
    }

    .rp-acta .value {
        display: block;
        font-weight: normal;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .rp-acta table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .rp-acta th {
        padding: 6px 4px;


        text-align: left;
        font-size: .85em;

        background: #000000; color: #ffffff; font-weight: normal;
}

    .rp-acta td {
        padding: 6px 4px;
        border: 1px solid #000000;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .rp-acta .secondary {
        margin-top: 3px;
        color: #000000;
    }

    .rp-acta .observaciones {
        padding: 6px 8px;
        border: 1px solid #000000;
        min-height: 30px;
    }

    .rp-acta .commit {
        margin: 6px 2px;
        color: #000000;
        text-align: justify;
        line-height: 1.45;
    }

    .rp-acta .signs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .rp-acta .sign {
        min-height: 70px;
        padding: 6px;
        border: 1px solid #000000;
        display: flex;
        flex-direction: column;
    }

    .rp-acta .sign .line {
        flex: 1;
        min-height: 30px;
        border-bottom: 1px solid #000000;
    }

    .rp-acta .sign .foot {
        padding-top: 4px;
        color: #000000;
    }

    .rp-acta .bottom {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        color: #000000;
        font-size: .85em;
    }

    /* Imagen institucional en la esquina superior izquierda del documento. */
    .logo-palladium-izquierda {
        display: block;
        float: none;
        width: 32%;
        max-width: 135px;
        height: auto;
        margin: 0 0 8px 0;
    }
</style>

<div class="overflow-hidden border border-black bg-white shadow-sm">
    <article class="rp-acta">

        <img
            class="logo"
            src="{{ asset('images/logo-grand-palladium.png') }}"
            alt="Palladium Hotel Group"
        >

        <div class="submarca">
            GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI
        </div>

        <h2>
            {{ $esReasignacion
                ? 'REASIGNACIÓN DE EQUIPO'
                : 'ACTA DE ASIGNACIÓN DE EQUIPO TECNOLÓGICO' }}
        </h2>

        <div class="sub">
            {{ $esReasignacion
                ? 'Constancia de transferencia de resguardo entre trabajadores'
                : 'Constancia de entrega, recepción y responsabilidad del resguardo' }}
        </div>

        <div class="section">
            <b>01</b>
            DATOS DE LA
            {{ $esReasignacion ? 'REASIGNACIÓN' : 'ASIGNACIÓN' }}
        </div>

        @if ($esReasignacion)
            <div class="fields">
                <div class="field">
                    <span class="label">Fecha de reasignación</span>
                    <span class="value">
                        {{ $dato($acta['fecha'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Motivo de reasignación</span>
                    <span class="value">
                        {{ $dato($acta['motivo'] ?? null) }}
                    </span>
                </div>
            </div>

            <div class="section">
                <b>02</b> ASIGNACIÓN ANTERIOR
            </div>

            <div class="fields">
                <div class="field">
                    <span class="label">Responsable anterior</span>
                    <span class="value">
                        {{ $dato($anterior['nombre'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Número de empleado</span>
                    <span class="value">
                        {{ $dato($anterior['numero'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Sede anterior</span>
                    <span class="value">
                        {{ $dato($anterior['sede'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Área / Departamento anterior</span>
                    <span class="value">
                        {{ $dato(implode(' / ', array_filter([
                            $anterior['departamento'] ?? null,
                            $anterior['area'] ?? null,
                        ]))) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Ubicación anterior</span>
                    <span class="value">
                        {{ $dato($anterior['ubicacion'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Host del equipo</span>
                    <span class="value">
                        {{ $dato($equipo['host'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Fecha de asignación anterior</span>
                    <span class="value">
                        {{ $dato($anterior['fecha'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Tipo de asignación anterior</span>
                    <span class="value">
                        {{ $dato($anterior['tipo'] ?? null) }}
                    </span>
                </div>
            </div>

            <div class="section">
                <b>03</b> NUEVA REASIGNACIÓN
            </div>

            <div class="fields">
                <div class="field">
                    <span class="label">Nuevo colaborador</span>
                    <span class="value">
                        {{ $dato($nuevo['nombre'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Número de empleado</span>
                    <span class="value">
                        {{ $dato($nuevo['numero'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Nueva sede</span>
                    <span class="value">
                        {{ $dato($nuevo['sede'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Nueva área</span>
                    <span class="value">
                        {{ $dato($nuevo['area'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Nuevo departamento</span>
                    <span class="value">
                        {{ $dato($nuevo['departamento'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Nueva ubicación</span>
                    <span class="value">
                        {{ $dato($nuevo['ubicacion'] ?? null) }}
                    </span>
                </div>
            </div>
        @else
            <div class="fields three">
                <div class="field">
                    <span class="label">Host del equipo</span>
                    <span class="value">
                        {{ $dato($equipo['host'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Fecha de entrega</span>
                    <span class="value">
                        {{ $dato($acta['fecha'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Tipo de asignación</span>
                    <span class="value">
                        {{ $dato($nuevo['tipo'] ?? null) }}
                    </span>
                </div>
            </div>

            <div class="section">
                <b>02</b> DATOS DEL TRABAJADOR RESPONSABLE
            </div>

            <div class="fields">
                <div class="field">
                    <span class="label">Nombre completo</span>
                    <span class="value">
                        {{ $dato($nuevo['nombre'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Número de empleado</span>
                    <span class="value">
                        {{ $dato($nuevo['numero'] ?? null) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Departamento / Área</span>
                    <span class="value">
                        {{ $dato($areaNuevo) }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Ubicación de trabajo</span>
                    <span class="value">
                        {{ $dato($nuevo['ubicacion'] ?? null) }}
                    </span>
                </div>
            </div>
        @endif

        <div class="section">
            <b>{{ $esReasignacion ? '04' : '03' }}</b>
            {{ $esReasignacion
                ? 'EQUIPO REASIGNADO'
                : 'EQUIPOS ASIGNADOS' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th width="18%">CÓDIGO</th>
                    <th width="32%">EQUIPO / MARCA-MODELO</th>
                    <th width="22%">N.º DE SERIE</th>
                    <th width="28%">CONDICIÓN / ACCESORIOS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $dato($equipo['codigo'] ?? null) }}</td>
                    <td>
                        {{ $dato($equipo['nombre'] ?? null) }}
                        @if ($marcaModelo !== '')
                            <div class="secondary">
                                {{ $marcaModelo }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $dato($equipo['serie'] ?? null) }}</td>
                    <td>{{ $dato($equipo['condicion'] ?? null) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section">
            <b>{{ $esReasignacion ? '05' : '04' }}</b>
            {{ $esReasignacion
                ? 'OBSERVACIONES Y COMPROMISO DE RESGUARDO'
                : 'ESTADO, ENTREGA Y OBSERVACIONES' }}
        </div>

        <div class="observaciones">
            <span class="label">Observaciones de entrega</span>
            <span class="value">
                {{ $dato($acta['observaciones'] ?? null) }}
            </span>
        </div>

        @unless ($esReasignacion)
            <div class="section">
                <b>05</b> COMPROMISO DE RESGUARDO
            </div>
        @endunless

        <div class="commit">
            @if ($esReasignacion)
                Declaro que el equipo indicado cambia de responsable
                y se entrega en las condiciones señaladas.
                El nuevo responsable se compromete a utilizarlo para
                fines laborales, cuidarlo, reportar incidentes y
                devolverlo cuando corresponda. Los daños por negligencia
                o uso indebido se atenderán conforme a las políticas
                del hotel y la legislación aplicable.
            @else
                Declaro recibir los equipos y accesorios descritos en
                las condiciones indicadas. Me comprometo a utilizarlos
                para fines de trabajo, cuidarlos, reportar cualquier
                incidente y devolverlos cuando corresponda. En caso
                de daños ocasionados por mi negligencia o uso indebido,
                asumiré los costos que correspondan, previa evaluación
                y conforme a las políticas internas del hotel y la
                legislación aplicable.
            @endif
        </div>

        <div class="section">
            <b>06</b> FIRMAS DE ENTREGA Y RECEPCIÓN
        </div>

        <div class="signs">
            <div class="sign">
                <strong>Entregó · Sistemas</strong>
                <div class="line"></div>
                <div class="foot">
                    Nombre y firma
                    <div>Fecha: ____ / ____ / ______</div>
                </div>
            </div>

            <div class="sign">
                <strong>
                    Recibió ·
                    {{ $esReasignacion
                        ? 'Nuevo responsable'
                        : 'Trabajador responsable' }}
                </strong>
                <div class="line"></div>
                <div class="foot">
                    Nombre y firma
                    <div>Fecha: ____ / ____ / ______</div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <span>Departamento de Sistemas · Control de Activos TI</span>
            <span>Vista previa</span>
        </div>
    </article>
</div>
