import { create } from 'zustand';
import { api } from '../api/client';

export const useAuth = create((set, get) => ({
  user: null,
  loading: true,

  async init() {
    try {
      const result = await api('auth/me', 'GET');
      set({ user: result.success ? result.data : null, loading: false });
    } catch (e) {
      // Error de red o servidor - limpiar sesión y no bloquear la app
      console.error('[AUTH] init() error:', e);
      set({ user: null, loading: false });
    }
  },

  async login(cedula, password) {
    const result = await api('auth/login', 'POST', { cedula, password });
    if (result.success) {
      set({ user: result.data });
    }
    return result;
  },

  getDefaultRoute() {
    const { user } = get();
    if (!user) return '/login';
    return user.rol === 'admin' ? '/admin' : '/bailarin/escanear';
  },

  async logout() {
    await api('auth/logout', 'POST');
    set({ user: null });
  },

  async updateUser(data) {
    set({ user: { ...get().user, ...data } });
  },
}));
