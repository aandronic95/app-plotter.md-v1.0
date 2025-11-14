<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface CartItem {
    id: number;
    name: string;
    price: number;
    image: string;
    quantity: number;
    subtotal: number;
}

interface Props {
    items: CartItem[];
    subtotal: number;
    tax: number;
    shippingCost: number;
    total: number;
}

const props = defineProps<Props>();

const form = useForm({
    shipping_name: '',
    shipping_email: '',
    shipping_phone: '',
    shipping_address: '',
    shipping_city: '',
    shipping_postal_code: '',
    shipping_country: 'Republica Moldova',
    notes: '',
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const submit = () => {
    form.post('/orders');
};
</script>

<template>
    <Head title="Finalizare comandă" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <div class="mb-6 flex items-center gap-4">
                    <Link
                        href="/cart"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Înapoi la coș
                    </Link>
                </div>

                <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">
                    Finalizare comandă
                </h1>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Shipping Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Date de livrare</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label
                                                for="shipping_name"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Nume complet *
                                            </label>
                                            <input
                                                id="shipping_name"
                                                v-model="form.shipping_name"
                                                type="text"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_name }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_name"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_name }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_email"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Email *
                                            </label>
                                            <input
                                                id="shipping_email"
                                                v-model="form.shipping_email"
                                                type="email"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_email }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_email"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="shipping_phone"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Telefon *
                                        </label>
                                        <input
                                            id="shipping_phone"
                                            v-model="form.shipping_phone"
                                            type="tel"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.shipping_phone }"
                                        />
                                        <p
                                            v-if="form.errors.shipping_phone"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.shipping_phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            for="shipping_address"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Adresă *
                                        </label>
                                        <input
                                            id="shipping_address"
                                            v-model="form.shipping_address"
                                            type="text"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.shipping_address }"
                                        />
                                        <p
                                            v-if="form.errors.shipping_address"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.shipping_address }}
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <label
                                                for="shipping_city"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Oraș *
                                            </label>
                                            <input
                                                id="shipping_city"
                                                v-model="form.shipping_city"
                                                type="text"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_city }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_city"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_city }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_postal_code"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Cod poștal
                                            </label>
                                            <input
                                                id="shipping_postal_code"
                                                v-model="form.shipping_postal_code"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_postal_code }"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_country"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Țară *
                                            </label>
                                            <input
                                                id="shipping_country"
                                                v-model="form.shipping_country"
                                                type="text"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_country }"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="notes"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Note (opțional)
                                        </label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="3"
                                            placeholder="Note suplimentare pentru comandă..."
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Order Summary -->
                        <div class="lg:col-span-1">
                            <Card class="sticky top-24">
                                <CardHeader>
                                    <CardTitle>Rezumat comandă</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="space-y-2">
                                        <div
                                            v-for="item in props.items"
                                            :key="item.id"
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                class="h-12 w-12 rounded object-cover"
                                            />
                                            <div class="flex-1">
                                                <p class="font-medium">{{ item.name }}</p>
                                                <p class="text-gray-500">
                                                    {{ item.quantity }} x {{ formatPrice(item.price) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Subtotal
                                            </span>
                                            <span class="font-medium">
                                                {{ formatPrice(props.subtotal) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                TVA (19%)
                                            </span>
                                            <span class="font-medium">
                                                {{ formatPrice(props.tax) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Transport
                                            </span>
                                            <span class="font-medium">
                                                {{ props.shippingCost === 0 ? 'Gratuit' : formatPrice(props.shippingCost) }}
                                            </span>
                                        </div>
                                        <div class="border-t pt-2">
                                            <div class="flex justify-between text-lg font-bold">
                                                <span>Total</span>
                                                <span>{{ formatPrice(props.total) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <Button
                                        type="submit"
                                        class="w-full"
                                        size="lg"
                                        :disabled="form.processing"
                                    >
                                        {{ form.processing ? 'Se procesează...' : 'Plasează comanda' }}
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

