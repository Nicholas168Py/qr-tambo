<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
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
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const cedula = document.getElementById('cedula').value.trim();
    const password = document.getElementById('password').value;
    const btn = document.getElementById('loginBtn');
    
    if (!cedula || !password) {
        showToast('Completa todos los campos', 'warning');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Ingresando...';
    
    const result = await api('api/auth/login.php', 'POST', { cedula, password });
    
    if (result.success) {
        showToast('¡Bienvenido, ' + result.data.nombre + '!', 'success');
        setTimeout(() => {
            if (result.data.rol === 'admin') {
                window.location.href = 'admin/dashboard.php';
            } else {
                window.location.href = 'bailarin/dashboard.php';
            }
        }, 800);
    } else {
        showToast(result.message || 'Error al iniciar sesión', 'error');
        btn.disabled = false;
        btn.textContent = 'Iniciar Sesión';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
