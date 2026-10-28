<?php

/*
|--------------------------------------------------------------------------
| Notificaciones push (Web Push)
|--------------------------------------------------------------------------
|
| Llaves VAPID: identifican a Track IT ante los servicios de push de
| Google, Apple y Mozilla. Se generan una sola vez con
| «php artisan trackit:llaves-push» y se copian al .env (local y Render).
|
| subject: correo (mailto:) o URL https de contacto del sistema.
|
*/

return [
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject' => env('VAPID_SUBJECT', env('APP_URL', 'https://localhost')),
];
