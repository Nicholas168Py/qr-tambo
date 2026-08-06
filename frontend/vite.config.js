import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// Dev: served at http://localhost:5173/ (proxy forwards /backend/api to XAMPP).
// Prod: built to dist/ and served by Apache at /qr_tambo/frontend/dist/.
export default defineConfig(({ mode }) => ({
  plugins: [react()],
  base: mode === 'production' ? '/qr_tambo/frontend/dist/' : '/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        manualChunks: {
          react: ['react', 'react-dom', 'react-router-dom'],
          query: ['@tanstack/react-query', 'zustand', 'axios'],
          qr: ['qrcode', 'html5-qrcode'],
          icons: ['lucide-react'],
        },
      },
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/backend/api': {
        target: 'http://localhost/qr_tambo/backend/api',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/backend\/api/, ''),
      },
    },
  },
}));
