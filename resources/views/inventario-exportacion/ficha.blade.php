<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha informativa {{ $equipo['codigo'] }}</title>
    <style>
        @page { margin: 26px 33px 38px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #000000; }
        .encabezado { border-bottom: 1px solid #000000; padding-bottom: 12px; }
        .marca { color: #a68b47; font-weight: bold; letter-spacing: 2px; font-size: 15px; }
        .submarca { color: #000000; font-size: 7px; letter-spacing: 1px; }
        h1 { font-size: 17px; margin: 17px 0 5px; color: #000000;  font-weight: normal; }
        .sub { color: #000000; font-size: 8px; margin-bottom: 16px; }
        .estado { margin: 12px 0; padding: 10px; border: 1px solid #000000; background: #ffffff; }
        .estado strong { color: #000000; }
        h2 { font-size: 10px; margin: 17px 0 7px;  padding-bottom: 5px; border-bottom: 1px solid #000000;
        background: #000000; color: #ffffff; font-weight: normal;
        padding: 6px 8px;
}
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000000; padding: 5px 7px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { width: 35%;
        background: #ffffff; color: #000000; font-weight: normal;
}
        tr { page-break-inside: avoid; }
        .pie { margin-top: 18px; border-top: 1px solid #000000; padding-top: 8px; font-size: 7px; color: #000000; }
    </style>
</head>
<body>
    <div class="encabezado">
        @include('inventario-exportacion.logo')
    </div>

    @include('inventario-exportacion.detalle')


</body>
</html>
