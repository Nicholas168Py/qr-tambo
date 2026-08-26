import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

const buildTimestamp = Date.now();

// Dev: served at http://localhost:5173/ (proxy forwards /backend/api to XAMPP).
// Prod: built to dist/ and served by Apache at / (docroot).
export default defineConfig(({ mode }) => ({
  plugins: [
    react(),
    {
      name: 'inject-build-timestamp',
      transformIndexHtml(html) {
        return html.replace('__BUILD_TIMESTAMP__', buildTimestamp);
      },
    },
  ],
  base: '/',
  define: {
    __BUILD_TIMESTAMP__: buildTimestamp,
  },
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
    headers: {
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0',
    },
    proxy: {
      '/backend/api': {
        target: 'http://localhost/qr_tambo/backend/api',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/backend\/api/, ''),
      },
    },
  },
}));
