<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import CategoriesSidebar from '@/components/CategoriesSidebar.vue';
import ProductCard from '@/components/ProductCard.vue';
import PromotionCarousel from '@/components/PromotionCarousel.vue';
import { Head } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    slug?: string;
    price: number;
    originalPrice?: number;
    image: string;
    description?: string;
    discount?: number;
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
</script>

<template>
    <Head title="Acasă" />
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
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Produse Populare
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
                                Nu există produse disponibile momentan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <AppFooter />
    </div>
</template>

