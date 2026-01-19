<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { ShoppingCart, Heart } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

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

const props = defineProps<{
    product: Product;
}>();

const { t } = useTranslations();
const loading = ref(false);
const isInWishlist = ref(false);
const wishlistLoading = ref(false);

// Check if product is in stock (support both camelCase and snake_case)
const isInStock = computed(() => {
    return props.product.inStock !== false && props.product.in_stock !== false;
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const addToCart = async () => {
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
        
        // Mesaj de succes (opțional - poate fi înlocuit cu toast notification)
        // alert('Produs adăugat în coș cu succes!');
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert(t('error_adding_to_cart_retry'));
    } finally {
        loading.value = false;
    }
};

// Wishlist status will be set by parent component via provide/inject or props
// This function is kept for backward compatibility but won't be called automatically
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const checkWishlistStatus = async () => {
    // This is now handled by batch checking in parent components
    // Kept for manual refresh if needed
};

const toggleWishlist = async (event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    
    if (wishlistLoading.value || !props.product.id) return;

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
                // Update cache in batch composable if available
                if (window.wishlistBatchUpdate) {
                    window.wishlistBatchUpdate(props.product.id, false);
                }
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
                // Update cache in batch composable if available
                if (window.wishlistBatchUpdate) {
                    window.wishlistBatchUpdate(props.product.id, true);
                }
            }
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
    } finally {
        wishlistLoading.value = false;
    }
};

// Expose method to set wishlist status from parent
defineExpose({
    setWishlistStatus: (status: boolean) => {
        isInWishlist.value = status;
    },
    getWishlistStatus: () => isInWishlist.value,
});
</script>

<template>
    <Card class="group flex h-full flex-col overflow-hidden">
        <div class="relative aspect-square w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
                :src="product.image"
                :alt="product.name"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-if="!isInStock"
                class="absolute left-2 top-2 rounded bg-gray-600 px-2 py-1 text-xs font-bold text-white"
            >
                {{ t('not_in_stock') }}
            </div>
            <div
                v-else-if="product.discount"
                class="absolute left-2 top-2 rounded bg-red-500 px-2 py-1 text-xs font-bold text-white"
            >
                -{{ product.discount }}%
            </div>
            <Button
                variant="ghost"
                size="icon"
                :disabled="wishlistLoading"
                :class="[
                    'absolute right-2 top-2 bg-white/80 dark:bg-gray-800/80 opacity-0 transition-opacity hover:bg-white dark:hover:bg-gray-800 group-hover:opacity-100',
                    isInWishlist && 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300',
                ]"
                @click="toggleWishlist"
            >
                <Heart 
                    class="h-4 w-4 text-current" 
                    :class="{ 'fill-current': isInWishlist }"
                />
            </Button>
        </div>
        <CardHeader class="flex-1">
            <h3 class="line-clamp-2 text-lg font-semibold text-gray-900 dark:text-white">
                {{ product.name }}
            </h3>
            <p
                v-if="product.description"
                class="mt-1 line-clamp-2 text-sm text-gray-600 dark:text-gray-400"
            >
                {{ product.description }}
            </p>
        </CardHeader>
        <CardContent class="pt-0">
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ formatPrice(product.price) }}
                </span>
                <span
                    v-if="product.originalPrice"
                    class="text-sm text-gray-500 dark:text-gray-400 line-through"
                >
                    {{ formatPrice(product.originalPrice) }}
                </span>
            </div>
        </CardContent>
        <CardFooter class="gap-2 pt-0">
            <Button
                class="flex-1"
                size="sm"
                :disabled="loading || !isInStock"
                @click="addToCart"
            >
                <ShoppingCart class="mr-2 h-4 w-4 text-current" />
                {{ !isInStock ? t('not_in_stock') : (loading ? t('adding_to_cart') : t('add_to_cart')) }}
            </Button>
            <Button variant="outline" size="sm" as-child>
                <Link :href="`/products/${product.slug || product.id}`">{{ t('details') }}</Link>
            </Button>
        </CardFooter>
    </Card>
</template>

