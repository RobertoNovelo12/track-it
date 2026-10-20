@php
    /*
     * Algunos abort(403) traen un motivo propio (p. ej. «No se puede
     * editar un equipo dado de baja.»); se muestra en lugar del texto
     * general. Los mensajes genéricos del framework se ignoran.
     */
    $motivo = trim((string) ($exception?->getMessage() ?? ''));

    $genericos = [
        '',
        'Forbidden',
        'This action is unauthorized.',
        'Esta acción no está autorizada.',
        'Acceso no autorizado.',
    ];

    $mensaje = in_array($motivo, $genericos, true)
        ? 'Tu rol no tiene permiso para ver esta sección o realizar esta acción. Si crees que es un error, contacta al administrador del sistema.'
        : $motivo;
@endphp

@component('errors.plantilla', [
    'codigo' => 403,
    'titulo' => 'Acceso no autorizado',
    'mensaje' => $mensaje,
    'tono' => 'warning',
])
    @slot('icono')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="5" y="10.5" width="14" height="10" rx="2"/>
            <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>
            <path d="M12 14.5v2"/>
        </svg>
    @endslot
@endcomponent
