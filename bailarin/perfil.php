<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Mi Perfil';
$basePath = '../';
include '../includes/header.php';
?>
<div class="page-wrapper">
    <main class="bailarin-main" style="max-width:500px;margin:0 auto;padding:20px 16px;padding-top:16px;">
        <!-- Perfil -->
        <div class="glass-card profile-card" style="text-align:center;padding:32px 24px;margin-bottom:20px;">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--gradient-accent);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;font-weight:700;color:#06080D;">
                <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
            </div>
            <h2 style="margin-bottom:4px;"><?= htmlspecialchars($_SESSION['nombre']) ?></h2>
            <span class="badge badge-bailarin">Bailarín</span>
            <div style="margin-top:20px;text-align:left;">
                <div class="profile-info-item" style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--glass-border);">
                    <span style="color:var(--text-secondary);">Cédula</span>
                    <span style="font-weight:600;"><?= htmlspecialchars($_SESSION['cedula']) ?></span>
                </div>
                <div class="profile-info-item" style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--glass-border);">
                    <span style="color:var(--text-secondary);">Rol</span>
                    <span style="font-weight:600;">Bailarín</span>
                </div>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="glass-card" style="padding:24px;">
            <h3 style="margin-bottom:16px;"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPassword">Contraseña Actual</label>
                    <input type="password" id="currentPassword" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">Nueva Contraseña</label>
                    <input type="password" id="newPassword" class="form-input" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirmPassword" class="form-input" placeholder="Repite la contraseña" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="changePwdBtn">Actualizar Contraseña</button>
            </form>
        </div>

        <!-- Cerrar Sesión -->
        <div style="text-align:center;margin-top:24px;">
            <button class="btn btn-danger" onclick="logout()" style="width:100%;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
console.log('[PERFIL] Página cargada');

document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const btn = document.getElementById('changePwdBtn');

    if (newPassword !== confirmPassword) {
        showToast('Las contraseñas nuevas no coinciden', 'warning');
        return;
    }
    if (newPassword.length < 6) {
        showToast('La contraseña debe tener al menos 6 caracteres', 'warning');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Actualizando...';

    const result = await api('../api/auth/cambiar-password', 'POST', {
        current_password: currentPassword,
        new_password: newPassword,
        confirm_password: confirmPassword
    });

    btn.disabled = false;
    btn.textContent = 'Actualizar Contraseña';

    if (result.success) {
        showToast(result.message, 'success');
        document.getElementById('changePasswordForm').reset();
    } else {
        showToast(result.message, 'error');
    }
});
</script>
