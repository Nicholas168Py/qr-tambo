import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Music, CalendarDays, Users, CheckCircle2, QrCode, Settings2, Clock } from 'lucide-react';
import { api } from '../../api/client';
import { StatCard } from '../../components/ui';
import { useAuth } from '../../stores/auth';
import { MONTH_NAMES } from '../../lib/format';

export default function AdminDashboard() {
  const { user } = useAuth();
  const now = new Date();
  const mes = now.getMonth() + 1;
  const anio = now.getFullYear();

  const clases = useQuery({ queryKey: ['clases'], queryFn: () => api('clases') });
  const horarios = useQuery({ queryKey: ['horarios'], queryFn: () => api('horarios') });
  const usuarios = useQuery({ queryKey: ['usuarios'], queryFn: () => api('usuarios') });
  const asistencia = useQuery({
    queryKey: ['asistencia', mes, anio],
    queryFn: () => api(`asistencia?mes=${mes}&anio=${anio}`),
  });

  return (
    <>
      <div className="main-header">
        <h1>Panel de Administración</h1>
        <p>Bienvenido, {user?.nombre}</p>
      </div>

      <div className="stats-grid">
        <StatCard icon={Music} value={clases.data?.data?.length ?? '—'} label="Clases" />
        <StatCard icon={CalendarDays} value={horarios.data?.data?.length ?? '—'} label="Horarios" />
        <StatCard icon={Users} value={usuarios.data?.total ?? '—'} label="Bailarines" />
        <StatCard icon={CheckCircle2} value={asistencia.data?.data?.length ?? '—'} label="Asistencias este mes" />
      </div>

      <div className="glass-card">
        <h2 style={{ marginBottom: 16 }}>Acceso Rápido</h2>
        <div className="cards-grid">
          <Link to="/admin/qr-dia" className="glass-card item-card glass-card-sm">
            <h3><QrCode size={20} /> Generar QR del Día</h3>
            <p>Crea códigos QR de las clases programadas para hoy.</p>
            <div className="card-actions">
              <span className="btn btn-primary btn-sm">Abrir</span>
            </div>
          </Link>
          <Link to="/admin/clases" className="glass-card item-card glass-card-sm">
            <h3><Settings2 size={20} /> Gestionar Clases</h3>
            <p>Administra las clases disponibles en la academia.</p>
            <div className="card-actions">
              <span className="btn btn-primary btn-sm">Abrir</span>
            </div>
          </Link>
          <Link to="/admin/horarios" className="glass-card item-card glass-card-sm">
            <h3><Clock size={20} /> Configurar Horarios</h3>
            <p>Define los horarios semanales de cada clase.</p>
            <div className="card-actions">
              <span className="btn btn-primary btn-sm">Abrir</span>
            </div>
          </Link>
        </div>
      </div>
    </>
  );
}
