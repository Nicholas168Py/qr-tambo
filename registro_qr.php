<?php
session_start();
$pageTitle = 'Registrar Asistencia';
$basePath = '';
include 'includes/header.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$loggedIn = isset($_SESSION['user_id']);
?>
<div class="auth-page">
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>
    <div class="glass-card" style="max-width:480px;width:100%;position:relative;z-index:1;text-align:center;padding:40px;" id="mainCard">
        <?php if (!$token): ?>
            <div style="padding:40px 0;">
                <div style="font-size:4rem;margin-bottom:16px;"><i class="fas fa-circle-xmark"></i></div>
                <h2>Código QR Inválido</h2>
                <p style="color:var(--text-secondary);margin-top:8px;">Este enlace no contiene un código QR válido.</p>
            </div>
        <?php elseif (!$loggedIn): ?>
            <div id="loginPrompt">
                <div style="font-size:3.5rem;margin-bottom:16px;"><i class="fas fa-lock"></i></div>
                <h2>Inicia Sesión</h2>
                <p style="color:var(--text-secondary);margin:8px 0 24px;">Debes iniciar sesión para registrar tu asistencia</p>
                <form id="qrLoginForm">
                    <div class="form-group">
                        <label for="qrCedula">Cédula</label>
                        <input type="text" id="qrCedula" class="form-input" placeholder="Tu cédula" required>
                    </div>
                    <div class="form-group">
                        <label for="qrPassword">Contraseña</label>
                        <input type="password" id="qrPassword" class="form-input" placeholder="Tu contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="qrLoginBtn">Iniciar Sesión y Registrar</button>
                </form>
                <p style="margin-top:16px;font-size:0.85rem;color:var(--text-muted);">
                    ¿No tienes cuenta? <a href="register.php">Regístrate</a>
                </p>
            </div>
        <?php else: ?>
            <div id="registeringState">
                <div class="spinner" style="margin:20px auto;"></div>
                <h2 style="margin-top:16px;">Registrando asistencia...</h2>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const QR_TOKEN = <?= json_encode($token) ?>;
const IS_LOGGED_IN = <?= $loggedIn ? 'true' : 'false' ?>;

async function registerAttendance() {
    const card = document.getElementById('mainCard');
    card.innerHTML = `
        <div class="spinner" style="margin:20px auto;"></div>
        <h2 style="margin-top:16px;">Registrando asistencia...</h2>
        <p style="color:var(--text-secondary);margin-top:8px;">Por favor espera...</p>`;

    const result = await api('api/asistencia/registrar.php', 'POST', { token: QR_TOKEN });

    if (result.success) {
        card.innerHTML = `
            <div style="padding:20px 0;">
                <div style="font-size:4rem;margin-bottom:16px;"><i class="fas fa-circle-check"></i></div>
                <h2>¡Asistencia Registrada!</h2>
                <div style="color:var(--text-secondary);margin:16px 0;">
                    <p><strong>${result.data.nombre}</strong></p>
                    <p>${result.data.clase}</p>
                    <p>${formatDate(result.data.fecha)} <i class="fas fa-circle"></i> ${formatTime(result.data.hora_registro)}</p>
                </div>
                <a href="bailarin/dashboard.php" class="btn btn-primary">Ir a Mi Panel</a>
            </div>`;
    } else {
        const isDuplicate = result.message.includes('Ya registraste');
        card.innerHTML = `
            <div style="padding:20px 0;">
                <div style="font-size:4rem;margin-bottom:16px;">${isDuplicate ? '<i class="fas fa-circle-info"></i>' : '<i class="fas fa-circle-xmark"></i>'}</div>
                <h2>${isDuplicate ? 'Ya Registrado' : 'Error'}</h2>
                <p style="color:var(--text-secondary);margin:12px 0;">${result.message}</p>
                <a href="bailarin/dashboard.php" class="btn btn-primary">Ir a Mi Panel</a>
            </div>`;
    }
}

// Handle login form
const loginForm = document.getElementById('qrLoginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const cedula = document.getElementById('qrCedula').value.trim();
        const password = document.getElementById('qrPassword').value;
        const btn = document.getElementById('qrLoginBtn');

        if (!cedula || !password) return showToast('Completa todos los campos', 'warning');

        btn.disabled = true;
        btn.textContent = 'Ingresando...';

        const result = await api('api/auth/login.php', 'POST', { cedula, password });

        if (result.success) {
            showToast('¡Bienvenido!', 'success');
            document.getElementById('loginPrompt').innerHTML = `
                <div class="spinner" style="margin:20px auto;"></div>
                <h2 style="margin-top:16px;">Registrando asistencia...</h2>`;
            setTimeout(() => registerAttendance(), 1000);
        } else {
            showToast(result.message || 'Error al iniciar sesión', 'error');
            btn.disabled = false;
            btn.textContent = 'Iniciar Sesión y Registrar';
        }
    });
}

// If already logged in, register immediately
if (IS_LOGGED_IN && QR_TOKEN) {
    registerAttendance();
}
</script>

<?php include 'includes/footer.php'; ?>
