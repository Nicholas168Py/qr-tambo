import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { Lock, CheckCircle2, Info, XCircle, Loader2, QrCode } from 'lucide-react';
import { api } from '../api/client';
import { useAuth } from '../stores/auth';
import { useToast } from '../stores/toast';
import { formatDate, formatTime } from '../lib/format';

export default function RegistroQr() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token') || '';
  const { user, login } = useAuth();
  const toast = useToast();

  const [state, setState] = useState('loading'); // loading | no-token | login | registering | success | error | duplicate | conn-error
  const [result, setResult] = useState(null);
  const [errorMsg, setErrorMsg] = useState('');
  const [cedula, setCedula] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!token) {
      setState('no-token');
    } else if (user) {
      registerAttendance();
    } else {
      setState('login');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token, user]);

  async function registerAttendance() {
    setState('registering');
    const res = await api('asistencia/registrar', 'POST', { token });
    if (res.success) {
      setResult(res.data);
      setState('success');
    } else {
      const isDuplicate = res.message && res.message.includes('Ya registraste');
      setState(isDuplicate ? 'duplicate' : 'error');
      setErrorMsg(res.message || 'Error desconocido');
      setResult(res);
    }
  }

  async function handleLogin(e) {
    e.preventDefault();
    if (!cedula.trim() || !password) {
      toast.warning('Completa todos los campos');
      return;
    }
    setSubmitting(true);
    const res = await login(cedula.trim(), password);
    setSubmitting(false);
    if (res.success) {
      toast.success('¡Bienvenido!');
      setTimeout(registerAttendance, 800);
    } else {
      toast.error(res.message || 'Error al iniciar sesión');
    }
  }

  if (state === 'loading') {
    return (
      <div className="auth-page" style={{ background: 'var(--gradient-bg)' }}>
        <div className="glass-card" style={{ maxWidth: 480, width: '100%', textAlign: 'center', padding: 40 }}>
          <div className="spinner" style={{ margin: '20px auto' }}></div>
          <h2 style={{ marginTop: 16 }}>Cargando...</h2>
        </div>
      </div>
    );
  }

  return (
    <div className="auth-page" style={{ background: 'var(--gradient-bg)', position: 'relative' }}>
      <div className="bg-shapes">
        <div className="bg-shape"></div>
        <div className="bg-shape"></div>
        <div className="bg-shape"></div>
      </div>

      <div className="glass-card" style={{ maxWidth: 480, width: '100%', position: 'relative', zIndex: 1, textAlign: 'center', padding: 40 }}>
        {state === 'no-token' && (
          <div style={{ padding: '40px 0' }}>
            <div style={{ fontSize: '4rem', marginBottom: 16 }}><XCircle size={56} /></div>
            <h2>Código QR Inválido</h2>
            <p style={{ color: 'var(--text-secondary)', marginTop: 8 }}>Este enlace no contiene un código QR válido.</p>
          </div>
        )}

        {state === 'login' && (
          <div>
            <div style={{ fontSize: '3.5rem', marginBottom: 16 }}><Lock size={48} /></div>
            <h2>Inicia Sesión</h2>
            <p style={{ color: 'var(--text-secondary)', margin: '8px 0 24px' }}>
              Debes iniciar sesión para registrar tu asistencia
            </p>
            <form onSubmit={handleLogin}>
              <div className="form-group">
                <label htmlFor="qrCedula">Cédula</label>
                <input
                  id="qrCedula"
                  className="form-input"
                  type="text"
                  placeholder="Tu cédula"
                  required
                  value={cedula}
                  onChange={(e) => setCedula(e.target.value)}
                />
              </div>
              <div className="form-group">
                <label htmlFor="qrPassword">Contraseña</label>
                <input
                  id="qrPassword"
                  className="form-input"
                  type="password"
                  placeholder="Tu contraseña"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>
              <button type="submit" className="btn btn-primary btn-block" disabled={submitting}>
                {submitting ? <Loader2 size={18} className="spin" /> : <QrCode size={18} />}
                Iniciar Sesión y Registrar
              </button>
            </form>
            <p style={{ marginTop: 16, fontSize: '0.85rem', color: 'var(--text-muted)' }}>
              ¿No tienes cuenta? <Link to="/register">Regístrate</Link>
            </p>
          </div>
        )}

        {state === 'registering' && (
          <div>
            <div className="spinner" style={{ margin: '20px auto' }}></div>
            <h2 style={{ marginTop: 16 }}>Registrando asistencia...</h2>
            <p style={{ color: 'var(--text-secondary)', marginTop: 8 }}>Por favor espera...</p>
          </div>
        )}

        {state === 'success' && (
          <div style={{ padding: '20px 0' }}>
            <div style={{ fontSize: '4rem', marginBottom: 16 }}><CheckCircle2 size={56} /></div>
            <h2>¡Asistencia Registrada!</h2>
            <div style={{ color: 'var(--text-secondary)', margin: '16px 0' }}>
              <p><strong>{result.nombre}</strong></p>
              <p>{result.clase}</p>
              <p>{formatDate(result.fecha)} · {formatTime(result.hora_registro)}</p>
            </div>
            <Link to="/bailarin" className="btn btn-primary">Ir a Mi Panel</Link>
          </div>
        )}

        {(state === 'duplicate' || state === 'error') && (
          <div style={{ padding: '20px 0' }}>
            <div style={{ fontSize: '4rem', marginBottom: 16 }}>
              {state === 'duplicate' ? <Info size={56} /> : <XCircle size={56} />}
            </div>
            <h2>{state === 'duplicate' ? 'Ya Registrado' : 'Error'}</h2>
            <p style={{ color: 'var(--text-secondary)', margin: '12px 0' }}>{errorMsg}</p>
            {result?.code && (
              <p style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Código: {result.code}</p>
            )}
            <Link to="/bailarin" className="btn btn-primary">Ir a Mi Panel</Link>
          </div>
        )}
      </div>
    </div>
  );
}
