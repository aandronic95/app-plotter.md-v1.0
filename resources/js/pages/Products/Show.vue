<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Button } from '@/components/ui/button';
import { ShoppingCart, Heart, ArrowLeft, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { useSEO } from '@/composables/useSEO';
import StructuredData from '@/components/StructuredData.vue';

const STORAGE_KEY = 'recently_viewed_products';
const MAX_RECENT_PRODUCTS = 8;

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
    slug: string;
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
const { t } = useTranslations();

const seo = useSEO({
    title: props.product.name,
    description: props.product.shortDescription || props.product.description || '',
    image: props.product.image,
    url: `/products/${props.product.slug}`,
    type: 'product',
});

const productStructuredData = computed(() => {
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';
    const imageUrl = props.product.image.startsWith('http') 
        ? props.product.image 
        : `${baseUrl}${props.product.image}`;

    return {
        name: props.product.name,
        description: props.product.shortDescription || props.product.description || '',
        image: imageUrl,
        sku: props.product.sku || undefined,
        brand: {
            '@type': 'Brand',
            name: 'PLOTTER.MD',
        },
        offers: {
            '@type': 'Offer',
            url: `${baseUrl}/products/${props.product.slug}`,
            priceCurrency: 'MDL',
            price: props.product.price.toString(),
            availability: props.product.inStock 
                ? 'https://schema.org/InStock' 
                : 'https://schema.org/OutOfStock',
            itemCondition: 'https://schema.org/NewCondition',
        },
        category: props.product.category?.name,
    };
});

const breadcrumbStructuredData = computed(() => {
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';
    const items = [
        {
            '@type': 'ListItem',
            position: 1,
            name: t('home'),
            item: baseUrl,
        },
    ];

    if (props.product.category) {
        items.push({
            '@type': 'ListItem',
            position: items.length + 1,
            name: props.product.category.name,
            item: `${baseUrl}/categories/${props.product.category.slug}`,
        });
    }

    items.push({
        '@type': 'ListItem',
        position: items.length + 1,
        name: props.product.name,
        item: `${baseUrl}/products/${props.product.slug}`,
    });

    return {
        itemListElement: items,
    };
});

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
const isModalOpen = ref(false);
const currentImageIndex = ref(0);
const recentlyViewedProducts = ref<RelatedProduct[]>([]);
const isInWishlist = ref(false);
const wishlistLoading = ref(false);

// Calculează toate imaginile disponibile (imaginea principală + imagini suplimentare)
const allImages = computed(() => {
    const images = [props.product.image];
    if (props.product.images && props.product.images.length > 0) {
        images.push(...props.product.images);
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

// Funcții pentru gestionarea produselor vizualizate recent
const saveToRecentlyViewed = () => {
    try {
        const productData: RelatedProduct = {
            id: props.product.id,
            name: props.product.name,
            slug: props.product.slug,
            price: props.product.price,
            originalPrice: props.product.originalPrice,
            image: props.product.image,
            description: props.product.shortDescription || props.product.description,
            discount: props.product.discount,
        };

        // Obține produsele existente din localStorage
        const existing = localStorage.getItem(STORAGE_KEY);
        let products: RelatedProduct[] = existing ? JSON.parse(existing) : [];

        // Elimină produsul curent dacă există deja
        products = products.filter((p) => p.id !== productData.id);

        // Adaugă produsul curent la început
        products.unshift(productData);

        // Limitează la numărul maxim de produse
        products = products.slice(0, MAX_RECENT_PRODUCTS);

        // Salvează în localStorage
        localStorage.setItem(STORAGE_KEY, JSON.stringify(products));
    } catch (error) {
        console.error('Error saving recently viewed product:', error);
    }
};

const loadRecentlyViewed = () => {
    try {
        const existing = localStorage.getItem(STORAGE_KEY);
        if (!existing) {
            recentlyViewedProducts.value = [];
            return;
        }

        const products: RelatedProduct[] = JSON.parse(existing);
        
        // Exclude produsul curent din lista de produse vizualizate recent
        recentlyViewedProducts.value = products.filter(
            (p) => p.id !== props.product.id
        );
    } catch (error) {
        console.error('Error loading recently viewed products:', error);
        recentlyViewedProducts.value = [];
    }
};

const checkWishlistStatus = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            return;
        }

        const response = await fetch(`/wishlist/check/${props.product.id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (response.ok) {
            const data = await response.json();
            isInWishlist.value = data.in_wishlist || false;
        }
    } catch (error) {
        console.error('Error checking wishlist status:', error);
    }
};

const toggleWishlist = async () => {
    if (wishlistLoading.value) return;

    wishlistLoading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            alert(t('error_csrf_missing'));
            wishlistLoading.value = false;
            return;
        }

        if (isInWishlist.value) {
            // Remove from wishlist
            const response = await fetch(`/wishlist/${props.product.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                isInWishlist.value = false;
                alert(t('common.product_removed_from_wishlist'));
            } else {
                const errorData = await response.json().catch(() => ({ message: t('error_unknown') }));
                alert(errorData.message || t('common.error_removing_from_wishlist'));
            }
        } else {
            // Add to wishlist
            const response = await fetch('/wishlist', {
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
                }),
            });

            if (response.ok) {
                isInWishlist.value = true;
                alert(t('common.product_added_to_wishlist'));
            } else {
                const errorData = await response.json().catch(() => ({ message: t('error_unknown') }));
                alert(errorData.message || t('common.error_adding_to_wishlist'));
            }
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
        alert(t('common.error_unknown'));
    } finally {
        wishlistLoading.value = false;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    saveToRecentlyViewed();
    loadRecentlyViewed();
    checkWishlistStatus();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
});

const addToCart = async () => {
    if (isOutOfStock.value) {
        return;
    }

    loading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            alert(t('error_csrf_missing'));
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
            const errorData = await response.json().catch(() => ({ message: t('error_unknown') }));
            alert(errorData.message || t('error_adding_to_cart'));
            return;
        }

        await response.json();

        // Emit event pentru a actualiza header-ul
        window.dispatchEvent(new CustomEvent('cart-updated'));
        
        // Mesaj de succes
        alert(t('product_added_to_cart_success'));
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert(t('error_adding_to_cart_retry'));
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <meta property="og:title" :content="seo.title" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:image" :content="seo.image" />
        <meta property="og:url" :content="seo.url" />
        <meta property="og:type" content="product" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seo.title" />
        <meta name="twitter:description" :content="seo.description" />
        <meta name="twitter:image" :content="seo.image" />
        <link rel="canonical" :href="seo.url" />
    </Head>
    
    <StructuredData type="Product" :data="productStructuredData" />
    <StructuredData type="BreadcrumbList" :data="breadcrumbStructuredData" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1" :class="{ 'opacity-60': isOutOfStock, 'blur-sm': isModalOpen }">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Back Button -->
                <Link
                    href="/"
                    class="mb-6 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    {{ t('back_to_products') }}
                </Link>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div 
                            class="aspect-square overflow-hidden rounded-lg border bg-gray-100 cursor-pointer transition-transform hover:scale-105"
                            @click="openImageModal(0)"
                        >
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
                                class="aspect-square overflow-hidden rounded-lg border bg-gray-100 cursor-pointer transition-transform hover:scale-105"
                                @click="openImageModal(index + 1)"
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
                                    {{ isOutOfStock ? t('not_in_stock') : t('in_stock') }}
                                </span>
                            </div>
                            <div
                                v-if="!isOutOfStock && props.product.stockQuantity > 0"
                                class="flex items-center gap-4"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ t('stock') }}:
                                </span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ props.product.stockQuantity }} {{ t('pieces') }}
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
                                {{ loading ? t('adding_to_cart') : isOutOfStock ? t('product_unavailable') : t('add_to_cart') }}
                            </Button>
                            <Button
                                variant="outline"
                                size="lg"
                                :disabled="isOutOfStock || wishlistLoading"
                                :class="{ 
                                    'opacity-50 cursor-not-allowed': isOutOfStock || wishlistLoading,
                                    'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400': isInWishlist && !isOutOfStock && !wishlistLoading
                                }"
                                @click="toggleWishlist"
                            >
                                <Heart 
                                    class="h-5 w-5" 
                                    :class="{ 'fill-current': isInWishlist }"
                                />
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

                <!-- Recently Viewed Products -->
                <div
                    v-if="recentlyViewedProducts.length > 0"
                    class="mt-16"
                >
                    <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ t('recently_viewed') }}
                    </h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <ProductCard
                            v-for="recentProduct in recentlyViewedProducts"
                            :key="recentProduct.id"
                            :product="recentProduct"
                        />
                    </div>
                </div>
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
                    class="absolute top-4 right-4 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                    aria-label="Închide"
                >
                    <X class="h-6 w-6" />
                </button>

                <!-- Previous Button -->
                <button
                    v-if="allImages.length > 1"
                    @click="previousImage"
                    class="absolute left-4 z-10 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20"
                    :aria-label="t('previous_image')"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>

                <!-- Next Button -->
                <button
                    v-if="allImages.length > 1"
                    @click="nextImage"
                    class="absolute right-4 z-10 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20"
                    :aria-label="t('next_image')"
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
                            :alt="`${props.product.name} - Imagine ${currentImageIndex + 1}`"
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

