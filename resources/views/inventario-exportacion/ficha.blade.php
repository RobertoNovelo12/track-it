<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha informativa {{ $equipo['codigo'] }}</title>
    <style>
        @page { margin: 26px 33px 38px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #26384b; }
        .encabezado { border-bottom: 2px solid #a68b47; padding-bottom: 12px; }
        .marca { color: #a68b47; font-weight: bold; letter-spacing: 2px; font-size: 15px; }
        .submarca { color: #708397; font-size: 7px; letter-spacing: 1px; }
        h1 { font-size: 17px; margin: 17px 0 5px; color: #192f44; }
        .sub { color: #697d90; font-size: 8px; margin-bottom: 16px; }
        .estado { margin: 12px 0; padding: 10px; border: 1px solid #cfd9e1; background: #f5f8fa; }
        .estado strong { color: #152f42; }
        h2 { font-size: 10px; margin: 17px 0 7px; color: #1f3e57; padding-bottom: 5px; border-bottom: 1px solid #d5dee5; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #d6e0e7; padding: 5px 7px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { width: 35%; color: #536a80; font-weight: bold; background: #f5f7f9; }
        tr { page-break-inside: avoid; }
        .pie { margin-top: 18px; border-top: 1px solid #d8e0e7; padding-top: 8px; font-size: 7px; color: #758593; }
    </style>
</head>
<body>
    <div class="encabezado">
        @include('inventario-exportacion.logo')
    </div>

    @include('inventario-exportacion.detalle')


</body>
</html>
