<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useApiCache } from '@/composables/useApiCache';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';

interface Portfolio {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    images: string[];
    is_active: boolean;
}

const apiCache = useApiCache();
const portfolios = ref<Portfolio[]>([]);
const carouselRef = ref<HTMLElement | null>(null);
const scrollPosition = ref(0);

const fetchPortfolios = async () => {
    try {
        const data = await apiCache.fetchWithCache<{ data: Portfolio[] }>(
            '/api/portfolios?active_only=true&order_by=created_at&order_dir=desc&per_page=10',
            {
                key: 'portfolio_carousel_api_cache',
                ttl: 30 * 60 * 1000,
                version: '1.0',
            }
        );
        
        if (data?.data && Array.isArray(data.data)) {
            portfolios.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching portfolios:', error);
        const cached = apiCache.loadFromCache<{ data: Portfolio[] }>({
            key: 'portfolio_carousel_api_cache',
            ttl: 30 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data)) {
            portfolios.value = cached.data;
        }
    }
};

const canScrollPrev = computed(() => scrollPosition.value > 0);
const canScrollNext = computed(() => {
    if (!carouselRef.value) return false;
    const { scrollLeft, scrollWidth, clientWidth } = carouselRef.value;
    return scrollLeft < scrollWidth - clientWidth - 10;
});

const scrollPrev = () => {
    if (!carouselRef.value) return;
    const cardWidth = carouselRef.value.clientWidth / 4;
    carouselRef.value.scrollBy({
        left: -cardWidth,
        behavior: 'smooth',
    });
};

const scrollNext = () => {
    if (!carouselRef.value) return;
    const cardWidth = carouselRef.value.clientWidth / 4;
    carouselRef.value.scrollBy({
        left: cardWidth,
        behavior: 'smooth',
    });
};

const updateScrollPosition = () => {
    if (carouselRef.value) {
        scrollPosition.value = carouselRef.value.scrollLeft;
    }
};

onMounted(() => {
    fetchPortfolios();
});

// Watch for carouselRef to be available
watch(carouselRef, (newRef) => {
    if (newRef) {
        newRef.addEventListener('scroll', updateScrollPosition);
        // Initial check
        nextTick(() => {
            updateScrollPosition();
        });
    }
}, { immediate: true });
</script>

<template>
    <div v-if="portfolios.length > 0" class="w-full bg-gray-50 dark:bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="rounded-lg bg-white p-8 dark:bg-gray-800">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-bold uppercase tracking-tight text-gray-800 dark:text-white md:text-3xl">
                        Lucrări Realizate
                    </h2>
                </div>

                <!-- Carousel -->
                <div class="relative flex items-center gap-4">
                    <!-- Previous Button -->
                    <button
                        v-if="canScrollPrev"
                        @click="scrollPrev"
                        class="flex-shrink-0 h-10 w-10 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center transition-colors"
                        aria-label="Previous slide"
                    >
                        <ArrowLeft class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                    </button>
                    <div v-else class="w-10"></div>

                    <!-- Carousel Container -->
                    <div class="flex-1 overflow-hidden">
                        <div
                            ref="carouselRef"
                            class="flex gap-4 overflow-x-auto scroll-smooth [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                        >
                            <div
                                v-for="portfolio in portfolios"
                                :key="portfolio.id"
                                class="flex-shrink-0 w-1/4"
                            >
                                <div class="p-1 h-full">
                                    <div class="overflow-hidden h-full flex flex-col rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                                        <!-- Image -->
                                        <div class="relative h-64 overflow-hidden bg-gray-200 dark:bg-gray-600 flex-shrink-0">
                                            <img
                                                v-if="portfolio.image"
                                                :src="portfolio.image"
                                                :alt="portfolio.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center"
                                            >
                                                <span class="text-gray-400 dark:text-gray-500">Fără imagine</span>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="p-6 flex flex-col flex-1">
                                            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 line-clamp-2">
                                                {{ portfolio.name }}
                                            </h3>
                                            <div
                                                v-if="portfolio.description"
                                                class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 line-clamp-3 mb-4 flex-1"
                                                v-html="portfolio.description"
                                            ></div>
                                            <Link
                                                :href="`/portfolios/${portfolio.slug}`"
                                                class="inline-flex items-center text-sm font-semibold text-teal-700 dark:text-teal-500 hover:text-teal-800 dark:hover:text-teal-400 transition-colors mt-auto"
                                            >
                                                Vezi detalii
                                                <span class="ml-1">→</span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <button
                        v-if="canScrollNext"
                        @click="scrollNext"
                        class="flex-shrink-0 h-10 w-10 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center transition-colors"
                        aria-label="Next slide"
                    >
                        <ArrowRight class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                    </button>
                    <div v-else class="w-10"></div>
                </div>
            </div>
        </div>
    </div>
</template>
