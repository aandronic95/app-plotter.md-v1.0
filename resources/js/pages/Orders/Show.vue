<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, ArrowLeft } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    product_name: string;
    product_sku?: string;
    quantity: number;
    price: number;
    subtotal: number;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    subtotal: number;
    tax: number;
    shipping_cost: number;
    total: number;
    shipping_name: string;
    shipping_email: string;
    shipping_phone: string;
    shipping_address: string;
    shipping_city: string;
    shipping_postal_code?: string;
    shipping_country: string;
    notes?: string;
    created_at: string;
    items: OrderItem[];
}

interface Props {
    order: Order;
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'În așteptare',
        processing: 'În procesare',
        shipped: 'Expediată',
        delivered: 'Livrată',
        cancelled: 'Anulată',
    };
    return labels[status] || status;
};

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'În așteptare',
        paid: 'Plătită',
        failed: 'Eșuată',
        refunded: 'Rambursată',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="`Comandă ${props.order.order_number}`" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-4xl px-4 py-6 md:px-6">
                <div class="mb-6 flex items-center gap-4">
                    <Link
                        href="/"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Înapoi la produse
                    </Link>
                </div>

                <!-- Success Message -->
                <div class="mb-6 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        <p class="font-medium text-green-800 dark:text-green-200">
                            Comanda a fost plasată cu succes!
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Comandă {{ props.order.order_number }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Plasată pe {{ props.order.created_at }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Items -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Produse comandate</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="item in props.order.items"
                                        :key="item.id"
                                        class="flex items-center justify-between border-b pb-4 last:border-0"
                                    >
                                        <div class="flex-1">
                                            <p class="font-medium">{{ item.product_name }}</p>
                                            <p
                                                v-if="item.product_sku"
                                                class="text-sm text-gray-500"
                                            >
                                                SKU: {{ item.product_sku }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-medium">
                                                {{ item.quantity }} x {{ formatPrice(item.price) }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ formatPrice(item.subtotal) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Shipping Address -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Adresă de livrare</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-1">
                                    <p class="font-medium">{{ props.order.shipping_name }}</p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_address }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_city }}
                                        <span v-if="props.order.shipping_postal_code">
                                            , {{ props.order.shipping_postal_code }}
                                        </span>
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_country }}
                                    </p>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_phone }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_email }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card
                            v-if="props.order.notes"
                        >
                            <CardHeader>
                                <CardTitle>Note</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ props.order.notes }}
                                </p>
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
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Status comandă
                                    </p>
                                    <p class="font-medium">
                                        {{ getStatusLabel(props.order.status) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Status plată
                                    </p>
                                    <p class="font-medium">
                                        {{ getPaymentStatusLabel(props.order.payment_status) }}
                                    </p>
                                </div>
                                <div class="border-t pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Subtotal
                                        </span>
                                        <span class="font-medium">
                                            {{ formatPrice(props.order.subtotal) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            TVA (19%)
                                        </span>
                                        <span class="font-medium">
                                            {{ formatPrice(props.order.tax) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Transport
                                        </span>
                                        <span class="font-medium">
                                            {{ props.order.shipping_cost === 0 ? 'Gratuit' : formatPrice(props.order.shipping_cost) }}
                                        </span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total</span>
                                            <span>{{ formatPrice(props.order.total) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

