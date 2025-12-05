<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import CategoriesSidebar from '@/components/CategoriesSidebar.vue';
import ProductCard from '@/components/ProductCard.vue';
import PromotionCarousel from '@/components/PromotionCarousel.vue';
import ContentTabs from '@/components/ContentTabs.vue';
import LoginSuccessModal from '@/components/LoginSuccessModal.vue';
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { ref, computed, onMounted } from 'vue';

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

defineProps<Props>();

const { t } = useTranslations();
const activeTab = ref('promotions');

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

// Fetch count on mount
onMounted(() => {
    fetchPromotionsCount();
});

const tabs = computed(() => [
    { id: 'promotions', label: 'Promoții', count: promotionsCount.value, route: '/promotions' },
    { id: 'news', label: 'Noutăți', route: '/news' },
    { id: 'events', label: 'Evenimente', route: '/events' },
    { id: 'tips', label: 'Sfatuti utile', route: '/tips' },
    { id: 'reviews', label: 'Review-uri', route: '/reviews' },
]);
</script>

<template>
    <Head :title="t('home')" />
    <div class="flex min-h-screen flex-col">
        <!-- Header with Main Menu -->
        <PublicHeader />

        <!-- Main Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Carousel cu Promoții -->
                <div class="mb-8">
                    <PromotionCarousel />
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
                            v-if="$props.products.length > 0"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <ProductCard
                                v-for="product in $props.products"
                                :key="product.id"
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

        <!-- Footer -->
        <AppFooter />
    </div>

    <!-- Login Success Modal -->
    <LoginSuccessModal />
</template>

