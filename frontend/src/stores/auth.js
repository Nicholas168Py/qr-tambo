import { create } from 'zustand';
import { api } from '../api/client';

function setCookie(name, value, days) {
  const expires = days > 0 ? `; max-age=${days * 86400}` : '; max-age=0';
  document.cookie = `${name}=${encodeURIComponent(value || '')}${expires}; path=/; SameSite=Lax`;
}

function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}

export const useAuth = create((set, get) => ({
  user: null,
  loading: true,

  async init() {
    try {
      const result = await api('auth/me', 'GET');
      set({ user: result.success ? result.data : null, loading: false });
    } catch (e) {
      console.error('[AUTH] init() error:', e);
      set({ user: null, loading: false });
    }
  },

  async login(cedula, password, remember = false) {
    const result = await api('auth/login', 'POST', { cedula, password, remember });
    if (result.success) {
      set({ user: result.data });
      if (remember) {
        setCookie('qr_remember', cedula, 30);
      } else {
        setCookie('qr_remember', '', 0);
      }
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
    setCookie('qr_remember', '', 0);
  },

  getRememberedCedula() {
    return getCookie('qr_remember') || '';
  },

  async updateUser(data) {
    set({ user: { ...get().user, ...data } });
  },
}));
