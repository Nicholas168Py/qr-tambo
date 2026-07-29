<?php
require_once 'config/init.php';
// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: bailarin/dashboard.php');
    }
    exit;
}
$pageTitle = 'Iniciar Sesión';
$basePath = '';
include 'includes/header.php';
?>

<div class="auth-page">
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <div class="glass-card auth-card">
        <div class="logo">
            <span class="logo-icon"><i class="fas fa-music"></i></span>
            <h1>QR Tambo</h1>
            <p>Sistema de Asistencia · Clases de Baile</p>
        </div>

        <form id="loginForm" autocomplete="off">
            <div class="form-group">
                <label for="cedula">Cédula</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="cedula" name="cedula" class="form-input" placeholder="Ingresa tu cédula" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Ingresa tu contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
                Iniciar Sesión
            </button>
        </form>

        <p style="text-align: center; margin-top: 24px; color: var(--text-secondary);">
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
        </p>
    </div>
</div>

<script>
console.log('[LOGIN] Página cargada');
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const cedula = document.getElementById('cedula').value.trim();
    const password = document.getElementById('password').value;
    const btn = document.getElementById('loginBtn');
    
    if (!cedula || !password) {
        console.warn('[LOGIN] Campos incompletos');
        showToast('Completa todos los campos', 'warning');
        return;
    }

    console.log('[LOGIN] Enviando credenciales para:', cedula);
    btn.disabled = true;
    btn.textContent = 'Ingresando...';
    
    const result = await api('api/auth/login.php', 'POST', { cedula, password });
    console.log('[LOGIN] Resultado:', JSON.stringify(result).substring(0, 300));
    
    if (result.success) {
        console.log('[LOGIN] Login exitoso, redirigiendo a:', result.data.rol === 'admin' ? 'admin/dashboard.php' : 'bailarin/dashboard.php');
        showToast('¡Bienvenido, ' + result.data.nombre + '!', 'success');
        setTimeout(() => {
            if (result.data.rol === 'admin') {
                window.location.href = 'admin/dashboard.php';
            } else {
                window.location.href = 'bailarin/dashboard.php';
            }
        }, 800);
    } else {
        console.warn('[LOGIN] Login falló:', result.message);
        showToast(result.message || 'Error al iniciar sesión', 'error');
        btn.disabled = false;
        btn.textContent = 'Iniciar Sesión';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
