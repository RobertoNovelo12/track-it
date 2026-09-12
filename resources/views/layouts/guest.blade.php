<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Track-IT') | Grand Palladium Hotels &amp; Resorts</title>

    {{-- Ajusta esta llamada a tus assets reales de Vite/Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Fuente Poppins. Si prefieres, muévela a app.css con @import y regístrala
         como fontFamily.sans en tailwind.config para no depender de Google Fonts. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#FFFCFF] font-['Poppins']">

    {{-- Logo del hotel: posición absoluta, así cambiar su tamaño nunca mueve el contenido --}}
    <img src="{{ asset('images/logo-grand-palladium.png') }}"
         alt="Grand Palladium Hotels & Resorts"
         class="absolute top-6 left-8 h-[164px] w-auto object-contain"
         onerror="this.style.display='none'">

    {{-- Contenido: se centra en toda la pantalla, sin depender del tamaño del logo --}}
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        @yield('content')
    </main>

</body>
</html>