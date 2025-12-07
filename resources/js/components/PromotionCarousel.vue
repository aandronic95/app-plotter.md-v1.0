<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, Clock } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { router } from '@inertiajs/vue3';

interface Promotion {
    id: number;
    title: string;
    description: string | null;
    image: string;
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
const currentIndex = ref(0);
const intervalRef = ref<number | null>(null);
const timerIntervalRef = ref<number | null>(null);
const currentTime = ref(new Date());

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

// Computed property to get current promotions or empty array
const activePromotions = computed(() => promotions.value);

// Get current promotion
const currentPromotion = computed(() => {
    if (activePromotions.value.length === 0) return null;
    return activePromotions.value[currentIndex.value];
});

// Calculate time remaining for current promotion
const timeRemaining = computed(() => {
    if (!currentPromotion.value?.end_date) {
        return null;
    }

    const endDate = new Date(currentPromotion.value.end_date);
    const now = currentTime.value;
    const diff = endDate.getTime() - now.getTime();

    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    return { days, hours, minutes, seconds, expired: false };
});

// Handle promotion click
const handlePromotionClick = (promotion: Promotion) => {
    // Check if it's an external link
    if (promotion.external_link) {
        window.open(promotion.external_link, '_blank');
        return;
    }

    // Check if it's a page link
    if (promotion.page_id && promotion.page?.slug) {
        router.visit(`/${promotion.page.slug}`);
        return;
    }

    // Check if it's a product link
    if (promotion.product_id && promotion.product?.slug) {
        router.visit(`/products/${promotion.product.slug}`);
        return;
    }
};

const goToSlide = (index: number) => {
    if (activePromotions.value.length === 0) return;
    currentIndex.value = index;
    resetInterval();
};

const nextSlide = () => {
    if (activePromotions.value.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % activePromotions.value.length;
    resetInterval();
};

const prevSlide = () => {
    if (activePromotions.value.length === 0) return;
    currentIndex.value =
        (currentIndex.value - 1 + activePromotions.value.length) %
        activePromotions.value.length;
    resetInterval();
};

const startInterval = () => {
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

// Start timer update interval
const startTimer = () => {
    timerIntervalRef.value = window.setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
};

// Stop timer
const stopTimer = () => {
    if (timerIntervalRef.value) {
        clearInterval(timerIntervalRef.value);
        timerIntervalRef.value = null;
    }
};

onMounted(() => {
    fetchPromotions().then(() => {
        if (activePromotions.value.length > 0) {
            startInterval();
        }
    });
    startTimer();
});

onUnmounted(() => {
    if (intervalRef.value) {
        clearInterval(intervalRef.value);
    }
    stopTimer();
});
</script>

<template>
    <div v-if="isLoading" class="relative w-full overflow-hidden rounded-lg">
        <div class="flex h-64 items-center justify-center bg-gray-200 md:h-96">
            <p class="text-gray-500">{{ t('loading') }}...</p>
        </div>
    </div>
    <div
        v-else-if="activePromotions.length > 0"
        class="relative w-full overflow-hidden rounded-lg"
    >
        <div
            class="relative flex transition-transform duration-500 ease-in-out"
            :style="{
                transform: `translateX(-${(currentIndex * 100) / activePromotions.length}%)`,
                width: `${activePromotions.length * 100}%`,
            }"
        >
            <div
                v-for="(promotion, index) in activePromotions"
                :key="promotion.id"
                class="flex-shrink-0"
                :style="{ width: `${100 / activePromotions.length}%` }"
            >
                <div
                    class="relative h-64 w-full bg-gradient-to-r from-blue-600 to-purple-600 md:h-96 cursor-pointer"
                    @click="handlePromotionClick(promotion)"
                >
                    <img
                        v-if="promotion.banner || promotion.image"
                        :src="promotion.banner || promotion.image"
                        :alt="promotion.title"
                        class="h-full w-full object-cover"
                    />
                    
                    <!-- Countdown Timer - Top Right -->
                    <div
                        v-if="index === currentIndex && timeRemaining && !timeRemaining.expired"
                        class="absolute right-4 top-4 z-10 rounded-xl bg-gradient-to-r from-red-600 to-red-500 px-5 py-3 shadow-2xl ring-2 ring-white/30 backdrop-blur-sm"
                        :class="{ 'animate-pulse': timeRemaining.hours < 24 }"
                    >
                        <div class="flex items-center gap-3 text-white">
                            <Clock class="h-6 w-6 animate-pulse" />
                            <div class="flex items-center gap-2 text-base font-extrabold">
                                <span v-if="timeRemaining.days > 0" class="flex flex-col items-center">
                                    <span class="rounded-lg bg-white/25 px-2.5 py-1 text-lg font-black shadow-inner">{{ String(timeRemaining.days).padStart(2, '0') }}</span>
                                    <span class="text-[10px] uppercase leading-tight">{{ t('days') }}</span>
                                </span>
                                <span class="flex flex-col items-center">
                                    <span class="rounded-lg bg-white/25 px-2.5 py-1 text-lg font-black shadow-inner">{{ String(timeRemaining.hours).padStart(2, '0') }}</span>
                                    <span class="text-[10px] uppercase leading-tight">{{ t('hours') }}</span>
                                </span>
                                <span class="text-2xl font-bold">:</span>
                                <span class="flex flex-col items-center">
                                    <span class="rounded-lg bg-white/25 px-2.5 py-1 text-lg font-black shadow-inner">{{ String(timeRemaining.minutes).padStart(2, '0') }}</span>
                                    <span class="text-[10px] uppercase leading-tight">{{ t('minutes') }}</span>
                                </span>
                                <span class="text-2xl font-bold">:</span>
                                <span class="flex flex-col items-center">
                                    <span class="rounded-lg bg-white/25 px-2.5 py-1 text-lg font-black shadow-inner animate-pulse">{{ String(timeRemaining.seconds).padStart(2, '0') }}</span>
                                    <span class="text-[10px] uppercase leading-tight">{{ t('seconds') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center bg-black/30 p-8 text-center text-white"
                    >
                        <h2 class="mb-4 text-3xl font-bold md:text-5xl">
                            {{ promotion.title }}
                        </h2>
                        <p v-if="promotion.description" class="mb-6 text-lg md:text-xl">
                            {{ promotion.description }}
                        </p>
                        <Button
                            v-if="promotion.external_link || promotion.page_id || promotion.product_id"
                            size="lg"
                            class="bg-white text-gray-900 hover:bg-gray-100"
                            @click.stop="handlePromotionClick(promotion)"
                        >
                            {{ t('view_offer') }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <Button
            v-if="activePromotions.length > 1"
            variant="outline"
            size="icon"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white"
            @click="prevSlide"
        >
            <ChevronLeft class="h-6 w-6" />
        </Button>
        <Button
            v-if="activePromotions.length > 1"
            variant="outline"
            size="icon"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white"
            @click="nextSlide"
        >
            <ChevronRight class="h-6 w-6" />
        </Button>

        <!-- Dots Indicator -->
        <div
            v-if="activePromotions.length > 1"
            class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2"
        >
            <button
                v-for="(promotion, index) in activePromotions"
                :key="promotion.id"
                :class="[
                    'h-2 w-2 rounded-full transition-all',
                    index === currentIndex
                        ? 'w-8 bg-white'
                        : 'bg-white/50 hover:bg-white/75',
                ]"
                @click="goToSlide(index)"
            />
        </div>
    </div>
</template>

