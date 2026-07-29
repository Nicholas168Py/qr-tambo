<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Escanear QR';
$basePath = '../';
include '../includes/header.php';
?>
<div class="bailarin-layout">
    <header class="bailarin-header">
        <h2><i class="fas fa-music"></i> QR Tambo</h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:0.85rem;color:var(--text-secondary)"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="badge badge-bailarin">Bailarín</span>
            <button class="btn btn-secondary btn-sm" onclick="window.location.href='dashboard.php'"><i class="fas fa-arrow-left"></i> Volver</button>
        </div>
    </header>

    <main class="bailarin-main">
        <div class="glass-card" style="padding:24px;">
            <h2 style="text-align:center;margin-bottom:20px;"><i class="fas fa-camera"></i> Escanea el Código QR</h2>
            <p style="text-align:center;color:var(--text-secondary);margin-bottom:20px;">
            Apunta la cámara al código QR que muestra el instructor
            </p>
            <div id="scanner-container"></div>
            <div id="scanResult">
                <div class="scan-result">
                    <span class="result-icon"><i class="fas fa-camera"></i></span>
                    <h2>Listo para escanear</h2>
                    <div class="result-details">
                        <p>Presiona el botón para iniciar la cámara</p>
                    </div>
                    <button class="btn btn-primary" onclick="initScanner()" id="startBtn">Iniciar Cámara</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="../assets/js/qr-scanner.js"></script>

<?php include '../includes/footer.php'; ?>
