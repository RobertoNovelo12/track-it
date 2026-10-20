@component('errors.plantilla', [
    'codigo' => 500,
    'titulo' => 'Algo salió mal',
    'mensaje' => 'Ocurrió un error inesperado en el servidor. Ya quedó registrado; intenta de nuevo en unos momentos.',
    'tono' => 'danger',
])
    @slot('icono')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 9v4M12 16.5v.5"/>
            <path d="M10.3 3.9L2.6 17.3A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.7L13.7 3.9a2 2 0 0 0-3.4 0z"/>
        </svg>
    @endslot
@endcomponent
