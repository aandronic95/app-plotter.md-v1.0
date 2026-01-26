<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { useApiCache } from '@/composables/useApiCache';

interface AboutSection {
    id: number;
    name: string;
    description: string | null;
    image: string | null;
    is_active: boolean;
}

const apiCache = useApiCache();
const section = ref<AboutSection | null>(null);
const imageLoading = ref(true);
const imageError = ref(false);

const handleImageError = () => {
    imageError.value = true;
    imageLoading.value = false;
    console.error('Error loading about section image:', section.value?.image);
};

const handleImageLoad = () => {
    imageLoading.value = false;
    imageError.value = false;
};

const fetchSection = async () => {
    try {
        const data = await apiCache.fetchWithCache<{ data: AboutSection[] }>(
            '/api/about-sections?active_only=true&order_by=created_at&order_dir=desc&per_page=1',
            {
                key: 'about_section_api_cache',
                ttl: 30 * 60 * 1000,
                version: '1.0',
            }
        );
        
        if (data?.data && Array.isArray(data.data) && data.data.length > 0) {
            section.value = data.data[0];
            // Reset image loading state when section changes
            if (section.value.image) {
                imageLoading.value = true;
                imageError.value = false;
            } else {
                imageLoading.value = false;
            }
        }
    } catch (error) {
        console.error('Error fetching about section:', error);
        const cached = apiCache.loadFromCache<{ data: AboutSection[] }>({
            key: 'about_section_api_cache',
            ttl: 30 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            section.value = cached.data[0];
            // Reset image loading state when section changes
            if (section.value.image) {
                imageLoading.value = true;
                imageError.value = false;
            } else {
                imageLoading.value = false;
            }
        }
    }
    
    await nextTick();
};

onMounted(() => {
    fetchSection();
});
</script>

<template>
    <div v-if="section" class="w-full bg-gray-50 dark:bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="rounded-lg bg-white p-8 dark:bg-gray-800">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:items-center">
                    <!-- Left Section: Text Content -->
                    <div class="space-y-6">
                        <!-- Title -->
                        <h2 class="text-lg font-bold uppercase tracking-tight text-gray-800 dark:text-white">
                            {{ section.name }}
                        </h2>

                        <!-- Description -->
                        <div
                            v-if="section.description"
                            class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 prose max-w-none [&_*]:text-gray-600 dark:[&_*]:text-gray-300 [&_p]:text-gray-600 dark:[&_p]:text-gray-300 [&_strong]:text-gray-800 dark:[&_strong]:text-white [&_a]:text-gray-700 dark:[&_a]:text-gray-300 hover:[&_a]:text-gray-900 dark:hover:[&_a]:text-gray-100 [&_ul]:text-gray-600 dark:[&_ul]:text-gray-300 [&_ol]:text-gray-600 dark:[&_ol]:text-gray-300 [&_li]:text-gray-600 dark:[&_li]:text-gray-300"
                            v-html="section.description"
                        ></div>
                    </div>

                    <!-- Right Section: Image -->
                    <div class="relative h-64 lg:h-[500px] max-h-[500px]">
                        <div
                            v-if="section.image && !imageError"
                            class="relative h-full w-full overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700"
                        >
                            <img
                                :src="section.image"
                                :alt="section.name || 'Despre noi'"
                                class="h-full w-full object-cover"
                                @error="handleImageError"
                                @load="handleImageLoad"
                            />
                            <div
                                v-if="imageLoading"
                                class="absolute inset-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700"
                            >
                                <div class="text-gray-500 dark:text-gray-400">Se încarcă...</div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="relative h-full w-full overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                        >
                            <span class="text-2xl font-bold text-gray-400 dark:text-gray-500 opacity-50">
                                IMG
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

