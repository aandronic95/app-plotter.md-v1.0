<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle, ArrowLeft, X, AlertCircle } from 'lucide-vue-next';
import { computed, ref, watch, onMounted } from 'vue';

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
    delivery_method?: {
        id: number;
        name: string;
        logo?: string | null;
    } | null;
    delivery_tracking_number?: string | null;
    notes?: string;
    created_at: string;
    items: OrderItem[];
}

interface Props {
    order: Order;
}

const props = defineProps<Props>();
const page = usePage();

// Flash messages
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Watch for flash messages
watch(flash, (newFlash) => {
    if (newFlash?.success) {
        successMessage.value = newFlash.success;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
    if (newFlash?.error) {
        errorMessage.value = newFlash.error;
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 5000);
    }
}, { immediate: true });

// Also check on mount
onMounted(() => {
    if (flash.value?.success) {
        successMessage.value = flash.value.success;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
    if (flash.value?.error) {
        errorMessage.value = flash.value.error;
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 5000);
    }
});

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
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-gray-900">
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
                <div
                    v-if="showSuccessMessage"
                    class="mb-6 relative rounded-lg bg-green-50 p-4 dark:bg-green-900/20"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-1">
                                Succes!
                            </h4>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                {{ successMessage }}
                            </p>
                        </div>
                        <button
                            @click="showSuccessMessage = false"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-if="showErrorMessage"
                    class="mb-6 relative rounded-lg bg-red-50 p-4 dark:bg-red-900/20"
                >
                    <div class="flex items-start gap-3">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">
                                Eroare
                            </h4>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                {{ errorMessage }}
                            </p>
                        </div>
                        <button
                            @click="showErrorMessage = false"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
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
                                        class="flex items-center justify-between pb-4"
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

                        <!-- Delivery Method -->
                        <Card v-if="props.order.delivery_method">
                            <CardHeader>
                                <CardTitle>Metodă de livrare</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="props.order.delivery_method.logo"
                                        :src="props.order.delivery_method.logo"
                                        :alt="props.order.delivery_method.name"
                                        class="h-12 w-12 object-contain rounded"
                                    />
                                    <div>
                                        <p class="font-medium">{{ props.order.delivery_method.name }}</p>
                                        <p
                                            v-if="props.order.delivery_tracking_number"
                                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                                        >
                                            AWB / Tracking: <span class="font-mono">{{ props.order.delivery_tracking_number }}</span>
                                        </p>
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
                                <div class="pt-4 space-y-2">
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
                                    <div class="pt-2">
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

