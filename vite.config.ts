import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    // ensure runtime-only build
                    isCustomElement: (tag) => tag.startsWith('custom-'),
                },
            },
        }),
        visualizer({ open: true }),
    ],
    build: {
        sourcemap: false, // don't generate .map files
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3', 'axios'],
                },
            },
        },
    },
});
