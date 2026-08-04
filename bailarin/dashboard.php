<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Mis Asistencias';
$basePath = '../';
include '../includes/header.php';
?>
<div class="page-wrapper">
    <main class="bailarin-main" style="max-width:500px;margin:0 auto;padding:20px 16px;padding-top:16px;">
        <div style="margin-bottom:20px;">
            <h2 style="font-size:1.2rem;"><i class="fas fa-clipboard-list"></i> Mi Historial de Asistencia</h2>
        </div>

        <!-- Date Filter -->
        <div class="glass-card" style="padding:12px;margin-bottom:16px;">
            <div style="display:flex;gap:8px;align-items:center;">
                <div style="flex:1;">
                    <input type="date" id="filterDate" class="form-input" style="padding:8px 10px;font-size:0.82rem;">
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadHistory()" style="padding:8px 14px;"><i class="fas fa-search"></i></button>
                <button class="btn btn-secondary btn-sm" onclick="clearFilter()" style="padding:8px 14px;"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <!-- History List -->
        <div id="historyList">
            <div style="text-align:center;padding:40px;">
                <div class="spinner" style="margin:0 auto;"></div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
console.log('[BAILARIN] Dashboard cargado');

async function loadHistory() {
    console.log('[BAILARIN] Cargando historial...');
    const dateFilter = document.getElementById('filterDate').value;
    let endpoint = '../api/asistencia';
    if (dateFilter) {
        const d = new Date(dateFilter);
        endpoint += `?mes=${d.getMonth() + 1}&anio=${d.getFullYear()}`;
    }
    const result = await api(endpoint, 'GET');
    const container = document.getElementById('historyList');

    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state" style="padding:40px 16px;"><span class="empty-icon"><i class="fas fa-clipboard-list"></i></span><p>Aún no has asistido a clases</p><p style="font-size:0.85rem;color:var(--text-muted);margin-top:4px;">Escanea un QR en tu clase para registrar tu asistencia.</p></div>';
        return;
    }

    container.innerHTML = result.data.map(r => `
        <div class="glass-card" style="padding:14px 16px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:600;font-size:0.92rem;">${r.clase}</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">${formatDate(r.fecha)} · ${formatTime(r.hora_registro)}</div>
            </div>
            <span style="font-size:1.2rem;color:var(--success);"><i class="fas fa-check-circle"></i></span>
        </div>`).join('');
}

function clearFilter() {
    document.getElementById('filterDate').value = '';
    loadHistory();
}

// Set default date to today
const today = new Date();
document.getElementById('filterDate').value = today.toISOString().split('T')[0];

loadHistory();
</script>
