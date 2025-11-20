import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { execSync } from 'child_process';

// Find PHP executable
function findPhpPath(): string {
    // Common PHP paths on Windows
    const possiblePaths = [
        'php',
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

    // Fallback to 'php' and let the system PATH handle it
    return 'php';
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
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
});
