<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'QR del Día';
$basePath = '../';
include '../includes/header.php';
?>
<div class="admin-layout">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h2><i class="fas fa-music"></i> QR Tambo</h2>
            <small>Panel de Administración</small>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Panel Principal
            </a>
            <button class="nav-link active" onclick="window.location.href='qr_dia.php'">
                <span class="nav-icon"><i class="fas fa-qrcode"></i></span> QR del Día
            </button>
            <button class="nav-link" onclick="selectDayWithNav(1, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Lunes</button>
            <button class="nav-link" onclick="selectDayWithNav(2, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Martes</button>
            <button class="nav-link" onclick="selectDayWithNav(3, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Miércoles</button>
            <button class="nav-link" onclick="selectDayWithNav(4, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Jueves</button>
            <button class="nav-link" onclick="selectDayWithNav(5, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Viernes</button>
            <button class="nav-link" onclick="selectDayWithNav(6, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Sábado</button>
            <button class="nav-link" onclick="selectDayWithNav(7, this)"><span class="nav-icon"><i class="fas fa-sun"></i></span> Domingo</button>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">A</div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['nombre']) ?></div>
                    <div class="user-role"><span class="badge badge-admin">Admin</span></div>
                </div>
            </div>
            <button class="btn btn-secondary btn-block btn-sm" onclick="logout()">Cerrar Sesión</button>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <h1><i class="fas fa-qrcode"></i> QR del Día</h1>
            <p id="dayStatus">Genera códigos QR para las clases de hoy</p>
        </div>

        <div class="day-selector" id="daySelector"></div>

        <div class="qr-section" id="qrDisplay">
            <div style="text-align:center;padding:80px 20px;">
                <div class="spinner" style="margin:20px auto;"></div>
                <p style="color:var(--text-secondary);margin-top:16px;">Cargando clases del día...</p>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="../assets/js/qr-generator.js"></script>
<?php include '../includes/footer.php'; ?>

<script>
console.log('[QR_ADMIN] Página QR del día cargada');
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function selectDayWithNav(day, btn) {
    console.log('[QR_ADMIN] selectDayWithNav:', day);
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(n => n.classList.remove('active'));
    if (btn) btn.classList.add('active');
    selectDay(day);
}

function selectDay(day) {
    console.log('[QR_ADMIN] selectDay:', day);
    document.querySelectorAll('.day-pill').forEach(p => p.classList.remove('active'));
    document.querySelector(`.day-pill[data-day="${day}"]`)?.classList.add('active');
    loadAndRenderDay(day);
}

async function loadAndRenderDay(day) {
    console.log('[QR_ADMIN] loadAndRenderDay:', day);
    document.getElementById('qrDisplay').innerHTML = `
        <div style="text-align:center;padding:80px 20px;">
            <div class="spinner" style="margin:20px auto;"></div>
            <p style="color:var(--text-secondary);margin-top:16px;">Cargando clases...</p>
        </div>`;
    const success = await loadDayClasses(day);
    console.log('[QR_ADMIN] loadDayClasses result:', success, 'clases:', dayClasses.length);
    if (success && dayClasses.length > 0) {
        currentClassIndex = 0;
        renderQR();
    } else {
        document.getElementById('qrDisplay').innerHTML = `
            <div class="qr-empty" style="padding:80px 20px;">
                <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
                <h2 style="margin-bottom:12px;">No hay clases para este día</h2>
                <p>No hay clases programadas para ${getDayName(day)}.</p>
                <p style="margin-top:8px;font-size:0.85rem;color:var(--text-muted);">
                    Ve a <a href="dashboard.php">Panel <i class="fas fa-arrow-right"></i> Horarios</a> para configurar el horario.
                </p>
            </div>`;
    }
}

// Init day selector pills
function initDayPills() {
    const container = document.getElementById('daySelector');
    const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    const today = getCurrentDayNumber();
    container.innerHTML = days.map((d, i) =>
        `<button class="day-pill ${i + 1 === today ? 'active' : ''}" data-day="${i + 1}" onclick="selectDay(${i + 1})">${d}</button>`
    ).join('');
    console.log('[QR_ADMIN] Day pills initialized, today:', today);
}

initDayPills();
console.log('[QR_ADMIN] Cargando día actual...');
loadAndRenderDay(getCurrentDayNumber());
</script>
