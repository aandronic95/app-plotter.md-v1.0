<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Showcase {
    id: number;
    name: string;
    subtitle?: string;
    image: string;
    category: Category | null;
    button_text?: string;
    button_link?: string;
}

interface SectionData {
    title: string;
    description?: string;
    button_text?: string;
    button_link?: string;
}

const { t } = useTranslations();
const showcases = ref<Showcase[]>([]);
const section = ref<SectionData | null>(null);
const isLoading = ref(true);

const fetchShowcases = async () => {
    try {
        isLoading.value = true;
        const response = await fetch('/api/product-category-showcases?active_only=true&order_by=sort_order&order_dir=asc');
        const data = await response.json();
        
        if (data.data && Array.isArray(data.data)) {
            showcases.value = data.data;
        }
        
        if (data.section) {
            section.value = data.section;
        }
    } catch (error) {
        console.error('Error fetching product category showcases:', error);
        showcases.value = [];
    } finally {
        isLoading.value = false;
    }
};

const handleCardClick = (showcase: Showcase) => {
    if (showcase.button_link) {
        if (showcase.button_link.startsWith('http')) {
            window.open(showcase.button_link, '_blank');
        } else {
            router.visit(showcase.button_link);
        }
    } else if (showcase.category?.slug) {
        router.visit(`/categories/${showcase.category.slug}`);
    }
};

const handleSectionButtonClick = () => {
    if (section.value?.button_link) {
        if (section.value.button_link.startsWith('http')) {
            window.open(section.value.button_link, '_blank');
        } else {
            router.visit(section.value.button_link);
        }
    }
};

onMounted(() => {
    fetchShowcases();
});
</script>

<template>
    <div v-if="isLoading" class="w-full py-12">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex items-center justify-center">
                <p class="text-gray-500">{{ t('loading') }}...</p>
            </div>
        </div>
    </div>
    
    <div v-else-if="showcases.length > 0" class="w-full bg-white py-12 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <!-- Section Header -->
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex-1">
                    <h2 class="mb-2 text-3xl font-bold uppercase text-gray-900 dark:text-white">
                        {{ section?.title || 'TIPURI DE PRODUSE' }}
                    </h2>
                    <p v-if="section?.description" class="text-gray-600 dark:text-gray-400">
                        {{ section.description }}
                    </p>
                </div>
                <button
                    v-if="section?.button_text"
                    @click="handleSectionButtonClick"
                    class="mt-4 rounded border border-gray-300 bg-white px-6 py-2 text-sm font-medium text-gray-900 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700 md:mt-0"
                >
                    {{ section.button_text }}
                </button>
            </div>

            <!-- Showcase Cards Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="showcase in showcases"
                    :key="showcase.id"
                    class="group relative cursor-pointer overflow-hidden rounded-lg bg-white shadow-md transition-shadow hover:shadow-xl dark:bg-gray-800"
                    @click="handleCardClick(showcase)"
                >
                    <!-- Image -->
                    <div class="relative h-48 w-full overflow-hidden bg-gray-200">
                        <img
                            :src="showcase.image"
                            :alt="showcase.name"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                    </div>

                    <!-- Card Footer -->
                    <div class="absolute bottom-0 left-0 right-0 bg-white/95 p-4 backdrop-blur-sm dark:bg-gray-800/95">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ showcase.name }}
                                </h3>
                                <p v-if="showcase.subtitle" class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ showcase.subtitle }}
                                </p>
                            </div>
                            <svg
                                class="h-5 w-5 text-gray-600 transition-transform group-hover:translate-x-1 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Button (if exists) -->
            <div v-if="section?.button_text && showcases.length > 0" class="mt-8 flex justify-center">
                <button
                    @click="handleSectionButtonClick"
                    class="rounded border border-gray-300 bg-white px-6 py-2 text-sm font-medium text-gray-900 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                >
                    {{ section.button_text }}
                </button>
            </div>
        </div>
    </div>
</template>

