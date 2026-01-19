<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

interface Banner {
    id: number;
    headline: string;
    title?: string;
    description?: string;
    button_text: string;
    button_link?: string;
    image: string;
    background_color: string;
}

const { t } = useTranslations();
const banner = ref<Banner | null>(null);
const isLoading = ref(true);

const fetchBanner = async () => {
    try {
        isLoading.value = true;
        const response = await fetch('/api/custom-print-banners?active_only=true&order_by=sort_order&order_dir=asc');
        const data = await response.json();
        
        if (data.data) {
            banner.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching custom print banner:', error);
        banner.value = null;
    } finally {
        isLoading.value = false;
    }
};

const handleButtonClick = () => {
    if (banner.value?.button_link) {
        if (banner.value.button_link.startsWith('http')) {
            window.open(banner.value.button_link, '_blank');
        } else {
            router.visit(banner.value.button_link);
        }
    }
};

onMounted(() => {
    fetchBanner();
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
    
    <div
        v-else-if="banner"
        class="w-full overflow-hidden rounded-lg bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800"
    >
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:py-16">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-center">
                <!-- Left Side - Text Content -->
                <div class="flex flex-col justify-center space-y-6 lg:col-span-2">
                    <h2
                        v-if="banner.headline"
                        class="text-3xl font-bold uppercase tracking-tight text-gray-900 dark:text-white md:text-4xl lg:text-5xl"
                    >
                        {{ banner.headline }}
                    </h2>
                    
                    <h3
                        v-if="banner.title"
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-200"
                    >
                        {{ banner.title }}
                    </h3>
                    
                    <p
                        v-if="banner.description"
                        class="text-lg text-gray-700 dark:text-gray-300"
                    >
                        {{ banner.description }}
                    </p>
                    
                    <button
                        v-if="banner.button_text"
                        @click="handleButtonClick"
                        class="w-fit rounded-md bg-[#2d5016] px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f350e] focus:outline-none focus:ring-2 focus:ring-[#2d5016]/50 dark:bg-[#3d6b1f] dark:hover:bg-[#2d5016] dark:focus:ring-[#3d6b1f]/50"
                    >
                        {{ banner.button_text }}
                        <span class="ml-2">></span>
                    </button>
                </div>

                <!-- Right Side - Image -->
                <div class="flex items-center justify-center lg:col-span-1">
                    <div class="relative h-64 w-full overflow-hidden rounded-lg bg-neutral-200 dark:bg-neutral-700 md:h-80 lg:h-96">
                        <img
                            v-if="banner.image"
                            :src="banner.image"
                            :alt="banner.headline || 'Custom Print'"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
                        >
                            <span class="text-lg font-medium text-gray-400 dark:text-gray-500">
                                {{ banner.headline || 'Imagine' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
