import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';

// Standalone build used to produce the static SPA bundle loaded by the
// Capacitor native shell (see capacitor.config.ts -> webDir). This is
// intentionally separate from vite.config.mts, which targets Laravel's
// Blade-served build and has no standalone index.html of its own.
export default defineConfig({
    // `publicDir` defaults to `<root>/public`, which resolves to
    // resources/capacitor/public — a curated subset of the project's public/
    // assets actually used by the SPA (see that folder's contents), kept
    // separate from Laravel's own public/ so server-only files (index.php,
    // the web PWA service worker, etc.) never end up bundled into the app.
    root: fileURLToPath(new URL('./resources/capacitor', import.meta.url)),
    base: './',
    plugins: [
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    build: {
        outDir: fileURLToPath(new URL('./dist-capacitor', import.meta.url)),
        emptyOutDir: true,
    },
});
