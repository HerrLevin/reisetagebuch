import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    optimizeDeps: {
        exclude: ['maplibre-gl'],
    },
    worker: {
        format: 'es',
    },
    resolve: {
        alias: {
            '@shared': fileURLToPath(
                new URL('./resources/shared', import.meta.url),
            ),
            '@admin': fileURLToPath(
                new URL('./resources/admin', import.meta.url),
            ),
        },
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/css/app.css',
                'resources/admin/admin.ts',
            ],
            refresh: true,
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
            workbox: {
                // The admin panel is a separate, server-gated page.
                navigateFallbackDenylist: [/^\/admin/],
            },
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
