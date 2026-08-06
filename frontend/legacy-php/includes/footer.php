    <div id="toast-container"></div>
    <div id="loading-overlay" class="loading-overlay" style="display:none">
        <div class="spinner"></div>
    </div>
    <div id="debug-panel" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#06080D;color:#0f0;font-family:monospace;font-size:11px;padding:8px;max-height:40vh;overflow-y:auto;border-top:2px solid #07C7F2;padding-bottom:calc(var(--bottom-nav-height) + 8px);">
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
    if (!navItems.length) return;

    // El menú hamburguesa solo debe abrirse con su botón:
    // al tocar un ítem del menú inferior, si el sidebar está abierto, se cierra.
    navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            closeSidebarIfOpen();
        });
    });

    // Resaltar el ítem activo según ruta + sección (?section=...)
    var currentPath = window.location.pathname;
    var currentSection = new URLSearchParams(window.location.search).get('section');

    navItems.forEach(function(item) {
        var href = item.getAttribute('href') || '';
        var cleanHref = href.replace('../', '').replace('./', '').split('?')[0];
        if (cleanHref && currentPath.indexOf(cleanHref) !== -1) {
            var itemSection = new URLSearchParams(href.split('?')[1] || '').get('section');
            if (itemSection === currentSection || (itemSection === null && currentSection === null)) {
                item.classList.add('active');
            }
        }
    });
});
</script>
</body>
</html>
