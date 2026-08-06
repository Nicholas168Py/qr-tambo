import { create } from 'zustand';

let toastId = 0;

export const useToast = create((set) => ({
  toasts: [],
  show(message, type = 'success') {
    const id = ++toastId;
    set((s) => ({ toasts: [...s.toasts, { id, message, type }] }));
    setTimeout(() => {
      set((s) => ({ toasts: s.toasts.filter((t) => t.id !== id) }));
    }, 4000);
  },
  success: (msg) => useToast.getState().show(msg, 'success'),
  error: (msg) => useToast.getState().show(msg, 'error'),
  warning: (msg) => useToast.getState().show(msg, 'warning'),
  info: (msg) => useToast.getState().show(msg, 'info'),
  remove: (id) => set((s) => ({ toasts: s.toasts.filter((t) => t.id !== id) })),
}));
