import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  server: {
    port: 5173,
    host: "0.0.0.0", // Écouter sur toutes les interfaces (requis pour Docker/Railway)
    allowedHosts: [
      "ticketing-app.up.railway.app", // Le domaine qui bloquait
      ".railway.app", // (Optionnel) Autorise tous les sous-domaines railway
    ],
  },
});
