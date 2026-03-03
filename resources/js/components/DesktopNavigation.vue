<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { urlIsActive } from '@/lib/utils';
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

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const { t } = useTranslations();
const page = usePage();

const isCurrentRoute = computed(
    () => (href: string) =>
        urlIsActive(href, page.url),
);
</script>

<template>
    <div class="bg-gray-200 dark:bg-gray-800 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <nav v-if="!props.isLoading && props.menuItems.length > 0" class="flex h-12 items-center justify-center gap-1">
                <Link
                    v-for="item in props.menuItems"
                    :key="item.id"
                    :href="item.href"
                    :target="item.is_external ? (item.target || '_blank') : '_self'"
                    :rel="item.is_external ? 'noopener noreferrer' : undefined"
                    :class="[
                        'group relative flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-semibold uppercase tracking-wide transition-all duration-200 hover:bg-white/10 dark:hover:bg-white/10',
                        isCurrentRoute(item.href)
                            ? 'bg-white/20 text-gray-900 dark:text-gray-100'
                            : 'text-gray-900 dark:text-gray-200',
                    ]"
                >
                    <span>{{ item.title }}</span>
                </Link>
            </nav>
            <div v-else-if="props.isLoading" class="flex h-12 items-center justify-center gap-6">
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-700 dark:bg-gray-600"></div>
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-700 dark:bg-gray-600"></div>
                <div class="h-4 w-20 animate-pulse rounded-md bg-gray-700 dark:bg-gray-600"></div>
            </div>
            <div v-else-if="props.menuItems.length === 0" class="flex h-12 items-center justify-center">
                <span class="text-sm text-gray-400 dark:text-gray-500">{{ t('no_navigation_items') }}</span>
            </div>
        </div>
    </div>
</template>

