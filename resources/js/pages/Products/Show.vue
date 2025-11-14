<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Button } from '@/components/ui/button';
import { ShoppingCart, Heart, ArrowLeft } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    originalPrice?: number;
    image: string;
    images?: string[];
    description?: string;
    shortDescription?: string;
    discount?: number;
    stockQuantity: number;
    inStock: boolean;
    sku?: string;
    category?: {
        id: number;
        name: string;
        slug: string;
    };
}

interface RelatedProduct {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    description?: string;
    discount?: number;
}

interface Props {
    product: Product;
    relatedProducts: RelatedProduct[];
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const isOutOfStock = computed(() => {
    return !props.product.inStock || props.product.stockQuantity === 0;
});

const loading = ref(false);

const addToCart = async () => {
    if (isOutOfStock.value) {
        return;
    }

    loading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            alert('Eroare: Token CSRF lipsă. Te rugăm să reîmprospătezi pagina.');
            loading.value = false;
            return;
        }

        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                product_id: props.product.id,
                quantity: 1,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Eroare necunoscută' }));
            alert(errorData.message || 'Eroare la adăugarea produsului în coș');
            return;
        }

        await response.json();

        // Emit event pentru a actualiza header-ul
        window.dispatchEvent(new CustomEvent('cart-updated'));
        
        // Mesaj de succes
        alert('Produs adăugat în coș cu succes!');
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Eroare la adăugarea produsului în coș. Te rugăm să încerci din nou.');
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head :title="props.product.name" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1" :class="{ 'opacity-60': isOutOfStock }">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Back Button -->
                <Link
                    href="/"
                    class="mb-6 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Înapoi la produse
                </Link>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div class="aspect-square overflow-hidden rounded-lg border bg-gray-100">
                            <img
                                :src="props.product.image"
                                :alt="props.product.name"
                                class="h-full w-full object-cover"
                                :class="{ 'grayscale': isOutOfStock }"
                            />
                        </div>
                        <div
                            v-if="props.product.images && props.product.images.length > 0"
                            class="grid grid-cols-4 gap-4"
                        >
                            <div
                                v-for="(image, index) in props.product.images"
                                :key="index"
                                class="aspect-square overflow-hidden rounded-lg border bg-gray-100"
                            >
                                <img
                                    :src="image"
                                    :alt="`${props.product.name} - Imagine ${index + 1}`"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ props.product.name }}
                            </h1>
                            <p
                                v-if="props.product.category"
                                class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <Link
                                    :href="`/categories/${props.product.category.slug}`"
                                    class="hover:underline"
                                >
                                    {{ props.product.category.name }}
                                </Link>
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ formatPrice(props.product.price) }}
                            </span>
                            <span
                                v-if="props.product.originalPrice"
                                class="text-xl text-gray-500 line-through"
                            >
                                {{ formatPrice(props.product.originalPrice) }}
                            </span>
                            <span
                                v-if="props.product.discount"
                                class="rounded bg-red-500 px-3 py-1 text-sm font-bold text-white"
                            >
                                -{{ props.product.discount }}%
                            </span>
                        </div>

                        <div
                            v-if="props.product.description"
                            class="prose max-w-none dark:prose-invert"
                        >
                            <p class="text-gray-700 dark:text-gray-300">
                                {{ props.product.description }}
                            </p>
                        </div>

                        <div class="space-y-4 border-t pt-6">
                            <div class="flex items-center gap-4">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    SKU:
                                </span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ props.product.sku || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    Disponibilitate:
                                </span>
                                <span
                                    :class="[
                                        isOutOfStock
                                            ? 'text-red-600 dark:text-red-400 font-semibold'
                                            : 'text-green-600 dark:text-green-400',
                                    ]"
                                >
                                    {{ isOutOfStock ? 'Nu este în stoc' : 'În stoc' }}
                                </span>
                            </div>
                            <div
                                v-if="!isOutOfStock && props.product.stockQuantity > 0"
                                class="flex items-center gap-4"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">
                                    Cantitate disponibilă:
                                </span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ props.product.stockQuantity }} bucăți
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button
                                :disabled="isOutOfStock || loading"
                                class="flex-1"
                                size="lg"
                                :class="{ 'opacity-50 cursor-not-allowed': isOutOfStock || loading }"
                                @click="addToCart"
                            >
                                <ShoppingCart class="mr-2 h-5 w-5" />
                                {{ loading ? 'Se adaugă...' : isOutOfStock ? 'Produs indisponibil' : 'Adaugă în coș' }}
                            </Button>
                            <Button
                                variant="outline"
                                size="lg"
                                :disabled="isOutOfStock"
                                :class="{ 'opacity-50 cursor-not-allowed': isOutOfStock }"
                            >
                                <Heart class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div
                    v-if="props.relatedProducts.length > 0"
                    class="mt-16"
                >
                    <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                        Produse similare
                    </h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <ProductCard
                            v-for="relatedProduct in props.relatedProducts"
                            :key="relatedProduct.id"
                            :product="relatedProduct"
                        />
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

