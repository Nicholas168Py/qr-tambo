import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../stores/auth';
import { useToast } from '../stores/toast';
import {
  LayoutDashboard, Music, CalendarDays, CheckCircle2, BarChart3, Users, QrCode, LogOut,
} from 'lucide-react';
import { LOGO } from '../lib/assets';

const LINKS = [
  { to: '/admin', end: true, icon: LayoutDashboard, label: 'Resumen' },
  { to: '/admin/clases', icon: Music, label: 'Clases' },
  { to: '/admin/horarios', icon: CalendarDays, label: 'Horarios' },
  { to: '/admin/asistencia', icon: CheckCircle2, label: 'Asistencia' },
  { to: '/admin/reportes', icon: BarChart3, label: 'Reportes' },
  { to: '/admin/bailarines', icon: Users, label: 'Bailarines' },
  { to: '/admin/qr-dia', icon: QrCode, label: 'QR del DÃ­a' },
];

export default function Sidebar({ open, onClose }) {
  const navigate = useNavigate();
  const { user, logout } = useAuth();
  const toast = useToast();

  async function handleLogout() {
    await logout();
    toast.info('SesiÃ³n cerrada');
    navigate('/login');
  }

  return (
    <>
      <div className={`sidebar-overlay ${open ? 'open' : ''}`} onClick={onClose}></div>
      <aside className={`sidebar ${open ? 'open' : ''}`} id="sidebar">
        <div className="sidebar-logo">
          <img src={LOGO} alt="QR Tambo" className="sidebar-logo-img" />
          <small>Panel de AdministraciÃ³n</small>
        </div>
        <nav className="sidebar-nav">
          {LINKS.map((l) => {
            const Icon = l.icon;
            return (
              <NavLink
                key={l.to}
                to={l.to}
                end={l.end}
                onClick={onClose}
                className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
              >
                <span className="nav-icon"><Icon size={18} /></span>
                {l.label}
              </NavLink>
            );
          })}
        </nav>
        <div className="sidebar-footer">
          <div className="sidebar-user">
            <div className="user-avatar">{(user?.nombre || 'U').charAt(0).toUpperCase()}</div>
            <div className="user-info">
              <div className="user-name">{user?.nombre}</div>
              <div className="user-role">Administrador</div>
            </div>
          </div>
          <button className="nav-link" onClick={handleLogout} style={{ color: 'var(--error)' }}>
            <span className="nav-icon"><LogOut size={18} /></span>
            Cerrar SesiÃ³n
          </button>
        </div>
      </aside>
    </>
  );
}
