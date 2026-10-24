    <h1>Ficha informativa de equipo</h1>
    <div class="sub">Código: {{ $equipo['codigo'] }} · Generado: {{ $generadoEn->format('d/m/Y H:i') }}</div>

    <div class="estado">
        <strong>Estado:</strong> {{ $equipo['estado'] }}
        &nbsp;&nbsp; | &nbsp;&nbsp;
        <strong>Situación:</strong> {{ $equipo['situacion'] }}
    </div>

    <h2>01 · Identificación del equipo</h2>
    <table>
        @foreach([
            'Código de inventario' => $equipo['codigo'],
            'Nombre del equipo' => $equipo['nombre'],
            'Tipo de equipo' => $equipo['tipo'],
            'Marca' => $equipo['marca'],
            'Modelo' => $equipo['modelo'],
            'Número de serie' => $equipo['serie'],
            'Service Tag' => $equipo['service_tag'],
            'Condición' => $equipo['condicion'],
        ] as $campo => $valor)
        <tr><th>{{ $campo }}</th><td>{{ $valor }}</td></tr>
        @endforeach
    </table>

    <h2>02 · Responsable y ubicación</h2>
    <table>
        @foreach([
            'Situación administrativa' => $equipo['situacion'],
            'Responsable actual' => $equipo['responsable'],
            'Hotel' => $equipo['hotel'],
            'Departamento' => $equipo['departamento'],
            'Ubicación registrada' => $equipo['ubicacion'],
            'Fecha de asignación vigente' => $equipo['fecha_asignacion'],
        ] as $campo => $valor)
        <tr><th>{{ $campo }}</th><td>{{ $valor }}</td></tr>
        @endforeach
    </table>

    <h2>03 · Identificación del usuario</h2>
    <table>
        <tr><th>Nombre completo</th><td>{{ $equipo['responsable'] ?: 'Sin asignación vigente' }}</td></tr>
        <tr><th>Nombre de usuario</th><td>{{ $equipo['usuario_nombre'] ?? '—' }}</td></tr>
        <tr><th>Correo</th><td>{{ $equipo['usuario_correo'] ?? '—' }}</td></tr>
    </table>

    <h2>04 · Software instalado</h2>
    @if (($equipo['programas'] ?? []) !== [])
        <table>
            @foreach ($equipo['programas'] as $programa)
                <tr><th>{{ $programa['nombre'] }}</th><td>{{ $programa['detalle'] ?: '—' }}</td></tr>
            @endforeach
        </table>
    @else
        <p>Sin programas registrados.</p>
    @endif
