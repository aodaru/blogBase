import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            host: "api-ale-adalgarcia.com",
        },
        watch: {
            ignored: ["!**/node_modules/your-package-name/**"],
        },
    },
});
