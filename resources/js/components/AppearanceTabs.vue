<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';

const { theme, setTheme } = useAppearance();
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-800"
    >
        <button
            @click="setTheme('light')"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                theme === 'light'
                    ? 'bg-white shadow-sm dark:bg-gray-700 text-gray-900 dark:text-gray-100'
                    : 'text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700',
            ]"
        >
            <Sun class="h-4 w-4" />
            <span class="ml-1.5 text-sm">Light</span>
        </button>
        <button
            @click="setTheme('dark')"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                theme === 'dark'
                    ? 'bg-white shadow-sm dark:bg-gray-700 text-gray-900 dark:text-gray-100'
                    : 'text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700',
            ]"
        >
            <Moon class="h-4 w-4" />
            <span class="ml-1.5 text-sm">Dark</span>
        </button>
    </div>
</template>

