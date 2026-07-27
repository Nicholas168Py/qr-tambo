<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: bailarin/dashboard.php');
    }
    exit;
}
$pageTitle = 'Registro';
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
            <span class="logo-icon"><i class="fas fa-user-plus"></i></span>
            <h1>Crear Cuenta</h1>
            <p>Únete como Bailarín</p>
        </div>

        <form id="registerForm" autocomplete="off">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-pen"></i></span>
                    <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Tu nombre completo" required>
                </div>
            </div>

            <div class="form-group">
                <label for="cedula">Cédula</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-id-card"></i></span>
                    <input type="text" id="cedula" name="cedula" class="form-input" placeholder="Tu número de cédula" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Crea una contraseña" required minlength="4">
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar Contraseña</label>
                <div class="form-input-icon">
                    <span class="icon"><i class="fas fa-shield"></i></span>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Repite la contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="registerBtn">
                Crear Cuenta
            </button>
        </form>

        <p style="text-align: center; margin-top: 24px; color: var(--text-secondary);">
            ¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value.trim();
    const cedula = document.getElementById('cedula').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const btn = document.getElementById('registerBtn');

    if (!nombre || !cedula || !password) {
        showToast('Completa todos los campos', 'warning');
        return;
    }

    if (password !== passwordConfirm) {
        showToast('Las contraseñas no coinciden', 'error');
        return;
    }

    if (password.length < 4) {
        showToast('La contraseña debe tener al menos 4 caracteres', 'warning');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Registrando...';

    const result = await api('api/auth/register.php', 'POST', { nombre, cedula, password });

    if (result.success) {
        showToast(result.message, 'success');
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2000);
    } else {
        showToast(result.message || 'Error al registrarse', 'error');
        btn.disabled = false;
        btn.textContent = 'Crear Cuenta';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
