<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { ArrowLeft, ChevronLeft, ChevronRight, X } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { useSEO } from '@/composables/useSEO';
import StructuredData from '@/components/StructuredData.vue';

interface Portfolio {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    images: string[];
    is_active: boolean;
}

interface Props {
    portfolio: Portfolio | null;
}

const props = defineProps<Props>();
const { t } = useTranslations();

const portfolio = computed(() => props.portfolio);

const seo = useSEO({
    title: portfolio.value?.name || 'Portfolio',
    description: portfolio.value?.description || '',
    image: portfolio.value?.image || '',
    url: portfolio.value?.slug ? `/portfolios/${portfolio.value.slug}` : '/portfolios',
    type: 'article',
});

const portfolioStructuredData = computed(() => {
    if (!portfolio.value || !portfolio.value.name) {
        return null;
    }
    return {
        '@context': 'https://schema.org',
        '@type': 'CreativeWork',
        name: portfolio.value.name,
        description: portfolio.value.description || '',
        image: portfolio.value.image ? [portfolio.value.image] : [],
        url: `${window.location.origin}/portfolios/${portfolio.value.slug}`,
    };
});

const isModalOpen = ref(false);
const currentImageIndex = ref(0);

// Calculează toate imaginile disponibile (imaginea principală + galerie)
const allImages = computed(() => {
    if (!portfolio.value) return [];
    const images: string[] = [];
    if (portfolio.value.image) {
        images.push(portfolio.value.image);
    }
    if (portfolio.value.images && portfolio.value.images.length > 0) {
        images.push(...portfolio.value.images);
    }
    return images;
});

const openImageModal = (index: number) => {
    currentImageIndex.value = index;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = '';
};

const nextImage = () => {
    if (currentImageIndex.value < allImages.value.length - 1) {
        currentImageIndex.value++;
    } else {
        currentImageIndex.value = 0;
    }
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else {
        currentImageIndex.value = allImages.value.length - 1;
    }
};

// Navigare cu tastatura
const handleKeyDown = (event: KeyboardEvent) => {
    if (!isModalOpen.value) return;

    switch (event.key) {
        case 'Escape':
            closeImageModal();
            break;
        case 'ArrowLeft':
            previousImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    // Debug: verifică dacă portfolio-ul este primit
    console.log('Portfolio props:', props.portfolio);
    console.log('Portfolio computed:', portfolio.value);
    console.log('Portfolio name:', portfolio.value?.name);
    console.log('Portfolio description:', portfolio.value?.description);
    console.log('Portfolio image:', portfolio.value?.image);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Head>
        <title>{{ seo.title.value }}</title>
        <meta name="description" :content="seo.description.value" />
        <meta property="og:title" :content="seo.title.value" />
        <meta property="og:description" :content="seo.description.value" />
        <meta property="og:image" :content="seo.image.value" />
        <meta property="og:url" :content="seo.url.value" />
        <meta property="og:type" content="article" />
        <link rel="canonical" :href="seo.url.value" />
    </Head>

    <StructuredData v-if="portfolioStructuredData" type="CreativeWork" :data="portfolioStructuredData" />

    <div v-if="portfolio" class="flex min-h-screen flex-col bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <PublicHeader />

        <!-- Main Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Back Button -->
                <Link
                    href="/"
                    class="mb-6 inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Înapoi la pagina principală
                </Link>

                <!-- Portfolio Content -->
                <div class="rounded-lg bg-white p-8 dark:bg-gray-800">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Images Section -->
                        <div class="space-y-4">
                            <!-- Main Image -->
                            <div
                                v-if="portfolio && portfolio.image"
                                class="relative h-96 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700 cursor-pointer"
                                @click="openImageModal(0)"
                            >
                                <img
                                    :src="portfolio.image"
                                    :alt="portfolio.name || 'Portfolio'"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-96 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700"
                            >
                                <span class="text-gray-400 dark:text-gray-500">Fără imagine</span>
                            </div>

                            <!-- Gallery Images -->
                            <div
                                v-if="portfolio && portfolio.images && portfolio.images.length > 0"
                                class="grid grid-cols-4 gap-2"
                            >
                                <div
                                    v-for="(image, index) in portfolio.images"
                                    :key="index"
                                    class="relative h-20 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700 cursor-pointer hover:opacity-80 transition-opacity"
                                    @click="openImageModal(index + 1)"
                                >
                                    <img
                                        :src="image"
                                        :alt="`${portfolio.name || 'Portfolio'} - Imagine ${index + 2}`"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="space-y-6 overflow-hidden">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-800 dark:text-white md:text-4xl break-words">
                                    {{ portfolio && portfolio.name ? portfolio.name : 'Portfolio' }}
                                </h1>
                            </div>

                            <div
                                v-if="portfolio && portfolio.description"
                                class="prose max-w-none text-gray-600 dark:text-gray-300 [&_*]:text-gray-600 dark:[&_*]:text-gray-300 [&_p]:text-gray-600 dark:[&_p]:text-gray-300 [&_strong]:text-gray-800 dark:[&_strong]:text-white break-words overflow-hidden"
                                v-html="portfolio.description"
                            ></div>
                            <div
                                v-else-if="portfolio"
                                class="text-gray-500 dark:text-gray-400 italic"
                            >
                                Nu există descriere disponibilă.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <AppFooter />
    </div>
    <div v-else class="flex min-h-screen flex-col bg-gray-50 dark:bg-gray-900">
        <PublicHeader />
        <main class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Portfolio nu a fost găsit</h1>
                <Link
                    href="/"
                    class="inline-flex items-center text-sm font-medium text-teal-700 dark:text-teal-500 hover:text-teal-800 dark:hover:text-teal-400"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Înapoi la pagina principală
                </Link>
            </div>
        </main>
        <AppFooter />
    </div>

    <!-- Image Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm"
                @click.self="closeImageModal"
            >
                <!-- Close Button -->
                <button
                    @click="closeImageModal"
                    class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors"
                    aria-label="Închide"
                >
                    <X class="h-6 w-6" />
                </button>

                <!-- Previous Button -->
                <button
                    v-if="allImages.length > 1"
                    @click.stop="previousImage"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-10 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors"
                    aria-label="Imaginea anterioară"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>

                <!-- Next Button -->
                <button
                    v-if="allImages.length > 1"
                    @click.stop="nextImage"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-10 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors"
                    aria-label="Imaginea următoare"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>

                <!-- Image Container -->
                <div class="relative max-h-[90vh] max-w-[90vw]">
                    <Transition
                        enter-active-class="transition-all duration-300"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition-all duration-300"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                        mode="out-in"
                    >
                        <img
                            :key="currentImageIndex"
                            :src="allImages[currentImageIndex]"
                            :alt="`${portfolio?.name || 'Portfolio'} - Imagine ${currentImageIndex + 1}`"
                            class="max-h-[90vh] max-w-[90vw] object-contain"
                        />
                    </Transition>
                </div>

                <!-- Image Counter -->
                <div
                    v-if="allImages.length > 1"
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm"
                >
                    {{ currentImageIndex + 1 }} / {{ allImages.length }}
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

