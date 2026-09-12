import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    optimizeDeps: {
        exclude: ['maplibre-gl'],
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'node_modules/maplibre-gl/dist/maplibre-gl-worker.mjs',
                    dest: 'assets',
                    rename: { stripBase: true },
                },
                {
                    src: 'node_modules/maplibre-gl/dist/maplibre-gl-shared.mjs',
                    dest: 'assets',
                    rename: { stripBase: true },
                },
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['robots.txt'],
            manifest: {
                name: 'Reisetagebuch',
                short_name: 'Reisetagebuch',
                theme_color: '#47b3a5',
                background_color: '#47b3a5',
                display: 'minimal-ui',
                scope: '/',
                start_url: '/home',
                icons: [
                    {
                        src: '/favicon/web-app-manifest-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                    {
                        src: '/favicon/web-app-manifest-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            devOptions: {
                enabled: true,
            },
        }),
    ],
});
