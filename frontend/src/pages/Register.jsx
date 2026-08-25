import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { User, Lock, Loader2, UserPlus, CheckCircle2 } from 'lucide-react';
import { api } from '../api/client';
import { useToast } from '../stores/toast';
import '../styles/login.css';

function Field({ icon: Icon, label, id, ...props }) {
  return (
    <div className="input-group">
      <label htmlFor={id}>{label}</label>
      <div className="input-control">
        <span className="input-icon"><Icon size={16} /></span>
        <input id={id} className="form-input" {...props} />
        <span className="input-focus"></span>
      </div>
    </div>
  );
}

export default function Register() {
  const toast = useToast();
  const navigate = useNavigate();
  const [nombre, setNombre] = useState('');
  const [cedula, setCedula] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    if (!nombre.trim() || !cedula.trim() || !password || !confirm) {
      toast.warning('Completa todos los campos');
      return;
    }
    if (password !== confirm) {
      toast.warning('Las contraseñas no coinciden');
      return;
    }
    if (password.length < 6) {
      toast.warning('La contraseña debe tener al menos 6 caracteres');
      return;
    }
    setSubmitting(true);
    const result = await api('auth/register', 'POST', {
      nombre: nombre.trim(),
      cedula: cedula.trim(),
      password,
    });
    setSubmitting(false);
    if (result.success) {
      toast.success(result.message || 'Registro exitoso');
      setTimeout(() => navigate('/login'), 2000);
    } else {
      toast.error(result.message || 'Error al registrarse');
    }
  }

  return (
    <div className="auth-page" style={{ background: 'var(--gradient-bg)', position: 'relative' }}>
      <div className="bg-shapes">
        <div className="bg-shape"></div>
        <div className="bg-shape"></div>
        <div className="bg-shape"></div>
      </div>

      <div className="auth-card glass-card">
        <div className="logo">
          <span className="logo-icon"><UserPlus size={52} /></span>
          <h1>Crear Cuenta</h1>
          <p>Únete como Bailarín</p>
        </div>

        <form onSubmit={handleSubmit}>
          <Field icon={User} label="Nombre Completo" id="nombre" type="text"
            placeholder="Tu nombre y apellido" value={nombre}
            onChange={(e) => setNombre(e.target.value)} />
          <Field icon={User} label="Cédula" id="cedula" type="text"
            placeholder="Número de cédula" value={cedula}
            onChange={(e) => setCedula(e.target.value)} />
          <Field icon={Lock} label="Contraseña" id="password" type="password"
            minLength={6} placeholder="Mínimo 6 caracteres" value={password}
            onChange={(e) => setPassword(e.target.value)} />
          <Field icon={Lock} label="Confirmar Contraseña" id="password_confirm" type="password"
            placeholder="Repite tu contraseña" value={confirm}
            onChange={(e) => setConfirm(e.target.value)} />

          <button className="btn btn-primary btn-block" type="submit" disabled={submitting}>
            {submitting ? <Loader2 size={18} className="spin" /> : <CheckCircle2 size={18} />}
            {submitting ? 'Creando cuenta...' : 'Crear Cuenta'}
          </button>
        </form>

        <div className="login-footer">
          ¿Ya tienes cuenta? <Link to="/login">Inicia sesión</Link>
        </div>
      </div>
    </div>
  );
}
