import { Navigate } from 'react-router-dom';
import { useAuth } from '../stores/auth';
import LoadingOverlay from './LoadingOverlay';

export function RequireAuth({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <LoadingOverlay visible />;
  if (!user) return <Navigate to="/login" replace />;
  return children;
}

export function RequireAdmin({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <LoadingOverlay visible />;
  if (!user) return <Navigate to="/login" replace />;
  if (user.rol !== 'admin') return <Navigate to="/bailarin" replace />;
  return children;
}

export function RequireBailarin({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <LoadingOverlay visible />;
  if (!user) return <Navigate to="/login" replace />;
  if (user.rol !== 'bailarin') return <Navigate to="/admin" replace />;
  return children;
}

export function RedirectIfAuth({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <LoadingOverlay visible />;
  if (user) return <Navigate to={user.rol === 'admin' ? '/admin' : '/bailarin/escanear'} replace />;
  return children;
}
