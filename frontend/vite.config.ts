import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5173,
    // Nécessaire pour que le hot-reload fonctionne correctement depuis un bind mount Docker
    watch: {
      usePolling: true,
    },
  },
});
