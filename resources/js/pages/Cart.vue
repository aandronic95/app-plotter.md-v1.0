<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart, Trash2, Plus, Minus, ArrowLeft } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface CartItem {
    id: number;
    name: string;
    slug: string;
    price: number;
    image: string;
    quantity: number;
    subtotal: number;
    stock_quantity: number;
}

interface CartData {
    items: CartItem[];
    total: number;
    count: number;
}

const cart = ref<CartData>({
    items: [],
    total: 0,
    count: 0,
});

const loading = ref(false);
const { t } = useTranslations();

const fetchCart = async () => {
    try {
        const response = await fetch('/cart/data');
        cart.value = await response.json();
    } catch (error) {
        console.error('Error fetching cart:', error);
    }
};

const updateQuantity = async (productId: number, quantity: number) => {
    if (quantity < 1) {
        return;
    }

    loading.value = true;
    try {
        const response = await fetch('/cart/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
            }),
        });

        if (response.ok) {
            await fetchCart();
        } else {
            const data = await response.json();
            alert(data.message || t('error_updating_quantity'));
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert(t('error_updating_quantity'));
    } finally {
        loading.value = false;
    }
};

const removeItem = async (productId: number) => {
    if (!confirm(t('remove_item_confirm'))) {
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/cart/remove/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            await fetchCart();
        }
    } catch (error) {
        console.error('Error removing item:', error);
        alert(t('error_removing_item'));
    } finally {
        loading.value = false;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

onMounted(() => {
    fetchCart();
});
</script>

<template>
    <Head :title="t('cart_title')" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <div class="mb-6 flex items-center gap-4">
                    <Link
                        href="/"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ t('back_to_products') }}
                    </Link>
                </div>

                <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('cart_title') }}
                </h1>

                <div
                    v-if="cart.items.length === 0"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <ShoppingCart class="mb-4 h-16 w-16 text-gray-400" />
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">
                        {{ t('cart_empty') }}
                    </p>
                    <Link href="/">
                        <Button>{{ t('continue_shopping') }}</Button>
                    </Link>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-6 lg:grid-cols-3"
                >
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <Card
                            v-for="item in cart.items"
                            :key="item.id"
                        >
                            <CardContent class="p-4">
                                <div class="flex gap-4">
                                    <Link
                                        :href="`/products/${item.slug}`"
                                        class="flex-shrink-0"
                                    >
                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="h-24 w-24 rounded-lg object-cover"
                                        />
                                    </Link>
                                    <div class="flex flex-1 flex-col justify-between">
                                        <div>
                                            <Link
                                                :href="`/products/${item.slug}`"
                                                class="text-lg font-semibold text-gray-900 hover:underline dark:text-white"
                                            >
                                                {{ item.name }}
                                            </Link>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {{ formatPrice(item.price) }} {{ t('per_item') }}
                                            </p>
                                        </div>
                                        <div class="mt-4 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    :disabled="item.quantity <= 1 || loading"
                                                    @click="updateQuantity(item.id, item.quantity - 1)"
                                                >
                                                    <Minus class="h-4 w-4" />
                                                </Button>
                                                <span class="w-12 text-center font-medium">
                                                    {{ item.quantity }}
                                                </span>
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    :disabled="item.quantity >= item.stock_quantity || loading"
                                                    @click="updateQuantity(item.id, item.quantity + 1)"
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ formatPrice(item.subtotal) }}
                                                </span>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    :disabled="loading"
                                                    @click="removeItem(item.id)"
                                                >
                                                    <Trash2 class="h-4 w-4 text-red-500" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-24">
                            <CardHeader>
                                <CardTitle>{{ t('order_summary') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ t('subtotal') }}
                                    </span>
                                    <span class="font-medium">
                                        {{ formatPrice(cart.total) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ t('shipping') }}
                                    </span>
                                    <span class="font-medium">
                                        {{ cart.total > 500 ? t('free') : formatPrice(50) }}
                                    </span>
                                </div>
                                <div class="pt-4">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>{{ t('total') }}</span>
                                        <span>
                                            {{ formatPrice(cart.total + (cart.total > 500 ? 0 : 50) + cart.total * 0.19) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ t('vat_included') }}
                                    </p>
                                </div>
                                <Link
                                    href="/checkout"
                                    class="block"
                                >
                                    <Button
                                        class="w-full"
                                        size="lg"
                                    >
                                        {{ t('complete_order') }}
                                    </Button>
                                </Link>
                                <Link
                                    href="/"
                                    class="block"
                                >
                                    <Button
                                        variant="outline"
                                        class="w-full"
                                    >
                                        {{ t('continue_shopping') }}
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

