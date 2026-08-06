<?php
require_once __DIR__ . '/../../../backend/config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Escanear QR';
$basePath = '../';
include '../includes/header.php';
?>
<div class="page-wrapper">
    <main class="bailarin-main" style="max-width:500px;margin:0 auto;padding:20px 16px;padding-top:16px;">
        <div class="glass-card" style="padding:20px;">
            <h2 style="text-align:center;margin-bottom:16px;"><i class="fas fa-camera"></i> Escanea el CÃ³digo QR</h2>
            <p style="text-align:center;color:var(--text-secondary);margin-bottom:20px;">
                Apunta la cÃ¡mara al cÃ³digo QR que muestra el instructor
            </p>
            <div id="scanner-container"></div>
            <div id="scanResult">
                <div class="scan-result">
                    <span class="result-icon"><i class="fas fa-camera"></i></span>
                    <h2>Listo para escanear</h2>
                    <div class="result-details">
                        <p>Presiona el botÃ³n para iniciar la cÃ¡mara</p>
                    </div>
                    <button class="btn btn-primary" onclick="initScanner()" id="startBtn">Iniciar CÃ¡mara</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="../assets/js/qr-scanner.js?v=<?= filemtime(__DIR__ . '/../assets/js/qr-scanner.js') ?>"></script>

<?php include '../includes/footer.php'; ?>
