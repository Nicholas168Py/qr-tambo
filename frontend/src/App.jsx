import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuth } from './stores/auth';
import { RequireAuth, RequireAdmin, RequireBailarin, RedirectIfAuth } from './components/Guards';
import ToastContainer from './components/ToastContainer';

import AdminLayout from './layouts/AdminLayout';
import BailarinLayout from './layouts/BailarinLayout';

import Login from './pages/Login';
import Register from './pages/Register';
import RegistroQr from './pages/RegistroQr';

import AdminDashboard from './pages/admin/AdminDashboard';
import AdminClases from './pages/admin/Clases';
import AdminHorarios from './pages/admin/Horarios';
import AdminAsistencia from './pages/admin/Asistencia';
import AdminReportes from './pages/admin/Reportes';
import AdminBailarines from './pages/admin/Bailarines';
import AdminConfiguracion from './pages/admin/Configuracion';
import QrDia from './pages/admin/QrDia';

import Escanear from './pages/bailarin/Escanear';
import BailarinHorarios from './pages/bailarin/Horarios';
import Perfil from './pages/bailarin/Perfil';
import BailarinAsistencias from './pages/bailarin/Asistencias';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: Infinity,
      gcTime: 60 * 60 * 1000,
      retry: 1,
      refetchOnMount: false,
      refetchOnWindowFocus: false,
      refetchOnReconnect: false,
    },
  },
});

export default function App() {
  const init = useAuth((s) => s.init);

  useEffect(() => {
    init();
  }, [init]);

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter basename={import.meta.env.BASE_URL}>
      <ToastContainer />
      <Routes>
        <Route path="/login" element={<RedirectIfAuth><Login /></RedirectIfAuth>} />
        <Route path="/register" element={<RedirectIfAuth><Register /></RedirectIfAuth>} />
        <Route path="/registro-qr" element={<RegistroQr />} />

        <Route
          path="/admin"
          element={<RequireAdmin><AdminLayout /></RequireAdmin>}
        >
          <Route index element={<AdminDashboard />} />
          <Route path="clases" element={<AdminClases />} />
          <Route path="horarios" element={<AdminHorarios />} />
          <Route path="asistencia" element={<AdminAsistencia />} />
          <Route path="reportes" element={<AdminReportes />} />
          <Route path="bailarines" element={<AdminBailarines />} />
          <Route path="qr-dia" element={<QrDia />} />
          <Route path="configuracion" element={<AdminConfiguracion />} />
        </Route>

        <Route
          path="/bailarin"
          element={<RequireBailarin><BailarinLayout /></RequireBailarin>}
        >
          <Route index element={<Navigate to="/bailarin/escanear" replace />} />
          <Route path="escanear" element={<Escanear />} />
          <Route path="asistencias" element={<BailarinAsistencias />} />
          <Route path="horarios" element={<BailarinHorarios />} />
          <Route path="perfil" element={<Perfil />} />
        </Route>

        <Route path="/" element={<RequireAuth><HomeRedirect /></RequireAuth>} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}

function HomeRedirect() {
  const { user } = useAuth();
  if (!user) return <Navigate to="/login" replace />;
  return <Navigate to={user.rol === 'admin' ? '/admin' : '/bailarin/escanear'} replace />;
}
