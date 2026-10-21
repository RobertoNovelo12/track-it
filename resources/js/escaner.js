/*
|--------------------------------------------------------------------------
| Escáner de códigos de barras (cámara del teléfono)
|--------------------------------------------------------------------------
|
| Lee los códigos de barras de los equipos (Code 128, solo números del
| código de inventario). La librería (@zxing/browser) se descarga solo la
| primera vez que se abre el escáner, para no pesar en el resto del sistema.
| Funciona en Android y iPhone; la cámara exige HTTPS.
|
|   window.trackitEscaner.iniciar(videoElement, codigo => { ... })
|   window.trackitEscaner.detener()
|
| La interfaz está en layouts/partials/escaner-movil.blade.php.
|
*/

let controles = null;

export async function iniciarEscaner(video, alLeer) {
    await detenerEscaner();

    const [{ BrowserMultiFormatOneDReader }, { BarcodeFormat, DecodeHintType }] = await Promise.all([
        import('@zxing/browser'),
        import('@zxing/library'),
    ]);

    // Solo Code 128: lectura más rápida y sin falsos positivos de otros formatos.
    const pistas = new Map([
        [DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.CODE_128]],
    ]);

    const lector = new BrowserMultiFormatOneDReader(pistas, {
        delayBetweenScanAttempts: 120,
        delayBetweenScanSuccess: 600,
    });

    controles = await lector.decodeFromConstraints(
        {
            audio: false,
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
        },
        video,
        (resultado) => {
            if (resultado) {
                alLeer(String(resultado.getText() ?? '').trim());
            }
        },
    );
}

export async function detenerEscaner() {
    try {
        controles?.stop();
    } catch (error) {
        // La cámara ya estaba detenida.
    }

    controles = null;
}

window.trackitEscaner = {
    iniciar: iniciarEscaner,
    detener: detenerEscaner,
};

// Al cambiar de página con Livewire, apagar la cámara.
document.addEventListener('livewire:navigating', () => {
    detenerEscaner();
});
