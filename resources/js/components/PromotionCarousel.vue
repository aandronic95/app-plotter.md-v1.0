<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

interface Promotion {
    id: number;
    title: string;
    description: string;
    image: string;
    link?: string;
}

const props = withDefaults(
    defineProps<{
        promotions?: Promotion[];
    }>(),
    {
        promotions: () => [
            {
                id: 1,
                title: 'Promoție Specială',
                description: 'Reduceri până la 50%',
                image: 'https://img.freepik.com/free-vector/modern-black-friday-holiday-sale-offer-banner-get-30-percent-price-drop-vector_1017-47794.jpg?semt=ais_hybrid&w=740&q=80',
            },
            {
                id: 2,
                title: 'Oferte Noi',
                description: 'Produse noi la prețuri speciale',
                image: 'https://img.freepik.com/free-vector/modern-black-friday-holiday-sale-offer-banner-get-30-percent-price-drop-vector_1017-47794.jpg?semt=ais_hybrid&w=740&q=80',
            },
            {
                id: 3,
                title: 'Black Friday',
                description: 'Cele mai bune oferte',
                image: 'https://img.freepik.com/free-vector/modern-black-friday-holiday-sale-offer-banner-get-30-percent-price-drop-vector_1017-47794.jpg?semt=ais_hybrid&w=740&q=80',
            },
        ],
    },
);

const currentIndex = ref(0);
const intervalRef = ref<number | null>(null);

const goToSlide = (index: number) => {
    currentIndex.value = index;
    resetInterval();
};

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % props.promotions.length;
    resetInterval();
};

const prevSlide = () => {
    currentIndex.value =
        (currentIndex.value - 1 + props.promotions.length) %
        props.promotions.length;
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

onMounted(() => {
    startInterval();
});

onUnmounted(() => {
    if (intervalRef.value) {
        clearInterval(intervalRef.value);
    }
});
</script>

<template>
    <div class="relative w-full overflow-hidden rounded-lg">
        <div
            class="relative flex transition-transform duration-500 ease-in-out"
            :style="{
                transform: `translateX(-${currentIndex * 100}%)`,
            }"
        >
            <div
                v-for="promotion in promotions"
                :key="promotion.id"
                class="min-w-full"
            >
                <div
                    class="relative h-64 w-full bg-gradient-to-r from-blue-600 to-purple-600 md:h-96"
                >
                    <img
                        v-if="promotion.image"
                        :src="promotion.image"
                        :alt="promotion.title"
                        class="h-full w-full object-cover"
                    />
                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center bg-black/30 p-8 text-center text-white"
                    >
                        <h2 class="mb-4 text-3xl font-bold md:text-5xl">
                            {{ promotion.title }}
                        </h2>
                        <p class="mb-6 text-lg md:text-xl">
                            {{ promotion.description }}
                        </p>
                        <Button
                            v-if="promotion.link"
                            size="lg"
                            class="bg-white text-gray-900 hover:bg-gray-100"
                        >
                            Vezi oferta
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <Button
            variant="outline"
            size="icon"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white"
            @click="prevSlide"
        >
            <ChevronLeft class="h-6 w-6" />
        </Button>
        <Button
            variant="outline"
            size="icon"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white"
            @click="nextSlide"
        >
            <ChevronRight class="h-6 w-6" />
        </Button>

        <!-- Dots Indicator -->
        <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
            <button
                v-for="(promotion, index) in promotions"
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

