import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { Sidebar } from '../components/Sidebar';
import { TopBar, BailarinNavItems } from '../components/Nav';

export default function BailarinLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="bailarin-layout">
      <TopBar showMenu onMenu={() => setSidebarOpen(true)} />
      <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} role="bailarin" />
      <div className="bailarin-main">
        <Outlet />
      </div>
      <BailarinNavItems onNavigate={() => setSidebarOpen(false)} />
    </div>
  );
}
