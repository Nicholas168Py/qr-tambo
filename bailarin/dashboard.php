<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'bailarin') {
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
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:0.85rem;color:var(--text-secondary)"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
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

<script>
async function loadHistory() {
    const result = await api('../api/asistencia/list.php', 'GET');
    const container = document.getElementById('historyList');

    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-clipboard-list"></i></span><p>Aún no tienes asistencias registradas</p><p style="font-size:0.85rem;color:var(--text-muted);margin-top:4px;">Escanea un QR en tu clase para registrar asistencia.</p></div>';
        return;
    }

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

<?php include '../includes/footer.php'; ?>
