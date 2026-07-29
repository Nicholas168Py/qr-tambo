<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Mi Asistencia';
$basePath = '../';
include '../includes/header.php';
?>
<div class="bailarin-layout">
    <header class="bailarin-header">
        <h2><i class="fas fa-music"></i> QR Tambo</h2>
        <div class="bailarin-header-actions" style="display:flex;align-items:center;gap:12px;">
            <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="badge badge-bailarin">Bailarín</span>
            <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
        </div>
    </header>

    <main class="bailarin-main">
        <div class="glass-card scan-card" onclick="window.location.href='escanear.php'">
            <span class="scan-icon"><i class="fas fa-camera"></i></span>
            <h2>Escanear QR</h2>
            <p>Toca aquí para escanear el código QR de tu clase</p>
        </div>

        <div class="history-section">
            <h3><i class="fas fa-clipboard-list"></i> Mi Historial de Asistencia</h3>
            <div id="historyList">
                <div style="text-align:center;padding:40px;">
                    <div class="spinner" style="margin:0 auto;"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
console.log('[BAILARIN] Dashboard cargado');
async function loadHistory() {
    console.log('[BAILARIN] Cargando historial...');
    const result = await api('../api/asistencia/list.php', 'GET');
    const container = document.getElementById('historyList');
    console.log('[BAILARIN] Historial respuesta:', JSON.stringify(result).substring(0, 300));

    if (!result.success || result.data.length === 0) {
        console.log('[BAILARIN] Sin historial');
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-clipboard-list"></i></span><p>Aún no has asistido a clases</p><p style="font-size:0.85rem;color:var(--text-muted);margin-top:4px;">Escanea un QR en tu clase para registrar tu asistencia.</p></div>';
        return;
    }

    console.log('[BAILARIN] Mostrando', result.data.length, 'registros');
    container.innerHTML = result.data.map(r => `
        <div class="glass-card history-item">
            <div>
                <div class="history-class">${r.clase}</div>
                <div class="history-date">${formatDate(r.fecha)} · ${formatTime(r.hora_registro)}</div>
            </div>
            <span style="font-size:1.2rem;"><i class="fas fa-check-circle"></i></span>
        </div>`).join('');
}

loadHistory();
</script>
