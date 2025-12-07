<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

interface Tab {
    id: string;
    label: string;
    count?: number;
    route?: string;
}

const { t } = useTranslations();

const props = withDefaults(
    defineProps<{
        tabs?: Tab[];
        defaultTab?: string;
    }>(),
    {
        tabs: undefined,
        defaultTab: 'promotions',
    },
);

// Default tabs with translations
const defaultTabs = computed<Tab[]>(() => [
    { id: 'promotions', label: t('promotions'), count: 24, route: '/promotions' },
    { id: 'news', label: t('news'), route: '/news' },
    { id: 'events', label: t('events'), route: '/events' },
    { id: 'tips', label: t('useful_tips'), route: '/tips' },
    { id: 'reviews', label: t('reviews'), route: '/reviews' },
]);

const tabs = computed(() => props.tabs || defaultTabs.value);

const emit = defineEmits<{
    'update:activeTab': [value: string];
}>();

const page = usePage();
const activeTab = ref(props.defaultTab);

// Map tab IDs to routes
const tabRoutes: Record<string, string> = {
    promotions: '/promotions',
    news: '/news',
    events: '/events',
    tips: '/tips',
    reviews: '/reviews',
};

// Determine active tab from current URL
const currentUrl = computed(() => page.url);
const determineActiveTab = () => {
    const url = currentUrl.value;
    // Check exact matches first
    if (url === '/promotions' || url.startsWith('/promotions/')) return 'promotions';
    if (url === '/news' || url.startsWith('/news/')) return 'news';
    if (url === '/events' || url.startsWith('/events/')) return 'events';
    if (url === '/tips' || url.startsWith('/tips/')) return 'tips';
    if (url === '/reviews' || url.startsWith('/reviews/')) return 'reviews';
    // Default to promotions if on home page
    if (url === '/' || url === '/home') return props.defaultTab;
    return props.defaultTab;
};

// Initialize active tab based on current URL
activeTab.value = determineActiveTab();

// Watch for URL changes to update active tab
watch(currentUrl, () => {
    activeTab.value = determineActiveTab();
});

const setActiveTab = (tabId: string) => {
    activeTab.value = tabId;
    emit('update:activeTab', tabId);
    
    // Navigate to route if provided
    const tab = tabs.value.find(t => t.id === tabId);
    if (tab?.route) {
        router.visit(tab.route);
    } else if (tabRoutes[tabId]) {
        router.visit(tabRoutes[tabId]);
    }
};

const isActive = (tabId: string) => activeTab.value === tabId;

// Scroll active tab into view on mobile
const tabsContainer = ref<HTMLElement | null>(null);
const scrollToActiveTab = () => {
    if (!tabsContainer.value) return;
    const activeButton = tabsContainer.value.querySelector('[data-active="true"]') as HTMLElement;
    if (activeButton) {
        activeButton.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
        });
    }
};

// Watch for active tab changes and scroll to it
watch(activeTab, () => {
    setTimeout(scrollToActiveTab, 100);
});
</script>

<template>
    <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        <div
            ref="tabsContainer"
            class="flex w-full rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600"
        >
            <button
                v-for="(tab, index) in tabs"
                :key="tab.id"
                @click="setActiveTab(tab.id)"
                :data-active="isActive(tab.id)"
                :class="[
                    'relative flex flex-1 items-center justify-center whitespace-nowrap px-3 py-2 text-xs font-medium transition-colors sm:px-4 sm:text-sm',
                    index === 0 && 'rounded-l-md',
                    index === tabs.length - 1 && 'rounded-r-md',
                    isActive(tab.id)
                        ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white'
                        : 'bg-white text-gray-900 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                    index > 0 && 'border-l border-gray-300 dark:border-gray-600',
                ]"
            >
                <span>{{ tab.label }}</span>
                <span
                    v-if="tab.count !== undefined"
                    :class="[
                        'ml-1.5 sm:ml-2',
                        isActive(tab.id)
                            ? 'text-pink-500 dark:text-pink-400'
                            : 'text-gray-500 dark:text-gray-400',
                    ]"
                >
                    ({{ tab.count }})
                </span>
            </button>
        </div>
    </div>
</template>

