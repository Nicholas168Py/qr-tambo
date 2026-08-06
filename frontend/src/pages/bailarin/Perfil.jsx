import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { User, Lock, Loader2, KeyRound, LogOut } from 'lucide-react';
import { api } from '../../api/client';
import { useAuth } from '../../stores/auth';
import { useToast } from '../../stores/toast';

export default function Perfil() {
  const { user, logout } = useAuth();
  const toast = useToast();
  const navigate = useNavigate();

  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function handleChangePwd(e) {
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
    setSubmitting(true);
    const result = await api('auth/cambiar-password', 'POST', {
      current_password: currentPassword,
      new_password: newPassword,
      confirm_password: confirmPassword,
    });
    setSubmitting(false);
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

  const initial = user?.nombre?.charAt(0).toUpperCase() || 'U';

  return (
    <>
      <div className="glass-card profile-card" style={{ textAlign: 'center', marginBottom: 20 }}>
        <div className="top-bar-avatar" style={{ width: 72, height: 72, margin: '0 auto 16px', fontSize: '1.6rem' }}>
          {initial}
        </div>
        <h2 style={{ marginBottom: 4 }}>{user?.nombre}</h2>
        <span className="badge badge-bailarin">Bailarín</span>
        <div style={{ marginTop: 20, textAlign: 'left' }}>
          <div className="profile-info-item">
            <span style={{ color: 'var(--text-muted)' }}>Cédula</span>
            <span>{user?.cedula}</span>
          </div>
          <div className="profile-info-item">
            <span style={{ color: 'var(--text-muted)' }}>Rol</span>
            <span>Bailarín</span>
          </div>
        </div>
      </div>

      <div className="glass-card">
        <h2 style={{ marginBottom: 20 }}><KeyRound size={20} /> Cambiar Contraseña</h2>
        <form onSubmit={handleChangePwd}>
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
          <button className="btn btn-primary btn-block" type="submit" disabled={submitting}>
            {submitting ? <Loader2 size={18} className="spin" /> : <KeyRound size={18} />}
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
