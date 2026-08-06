import { create } from 'zustand';
import { api } from '../api/client';

export const useAuth = create((set, get) => ({
  user: null,
  loading: true,

  async init() {
    const result = await api('auth/me', 'GET');
    set({ user: result.success ? result.data : null, loading: false });
  },

  async login(cedula, password) {
    const result = await api('auth/login', 'POST', { cedula, password });
    if (result.success) {
      set({ user: result.data });
    }
    return result;
  },

  async logout() {
    await api('auth/logout', 'POST');
    set({ user: null });
  },
}));
