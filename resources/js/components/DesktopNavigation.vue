<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

interface NavigationItem {
    id: number;
    title: string;
    href: string;
    is_external?: boolean;
    target?: string;
}

interface Props {
    menuItems: NavigationItem[];
    isLoading?: boolean;
}

withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const { t } = useTranslations();
</script>

<template>
    <div class="bg-gray-950 dark:bg-gray-950 border-b border-gray-800 ">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <nav v-if="!isLoading && menuItems.length > 0" class="flex h-12 items-center justify-center gap-1">
                <Link
                    v-for="item in menuItems"
                    :key="item.id"
                    :href="item.href"
                    :target="item.is_external ? (item.target || '_blank') : '_self'"
                    :rel="item.is_external ? 'noopener noreferrer' : undefined"
                    class="group relative flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300 transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <span>{{ item.title }}</span>
                </Link>
            </nav>
            <div v-else-if="isLoading" class="flex h-12 items-center justify-center gap-6">
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-300 dark:bg-gray-700"></div>
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-300 dark:bg-gray-700"></div>
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-300 dark:bg-gray-700"></div>
            </div>
            <div v-else-if="menuItems.length === 0" class="flex h-12 items-center justify-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('no_navigation_items') }}</span>
            </div>
        </div>
    </div>
</template>

