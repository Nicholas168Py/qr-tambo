import { Outlet } from 'react-router-dom';
import { useAuth } from '../stores/auth';
import { BailarinNavItems } from '../components/Nav';

export default function BailarinLayout() {
  const { user } = useAuth();

  return (
    <div className="bailarin-layout">
      <header className="bailarin-header">
        <h2>QR Tambo</h2>
        <div className="bailarin-header-actions">
          <span>{user?.nombre}</span>
        </div>
      </header>
      <main className="bailarin-main">
        <Outlet />
      </main>
      <BailarinNavItems />
    </div>
  );
}
