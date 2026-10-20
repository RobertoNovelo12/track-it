@component('errors.plantilla', [
    'codigo' => 404,
    'titulo' => 'Página no encontrada',
    'mensaje' => 'La dirección no existe o el registro que buscas ya no está disponible. Revisa el enlace o regresa al inicio.',
    'tono' => 'primary',
])
    @slot('icono')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/>
            <path d="M20 20l-4-4"/>
            <path d="M8.5 11h5"/>
        </svg>
    @endslot
@endcomponent
