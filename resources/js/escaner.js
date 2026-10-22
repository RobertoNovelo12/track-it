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
|   window.trackitEscaner.pausar()    // entre lecturas (cámara encendida)
|   window.trackitEscaner.detener()   // al cerrar
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
let activo = false;        // cámara encendida
let pausado = false;       // lectura en pausa (la cámara sigue encendida)
let videoActual = null;
let alLeerActual = null;

/*
 * Entre lecturas NO se apaga la cámara: apagarla y volver a pedirla al
 * instante falla en Android (la cámara sigue «ocupada»). Solo se pausa
 * la lectura mientras se decide qué hacer con el equipo.
 */
function entregar(codigo) {
    if (! pausado && codigo && alLeerActual) {
        alLeerActual(codigo);
    }
}

const esperar = (ms) => new Promise((resolver) => setTimeout(resolver, ms));

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

/*
 * Formatos leídos: el código de inventario es Code 128; los demás son
 * los habituales en etiquetas de número de serie (Code 39/93, EAN/UPC,
 * ITF), para poder dar de alta un equipo que todavía no está registrado.
 */
const FORMATOS_NATIVOS = ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf'];
let formatosNativos = ['code_128'];

async function lectorNativoDisponible() {
    if (! ('BarcodeDetector' in window)) {
        return false;
    }

    try {
        const formatos = await window.BarcodeDetector.getSupportedFormats();

        formatosNativos = FORMATOS_NATIVOS.filter((formato) => formatos.includes(formato));

        return formatos.includes('code_128');
    } catch (error) {
        return false;
    }
}

async function iniciarNativo(video) {
    const detector = new window.BarcodeDetector({ formats: formatosNativos });

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
                    entregar(String(encontrados[0].rawValue ?? '').trim());
                }
            } catch (error) {
                // Cuadro no disponible todavía: se intenta en el siguiente.
            }
        }

        temporizador = setTimeout(ciclo, 120);
    };

    ciclo();
}

async function iniciarZxing(video) {
    const [{ BrowserMultiFormatOneDReader }, { BarcodeFormat, DecodeHintType }] = await Promise.all([
        import('@zxing/browser'),
        import('@zxing/library'),
    ]);

    // Códigos de una dimensión habituales y búsqueda intensiva (más tolerante a reflejos y pantallas).
    const pistas = new Map([
        [DecodeHintType.POSSIBLE_FORMATS, [
            BarcodeFormat.CODE_128,
            BarcodeFormat.CODE_39,
            BarcodeFormat.CODE_93,
            BarcodeFormat.EAN_13,
            BarcodeFormat.EAN_8,
            BarcodeFormat.UPC_A,
            BarcodeFormat.UPC_E,
            BarcodeFormat.ITF,
        ]],
        [DecodeHintType.TRY_HARDER, true],
    ]);

    const lector = new BrowserMultiFormatOneDReader(pistas, {
        delayBetweenScanAttempts: 100,
        delayBetweenScanSuccess: 600,
    });

    activo = true;

    controles = await lector.decodeFromConstraints(RESTRICCIONES, video, (resultado) => {
        if (resultado) {
            entregar(String(resultado.getText() ?? '').trim());
        }
    });

    await enfoqueContinuo(video.srcObject);
}

let inicioEnCurso = null;

/*
 * Una sola apertura a la vez: si se pide de nuevo mientras la cámara se
 * está abriendo (p. ej. dos toques en «Volver a intentar»), se espera a
 * que termine la anterior. Abrirla dos veces en paralelo interrumpe el
 * video (AbortError).
 */
export async function iniciarEscaner(video, alLeer) {
    if (! (video instanceof HTMLVideoElement)) {
        throw new Error('No se encontró el elemento de video del escáner.');
    }

    if (inicioEnCurso) {
        try {
            await inicioEnCurso;
        } catch (error) {
            // La apertura anterior falló: se intenta con esta.
        }
    }

    inicioEnCurso = iniciarInterno(video, alLeer);

    try {
        await inicioEnCurso;
    } finally {
        inicioEnCurso = null;
    }
}

// Errores de permisos o de hardware: no tiene caso probar otro lector.
const ERRORES_DEFINITIVOS = ['NotAllowedError', 'NotFoundError', 'SecurityError', 'OverconstrainedError'];

async function iniciarInterno(video, alLeer) {
    alLeerActual = alLeer;
    pausado = false;

    // Ya está encendida en este mismo video: solo reanudar la lectura.
    if (activo && videoActual === video) {
        return;
    }

    await detenerEscaner();

    alLeerActual = alLeer;
    pausado = false;
    videoActual = video;

    /*
     * Lector nativo primero; si falla al iniciar (en algunos Android
     * existe pero aún no está listo), se usa @zxing automáticamente.
     */
    const encender = async () => {
        if (await lectorNativoDisponible()) {
            try {
                await iniciarNativo(video);

                return;
            } catch (error) {
                if (ERRORES_DEFINITIVOS.includes(error?.name)) {
                    throw error;
                }

                console.warn('[Track-IT] El lector nativo no inició; se usa @zxing.', error);

                await detenerEscaner();
                alLeerActual = alLeer;
                videoActual = video;
            }
        }

        await iniciarZxing(video);
    };

    try {
        await encender();
    } catch (error) {
        // Cámara aún ocupada o reproducción interrumpida: un reintento.
        if (! ['NotReadableError', 'AbortError', 'TrackStartError'].includes(error?.name)) {
            throw error;
        }

        await detenerEscaner();
        await esperar(400);

        alLeerActual = alLeer;
        videoActual = video;
        await encender();
    }
}

/* Pausa la lectura sin apagar la cámara. */
export function pausarEscaner() {
    pausado = true;
}

export async function detenerEscaner() {
    activo = false;
    pausado = false;
    clearTimeout(temporizador);

    try {
        controles?.stop();
    } catch (error) {
        // La cámara ya estaba detenida.
    }

    flujo?.getTracks?.().forEach((pista) => pista.stop());

    if (videoActual) {
        videoActual.srcObject?.getTracks?.().forEach((pista) => pista.stop());
        videoActual.srcObject = null;
    }

    controles = null;
    flujo = null;
    videoActual = null;
}

window.trackitEscaner = {
    iniciar: iniciarEscaner,
    pausar: pausarEscaner,
    detener: detenerEscaner,
};

// Al cambiar de página con Livewire, apagar la cámara.
document.addEventListener('livewire:navigating', () => {
    detenerEscaner();
});
