let html5QrCode = null;
let scannerRunning = false;

async function startScanner(containerId = 'scanner-container') {
    if (scannerRunning) {
        console.log('[SCANNER] Ya está ejecutándose');
        return;
    }

    console.log('[SCANNER] Iniciando cámara...');
    try {
        html5QrCode = new Html5Qrcode(containerId);
        scannerRunning = true;

        const config = {
            fps: 15,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        await html5QrCode.start(
            { facingMode: 'environment' },
            config,
            onScanSuccess,
            onScanError
        );

        console.log('[SCANNER] Cámara iniciada correctamente');
        return true;
    } catch (err) {
        console.error('[SCANNER] Error al iniciar cámara:', err.message || err);
        scannerRunning = false;
        return false;
    }
}

async function stopScanner() {
    if (html5QrCode && scannerRunning) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (e) {}
        scannerRunning = false;
    }
}

function onScanSuccess(decodedText) {
    console.log('[SCANNER] QR detectado:', decodedText.substring(0, 100));
    stopScanner();
    processQRCode(decodedText);
}

function onScanError(err) {}

async function processQRCode(url) {
    console.log('[SCANNER] Procesando QR:', url.substring(0, 150));
    const scanResult = document.getElementById('scanResult');
    if (!scanResult) {
        console.error('[SCANNER] #scanResult no encontrado');
        return;
    }

    try {
        const parsedUrl = new URL(url);
        const token = parsedUrl.searchParams.get('token');
        if (!token) {
            console.error('[SCANNER] Token no encontrado en URL');
            scanResult.innerHTML = `<div class="scan-result">
                <span class="result-icon"><i class="fas fa-circle-xmark"></i></span>
                <h2>Código Inválido</h2>
                <div class="result-details"><p>Este código QR no es válido para QR Tambo.</p></div>
            </div>`;
            return;
        }

        console.log('[SCANNER] Token extraído:', token.substring(0, 50));

        scanResult.innerHTML = `<div class="scan-result">
            <div class="spinner" style="margin:20px auto;"></div>
            <h2>Registrando asistencia...</h2>
        </div>`;

        let apiBase = '';
        if (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/bailarin/')) {
            apiBase = '../';
        }
        const endpoint = apiBase + 'api/asistencia/registrar.php';
        console.log('[SCANNER] Llamando API:', endpoint);
        const result = await api(endpoint, 'POST', { token });
        console.log('[SCANNER] Respuesta API:', JSON.stringify(result).substring(0, 300));

        if (result.success) {
            scanResult.innerHTML = `
                <div class="scan-result">
                    <span class="result-icon"><i class="fas fa-circle-check"></i></span>
                    <h2>¡Asistencia Registrada!</h2>
                    <div class="result-details">
                        <p>${result.data.nombre}</p>
                        <p>${result.data.clase}</p>
                        <p>${formatDate(result.data.fecha)} - ${formatTime(result.data.hora_registro)}</p>
                    </div>
                    <button class="btn btn-primary" onclick="location.reload()">Escanear otro QR</button>
                </div>`;
        } else {
            const isDuplicate = result.message && result.message.includes('Ya registraste');
            console.log('[SCANNER] Error:', result.message, 'Código:', result.code);
            scanResult.innerHTML = `
                <div class="scan-result">
                    <span class="result-icon">${isDuplicate ? '<i class="fas fa-circle-info"></i>' : '<i class="fas fa-circle-xmark"></i>'}</span>
                    <h2>${isDuplicate ? 'Ya Registrado' : 'Error'}</h2>
                    <div class="result-details">
                        <p>${result.message || 'Error desconocido'}</p>
                        <p style="margin-top:8px;font-size:0.75rem;color:var(--text-muted);">Código: ${result.code || ''}</p>
                    </div>
                    <button class="btn btn-primary" onclick="location.reload()">Escanear otro QR</button>
                </div>`;
        }
    } catch (e) {
        console.error('[SCANNER] processQRCode error:', e.message || e);
        const errorMsg = e.message || '';
        scanResult.innerHTML = `<div class="scan-result">
            <span class="result-icon"><i class="fas fa-circle-xmark"></i></span>
            <h2>Error de Conexión</h2>
            <div class="result-details">
                <p>No se pudo conectar con el servidor.</p>
                <p style="margin-top:8px;font-size:0.75rem;color:var(--text-muted);">${errorMsg}</p>
            </div>
            <button class="btn btn-primary" onclick="location.reload()">Intentar de nuevo</button>
        </div>`;
    }
}

async function initScanner() {
    console.log('[SCANNER] initScanner() llamado');
    const scannerContainer = document.getElementById('scanner-container');
    const scanResult = document.getElementById('scanResult');
    if (!scannerContainer) {
        console.error('[SCANNER] #scanner-container no encontrado');
        return;
    }

    scannerContainer.style.display = 'block';
    scanResult.innerHTML = '<div class="scan-result"><div class="spinner" style="margin:20px auto;"></div><h2>Iniciando cámara...</h2></div>';

    console.log('[SCANNER] startScanner...');
    const started = await startScanner('scanner-container');

    if (!started) {
        console.error('[SCANNER] No se pudo iniciar la cámara');
        scanResult.innerHTML = `<div class="scan-result">
            <span class="result-icon"><i class="fas fa-camera"></i></span>
            <h2>Permiso de Cámara Requerido</h2>
            <div class="result-details">
                <p>QR Tambo necesita acceso a tu cámara para escanear códigos QR.</p>
                <p style="margin-top:8px;font-size:0.85rem;color:var(--text-muted);">
                    Permite el acceso cuando el navegador lo solicite.
                </p>
            </div>
            <button class="btn btn-primary" onclick="initScanner()" style="margin-top:16px;">Intentar de nuevo</button>
        </div>`;
    }
}
