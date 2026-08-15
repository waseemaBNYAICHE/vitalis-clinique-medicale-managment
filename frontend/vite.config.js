import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    // Bind to all interfaces so the port is reachable from outside the container.
    host: true,
    port: 5173,
    strictPort: true,
    // Bind-mounted filesystems do not deliver inotify events reliably.
    watch: {
      usePolling: true,
      interval: 300,
    },
    hmr: {
      host: 'localhost',
      clientPort: 5173,
    },
  },
  preview: {
    host: true,
    port: 5173,
    strictPort: true,
  },
})
