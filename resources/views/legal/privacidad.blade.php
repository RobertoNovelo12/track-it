@extends('legal.marco')

@php
    $org = rescue(fn () => app(\App\Services\OrganizacionService::class)->nombre(), \App\Services\OrganizacionService::NOMBRE_PREDETERMINADO, false);
    $correo = config('soporte.correo');
@endphp

@section('title', 'Aviso de privacidad')
@section('page-label', 'PRIVACIDAD')
@section('legal-titulo', 'Aviso de privacidad')
@section('legal-actualizado', '15 de noviembre de 2026')
@section('legal-otra-ruta', 'legal.terminos')
@section('legal-otra-texto', 'Términos y condiciones de uso')

@section('legal-cuerpo')

<p>
    Este aviso explica qué datos personales se tratan en <strong>Track IT</strong>, el sistema de gestión de activos
    tecnológicos que utiliza <strong>{{ $org }}</strong>, para qué se usan y cómo puedes ejercer tus derechos.
</p>

<h2>1. Responsable</h2>
<p>
    El responsable del tratamiento de tus datos es <strong>{{ $org }}</strong>, por medio del área encargada de administrar el sistema.
    Para cualquier asunto relacionado con este aviso puedes escribir a <a href="mailto:{{ $correo }}">{{ $correo }}</a>.
</p>

<h2>2. Datos que se tratan</h2>
<ul>
    <li><strong>Identificación y contacto:</strong> nombre, apellidos, nombre de usuario, correo electrónico, teléfono y número de colaborador.</li>
    <li><strong>Datos laborales:</strong> puesto, departamento, edificio o unidad y sede a la que perteneces.</li>
    <li><strong>Datos de acceso y seguridad:</strong> contraseña (guardada cifrada, nunca en texto plano), configuración de la verificación en dos pasos, fecha de nacimiento usada como pregunta de seguridad y fecha del último acceso.</li>
    <li><strong>Datos de uso:</strong> dirección IP, tipo de dispositivo y navegador de tus sesiones, y el registro de acciones realizadas en el sistema (bitácora).</li>
    <li><strong>Datos de operación:</strong> equipos asignados a tu nombre, movimientos, mantenimientos, bajas, firmas y evidencias que se adjunten en actas y reportes.</li>
</ul>
<p>No se solicitan datos personales sensibles.</p>

<h2>3. Para qué se usan</h2>
<ul>
    <li>Crear y administrar tu cuenta y verificar tu identidad al iniciar sesión.</li>
    <li>Registrar el inventario tecnológico, las asignaciones de equipos, los mantenimientos y las bajas.</li>
    <li>Generar actas, reportes y documentos de entrega y recepción de equipos.</li>
    <li>Enviarte notificaciones del sistema (en la aplicación, por correo o notificaciones push si las activas).</li>
    <li>Proteger la seguridad del sistema, detectar accesos no autorizados y mantener un historial de auditoría.</li>
    <li>Atender solicitudes de soporte y reportes de problemas.</li>
</ul>

<h2>4. Cookies y almacenamiento en el navegador</h2>
<p>
    Track IT usa cookies necesarias para mantener tu sesión abierta y proteger los formularios, y guarda en tu navegador
    preferencias como el tema (claro u oscuro) y la última pantalla visitada. No se usan cookies de publicidad ni de rastreo de terceros.
</p>

<h2>5. Con quién se comparten</h2>
<p>
    Tus datos solo los consultan las personas autorizadas de {{ $org }} según su rol en el sistema. Para funcionar, Track IT se
    aloja con proveedores de infraestructura (servidores y base de datos) que procesan la información únicamente por cuenta del
    responsable. Fuera de estos casos, los datos no se venden ni se comparten con terceros, salvo que lo exija una autoridad
    competente conforme a la ley.
</p>

<h2>6. Cuánto tiempo se conservan</h2>
<p>
    Los datos se conservan mientras tu cuenta esté activa y durante el tiempo necesario para cumplir con las obligaciones de
    control de inventario y auditoría de {{ $org }}. Los elementos eliminados permanecen 30 días en la papelera antes de borrarse
    definitivamente.
</p>

<h2>7. Cómo se protegen</h2>
<p>
    Se aplican medidas como contraseñas cifradas, verificación en dos pasos (obligatoria para administradores), permisos por rol,
    conexiones cifradas, cierre automático de sesiones y registro de actividad.
</p>

<h2>8. Tus derechos</h2>
<p>
    Puedes solicitar el <strong>acceso</strong> a tus datos, su <strong>rectificación</strong> si son incorrectos, su
    <strong>cancelación</strong> o <strong>oponerte</strong> a su uso, así como revocar tu consentimiento, conforme a la legislación de
    protección de datos personales aplicable. Escribe a <a href="mailto:{{ $correo }}">{{ $correo }}</a> indicando tu nombre, el
    derecho que deseas ejercer y los datos a los que se refiere. Algunos datos de tu perfil también los puedes actualizar desde
    <em>Mi cuenta</em>.
</p>

<h2>9. Cambios a este aviso</h2>
<p>
    Este aviso puede actualizarse. La versión vigente estará siempre disponible en esta página, con su fecha de última actualización.
</p>

@endsection
