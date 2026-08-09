import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { User, UserRoundCog, Lock, Loader2, KeyRound, LogOut } from 'lucide-react';
import { api } from '../../api/client';
import { useAuth } from '../../stores/auth';
import { useToast } from '../../stores/toast';

export default function Configuracion() {
  const { user, logout, updateUser } = useAuth();
  const toast = useToast();
  const navigate = useNavigate();

  const [newUsername, setNewUsername] = useState('');
  const [usernameCurrentPassword, setUsernameCurrentPassword] = useState('');
  const [submittingUsername, setSubmittingUsername] = useState(false);

  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [submittingPassword, setSubmittingPassword] = useState(false);

  async function handleChangeUsername(e) {
    e.preventDefault();
    if (!usernameCurrentPassword || !newUsername) {
      toast.warning('Completa todos los campos');
      return;
    }
    if (newUsername === user?.cedula) {
      toast.warning('El nuevo usuario debe ser diferente al actual');
      return;
    }
    setSubmittingUsername(true);
    const result = await api('auth/cambiar-credenciales', 'POST', {
      current_password: usernameCurrentPassword,
      new_username: newUsername,
    });
    setSubmittingUsername(false);
    if (result.success) {
      toast.success('Usuario actualizado correctamente');
      updateUser(result.data);
      setNewUsername('');
      setUsernameCurrentPassword('');
    } else {
      toast.error(result.message || 'Error al cambiar usuario');
    }
  }

  async function handleChangePassword(e) {
    e.preventDefault();
    if (!currentPassword || !newPassword || !confirmPassword) {
      toast.warning('Completa todos los campos');
      return;
    }
    if (newPassword !== confirmPassword) {
      toast.warning('Las contraseñas nuevas no coinciden');
      return;
    }
    if (newPassword.length < 6) {
      toast.warning('La contraseña debe tener al menos 6 caracteres');
      return;
    }
    setSubmittingPassword(true);
    const result = await api('auth/cambiar-credenciales', 'POST', {
      current_password: currentPassword,
      new_password: newPassword,
      confirm_password: confirmPassword,
    });
    setSubmittingPassword(false);
    if (result.success) {
      toast.success('Contraseña actualizada correctamente');
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } else {
      toast.error(result.message || 'Error al cambiar contraseña');
    }
  }

  async function handleLogout() {
    await logout();
    toast.info('Sesión cerrada');
    navigate('/login');
  }

  return (
    <>
      <div className="main-header">
        <h1>Configuración</h1>
        <p>Administra tu usuario y contraseña de acceso</p>
      </div>

      <div className="glass-card">
        <h2 style={{ marginBottom: 20 }}><UserRoundCog size={20} /> Cambiar Usuario</h2>
        <p style={{ marginBottom: 20, color: 'var(--text-muted)' }}>
          Actualmente inicias sesión con el usuario: <strong>{user?.cedula}</strong>
        </p>
        <form onSubmit={handleChangeUsername}>
          <div className="form-group">
            <label htmlFor="newUsername">Nuevo Usuario</label>
            <div className="form-input-icon">
              <span className="icon"><User size={18} /></span>
              <input
                id="newUsername"
                className="form-input"
                type="text"
                placeholder="Nuevo usuario de inicio de sesión"
                value={newUsername}
                onChange={(e) => setNewUsername(e.target.value)}
              />
            </div>
          </div>
          <div className="form-group">
            <label htmlFor="usernameCurrentPassword">Contraseña Actual</label>
            <div className="form-input-icon">
              <span className="icon"><Lock size={18} /></span>
              <input
                id="usernameCurrentPassword"
                className="form-input"
                type="password"
                placeholder="Confirma tu contraseña actual"
                value={usernameCurrentPassword}
                onChange={(e) => setUsernameCurrentPassword(e.target.value)}
              />
            </div>
          </div>
          <button className="btn btn-primary btn-block" type="submit" disabled={submittingUsername}>
            {submittingUsername ? <Loader2 size={18} className="spin" /> : <UserRoundCog size={18} />}
            Actualizar Usuario
          </button>
        </form>
      </div>

      <div className="glass-card" style={{ marginTop: 20 }}>
        <h2 style={{ marginBottom: 20 }}><KeyRound size={20} /> Cambiar Contraseña</h2>
        <form onSubmit={handleChangePassword}>
          <div className="form-group">
            <label htmlFor="currentPassword">Contraseña Actual</label>
            <div className="form-input-icon">
              <span className="icon"><Lock size={18} /></span>
              <input
                id="currentPassword"
                className="form-input"
                type="password"
                placeholder="Ingresa tu contraseña actual"
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
              />
            </div>
          </div>
          <div className="form-group">
            <label htmlFor="newPassword">Nueva Contraseña</label>
            <div className="form-input-icon">
              <span className="icon"><Lock size={18} /></span>
              <input
                id="newPassword"
                className="form-input"
                type="password"
                minLength={6}
                placeholder="Mínimo 6 caracteres"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
              />
            </div>
          </div>
          <div className="form-group">
            <label htmlFor="confirmPassword">Confirmar Nueva Contraseña</label>
            <div className="form-input-icon">
              <span className="icon"><Lock size={18} /></span>
              <input
                id="confirmPassword"
                className="form-input"
                type="password"
                placeholder="Repite la nueva contraseña"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
              />
            </div>
          </div>
          <button className="btn btn-primary btn-block" type="submit" disabled={submittingPassword}>
            {submittingPassword ? <Loader2 size={18} className="spin" /> : <KeyRound size={18} />}
            Actualizar Contraseña
          </button>
        </form>
      </div>

      <button className="btn btn-danger btn-block" style={{ marginTop: 20 }} onClick={handleLogout}>
        <LogOut size={18} /> Cerrar Sesión
      </button>
    </>
  );
}