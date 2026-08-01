<?php
require_once 'config/init.php';
// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: bailarin/escanear.php');
    }
    exit;
}
$pageTitle = 'Iniciar Sesión';
$basePath = '';
$extraCss = ['assets/css/login.css'];
include 'includes/header.php';
?>

<div class="login-scene">
    <!-- Fondo: Noise + Gradient + Aurora + Waves + Particles + Glow -->
    <div class="scene-bg">
        <div class="scene-noise"></div>
        <div class="scene-gradient"></div>
        <div class="scene-aurora" id="sceneAurora">
            <span class="aurora-blob" data-blob="1"></span>
            <span class="aurora-blob" data-blob="2"></span>
            <span class="aurora-blob" data-blob="3"></span>
        </div>
        <svg class="scene-waves" id="wavesLayer" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>
        <canvas class="scene-particles" id="particlesCanvas" aria-hidden="true"></canvas>
        <div class="scene-glow" id="mouseGlow"></div>
    </div>

    <main class="login-main">
        <div class="login-card" id="loginCard">
            <span class="card-reflection"></span>
            <span class="card-noise"></span>

            <div class="brand">
                <div class="brand-logo">
                    <img src="<?= $basePath ?>assets/logo1-white-removebg-preview.png" alt="QR Tambo">
                </div>
                <h1 class="brand-title">QR <span>TAMBO</span></h1>
                <p class="brand-subtitle">Tambo Dance Company</p>
                <p class="brand-tagline">Sistema de Asistencia · Clases de Baile</p>
            </div>

            <form id="loginForm" autocomplete="on">
                <div class="input-group">
                    <label for="cedula">Cédula</label>
                    <div class="input-control">
                        <i class="fas fa-user input-icon" aria-hidden="true"></i>
                        <input type="text" id="cedula" name="cedula" class="form-input" placeholder="Ingresa tu cédula" required autofocus autocomplete="username">
                        <span class="input-focus"></span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="input-control">
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                        <button type="button" class="input-toggle" id="passwordToggle" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                        <span class="input-focus"></span>
                    </div>
                </div>

                <div class="login-options">
                    <label class="remember-me" for="rememberMe">
                        <input type="checkbox" id="rememberMe" name="recordarme" checked>
                        Recordarme
                    </label>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-shine"></span>
                    <span class="btn-label"><i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Iniciar Sesión</span>
                </button>
            </form>

            <p class="login-footer">
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
            </p>
        </div>
    </main>
</div>

<script src="<?= $basePath ?>assets/js/login.js?v=<?= filemtime(__DIR__ . '/assets/js/login.js') ?>"></script>

<?php include 'includes/footer.php'; ?>
