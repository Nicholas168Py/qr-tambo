<?php
if (!isset($pageTitle)) $pageTitle = 'QR Tambo';
if (!isset($basePath)) $basePath = '';
$loggedIn = isset($_SESSION['user_id']);
$rol = $loggedIn ? ($_SESSION['rol'] ?? '') : '';
$nombre = $loggedIn ? ($_SESSION['nombre'] ?? 'U') : '';
$initial = $loggedIn ? strtoupper(substr($nombre, 0, 1)) : 'U';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Sistema de Asistencia QR para Clases de Baile - QR Tambo">
    <title><?= htmlspecialchars($pageTitle) ?> | QR Tambo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/responsive.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsive.css') ?>">
    <?php if (!empty($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $cssFile): ?>
            <?php if (file_exists(__DIR__ . '/../' . $cssFile)): ?>
    <link rel="stylesheet" href="<?= $basePath ?><?= $cssFile ?>?v=<?= filemtime(__DIR__ . '/../' . $cssFile) ?>">
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php if ($loggedIn): ?>
<?php
  $isAdmin = $rol === 'admin';
  $adminPage = $isAdmin && strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
  $bailarinPage = !$isAdmin && strpos($_SERVER['SCRIPT_NAME'], '/bailarin/') !== false;
?>
<!-- Top Bar -->
<header class="top-bar" id="topBar">
    <div class="top-bar-left">
        <?php if ($isAdmin && $adminPage): ?>
        <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Menú">
            <i class="fas fa-bars"></i>
        </button>
        <?php endif; ?>
        <span class="top-bar-logo"><img src="<?= $basePath ?>assets/logo1-white-removebg-preview.png" alt="QR Tambo" class="top-bar-logo-img"> QR TAMBO</span>
    </div>
    <div class="top-bar-avatar" title="<?= htmlspecialchars($nombre) ?>" onclick="window.location.href='<?= $basePath . ($isAdmin ? 'admin/dashboard.php' : 'bailarin/dashboard.php') ?>'">
        <?= $initial ?>
    </div>
</header>
<?php endif; ?>
