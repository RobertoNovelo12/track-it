@extends('legal.marco')

@php
    // Sin organización configurada (instalación nueva o restablecida): texto genérico.
    $org = rescue(fn () => app(\App\Services\OrganizacionService::class)->nombreConfigurado(), null, false)
        ?? 'la organización responsable de este sistema';
    $correo = config('soporte.correo');
@endphp

@section('title', 'Términos y condiciones')
@section('page-label', 'TÉRMINOS')
@section('legal-titulo', 'Términos y condiciones de uso')
@section('legal-actualizado', '15 de noviembre de 2026')
@section('legal-otra-ruta', 'legal.privacidad')
@section('legal-otra-texto', 'Aviso de privacidad')

@section('legal-cuerpo')

<p>
    Estos términos regulan el uso de <strong>Track IT</strong>, el sistema de gestión de activos tecnológicos de
    <strong>{{ $org }}</strong>. Al iniciar sesión aceptas cumplirlos.
</p>

<h2>1. Uso del sistema</h2>
<p>
    Track IT es una herramienta interna para registrar y dar seguimiento al inventario tecnológico, sus asignaciones,
    bajas. Debe usarse únicamente para fines relacionados con las actividades de {{ $org }}.
</p>

<h2>2. Cuentas y acceso</h2>
<ul>
    <li>Cada cuenta es personal e intransferible. No compartas tu usuario, contraseña ni códigos de verificación.</li>
    <li>Eres responsable de las acciones realizadas con tu cuenta. Si sospechas que alguien más la usa, cambia tu contraseña y avisa al área de sistemas.</li>
    <li>El acceso y las funciones disponibles dependen del rol que te asigne un administrador, quien puede modificarlo o desactivarlo en cualquier momento.</li>
    <li>Las cuentas de administrador deben usar verificación en dos pasos.</li>
</ul>

<h2>3. Información que registras</h2>
<ul>
    <li>Registra información veraz, completa y actualizada sobre equipos, movimientos y personas.</li>
    <li>No subas archivos con contenido ajeno a la operación, ilícito o que infrinja derechos de terceros.</li>
    <li>Los registros, actas y reportes forman parte del control interno de {{ $org }} y pueden usarse en auditorías.</li>
</ul>

<h2>4. Usos no permitidos</h2>
<ul>
    <li>Intentar acceder a cuentas, datos o funciones para los que no tienes permiso.</li>
    <li>Alterar, borrar u ocultar información para encubrir pérdidas, daños o movimientos de equipos.</li>
    <li>Interferir con el funcionamiento del sistema, probar vulnerabilidades sin autorización o automatizar accesos.</li>
    <li>Extraer información del sistema para fines distintos a los de la organización.</li>
</ul>

<h2>5. Disponibilidad</h2>
<p>
    Se procura que Track IT esté disponible de forma continua, pero puede haber interrupciones por mantenimiento, actualizaciones
    o causas ajenas. Se recomienda descargar los reportes y actas que necesites conservar.
</p>

<h2>6. Propiedad</h2>
<p>
    La información registrada pertenece a {{ $org }}. El software Track IT, su diseño y su código conservan los derechos de sus autores.
</p>

<h2>7. Suspensión</h2>
<p>
    El incumplimiento de estos términos puede dar lugar a la suspensión o cancelación de la cuenta, sin perjuicio de las medidas
    internas o legales que correspondan.
</p>

<h2>8. Datos personales</h2>
<p>
    El tratamiento de tus datos personales se describe en el <a href="{{ route('legal.privacidad') }}">aviso de privacidad</a>.
</p>

<h2>9. Cambios y contacto</h2>
<p>
    Estos términos pueden actualizarse; la versión vigente estará siempre en esta página. Para dudas escribe a
    <a href="mailto:{{ $correo }}">{{ $correo }}</a>.
</p>

@endsection
