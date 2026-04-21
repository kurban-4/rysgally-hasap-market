import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

const host = process.env.TAURI_DEV_HOST;

// https://vite.dev/config/
export default defineConfig(async () => ({
  plugins: [react()],

  // Vite options tailored for Tauri development and only applied in `tauri dev` or `tauri build`
  //
  // 1. prevent Vite from obscuring rust errors
  clearScreen: false,
  // 2. tauri expects a fixed port, fail if that port is not available
// vite.config.js
server: {
  port: 5173, // ИЗМЕНИ ЗДЕСЬ С 8001 НА 5173
  strictPort: true,
  host: true,
  hmr: host
    ? {
        protocol: "ws",
        host,
        port: 1421,
      }
    : undefined,
  watch: {
    ignored: ["**/src-tauri/**"],
  },
  watch: {
    ignored: ["**/src-tauri/**", "**/resources/**"],
},
},
}));