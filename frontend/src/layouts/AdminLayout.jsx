import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from '../components/Sidebar';
import { TopBar, AdminNavItems } from '../components/Nav';

export default function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="admin-layout">
      <TopBar showMenu onMenu={() => setSidebarOpen(true)} />
      <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="main-content">
        <Outlet />
      </div>
      <AdminNavItems onNavigate={() => setSidebarOpen(false)} />
    </div>
  );
}
