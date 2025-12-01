import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
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
