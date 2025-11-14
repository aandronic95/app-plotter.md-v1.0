<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { ShoppingCart, Heart } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const props = defineProps<{
    product: Product;
}>();

const loading = ref(false);

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
        
        // Mesaj de succes (opțional - poate fi înlocuit cu toast notification)
        // alert('Produs adăugat în coș cu succes!');
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Eroare la adăugarea produsului în coș. Te rugăm să încerci din nou.');
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Card class="group flex h-full flex-col overflow-hidden transition-shadow hover:shadow-lg">
        <div class="relative aspect-square w-full overflow-hidden bg-gray-100">
            <img
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-if="product.discount"
                class="absolute left-2 top-2 rounded bg-red-500 px-2 py-1 text-xs font-bold text-white"
            >
                -{{ product.discount }}%
            </div>
            <Button
                variant="ghost"
                size="icon"
                class="absolute right-2 top-2 bg-white/80 opacity-0 transition-opacity hover:bg-white group-hover:opacity-100"
            >
                <Heart class="h-4 w-4" />
            </Button>
        </div>
        <CardHeader class="flex-1">
            <h3 class="line-clamp-2 text-lg font-semibold">
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
                    class="text-sm text-gray-500 line-through"
                >
                    {{ formatPrice(product.originalPrice) }}
                </span>
            </div>
        </CardContent>
        <CardFooter class="gap-2 pt-0">
            <Button
                class="flex-1"
                size="sm"
                :disabled="loading"
                @click="addToCart"
            >
                <ShoppingCart class="mr-2 h-4 w-4" />
                {{ loading ? 'Se adaugă...' : 'Adaugă în coș' }}
            </Button>
            <Button variant="outline" size="sm" as-child>
                <Link :href="`/products/${product.slug || product.id}`">Detalii</Link>
            </Button>
        </CardFooter>
    </Card>
</template>

