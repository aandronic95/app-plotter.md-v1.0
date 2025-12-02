import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import { execSync } from 'child_process';

// Detect PHP executable
function findPhpPath(): string {
    const possiblePaths = [
        'php', // Linux/macOS
        'C:\\php\\php.exe',
        'C:\\xampp\\php\\php.exe',
        'C:\\wamp64\\bin\\php\\php8.2.0\\php.exe',
        'C:\\laragon\\bin\\php\\php-8.2.0-Win32-vs16-x64\\php.exe',
    ];

    for (const path of possiblePaths) {
        try {
            execSync(`${path} --version`, { stdio: 'ignore' });
            return path;
        } catch {
            continue;
        }
    }

    return 'php';
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'], // principal JS/TS entry
            refresh: true,                  // hot reload
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
            phpPath: findPhpPath(),
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
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.ts',
            },
        },
    },
});
