let qrInstance = null;
let currentClassIndex = 0;
let dayClasses = [];
let currentDayData = {};
let isGenerating = false;

function getApiBase() {
    let base = '';
    if (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/bailarin/')) {
        base = '../';
    }
    return base;
}

async function loadDayClasses(dayNum) {
    const result = await api(getApiBase() + `api/qr/get_clases_dia.php?dia=${dayNum}`, 'GET');
    if (result.success) {
        dayClasses = result.data;
        currentDayData = {
            dia: result.dia,
            dia_nombre: result.dia_nombre,
            fecha: result.fecha,
            total: result.total
        };
        currentClassIndex = 0;
        return true;
    }
    return false;
}

function generateQRContent(clase, fecha, horaInicio) {
    const token = generateToken();
    const data = {
        clase: clase.nombre,
        fecha: fecha,
        hora: horaInicio,
        token: token,
        horario_id: clase.id
    };
    return { raw: data, encoded: btoa(JSON.stringify(data)) };
}

function getQrSize() {
    const vw = window.innerWidth;
    if (vw <= 360) return 150;
    if (vw <= 480) return 180;
    return 220;
}

function renderQR() {
    if (dayClasses.length === 0) {
        document.getElementById('qrDisplay').innerHTML = `
            <div class="qr-empty">
                <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
                <p>No hay clases programadas para este día</p>
            </div>`;
        return;
    }

    const clase = dayClasses[currentClassIndex];
    const { encoded } = generateQRContent(clase, currentDayData.fecha, clase.hora_inicio);
    const total = dayClasses.length;
    const current = currentClassIndex + 1;
    const percent = Math.round((current / total) * 100);

    const url = `${window.location.origin}${window.location.pathname.replace('admin/qr_dia.php', 'registro_qr.php')}?token=${encodeURIComponent(encoded)}`;

    document.getElementById('qrDisplay').innerHTML = `
        <div class="qr-date">${currentDayData.dia_nombre}, ${formatDate(currentDayData.fecha)}</div>
        <div class="glass-card qr-card">
            <div class="qr-class-name">${clase.clase_nombre}</div>
            <div class="qr-class-time">${formatTime(clase.hora_inicio)} - ${formatTime(clase.hora_fin)}</div>
            <div class="qr-container" id="qrCode"></div>
            <div class="qr-progress">
                Clase ${current} de ${total}
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: ${percent}%"></div>
                </div>
            </div>
        </div>
        <div class="qr-nav-btn">
            ${currentClassIndex < dayClasses.length - 1
                ? `<button class="btn btn-primary" onclick="nextClass()" id="nextBtn">
                       Siguiente Clase <i class="fas fa-arrow-right"></i>
                   </button>`
                : `<button class="btn btn-success" onclick="finishDay()" id="nextBtn">
                       <i class="fas fa-check-circle"></i> Finalizar Día
                   </button>`
            }
        </div>
    `;

    const qrSize = getQrSize();
    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = '';
    qrInstance = new QRCode(qrContainer, {
        text: url,
        width: qrSize,
        height: qrSize,
        colorDark: '#1a0a2e',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    updateStatusBar();
}

function nextClass() {
    if (currentClassIndex < dayClasses.length - 1) {
        currentClassIndex++;
        renderQR();
    }
}

function finishDay() {
    const btn = document.getElementById('nextBtn');
    btn.disabled = true;
    btn.textContent = '<i class="fas fa-check-circle"></i> Día Completado';
    showToast('¡Día finalizado! Todas las clases han sido generadas.', 'success');

    document.getElementById('qrDisplay').innerHTML = `
        <div class="glass-card" style="text-align:center;padding:60px 20px;">
            <div style="font-size:4rem;margin-bottom:16px;"><i class="fas fa-star"></i></div>
            <h2>¡Día Completado!</h2>
            <p style="color:var(--text-secondary);margin-top:8px;">
                ${currentDayData.dia_nombre} ${currentDayData.fecha} - ${dayClasses.length} clases
            </p>
            <div style="margin-top:24px;">
                <button class="btn btn-primary" onclick="initPage()">Generar otro día</button>
            </div>
        </div>`;
}

function updateStatusBar() {
    const total = dayClasses.length;
    const current = currentClassIndex + 1;
    const statusEl = document.getElementById('dayStatus');
    if (statusEl) {
        statusEl.textContent = `Clase ${current} de ${total} - ${currentDayData.dia_nombre} ${currentDayData.fecha}`;
    }
}

async function initPage() {
    const dayNum = getCurrentDayNumber();
    const success = await loadDayClasses(dayNum);
    if (success && dayClasses.length > 0) {
        currentClassIndex = 0;
        renderQR();
    } else {
        document.getElementById('qrDisplay').innerHTML = `
            <div class="qr-empty" style="padding:80px 20px;">
                <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
                <h2 style="margin-bottom:12px;">No hay clases hoy</h2>
                <p>No hay clases programadas para ${getDayName(dayNum)}.</p>
                <p style="margin-top:8px;font-size:0.85rem;color:var(--text-muted);">
                    Ve a <a href="dashboard.php">Panel <i class="fas fa-arrow-right"></i> Horarios</a> para configurar el horario semanal.
                </p>
            </div>`;
    }
}
