import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../stores/auth';
import { LOGO } from '../lib/assets';
import { Menu, QrCode, CalendarDays, CheckCircle2, Music, Camera, ClipboardList, User } from 'lucide-react';

const ICONS = {
  qr: QrCode,
  horarios: CalendarDays,
  asistencia: CheckCircle2,
  clases: Music,
  scan: Camera,
  asistencias: ClipboardList,
  perfil: User,
};

export function AdminNavItems({ onNavigate }) {
  const items = [
    { key: 'qr', to: '/admin/qr-dia', icon: 'qr', label: 'QR del Día' },
    { key: 'horarios', to: '/admin/horarios', icon: 'horarios', label: 'Horarios' },
    { key: 'asistencia', to: '/admin/asistencia', icon: 'asistencia', label: 'Asistencia' },
    { key: 'clases', to: '/admin/clases', icon: 'clases', label: 'Clases' },
  ];
  return <BottomNavItems items={items} onNavigate={onNavigate} />;
}

export function BailarinNavItems({ onNavigate }) {
  const items = [
    { key: 'scan', to: '/bailarin/escanear', icon: 'scan', label: 'Leer QR' },
    { key: 'horarios', to: '/bailarin/horarios', icon: 'horarios', label: 'Horarios' },
    { key: 'asistencias', to: '/bailarin/asistencias', icon: 'asistencias', label: 'Mis Asistencias' },
    { key: 'perfil', to: '/bailarin/perfil', icon: 'perfil', label: 'Perfil' },
  ];
  return <BottomNavItems items={items} onNavigate={onNavigate} />;
}

function BottomNavItems({ items, onNavigate }) {
  const navigate = useNavigate();
  const location = useLocation();
  return (
    <nav className="bottom-nav" id="bottomNav">
      {items.map((item) => {
        const Icon = ICONS[item.icon];
        const isActive = location.pathname === item.to;
        return (
          <button
            key={item.key}
            className={`nav-item ${isActive ? 'active' : ''}`}
            onClick={() => {
              if (onNavigate) onNavigate();
              navigate(item.to);
            }}
          >
            <span className="nav-icon"><Icon size={20} /></span>
            <span className="nav-label">{item.label}</span>
          </button>
        );
      })}
    </nav>
  );
}

export function TopBar({ showMenu, onMenu }) {
  const navigate = useNavigate();
  const { user } = useAuth();
  const isAdmin = user?.rol === 'admin';
  const initial = user?.nombre ? user.nombre.charAt(0).toUpperCase() : 'U';

  return (
    <header className="top-bar" id="topBar">
      <div className="top-bar-left">
        {showMenu && (
          <button className="hamburger-btn" onClick={onMenu} aria-label="Menú">
            <Menu size={22} />
          </button>
        )}
        <span className="top-bar-logo">
          <img src={LOGO} alt="QR Tambo" className="top-bar-logo-img" />
          QR TAMBO
        </span>
      </div>
      <div
        className="top-bar-avatar"
        title={user?.nombre}
        onClick={() => navigate(isAdmin ? '/admin' : '/bailarin')}
      >
        {initial}
      </div>
    </header>
  );
}
