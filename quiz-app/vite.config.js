import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
    ],
    server: {
        // Vite will only be used to build/watch assets. Laravel will serve the app on port 8000.
        host: true,
        port: 8000,
        proxy: {
            // Proxy application requests to the Laravel dev server on port 8000
            '^/(?!(@vite|@id|@fs|node_modules|resources)/)': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
