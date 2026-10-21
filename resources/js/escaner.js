/*
|--------------------------------------------------------------------------
| Escáner de códigos de barras (cámara del teléfono)
|--------------------------------------------------------------------------
|
| Lee los códigos de barras de los equipos (Code 128, solo números del
| código de inventario). La cámara exige HTTPS.
|
| 1. Lector nativo del navegador (BarcodeDetector, p. ej. Chrome en
|    Android): más rápido y preciso.
| 2. Si no existe (iPhone/Safari), @zxing/browser con búsqueda intensiva.
|    Esa librería se descarga solo la primera vez que se abre el escáner.
|
|   window.trackitEscaner.iniciar(videoElement, codigo => { ... })
|   window.trackitEscaner.detener()
|
| La interfaz está en layouts/partials/escaner-movil.blade.php.
|
*/

const RESTRICCIONES = {
    audio: false,
    video: {
        facingMode: { ideal: 'environment' },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
    },
};

let controles = null;      // @zxing
let flujo = null;          // cámara (lector nativo)
let temporizador = null;
let activo = false;

/*
 * Enfoque continuo cuando la cámara lo permite (mejora la lectura de
 * cerca); si no lo soporta se ignora.
 */
async function enfoqueContinuo(stream) {
    const pista = stream?.getVideoTracks?.()[0];

    try {
        const capacidades = pista?.getCapabilities?.() ?? {};

        if ((capacidades.focusMode ?? []).includes('continuous')) {
            await pista.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
        }
    } catch (error) {
        // Sin control de enfoque: se usa el automático de la cámara.
    }
}

async function lectorNativoDisponible() {
    if (! ('BarcodeDetector' in window)) {
        return false;
    }

    try {
        const formatos = await window.BarcodeDetector.getSupportedFormats();

        return formatos.includes('code_128');
    } catch (error) {
        return false;
    }
}

async function iniciarNativo(video, alLeer) {
    const detector = new window.BarcodeDetector({ formats: ['code_128'] });

    flujo = await navigator.mediaDevices.getUserMedia(RESTRICCIONES);
    video.srcObject = flujo;
    video.setAttribute('playsinline', '');
    await video.play();
    await enfoqueContinuo(flujo);

    activo = true;

    const ciclo = async () => {
        if (! activo) {
            return;
        }

        if (video.readyState >= 2) {
            try {
                const encontrados = await detector.detect(video);

                if (activo && encontrados.length > 0) {
                    alLeer(String(encontrados[0].rawValue ?? '').trim());
                }
            } catch (error) {
                // Cuadro no disponible todavía: se intenta en el siguiente.
            }
        }

        temporizador = setTimeout(ciclo, 120);
    };

    ciclo();
}

async function iniciarZxing(video, alLeer) {
    const [{ BrowserMultiFormatOneDReader }, { BarcodeFormat, DecodeHintType }] = await Promise.all([
        import('@zxing/browser'),
        import('@zxing/library'),
    ]);

    // Solo Code 128 y búsqueda intensiva (más tolerante a reflejos y pantallas).
    const pistas = new Map([
        [DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.CODE_128]],
        [DecodeHintType.TRY_HARDER, true],
    ]);

    const lector = new BrowserMultiFormatOneDReader(pistas, {
        delayBetweenScanAttempts: 100,
        delayBetweenScanSuccess: 600,
    });

    controles = await lector.decodeFromConstraints(RESTRICCIONES, video, (resultado) => {
        if (resultado) {
            alLeer(String(resultado.getText() ?? '').trim());
        }
    });

    await enfoqueContinuo(video.srcObject);
}

export async function iniciarEscaner(video, alLeer) {
    await detenerEscaner();

    if (await lectorNativoDisponible()) {
        await iniciarNativo(video, alLeer);

        return;
    }

    await iniciarZxing(video, alLeer);
}

export async function detenerEscaner() {
    activo = false;
    clearTimeout(temporizador);

    try {
        controles?.stop();
    } catch (error) {
        // La cámara ya estaba detenida.
    }

    flujo?.getTracks?.().forEach((pista) => pista.stop());

    controles = null;
    flujo = null;
}

window.trackitEscaner = {
    iniciar: iniciarEscaner,
    detener: detenerEscaner,
};

// Al cambiar de página con Livewire, apagar la cámara.
document.addEventListener('livewire:navigating', () => {
    detenerEscaner();
});
