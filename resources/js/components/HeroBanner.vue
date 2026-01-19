<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { useApiCache } from '@/composables/useApiCache';

interface HeroBanner {
    id: number;
    headline: string | null;
    title: string;
    description: string | null;
    features: string[];
    button1_text: string | null;
    button1_link: string | null;
    button2_text: string | null;
    button2_link: string | null;
    image: string | null;
    is_active: boolean;
    sort_order: number;
    rotating_words?: string[];
}

const apiCache = useApiCache();
const banner = ref<HeroBanner | null>(null);

const defaultRotatingWords = ['HAINE', 'CĂRȚI DE VIZITE', 'BANERE', 'CUTII', 'POSTERE'];
const rotatingWords = ref<string[]>(defaultRotatingWords);
const currentWordIndex = ref(0);
const displayedText = ref('');
const isDeleting = ref(false);
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

const typeText = () => {
    if (typingTimeout) {
        clearTimeout(typingTimeout);
        typingTimeout = null;
    }
    
    if (rotatingWords.value.length === 0) return;
    
    const currentWord = rotatingWords.value[currentWordIndex.value];
    if (!currentWord) return;
    
    let typingSpeed = 100;
    
    if (isDeleting.value) {
        if (displayedText.value.length > 0) {
            displayedText.value = currentWord.substring(0, displayedText.value.length - 1);
            typingSpeed = 50;
        } else {
            isDeleting.value = false;
            currentWordIndex.value = (currentWordIndex.value + 1) % rotatingWords.value.length;
            typingSpeed = 300;
        }
    } else {
        const currentLength = displayedText.value.length;
        const targetLength = currentWord.length;
        
        if (currentLength < targetLength) {
            displayedText.value = currentWord.substring(0, currentLength + 1);
            typingSpeed = 100;
        } else {
            typingSpeed = 2000;
            isDeleting.value = true;
        }
    }
    
    typingTimeout = setTimeout(() => {
        typeText();
    }, typingSpeed);
};

const startTypingAnimation = () => {
    // Clear any existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
        typingTimeout = null;
    }
    
    // Reset animation state
    currentWordIndex.value = 0;
    displayedText.value = '';
    isDeleting.value = false;
    
    // Start animation after a small delay to ensure DOM is ready
    setTimeout(() => {
        typeText();
    }, 100);
};

const fetchBanner = async () => {
    try {
        const data = await apiCache.fetchWithCache<{ data: HeroBanner[] }>(
            '/api/hero-banners?active_only=true&order_by=sort_order&order_dir=asc&per_page=1',
            {
                key: 'hero_banner_api_cache',
                ttl: 30 * 60 * 1000,
                version: '1.0',
            }
        );
        
        if (data?.data && Array.isArray(data.data) && data.data.length > 0) {
            banner.value = data.data[0];
            // Update rotating words from banner data
            if (banner.value.rotating_words && banner.value.rotating_words.length > 0) {
                rotatingWords.value = banner.value.rotating_words;
            }
        }
    } catch (error) {
        console.error('Error fetching hero banner:', error);
        const cached = apiCache.loadFromCache<{ data: HeroBanner[] }>({
            key: 'hero_banner_api_cache',
            ttl: 30 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            banner.value = cached.data[0];
            if (banner.value.rotating_words && banner.value.rotating_words.length > 0) {
                rotatingWords.value = banner.value.rotating_words;
            }
        }
    }
    
    // Start animation after 5 seconds
    setTimeout(() => {
        startTypingAnimation();
    }, 5000);
};

onMounted(() => {
    fetchBanner();
});

onUnmounted(() => {
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }
});
</script>

<template>
    <div class="text-gray-900 dark:text-white w-full bg-transparent dark:bg-gray-800 rounded-lg mt-10">
        <div class="text-gray-900 dark:text-white bg-gray-200 dark:bg-gray-800 mx-auto max-w-7xl rounded-lg overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:items-center">
                <div class="space-y-6 px-4 py-12 md:px-6 lg:py-16">
                    <div v-if="banner?.headline" class="flex items-center gap-2">
                        <span class="text-gray-900 dark:text-white text-lg">🔥</span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">
                            {{ banner?.headline }}
                        </span>
                    </div>

                    <h1 class="text-4xl font-bold uppercase tracking-tight text-gray-800 dark:text-white md:text-5xl lg:text-6xl">
                        <template v-if="banner?.title && banner.title.trim()">
                            {{ banner.title }}
                        </template>
                        <template v-else>
                            PRINTĂM 
                            <span class="inline-block min-w-[200px] text-teal-800 dark:text-teal-500">
                                {{ displayedText || '&nbsp;' }}
                                <span v-if="displayedText" class="animate-pulse">|</span>
                            </span>
                        </template>
                    </h1>

                    <p
                        v-if="banner?.description"
                        class="text-lg text-gray-600 dark:text-gray-300"
                    >
                        {{ banner?.description }}
                    </p>

                    <div v-if="banner?.features && banner.features.length > 0" class="flex flex-wrap gap-4">
                        <div
                            v-for="(feature, index) in banner.features"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-800 text-white dark:bg-teal-500">
                                <Check class="h-4 w-4" />
                            </div>
                            <span class="font-semibold text-gray-800 dark:text-white">
                                {{ feature }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <Button
                            v-if="banner?.button1_text && banner.button1_link"
                            as-child
                            size="lg"
                            class="bg-teal-800 hover:bg-teal-800 dark:bg-teal-500 dark:hover:bg-teal-600 text-white"
                        >
                            <Link :href="banner.button1_link">
                                {{ banner.button1_text }}
                                <span class="ml-2">></span>
                            </Link>
                        </Button>
                        <Button
                            v-if="banner?.button2_text && banner.button2_link"
                            as-child
                            size="lg"
                            variant="outline"
                            class="border-2 border-gray-800 bg-gray-800 text-white hover:bg-gray-800 dark:border-gray-200 dark:bg-gray-200 dark:text-gray-900 dark:hover:bg-gray-300"
                        >
                            <Link :href="banner.button2_link">
                                {{ banner.button2_text }}
                                <span class="ml-2">></span>
                            </Link>
                        </Button>
                    </div>
                </div>

                <div class="relative h-64 lg:h-[500px] max-h-[500px]">
                    <div
                        v-if="banner?.image"
                        class="relative h-full w-full overflow-hidden bg-gray-200 dark:bg-gray-800"
                    >
                        <img
                            :src="banner.image"
                            :alt="banner?.title || 'Banner'"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="relative h-full w-full overflow-hidden bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 dark:from-blue-600 dark:via-purple-600 dark:to-pink-600"
                    >
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="text-2xl font-bold text-white opacity-50 dark:opacity-70">
                                {{ banner?.title || 'PRINTĂM' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

