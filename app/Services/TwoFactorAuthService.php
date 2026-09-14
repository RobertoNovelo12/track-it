<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    private Google2FA $google2fa;


    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }


    /*
    |--------------------------------------------------------------------------
    | Generar secreto TOTP
    |--------------------------------------------------------------------------
    */

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }


    /*
    |--------------------------------------------------------------------------
    | Generar QR
    |--------------------------------------------------------------------------
    */

    public function generateQrCode(
        User $user,
        string $secret
    ): string {
        $issuer = config(
            'app.name',
            'Track-It'
        );

        $account = $user->correo
            ?: $user->username;


        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $issuer,
            $account,
            $secret
        );


        $renderer = new ImageRenderer(
            new RendererStyle(240),
            new SvgImageBackEnd()
        );


        $writer = new Writer(
            $renderer
        );


        $svg = $writer->writeString(
            $qrCodeUrl
        );


        return 'data:image/svg+xml;base64,'
            . base64_encode($svg);
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar código TOTP
    |--------------------------------------------------------------------------
    */

    public function verifyCode(
        string $secret,
        string $code
    ): bool {
        return $this->google2fa->verifyKey(
            $secret,
            $code
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Formatear clave para mostrarla manualmente
    |--------------------------------------------------------------------------
    */

    public function formatSecret(
        string $secret
    ): string {
        return trim(
            chunk_split(
                $secret,
                4,
                ' '
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generar códigos de recuperación
    |--------------------------------------------------------------------------
    */

    public function generateRecoveryCodes(
        int $count = 8
    ): array {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $raw = strtoupper(
                bin2hex(
                    random_bytes(5)
                )
            );

            $codes[] =
                substr($raw, 0, 5)
                . '-'
                . substr($raw, 5, 5);
        }

        return $codes;
    }


    /*
    |--------------------------------------------------------------------------
    | Hashear códigos de recuperación
    |--------------------------------------------------------------------------
    */

    public function hashRecoveryCodes(
        array $codes
    ): array {
        return array_map(
            fn (string $code) =>
                Hash::make($code),
            $codes
        );
    }
}