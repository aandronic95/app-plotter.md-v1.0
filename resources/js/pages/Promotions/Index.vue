<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import PromotionCard from '@/components/PromotionCard.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface Promotion {
    id: number;
    title: string;
    description: string | null;
    banner: string;
    external_link: string | null;
    page_id: number | null;
    product_id: number | null;
    page?: {
        id: number;
        title: string;
        slug: string;
    } | null;
    product?: {
        id: number;
        name: string;
        slug: string;
    } | null;
    link: string | null;
    end_date: string | null;
    created_at: string;
    updated_at: string;
}

const { t } = useTranslations();
const promotions = ref<Promotion[]>([]);
const isLoading = ref(true);

// Fetch promotions from API
const fetchPromotions = async () => {
    try {
        isLoading.value = true;
        const response = await fetch('/api/promotions?active_only=true&order_by=sort_order&order_dir=asc');
        const data = await response.json();
        
        if (data.data && Array.isArray(data.data)) {
            promotions.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching promotions:', error);
        promotions.value = [];
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchPromotions();
});
</script>

<template>
    <Head :title="t('promotions_title')" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ t('promotions_title') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ t('discover_best_offers_and_promotions') }}
                    </p>
                </div>

                <!-- Loading State -->
                <div
                    v-if="isLoading"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="i in 6"
                        :key="i"
                        class="animate-pulse rounded-lg bg-white dark:bg-gray-800"
                    >
                        <div class="aspect-[4/3] w-full bg-gray-200 dark:bg-gray-700" />
                        <div class="p-4">
                            <div class="mb-2 h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
                            <div class="mb-2 h-6 w-full rounded bg-gray-200 dark:bg-gray-700" />
                            <div class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Promotions Grid -->
                <div
                    v-else-if="promotions.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <PromotionCard
                        v-for="promotion in promotions"
                        :key="promotion.id"
                        :promotion="promotion"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12"
                >
                    <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                        <svg
                            class="h-12 w-12 text-gray-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                            />
                        </svg>
                    </div>
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        {{ t('no_promotions_available') }}
                    </p>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

