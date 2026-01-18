<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import CategoriesSidebar from '@/components/CategoriesSidebar.vue';
import ProductCard from '@/components/ProductCard.vue';
import PromotionCarousel from '@/components/PromotionCarousel.vue';
import ContentTabs from '@/components/ContentTabs.vue';
import LoginSuccessModal from '@/components/LoginSuccessModal.vue';
import NewsletterForm from '@/components/NewsletterForm.vue';
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { useSEO } from '@/composables/useSEO';
import { useWishlistBatch } from '@/composables/useWishlistBatch';
import { ref, computed, onMounted, nextTick } from 'vue';
import StructuredData from '@/components/StructuredData.vue';
import HeroBanner from '@/components/HeroBanner.vue';
import ServiceFeatures from '@/components/ServiceFeatures.vue';
import CustomPrintBanner from '@/components/CustomPrintBanner.vue';

interface Product {
    id: number;
    name: string;
    slug?: string;
    price: number;
    originalPrice?: number;
    image: string;
    description?: string;
    discount?: number;
    inStock?: boolean;
    in_stock?: boolean;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    count?: number;
    children?: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
}

interface Props {
    products: Product[];
    categories: Category[];
}

const props = defineProps<Props>();

const { t } = useTranslations();
const seo = useSEO();
const { checkBatch } = useWishlistBatch();

const organizationStructuredData = computed(() => {
    return seo.structuredData.value;
});
const activeTab = ref('promotions');

// Product card refs for batch wishlist checking
const productCardRefs = ref<Record<number, any>>({});

// Fetch promotions count
const promotionsCount = ref(0);

const fetchPromotionsCount = async () => {
    try {
        const response = await fetch('/api/promotions?active_only=true');
        const data = await response.json();
        if (data.data && Array.isArray(data.data)) {
            promotionsCount.value = data.data.length;
        }
    } catch (error) {
        console.error('Error fetching promotions count:', error);
    }
};

// Batch check wishlist status for all products
const checkWishlistBatch = async () => {
    if (props.products.length === 0) return;
    
    const productIds = props.products.map(p => p.id);
    const statuses = await checkBatch(productIds);
    
    // Update each product card with its wishlist status
    await nextTick();
    Object.entries(statuses).forEach(([productId, inWishlist]) => {
        const cardRef = productCardRefs.value[Number(productId)];
        if (cardRef && cardRef.setWishlistStatus) {
            cardRef.setWishlistStatus(inWishlist);
        }
    });
};

// Fetch count on mount
onMounted(async () => {
    await Promise.all([
        fetchPromotionsCount(),
        checkWishlistBatch(),
    ]);
});

const tabs = computed(() => [
    { id: 'promotions', label: t('promotions'), count: promotionsCount.value, route: '/promotions' },
    { id: 'news', label: t('news'), route: '/news' },
    { id: 'events', label: t('events'), route: '/events' },
    { id: 'tips', label: t('useful_tips'), route: '/tips' },
    { id: 'reviews', label: t('reviews'), route: '/reviews' },
]);
</script>

<template>
    <Head>
        <title>{{ seo.title.value }}</title>
        <meta name="description" :content="seo.description.value" />
        <meta property="og:title" :content="seo.title.value" />
        <meta property="og:description" :content="seo.description.value" />
        <meta property="og:image" :content="seo.image.value" />
        <meta property="og:url" :content="seo.url.value" />
        <meta property="og:type" content="website" />
        <link rel="canonical" :href="seo.url.value" />
    </Head>
    
    <StructuredData type="Organization" :data="organizationStructuredData.value" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <!-- Header with Main Menu -->
        <PublicHeader />

        <!-- Main Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Carousel cu Promoții -->
                <div class="mb-8">
                    
                    <PromotionCarousel />
                    <!-- Hero Banner Section -->
                    <HeroBanner />  
                </div>


                <!-- Custom Print Banner Section -->
                <div class="mb-12">
                    <CustomPrintBanner />
                </div>

                <!-- Content Grid: Categories (stânga) + Products (dreapta) -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Categories Sidebar - Col 3 (stânga) -->
                    <aside class="lg:col-span-1">
                        <CategoriesSidebar :categories="$props.categories" />
                    </aside>

                    <!-- Products Grid - Col 3 (dreapta) -->
                    <div class="lg:col-span-3">
                                        <!-- Content Tabs -->
                <div class="mb-6">
                    <ContentTabs
                        :tabs="tabs"
                        :default-tab="activeTab"
                        @update:active-tab="activeTab = $event"
                    />
                </div>
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ t('popular_products') }}
                            </h2>
                        </div>
                        <div
                            v-if="props.products.length > 0"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <ProductCard
                                v-for="product in props.products"
                                :key="product.id"
                                :ref="el => { if (el) productCardRefs[product.id] = el; }"
                                :product="product"
                            />
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center py-12"
                        >
                            <p class="text-lg text-gray-500 dark:text-gray-400">
                                {{ t('no_products_available') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Newsletter Form -->
        <div class="w-full bg-gray-50 dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <NewsletterForm />
            </div>
        </div>
        <!-- Service Features Section -->
        <ServiceFeatures />
        <!-- Footer -->
        <AppFooter />
    </div>

    <!-- Login Success Modal -->
    <LoginSuccessModal />
</template>

