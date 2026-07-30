    <div id="toast-container"></div>
    <div id="loading-overlay" class="loading-overlay" style="display:none">
        <div class="spinner"></div>
    </div>
    <div id="debug-panel" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#1a0a2e;color:#0f0;font-family:monospace;font-size:11px;padding:8px;max-height:40vh;overflow-y:auto;border-top:2px solid #f0f;padding-bottom:calc(var(--bottom-nav-height) + 8px);">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <strong style="color:#fff;">🐛 DEBUG</strong>
            <button onclick="var p=document.getElementById('debug-panel');p.style.display='none'" style="background:none;border:none;color:#f66;cursor:pointer;font-size:14px;">✕</button>
        </div>
        <div id="debug-logs" style="white-space:pre-wrap;word-break:break-all;"></div>
    </div>

<?php
$loggedInFooter = isset($_SESSION['user_id']);
$rolFooter = $loggedInFooter ? ($_SESSION['rol'] ?? '') : '';
if ($loggedInFooter):
    $isAdminFooter = $rolFooter === 'admin';
    // Use $basePath if set by the including page, otherwise determine from role dir
    if (!isset($basePath)) {
        $basePath = $isAdminFooter ? '' : '../';
    }
?>
<!-- Bottom Navigation -->
<nav class="bottom-nav" id="bottomNav">
    <?php if ($isAdminFooter): ?>
    <a class="nav-item" href="<?= $basePath ?>admin/qr_dia.php" data-nav="qr">
        <span class="nav-icon"><i class="fas fa-qrcode"></i></span>
        <span class="nav-label">QR del Día</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>admin/dashboard.php?section=horarios" data-nav="horarios">
        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
        <span class="nav-label">Horarios</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>admin/dashboard.php?section=asistencia" data-nav="asistencia">
        <span class="nav-icon"><i class="fas fa-check-circle"></i></span>
        <span class="nav-label">Asistencia</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>admin/dashboard.php?section=clases" data-nav="clases">
        <span class="nav-icon"><i class="fas fa-music"></i></span>
        <span class="nav-label">Clases</span>
    </a>
    <?php else: ?>
    <a class="nav-item" href="<?= $basePath ?>bailarin/escanear.php" data-nav="scan">
        <span class="nav-icon"><i class="fas fa-camera"></i></span>
        <span class="nav-label">Leer QR</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>bailarin/horarios.php" data-nav="horarios">
        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
        <span class="nav-label">Horarios</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>bailarin/dashboard.php" data-nav="asistencias">
        <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
        <span class="nav-label">Mis Asistencias</span>
    </a>
    <a class="nav-item" href="<?= $basePath ?>bailarin/perfil.php" data-nav="perfil">
        <span class="nav-icon"><i class="fas fa-user"></i></span>
        <span class="nav-label">Perfil</span>
    </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

    <script src="<?= $basePath ?>assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var navItems = document.querySelectorAll('.bottom-nav .nav-item');
    if (navItems.length) {
        var currentPath = window.location.pathname;
        navItems.forEach(function(item) {
            var href = item.getAttribute('href');
            if (href && currentPath.indexOf(href.replace('../', '').replace('./', '')) !== -1) {
                item.classList.add('active');
            }
        });
    }
});
</script>
</body>
</html>
