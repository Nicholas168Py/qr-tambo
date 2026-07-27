let html5QrCode = null;
let scannerRunning = false;

async function startScanner(containerId = 'scanner-container') {
    if (scannerRunning) return;

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

        return true;
    } catch (err) {
        console.error('Scanner error:', err);
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
    stopScanner();
    processQRCode(decodedText);
}

function onScanError(err) {}

async function processQRCode(url) {
    const scanResult = document.getElementById('scanResult');
    if (!scanResult) return;

    try {
        const parsedUrl = new URL(url);
        const token = parsedUrl.searchParams.get('token');
        if (!token) {
            scanResult.innerHTML = `<div class="scan-result">
                <span class="result-icon"><i class="fas fa-circle-xmark"></i></span>
                <h2>Código Inválido</h2>
                <div class="result-details"><p>Este código QR no es válido para QR Tambo.</p></div>
            </div>`;
            return;
        }

        scanResult.innerHTML = `<div class="scan-result">
            <div class="spinner" style="margin:20px auto;"></div>
            <h2>Registrando asistencia...</h2>
        </div>`;

        let apiBase = '';
        if (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/bailarin/')) {
            apiBase = '../';
        }
        const result = await api(apiBase + 'api/asistencia/registrar.php', 'POST', { token });

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
            scanResult.innerHTML = `
                <div class="scan-result">
                    <span class="result-icon">${result.message.includes('Ya registraste') ? '<i class="fas fa-circle-info"></i>' : '<i class="fas fa-circle-xmark"></i>'}</span>
                    <h2>${result.message.includes('Ya registraste') ? 'Ya Registrado' : 'Error'}</h2>
                    <div class="result-details"><p>${result.message}</p></div>
                    <button class="btn btn-primary" onclick="location.reload()">Escanear otro QR</button>
                </div>`;
        }
    } catch (e) {
        scanResult.innerHTML = `<div class="scan-result">
            <span class="result-icon"><i class="fas fa-circle-xmark"></i></span>
            <h2>Error de Conexión</h2>
            <div class="result-details"><p>No se pudo conectar con el servidor.</p></div>
            <button class="btn btn-primary" onclick="location.reload()">Intentar de nuevo</button>
        </div>`;
    }
}

async function initScanner() {
    const scannerContainer = document.getElementById('scanner-container');
    const scanResult = document.getElementById('scanResult');
    if (!scannerContainer) return;

    scannerContainer.style.display = 'block';
    scanResult.innerHTML = '<div class="scan-result"><div class="spinner" style="margin:20px auto;"></div><h2>Iniciando cámara...</h2></div>';

    const started = await startScanner('scanner-container');

    if (!started) {
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
