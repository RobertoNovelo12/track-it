<?php

namespace App\Livewire;

use App\Services\NotificacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Centro de ayuda › Reportar un problema
|--------------------------------------------------------------------------
|
| Envía un aviso (campana y push) a quienes administran usuarios, con la
| descripción del problema, la página donde estaba la persona y su
| navegador. Así el soporte no depende de capturas sueltas por chat.
|
*/

class AyudaReporte extends Component
{
    public string $asunto = '';

    public string $descripcion = '';

    // Lo llena el navegador al enviar (página anterior y navegador).
    public string $pagina = '';

    public string $navegador = '';

    public bool $enviado = false;

    public function enviar(): void
    {
        abort_unless(auth()->check(), 403);

        $datos = $this->validate([
            'asunto' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'min:10', 'max:2000'],
            'pagina' => ['nullable', 'string', 'max:500'],
            'navegador' => ['nullable', 'string', 'max:300'],
        ], [
            'asunto.required' => 'Escribe un asunto corto.',
            'descripcion.required' => 'Describe qué pasó.',
            'descripcion.min' => 'Describe un poco más qué pasó (al menos 10 caracteres).',
        ]);

        $clave = 'ayuda-reporte:'.auth()->id();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $this->addError('descripcion', 'Ya enviaste varios reportes seguidos. Espera unos minutos o escribe al correo de soporte.');

            return;
        }

        RateLimiter::hit($clave, 600);

        $notificaciones = app(NotificacionService::class);

        $detalle = collect([
            $notificaciones->nombreActor().' reportó: «'.trim($datos['asunto']).'».',
            trim($datos['descripcion']),
            filled($datos['pagina']) ? 'Página: '.trim($datos['pagina']) : null,
            filled($datos['navegador']) ? 'Navegador: '.trim($datos['navegador']) : null,
            'Fecha: '.now()->format('d/m/Y H:i').' (UTC)',
        ])->filter()->implode("\n");

        $notificaciones->notificar(
            'usuarios',
            'Reporte de problema: '.trim($datos['asunto']),
            $detalle,
            $this->rutaInterna($datos['pagina']) ?? route('ayuda', [], false),
            $notificaciones->administradoresDeUsuarios()
        );

        $this->dispatch('notificaciones-actualizadas');
        $this->dispatch('toast', type: 'success', title: 'Reporte enviado', message: 'El equipo de soporte recibió tu reporte.');

        $this->reset(['asunto', 'descripcion']);
        $this->enviado = true;
    }

    /*
     * Solo páginas del propio sistema (ruta relativa): el enlace del
     * aviso nunca lleva a otro sitio.
     */
    private function rutaInterna(?string $url): ?string
    {
        $partes = parse_url((string) $url);

        if (! $partes || ! isset($partes['path'])) {
            return null;
        }

        if (isset($partes['host']) && $partes['host'] !== request()->getHost()) {
            return null;
        }

        return $partes['path'].(isset($partes['query']) ? '?'.$partes['query'] : '');
    }

    public function render(): View
    {
        return view('livewire.ayuda-reporte');
    }
}
