import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/landing.css', 'resources/js/landing.js', 'resources/css/marketplace.css', 'resources/js/marketplace.js', 'resources/js/pauta-marketplace.js'],
        refresh: true,
    })],

    server: {
        watch: {
            ignored: ['**/public/livewire/**'],  // evita loops
        }
    }
});
