<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
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
}

const apiCache = useApiCache();
const banner = ref<HeroBanner | null>(null);
const isLoading = ref(true);

// Typing animation for rotating words
const rotatingWords = ['HAINE', 'CĂRȚI DE VIZITE', 'BANERE', 'CUTII', 'POSTERE'];
const currentWordIndex = ref(0);
const displayedText = ref('');
const isDeleting = ref(false);
const typingSpeed = ref(100);
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

const typeText = () => {
    const currentWord = rotatingWords[currentWordIndex.value];
    
    if (isDeleting.value) {
        // Deleting text
        displayedText.value = currentWord.substring(0, displayedText.value.length - 1);
        typingSpeed.value = 50; // Faster when deleting
    } else {
        // Typing text
        displayedText.value = currentWord.substring(0, displayedText.value.length + 1);
        typingSpeed.value = 100; // Normal speed when typing
    }
    
    if (!isDeleting.value && displayedText.value === currentWord) {
        // Finished typing, wait then start deleting
        typingSpeed.value = 2000; // Wait 2 seconds before deleting
        isDeleting.value = true;
    } else if (isDeleting.value && displayedText.value === '') {
        // Finished deleting, move to next word
        isDeleting.value = false;
        currentWordIndex.value = (currentWordIndex.value + 1) % rotatingWords.length;
        typingSpeed.value = 500; // Wait 0.5 seconds before typing next word
    }
    
    typingTimeout = setTimeout(typeText, typingSpeed.value);
};

const fetchBanner = async () => {
    try {
        isLoading.value = true;
        
        const data = await apiCache.fetchWithCache<{ data: HeroBanner[] }>(
            '/api/hero-banners?active_only=true&order_by=sort_order&order_dir=asc&per_page=1',
            {
                key: 'hero_banner_api_cache',
                ttl: 30 * 60 * 1000, // 30 minutes
                version: '1.0',
            }
        );
        
        if (data.data && Array.isArray(data.data) && data.data.length > 0) {
            banner.value = data.data[0];
        }
    } catch (error) {
        console.error('Error fetching hero banner:', error);
        // Try to load from cache as fallback
        const cached = apiCache.loadFromCache<{ data: HeroBanner[] }>({
            key: 'hero_banner_api_cache',
            ttl: 30 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            banner.value = cached.data[0];
        }
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchBanner();
    // Start typing animation
    typeText();
});

onUnmounted(() => {
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }
});

const hasContent = computed(() => {
    return banner.value && !isLoading.value;
});
</script>

<template>
    <div v-if="isLoading" class="w-full bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800">
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="h-6 w-32 animate-pulse rounded-md bg-neutral-200 dark:bg-neutral-700"></div>
                    <div class="h-12 w-64 animate-pulse rounded-md bg-neutral-200 dark:bg-neutral-700"></div>
                    <div class="h-24 w-full animate-pulse rounded-md bg-neutral-200 dark:bg-neutral-700"></div>
                </div>
                <div class="h-64 animate-pulse rounded-lg bg-neutral-200 dark:bg-neutral-700 lg:h-96"></div>
            </div>
        </div>
    </div>

    <div
        v-else-if="hasContent"
        class="w-full bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800 rounded-lg mt-10"
    >
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:py-16 rounded-lg">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-center rounded-lg">
                <!-- Left Section: Text Content -->
                <div class="space-y-6">
                    <!-- Headline -->
                    <div v-if="banner?.headline" class="flex items-center gap-2">
                        <span class="text-lg">🔥</span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">
                            {{ banner?.headline }}
                        </span>
                    </div>

                    <!-- Title with typing animation -->
                    <h1 class="text-4xl font-bold uppercase tracking-tight text-gray-900 dark:text-white md:text-5xl lg:text-6xl">
                        PRINTĂM 
                        <span class="inline-block min-w-[200px] text-primary dark:text-primary-400">
                            {{ displayedText }}
                            <span class="animate-pulse">|</span>
                        </span>
                    </h1>

                    <!-- Description -->
                    <p
                        v-if="banner?.description"
                        class="text-lg text-gray-700 dark:text-gray-300"
                    >
                        {{ banner?.description }}
                    </p>

                    <!-- Features -->
                    <div v-if="banner?.features && banner.features.length > 0" class="flex flex-wrap gap-4">
                        <div
                            v-for="(feature, index) in banner.features"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-white">
                                <Check class="h-4 w-4" />
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ feature }}
                            </span>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap gap-4 pt-4">
                        <Button
                            v-if="banner?.button1_text && banner.button1_link"
                            as-child
                            size="lg"
                            class="bg-[#2d5016] hover:bg-[#1f350e] dark:bg-[#3d6b1f] dark:hover:bg-[#2d5016]"
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
                            class="border-2 border-gray-900 bg-gray-900 text-white hover:bg-gray-800 dark:border-gray-100 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200"
                        >
                            <Link :href="banner.button2_link">
                                {{ banner.button2_text }}
                                <span class="ml-2">></span>
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Right Section: Image -->
                <div class="relative">
                    <div
                        v-if="banner?.image"
                        class="relative h-64 w-full overflow-hidden rounded-lg bg-neutral-200 dark:bg-neutral-700 lg:h-96"
                    >
                        <img
                            :src="banner.image"
                            :alt="banner?.title || 'Banner'"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="relative h-64 w-full overflow-hidden rounded-lg bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 lg:h-96"
                    >
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="text-2xl font-bold text-white opacity-50">
                                {{ banner?.title || 'PRINTĂM' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

