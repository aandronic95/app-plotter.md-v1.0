<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { router } from '@inertiajs/vue3';

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
    carousel_banner_image?: string | null;
}

const { t } = useTranslations();
const showcases = ref<Showcase[]>([]);
const section = ref<SectionData | null>(null);
const isLoading = ref(true);
const currentIndex = ref(0); // Index pentru cardurile de categorii (nu include banner-ul)
const intervalRef = ref<number | null>(null);

const CARDS_TO_SHOW = 4; // Numărul de carduri de categorii de afișat (fără banner)

// Get visible showcases based on current index
const visibleShowcases = computed(() => {
    if (showcases.value.length === 0) return [];
    
    const result: Showcase[] = [];
    for (let i = 0; i < CARDS_TO_SHOW; i++) {
        const index = (currentIndex.value + i) % showcases.value.length;
        result.push(showcases.value[index]);
    }
    return result;
});

// Check if we can navigate (need more than 4 showcases)
const canNavigate = computed(() => {
    return showcases.value.length > CARDS_TO_SHOW;
});

// Fetch showcases and section data
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

const handleBannerClick = () => {
    if (section.value?.button_link) {
        if (section.value.button_link.startsWith('http')) {
            window.open(section.value.button_link, '_blank');
        } else {
            router.visit(section.value.button_link);
        }
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

const nextSlide = () => {
    if (!canNavigate.value) return;
    currentIndex.value = (currentIndex.value + 1) % showcases.value.length;
    resetInterval();
};

const prevSlide = () => {
    if (!canNavigate.value) return;
    currentIndex.value = (currentIndex.value - 1 + showcases.value.length) % showcases.value.length;
    resetInterval();
};

const startInterval = () => {
    if (!canNavigate.value) return;
    intervalRef.value = window.setInterval(() => {
        nextSlide();
    }, 5000);
};

const resetInterval = () => {
    if (intervalRef.value) {
        clearInterval(intervalRef.value);
    }
    startInterval();
};

onMounted(() => {
    fetchShowcases().then(() => {
        if (canNavigate.value) {
            startInterval();
        }
    });
});

onUnmounted(() => {
    if (intervalRef.value) {
        clearInterval(intervalRef.value);
    }
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
    
    <div v-else-if="section?.carousel_banner_image || showcases.length > 0" class="w-full bg-gray-800 py-12 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <!-- Section Header -->
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex-1">
                    <h2 class="mb-2 text-3xl font-bold uppercase text-white">
                        {{ section?.title || 'TIPURI DE PRODUSE' }}
                    </h2>
                    <p v-if="section?.description" class="text-gray-300">
                        {{ section.description }}
                    </p>
                </div>
                <button
                    v-if="section?.button_text"
                    @click="handleSectionButtonClick"
                    class="mt-4 rounded border border-gray-600 bg-transparent px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700 md:mt-0"
                >
                    {{ section.button_text }}
                </button>
            </div>

            <!-- Carousel Container - 5 Cards: 1 Banner + 4 Category Cards -->
            <div class="relative">
                <!-- Navigation Arrows -->
                <button
                    v-if="canNavigate"
                    @click="prevSlide"
                    class="absolute left-0 top-1/2 z-10 -translate-y-1/2 -translate-x-4 rounded-full bg-white p-2 shadow-lg transition-all hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700"
                >
                    <ChevronLeft class="h-6 w-6 text-gray-900 dark:text-white" />
                </button>
                <button
                    v-if="canNavigate"
                    @click="nextSlide"
                    class="absolute right-0 top-1/2 z-10 -translate-y-1/2 translate-x-4 rounded-full bg-white p-2 shadow-lg transition-all hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700"
                >
                    <ChevronRight class="h-6 w-6 text-gray-900 dark:text-white" />
                </button>

                <!-- 5 Cards Grid: Banner (fixed) + 4 Category Cards (moving) -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <!-- Banner Image (First Card - Fixed) -->
                    <div
                        v-if="section?.carousel_banner_image"
                        class="cursor-pointer"
                        @click="handleBannerClick"
                    >
                        <div class="h-64 w-full rounded-lg overflow-hidden bg-gray-700 transition-shadow hover:shadow-xl">
                            <img
                                :src="section.carousel_banner_image"
                                :alt="section.title || 'Banner'"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>

                    <!-- Category Cards (4 Cards - Moving) -->
                    <div
                        v-for="(showcase, index) in visibleShowcases"
                        :key="`${showcase.id}-${currentIndex}-${index}`"
                        class="group relative h-64 cursor-pointer overflow-hidden rounded-lg bg-white shadow-md transition-shadow hover:shadow-xl dark:bg-gray-800"
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
            </div>

            <!-- Bottom Button (if exists) -->
            <div v-if="section?.button_text" class="mt-8 flex justify-center">
                <button
                    @click="handleSectionButtonClick"
                    class="rounded border border-gray-600 bg-transparent px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700"
                >
                    {{ section.button_text }}
                </button>
            </div>
        </div>
    </div>
</template>
